<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;
use Mangadex\Exception\Http\BadRequestHttpException;
use Mangadex\Exception\Http\ForbiddenHttpException;
use Mangadex\Exception\Http\GoneHttpException;
use Mangadex\Exception\Http\UnavailableForLegalReasonsHttpException;

class ChapterController extends APIController
{
    const CHAPTERS_LIMIT = 6000;
    const CH_STATUS_OK = 'OK';
    const CH_STATUS_DELETED = 'deleted';
    const CH_STATUS_DELAYED = 'delayed';
    const CH_STATUS_UNAVAILABLE = 'unavailable';
    const CH_STATUS_RESTRICTED = 'restricted';
    const CH_STATUS_EXTERNAL = 'external';

    protected function validateId($id)
    {
        try {
            return parent::validateId($id);
        } catch (\Throwable $th) {
            if (preg_match('/^[a-z0-9]+$/i', $id)) {
                $id = $this->fetchIdByHash($id);
                if (!$id) {
                    throw new NotFoundHttpException("Chapter not found by hash.");
                }
                return $id;
            }
            throw $th;
        }
    }

    public function view($path)
    {
        /**
         * @param array{0: int|string, 1: string|null, 2: int|string|mixed|null} $path
         */
        [$id, $subResource, $subResourceId] = $path;

        $id = $this->validateId($id);

        if (!empty($subResource)) {
            throw new NotFoundHttpException();
        }

        $chapter = $this->fetch($id);
        $normalized = $this->normalize($chapter, true);
        // only update the views if the user can actually read the chapter
        if (isset($normalized['pages'])) {
            $this->updateChapterViews($chapter);
        }
        return $normalized;
    }

    public function fetchIdByHash($hash)
    {
        global $sql;
        return $sql->prep(
            "chapter_$hash",
            " SELECT chapter_id FROM mangadex_chapters WHERE chapter_hash = ? LIMIT 1 ",
            [$hash],
            'fetchColumn',
            '',
            3600
        );
    }

    public function fetch($id)
    {
        $chapter = new \Chapter($id);
        $chapter = (object)$chapter;
        if (!isset($chapter->chapter_id)) {
            throw new NotFoundHttpException("Chapter not found.");
        }
        return $chapter;
    }

    protected function fetchChapterList($search, $order)
    {
        $page = $this->request->query->getInt('p', 0);
        $limit = $this->request->query->getInt('limit', 100);
        if ($limit < 10 || $limit > 100) {
            throw new BadRequestHttpException("Invalid limit, range must be within 10 - 100.");
        }

        $chapters = new \Chapters($search);
        $list = $chapters->query_read($order, self::CHAPTERS_LIMIT, 1);
        if ($page > 0) {
            $list = array_slice($list, $limit * ($page - 1), $limit);
        }
        return $this->normalizeList($list, false);
    }

    public function fetchForManga($mangaId)
    {
        $search = [
            "manga_id" => $mangaId,
            "available" => 1,
            "chapter_deleted" => 0,
        ];
        $order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";
        return $this->fetchChapterList($search, $order);
    }

    public function fetchForGroup($groupId)
    {
        $search = [
            "group_id" => $groupId,
            "available" => 1,
            "chapter_deleted" => 0,
        ];
        $order = "chapter_id ASC";
        return $this->fetchChapterList($search, $order);
    }

    public function fetchForUser($userId)
    {
        $search = [
            "user_id" => $userId,
            "available" => 1,
            "chapter_deleted" => 0,
        ];
        $order = "chapter_id ASC";
        return $this->fetchChapterList($search, $order);
    }

