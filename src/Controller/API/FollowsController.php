<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class FollowsController extends APIController
{
    public function view($path)
    {
        $id = $path[0] ?? null;
        $subResource = $path[1] ?? null;
        $subResourceId = $path[2] ?? null;

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
