<?php

$id = (int)($_GET["id"] ?? 0);
$mode = $_GET['mode'] ?? 'tracks';

// hardcoded data
$tracks = [
    [
        "collection_id" => 1,
        "number" => 1,
        "title" => "お兄ちゃんとイケない身体",
        "title_romaji" => "Onii-chan to ikenai karada",
        "length" => 963,
        "sources" => [
            [
                "format" => "Ogg Vorbis q10.0",
                "filename" => "/data-drama/1/source.ogg",
                "size" => 47529903,
            ],
            [
                "format" => "FLAC",
                "filename" => "/data-drama/1/source.flac",
                "size" => 73856626,
            ],
            [
                "format" => "MP3 V2",
                "filename" => "/data-drama/1/source.mp3",
                "size" => 20008343,
            ],
        ],
    ],
    [
        "collection_id" => 1,
        "number" => 2,
        "title" => "お兄ちゃんとロールプレイ",
        "title_romaji" => "Onii-chan to roorupurei",
        "length" => null,
        "sources" => [],
    ],
    [
        "collection_id" => 1,
        "number" => 3,
        "title" => "お兄ちゃんとはじめてのおつかい",
        "title_romaji" => "Onii-chan to hajimete no otsukai",
        "length" => null,
        "sources" => [],
    ],
    [
        "collection_id" => 1,
        "number" => 4,
        "title" => "お兄ちゃんと特別自宅警備",
        "title_romaji" => "Onii-chan to tokubetsu jitakukeibi",
        "length" => null,
        "sources" => [],
    ],
];

$captions = [
    1 => [
        "id" => 1,
        "collection_id" => 1,
        "track_number" => 1,
        "title" => "Onii-chan and the Unpleasurable Body",
        "groups" => [
            [
                "id" => 1,
                "name" => "Doki Fansubs",
            ],
            [
                "id" => 262,
                "name" => "teas-tl",
            ],
        ],
        "filename" => "/data-drama/1/captions-1.vtt",
        "uploader" => [
            "id" => 3425,
            "name" => "Teasday",
            "color" => "#c00",
        ],
        "uploaded_at" => 1555181920,
        "views" => 420,
        "language" => [
            "code" => "gb",
            "name" => "English",
        ],
    ],
    2 => [
        "id" => 2,
        "collection_id" => 1,
        "track_number" => 1,
        "title" => "Onii-chan et le Corps Insatisfiable",
        "groups" => [
            [
                "id" => 1,
                "name" => "Doki Fansubs",
            ],
            [
                "id" => 262,
                "name" => "teas-tl",
            ],
            [
                "id" => 6669,
                "name" => "Bocchi-sensei teaches La France !",
            ],
        ],
        "filename" => "/data-drama/1/captions-1_FR.vtt",
        "uploader" => [
            "id" => 9268,
            "name" => "shyning",
            "color" => "#099",
        ],
        "uploaded_at" => 1556181920,
        "views" => 420,
        "language" => [
            "code" => "fr",
            "name" => "French",
        ],
    ],
];
$collections = [
    1 => [
        "id" => 1,
        "name" => "Drama CD Onii-chan wa Oshimai!",
        "alt_names" => ["ドラマＣＤお兄ちゃんはおしまい！", "Drama CD Onii-chan is Done For!"],
        "cover" => "/data-drama/1/cover.png",
        "language" => [
            "code" => "jp",
            "name" => "Japanese",
        ],
        "pub_date" => 1525046400,
        "description" => "A TS Slice of Life Comedy about a brother who one morning, wakes up to find that he was transformed into a little girl by his younger sister's strange drugs.",
    ]
];

$thread = 80852;

if ($id !== 0) {
    if (isset($collections[$id])) {
        $collectionTracks = [];
        foreach ($tracks as $track) {
            if ($track["collection_id"] === $id) {
                $track["captions"] = [];
                foreach ($captions as $caption) {
                    if ($caption["collection_id"] === $track["collection_id"] && $caption["track_number"] === $track["number"]) {
                        $track["captions"][] = $caption;
                    }
                }
                $collectionTracks[] = $track;
            }
        }
        $page_html = parse_template('drama/drama', [
            "page" => "drama",
            "collection" => $collections[$id],
            "tracks" => $collectionTracks,
            "mode" => $mode,
            "thread" => $thread,
        ]);
    } else {
        $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Drama collection #$id does not exist."]);
    }
} else {
    $page_html = parse_template('drama/drama_list', [
        "page" => "drama_list",
        "collections" => $collections,
        "thread" => $thread,
    ]);
}