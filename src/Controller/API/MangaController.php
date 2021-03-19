<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class MangaController extends APIController
{
    public function view($path)
    {
        $id = $path[0] ?? null;
        $subResource = $path[1] ?? null;
        $subResourceId = $path[2] ?? null;

        $id = $this->validateId($id);

        switch ($subResource) {
            case 'chapters':
                $this->fetch($id); // check if exists
                return (new ChapterController())->fetchForManga($id);
            case 'covers':
                return $this->normalizeCovers($this->fetch($id));
            default:
                if (!empty($subResource)) {
                    throw new NotFoundHttpException();
                }
        }

        $data = $this->normalize($this->fetch($id));

        if (in_array('chapters', $this->request->query->getList('include'))) {
            $data = array_merge(['manga' => $data], (new ChapterController())->fetchForManga($id));
        }

        return $data;
    }

    public function fetch($id)
    {
        $manga = new \Manga($id);
        if (!isset($manga->manga_id)) {
            throw new NotFoundHttpException("Manga not found.");
        }
        return $manga;
    }

    public function normalize($manga)
    {
        $normalized = [
            //'type' => 'manga',
            'id' => $manga->manga_id,
            'title' => $manga->manga_name,
            'altTitles' => array_map(function ($alt_name) {
                return trim(\html_entity_decode($alt_name));
            }, $manga->get_manga_alt_names()),
            'description' => $manga->manga_description,
            'artist' => array_map(function ($a) {
                return trim($a);
            }, explode(',', $manga->manga_artist)),
            'author' => array_map(function ($a) {
                return trim($a);
            }, explode(',', $manga->manga_author)),
            'publication' => [
                'language' => $manga->lang_flag,
                'status' => $manga->manga_status_id,
                'demographic' => $manga->manga_demo_id,
            ],
            'tags' => $manga->get_manga_genres(),
            'lastChapter' => (!empty($manga->manga_last_chapter) && $manga->manga_last_chapter !== '0') ? $manga->manga_last_chapter : null,
            'lastVolume' => (string)$manga->manga_last_volume ?: null,
            'isHentai' => (bool)$manga->manga_hentai,
            'links' => json_decode($manga->manga_links),
            'relations' => array_map(function ($relation) {
                return [
                    'id' => $relation['related_manga_id'],
                    'title' => $relation['manga_name'],
                    'type' => $relation['relation_id'],
                    'isHentai' => (bool)$relation['manga_hentai'],
                ];
            }, $manga->get_related_manga()),
            'rating' => [
                'bayesian' => (float)($manga->manga_bayesian ?? 0),
                'mean' => (float)$manga->manga_rating ?? 0,
                'users' => count($manga->get_user_ratings()) ?? 0,
            ],
            'views' => $manga->manga_views,
            'follows' => $manga->manga_follows,
            'comments' => $manga->thread_posts ?? 0,
            'lastUploaded' => $manga->manga_last_uploaded,
            'mainCover' => $manga->manga_image ? $this->getFileUrl("/images/manga/$manga->manga_id.$manga->manga_image") : null,
            //'covers' => $this->normalizeCovers($manga),
        ];

        return $normalized;
    }

    private function normalizeCovers($manga) {
        return array_map(function ($cover) use ($manga) {
            return [
                'volume' => $cover['volume'],
                'url' => $this->getFileUrl("/images/covers/{$manga->manga_id}v{$cover['volume']}.{$cover['img']}"),
            ];
        }, $manga->get_covers());
    }
}
