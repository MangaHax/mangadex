<?php

// Sentry error handling (must be as early as possible to catch any init exceptions=
if (defined('SENTRY_DSN') && SENTRY_DSN && class_exists('Raven_Client')) {
	$sentry = new Raven_Client(SENTRY_DSN, [
		'sample_rate' => SENTRY_SAMPLE_RATE,
		'curl_method' => SENTRY_CURL_METHOD,
		'timeout' => SENTRY_TIMEOUT
	]);
	try {
		$sentry->install();
	} catch (\Raven_Exception $e) {
		// This should land in the logfiles at least but not block script execution
		trigger_error('Failed to install Sentry client: '.$e->getMessage(), E_USER_WARNING);
	}
}

//database stuff
$host = DB_HOST;
$db   = DB_NAME;
$charset = 'utf8mb4';

$dsn_master = "mysql:host=$host;dbname=$db;charset=$charset";
$dsn_slaves = [];

foreach (DB_READ_HOSTS ?? [] AS $slave_host) {
	$slave_db = DB_READ_NAME;
	$slave_port = 3306;
	if (strpos($slave_host, ':') !== false) {
		[$slave_host, $slave_port] = explode(':', $slave_host, 2);
	}
	$dsn_slaves[] = "mysql:host=$slave_host;port=$slave_port;dbname=$slave_db;charset=$charset";
}

$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

class SQL extends PDO {
	private $debug = [];
	private $time_array = [];

	/** @var \PDO */
	private $slave_sql;

	public function __construct(string $dsn_master, array $dsn_slaves, $username = null, $passwd = null, $options = null)
	{
		// Establish connection with master
		parent::__construct($dsn_master, $username, $passwd, $options);

		// Randomize pick order
		shuffle($dsn_slaves);

		while (!empty($dsn_slaves)) {
			$dsn_slave = array_pop($dsn_slaves);
			$error = 'Slave failed with unknown reason';
			try {
				$this->slave_sql = new \PDO($dsn_slave, DB_READ_USER, DB_READ_PASSWORD, $options);
				// Try a ping
				if (false === $this->slave_sql->query('SELECT 1')) {
					throw new \RuntimeException('Ping on slave failed!');
				}
				// Connection successful
				return;
			} catch (\PDOException $e) {
				$error = sprintf('Slave failed with error code %s', $e->getCode());
			} catch (\Throwable $e) {
				$error = sprintf('Unexpected exception: %s', $e->getMessage());
			}
			// A slave failed, report warning to sentry
			trigger_error($error, E_USER_WARNING);
		}
		// Fall back to master
		$this->slave_sql = $this;
	}

	public function query_read($name, $query, $fetch, $pdo_mode, $expiry = 0) {
		global $memcached;
		
		$name = str_replace(' ', '_', $name);
		
		if ($expiry < 0) //delete from cache and update
			$memcached->delete($name);
		
		$start = microtime(true);
		
		$cache = $memcached->get($name);
		$from_cache = 'Y';
		
		if ($cache === FALSE) {
			if ($fetch == 'fetchAll')
				$cache = $this->slave_sql->query($query)->fetchAll($pdo_mode);
			elseif ($fetch == 'fetchColumn')
				$cache = $this->slave_sql->query($query)->fetchColumn();
			else 
				$cache = $this->slave_sql->query($query)->fetch($pdo_mode);

            if ($expiry >= 0)
                $memcached->set($name, $cache, $expiry);
			$from_cache = 'N';
		}
		
		$time_taken = round((microtime(true) - $start) * 1000, 2);
		
		$this->time_array[] = $time_taken;
		$this->debug[] = [$name, nl2br(trim($query)), $fetch, $from_cache, $time_taken];
		
		return $cache;
	}

	public function prep($name, $query, $bind, $fetch, $pdo_mode = '', $expiry = 0) {
		global $memcached;
		
		$name = str_replace(' ', '_', $name);
		
		if ($expiry < 0) //delete from cache and update
			$memcached->delete($name);
		
		$start = microtime(true);
		
		$cache = $memcached->get($name);
		$from_cache = 'Y';
		
		if ($cache === FALSE) {
			$stmt = $this->slave_sql->prepare($query);
			$stmt->execute($bind);
			
			switch ($fetch) {
				case 'fetch':
					$cache = $stmt->fetch($pdo_mode);
					break;
				
				case 'fetchColumn':
					$cache = $stmt->fetchColumn();
					break;
					
				default:
					$cache = $stmt->fetchAll($pdo_mode);
					break;
			}

			if ($expiry >= 0)
			    $memcached->set($name, $cache, $expiry);
			$from_cache = 'N';
		}
		
		$time_taken = round((microtime(true) - $start) * 1000, 2);
		
		$this->time_array[] = $time_taken;
		
		$query = preg_replace(array_fill(0, count($bind), '/\?/'), array_fill(0, count($bind), "<span style='color: red'>~</span>"), $query, 1);
		$query = preg_replace(array_fill(0, count($bind), '/~/'), $bind, $query, 1);
		$this->debug[] = [$name, nl2br(trim($query)), $fetch, $from_cache, $time_taken];
		
		return $cache;
	}	
	
	public function modify($name, $query, $bind) {
		$name = str_replace(' ', '_', $name);
		
		$start = microtime(true);
		
		$from_cache = '/';
		
		$stmt = $this->prepare($query);
		$stmt->execute($bind);
			
		$time_taken = round((microtime(true) - $start) * 1000, 2);
		
		$this->time_array[] = $time_taken;
		
		$query = preg_replace(array_fill(0, count($bind), '/\?/'), array_fill(0, count($bind), "<span style='color: red'>~</span>"), $query, 1);
		$query = preg_replace(array_fill(0, count($bind), '/~/'), $bind, $query, 1);
		$this->debug[] = [$name, nl2br(trim($query)), 'modify', $from_cache, $time_taken];

		return $this->lastInsertId();
	}

