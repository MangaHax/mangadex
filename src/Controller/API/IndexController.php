<?php

namespace Mangadex\Controller\API;

class IndexController extends APIController
{
    public function view($path)
    {
        return [
            "information" => "Authentication is achieved by the same means as logging in to the site (i.e. the mangadex_session, mangadex_rememberme_token cookies, correct User-Agent). Some chapters may require authenticated permissions to access. The Content-Type header for requests with bodies must be application/json, and the content must be valid JSON. Boolean query values are evaluated 1/true/on/yes for true, otherwise false.",
            "baseUrl" => URL . "api/v2",
            "resources" => [
                "GET /" => [
                    "description" => "The current page, the API index.",
                    "aliases" => [ "index" ],
                ],
                "GET /manga/{id}" => [
                    "description" => "Get a manga.",
                    "aliases" => [ "title" ],
                    "pathParameters" => [
                        "{id}" => "The manga ID number.",
                    ],
                    "queryParameters" => [
                        "include" => "(Optional) Possible values: chapters (Include partial chapter information)."
                    ],
                    "subResources" => [
                        "GET /manga/{id}/chapters" => [
                            "description" => "Get partial information about the chapters belonging to a manga.",
                            "queryParameters" => [
                                "p" => "(Optional) The current page of the paginated results, starting from 1. Integer, default disables pagination.",
                                "limit" => "(Optional) The limit of the paginated results, allowed range 10 - 100. Integer, default 100.",
                            ],
                        ],
                        "GET /manga/{id}/covers" => [
                            "description" => "Get a list of covers belonging to a manga.",
                        ],
                    ],
                ],
                "GET /chapter/{id|hash}" => [
                    "description" => "Get a chapter. Possible error codes: 410 (deleted), 403 (restricted), 451 (unavailable).",
                    "pathParameters" => [
                        "{id|hash}" => "The chapter ID number, or the chapter hash.",
                    ],
                    "queryParameters" => [
                        "server" => "(Optional) Use to override location-based server assignment. Possible values: na, na2.",
                        "saver" => "(Optional) Use low quality images (data saver). Boolean, default false.",
                        "mark_read" => "(Optional) Mark the chapter as read. Boolean, default true.",
                    ],
                ],
                "GET /group/{id}" => [
                    "description" => "Get a group.",
                    "pathParameters" => [
                        "{id}" => "The group ID number.",
                    ],
                    "queryParameters" => [
                        "include" => "(Optional) Possible values: chapters (Include partial chapter information)."
                    ],
                    "subResources" => [
                        "GET /group/{id}/chapters" => [
                            "description" => "Get partial information about the chapters belonging to the group.",
                            "queryParameters" => [
                                "p" => "(Optional) The current page of the paginated results, starting from 1. Integer, default disables pagination.",
                                "limit" => "(Optional) The limit of the paginated results, allowed range 10 - 100. Integer, default 100.",
                            ],
                        ],
                    ],
                ],
                "GET /user/{id}" => [
                    "description" => "Get a user.",
                    "pathParameters" => [
                        "{id}" => "The user ID number, or the string 'me' as an alias for the current cookie-authenticated user.",
                    ],
                    "queryParameters" => [
                        "include" => "(Optional) Possible values: chapters (Include partial chapter information)."
                    ],
                    "subResources" => [
                        "GET /user/{id}/chapters" => [
                            "description" => "Get partial information about the chapters uploaded by the user.",
                            "queryParameters" => [
                                "p" => "(Optional) The current page of the paginated results, starting from 1. Integer, default disables pagination.",
                                "limit" => "(Optional) The limit of the paginated results, allowed range 10 - 100. Integer, default 100.",
                            ],
                        ],
                        "GET /user/{id}/settings" => [
                            "description" => "(Authorization required) Get a user's website settings.",
                        ],
                        "GET /user/{id}/followed-manga" => [
                            "description" => "(Authorization required) Get a user's followed manga and personal data for them.",
                        ],
                        "GET /user/{id}/followed-updates" => [
                            "description" => "(Authorization required) Get the latest uploaded chapters for the manga that the user has followed, as well as basic related manga information. Ordered by timestamp descending (the datetime when the chapter is available). Limit 100 chapters per page. Note that the results are automatically filtered by the authorized user's chapter language filter setting.",
                            "queryParameters" => [
                                "p" => "(Optional) The current page of the paginated results. Integer, default 1.",
                                "type" => "(Optional) Filter the results by the follow type ID (i.e. 1 = Reading, 2 = Completed etc). Use 0 to remove filtering. Integer, default 0.",
                                "hentai" => "(Optional) Filter results based on whether the titles are marked as hentai. 0 = Hide H, 1 = Show all, 2 = Show H only. Integer, default 0.",
                                "delayed" => "(Optional) Include delayed chapters in the results. Boolean, default false.",
                                //"langs" => "(Optional) Filter results based on the scanlation language. Use a comma-separated list of language IDs.",
                            ],
                        ],
                        "GET /user/{id}/ratings" => [
                            "description" => "(Authorization required) Get a user's manga ratings.",
                        ],
                        "GET /user/{id}/manga/{mangaId}" => [
                            "description" => "(Authorization required) Get a user's personal data for any given manga.",
                            "pathParameters" => [
                                "{mangaId}" => "The manga ID number.",
                            ],
                        ],
                    ],
                ],
                "POST /user/{id}/marker" => [
                    "description" => "(Authorization required) Set or unset chapter read markers.",
                    "pathParameters" => [
                        "{id}" => "The user ID number, or the string 'me' as an alias for the current cookie-authenticated user.",
                    ],
                    "jsonBodyParameters" => [
                        "chapters" => "(Required) Array[Integer]. List of chapter IDs to set or unset. Max 100 items.",
                        "read" => "(Required) Boolean. Set or unset the chapter as read.",
                    ]
                ],
                "GET /tag" => [
                    "description" => "Get all tags.",
                ],
                "GET /tag/{id}" => [
                    "description" => "Get a tag.",
                ],
                "GET /relations" => [
                    "description" => "Get all manga relation types.",
                ],
                "GET /follows" => [
                    "description" => "Get all follow types.",
                ],
            ],
        ];
    }

}
