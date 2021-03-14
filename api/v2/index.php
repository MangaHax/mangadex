<?php

require_once('../../bootstrap.php');
require_once(ABSPATH . '/scripts/header.req.php');

use Mangadex\Controller\API\ChapterController;
use Mangadex\Controller\API\FollowsController;
use Mangadex\Controller\API\GroupController;
use Mangadex\Controller\API\IndexController;
use Mangadex\Controller\API\MangaController;
use Mangadex\Controller\API\RelationTypeController;
use Mangadex\Controller\API\TagController;
use Mangadex\Controller\API\UserController;
use Mangadex\Exception\Http\HttpException;
use Mangadex\Exception\Http\NotFoundHttpException;
use Mangadex\Exception\Http\TooManyRequestsHttpException;
use Mangadex\Model\Guard;
use Mangadex\Model\JsonResponse;

$response = new JsonResponse();

try {
    if (!process_user_limit(1500, 'api_')) {
        throw new TooManyRequestsHttpException('Too many hits detected from your IP! Please try again tomorrow.');
    }

    $guard = Guard::getInstance();
    if (isset($_COOKIE[SESSION_COOKIE_NAME]) || isset($_COOKIE[SESSION_REMEMBERME_COOKIE_NAME])) {
        $guard->tryRestoreSession($_COOKIE[SESSION_COOKIE_NAME] ?? null, $_COOKIE[SESSION_REMEMBERME_COOKIE_NAME] ?? null);
        $user = $guard->hasUser() ? $guard->getUser() : $guard->getUser(0); // Fetch guest record (userid=0) if no user could be restored
    } else {
        $user = $guard->getUser(0); // Fetch guest
    }
    /** @var $sentry Raven_Client */
    if (isset($sentry) && isset($user)) {
        $sentry->user_context([
            'id' => $user->user_id,
            'username' => $user->username,
        ]);
    }

    $path = explode('/', $_GET['path']);

    switch ($path[0] ?: 'index') {
        case 'manga':
        case 'title':
            $controller = new MangaController();
            break;
        case 'chapter':
            $controller = new ChapterController();
            break;
        case 'user':
            $controller = new UserController();
            break;
        case 'group':
            $controller = new GroupController();
            break;
        case 'tag':
            $controller = new TagController();
            break;
        case 'relations':
            $controller = new RelationTypeController();
            break;
        case 'follows':
            $controller = new FollowsController();
            break;
        case 'index':
            $controller = new IndexController();
            break;
        default:
            throw new NotFoundHttpException("Invalid endpoint");
            break;
    }
    $response = $controller->handleRequest(array_slice($path, 1));
} catch (HttpException $e) {
    $response->setCode($e->getCode());
    $response->setMessage($e->getMessage());
} catch (\Throwable $e) {
    $response->setCode(500);
    $response->setMessage(DEBUG ? $e->getMessage() : "Internal server error");
} finally {
    header('Content-Type: application/json');
    http_response_code($response->getCode());
    echo json_encode($response->normalize());
}