    public function fetchFollowedUpdates($userResource)
    {
        // adapted from /pages/follows.req.php

        $page = $this->request->query->getInt('p', 1);
        $followType = $this->request->query->getInt('type', 0);
        // get hentai setting from the query param, fallback to hidden
        $hentai = $this->request->query->getInt('hentai', 0);
        // get lang filter from the query param, fallback to user setting
        // TODO: lang code -> id mapping, plus make sure the query param actually works properly
        $langFilter = /*$this->request->query->has('langs') ? $this->request->query->getList('langs') :*/ $this->user->default_lang_ids;
        // include delayed
        $delayed = $this->request->query->getBoolean('delayed', false);

        $followedManga = $userResource->get_followed_manga_ids_key_pair();
        $mangaIds = $followType === 0 ? array_keys($followedManga) : array_keys($followedManga, $followType);

        if (empty($mangaIds)) {
            return [];
        }

        $search = [];
        if (!empty($langFilter)) {
            $search["multi_lang_id"] = $langFilter;
        }
        $blockedGroups = $this->user->get_blocked_groups();
        if ($blockedGroups) {
            $search['blocked_groups'] = array_keys($blockedGroups);
        }
        if ($hentai !== 1) { // i.e. if hentai is 0 (hide) or >1 (show only)
            $search['manga_hentai'] = $hentai ? 1 : 0;
        }
        $search['chapter_deleted'] = 0;
        $search['exclude_delayed'] = $delayed ? 0 : 1;
        $search['manga_ids_array'] = $mangaIds;
        // limit the search to available chapters if the user has NOT set "show unavailable" on
        if (!$this->user->show_unavailable ?? true) {
            $search['available'] = 1;
        }

        $order = 'upload_timestamp DESC';
        $limit = 100;
        $chapters = new \Chapters($search);
        $chaptersResult = $chapters->query_read($order, $limit, max($page, 1));
        $normalized = $this->normalizeList($chaptersResult, false);

        // include basic manga entities
        $manga = [];
        foreach ($chaptersResult as $chapter) {
            if (!isset($manga[$chapter['manga_id']])) {
                $manga[$chapter['manga_id']] = [
                    'id' => $chapter['manga_id'],
                    'name' => $chapter['manga_name'],
                    'isHentai' => (bool)$chapter['manga_hentai'],
                    'lastChapter' => (!empty($chapter['manga_last_chapter']) && $chapter['manga_last_chapter'] !== '0') ? $chapter['manga_last_chapter'] : null,
                    'lastVolume' => (string)$chapter['manga_last_volume'] ?: null,
                    'mainCover' => $chapter['manga_image'] ? $this->getFileUrl("/images/manga/{$chapter['manga_id']}.{$chapter['manga_image']}") : null,
                ];
            }
        }

        $normalized['manga'] = $manga;
        return $normalized;
    }

    protected function normalize($chapter, $fullData = false)
    {
        $normalized = [
            //'type' => 'chapter',
            'id' => $chapter->chapter_id,
            'hash' => $chapter->chapter_hash,
            'mangaId' => $chapter->manga_id,
            'mangaTitle' => $chapter->manga_name,
            'volume' => $chapter->volume,
            'chapter' => $chapter->chapter,
            'title' => html_entity_decode($chapter->title),
            'language' => $chapter->lang_flag,
            'groups' => [],
            'uploader' => $chapter->user_id,
            'timestamp' => $chapter->upload_timestamp,
            'comments' => $chapter->thread_posts ?? 0,
            'views' => $chapter->chapter_views ?? 0,
        ];

        $groups = [[$chapter->group_id, $chapter->group_name], [$chapter->group_id_2, $chapter->group_name_2], [$chapter->group_id_3, $chapter->group_name_3]];
        $groupsFiltered = array_filter($groups, function ($g) {
            return !empty($g[0]);
        });
        $normalized['groups'] = array_map(function ($g) {
            return ['id' => $g[0], 'name' => $g[1]];
        }, $groupsFiltered);

        if ($fullData) {
            $normalized['status'] = self::CH_STATUS_OK;
            $isExternal = substr($chapter->page_order, 0, 4) === 'http';

            // Set status when something other than OK
            if ($chapter->chapter_deleted) {
                if (!validate_level($this->user, 'pr')) {
                    throw new GoneHttpException(self::CH_STATUS_DELETED);
                }
                $normalized['status'] = self::CH_STATUS_DELETED;
            } else if (!$chapter->available) {
                if (!validate_level($this->user, 'pr')) {
                    throw new UnavailableForLegalReasonsHttpException(self::CH_STATUS_UNAVAILABLE);
                }
                $normalized['status'] = self::CH_STATUS_UNAVAILABLE;
                $normalized['groups'] = [];
            } else if ($chapter->upload_timestamp > time()) {
                $normalized['status'] = self::CH_STATUS_DELAYED;
                $normalized['groupWebsite'] = $chapter->group_website ?: null;
            } else if ($isExternal) {
                $normalized['status'] = self::CH_STATUS_EXTERNAL;
                $normalized['pages'] = $chapter->page_order;
            } else if (
                in_array($chapter->manga_id, RESTRICTED_MANGA_IDS) &&
                !validate_level($this->user, 'contributor') &&
                $this->user->get_chapters_read_count() < MINIMUM_CHAPTERS_READ_FOR_RESTRICTED_MANGA
            ) {
                if (!validate_level($this->user, 'pr')) {
                    throw new ForbiddenHttpException(self::CH_STATUS_RESTRICTED);
                }
                $normalized = [
                    'id' => $chapter->chapter_id,
                    'status' => self::CH_STATUS_RESTRICTED,
                ];
            }

            // Include page information for non-external chapters and only for non-restricted users
            if (!$isExternal && ($normalized['status'] === self::CH_STATUS_OK || validate_level($this->user, 'pr'))) {
                $pages = explode(',', $chapter->page_order);

                $serverFallback = LOCAL_SERVER_URL;
                $serverNetwork = null;
                // when a chapter does not exist on the local webserver, it gets an id
                // since all imageservers share the same data, we can assign any imageserver with the best location to the user
                if ($chapter->server > 0) {
                    if ($this->user->md_at_home ?? false) {
                        try {
                            $subsubdomain = $this->mdAtHomeClient->getServerUrl($chapter->chapter_hash, $pages, _IP);
                            if (!empty($subsubdomain)) {
                                $serverNetwork = $subsubdomain;
                            }
                        } catch (\Throwable $t) {
                            trigger_error($t->getMessage(), E_USER_WARNING);
                        }
                    }
                    $serverId = -1;
                    if ($this->request->query->has('server')) {
                        // if the parameter was trash, this returns -1
                        $serverId = get_server_id_by_code($this->request->query->get('server'));
                    }
                    if ($serverId < 1) {
                        // try to select a region-based server if we haven't one set already
                        $serverId = get_server_id_by_geography();
                    }
                    if ($serverId > 0) {
                        $serverFallback = "https://s$serverId.mangadex.org";
                    }
                }
                $server = $serverNetwork ?: $serverFallback;
                $dataDir = $this->request->query->getBoolean('saver') ? '/data-saver/' : '/data/';

                $normalized['pages'] = $pages;
                $normalized['server'] = $server . $dataDir;

                if (!empty($serverNetwork)) {
                    $normalized['serverFallback'] = $serverFallback . $dataDir;
                }
            }
        }

        return $normalized;
    }

