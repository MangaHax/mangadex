<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;
use Mangadex\Exception\Http\BadRequestHttpException;
use Mangadex\Exception\Http\ForbiddenHttpException;

class UserController extends APIController
{
    protected function validateId($id)
    {
        if ($id === 'me') {
            if ($this->user->user_id === 0) { //guest
                throw new ForbiddenHttpException("You are not authenticated.");
            }
            $id = $this->user->user_id;
        }
        return parent::validateId($id);
    }

    protected function isAuthorizedUser($id, $level = 'pr')
    {
        return $id == $this->user->user_id || ($level !== null && validate_level($this->user, $level));
    }

    protected function isAuthorizedUserForMangaList($targetUser, $level)
    {
        if ($this->isAuthorizedUser($targetUser->user_id, $level)) {
            return true;
        } else if ($targetUser->list_privacy === 1) {
            return true;
        } else if ($targetUser->list_privacy === 2) {
            $friends = $targetUser->get_friends_user_ids();
            if ($friends[$this->user->user_id]['accepted'] ?? false) {
                return true;
            }
        }
        return false;
    }

    public function view($path)
    {
        $id = $path[0] ?? null;
        $subResource = $path[1] ?? null;
        $subResourceId = $path[2] ?? null;

        $id = $this->validateId($id);

        switch ($subResource) {
            case 'chapters':
                $this->fetch($id); // check if exists
                return (new ChapterController())->fetchForUser($id);
            case 'followed-manga':
                $user = $this->fetch($id);
                if (!$this->isAuthorizedUserForMangaList($user, 'mod')) {
                    throw new ForbiddenHttpException();
                }
                return $this->fetchFollowedManga($id);
            case 'followed-updates':
                if (!$this->isAuthorizedUser($id)) {
                    throw new ForbiddenHttpException();
                }
                return $this->fetchFollowedUpdates($id);
            case 'manga':
                if (!$this->isAuthorizedUser($id)) {
                    throw new ForbiddenHttpException();
                }
                if (!is_numeric($subResourceId) || !$subResourceId) {
                    throw new BadRequestHttpException("No valid manga ID provided.");
                }
                return $this->fetchMangaUserData($id, $subResourceId);
            case 'ratings':
                if (!$this->isAuthorizedUser($id)) {
                    throw new ForbiddenHttpException();
                }
                return $this->fetchRatings($id);
            case 'settings':
                if (!$this->isAuthorizedUser($id)) {
                    throw new ForbiddenHttpException();
                }
                return $this->fetchSettings($id);
            case 'settings':
                if (!$this->isAuthorizedUser($id)) {
                    throw new ForbiddenHttpException();
                }
                return $this->fetchSettings($id);
            default:
                if (!empty($subResource)) {
                    throw new NotFoundHttpException();
                }
        }

        $data = $this->normalize($this->fetch($id));

        if (in_array('chapters', $this->request->query->getList('include'))) {
            $data = array_merge(['user' => $data], (new ChapterController())->fetchForUser($id));
        }

        return $data;
    }

    public function fetch($id)
    {
        if ($this->isAuthorizedUser($id, null)) {
            return $this->user;
        }
        $userResource = new \User($id, 'user_id');
        if (!isset($userResource->user_id) || $userResource->user_id === 0) {
            throw new NotFoundHttpException("User not found.");
        }
        return $userResource;
    }

    private function normalize($user)
    {
        $normalized = [
            //'type' => 'user',
            'id' => $user->user_id,
            'username' => $user->username,
            'levelId' => $user->level_id,
            'joined' => $user->joined_timestamp,
            'lastSeen' => $user->last_seen_timestamp,
            'website' => $user->user_website,
            'biography' => $user->user_bio,
            'views' => $user->user_views,
            'uploads' => $user->user_uploads,
            'premium' => $user->premium ? true : false,
            'mdAtHome' => ($user->md_at_home && $user->show_md_at_home_badge) ? (int)$user->md_at_home : 0,
            'avatar' => $user->avatar ? $this->getFileUrl("/images/avatars/$user->user_id.$user->avatar") : null,
        ];

        return $normalized;
    }

    private function normalizeMangaUserData($userId, $data)
    {
        return [
            'userId' => $userId,
            'mangaId' => $data['manga_id'],
            'mangaTitle' => $data['title'],
            'isHentai' => (bool) $data['manga_hentai'],
            'followType' => $data['follow_type'],
            'volume' => $data['volume'],
            'chapter' => $data['chapter'],
            'rating' => $data['rating'] ?: null,
            'mainCover' => $data['manga_image'] ? $this->getFileUrl("/images/manga/{$data['manga_id']}.{$data['manga_image']}") : null,
        ];
    }

