<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\ForbiddenHttpException;

class HighestChapterIDController extends APIController
{
    public function view($path)
    {
        $isAuthorized = validate_level($this->user, 'pr') || $this->request->headers->get("API_KEY") === PRIVATE_API_KEY;
        if (!$isAuthorized) {
            throw new ForbiddenHttpException("You are not authorized.");
        }

        $id = $this->fetch();
        $normalized = $this->normalize($id);

        return $normalized;
    }

    public function fetch()
    {
        global $sql;
        return $sql->prep(
            "latest_chapter_id",
            " SELECT MAX(chapter_id) FROM mangadex_chapters",
            [],
            'fetchColumn',
            '',
            -1
        );
    }

    protected function normalize($id)
    {
        $normalized = [
            'id' => $id,
        ];
        return $normalized;
    }
}