	public function modify_deferred($name, $query, $bind) {

    }
	
	public function debug() {
	    global $memcached;

	    // ======== Add sql table
		$return = "
			<table style='margin-top: 50px;' class='table table-condensed table-striped'>
				<tr>
					<th><button id='toggle-sql-table'><i class='fa fa-eye'></i></button></th>
					<th>N</th>
					<th>Q</th>
					<th>M</th>
					<th>C</th>
					<th>T</th>
				</tr>";
		
		foreach ($this->debug as $key => $array) {
			++$key;
			$return .= "<tr style='display:none'>";
			$return .= "<td>$key</td>";
			foreach ($array as $value) {
				$return .= "<td>$value</td>";
			}
			$return .= "</tr>";
		}
		
		$total_time = array_sum($this->time_array);
		
		$return .= "
				<tr style='display:none'>
					<th>#</th>
					<th>Name</th>
					<th>Query</th>
					<th>Mode</th>
					<th>Cache</th>
					<th>$total_time</th>
				</tr>
			</table>";

		if (defined('CAPTURE_CACHE_STATS') && CAPTURE_CACHE_STATS) {
            // ======== Add cache table

            $cacheDebug = $memcached->toArray();

            $return .= "
			<table style='margin-top: 50px;' class='table table-condensed table-striped'>
				<tr>
					<th><button id='toggle-cache-table'><i class='fa fa-eye'></i></button></th>
					<th>Method</th>
					<th>Time (s)</th>
					<th>Key</th>
					<th>Result</th>
					<th>Call Stack</th>
				</tr>";

            $return .= "
                <tr style='display:none'>
                    <td colspan='6'>Hits: {$cacheDebug['stats']['hit']}, Misses: {$cacheDebug['stats']['miss']}, Sets: {$cacheDebug['stats']['set']}, Deletes: {$cacheDebug['stats']['delete']}</td>
                </tr>";

            foreach ($cacheDebug['log'] as $key => $cacheLogRow) {
                $return .= "
                <tr style='display:none'>
                    <td>$key</td>
                    <td>$cacheLogRow[method]</td>
                    <td>$cacheLogRow[time]</td>
                    <td>$cacheLogRow[key]</td>
                    <td>".$memcached->getResultString($cacheLogRow['result'])."</td>
                    <td><ul><li>".implode("</li><li>",$cacheLogRow['call_stack'])."</li></ul></td>
                </tr>";
            }

            $total_time = array_sum($this->time_array);

            $return .= "
				<tr style='display:none'>
					<th>#</th>
					<th>Method</th>
					<th>Time (s)</th>
					<th>Key</th>
					<th>Result</th>
					<th>$cacheDebug[time]</th>
				</tr>
			</table>";
        }

		$return .= "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#toggle-sql-table').click(function(ev){
            console.log($(this).parent().parent().parent().find('tr'));
            $(this).parent().parent().parent().find('tr').each(function(i,e){
                if (i == 0) return;
                $(e).toggle();
            });
        });
        $('#toggle-cache-table').click(function(ev){
            console.log($(this).parent().parent().parent().find('tr'));
            $(this).parent().parent().parent().find('tr').each(function(i,e){
                if (i == 0) return;
                $(e).toggle();
            });
        });
    });
</script>";
		
		return $return;
	}
}

try {
    $sql = new SQL($dsn_master, $dsn_slaves, DB_USER, DB_PASSWORD, $opt);
} catch (\PDOException $e) {
    print file_get_contents(ABSPATH . '/dberror.html');
    // Send to sentry
    trigger_error('DB is down: '.$e->getMessage(), E_USER_ERROR);
    die();
}

//cache
require_once ABSPATH . '/scripts/classes/cache.class.req.php';
if (defined('CAPTURE_CACHE_STATS') && CAPTURE_CACHE_STATS) {
    $memcached = new Cache();
} else {
    $memcached = new Memcached();
}
$memcached->addServer(MEMCACHED_HOST, 11211);

//including files
require_once (ABSPATH . '/scripts/functions.req.php');

foreach (read_dir('scripts/classes') as $file) {
    $file = str_replace(['..', '/', '\\', '`', '´', '"', "'"], '', $file);
	require_once (ABSPATH . "/scripts/classes/$file");
} //require every file in classes

require_once (ABSPATH . '/scripts/display.req.php');

//set vars
$ip = substr($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'cli', 0, 45);
if (\strpos($ip, ',') !== false) {
    $_tmp = explode(',', $ip);
    $ip = reset($_tmp);
    unset($_tmp);
}
define('_IP', $ip);

$ipService = new \Mangadex\Model\IpLocator();
$mdAtHomeClient = new Mangadex\Model\MdexAtHomeClient();
$hentai_toggle = max(0,min(2, $_COOKIE['mangadex_h_toggle'] ?? 0));
$theme_cookie = (int)($_COOKIE['mangadex_theme'] ?? 1);
$display_lang_cookie = (int)($_COOKIE['mangadex_display_lang'] ?? 0);
$filter_langs_cookie = $_COOKIE['mangadex_filter_langs'] ?? '';
$title_mode_cookie = (int)($_COOKIE['mangadex_title_mode'] ?? 0);
$_GET['page'] = $_GET['page'] ?? '';
$timestamp = time();
//require other stuff

require_once (ABSPATH . '/scripts/JBBCode/Parser.php');
$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());

