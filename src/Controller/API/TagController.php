<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class TagController extends APIController
{
    public function view($path)
    {
        $id = $path[0] ?? null;
        $subResource = $path[1] ?? null;
        $subResourceId = $path[2] ?? null;

        if ($id) {
            $id = $this->validateId($id);

            switch ($subResource) {
                default:
                    if (!empty($subResource)) {
                        throw new NotFoundHttpException();
                    }
            }
            return $this->normalize($this->fetch($id));
        } else {
            return $this->normalize($this->fetchAll());
        }
    }

    public function fetch($id)
    {
        $tags = $this->fetchAll();
        foreach ($tags as $tag) {
            if ($tag['id'] == $id) {
                return $tag;
            }
        }
        throw new NotFoundHttpException("Tag not found.");
    }

    public function fetchAll()
    {
        $tags = new \Grouped_Genres();
        return $tags->toGenreArray();
    }

    private function normalize($tag)
    {
        return $tag;
    }
}
