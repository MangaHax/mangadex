<?php
/**
 * Created by PhpStorm.
 * User: Radon
 * Date: 27.01.2019
 * Time: 15:11
 */

namespace Mangadex\Model;


use Ramsey\Uuid\Uuid;
use WhichBrowser\Parser;

class Guard
{

	/** @var \Memcached */
	private $memcached;

	/** @var \SQL */
	private $sql;

	/** @var array Caches any user that has been retrieved from the database */
	private $_userCache = [];

	/** @var \User? The currently authenticated user */
	private $user = null;

	/** @var Parser */
	private $uaParser = null;

	private static $instance;

	/**
	 * Singleton
	 * @return Guard
	 */
	public static function getInstance()
    {
		global $memcached, $sql;

		if (self::$instance === null) {
			self::$instance = new self($memcached, $sql);
		}

		return self::$instance;
	}

	private function __construct($memcached, $sql)
	{
		$this->memcached = $memcached;
		$this->sql = $sql;
	}

	// Handle User session

	/**
	 * Tries to detect if a valid session exist. if not, tries to create a new session of rememberme is valid
	 * @param $sessionId
	 * @param null $rememberMeToken
	 * @throws \Exception
	 */
	public function tryRestoreSession($sessionId = null, $rememberMeToken = null)
	{
		$userId = 0;
		$sessionInfo = isset($sessionId) ? $this->memcached->get('session:'.$sessionId) : null; // sessions that havent been refreshed within one hour, are auto-expired and wont be returned by memcached

		// User agent must match, this allows for different sessions on different devices
		if (is_array($sessionInfo) && $sessionInfo['useragent'] === ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown')/* && _IP === $sessionInfo['ip']*/) {
			$userId = (int)$sessionInfo['userid'];
		}
		// Check if we can regenerate a session if rememberme is set. (rememberme must have been set at login time, there must be a rememberme-token, it must match with the user db row and must be non-expired
		else if (isset($rememberMeToken)) {
			$check = $this->sql->prep('user_rememberme_check', 'SELECT user_id, region_data FROM mangadex_sessions WHERE session_token = ? AND created > UNIX_TIMESTAMP() - ?',
				[$rememberMeToken, SESSION_REMEMBERME_TIMEOUT], 'fetch', \PDO::FETCH_ASSOC, -1);

			$tokenRegionData = json_decode($check['region_data'], 1);
			$userRegionData = $this->getClientDetails();

			$isMetaDataValid
				= $tokenRegionData['os'] === $userRegionData['os']
				&& $tokenRegionData['device'] === $userRegionData['device']
				&& $tokenRegionData['browser'] === $userRegionData['browser'];

			if ($check && isset($check['user_id']) && $check['user_id'] > 0 && $isMetaDataValid) {
				// The user provided rememberme-token is valid. restore a session to skip userlogin process
				$this->createSession($check['user_id'], true);
				$userId = (int)$check['user_id'];
				$this->user = $this->getUser($userId);
				return;
			}
		}

		if ($userId > 0) {
			$this->updateSession($sessionId);
			$this->user = $this->getUser($userId);
		}
		else if ($sessionId) {
			// Provided session id but no session was found? Scrub the cookie
			setcookie(SESSION_COOKIE_NAME, null, time() - 3600, SESSION_COOKIE_PATH, SESSION_COOKIE_DOMAIN);
			$this->user = null;
		}
	}

	/**
	 * Destroys both session and rememberme.
	 * Should only be called from explicit logout
	 * @param $sessionId
	 */
	public function destroySession($sessionId = null)
	{
		$sessionId = $sessionId ?? $_COOKIE[SESSION_COOKIE_NAME] ?? false;

		if (!$sessionId)
			return;

		$this->memcached->delete('session:'.$sessionId);

		if (isset($_COOKIE[SESSION_REMEMBERME_COOKIE_NAME])) {
			try {
				$this->sql->modify('delete_rememberme_session', 'DELETE FROM mangadex_sessions WHERE user_id = ? AND session_token = ?',
					[$this->getUser()->user_id, $_COOKIE[SESSION_REMEMBERME_COOKIE_NAME]]);
			} catch (\Exception $e) {
				trigger_error('Failed to delete rememberme session: '.$e->getMessage(), E_USER_WARNING);
			}
		}

		setcookie(SESSION_COOKIE_NAME, null, time() - 3600, SESSION_COOKIE_PATH, SESSION_COOKIE_DOMAIN);
		setcookie(SESSION_REMEMBERME_COOKIE_NAME, null, time() - 3600, SESSION_COOKIE_PATH, SESSION_COOKIE_DOMAIN);
	}