    public function fetchFollowedManga($id)
    {
        $userResource = $this->fetch($id);
        $follows = $userResource->get_followed_manga_ids_api();
        if ($this->request->query->has('type')) {
            $type = $this->request->query->getInt('type');
            $follows = array_filter($follows, function ($m) use ($type) {
                return $m['follow_type'] === $type;
            });
        }
        $hentai = $this->request->query->getInt('hentai', 0);
        if ($hentai !== 1) {
            $follows = array_filter($follows, function ($m) use ($hentai) {
                return $m['manga_hentai'] === 0 && $hentai === 0 || $m['manga_hentai'] === 1 && $hentai === 2;
            });
        }
        return array_values(array_map(function ($data) use ($id) {
            return $this->normalizeMangaUserData($id, $data);
        }, $follows));
    }

    public function fetchFollowedUpdates($id)
    {
        $userResource = $this->fetch($id);
        return (new ChapterController())->fetchFollowedUpdates($userResource);
    }

    public function fetchMangaUserData($id, $mangaId)
    {
        $userResource = $this->fetch($id);
        $data = $userResource->get_manga_userdata($mangaId) ?? null;
        if ($data === null) {
            throw new NotFoundHttpException("Manga not found.");
        }
        return $this->normalizeMangaUserData($id, $data);
    }

    public function fetchRatings($id)
    {
        $userResource = $this->fetch($id);
        $follows = $userResource->get_manga_ratings();
        return array_map(
            function ($mangaId, $rating) {
                return ['mangaId' => $mangaId, 'rating' => $rating];
            },
            array_keys($follows),
            $follows
        );
    }

    public function fetchSettings($id)
    {
        $user = $this->fetch($id);
        $langIds = explode(',', $user->default_lang_ids ?? '');
        $exludedTags = explode(',', $user->excluded_genres ?? '');
        return [
            'id' => $user->user_id,
            'hentaiMode' => $user->hentai_mode,
            'latestUpdates' => $user->latest_updates,
            'showModeratedPosts' => (bool)$user->display_moderated,
            'showUnavailableChapters' => (bool)$user->show_unavailable,
            'shownChapterLangs' => array_map(function ($id) {
                return ['id' => $id];
            }, $langIds),
            'excludedTags' => array_map(function ($id) {
                return ['id' => (int)$id];
            }, $exludedTags),
        ];
    }

    public function create($path)
    {
        $id = $path[0] ?? null;
        $subResource = $path[1] ?? null;
        $subResourceId = $path[2] ?? null;

        $id = $this->validateId($id);
        $content = $this->decodeJSONContent();

        if ($subResource === 'marker') {
            if (!$this->isAuthorizedUser($id, 'developer')) {
                throw new ForbiddenHttpException();
            }
            $this->validateJSONContent(['chapters' => 'array', 'read' => 'bool'], $content);

            $user = $this->fetch($id);

            $ids = array_filter($content->chapters, function ($id) {
                return is_numeric($id);
            });
            $ids = array_slice($ids, 0, 100);

            return $content->read ? $this->markAsRead($ids, $user) : $this->markAsUnread($ids, $user);
        } else if (!empty($subResource)) {
            throw new NotFoundHttpException();
        }

        return parent::create($path);
    }

    public function markAsRead($ids, $user)
    {
        global $sql, $memcached;

        if (!empty($ids)) {
            $values = [];
            $binds = [];
            foreach ($ids as $id) {
                $values[] = "(?, ?)";
                $binds[] = $user->user_id;
                $binds[] = $id;
            }
            $sql->modify('chapter_mark_read', "INSERT IGNORE INTO mangadex_chapter_views (user_id, chapter_id) VALUES " . implode(',', $values), $binds);
            $memcached->delete("user_{$user->user_id}_read_chapters");
        }
        return ['read' => $ids];
    }

    public function markAsUnread($ids, $user)
    {
        global $sql, $memcached;

        $ids_in = prepare_in($ids);
        if (!empty($ids_in)) {
            $sql->modify('chapter_mark_unread', "DELETE FROM mangadex_chapter_views WHERE user_id = ? AND chapter_id IN ($ids_in)", array_merge([$user->user_id], $ids));
            $memcached->delete("user_{$user->user_id}_read_chapters");
        }
        return ['unread' => $ids];
    }
}
