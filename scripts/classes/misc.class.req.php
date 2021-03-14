<?php
/*
class Batoto {
	protected $db = null;

	public function __construct($db) {
		$this->db = $db;
		$results = $this->db->get_results(" SELECT * FROM batoto_comics ORDER BY title ASC ");

		foreach ($results as $i => $genre) {
			$this->{$i} = new \stdClass();
			foreach ($genre as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}
*/

class Languages {
	public function __construct() {
		global $sql;
		$results = $sql->query_read('langs_lang_name_ASC', 'SELECT * FROM mangadex_languages ORDER BY lang_name ASC', 'fetchAll', PDO::FETCH_UNIQUE);

		foreach ($results as $i => $torrent) {
			$this->{$i} = new \stdClass();
			foreach ($torrent as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}

class Language {
	public function __construct($id, $type) {
		$type = prepare_identifier($type);

		global $sql;
		$row = $sql->prep("lang_$id", " SELECT * FROM mangadex_languages WHERE $type = ? ", [$id], 'fetch', PDO::FETCH_OBJ);

		if (!$row)
			$row = $sql->query_read('lang_1', 'SELECT * FROM mangadex_languages WHERE lang_id = 1', 'fetch', PDO::FETCH_OBJ);

		//copy $row into $this
		foreach ($row as $key => $value) {
			$this->$key = $value;
		}
	}

	function get_ui($type) {
		return json_decode($this->$type);
	}
}

class User_levels {
	public function __construct() {
		global $sql;
		$results = $sql->query_read('user_levels', 'SELECT * FROM mangadex_user_levels', 'fetchAll', PDO::FETCH_UNIQUE, -1);

		foreach ($results as $i => $row) {
			$this->{$i} = new \stdClass();
			foreach ($row as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}

class Tags {
    private $tags = [];
    private $groups = [];

    public function __construct() {
        $this->initialize(-1);
    }
    public function initialize($expiry = 86400) {
        global $sql;
        $this->tags = $sql->query_read("tags_all", "SELECT * FROM mangadex_genres", 'fetchAll', PDO::FETCH_UNIQUE, $expiry);
        $this->groups = $sql->query_read("tag_groups_all", "SELECT * FROM mangadex_genre_groups", 'fetchAll', PDO::FETCH_UNIQUE, $expiry);
    }
    public function flushCache() {
        $this->initialize(-1);
    }
    public function getTags() {
        return $this->tags;
    }
    public function getTagById($id) {
        return $this->tags[$id] ?? null;
    }
    public function getGroups() {
        return $this->groups;
    }
    public function getGroupById($id) {
        return $this->groups[$id] ?? null;
    }
}

class Visit_logs {
	public function __construct($table = "visits", $limit = 100) {
		global $sql;

		$table = prepare_identifier("mangadex_logs_$table");

		$limit = prepare_numeric($limit);

		$results = $sql->query_read('visit_logs', "
			SELECT $table.*, mangadex_users.username 
			FROM $table, mangadex_users 
			WHERE $table.visit_user_id = mangadex_users.user_id 
			ORDER BY visit_timestamp DESC 
			LIMIT $limit 
			", 'fetchAll', PDO::FETCH_UNIQUE, -1);

		foreach ($results as $i => $row) {
			$this->{$i} = new \stdClass();
			foreach ($row as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}

class Action_logs {
	public function __construct($limit = 100) {
		$limit = prepare_numeric($limit);

		$results = $sql->query_read('action_logs', "
			SELECT mangadex_logs_actions.*, mangadex_users.username 
			FROM mangadex_logs_actions, mangadex_users 
			WHERE mangadex_logs_actions.action_user_id = mangadex_users.user_id 
			ORDER BY action_timestamp DESC 
			LIMIT $limit
			", 'fetchAll', PDO::FETCH_UNIQUE, -1);

		foreach ($results as $i => $row) {
			$this->{$i} = new \stdClass();
			foreach ($row as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}

class Report_Reasons {

    private $reasons = [];

    public function __construct()
    {
        global $sql;

        $this->reasons = $sql->prep('report_reasons', "SELECT * FROM mangadex_report_reason ORDER BY sortorder ASC", [], 'fetchAll', PDO::FETCH_ASSOC, 3600);
    }

    public function toArray()
    {
        $a = [];
        foreach ($this->reasons AS $reason)
            $a[$reason['id']] = $reason;
        return $a;
    }

}

class Clients {
	public function __construct() {
		global $sql;
		$results = $sql->query_read('clients', '
			SELECT clients.*, users.username, levels.level_colour 
				FROM mangadex_clients AS clients
				LEFT JOIN mangadex_users AS users ON clients.user_id = users.user_id
				LEFT JOIN mangadex_user_levels AS levels ON users.level_id = levels.level_id
			', 'fetchAll', PDO::FETCH_UNIQUE, -1);

		foreach ($results as $i => $torrent) {
			$this->{$i} = new \stdClass();
			foreach ($torrent as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}