<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class FollowsController extends APIController
{
    public function view($path)
    {
        /**
         * @param array{0: int|string, 1: string|null, 2: int|string|mixed|null} $path
         */
        [$id, $subResource, $subResourceId] = $path;

        if (!empty($id)) {
            throw new NotFoundHttpException();
        }

        return $this->normalize($this->fetchTypes());
    }

    public function fetchTypes()
    {
        return new \Follow_Types();
    }

    private function normalize($types)
    {
        return array_map(function ($type) {
            return [
                'id' => $type->type_id,
                'name' => $type->type_name,
            ];
        }, (array)$types);
    }
}