    protected function normalizeList($chapters, $fullData = false)
    {
        $chapters = array_map(function ($chapter) use ($fullData) {
            return $this->normalize((object)$chapter, $fullData);
        }, $chapters);

        // create a separate group array and leave only the group ids inside the chapter entity
        $groups = [];
        foreach ($chapters as &$chapter) {
            $chapterGroups = $chapter['groups'];
            $chapter['groups'] = [];
            foreach ($chapterGroups as $g) {
                $groups[$g['id']] = $g;
                $chapter['groups'][] = $g['id'];
            }
        }

        return [
            'chapters' => $chapters,
            'groups' => array_values($groups), // turn them from maps to arrays
        ];
    }

    private function updateChapterViews($chapter)
    {
        global $sql;
        global $memcached;

        // copypasted code from the old api

        update_views_v2("chapter", $chapter->chapter_id, _IP, $this->user->user_id);

        if ($this->user->user_id && $this->request->query->getBoolean("mark_read", true)) {
            $manga = new \Manga($chapter->manga_id);

            $chapter->update_chapter_views($this->user->user_id, $manga->get_follows_user_id());

            $chapter->update_reading_history($this->user->user_id, $this->user->get_reading_history(true));

            $followed_manga_ids_array = $this->user->get_followed_manga_ids();
            if (isset($followed_manga_ids_array[$chapter->manga_id])) {
                if ((int) $followed_manga_ids_array[$chapter->manga_id]['chapter'] == (int) $chapter->chapter - 1)
                    $sql->modify('increment_chapter', ' UPDATE mangadex_follow_user_manga SET chapter = ABS(chapter) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$chapter->manga_id, $this->user->user_id]);

                if ((int) $followed_manga_ids_array[$chapter->manga_id]['volume'] == (int) $chapter->volume - 1)
                    $sql->modify('increment_volume', ' UPDATE mangadex_follow_user_manga SET volume = ABS(volume) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$chapter->manga_id, $this->user->user_id]);

                $memcached->delete("user_{$this->user->user_id}_followed_manga_ids");
            }
        }
    }
}