	/**
	 * Updates an existing session
	 * @param $sessionId
	 */
	public function updateSession($sessionId)
	{
		$sessionInfo = $this->memcached->get('session:'.$sessionId);
		if (is_array($sessionInfo)) {
            $sessionInfo['hits']++;
			$sessionInfo['updated'] = time();
			$sessionInfo['ip'] = _IP;

			$this->memcached->set('session:'.$sessionId, $sessionInfo, time() + SESSION_TIMEOUT);
			setcookie(SESSION_COOKIE_NAME,
				$sessionId,
				time() + SESSION_TIMEOUT,
				SESSION_COOKIE_PATH,
				SESSION_COOKIE_DOMAIN);

			// Update lastseen
            //if ($this->getUser($sessionInfo['userid'])->last_seen_timestamp + 60 < time()) {
            if ($this->memcached->get("user_{$sessionInfo['userid']}_lastseen") === FALSE) {
                $this->sql->modify(
                    'user_lastseen',
                    'UPDATE mangadex_users SET last_seen_timestamp = ?, last_ip = ? WHERE user_id = ?',
                    [
                        time(),
                        _IP,
                        $sessionInfo['userid']
                    ]
                );
				$this->sql->modify(
                    'user_country',
                    'UPDATE mangadex_user_stats SET country = ? WHERE user_id = ?',
                    [
                        get_country_code(_IP),
                        $sessionInfo['userid']
                    ]
                );
				$this->memcached->set("user_{$sessionInfo['userid']}_lastseen", 1, 60);
            }
		} else {
			// No session found? It could've been kicked out of memcached. Lets destroy the session cookie
			setcookie(SESSION_COOKIE_NAME, null, time() - 3600, SESSION_COOKIE_PATH, SESSION_COOKIE_DOMAIN);
		}
	}

	/**
	 * Creates new session record in memory and sends the cookie to the user
	 * @param $userId
	 * @param bool $isRemembermeSession
	 * @return string the new session key
	 * @throws \Exception
	 */
	public function createSession($userId, $isRemembermeSession = false)
	{
		$sessionId = Uuid::uuid4()->toString();
		$sessionInfo = [
			'ip' => _IP,
			'created' => time(),
			'updated' => time(),
			'useragent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
			'hits' => 0,
			'userid' => (int)$userId,
			'is_rememberme' => $isRemembermeSession,
		];
		$this->memcached->set('session:'.$sessionId, $sessionInfo, time() + SESSION_TIMEOUT);
		setcookie(SESSION_COOKIE_NAME,
			$sessionId,
			time() + SESSION_TIMEOUT,
			SESSION_COOKIE_PATH,
			SESSION_COOKIE_DOMAIN);
		return $sessionId;
	}

	/**
	 * Creates a new rememberme token in the user record and sends the token cookie to the user
	 * @param int $userId
	 * @throws \Exception
	 */
	public function createRemembermeToken(int $userId)
	{
		try {
			$token = hash('sha256', $userId.time().random_bytes(128));
		} catch (\Exception $e) {
			// Fallback to a weaker hash algo
			$token = sha1($userId.time().random_bytes(128));
		}

		/*
		$this->sql->modify('user_create_rememberme', 'UPDATE mangadex_users SET token = ?, last_login_timestamp = UNIX_TIMESTAMP() WHERE user_id = ?',
			[$token, $userId]);
		*/
		try {
			$clientDetails = $this->getClientDetails();
			$clientDetailsJson = json_encode($clientDetails);

			$this->sql->modify('user_create_rememberme', 'INSERT INTO mangadex_sessions (session_token, user_id, created, region_data) VALUES (?,?,?,?)',
				[$token, $userId, time(), $clientDetailsJson]);

			setcookie(SESSION_REMEMBERME_COOKIE_NAME,
				$token,
				time() + SESSION_REMEMBERME_TIMEOUT,
				SESSION_COOKIE_PATH,
				SESSION_COOKIE_DOMAIN);
		} catch (\Exception $e) {
			trigger_error('Failed to create rememberme token: ' .$e->getMessage(), E_USER_ERROR);
		}
	}

