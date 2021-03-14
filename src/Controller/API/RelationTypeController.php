<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class RelationTypeController extends APIController
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

        return $this->normalize($this->fetch());
    }

    public function fetch()
    {
        return new \Relation_Types();
    }

    private function normalize($types)
    {
        return array_map(function ($type) {
            return [
                'id' => $type->relation_id,
                'name' => $type->relation_name,
                'pairId' => $type->pair_id,
            ];
        }, (array)$types);
    }
}
