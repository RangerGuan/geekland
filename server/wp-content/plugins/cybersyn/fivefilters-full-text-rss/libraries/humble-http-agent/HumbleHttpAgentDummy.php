<?php
/**
 * Humble HTTP Agent Dummy
 * 
 * This class is designed to respond to HumbleHttpAgent calls
 * but to return a predefined HTML response rather than
 * actually making HTTP requests.
 * 
 * @version 1.5
 * @date 2014-05-07
 * @author Keyvan Minoukadeh
 * @copyright 2014 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */

class HumbleHttpAgentDummy
{
	public $debug = false;
	public $debugVerbose = false;
	public $rewriteHashbangFragment = true;
	public $maxRedirects = 5;
	public $userAgentMap = array();
	public $rewriteUrls = array();
	public $userAgentDefault;
	public $referer;
	
	protected $body = '';
	protected $headers = "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\n\r\n";
	
	function __construct($body, $headers=null) {
		$this->body = $body;
		if (isset($headers)) $this->headers = $headers;
	}
	
	public function rewriteHashbangFragment($url) {
		return $url;
	}
	
	public function getRedirectURLfromHTML($url, $html) {
		return false;
	}
	
	public function getMetaRefreshURL($url, $html) {
		return false;
	}	
	
	public function getUglyURL($url, $html) {
		return false;
	}
	
	public function removeFragment($url) {
		return $url;
	}
	
	public function rewriteUrls($url) {
		return $url;
	}
	
	public function enableDebug($bool=true) {
		return;
	}
	
	public function minimiseMemoryUse($bool = true) {
		return;
	}
	
	public function setMaxParallelRequests($max) {
		return;
	}
	
	public function validateUrl($url) {
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$test = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
		// deal with bug http://bugs.php.net/51192 (present in PHP 5.2.13 and PHP 5.3.2)
		if ($test === false) {
			$test = filter_var(strtr($url, '-', '_'), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
		}
		if ($test !== false && $test !== null && preg_match('!^https?://!', $url)) {
			return $url;
		} else {
			return false;
		}
	}
	
	public function fetchAll(array $urls) {
		return;
	}
	
	// fetch all URLs without following redirects
	public function fetchAllOnce(array $urls, $isRedirect=false) {
		return;
	}
	
	public function get($url, $remove=false, $gzdecode=true) {
		return array(
			'body' => $this->body,
			'headers' => $this->headers,
			'status_code' => 200,
			'effective_url' => $url
		);
	}
	
	public function parallelSupport() {
		return false;
	}
}