	/**
	 * Check of a given password match with the user records password
	 * @param $userId
	 * @param $rawPassword
	 * @return bool
	 */
	public function verifyUserCredentials($userId, $rawPassword)
	{
		$user = $this->getUser($userId);
		return password_verify($rawPassword, $user->password);
	}

	/**
	 * Authenticates username and password
	 * @param $username
	 * @param $rawPassword
	 * @return int the user_id
	 * @throws \Exception when login was unsuccessful
	 */
	public function authenticate($username, $rawPassword) : int
	{
		$check = $this->sql->prep('user_check_'.md5($username), 'SELECT user_id, `password` FROM mangadex_users WHERE username = ?',
			[$username], 'fetch', \PDO::FETCH_ASSOC, -1);

		if ($check && isset($check['password']) && password_verify($rawPassword, $check['password'])) {
			return $check['user_id'];
		} else {
			throw new \RuntimeException('Authentication failed. Username or password don\'t match with any user record.');
		}
	}

	public function passwordHash($rawPassword)
	{
		return password_hash($rawPassword, PASSWORD_DEFAULT);
	}

	/**
	 * Retrieve a cached user object from the database. If no user_id is specified, returns the current session user
	 * @param int $user_id
	 * @return \User?
	 */
	public function getUser(int $user_id = -1)
	{
		// -1 is the current user
		if ($user_id < 0 && $this->hasUser()) {
			return $this->user;
		}

		if (!isset($this->_userCache[$user_id])) {
			//$this->_userCache[$user_id] = $this->sql->prep('user_'.$user_id, 'SELECT * FROM mangadex_users WHERE user_id = ?', [$user_id], 'fetch', \PDO::FETCH_ASSOC, 3600);
			$u = new \User($user_id, 'user_id');
			if ($u !== null && isset($u->user_id) && $u->user_id === $user_id) // Make sure we get the user we actually requested. Counteracts the fallback-to-guest-if-no-user-found from the User class
				$this->_userCache[$user_id] = $u;
			else return null;
		}

		return $this->_userCache[$user_id];
	}

	/**
	 * Checks if we have an authenticated user session (true) or a guest session (false)
	 * @return bool
	 */
	public function hasUser()
	{
		return $this->user !== null;
	}

	public function getClientDetails()
	{
		try {
			$ipLocator = new IpLocator();
			$client = $this->getUseragent();
			return [
				'country_code' => $ipLocator->getCountryCodeFromIp(_IP),
				'country_name' => $this->sanitizeNames($ipLocator->getCountryFromIp(_IP)),
				'city' => $this->sanitizeNames($ipLocator->getCityFromIp(_IP) ?? ''),
				'browser' => $this->sanitizeNames($client->browser->name ?? 'Unknown'),
				'os' => $client->os->getName(),
				'device' => $this->sanitizeNames($client->device->toString()),
			];
		} catch (\Exception $e) {
			trigger_error("Failed to create region client details: " . $e->getMessage());
		}
		return [
			'country_code' => '',
			'country_name' => 'Unknown',
			'city' => '',
			'browser' => 'Unknown',
			'os' => 'Unknown',
			'device' => ''
		];
	}

	public function getUseragent() : Parser
	{
		if (!isset($this->uaParser)) {
			$this->uaParser = new Parser($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
		}
		return $this->uaParser;
	}

	public function sanitizeNames($str)
	{
		return preg_replace('#[^a-zA-Z0-9\.\-_ ]#u', '', $str);
	}

}