<?php
class Mangas {
    public function __construct($search) {
        global $sql;
        $this->sql = $sql;

        $search_string = "";
        $left_join_alt_names = "";

        $pdo_bind = [];

        $multi_langs_string = "";
        $multi_lang_id_array = [];

        $distinct = '';
        $cache_time = 60;

        $tag_mode_inc = $search['tag_mode_inc'] ?? 'all';
        $tag_mode_exc = $search['tag_mode_exc'] ?? 'any';

        foreach ($search as $key => $value) {
            switch ($key) {
                case "tag_mode_inc":
                case "tag_mode_exc":
                    break;

                case "manga_genres_inc":
                    if (!empty($value)) {
                        $terms = is_array($value) ? $value : explode(',', $value);
                        $terms = array_map(function($t) { return (int)$t; }, $terms);
                        $terms = array_filter($terms, function($t) { return (bool)$t; });
                        $terms_count = sizeof($terms);
                        $terms = implode(',', $terms);
                        if ($tag_mode_inc === 'all') {
                            // include if manga has all of the included tags
                            $search_string .= $terms_count.' = (SELECT count(*) AS genre_count FROM mangadex_manga_genres genres WHERE genres.manga_id = mangas.manga_id AND genres.genre_id IN ('.$terms.')) AND ';
                        } else {
                            // include if manga has any of the included tags
                            $search_string .= 'EXISTS (SELECT genre_id FROM mangadex_manga_genres genres WHERE genres.manga_id = mangas.manga_id AND genres.genre_id IN ('.$terms.')) AND ';
                        }
                    }
                    break;

                case 'excluded_genres':
                case "manga_genres_exc":
                    if (!empty($value)) {
                        $terms = is_array($value) ? $value : explode(',', $value);
                        $terms = array_map(function($t) { return (int)$t; }, $terms);
                        $terms = array_filter($terms, function($t) { return (bool)$t; });
                        $terms_count = sizeof($terms);
                        $terms = implode(',', $terms);
                        if ($tag_mode_exc === 'all') {
                            // include if manga doesn't have all of the excluded tags
                            $search_string .= $terms_count.' != (SELECT count(*) AS genre_count FROM mangadex_manga_genres genres WHERE genres.manga_id = mangas.manga_id AND genres.genre_id IN ('.$terms.')) AND ';
                        } else {
                            // include if manga doesn't have any of the excluded tags
                            $search_string .= 'NOT EXISTS (SELECT genre_id FROM mangadex_manga_genres genres WHERE genres.manga_id = mangas.manga_id AND genres.genre_id IN ('.$terms.')) AND ';
                        }
                    }
                    break;

                case "manga_name":
                    $terms = explode(" ", $value);
                    foreach ($terms as $term) {
                        $search_string .= "(mangas.manga_name LIKE ? OR alt_names.alt_name LIKE ?) AND ";
                        $pdo_bind[] = "%$term%";
                        $pdo_bind[] = "%$term%";
                    }
                    $left_join_alt_names = "LEFT JOIN mangadex_manga_alt_names AS alt_names ON alt_names.manga_id = mangas.manga_id";
                    $distinct = 'DISTINCT';
                    break;

                case "manga_alpha":
                    if ($value == "~")
                        $search_string .= "manga_name REGEXP '^[0-9._\+\#]' AND ";
                    else {
                        $search_string .= "manga_name LIKE ? AND ";
                        $pdo_bind[] = "$value%";
                    }
                    break;

                case "manga_artist":
                case "manga_author":
                    $field = prepare_identifier("$key");
                    $search_string .= "$field LIKE ? AND ";
                    $pdo_bind[] = "%$value%";
                    break;

                case "manga_ids_array":
                    $in = prepare_in($value);
                    $search_string .= "mangas.manga_id IN ($in) AND ";
                    $pdo_bind = array_merge($pdo_bind, $value);
                    break;

                case 'multi_lang_id':
                    $in = prepare_in($value);
                    $multi_langs_string = "AND chapters.lang_id IN ($in) ";
                    $multi_lang_id_array = $value;
                    break;

                case 'demos':
                    if (!empty($value)) {
                        $in = prepare_in($value);
                        $search_string .= "mangas.manga_demo_id IN ($in) AND ";
                        $pdo_bind = array_merge($pdo_bind, $value);
                    }
                    break;

                case 'statuses':
                    if (!empty($value)) {
                        $in = prepare_in($value);
                        $search_string .= "mangas.manga_status_id IN ($in) AND ";
                        $pdo_bind = array_merge($pdo_bind, $value);
                    }
                    break;

                /*
            case 'excluded_genres':
                if (is_array($value))
                    $value = implode(',', array_map('intval', $value));
                $search_string .= 'mangas.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ('.$value.')) AND';
                break;
                */

                default:
                    $field = prepare_identifier("$key");
                    $search_string .= "$field = ? AND ";
                    $pdo_bind[] = $value;
                    break;
            }
        }

        $this->hash = hash_array($search);

        $this->num_rows = $sql->prep("mangas_query_{$this->hash}_num_rows", "
			SELECT count(*) FROM
			(SELECT DISTINCT mangas.manga_id
			FROM mangadex_mangas AS mangas
			$left_join_alt_names
			WHERE $search_string 1=1) AS temp
			", $pdo_bind, 'fetchColumn', '', 60);

        $this->search_string = $search_string;
        $this->left_join_alt_names = $left_join_alt_names;
        $this->pdo_bind = $pdo_bind;
        $this->multi_langs_string = $multi_langs_string;
        $this->multi_lang_id_array = $multi_lang_id_array;
        $this->distinct = $distinct;
        $this->cache_time = $cache_time;
    }

    public function query_read($order, $limit, $current_page) {
        $orderby = prepare_orderby($order, SORT_ARRAY_MANGA);
        $limit = prepare_numeric($limit);
        $offset = prepare_numeric($limit * ($current_page - 1));

        $results = $this->sql->prep("mangas_query_{$this->hash}_orderby_{$orderby}_limit_{$limit}_offset_$offset", "
			SELECT $this->distinct mangas.manga_id, manga_image, manga_name, manga_author, manga_hentai, manga_rating, manga_bayesian, manga_rated_users, manga_views, manga_follows, manga_comments, manga_description,
				lang_name, lang_flag, thread_posts, manga_last_uploaded AS manga_last_updated
			FROM mangadex_mangas AS mangas
			$this->left_join_alt_names
			LEFT JOIN mangadex_languages AS lang
				ON lang.lang_id = mangas.manga_lang_id
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = mangas.thread_id
			WHERE $this->search_string 1=1
			ORDER BY $orderby
			LIMIT $limit OFFSET $offset
			", array_merge($this->pdo_bind), 'fetchAll', PDO::FETCH_UNIQUE, -1);

        return get_results_as_object($results, 'manga_id');
    }

    public function getIds($order, $limit, $current_page) {
        $orderby = prepare_orderby($order, SORT_ARRAY_MANGA);
        $limit = prepare_numeric($limit);
        $offset = prepare_numeric($limit * ($current_page - 1));

        $results = $this->sql->prep("manga_ids_query_" . hash_array($this->pdo_bind) . "_orderby_{$orderby}_limit_{$limit}_offset_$offset", "
			SELECT manga_id, manga_last_uploaded AS manga_last_updated
			FROM mangadex_mangas AS mangas
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = mangas.thread_id
			WHERE $this->search_string 1=1
			ORDER BY $orderby
			LIMIT $limit OFFSET $offset
			", array_merge($this->pdo_bind), 'fetchAll', PDO::FETCH_COLUMN, -1);

        return $results;
    }
}

class Manga {
    public function __construct($id) {
        global $sql;
        $this->sql = $sql;

        $id = (int)prepare_numeric($id);

        $row = $sql->prep("manga_$id", "
			SELECT mangadex_mangas.*, lang_name, lang_flag, thread_posts
				FROM mangadex_mangas
				LEFT JOIN mangadex_languages ON mangadex_languages.lang_id = mangadex_mangas.manga_lang_id
				LEFT JOIN mangadex_threads ON mangadex_mangas.thread_id = mangadex_threads.thread_id
				WHERE mangadex_mangas.manga_id = ?
			", [$id], 'fetch', PDO::FETCH_OBJ, 86400);

        //copy $row into $this
        if ($row) {
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function get_comments() {
        $results = $this->sql->prep("manga_{$this->manga_id}_get_comments", "
			SELECT posts.*, users.username, users.avatar, user_levels.level_colour, user_levels.level_id, user_levels.level_name,
				editor.username AS editor_username,
				editor_levels.level_colour AS editor_level_colour,
				(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts
					WHERE mangadex_forum_posts.post_id <= posts.post_id
					AND mangadex_forum_posts.thread_id = posts.thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page
			FROM mangadex_forum_posts AS posts
			LEFT JOIN mangadex_users AS users
				ON posts.user_id = users.user_id
			LEFT JOIN mangadex_user_levels AS user_levels
				ON users.level_id = user_levels.level_id
			LEFT JOIN mangadex_users AS editor
				ON posts.edit_user_id = editor.user_id
			LEFT JOIN mangadex_user_levels AS editor_levels
				ON editor.level_id = editor_levels.level_id
			WHERE posts.thread_id = ? AND posts.deleted = 0
			ORDER BY timestamp DESC
			LIMIT 20
			", [$this->thread_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);

        return get_results_as_object($results, 'post_id');
    }

    public function get_total_chapters($multi_lang) {
        if ($multi_lang) {
            $arr = explode(',', $multi_lang);
            $in = prepare_in($arr);

            return $this->sql->prep("manga_{$this->manga_id}_total_chapters_lang_$multi_lang", "
				SELECT count(*)
				FROM mangadex_chapters
				WHERE manga_id = ? AND chapter_deleted = 0 AND lang_id IN ($in)
				", array_merge([$this->manga_id], $arr), 'fetchColumn', '', 60);
        }
        else
            return $this->sql->prep("manga_{$this->manga_id}_total_chapters", "
				SELECT count(*)
				FROM mangadex_chapters
				WHERE manga_id = ? AND chapter_deleted = 0
				", [$this->manga_id], 'fetchColumn', '', 60);
    }

    public function get_missing_chapters($lang_id) {
        $chapter_array = $this->sql->prep("manga_{$this->manga_id}_missing_chapters_lang_$lang_id", "
			SELECT chapter
			FROM mangadex_chapters
			WHERE manga_id = ? AND lang_id = ? AND chapter_deleted = 0 AND available = 1
			ORDER BY abs(chapter) DESC
			", [$this->manga_id, $lang_id], 'fetchAll', PDO::FETCH_COLUMN, 60);

        if (count($chapter_array) && $chapter_array[0] && $chapter_array[0] < 1500) {
            $diff_array = array_diff(range(1, $chapter_array[0]), $chapter_array);

            $string = "";
            foreach($diff_array as $value) {
                $string .= $value . " ";
            }

            return $string;
        }
    }

    public function get_manga_genres() {
        return $this->sql->prep("manga_{$this->manga_id}_genres", "
			SELECT genre_id
			FROM mangadex_manga_genres
			WHERE manga_id = ?
			", [$this->manga_id], 'fetchAll', PDO::FETCH_COLUMN);
    }

    public function get_manga_alt_names() {
        return $this->sql->prep("manga_{$this->manga_id}_alt_names", "
			SELECT alt_name
			FROM mangadex_manga_alt_names
			WHERE manga_id = ?
			", [$this->manga_id], 'fetchAll', PDO::FETCH_COLUMN);
    }

    public function get_follows_user_id() {
        return $this->sql->prep("manga_{$this->manga_id}_follows_user_id", "
			SELECT user_id, follow_type
			FROM mangadex_follow_user_manga
			WHERE manga_id = ?
			", [$this->manga_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
    }

    public function get_user_follow_info($user_id){
        return $this->sql->prep("manga_{$this->manga_id}_follows_user_{$user_id}",
                        "SELECT follow_type, volume, chapter
                                FROM mangadex_follow_user_manga
                                WHERE user_id = ? AND manga_id = ?",
                                [$user_id, $this->manga_id], 'fetchAll', PDO::FETCH_ASSOC) ?: ["follow_type" => 0, "volume" => 0, "chapter" => 0];
    }

    public function get_user_rating($user_id) {
        return $this->sql->prep("manga_{$this->manga_id}_user_rating_$user_id", "
			SELECT rating
			FROM mangadex_manga_ratings
			WHERE manga_id = ? AND user_id = ?
			", [$this->manga_id, $user_id], 'fetchColumn', '');
    }

    public function get_user_ratings() {
        return $this->sql->prep("manga_{$this->manga_id}_user_ratings", "
			SELECT rating
			FROM mangadex_manga_ratings
			WHERE manga_id = ?
			", [$this->manga_id], 'fetchAll', PDO::FETCH_COLUMN);
    }

    public function get_related_manga() {
        return $this->sql->prep("manga_{$this->manga_id}_related_manga", "
			SELECT relations.relation_id, relations.related_manga_id, mangas.manga_name, mangas.manga_hentai
			FROM mangadex_manga_relations AS relations
			LEFT JOIN mangadex_mangas AS mangas
				ON relations.related_manga_id = mangas.manga_id
			WHERE relations.manga_id = ?
			ORDER BY relations.relation_id ASC, mangas.manga_name ASC
			", [$this->manga_id], 'fetchAll', PDO::FETCH_ASSOC);
    }

    public function get_covers() {
        return $this->sql->prep("manga_{$this->manga_id}_covers", "
			SELECT covers.*,  users.username, user_levels.level_colour, user_levels.level_id
			FROM mangadex_manga_covers AS covers
			LEFT JOIN mangadex_users AS users
				ON covers.user_id = users.user_id
			LEFT JOIN mangadex_user_levels AS user_levels
				ON users.level_id = user_levels.level_id
			WHERE covers.manga_id = ?
			ORDER BY covers.volume + 0 ASC
			", [$this->manga_id], 'fetchAll', PDO::FETCH_ASSOC);
    }
}

class Genres {

    protected $genres = [];

    public function __construct() {
        global $sql;
        $results = $sql->query_read('genres', 'SELECT * FROM mangadex_genres ORDER BY genre_name ASC', 'fetchAll', PDO::FETCH_ASSOC, 600);

        foreach ($results AS $row) {
            $this->genres[$row['genre_id']] = $row['genre_name'];
        }
    }

    public function toArray()
    {
        return $this->genres;
    }
}

class Genre
{
    public function __construct($id)
    {
        global $sql;

        $id = prepare_numeric($id);

        $row = $sql->prep("genre_$id", " SELECT * FROM mangadex_genres WHERE genre_id = ? ", [$id], 'fetch', PDO::FETCH_OBJ, -1);

        //copy $row into $this
        if ($row) {
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}

class Grouped_Genres {

    protected $results = [];

    public function __construct() {
        global $sql;
        $this->results = $sql->query_read('grouped_genres', '
          SELECT *
          FROM mangadex_genres AS genres
          LEFT JOIN mangadex_genre_groups AS _groups
            ON genres.genre_group_id = _groups.genre_group_id
          ORDER BY genres.genre_group_id ASC, genres.genre_name ASC', 'fetchAll', PDO::FETCH_ASSOC);
    }

    public function toGenreArray()
    {
        $genres = [];
        foreach ($this->results AS $row) {
            $genres[$row['genre_id']] = ['id' => $row['genre_id'], 'name' => $row['genre_name'], 'group' => $row['genre_group_name'], 'description' => $row['genre_description']];
        }
        ksort($genres);
        return $genres;
    }

    public function toGroupedArray() {
        $genres = [];
        foreach ($this->results AS $row) {
            if ($row['genre_group_id'] != 0) {
                $genres[$row['genre_group_name']][] = ['id' => $row['genre_id'], 'name' => $row['genre_name'], 'description' => $row['genre_description']];
            }
        }
        ksort($genres);
        return $genres;
    }
}

class Manga_Lists {
    public function __construct() {
        global $sql;
        $this->sql = $sql;
        $results = $sql->query_read('manga_lists', 'SELECT * FROM mangadex_manga_lists', 'fetchAll', PDO::FETCH_UNIQUE);

        foreach ($results as $i => $row) {
            $this->{$i} = new \stdClass();
            foreach ($row as $key => $value) {
                $this->{$i}->$key = $value;
            }
            $this->{$i}->list_id = $i;
        }
    }

    function get_manga_list($id) {
        $id = prepare_numeric($id);
        return $this->sql->prep("manga_list_$id", " SELECT manga_id FROM mangadex_manga_featured WHERE list_id = ? ORDER BY manga_id DESC ", [$id], 'fetchAll', PDO::FETCH_COLUMN);
    }
}

class Follow_Types {
    public function __construct() {
        global $sql;
        $results = $sql->query_read('follow_types', 'SELECT * FROM mangadex_follow_types', 'fetchAll', PDO::FETCH_UNIQUE);

        foreach ($results as $i => $row) {
            $this->{$i} = new \stdClass();
            foreach ($row as $key => $value) {
                $this->{$i}->$key = $value;
            }
            $this->{$i}->type_id = $i;
        }
    }
}

class Relation_Types {
    public function __construct() {
        global $sql;
        $results = $sql->query_read('relation_types', 'SELECT * FROM mangadex_relation_types', 'fetchAll', PDO::FETCH_UNIQUE);

        foreach ($results as $i => $row) {
            $this->{$i} = new \stdClass();
            foreach ($row as $key => $value) {
                $this->{$i}->$key = $value;
            }
            $this->{$i}->relation_id = $i;
        }
    }
}

class Manga_reports {
    public function __construct($age, $limit = 300) {
        global $sql;
        $age_operator = ($age == "new") ? "=" : ">";

        $limit = prepare_numeric($limit);
        //$offset = prepare_numeric($offset);

        $results = $sql->query_read("manga_reports", "
			SELECT reports.*,
				reporter.username AS reported_name,
				actioned.username AS actioned_name,
				reporter_levels.level_colour AS reported_level_colour,
				actioned_levels.level_colour AS actioned_level_colour
			FROM mangadex_reports_manga AS reports
				LEFT JOIN mangadex_users AS reporter ON reports.report_user_id = reporter.user_id
				LEFT JOIN mangadex_user_levels AS reporter_levels ON reporter.level_id = reporter_levels.level_id
				LEFT JOIN mangadex_users AS actioned ON reports.report_mod_user_id = actioned.user_id
				LEFT JOIN mangadex_user_levels AS actioned_levels ON actioned.level_id = actioned_levels.level_id
			WHERE reports.report_mod_user_id $age_operator 0
			ORDER BY reports.report_timestamp DESC
			LIMIT $limit
			", 'fetchAll', PDO::FETCH_UNIQUE, -1);

        foreach ($results as $i => $report) {
            $this->{$i} = new \stdClass();
            foreach ($report as $key => $value) {
                $this->{$i}->$key = $value;
            }
            $this->{$i}->report_id = $i;
        }
    }
}