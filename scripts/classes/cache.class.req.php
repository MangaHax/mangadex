<?php

class Synced_Memcached extends Memcached
{

    private $memcachedSync = null;

    public function __construct($persistent_id = '', $on_new_object_cb = null, $connection_str = '')
    {
        parent::__construct($persistent_id, $on_new_object_cb, $connection_str);

        if (defined('MEMCACHED_SYNC_HOST') && !empty(MEMCACHED_SYNC_HOST)) {
            $this->memcachedSync = new Memcached('sync_host');
            if (!$this->memcachedSync->getServerList()) {
                // Persistent servers remember the serverlist, so only add if its empty after a php-fpm restart
                $this->memcachedSync->addServer(MEMCACHED_SYNC_HOST, defined('MEMCACHED_SYNC_PORT') ? MEMCACHED_SYNC_PORT : 11211);
            }
        }
    }

    public function setSynced($key, $value, $expiration = 0, $udf_flags = 0)
    {
        parent::set($key, $value, $expiration);
        if ($this->memcachedSync !== null) {
            $this->memcachedSync->set($key, $value, $expiration);
        }
    }

    public function deleteSynced($key, $time = 0)
    {
        parent::delete($key, $time);
        if ($this->memcachedSync !== null) {
            $this->memcachedSync->delete($key, $time);
        }
    }
}

class Cache extends Synced_Memcached
{

    const RESULT_CODES = [
        Memcached::RES_SUCCESS => "SUCCESS",
        Memcached::RES_FAILURE => "FAILURE",
        2 => "HOST_LOOKUP_FAILURE", // getaddrinfo() and getnameinfo() only
        3 => "CONNECTION_FAILURE",
        4 => "CONNECTION_BIND_FAILURE", // DEPRECATED see MEMCACHED_HOST_LOOKUP_FAILURE
        5 => "WRITE_FAILURE",
        6 => "READ_FAILURE",
        7 => "UNKNOWN_READ_FAILURE",
        8 => "PROTOCOL_ERROR",
        9 => "CLIENT_ERROR",
        10 => "SERVER_ERROR", // Server returns SERVER_ERROR
        11 => "ERROR (?)", // Server returns
        12 => "DATA_EXISTS",
        13 => "DATA_DOES_NOT_EXIST",
        14 => "NOTSTORED",
        15 => "STORED",
        16 => "NOTFOUND",
        17 => "MEMORY_ALLOCATION_FAILURE",
        18 => "PARTIAL_READ",
        19 => "SOME_ERRORS",
        20 => "NO_SERVERS",
        21 => "END",
        22 => "DELETED",
        23 => "VALUE",
        24 => "STAT",
        25 => "ITEM",
        26 => "ERRNO",
        27 => "FAIL_UNIX_SOCKET", // DEPRECATED,
        28 => "NOT_SUPPORTED",
        29 => "NO_KEY_PROVIDED", /* Deprecated. Use MEMCACHED_BAD_KEY_PROVIDED! */
        30 => "FETCH_NOTFINISHED",
        31 => "TIMEOUT",
        32 => "BUFFERED",
        33 => "BAD_KEY_PROVIDED",
        34 => "INVALID_HOST_PROTOCOL",
        35 => "SERVER_MARKED_DEAD",
        36 => "UNKNOWN_STAT_KEY",
        37 => "E2BIG",
        38 => "INVALID_ARGUMENTS",
        39 => "KEY_TOO_BIG",
        40 => "AUTH_PROBLEM",
        41 => "AUTH_FAILURE",
        42 => "AUTH_CONTINUE",
        43 => "PARSE_ERROR",
        44 => "PARSE_USER_ERROR",
        45 => "DEPRECATED",
        46 => "IN_PROGRESS",
        47 => "SERVER_TEMPORARILY_DISABLED",
        48 => "SERVER_MEMORY_ALLOCATION_FAILURE",
        49 => "MAXIMUM_RETURN", /* Always add new error code before */
    ];

    protected $stats = [];

    protected $log = [];

    protected $stopwatch;

    public function __construct(string $persistent_id = '', $on_new_object_cb = null)
    {
        parent::__construct($persistent_id, $on_new_object_cb);
        $this->stats = [
            'hit' => 0,
            'miss' => 0,
            'set' => 0,
            'delete' => 0,
        ];
        $this->stopwatch = microtime(1);
    }

    public function get($key, $cache_cb = null, $flags = null)
    {
        $res = parent::get($key, $cache_cb, $flags);
        $this->stats[$res === false ? 'miss' : 'hit']++;
        $this->log[] = [
            'method' => "GET",
            'time' => substr(microtime(1)-$this->stopwatch,0,6),
            'key' => $key,
            'result' => $this->getResultCode(),
            'call_stack' => $this->formatCallStack(debug_backtrace()),
        ];
        return $res;
    }

    public function set($key, $value, $expiration = 0, $udf_flags = 0)
    {
        parent::set($key, $value, $expiration);
        $this->stats['set']++;
        $this->log[] = [
            'method' => "SET",
            'time' => substr(microtime(1)-$this->stopwatch,0,6),
            'key' => $key,
            'result' => $this->getResultCode(),
            'call_stack' => $this->formatCallStack(debug_backtrace()),
        ];
    }

    public function delete($key, $time = 0)
    {
        parent::delete($key, $time);
        $this->stats['delete']++;
        $this->log[] = [
            'method' => "DEL",
            'time' => substr(microtime(1)-$this->stopwatch,0,6),
            'key' => $key,
            'result' => $this->getResultCode(),
            'call_stack' => $this->formatCallStack(debug_backtrace()),
        ];
    }

    public function toArray()
    {
        $elapsed = microtime(1) - $this->stopwatch;
        return [
            //'memcached' => $this->getStats(),
            'stats' => $this->stats,
            'log' => $this->log,
            'time' => $elapsed
        ];
    }

    public function getResultString($code = null)
    {
        if ($code === null)
            $code = $this->getResultCode();

        return isset(self::RESULT_CODES[$code]) ? self::RESULT_CODES[$code] : $code;
    }

    private function formatCallStack($stack)
    {
        $call_stack = [];
        foreach ($stack AS $t) {
            $call_stack[] = str_replace(ABSPATH, '', $t['file'] ?? '(no file)')."(".($t['line'] ?? 0).")";
        }
        return $call_stack;
    }

}