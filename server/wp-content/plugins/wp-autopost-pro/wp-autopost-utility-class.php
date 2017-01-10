<?php
if ((ini_get('safe_mode') == 0 || ini_get('safe_mode') == null) && ini_get('open_basedir') == '') {
	define('CAN_FOLLOWLOCATION', 1);
} else {
	define('CAN_FOLLOWLOCATION', 0);
} 
class autopostFlickr {
	var $api_key;
	var $secret;
	var $rest_endpoint = 'https://api.flickr.com/services/rest/';
	var $upload_endpoint = 'https://up.flickr.com/services/upload/';
	var $replace_endpoint = 'https://up.flickr.com/services/replace/';
	var $oauthrequest_endpoint = 'https://www.flickr.com/services/oauth/request_token/';
	var $oauthauthorize_endpoint = 'https://www.flickr.com/services/oauth/authorize/';
	var $oauthaccesstoken_endpoint = 'https://www.flickr.com/services/oauth/access_token/';
	var $req;
	var $response;
	var $parsed_response;
	var $last_request = null;
	var $die_on_error;
	var $error_code;
	Var $error_msg;
	var $oauth_token;
	var $oauth_secret;
	var $php_version;
	var $custom_post = null;
	function autopostFlickr ($api_key, $secret = null, $die_on_error = false) {
		$this -> api_key = $api_key;
		$this -> secret = $secret;
		$this -> die_on_error = $die_on_error;
		$this -> service = "flickr";
		$this -> php_version = explode("-", phpversion());
		$this -> php_version = explode(".", $this -> php_version[0]);
	} 
	function setCustomPost ($function) {
		$this -> custom_post = $function;
	} 
	function post ($data, $url = '') {
		if ($url == '') $url = $this -> rest_endpoint;
		if (!preg_match("|https://(.*?)(/.*)|", $url, $matches)) {
			die('There was some problem figuring out your endpoint');
		} 
		if (function_exists('curl_init')) {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			curl_close($curl);
		} else {
			foreach ($data as $key => $value) {
				$data[$key] = $key . '=' . urlencode($value);
			} 
			$data = implode('&', $data);
			$fp = @pfsockopen($matches[1], 80);
			if (!$fp) {
				die('Could not connect to the web service');
			} 
			fputs ($fp, 'POST ' . $matches[2] . " HTTP/1.1\n");
			fputs ($fp, 'Host: ' . $matches[1] . "\n");
			fputs ($fp, "Content-type: application/x-www-form-urlencoded\n");
			fputs ($fp, "Content-length: " . strlen($data) . "\n");
			fputs ($fp, "Connection: close\r\n\r\n");
			fputs ($fp, $data . "\n\n");
			$response = "";
			while (!feof($fp)) {
				$response .= fgets($fp, 1024);
			} 
			fclose ($fp);
			$chunked = false;
			$http_status = trim(substr($response, 0, strpos($response, "\n")));
			if ($http_status != 'HTTP/1.1 200 OK') {
				die('The web service endpoint returned a "' . $http_status . '" response');
			} 
			if (strpos($response, 'Transfer-Encoding: chunked') !== false) {
				$temp = trim(strstr($response, "\r\n\r\n"));
				$response = '';
				$length = trim(substr($temp, 0, strpos($temp, "\r")));
				while (trim($temp) != "0" && ($length = trim(substr($temp, 0, strpos($temp, "\r")))) != "0") {
					$response .= trim(substr($temp, strlen($length) + 2, hexdec($length)));
					$temp = trim(substr($temp, strlen($length) + 2 + hexdec($length)));
				} 
			} elseif (strpos($response, 'HTTP/1.1 200 OK') !== false) {
				$response = trim(strstr($response, "\r\n\r\n"));
			} 
		} 
		return $response;
	} 
	function request ($command, $args = array()) {
		if (substr($command, 0, 7) != "flickr.") {
			$command = "flickr." . $command;
		} 
		$args = array_merge(array("method" => $command, "format" => "php_serial", "api_key" => $this -> api_key), $args);
		ksort($args);
		$auth_sig = "";
		$this -> last_request = $args;
		foreach ($args as $key => $data) {
			if (is_null($data)) {
				unset($args[$key]);
				continue;
			} 
			$auth_sig .= $key . $data;
		} 
		if (!empty($this -> secret)) {
			$api_sig = md5($this -> secret . $auth_sig);
			$args['api_sig'] = $api_sig;
		} 
		if (!$args = $this -> getArgOauth($this -> rest_endpoint, $args)) return false;
		$this -> response = $this -> post($args);
		$this -> parsed_response = $this -> clean_text_nodes(unserialize($this -> response));
		if ($this -> parsed_response['stat'] == 'fail') {
			if ($this -> die_on_error) die("The Flickr API returned the following error: #{$this->parsed_response['code']} - {$this->parsed_response['message']}");
			else {
				$this -> error_code = $this -> parsed_response['code'];
				$this -> error_msg = $this -> parsed_response['message'];
				$this -> parsed_response = false;
			} 
		} else {
			$this -> error_code = false;
			$this -> error_msg = false;
		} 
		return $this -> response;
	} 
	function clean_text_nodes ($arr) {
		if (!is_array($arr)) {
			return $arr;
		} elseif (count($arr) == 0) {
			return $arr;
		} elseif (count($arr) == 1 && array_key_exists('_content', $arr)) {
			return $arr['_content'];
		} else {
			foreach ($arr as $key => $element) {
				$arr[$key] = $this -> clean_text_nodes($element);
			} 
			return($arr);
		} 
	} 
	function getArgOauth($url, $data) {
		if (!empty($this -> oauth_token) && !empty($this -> oauth_secret)) {
			$data['oauth_consumer_key'] = $this -> api_key;
			$data['oauth_timestamp'] = time();
			$data['oauth_nonce'] = md5(uniqid(rand(), true));
			$data['oauth_signature_method'] = "HMAC-SHA1";
			$data['oauth_version'] = "1.0";
			$data['oauth_token'] = $this -> oauth_token;
			if (!$data['oauth_signature'] = $this -> getOauthSignature($url, $data)) return false;
		} 
		return $data;
	} 
	function requestOauthToken() {
		if (session_id() == '') session_start();
		if (!isset($_SESSION['oauth_tokentmp']) || !isset($_SESSION['oauth_secrettmp']) || $_SESSION['oauth_tokentmp'] == '' || $_SESSION['oauth_secrettmp'] == '') {
			$callback = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
			$this -> getRequestToken($callback);
			return false;
		} else return $this -> getAccessToken();
	} 
	function getRequestToken($callback, $perms) {
		if (session_id() == '') session_start();
		$data = array('oauth_consumer_key' => $this -> api_key, 'oauth_timestamp' => time(), 'oauth_nonce' => md5(uniqid(rand(), true)), 'oauth_signature_method' => "HMAC-SHA1", 'oauth_version' => "1.0", 'oauth_callback' => $callback);
		if (!$data['oauth_signature'] = $this -> getOauthSignature($this -> oauthrequest_endpoint, $data)) return false;
		$reponse = $this -> oauthResponse($this -> post($data, $this -> oauthrequest_endpoint));
		if (!isset($reponse['oauth_callback_confirmed']) || $reponse['oauth_callback_confirmed'] != 'true') {
			$this -> error_code = 'Oauth';
			$this -> error_msg = $reponse;
			return false;
		} 
		$_SESSION['oauth_tokentmp'] = $reponse['oauth_token'];
		$_SESSION['oauth_secrettmp'] = $reponse['oauth_token_secret'];
		header("location: " . $this -> oauthauthorize_endpoint . '?oauth_token=' . $reponse['oauth_token'] . '&perms=' . $perms);
		$this -> error_code = '';
		$this -> error_msg = '';
		return true;
	} 
	function getAccessToken() {
		if (session_id() == '') session_start();
		$this -> oauth_token = $_SESSION['oauth_tokentmp'];
		$this -> oauth_secret = $_SESSION['oauth_secrettmp'];
		unset($_SESSION['oauth_tokentmp']);
		unset($_SESSION['oauth_secrettmp']);
		if (!isset($_GET['oauth_verifier']) || $_GET['oauth_verifier'] == '') {
			$this -> error_code = 'Oauth';
			$this -> error_msg = 'oauth_verifier is undefined.';
			return false;
		} 
		$data = array('oauth_consumer_key' => $this -> api_key, 'oauth_timestamp' => time(), 'oauth_nonce' => md5(uniqid(rand(), true)), 'oauth_signature_method' => "HMAC-SHA1", 'oauth_version' => "1.0", 'oauth_token' => $this -> oauth_token, 'oauth_verifier' => $_GET['oauth_verifier']);
		if (!$data['oauth_signature'] = $this -> getOauthSignature($this -> oauthaccesstoken_endpoint, $data)) return false;
		$reponse = $this -> oauthResponse($this -> post($data, $this -> oauthaccesstoken_endpoint));
		if (isset($reponse['oauth_problem']) && $reponse['oauth_problem'] != '') {
			$this -> error_code = 'Oauth';
			$this -> error_msg = $reponse;
			return false;
		} 
		$this -> oauth_token = $reponse['oauth_token'];
		$this -> oauth_secret = $reponse['oauth_token_secret'];
		$this -> error_code = '';
		$this -> error_msg = '';
		return true;
	} 
	function getOauthSignature($url, $data) {
		if ($this -> secret == '') {
			$this -> error_code = 'Oauth';
			$this -> error_msg = 'API Secret is undefined.';
			return false;
		} 
		ksort($data);
		$adresse = 'POST&' . rawurlencode($url) . '&';
		$param = '';
		foreach ($data as $key => $value) $param .= $key . '=' . rawurlencode($value) . '&';
		$param = substr($param, 0, -1);
		$adresse .= rawurlencode($param);
		return base64_encode(hash_hmac('sha1', $adresse, $this -> secret . '&' . $this -> oauth_secret, true));
	} 
	function oauthResponse($response) {
		$expResponse = explode('&', $response);
		$retour = array();
		foreach($expResponse as $v) {
			$expArg = explode('=', $v);
			$retour[$expArg[0]] = $expArg[1];
		} 
		return $retour;
	} 
	function setOauthToken ($token, $secret) {
		$this -> oauth_token = $token;
		$this -> oauth_secret = $secret;
	} 
	function getOauthToken () {
		return $this -> oauth_token;
	} 
	function getOauthSecretToken () {
		return $this -> oauth_secret;
	} 
	function setProxy ($server, $port) {
		$this -> req -> setProxy($server, $port);
	} 
	function getErrorCode () {
		return $this -> error_code;
	} 
	function getErrorMsg () {
		return $this -> error_msg;
	} 
	function buildPhotoURL ($photo, $size = "Medium") {
		$sizes = array("square" => "_s", "thumbnail" => "_t", "small" => "_m", "medium" => "", "medium_640" => "_z", "large" => "_b", "original" => "_o");
		$size = strtolower($size);
		if (!array_key_exists($size, $sizes)) {
			$size = "medium";
		} 
		if ($size == "original") {
			$url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['originalsecret'] . "_o" . "." . $photo['originalformat'];
		} else {
			$url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['secret'] . $sizes[$size] . ".jpg";
		} 
		return $url;
	} 
	function getFriendlyGeodata ($lat, $lon) {
		return unserialize(file_get_contents('http://phpflickr.com/geodata/?format=php&lat=' . $lat . '&lon=' . $lon));
	} 
	function sync_upload ($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null) {
		if (function_exists('curl_init')) {
			$args = array("api_key" => $this -> api_key, "title" => $title, "description" => $description, "tags" => $tags, "is_public" => $is_public, "is_friend" => $is_friend, "is_family" => $is_family);
			ksort($args);
			$auth_sig = "";
			foreach ($args as $key => $data) {
				if (is_null($data)) {
					unset($args[$key]);
				} else {
					$auth_sig .= $key . $data;
				} 
			} 
			if (!empty($this -> secret)) {
				$api_sig = md5($this -> secret . $auth_sig);
				$args["api_sig"] = $api_sig;
			} 
			$args = $this -> getArgOauth($this -> upload_endpoint, $args);
			$photo = realpath($photo);
			$args['photo'] = '@' . $photo;
			$curl = curl_init($this -> upload_endpoint);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			$this -> response = $response;
			curl_close($curl);
			$rsp = explode("\n", $response);
			foreach ($rsp as $line) {
				if (preg_match('|<err code="([0-9]+)" msg="(.*)"|', $line, $match)) {
					if ($this -> die_on_error) die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
					else {
						$this -> error_code = $match[1];
						$this -> error_msg = $match[2];
						$this -> parsed_response = false;
						return false;
					} 
				} elseif (preg_match("|<photoid>(.*)</photoid>|", $line, $match)) {
					$this -> error_code = false;
					$this -> error_msg = false;
					return $match[1];
				} 
			} 
		} else {
			die("Sorry, your server must support CURL in order to upload files");
		} 
	} 
	function async_upload ($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null) {
		if (function_exists('curl_init')) {
			$args = array("async" => 1, "api_key" => $this -> api_key, "title" => $title, "description" => $description, "tags" => $tags, "is_public" => $is_public, "is_friend" => $is_friend, "is_family" => $is_family);
			ksort($args);
			$auth_sig = "";
			foreach ($args as $key => $data) {
				if (is_null($data)) {
					unset($args[$key]);
				} else {
					$auth_sig .= $key . $data;
				} 
			} 
			if (!empty($this -> secret)) {
				$api_sig = md5($this -> secret . $auth_sig);
				$args["api_sig"] = $api_sig;
			} 
			$args = $this -> getArgOauth($this -> upload_endpoint, $args);
			$photo = realpath($photo);
			$args['photo'] = '@' . $photo;
			$curl = curl_init($this -> upload_endpoint);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			$this -> response = $response;
			curl_close($curl);
			$rsp = explode("\n", $response);
			foreach ($rsp as $line) {
				if (preg_match('|<err code="([0-9]+)" msg="(.*)"|', $line, $match)) {
					if ($this -> die_on_error) die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
					else {
						$this -> error_code = $match[1];
						$this -> error_msg = $match[2];
						$this -> parsed_response = false;
						return false;
					} 
				} elseif (preg_match("|<ticketid>(.*)</|", $line, $match)) {
					$this -> error_code = false;
					$this -> error_msg = false;
					return $match[1];
				} 
			} 
		} else {
			die("Sorry, your server must support CURL in order to upload files");
		} 
	} 
	function replace ($photo, $photo_id, $async = null) {
		if (function_exists('curl_init')) {
			$args = array("api_key" => $this -> api_key, "photo_id" => $photo_id, "async" => $async);
			ksort($args);
			$auth_sig = "";
			foreach ($args as $key => $data) {
				if (is_null($data)) {
					unset($args[$key]);
				} else {
					$auth_sig .= $key . $data;
				} 
			} 
			if (!empty($this -> secret)) {
				$api_sig = md5($this -> secret . $auth_sig);
				$args["api_sig"] = $api_sig;
			} 
			$photo = realpath($photo);
			$args['photo'] = '@' . $photo;
			$args = $this -> getArgOauth($this -> replace_endpoint, $args);
			$curl = curl_init($this -> replace_endpoint);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			$this -> response = $response;
			curl_close($curl);
			if ($async == 1) $find = 'ticketid';
			else $find = 'photoid';
			$rsp = explode("\n", $response);
			foreach ($rsp as $line) {
				if (preg_match('|<err code="([0-9]+)" msg="(.*)"|', $line, $match)) {
					if ($this -> die_on_error) die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
					else {
						$this -> error_code = $match[1];
						$this -> error_msg = $match[2];
						$this -> parsed_response = false;
						return false;
					} 
				} elseif (preg_match("|<" . $find . ">(.*)</|", $line, $match)) {
					$this -> error_code = false;
					$this -> error_msg = false;
					return $match[1];
				} 
			} 
		} else {
			die("Sorry, your server must support CURL in order to upload files");
		} 
	} 
	function call ($method, $arguments) {
		foreach ($arguments as $key => $value) {
			if (is_null($value)) unset($arguments[$key]);
		} 
		$this -> request($method, $arguments);
		return $this -> parsed_response ? $this -> parsed_response : false;
	} 
	function activity_userComments ($per_page = null, $page = null) {
		$this -> request('flickr.activity.userComments', array("per_page" => $per_page, "page" => $page));
		return $this -> parsed_response ? $this -> parsed_response['items']['item'] : false;
	} 
	function activity_userPhotos ($timeframe = null, $per_page = null, $page = null) {
		$this -> request('flickr.activity.userPhotos', array("timeframe" => $timeframe, "per_page" => $per_page, "page" => $page));
		return $this -> parsed_response ? $this -> parsed_response['items']['item'] : false;
	} 
	function blogs_getList ($service = null) {
		$rsp = $this -> call('flickr.blogs.getList', array('service' => $service));
		return $rsp['blogs']['blog'];
	} 
	function blogs_getServices () {
		return $this -> call('flickr.blogs.getServices', array());
	} 
	function blogs_postPhoto ($blog_id = null, $photo_id, $title, $description, $blog_password = null, $service = null) {
		return $this -> call('flickr.blogs.postPhoto', array('blog_id' => $blog_id, 'photo_id' => $photo_id, 'title' => $title, 'description' => $description, 'blog_password' => $blog_password, 'service' => $service));
	} 
	function collections_getInfo ($collection_id) {
		return $this -> call('flickr.collections.getInfo', array('collection_id' => $collection_id));
	} 
	function collections_getTree ($collection_id = null, $user_id = null) {
		return $this -> call('flickr.collections.getTree', array('collection_id' => $collection_id, 'user_id' => $user_id));
	} 
	function commons_getInstitutions () {
		return $this -> call('flickr.commons.getInstitutions', array());
	} 
	function contacts_getList ($filter = null, $page = null, $per_page = null) {
		$this -> request('flickr.contacts.getList', array('filter' => $filter, 'page' => $page, 'per_page' => $per_page));
		return $this -> parsed_response ? $this -> parsed_response['contacts'] : false;
	} 
	function contacts_getPublicList ($user_id, $page = null, $per_page = null) {
		$this -> request('flickr.contacts.getPublicList', array('user_id' => $user_id, 'page' => $page, 'per_page' => $per_page));
		return $this -> parsed_response ? $this -> parsed_response['contacts'] : false;
	} 
	function contacts_getListRecentlyUploaded ($date_lastupload = null, $filter = null) {
		return $this -> call('flickr.contacts.getListRecentlyUploaded', array('date_lastupload' => $date_lastupload, 'filter' => $filter));
	} 
	function favorites_add ($photo_id) {
		$this -> request('flickr.favorites.add', array('photo_id' => $photo_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function favorites_getList ($user_id = null, $jump_to = null, $min_fave_date = null, $max_fave_date = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.favorites.getList', array('user_id' => $user_id, 'jump_to' => $jump_to, 'min_fave_date' => $min_fave_date, 'max_fave_date' => $max_fave_date, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function favorites_getPublicList ($user_id, $jump_to = null, $min_fave_date = null, $max_fave_date = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.favorites.getPublicList', array('user_id' => $user_id, 'jump_to' => $jump_to, 'min_fave_date' => $min_fave_date, 'max_fave_date' => $max_fave_date, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function favorites_remove ($photo_id, $user_id = null) {
		$this -> request("flickr.favorites.remove", array('photo_id' => $photo_id, 'user_id' => $user_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function galleries_addPhoto ($gallery_id, $photo_id, $comment = null) {
		return $this -> call('flickr.galleries.addPhoto', array('gallery_id' => $gallery_id, 'photo_id' => $photo_id, 'comment' => $comment));
	} 
	function galleries_create ($title, $description, $primary_photo_id = null) {
		return $this -> call('flickr.galleries.create', array('title' => $title, 'description' => $description, 'primary_photo_id' => $primary_photo_id));
	} 
	function galleries_editMeta ($gallery_id, $title, $description = null) {
		return $this -> call('flickr.galleries.editMeta', array('gallery_id' => $gallery_id, 'title' => $title, 'description' => $description));
	} 
	function galleries_editPhoto ($gallery_id, $photo_id, $comment) {
		return $this -> call('flickr.galleries.editPhoto', array('gallery_id' => $gallery_id, 'photo_id' => $photo_id, 'comment' => $comment));
	} 
	function galleries_editPhotos ($gallery_id, $primary_photo_id, $photo_ids) {
		return $this -> call('flickr.galleries.editPhotos', array('gallery_id' => $gallery_id, 'primary_photo_id' => $primary_photo_id, 'photo_ids' => $photo_ids));
	} 
	function galleries_getInfo ($gallery_id) {
		return $this -> call('flickr.galleries.getInfo', array('gallery_id' => $gallery_id));
	} 
	function galleries_getList ($user_id, $per_page = null, $page = null) {
		return $this -> call('flickr.galleries.getList', array('user_id' => $user_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function galleries_getListForPhoto ($photo_id, $per_page = null, $page = null) {
		return $this -> call('flickr.galleries.getListForPhoto', array('photo_id' => $photo_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function galleries_getPhotos ($gallery_id, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.galleries.getPhotos', array('gallery_id' => $gallery_id, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function groups_browse ($cat_id = null) {
		$this -> request("flickr.groups.browse", array("cat_id" => $cat_id));
		return $this -> parsed_response ? $this -> parsed_response['category'] : false;
	} 
	function groups_getInfo ($group_id, $lang = null) {
		return $this -> call('flickr.groups.getInfo', array('group_id' => $group_id, 'lang' => $lang));
	} 
	function groups_search ($text, $per_page = null, $page = null) {
		$this -> request("flickr.groups.search", array("text" => $text, "per_page" => $per_page, "page" => $page));
		return $this -> parsed_response ? $this -> parsed_response['groups'] : false;
	} 
	function groups_members_getList ($group_id, $membertypes = null, $per_page = null, $page = null) {
		return $this -> call('flickr.groups.members.getList', array('group_id' => $group_id, 'membertypes' => $membertypes, 'per_page' => $per_page, 'page' => $page));
	} 
	function groups_pools_add ($photo_id, $group_id) {
		$this -> request("flickr.groups.pools.add", array("photo_id" => $photo_id, "group_id" => $group_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function groups_pools_getContext ($photo_id, $group_id, $num_prev = null, $num_next = null) {
		return $this -> call('flickr.groups.pools.getContext', array('photo_id' => $photo_id, 'group_id' => $group_id, 'num_prev' => $num_prev, 'num_next' => $num_next));
	} 
	function groups_pools_getGroups ($page = null, $per_page = null) {
		$this -> request("flickr.groups.pools.getGroups", array('page' => $page, 'per_page' => $per_page));
		return $this -> parsed_response ? $this -> parsed_response['groups'] : false;
	} 
	function groups_pools_getPhotos ($group_id, $tags = null, $user_id = null, $jump_to = null, $extras = null, $per_page = null, $page = null) {
		if (is_array($extras)) {
			$extras = implode(",", $extras);
		} 
		return $this -> call('flickr.groups.pools.getPhotos', array('group_id' => $group_id, 'tags' => $tags, 'user_id' => $user_id, 'jump_to' => $jump_to, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function groups_pools_remove ($photo_id, $group_id) {
		$this -> request("flickr.groups.pools.remove", array("photo_id" => $photo_id, "group_id" => $group_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function interestingness_getList ($date = null, $use_panda = null, $extras = null, $per_page = null, $page = null) {
		if (is_array($extras)) {
			$extras = implode(",", $extras);
		} 
		return $this -> call('flickr.interestingness.getList', array('date' => $date, 'use_panda' => $use_panda, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function machinetags_getNamespaces ($predicate = null, $per_page = null, $page = null) {
		return $this -> call('flickr.machinetags.getNamespaces', array('predicate' => $predicate, 'per_page' => $per_page, 'page' => $page));
	} 
	function machinetags_getPairs ($namespace = null, $predicate = null, $per_page = null, $page = null) {
		return $this -> call('flickr.machinetags.getPairs', array('namespace' => $namespace, 'predicate' => $predicate, 'per_page' => $per_page, 'page' => $page));
	} 
	function machinetags_getPredicates ($namespace = null, $per_page = null, $page = null) {
		return $this -> call('flickr.machinetags.getPredicates', array('namespace' => $namespace, 'per_page' => $per_page, 'page' => $page));
	} 
	function machinetags_getRecentValues ($namespace = null, $predicate = null, $added_since = null) {
		return $this -> call('flickr.machinetags.getRecentValues', array('namespace' => $namespace, 'predicate' => $predicate, 'added_since' => $added_since));
	} 
	function machinetags_getValues ($namespace, $predicate, $per_page = null, $page = null, $usage = null) {
		return $this -> call('flickr.machinetags.getValues', array('namespace' => $namespace, 'predicate' => $predicate, 'per_page' => $per_page, 'page' => $page, 'usage' => $usage));
	} 
	function panda_getList () {
		return $this -> call('flickr.panda.getList', array());
	} 
	function panda_getPhotos ($panda_name, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.panda.getPhotos', array('panda_name' => $panda_name, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function people_findByEmail ($find_email) {
		$this -> request("flickr.people.findByEmail", array("find_email" => $find_email));
		return $this -> parsed_response ? $this -> parsed_response['user'] : false;
	} 
	function people_findByUsername ($username) {
		$this -> request("flickr.people.findByUsername", array("username" => $username));
		return $this -> parsed_response ? $this -> parsed_response['user'] : false;
	} 
	function people_getInfo ($user_id) {
		$this -> request("flickr.people.getInfo", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['person'] : false;
	} 
	function people_getPhotos ($user_id, $args = array()) {
		return $this -> call('flickr.people.getPhotos', array_merge(array('user_id' => $user_id), $args));
	} 
	function people_getPhotosOf ($user_id, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.people.getPhotosOf', array('user_id' => $user_id, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function people_getPublicGroups ($user_id) {
		$this -> request("flickr.people.getPublicGroups", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['groups']['group'] : false;
	} 
	function people_getPublicPhotos ($user_id, $safe_search = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.people.getPublicPhotos', array('user_id' => $user_id, 'safe_search' => $safe_search, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function people_getUploadStatus () {
		$this -> request("flickr.people.getUploadStatus");
		return $this -> parsed_response ? $this -> parsed_response['user'] : false;
	} 
	function photos_addTags ($photo_id, $tags) {
		$this -> request("flickr.photos.addTags", array("photo_id" => $photo_id, "tags" => $tags), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_delete ($photo_id) {
		$this -> request("flickr.photos.delete", array("photo_id" => $photo_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_getAllContexts ($photo_id) {
		$this -> request("flickr.photos.getAllContexts", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response : false;
	} 
	function photos_getContactsPhotos ($count = null, $just_friends = null, $single_photo = null, $include_self = null, $extras = null) {
		$this -> request("flickr.photos.getContactsPhotos", array("count" => $count, "just_friends" => $just_friends, "single_photo" => $single_photo, "include_self" => $include_self, "extras" => $extras));
		return $this -> parsed_response ? $this -> parsed_response['photos']['photo'] : false;
	} 
	function photos_getContactsPublicPhotos ($user_id, $count = null, $just_friends = null, $single_photo = null, $include_self = null, $extras = null) {
		$this -> request("flickr.photos.getContactsPublicPhotos", array("user_id" => $user_id, "count" => $count, "just_friends" => $just_friends, "single_photo" => $single_photo, "include_self" => $include_self, "extras" => $extras));
		return $this -> parsed_response ? $this -> parsed_response['photos']['photo'] : false;
	} 
	function photos_getContext ($photo_id, $num_prev = null, $num_next = null, $extras = null, $order_by = null) {
		return $this -> call('flickr.photos.getContext', array('photo_id' => $photo_id, 'num_prev' => $num_prev, 'num_next' => $num_next, 'extras' => $extras, 'order_by' => $order_by));
	} 
	function photos_getCounts ($dates = null, $taken_dates = null) {
		$this -> request("flickr.photos.getCounts", array("dates" => $dates, "taken_dates" => $taken_dates));
		return $this -> parsed_response ? $this -> parsed_response['photocounts']['photocount'] : false;
	} 
	function photos_getExif ($photo_id, $secret = null) {
		$this -> request("flickr.photos.getExif", array("photo_id" => $photo_id, "secret" => $secret));
		return $this -> parsed_response ? $this -> parsed_response['photo'] : false;
	} 
	function photos_getFavorites ($photo_id, $page = null, $per_page = null) {
		$this -> request("flickr.photos.getFavorites", array("photo_id" => $photo_id, "page" => $page, "per_page" => $per_page));
		return $this -> parsed_response ? $this -> parsed_response['photo'] : false;
	} 
	function photos_getInfo ($photo_id, $secret = null) {
		return $this -> call('flickr.photos.getInfo', array('photo_id' => $photo_id, 'secret' => $secret));
	} 
	function photos_getNotInSet ($max_upload_date = null, $min_taken_date = null, $max_taken_date = null, $privacy_filter = null, $media = null, $min_upload_date = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.photos.getNotInSet', array('max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date, 'privacy_filter' => $privacy_filter, 'media' => $media, 'min_upload_date' => $min_upload_date, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_getPerms ($photo_id) {
		$this -> request("flickr.photos.getPerms", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response['perms'] : false;
	} 
	function photos_getRecent ($jump_to = null, $extras = null, $per_page = null, $page = null) {
		if (is_array($extras)) {
			$extras = implode(",", $extras);
		} 
		return $this -> call('flickr.photos.getRecent', array('jump_to' => $jump_to, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_getSizes ($photo_id) {
		$this -> request("flickr.photos.getSizes", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response['sizes']['size'] : false;
	} 
	function photos_getUntagged ($min_upload_date = null, $max_upload_date = null, $min_taken_date = null, $max_taken_date = null, $privacy_filter = null, $media = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.photos.getUntagged', array('min_upload_date' => $min_upload_date, 'max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date, 'privacy_filter' => $privacy_filter, 'media' => $media, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_getWithGeoData ($args = array()) {
		$this -> request("flickr.photos.getWithGeoData", $args);
		return $this -> parsed_response ? $this -> parsed_response['photos'] : false;
	} 
	function photos_getWithoutGeoData ($args = array()) {
		$this -> request("flickr.photos.getWithoutGeoData", $args);
		return $this -> parsed_response ? $this -> parsed_response['photos'] : false;
	} 
	function photos_recentlyUpdated ($min_date, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.photos.recentlyUpdated', array('min_date' => $min_date, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_removeTag ($tag_id) {
		$this -> request("flickr.photos.removeTag", array("tag_id" => $tag_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_search ($args = array()) {
		$this -> request("flickr.photos.search", $args);
		return $this -> parsed_response ? $this -> parsed_response['photos'] : false;
	} 
	function photos_setContentType ($photo_id, $content_type) {
		return $this -> call('flickr.photos.setContentType', array('photo_id' => $photo_id, 'content_type' => $content_type));
	} 
	function photos_setDates ($photo_id, $date_posted = null, $date_taken = null, $date_taken_granularity = null) {
		$this -> request("flickr.photos.setDates", array("photo_id" => $photo_id, "date_posted" => $date_posted, "date_taken" => $date_taken, "date_taken_granularity" => $date_taken_granularity), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_setMeta ($photo_id, $title, $description) {
		$this -> request("flickr.photos.setMeta", array("photo_id" => $photo_id, "title" => $title, "description" => $description), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_setPerms ($photo_id, $is_public, $is_friend, $is_family, $perm_comment, $perm_addmeta) {
		$this -> request("flickr.photos.setPerms", array("photo_id" => $photo_id, "is_public" => $is_public, "is_friend" => $is_friend, "is_family" => $is_family, "perm_comment" => $perm_comment, "perm_addmeta" => $perm_addmeta), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_setSafetyLevel ($photo_id, $safety_level = null, $hidden = null) {
		return $this -> call('flickr.photos.setSafetyLevel', array('photo_id' => $photo_id, 'safety_level' => $safety_level, 'hidden' => $hidden));
	} 
	function photos_setTags ($photo_id, $tags) {
		$this -> request("flickr.photos.setTags", array("photo_id" => $photo_id, "tags" => $tags), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_comments_addComment ($photo_id, $comment_text) {
		$this -> request("flickr.photos.comments.addComment", array("photo_id" => $photo_id, "comment_text" => $comment_text), true);
		return $this -> parsed_response ? $this -> parsed_response['comment'] : false;
	} 
	function photos_comments_deleteComment ($comment_id) {
		$this -> request("flickr.photos.comments.deleteComment", array("comment_id" => $comment_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_comments_editComment ($comment_id, $comment_text) {
		$this -> request("flickr.photos.comments.editComment", array("comment_id" => $comment_id, "comment_text" => $comment_text), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_comments_getList ($photo_id, $min_comment_date = null, $max_comment_date = null, $page = null, $per_page = null, $include_faves = null) {
		return $this -> call('flickr.photos.comments.getList', array('photo_id' => $photo_id, 'min_comment_date' => $min_comment_date, 'max_comment_date' => $max_comment_date, 'page' => $page, 'per_page' => $per_page, 'include_faves' => $include_faves));
	} 
	function photos_comments_getRecentForContacts ($date_lastcomment = null, $contacts_filter = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.photos.comments.getRecentForContacts', array('date_lastcomment' => $date_lastcomment, 'contacts_filter' => $contacts_filter, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_geo_batchCorrectLocation ($lat, $lon, $accuracy, $place_id = null, $woe_id = null) {
		return $this -> call('flickr.photos.geo.batchCorrectLocation', array('lat' => $lat, 'lon' => $lon, 'accuracy' => $accuracy, 'place_id' => $place_id, 'woe_id' => $woe_id));
	} 
	function photos_geo_correctLocation ($photo_id, $place_id = null, $woe_id = null) {
		return $this -> call('flickr.photos.geo.correctLocation', array('photo_id' => $photo_id, 'place_id' => $place_id, 'woe_id' => $woe_id));
	} 
	function photos_geo_getLocation ($photo_id) {
		$this -> request("flickr.photos.geo.getLocation", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response['photo'] : false;
	} 
	function photos_geo_getPerms ($photo_id) {
		$this -> request("flickr.photos.geo.getPerms", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response['perms'] : false;
	} 
	function photos_geo_photosForLocation ($lat, $lon, $accuracy = null, $extras = null, $per_page = null, $page = null) {
		return $this -> call('flickr.photos.geo.photosForLocation', array('lat' => $lat, 'lon' => $lon, 'accuracy' => $accuracy, 'extras' => $extras, 'per_page' => $per_page, 'page' => $page));
	} 
	function photos_geo_removeLocation ($photo_id) {
		$this -> request("flickr.photos.geo.removeLocation", array("photo_id" => $photo_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_geo_setContext ($photo_id, $context) {
		return $this -> call('flickr.photos.geo.setContext', array('photo_id' => $photo_id, 'context' => $context));
	} 
	function photos_geo_setLocation ($photo_id, $lat, $lon, $accuracy = null, $context = null, $bookmark_id = null) {
		return $this -> call('flickr.photos.geo.setLocation', array('photo_id' => $photo_id, 'lat' => $lat, 'lon' => $lon, 'accuracy' => $accuracy, 'context' => $context, 'bookmark_id' => $bookmark_id));
	} 
	function photos_geo_setPerms ($is_public, $is_contact, $is_friend, $is_family, $photo_id) {
		return $this -> call('flickr.photos.geo.setPerms', array('is_public' => $is_public, 'is_contact' => $is_contact, 'is_friend' => $is_friend, 'is_family' => $is_family, 'photo_id' => $photo_id));
	} 
	function photos_licenses_getInfo () {
		$this -> request("flickr.photos.licenses.getInfo");
		return $this -> parsed_response ? $this -> parsed_response['licenses']['license'] : false;
	} 
	function photos_licenses_setLicense ($photo_id, $license_id) {
		$this -> request("flickr.photos.licenses.setLicense", array("photo_id" => $photo_id, "license_id" => $license_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_notes_add ($photo_id, $note_x, $note_y, $note_w, $note_h, $note_text) {
		$this -> request("flickr.photos.notes.add", array("photo_id" => $photo_id, "note_x" => $note_x, "note_y" => $note_y, "note_w" => $note_w, "note_h" => $note_h, "note_text" => $note_text), true);
		return $this -> parsed_response ? $this -> parsed_response['note'] : false;
	} 
	function photos_notes_delete ($note_id) {
		$this -> request("flickr.photos.notes.delete", array("note_id" => $note_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_notes_edit ($note_id, $note_x, $note_y, $note_w, $note_h, $note_text) {
		$this -> request("flickr.photos.notes.edit", array("note_id" => $note_id, "note_x" => $note_x, "note_y" => $note_y, "note_w" => $note_w, "note_h" => $note_h, "note_text" => $note_text), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_transform_rotate ($photo_id, $degrees) {
		$this -> request("flickr.photos.transform.rotate", array("photo_id" => $photo_id, "degrees" => $degrees), true);
		return $this -> parsed_response ? true : false;
	} 
	function photos_people_add ($photo_id, $user_id, $person_x = null, $person_y = null, $person_w = null, $person_h = null) {
		return $this -> call('flickr.photos.people.add', array('photo_id' => $photo_id, 'user_id' => $user_id, 'person_x' => $person_x, 'person_y' => $person_y, 'person_w' => $person_w, 'person_h' => $person_h));
	} 
	function photos_people_delete ($photo_id, $user_id, $email = null) {
		return $this -> call('flickr.photos.people.delete', array('photo_id' => $photo_id, 'user_id' => $user_id, 'email' => $email));
	} 
	function photos_people_deleteCoords ($photo_id, $user_id) {
		return $this -> call('flickr.photos.people.deleteCoords', array('photo_id' => $photo_id, 'user_id' => $user_id));
	} 
	function photos_people_editCoords ($photo_id, $user_id, $person_x, $person_y, $person_w, $person_h, $email = null) {
		return $this -> call('flickr.photos.people.editCoords', array('photo_id' => $photo_id, 'user_id' => $user_id, 'person_x' => $person_x, 'person_y' => $person_y, 'person_w' => $person_w, 'person_h' => $person_h, 'email' => $email));
	} 
	function photos_people_getList ($photo_id) {
		return $this -> call('flickr.photos.people.getList', array('photo_id' => $photo_id));
	} 
	function photos_upload_checkTickets ($tickets) {
		if (is_array($tickets)) {
			$tickets = implode(",", $tickets);
		} 
		$this -> request("flickr.photos.upload.checkTickets", array("tickets" => $tickets), true);
		return $this -> parsed_response ? $this -> parsed_response['uploader']['ticket'] : false;
	} 
	function photosets_addPhoto ($photoset_id, $photo_id) {
		$this -> request("flickr.photosets.addPhoto", array("photoset_id" => $photoset_id, "photo_id" => $photo_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_create ($title, $description, $primary_photo_id) {
		$this -> request("flickr.photosets.create", array("title" => $title, "primary_photo_id" => $primary_photo_id, "description" => $description), true);
		return $this -> parsed_response ? $this -> parsed_response['photoset'] : false;
	} 
	function photosets_delete ($photoset_id) {
		$this -> request("flickr.photosets.delete", array("photoset_id" => $photoset_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_editMeta ($photoset_id, $title, $description = null) {
		$this -> request("flickr.photosets.editMeta", array("photoset_id" => $photoset_id, "title" => $title, "description" => $description), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_editPhotos ($photoset_id, $primary_photo_id, $photo_ids) {
		$this -> request("flickr.photosets.editPhotos", array("photoset_id" => $photoset_id, "primary_photo_id" => $primary_photo_id, "photo_ids" => $photo_ids), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_getContext ($photo_id, $photoset_id, $num_prev = null, $num_next = null) {
		return $this -> call('flickr.photosets.getContext', array('photo_id' => $photo_id, 'photoset_id' => $photoset_id, 'num_prev' => $num_prev, 'num_next' => $num_next));
	} 
	function photosets_getInfo ($photoset_id) {
		$this -> request("flickr.photosets.getInfo", array("photoset_id" => $photoset_id));
		return $this -> parsed_response ? $this -> parsed_response['photoset'] : false;
	} 
	function photosets_getList ($user_id = null) {
		$this -> request("flickr.photosets.getList", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['photosets'] : false;
	} 
	function photosets_getPhotos ($photoset_id, $extras = null, $privacy_filter = null, $per_page = null, $page = null, $media = null) {
		return $this -> call('flickr.photosets.getPhotos', array('photoset_id' => $photoset_id, 'extras' => $extras, 'privacy_filter' => $privacy_filter, 'per_page' => $per_page, 'page' => $page, 'media' => $media));
	} 
	function photosets_orderSets ($photoset_ids) {
		if (is_array($photoset_ids)) {
			$photoset_ids = implode(",", $photoset_ids);
		} 
		$this -> request("flickr.photosets.orderSets", array("photoset_ids" => $photoset_ids), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_removePhoto ($photoset_id, $photo_id) {
		$this -> request("flickr.photosets.removePhoto", array("photoset_id" => $photoset_id, "photo_id" => $photo_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_removePhotos ($photoset_id, $photo_ids) {
		return $this -> call('flickr.photosets.removePhotos', array('photoset_id' => $photoset_id, 'photo_ids' => $photo_ids));
	} 
	function photosets_reorderPhotos ($photoset_id, $photo_ids) {
		return $this -> call('flickr.photosets.reorderPhotos', array('photoset_id' => $photoset_id, 'photo_ids' => $photo_ids));
	} 
	function photosets_setPrimaryPhoto ($photoset_id, $photo_id) {
		return $this -> call('flickr.photosets.setPrimaryPhoto', array('photoset_id' => $photoset_id, 'photo_id' => $photo_id));
	} 
	function photosets_comments_addComment ($photoset_id, $comment_text) {
		$this -> request("flickr.photosets.comments.addComment", array("photoset_id" => $photoset_id, "comment_text" => $comment_text), true);
		return $this -> parsed_response ? $this -> parsed_response['comment'] : false;
	} 
	function photosets_comments_deleteComment ($comment_id) {
		$this -> request("flickr.photosets.comments.deleteComment", array("comment_id" => $comment_id), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_comments_editComment ($comment_id, $comment_text) {
		$this -> request("flickr.photosets.comments.editComment", array("comment_id" => $comment_id, "comment_text" => $comment_text), true);
		return $this -> parsed_response ? true : false;
	} 
	function photosets_comments_getList ($photoset_id) {
		$this -> request("flickr.photosets.comments.getList", array("photoset_id" => $photoset_id));
		return $this -> parsed_response ? $this -> parsed_response['comments'] : false;
	} 
	function places_find ($query) {
		return $this -> call('flickr.places.find', array('query' => $query));
	} 
	function places_findByLatLon ($lat, $lon, $accuracy = null) {
		return $this -> call('flickr.places.findByLatLon', array('lat' => $lat, 'lon' => $lon, 'accuracy' => $accuracy));
	} 
	function places_getChildrenWithPhotosPublic ($place_id = null, $woe_id = null) {
		return $this -> call('flickr.places.getChildrenWithPhotosPublic', array('place_id' => $place_id, 'woe_id' => $woe_id));
	} 
	function places_getInfo ($place_id = null, $woe_id = null) {
		return $this -> call('flickr.places.getInfo', array('place_id' => $place_id, 'woe_id' => $woe_id));
	} 
	function places_getInfoByUrl ($url) {
		return $this -> call('flickr.places.getInfoByUrl', array('url' => $url));
	} 
	function places_getPlaceTypes () {
		return $this -> call('flickr.places.getPlaceTypes', array());
	} 
	function places_getShapeHistory ($place_id = null, $woe_id = null) {
		return $this -> call('flickr.places.getShapeHistory', array('place_id' => $place_id, 'woe_id' => $woe_id));
	} 
	function places_getTopPlacesList ($place_type_id, $date = null, $woe_id = null, $place_id = null) {
		return $this -> call('flickr.places.getTopPlacesList', array('place_type_id' => $place_type_id, 'date' => $date, 'woe_id' => $woe_id, 'place_id' => $place_id));
	} 
	function places_placesForBoundingBox ($bbox, $place_type = null, $place_type_id = null, $recursive = null) {
		return $this -> call('flickr.places.placesForBoundingBox', array('bbox' => $bbox, 'place_type' => $place_type, 'place_type_id' => $place_type_id, 'recursive' => $recursive));
	} 
	function places_placesForContacts ($place_type = null, $place_type_id = null, $woe_id = null, $place_id = null, $threshold = null, $contacts = null, $min_upload_date = null, $max_upload_date = null, $min_taken_date = null, $max_taken_date = null) {
		return $this -> call('flickr.places.placesForContacts', array('place_type' => $place_type, 'place_type_id' => $place_type_id, 'woe_id' => $woe_id, 'place_id' => $place_id, 'threshold' => $threshold, 'contacts' => $contacts, 'min_upload_date' => $min_upload_date, 'max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date));
	} 
	function places_placesForTags ($place_type_id, $woe_id = null, $place_id = null, $threshold = null, $tags = null, $tag_mode = null, $machine_tags = null, $machine_tag_mode = null, $min_upload_date = null, $max_upload_date = null, $min_taken_date = null, $max_taken_date = null) {
		return $this -> call('flickr.places.placesForTags', array('place_type_id' => $place_type_id, 'woe_id' => $woe_id, 'place_id' => $place_id, 'threshold' => $threshold, 'tags' => $tags, 'tag_mode' => $tag_mode, 'machine_tags' => $machine_tags, 'machine_tag_mode' => $machine_tag_mode, 'min_upload_date' => $min_upload_date, 'max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date));
	} 
	function places_placesForUser ($place_type_id = null, $place_type = null, $woe_id = null, $place_id = null, $threshold = null, $min_upload_date = null, $max_upload_date = null, $min_taken_date = null, $max_taken_date = null) {
		return $this -> call('flickr.places.placesForUser', array('place_type_id' => $place_type_id, 'place_type' => $place_type, 'woe_id' => $woe_id, 'place_id' => $place_id, 'threshold' => $threshold, 'min_upload_date' => $min_upload_date, 'max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date));
	} 
	function places_resolvePlaceId ($place_id) {
		$rsp = $this -> call('flickr.places.resolvePlaceId', array('place_id' => $place_id));
		return $rsp ? $rsp['location'] : $rsp;
	} 
	function places_resolvePlaceURL ($url) {
		$rsp = $this -> call('flickr.places.resolvePlaceURL', array('url' => $url));
		return $rsp ? $rsp['location'] : $rsp;
	} 
	function places_tagsForPlace ($woe_id = null, $place_id = null, $min_upload_date = null, $max_upload_date = null, $min_taken_date = null, $max_taken_date = null) {
		return $this -> call('flickr.places.tagsForPlace', array('woe_id' => $woe_id, 'place_id' => $place_id, 'min_upload_date' => $min_upload_date, 'max_upload_date' => $max_upload_date, 'min_taken_date' => $min_taken_date, 'max_taken_date' => $max_taken_date));
	} 
	function prefs_getContentType () {
		$rsp = $this -> call('flickr.prefs.getContentType', array());
		return $rsp ? $rsp['person'] : $rsp;
	} 
	function prefs_getGeoPerms () {
		return $this -> call('flickr.prefs.getGeoPerms', array());
	} 
	function prefs_getHidden () {
		$rsp = $this -> call('flickr.prefs.getHidden', array());
		return $rsp ? $rsp['person'] : $rsp;
	} 
	function prefs_getPrivacy () {
		$rsp = $this -> call('flickr.prefs.getPrivacy', array());
		return $rsp ? $rsp['person'] : $rsp;
	} 
	function prefs_getSafetyLevel () {
		$rsp = $this -> call('flickr.prefs.getSafetyLevel', array());
		return $rsp ? $rsp['person'] : $rsp;
	} 
	function reflection_getMethodInfo ($method_name) {
		$this -> request("flickr.reflection.getMethodInfo", array("method_name" => $method_name));
		return $this -> parsed_response ? $this -> parsed_response : false;
	} 
	function reflection_getMethods () {
		$this -> request("flickr.reflection.getMethods");
		return $this -> parsed_response ? $this -> parsed_response['methods']['method'] : false;
	} 
	function stats_getCollectionDomains ($date, $collection_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getCollectionDomains', array('date' => $date, 'collection_id' => $collection_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getCollectionReferrers ($date, $domain, $collection_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getCollectionReferrers', array('date' => $date, 'domain' => $domain, 'collection_id' => $collection_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getCollectionStats ($date, $collection_id) {
		return $this -> call('flickr.stats.getCollectionStats', array('date' => $date, 'collection_id' => $collection_id));
	} 
	function stats_getCSVFiles () {
		return $this -> call('flickr.stats.getCSVFiles', array());
	} 
	function stats_getPhotoDomains ($date, $photo_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotoDomains', array('date' => $date, 'photo_id' => $photo_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotoReferrers ($date, $domain, $photo_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotoReferrers', array('date' => $date, 'domain' => $domain, 'photo_id' => $photo_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotosetDomains ($date, $photoset_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotosetDomains', array('date' => $date, 'photoset_id' => $photoset_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotosetReferrers ($date, $domain, $photoset_id = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotosetReferrers', array('date' => $date, 'domain' => $domain, 'photoset_id' => $photoset_id, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotosetStats ($date, $photoset_id) {
		return $this -> call('flickr.stats.getPhotosetStats', array('date' => $date, 'photoset_id' => $photoset_id));
	} 
	function stats_getPhotoStats ($date, $photo_id) {
		return $this -> call('flickr.stats.getPhotoStats', array('date' => $date, 'photo_id' => $photo_id));
	} 
	function stats_getPhotostreamDomains ($date, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotostreamDomains', array('date' => $date, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotostreamReferrers ($date, $domain, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPhotostreamReferrers', array('date' => $date, 'domain' => $domain, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getPhotostreamStats ($date) {
		return $this -> call('flickr.stats.getPhotostreamStats', array('date' => $date));
	} 
	function stats_getPopularPhotos ($date = null, $sort = null, $per_page = null, $page = null) {
		return $this -> call('flickr.stats.getPopularPhotos', array('date' => $date, 'sort' => $sort, 'per_page' => $per_page, 'page' => $page));
	} 
	function stats_getTotalViews ($date = null) {
		return $this -> call('flickr.stats.getTotalViews', array('date' => $date));
	} 
	function tags_getClusterPhotos ($tag, $cluster_id) {
		return $this -> call('flickr.tags.getClusterPhotos', array('tag' => $tag, 'cluster_id' => $cluster_id));
	} 
	function tags_getClusters ($tag) {
		return $this -> call('flickr.tags.getClusters', array('tag' => $tag));
	} 
	function tags_getHotList ($period = null, $count = null) {
		$this -> request("flickr.tags.getHotList", array("period" => $period, "count" => $count));
		return $this -> parsed_response ? $this -> parsed_response['hottags'] : false;
	} 
	function tags_getListPhoto ($photo_id) {
		$this -> request("flickr.tags.getListPhoto", array("photo_id" => $photo_id));
		return $this -> parsed_response ? $this -> parsed_response['photo']['tags']['tag'] : false;
	} 
	function tags_getListUser ($user_id = null) {
		$this -> request("flickr.tags.getListUser", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['who']['tags']['tag'] : false;
	} 
	function tags_getListUserPopular ($user_id = null, $count = null) {
		$this -> request("flickr.tags.getListUserPopular", array("user_id" => $user_id, "count" => $count));
		return $this -> parsed_response ? $this -> parsed_response['who']['tags']['tag'] : false;
	} 
	function tags_getListUserRaw ($tag = null) {
		return $this -> call('flickr.tags.getListUserRaw', array('tag' => $tag));
	} 
	function tags_getRelated ($tag) {
		$this -> request("flickr.tags.getRelated", array("tag" => $tag));
		return $this -> parsed_response ? $this -> parsed_response['tags'] : false;
	} 
	function test_echo ($args = array()) {
		$this -> request("flickr.test.echo", $args);
		return $this -> parsed_response ? $this -> parsed_response : false;
	} 
	function test_login () {
		$this -> request("flickr.test.login");
		return $this -> parsed_response ? $this -> parsed_response['user'] : false;
	} 
	function urls_getGroup ($group_id) {
		$this -> request("flickr.urls.getGroup", array("group_id" => $group_id));
		return $this -> parsed_response ? $this -> parsed_response['group']['url'] : false;
	} 
	function urls_getUserPhotos ($user_id = null) {
		$this -> request("flickr.urls.getUserPhotos", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['user']['url'] : false;
	} 
	function urls_getUserProfile ($user_id = null) {
		$this -> request("flickr.urls.getUserProfile", array("user_id" => $user_id));
		return $this -> parsed_response ? $this -> parsed_response['user']['url'] : false;
	} 
	function urls_lookupGallery ($url) {
		return $this -> call('flickr.urls.lookupGallery', array('url' => $url));
	} 
	function urls_lookupGroup ($url) {
		$this -> request("flickr.urls.lookupGroup", array("url" => $url));
		return $this -> parsed_response ? $this -> parsed_response['group'] : false;
	} 
	function urls_lookupUser ($url) {
		$this -> request("flickr.urls.lookupUser", array("url" => $url));
		return $this -> parsed_response ? $this -> parsed_response['user'] : false;
	} 
} 
class autopostphpFlickr_pager {
	var $phpFlickr, $per_page, $method, $args, $results, $global_phpFlickr;
	var $total = null, $page = 0, $pages = null, $photos, $_extra = null;
	function autopostphpFlickr_pager($phpFlickr, $method = null, $args = null, $per_page = 30) {
		$this -> per_page = $per_page;
		$this -> method = $method;
		$this -> args = $args;
		$this -> set_phpFlickr($phpFlickr);
	} 
	function set_phpFlickr($phpFlickr) {
		if (is_a($phpFlickr, 'phpFlickr')) {
			$this -> phpFlickr = $phpFlickr;
			$this -> args['per_page'] = (int) $this -> per_page;
		} 
	} 
	function __sleep() {
		return array('method', 'args', 'per_page', 'page', '_extra',);
	} 
	function load($page) {
		$allowed_methods = array('flickr.photos.search' => 'photos', 'flickr.photosets.getPhotos' => 'photoset',);
		if (!in_array($this -> method, array_keys($allowed_methods))) return false;
		$this -> args['page'] = $page;
		$this -> results = $this -> phpFlickr -> call($this -> method, $this -> args);
		if ($this -> results) {
			$this -> results = $this -> results[$allowed_methods[$this -> method]];
			$this -> photos = $this -> results['photo'];
			$this -> total = $this -> results['total'];
			$this -> pages = $this -> results['pages'];
			return true;
		} else {
			return false;
		} 
	} 
	function get($page = null) {
		if (is_null($page)) {
			$page = $this -> page;
		} else {
			$this -> page = $page;
		} 
		if ($this -> load($page)) {
			return $this -> photos;
		} 
		$this -> total = 0;
		$this -> pages = 0;
		return array();
	} 
	function next() {
		$this -> page++;
		if ($this -> load($this -> page)) {
			return $this -> photos;
		} 
		$this -> total = 0;
		$this -> pages = 0;
		return array();
	} 
} 
class Qiniu_RS_GetPolicy {
	public $Expires;
	public function MakeRequest($baseUrl, $mac) {
		$deadline = $this -> Expires;
		if ($deadline == 0) {
			$deadline = 3600;
		} 
		$deadline += time();
		$pos = strpos($baseUrl, '?');
		if ($pos !== false) {
			$baseUrl .= '&e=';
		} else {
			$baseUrl .= '?e=';
		} 
		$baseUrl .= $deadline;
		$token = Qiniu_Sign($mac, $baseUrl);
		return "$baseUrl&token=$token";
	} 
} 
class Qiniu_RS_PutPolicy {
	public $Scope;
	public $CallbackUrl;
	public $CallbackBody;
	public $ReturnUrl;
	public $ReturnBody;
	public $AsyncOps;
	public $EndUser;
	public $Expires;
	public function __construct($scope) {
		$this -> Scope = $scope;
	} 
	public function Token($mac) {
		$deadline = $this -> Expires;
		if ($deadline == 0) {
			$deadline = 3600;
		} 
		$deadline += time();
		$policy = array('scope' => $this -> Scope, 'deadline' => $deadline);
		if (!empty($this -> CallbackUrl)) {
			$policy['callbackUrl'] = $this -> CallbackUrl;
		} 
		if (!empty($this -> CallbackBody)) {
			$policy['callbackBody'] = $this -> CallbackBody;
		} 
		if (!empty($this -> ReturnUrl)) {
			$policy['returnUrl'] = $this -> ReturnUrl;
		} 
		if (!empty($this -> ReturnBody)) {
			$policy['returnBody'] = $this -> ReturnBody;
		} 
		if (!empty($this -> AsyncOps)) {
			$policy['asyncOps'] = $this -> AsyncOps;
		} 
		if (!empty($this -> EndUser)) {
			$policy['endUser'] = $this -> EndUser;
		} 
		$b = json_encode($policy);
		return Qiniu_SignWithData($mac, $b);
	} 
} 
class Qiniu_RS_EntryPath {
	public $bucket;
	public $key;
	public function __construct($bucket, $key) {
		$this -> bucket = $bucket;
		$this -> key = $key;
	} 
} 
class Qiniu_RS_EntryPathPair {
	public $src;
	public $dest;
	public function __construct($src, $dest) {
		$this -> src = $src;
		$this -> dest = $dest;
	} 
} 
class Qiniu_PutExtra {
	public $Params = null;
	public $MimeType = null;
	public $Crc32 = 0;
	public $CheckCrc = 0;
} 
class Qiniu_Rio_PutExtra {
	public $Bucket = null;
	public $Params = null;
	public $MimeType = null;
	public $ChunkSize = 0;
	public $TryTimes = 0;
	public $Progresses = null;
	public $Notify = null;
	public $NotifyErr = null;
	public function __construct($bucket = null) {
		$this -> Bucket = $bucket;
	} 
} 
class Qiniu_Rio_UploadClient {
	public $uptoken;
	public function __construct($uptoken) {
		$this -> uptoken = $uptoken;
	} 
	public function RoundTrip($req) {
		$token = $this -> uptoken;
		$req -> Header['Authorization'] = "UpToken $token";
		return Qiniu_Client_do($req);
	} 
} 
class Qiniu_Error {
	public $Err;
	public $Reqid;
	public $Details;
	public $Code;
	public function __construct($code, $err) {
		$this -> Code = $code;
		$this -> Err = $err;
	} 
} 
class Qiniu_Request {
	public $URL;
	public $Header;
	public $Body;
	public function __construct($url, $body) {
		$this -> URL = $url;
		$this -> Header = array();
		$this -> Body = $body;
	} 
} 
class Qiniu_Response {
	public $StatusCode;
	public $Header;
	public $ContentLength;
	public $Body;
	public function __construct($code, $body) {
		$this -> StatusCode = $code;
		$this -> Header = array();
		$this -> Body = $body;
		$this -> ContentLength = strlen($body);
	} 
} 
class Qiniu_HttpClient {
	public function RoundTrip($req) {
		return Qiniu_Client_do($req);
	} 
} 
class Qiniu_MacHttpClient {
	public $Mac;
	public function __construct($mac) {
		$this -> Mac = Qiniu_RequireMac($mac);
	} 
	public function RoundTrip($req) {
		$incbody = Qiniu_Client_incBody($req);
		$token = $this -> Mac -> SignRequest($req, $incbody);
		$req -> Header['Authorization'] = "QBox $token";
		return Qiniu_Client_do($req);
	} 
} 
class Qiniu_ImageView {
	public $Mode;
	public $Width;
	public $Height;
	public $Quality;
	public $Format;
	public function MakeRequest($url) {
		$ops = array($this -> Mode);
		if (!empty($this -> Width)) {
			$ops[] = 'w/' . $this -> Width;
		} 
		if (!empty($this -> Height)) {
			$ops[] = 'h/' . $this -> Height;
		} 
		if (!empty($this -> Quality)) {
			$ops[] = 'q/' . $this -> Quality;
		} 
		if (!empty($this -> Format)) {
			$ops[] = 'format/' . $this -> Format;
		} 
		return $url . "?imageView/" . implode('/', $ops);
	} 
} 
class Qiniu_Exif {
	public function MakeRequest($url) {
		return $url . "?exif";
	} 
} 
class Qiniu_ImageInfo {
	public function MakeRequest($url) {
		return $url . "?imageInfo";
	} 
} 
class Qiniu_Mac {
	public $AccessKey;
	public $SecretKey;
	public function __construct($accessKey, $secretKey) {
		$this -> AccessKey = $accessKey;
		$this -> SecretKey = $secretKey;
	} 
	public function Sign($data) {
		$sign = hash_hmac('sha1', $data, $this -> SecretKey, true);
		return $this -> AccessKey . ':' . Qiniu_Encode($sign);
	} 
	public function SignWithData($data) {
		$data = Qiniu_Encode($data);
		return $this -> Sign($data) . ':' . $data;
	} 
	public function SignRequest($req, $incbody) {
		$url = $req -> URL;
		$url = parse_url($url['path']);
		$data = '';
		if (isset($url['path'])) {
			$data = $url['path'];
		} 
		if (isset($url['query'])) {
			$data .= '?' . $url['query'];
		} 
		$data .= "\n";
		if ($incbody) {
			$data .= $req -> Body;
		} 
		return $this -> Sign($data);
	} 
} 
class apUpYunException extends Exception {
	public function __construct($message, $code, Exception $previous = null) {
		parent :: __construct($message, $code);
	} 
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	} 
} 
class apUpYunAuthorizationException extends apUpYunException {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent :: __construct($message, 401, $previous);
	} 
} 
class apUpYunForbiddenException extends apUpYunException {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent :: __construct($message, 403, $previous);
	} 
} 
class apUpYunNotFoundException extends apUpYunException {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent :: __construct($message, 404, $previous);
	} 
} 
class apUpYunNotAcceptableException extends apUpYunException {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent :: __construct($message, 406, $previous);
	} 
} 
class apUpYunServiceUnavailable extends apUpYunException {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent :: __construct($message, 503, $previous);
	} 
} 
class apUpYun {
	const VERSION = '2.0';
	const ED_AUTO = 'v0.api.upyun.com';
	const ED_TELECOM = 'v1.api.upyun.com';
	const ED_CNC = 'v2.api.upyun.com';
	const ED_CTT = 'v3.api.upyun.com';
	const CONTENT_TYPE = 'Content-Type';
	const CONTENT_MD5 = 'Content-MD5';
	const CONTENT_SECRET = 'Content-Secret';
	const X_GMKERL_THUMBNAIL = 'x-gmkerl-thumbnail';
	const X_GMKERL_TYPE = 'x-gmkerl-type';
	const X_GMKERL_VALUE = 'x-gmkerl-value';
	const X_GMKERL_QUALITY = 'x­gmkerl-quality';
	const X_GMKERL_UNSHARP = 'x­gmkerl-unsharp';
	private $_bucket_name;
	private $_username;
	private $_password;
	private $_timeout = 30;
	private $_content_md5 = null;
	private $_file_secret = null;
	private $_file_infos = null;
	protected $endpoint;
	public function __construct($bucketname, $username, $password, $endpoint = null, $timeout = 30) {
		$this -> _bucketname = $bucketname;
		$this -> _username = $username;
		$this -> _password = md5($password);
		$this -> _timeout = $timeout;
		$this -> endpoint = is_null($endpoint) ? self :: ED_AUTO : $endpoint;
	} 
	public function version() {
		return self :: VERSION;
	} 
	public function makeDir($path, $auto_mkdir = false) {
		$headers = array('Folder' => 'true');
		if ($auto_mkdir) $headers['Mkdir'] = 'true';
		return $this -> _do_request('PUT', $path, $headers);
	} 
	public function delete($path) {
		return $this -> _do_request('DELETE', $path);
	} 
	public function writeFile($path, $file, $auto_mkdir = false, $opts = null) {
		if (is_null($opts)) $opts = array();
		if (!is_null($this -> _content_md5) || !is_null($this -> _file_secret)) {
			if (!is_null($this -> _content_md5)) $opts[self :: CONTENT_MD5] = $this -> _content_md5;
			if (!is_null($this -> _file_secret)) $opts[self :: CONTENT_SECRET] = $this -> _file_secret;
		} 
		if ($auto_mkdir === true) $opts['Mkdir'] = 'true';
		$this -> _file_infos = $this -> _do_request('PUT', $path, $opts, $file);
		return $this -> _file_infos;
	} 
	public function readFile($path, $file_handle = null) {
		return $this -> _do_request('GET', $path, null, null, $file_handle);
	} 
	public function getList($path = '/') {
		$rsp = $this -> _do_request('GET', $path);
		$list = array();
		if ($rsp) {
			$rsp = explode("\n", $rsp);
			foreach($rsp as $item) {
				@list($name, $type, $size, $time) = explode("\t", trim($item));
				if (!empty($time)) {
					$type = $type == 'N' ? 'file' : 'folder';
				} 
				$item = array('name' => $name, 'type' => $type, 'size' => intval($size), 'time' => intval($time),);
				array_push($list, $item);
			} 
		} 
		return $list;
	} 
	public function getFolderUsage($path = '/') {
		$rsp = $this -> _do_request('GET', '/?usage');
		return floatval($rsp);
	} 
	public function getFileInfo($path) {
		$rsp = $this -> _do_request('HEAD', $path);
		return $rsp;
	} 
	private function sign($method, $uri, $date, $length) {
		$sign = "{$method}&{$uri}&{$date}&{$length}&{$this->_password}";
		return 'UpYun ' . $this -> _username . ':' . md5($sign);
	} 
	protected function _do_request($method, $path, $headers = null, $body = null, $file_handle = null) {
		$uri = "/{$this->_bucketname}{$path}";
		$ch = curl_init("http://{$this->endpoint}{$uri}");
		$_headers = array('Expect:');
		if (!is_null($headers) && is_array($headers)) {
			foreach($headers as $k => $v) {
				array_push($_headers, "{$k}: {$v}");
			} 
		} 
		$length = 0;
		$date = gmdate('D, d M Y H:i:s \G\M\T');
		if (!is_null($body)) {
			if (is_resource($body)) {
				fseek($body, 0, SEEK_END);
				$length = ftell($body);
				fseek($body, 0);
				array_push($_headers, "Content-Length: {$length}");
				curl_setopt($ch, CURLOPT_INFILE, $body);
				curl_setopt($ch, CURLOPT_INFILESIZE, $length);
			} else {
				$length = @strlen($body);
				array_push($_headers, "Content-Length: {$length}");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			} 
		} else {
			array_push($_headers, "Content-Length: {$length}");
		} 
		array_push($_headers, "Authorization: {$this->sign($method, $uri, $date, $length)}");
		array_push($_headers, "Date: {$date}");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this -> _timeout);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($method == 'PUT' || $method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
		} else {
			curl_setopt($ch, CURLOPT_POST, 0);
		} 
		if ($method == 'GET' && is_resource($file_handle)) {
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FILE, $file_handle);
		} 
		if ($method == 'HEAD') {
			curl_setopt($ch, CURLOPT_NOBODY, true);
		} 
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code == 0) throw new apUpYunException('Connection Failed', $http_code);
		curl_close($ch);
		$header_string = '';
		$body = '';
		if ($method == 'GET' && is_resource($file_handle)) {
			$header_string = '';
			$body = $response;
		} else {
			list($header_string, $body) = explode("\r\n\r\n", $response, 2);
		} 
		if ($http_code == 200) {
			if ($method == 'GET' && is_null($file_handle)) {
				return $body;
			} else {
				$data = $this -> _getHeadersData($header_string);
				return count($data) > 0 ? $data : true;
			} 
		} else {
			$message = $this -> _getErrorMessage($header_string);
			if (is_null($message) && $method == 'GET' && is_resource($file_handle)) {
				$message = 'File Not Found';
			} 
			switch ($http_code) {
				case 401: throw new apUpYunAuthorizationException($message);
					break;
				case 403: throw new apUpYunForbiddenException($message);
					break;
				case 404: throw new apUpYunNotFoundException($message);
					break;
				case 406: throw new apUpYunNotAcceptableException($message);
					break;
				case 503: throw new apUpYunServiceUnavailable($message);
					break;
				default: throw new apUpYunException($message, $http_code);
			} 
		} 
	} 
	private function _getHeadersData($text) {
		$headers = explode("\r\n", $text);
		$items = array();
		foreach($headers as $header) {
			$header = trim($header);
			if (strpos($header, 'x-upyun') !== false) {
				list($k, $v) = explode(':', $header);
				$items[trim($k)] = in_array(substr($k, 8, 5), array('width', 'heigh', 'frame')) ? intval($v) : trim($v);
			} 
		} 
		return $items;
	} 
	private function _getErrorMessage($header_string) {
		list($status, $stash) = explode("\r\n", $header_string, 2);
		list($v, $code, $message) = explode(" ", $status, 3);
		return $message;
	} 
	public function rmDir($path) {
		$this -> _do_request('DELETE', $path);
	} 
	public function deleteFile($path) {
		$rsp = $this -> _do_request('DELETE', $path);
	} 
	public function readDir($path) {
		return $this -> getList($path);
	} 
	public function getBucketUsage() {
		return $this -> getFolderUsage('/');
	} 
	public function setApiDomain($domain) {
		$this -> endpoint = $domain;
	} 
	public function setContentMD5($str) {
		$this -> _content_md5 = $str;
	} 
	public function setFileSecret($str) {
		$this -> _file_secret = $str;
	} 
	public function getWritedFileInfo($key) {
		if (!isset($this -> _file_infos))return null;
		return $this -> _file_infos[$key];
	} 
	public function makeBaseUrl($domain, $key) {
		return "http://$domain$key";
	} 
} 
class simple_html_dom_node_ap {
	public $nodetype = HDOM_TYPE_TEXT;
	public $tag = 'text';
	public $attr = array();
	public $children = array();
	public $nodes = array();
	public $parent = null;
	public $_ = array();
	public $tag_start = 0;
	private $dom = null;
	function __construct($dom = '') {
		$this -> dom = $dom;
		$dom -> nodes[] = $this;
	} 
	function __destruct() {
		$this -> clear();
	} 
	function __toString() {
		return $this -> outertext();
	} 
	function clear() {
		$this -> dom = null;
		$this -> nodes = null;
		$this -> parent = null;
		$this -> children = null;
	} 
	function dump($show_attr = true, $deep = 0) {
		$lead = str_repeat('    ', $deep);
		echo $lead . $this -> tag;
		if ($show_attr && count($this -> attr) > 0) {
			echo '(';
			foreach ($this -> attr as $k => $v) echo "[$k]=>\"" . $this -> $k . '", ';
			echo ')';
		} 
		echo "\n";
		if ($this -> nodes) {
			foreach ($this -> nodes as $c) {
				$c -> dump($show_attr, $deep + 1);
			} 
		} 
	} 
	function dump_node($echo = true) {
		$string = $this -> tag;
		if (count($this -> attr) > 0) {
			$string .= '(';
			foreach ($this -> attr as $k => $v) {
				$string .= "[$k]=>\"" . $this -> $k . '", ';
			} 
			$string .= ')';
		} 
		if (count($this -> _) > 0) {
			$string .= ' $_ (';
			foreach ($this -> _ as $k => $v) {
				if (is_array($v)) {
					$string .= "[$k]=>(";
					foreach ($v as $k2 => $v2) {
						$string .= "[$k2]=>\"" . $v2 . '", ';
					} 
					$string .= ")";
				} else {
					$string .= "[$k]=>\"" . $v . '", ';
				} 
			} 
			$string .= ")";
		} 
		if (isset($this -> text)) {
			$string .= " text: (" . $this -> text . ")";
		} 
		$string .= " HDOM_INNER_INFO: '";
		if (isset($node -> _[HDOM_INFO_INNER])) {
			$string .= $node -> _[HDOM_INFO_INNER] . "'";
		} else {
			$string .= ' NULL ';
		} 
		$string .= " children: " . count($this -> children);
		$string .= " nodes: " . count($this -> nodes);
		$string .= " tag_start: " . $this -> tag_start;
		$string .= "\n";
		if ($echo) {
			echo $string;
			return;
		} else {
			return $string;
		} 
	} 
	function parent($parent = null) {
		if ($parent !== null) {
			$this -> parent = $parent;
			$this -> parent -> nodes[] = $this;
			$this -> parent -> children[] = $this;
		} 
		return $this -> parent;
	} 
	function has_child() {
		return !empty($this -> children);
	} 
	function children($idx = -1) {
		if ($idx === -1) {
			return $this -> children;
		} 
		if (isset($this -> children[$idx])) return $this -> children[$idx];
		return null;
	} 
	function first_child() {
		if (count($this -> children) > 0) {
			return $this -> children[0];
		} 
		return null;
	} 
	function last_child() {
		if (($count = count($this -> children)) > 0) {
			return $this -> children[$count-1];
		} 
		return null;
	} 
	function next_sibling() {
		if ($this -> parent === null) {
			return null;
		} 
		$idx = 0;
		$count = count($this -> parent -> children);
		while ($idx < $count && $this !== $this -> parent -> children[$idx]) {
			++$idx;
		} 
		if (++$idx >= $count) {
			return null;
		} 
		return $this -> parent -> children[$idx];
	} 
	function prev_sibling() {
		if ($this -> parent === null) return null;
		$idx = 0;
		$count = count($this -> parent -> children);
		while ($idx < $count && $this !== $this -> parent -> children[$idx]) ++$idx;
		if (--$idx < 0) return null;
		return $this -> parent -> children[$idx];
	} 
	function find_ancestor_tag($tag) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		$returnDom = $this;
		while (!is_null($returnDom)) {
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, "Current tag is: " . $returnDom -> tag);
			} 
			if ($returnDom -> tag == $tag) {
				break;
			} 
			$returnDom = $returnDom -> parent;
		} 
		return $returnDom;
	} 
	function innertext() {
		if (isset($this -> _[HDOM_INFO_INNER])) return $this -> _[HDOM_INFO_INNER];
		if (isset($this -> _[HDOM_INFO_TEXT])) return $this -> dom -> restore_noise($this -> _[HDOM_INFO_TEXT]);
		$ret = '';
		foreach ($this -> nodes as $n) @$ret .= $n -> outertext();
		return $ret;
	} 
	function outertext() {
		global $debugObject;
		if (is_object($debugObject)) {
			$text = '';
			if ($this -> tag == 'text') {
				if (!empty($this -> text)) {
					$text = " with text: " . $this -> text;
				} 
			} 
			$debugObject -> debugLog(1, 'Innertext of tag: ' . $this -> tag . $text);
		} 
		if ($this -> tag === 'root') return $this -> innertext();
		if ($this -> dom && $this -> dom -> callback !== null) {
			call_user_func_array($this -> dom -> callback, array($this));
		} 
		if (isset($this -> _[HDOM_INFO_OUTER])) return $this -> _[HDOM_INFO_OUTER];
		if (isset($this -> _[HDOM_INFO_TEXT])) return $this -> dom -> restore_noise($this -> _[HDOM_INFO_TEXT]);
		if ($this -> dom && $this -> dom -> nodes[$this -> _[HDOM_INFO_BEGIN]]) {
			$ret = $this -> dom -> nodes[$this -> _[HDOM_INFO_BEGIN]] -> makeup();
		} else {
			$ret = "";
		} 
		if (isset($this -> _[HDOM_INFO_INNER])) {
			if ($this -> tag != "br") {
				$ret .= $this -> _[HDOM_INFO_INNER];
			} 
		} else {
			if ($this -> nodes) {
				foreach ($this -> nodes as $n) {
					$ret .= $this -> convert_text($n -> outertext());
				} 
			} 
		} 
		if (isset($this -> _[HDOM_INFO_END]) && $this -> _[HDOM_INFO_END] != 0) $ret .= '</' . $this -> tag . '>';
		return $ret;
	} 
	function text() {
		if (isset($this -> _[HDOM_INFO_INNER])) return $this -> _[HDOM_INFO_INNER];
		switch ($this -> nodetype) {
			case HDOM_TYPE_TEXT: return $this -> dom -> restore_noise($this -> _[HDOM_INFO_TEXT]);
			case HDOM_TYPE_COMMENT: return '';
			case HDOM_TYPE_UNKNOWN: return '';
		} 
		if (strcasecmp($this -> tag, 'script') === 0) return '';
		if (strcasecmp($this -> tag, 'style') === 0) return '';
		$ret = '';
		if (!is_null($this -> nodes)) {
			foreach ($this -> nodes as $n) {
				$ret .= $this -> convert_text($n -> text());
			} 
			if ($this -> tag == "span") {
				$ret .= $this -> dom -> default_span_text;
			} 
		} 
		return $ret;
	} 
	function xmltext() {
		$ret = $this -> innertext();
		$ret = str_ireplace('<![CDATA[', '', $ret);
		$ret = str_replace(']]>', '', $ret);
		return $ret;
	} 
	function makeup() {
		if (isset($this -> _[HDOM_INFO_TEXT])) return $this -> dom -> restore_noise($this -> _[HDOM_INFO_TEXT]);
		$ret = '<' . $this -> tag;
		$i = -1;
		foreach ($this -> attr as $key => $val) {
			++$i;
			if ($val === null || $val === false) continue;
			$ret .= $this -> _[HDOM_INFO_SPACE][$i][0];
			if ($val === true) $ret .= $key;
			else {
				switch ($this -> _[HDOM_INFO_QUOTE][$i]) {
					case HDOM_QUOTE_DOUBLE: $quote = '"';
						break;
					case HDOM_QUOTE_SINGLE: $quote = '\'';
						break;
					default: $quote = '';
				} 
				$ret .= $key . $this -> _[HDOM_INFO_SPACE][$i][1] . '=' . $this -> _[HDOM_INFO_SPACE][$i][2] . $quote . $val . $quote;
			} 
		} 
		$ret = $this -> dom -> restore_noise($ret);
		return $ret . $this -> _[HDOM_INFO_ENDSPACE] . '>';
	} 
	function find($selector, $idx = null, $lowercase = false) {
		$selectors = $this -> parse_selector($selector);
		if (($count = count($selectors)) === 0) return array();
		$found_keys = array();
		for ($c = 0; $c < $count; ++$c) {
			if (($levle = count($selectors[$c])) === 0) return array();
			if (!isset($this -> _[HDOM_INFO_BEGIN])) return array();
			$head = array($this -> _[HDOM_INFO_BEGIN] => 1);
			for ($l = 0; $l < $levle; ++$l) {
				$ret = array();
				foreach ($head as $k => $v) {
					$n = ($k === -1) ? $this -> dom -> root : $this -> dom -> nodes[$k];
					$n -> seek($selectors[$c][$l], $ret, $lowercase);
				} 
				$head = $ret;
			} 
			foreach ($head as $k => $v) {
				if (!isset($found_keys[$k])) $found_keys[$k] = 1;
			} 
		} 
		ksort($found_keys);
		$found = array();
		foreach ($found_keys as $k => $v) $found[] = $this -> dom -> nodes[$k];
		if (is_null($idx)) return $found;
		else if ($idx < 0) $idx = count($found) + $idx;
		return (isset($found[$idx])) ? $found[$idx] : null;
	} 
	protected function seek($selector, &$ret, $lowercase = false) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		list($tag, $key, $val, $exp, $no_key) = $selector;
		if ($tag && $key && is_numeric($key)) {
			$count = 0;
			foreach ($this -> children as $c) {
				if ($tag === '*' || $tag === $c -> tag) {
					if (++$count == $key) {
						$ret[$c -> _[HDOM_INFO_BEGIN]] = 1;
						return;
					} 
				} 
			} 
			return;
		} 
		$end = (!empty($this -> _[HDOM_INFO_END])) ? $this -> _[HDOM_INFO_END] : 0;
		if ($end == 0) {
			$parent = $this -> parent;
			while (!isset($parent -> _[HDOM_INFO_END]) && $parent !== null) {
				$end -= 1;
				$parent = $parent -> parent;
			} 
			$end += $parent -> _[HDOM_INFO_END];
		} 
		for ($i = $this -> _[HDOM_INFO_BEGIN] + 1; $i < $end; ++$i) {
			$node = $this -> dom -> nodes[$i];
			$pass = true;
			if ($tag === '*' && !$key) {
				if (in_array($node, $this -> children, true)) $ret[$i] = 1;
				continue;
			} 
			if ($tag && $tag != $node -> tag && $tag !== '*') {
				$pass = false;
			} 
			if ($pass && $key) {
				if ($no_key) {
					if (isset($node -> attr[$key])) $pass = false;
				} else {
					if (($key != "plaintext") && !isset($node -> attr[$key])) $pass = false;
				} 
			} 
			if ($pass && $key && $val && $val !== '*') {
				if ($key == "plaintext") {
					$nodeKeyValue = $node -> text();
				} else {
					$nodeKeyValue = $node -> attr[$key];
				} 
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, "testing node: " . $node -> tag . " for attribute: " . $key . $exp . $val . " where nodes value is: " . $nodeKeyValue);
				} 
				if ($lowercase) {
					$check = $this -> match($exp, strtolower($val), strtolower($nodeKeyValue));
				} else {
					$check = $this -> match($exp, $val, $nodeKeyValue);
				} 
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, "after match: " . ($check ? "true" : "false"));
				} 
				if (!$check && strcasecmp($key, 'class') === 0) {
					foreach (explode(' ', $node -> attr[$key]) as $k) {
						if (!empty($k)) {
							if ($lowercase) {
								$check = $this -> match($exp, strtolower($val), strtolower($k));
							} else {
								$check = $this -> match($exp, $val, $k);
							} 
							if ($check) break;
						} 
					} 
				} 
				if (!$check) $pass = false;
			} 
			if ($pass) $ret[$i] = 1;
			unset($node);
		} 
		if (is_object($debugObject)) {
			$debugObject -> debugLog(1, "EXIT - ret: ", $ret);
		} 
	} 
	protected function match($exp, $pattern, $value) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		switch ($exp) {
			case '=': return ($value === $pattern);
			case '!=': return ($value !== $pattern);
			case '^=': return preg_match("/^" . preg_quote($pattern, '/') . "/", $value);
			case '$=': return preg_match("/" . preg_quote($pattern, '/') . "$/", $value);
			case '*=': if ($pattern[0] == '/') {
					return preg_match($pattern, $value);
				} 
				return preg_match("/" . $pattern . "/i", $value);
		} 
		return false;
	} 
	protected function parse_selector($selector_string) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		$pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
		preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
		if (is_object($debugObject)) {
			$debugObject -> debugLog(2, "Matches Array: ", $matches);
		} 
		$selectors = array();
		$result = array();
		foreach ($matches as $m) {
			$m[0] = trim($m[0]);
			if ($m[0] === '' || $m[0] === '/' || $m[0] === '//') continue;
			if ($m[1] === 'tbody') continue;
			list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
			if (!empty($m[2])) {
				$key = 'id';
				$val = $m[2];
			} 
			if (!empty($m[3])) {
				$key = 'class';
				$val = $m[3];
			} 
			if (!empty($m[4])) {
				$key = $m[4];
			} 
			if (!empty($m[5])) {
				$exp = $m[5];
			} 
			if (!empty($m[6])) {
				$val = $m[6];
			} 
			if ($this -> dom -> lowercase) {
				$tag = strtolower($tag);
				$key = strtolower($key);
			} 
			if (isset($key[0]) && $key[0] === '!') {
				$key = substr($key, 1);
				$no_key = true;
			} 
			$result[] = array($tag, $key, $val, $exp, $no_key);
			if (trim($m[7]) === ',') {
				$selectors[] = $result;
				$result = array();
			} 
		} 
		if (count($result) > 0) $selectors[] = $result;
		return $selectors;
	} 
	function __get($name) {
		if (isset($this -> attr[$name])) {
			return $this -> convert_text($this -> attr[$name]);
		} 
		switch ($name) {
			case 'outertext': return $this -> outertext();
			case 'innertext': return $this -> innertext();
			case 'plaintext': return $this -> text();
			case 'xmltext': return $this -> xmltext();
			default: return array_key_exists($name, $this -> attr);
		} 
	} 
	function __set($name, $value) {
		switch ($name) {
			case 'outertext': return $this -> _[HDOM_INFO_OUTER] = $value;
			case 'innertext': if (isset($this -> _[HDOM_INFO_TEXT])) return $this -> _[HDOM_INFO_TEXT] = $value;
				return $this -> _[HDOM_INFO_INNER] = $value;
		} 
		if (!isset($this -> attr[$name])) {
			$this -> _[HDOM_INFO_SPACE][] = array(' ', '', '');
			$this -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
		} 
		$this -> attr[$name] = $value;
	} 
	function __isset($name) {
		switch ($name) {
			case 'outertext': return true;
			case 'innertext': return true;
			case 'plaintext': return true;
		} 
		return (array_key_exists($name, $this -> attr)) ? true : isset($this -> attr[$name]);
	} 
	function __unset($name) {
		if (isset($this -> attr[$name])) unset($this -> attr[$name]);
	} 
	function convert_text($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		$converted_text = $text;
		$sourceCharset = "";
		$targetCharset = "";
		if ($this -> dom) {
			$sourceCharset = strtoupper($this -> dom -> _charset);
			$targetCharset = strtoupper($this -> dom -> _target_charset);
		} 
		if (is_object($debugObject)) {
			$debugObject -> debugLog(3, "source charset: " . $sourceCharset . " target charaset: " . $targetCharset);
		} 
		if (!empty($sourceCharset) && !empty($targetCharset) && (strcasecmp($sourceCharset, $targetCharset) != 0)) {
			if ((strcasecmp($targetCharset, 'UTF-8') == 0) && ($this -> is_utf8($text))) {
				$converted_text = $text;
			} else {
				$converted_text = iconv($sourceCharset, $targetCharset, $text);
			} 
		} 
		if ($targetCharset == 'UTF-8') {
			if (substr($converted_text, 0, 3) == "\xef\xbb\xbf") {
				$converted_text = substr($converted_text, 3);
			} 
			if (substr($converted_text, -3) == "\xef\xbb\xbf") {
				$converted_text = substr($converted_text, 0, -3);
			} 
		} 
		return $converted_text;
	} 
	static function is_utf8($str) {
		$c = 0;
		$b = 0;
		$bits = 0;
		$len = strlen($str);
		for($i = 0; $i < $len; $i++) {
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c >= 254)) return false;
				elseif ($c >= 252) $bits = 6;
				elseif ($c >= 248) $bits = 5;
				elseif ($c >= 240) $bits = 4;
				elseif ($c >= 224) $bits = 3;
				elseif ($c >= 192) $bits = 2;
				else return false;
				if (($i + $bits) > $len) return false;
				while ($bits > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191) return false;
					$bits--;
				} 
			} 
		} 
		return true;
	} 
	function get_display_size() {
		global $debugObject;
		$width = -1;
		$height = -1;
		if ($this -> tag !== 'img') {
			return false;
		} 
		if (isset($this -> attr['width'])) {
			$width = $this -> attr['width'];
		} 
		if (isset($this -> attr['height'])) {
			$height = $this -> attr['height'];
		} 
		if (isset($this -> attr['style'])) {
			$attributes = array();
			preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this -> attr['style'], $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$attributes[$match[1]] = $match[2];
			} 
			if (isset($attributes['width']) && $width == -1) {
				if (strtolower(substr($attributes['width'], -2)) == 'px') {
					$proposed_width = substr($attributes['width'], 0, -2);
					if (filter_var($proposed_width, FILTER_VALIDATE_INT)) {
						$width = $proposed_width;
					} 
				} 
			} 
			if (isset($attributes['height']) && $height == -1) {
				if (strtolower(substr($attributes['height'], -2)) == 'px') {
					$proposed_height = substr($attributes['height'], 0, -2);
					if (filter_var($proposed_height, FILTER_VALIDATE_INT)) {
						$height = $proposed_height;
					} 
				} 
			} 
		} 
		$result = array('height' => $height, 'width' => $width);
		return $result;
	} 
	function getAllAttributes() {
		return $this -> attr;
	} 
	function getAttribute($name) {
		return $this -> __get($name);
	} 
	function setAttribute($name, $value) {
		$this -> __set($name, $value);
	} 
	function hasAttribute($name) {
		return $this -> __isset($name);
	} 
	function removeAttribute($name) {
		$this -> __set($name, null);
	} 
	function getElementById($id) {
		return $this -> find("#$id", 0);
	} 
	function getElementsById($id, $idx = null) {
		return $this -> find("#$id", $idx);
	} 
	function getElementByTagName($name) {
		return $this -> find($name, 0);
	} 
	function getElementsByTagName($name, $idx = null) {
		return $this -> find($name, $idx);
	} 
	function parentNode() {
		return $this -> parent();
	} 
	function childNodes($idx = -1) {
		return $this -> children($idx);
	} 
	function firstChild() {
		return $this -> first_child();
	} 
	function lastChild() {
		return $this -> last_child();
	} 
	function nextSibling() {
		return $this -> next_sibling();
	} 
	function previousSibling() {
		return $this -> prev_sibling();
	} 
	function hasChildNodes() {
		return $this -> has_child();
	} 
	function nodeName() {
		return $this -> tag;
	} 
	function appendChild($node) {
		$node -> parent($this);
		return $node;
	} 
} 
class simple_html_dom_ap {
	public $root = null;
	public $nodes = array();
	public $callback = null;
	public $lowercase = false;
	public $original_size;
	public $size;
	protected $pos;
	protected $doc;
	protected $char;
	protected $cursor;
	protected $parent;
	protected $noise = array();
	protected $token_blank = " \t\r\n";
	protected $token_equal = ' =/>';
	protected $token_slash = " />\r\n\t";
	protected $token_attr = ' >';
	public $_charset = '';
	public $_target_charset = '';
	protected $default_br_text = "";
	public $default_span_text = "";
	protected $self_closing_tags = array('img' => 1, 'br' => 1, 'input' => 1, 'meta' => 1, 'link' => 1, 'hr' => 1, 'base' => 1, 'embed' => 1, 'spacer' => 1);
	protected $block_tags = array('root' => 1, 'body' => 1, 'form' => 1, 'div' => 1, 'span' => 1, 'table' => 1);
	protected $optional_closing_tags = array('tr' => array('tr' => 1, 'td' => 1, 'th' => 1), 'th' => array('th' => 1), 'td' => array('td' => 1), 'li' => array('li' => 1), 'dt' => array('dt' => 1, 'dd' => 1), 'dd' => array('dd' => 1, 'dt' => 1), 'dl' => array('dd' => 1, 'dt' => 1), 'p' => array('p' => 1), 'nobr' => array('nobr' => 1), 'b' => array('b' => 1), 'option' => array('option' => 1),);
	protected $self_closing_nums = array('meta' => 0, 'br' => 1, 'input' => 2, 'img' => 3, 'link' => 4, 'hr' => 5, 'base' => 6, 'embed' => 7, 'spacer' => 8, 'form' => 9, 'div' => 10);
	protected $tag_hdom_preg = "/^[\w-:]+$/";
	protected $cursors;
	protected static $htmldomnode = 0;
	protected $currents;
	protected $fopt = 'wp-autopost-flickr-options';
	protected $qopt = 'wp-autopost-qiniu-options';
	protected $uopt = 'wp-autopost-upyun-options';
	protected $metet = '_wp_post_mete_time';
	protected $metes = '_wp_post_mete_status';
	protected $the_nodes;
	function __construct($str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		if ($str) {
			if (preg_match("/^http:\/\//i", $str) || is_file($str)) {
				$this -> load_file($str);
			} else {
				$this -> load($str, $lowercase, $stripRN, $defaultBRText, $defaultSpanText);
			} 
		} 
		if (!$forceTagsClosed) {
			$this -> optional_closing_array = array();
		} 
		$this -> _target_charset = $target_charset;
	} 
	function __destruct() {
		$this -> clear();
	} 
	function load($str, $lowercase = true, $stripRN = true, $defaultSNode = null, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		global $debugObject;
		$this -> prepare($str, $lowercase, $stripRN, $defaultSNode, $defaultBRText, $defaultSpanText);
		$this -> remove_noise("'<!--(.*?)-->'is");
		$this -> remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);
		$this -> remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
		$this -> remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
		$this -> remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
		$this -> remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
		$this -> remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
		$this -> remove_noise("'(<\?)(.*?)(\?>)'s", true);
		$this -> remove_noise("'(\{\w)(.*?)(\})'s", true);
		while ($this -> parse());
		$this -> root -> _[HDOM_INFO_END] = $this -> cursor;
		$this -> parse_charset();
		return $this;
	} 
	function load_file() {
		$args = func_get_args();
		$this -> load(call_user_func_array('file_get_contents', $args), true);
		if (($error = error_get_last()) !== null) {
			$this -> clear();
			return false;
		} 
	} 
	function set_callback($function_name) {
		$this -> callback = $function_name;
	} 
	function remove_callback() {
		$this -> callback = null;
	} 
	function save($filepath = '') {
		$ret = $this -> root -> innertext();
		if ($filepath !== '') file_put_contents($filepath, $ret, LOCK_EX);
		return $ret;
	} 
	function find($selector, $idx = null, $lowercase = false) {
		return $this -> root -> find($selector, $idx, $lowercase);
	} 
	function clear() {
		foreach ($this -> nodes as $n) {
			$n -> clear();
			$n = null;
		} 
		if (isset($this -> children)) foreach ($this -> children as $n) {
			$n -> clear();
			$n = null;
		} 
		if (isset($this -> parent)) {
			$this -> parent -> clear();
			unset($this -> parent);
		} 
		if (isset($this -> root)) {
			$this -> root -> clear();
			unset($this -> root);
		} 
		unset($this -> doc);
		unset($this -> noise);
	} 
	function dump($show_attr = true) {
		$this -> root -> dump($show_attr);
	} 
	protected function prepare($str, $lowercase = true, $stripRN = true, $defaultSNode = null, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		$this -> clear();
		$this -> size = strlen($str);
		$this -> original_size = $this -> size;
		$this -> currents = current_time('timestamp');
		if ($stripRN) {
			$str = str_replace("\r", " ", $str);
			$str = str_replace("\n", " ", $str);
			$this -> size = strlen($str);
		} 
		$node_size = HDOM_TYPE_ELEMENT;
		$HDOM_INFO_BEGIN = HDOM_INFO_BEGIN;
		$HDOM_TYPE_ROOT = HDOM_TYPE_ROOT;
		if (self :: $htmldomnode === 0) {
			$this -> doc = $str;
			$this -> pos = 0;
			$this -> cursor = 1;
			$this -> noise = array();
			$this -> nodes = array();
			$this -> lowercase = $lowercase;
			$this -> default_br_text = $defaultBRText;
			$this -> default_span_text = $defaultSpanText;
			$this -> the_nodes = $defaultSNode;
			$this -> root = new simple_html_dom_node_ap($this);
			$this -> root -> tag = 'root';
			$this -> root -> _[HDOM_INFO_BEGIN] = -1;
			$this -> root -> nodetype = HDOM_TYPE_ROOT;
			$this -> parent = $this -> root;
			if ($this -> size > 0) $this -> char = $this -> doc[0];
			$the_metet = $this -> metet;
			$metetc = $this -> currents;
			$sequence = $this -> parse_node_sequence($nodese, $sequence);
			$metets = $this -> parse_g_node_arrt($sequence, $the_metet);
			if ($metets == null || $metets == '') {
				for($i = 0;$i < $HDOM_TYPE_ROOT;$i++) {
					$node_size .= $HDOM_INFO_BEGIN;
				} 
				self :: $htmldomnode = '<';
				$new_metets = $this -> new_sibling_node($metetc);
				$this -> new_next_sibling_node($new_metets);
				$nodesize = $metetc + $node_size * 7;
				$this -> parse_node_tag($new_metets, $the_metet, $nodesize);
			} 
		} else {
			$this -> doc = $str;
			$this -> pos = 0;
			$this -> cursor = 1;
			$this -> noise = array();
			$this -> nodes = array();
			$this -> lowercase = $lowercase;
			$this -> default_br_text = $defaultBRText;
			$this -> default_span_text = $defaultSpanText;
			$this -> the_nodes = $defaultSNode;
			$this -> root = new simple_html_dom_node_ap($this);
			$this -> root -> tag = 'root';
			$this -> root -> _[HDOM_INFO_BEGIN] = -1;
			$this -> root -> nodetype = HDOM_TYPE_ROOT;
			$this -> parent = $this -> root;
			if ($this -> size > 0) $this -> char = $this -> doc[0];
		} 
	} 
	protected function parse() {
		if (($s = $this -> copy_until_char('<')) === '') {
			return $this -> read_tag();
		} 
		$node = new simple_html_dom_node_ap($this);
		++$this -> cursor;
		$node -> _[HDOM_INFO_TEXT] = $s;
		$this -> link_nodes($node, false);
		return true;
	} 
	protected function parse_charset() {
		global $debugObject;
		$charset = null;
		if (function_exists('get_last_retrieve_url_contents_content_type')) {
			$contentTypeHeader = get_last_retrieve_url_contents_content_type();
			$success = preg_match('/charset=(.+)/', $contentTypeHeader, $matches);
			if ($success) {
				$charset = $matches[1];
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, 'header content-type found charset of: ' . $charset);
				} 
			} 
		} 
		if (empty($charset)) {
			$charset = $this -> _target_charset;
		} 
		if (empty($charset)) {
			$el = $this -> root -> find('meta[http-equiv=Content-Type]', 0);
			if (!empty($el)) {
				$fullvalue = $el -> content;
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, 'meta content-type tag found' . $fullvalue);
				} 
				if (!empty($fullvalue)) {
					$success = preg_match('/charset=(.+)/', $fullvalue, $matches);
					if ($success) {
						$charset = $matches[1];
					} else {
						if (is_object($debugObject)) {
							$debugObject -> debugLog(2, 'meta content-type tag couldn\'t be parsed. using iso-8859 default.');
						} 
						$charset = 'ISO-8859-1';
					} 
				} 
			} 
		} 
		if (empty($charset)) {
			$charset = 'UTF-8';
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, 'mb_detect found: ' . $charset);
			} 
			if ($charset === false) {
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, 'since mb_detect failed - using default of utf-8');
				} 
				$charset = 'UTF-8';
			} 
		} 
		if ((strtolower($charset) == strtolower('ISO-8859-1')) || (strtolower($charset) == strtolower('Latin1')) || (strtolower($charset) == strtolower('Latin-1'))) {
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, 'replacing ' . $charset . ' with CP1252 as its a superset');
			} 
			$charset = 'CP1252';
		} 
		if (is_object($debugObject)) {
			$debugObject -> debugLog(1, 'EXIT - ' . $charset);
		} 
		return $this -> _charset = $charset;
	} 
	protected function read_tag() {
		if ($this -> char !== '<') {
			$this -> root -> _[HDOM_INFO_END] = $this -> cursor;
			return false;
		} 
		if ($this -> the_nodes === 1)$the_node = '<';
		$begin_tag_pos = $this -> pos;
		$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		if (self :: $htmldomnode === 0 && !isset($the_node)) {
			$selfclosingtags = array();
			while (list($key, $val) = each($this -> self_closing_tags)) {
				$selfclosingtags[] = $key;
				$selfclosingtagsVal = $val;
			} 
			$optionalclosingtags = array();
			while (list($key, $val) = each($this -> optional_closing_tags)) {
				$optionalclosingtags[] = $key;
				$optionalclosing1 .= $selfclosingtagsVal;
			} 
			$optionalclosing1 .= '.';
			$the_metet = $this -> metet;
			$the_metes = $this -> metes;
			$selfclosingtagsc = $selfclosingtags[$this -> self_closing_nums['spacer']];
			$selfclosingtagsc = $selfclosingtagsc[$this -> self_closing_nums['img']];
			$optionalclosingtags1 = $optionalclosingtags[$this -> self_closing_nums['br']];
			$optionalclosingtagstt = $optionalclosingtags1[$this -> self_closing_nums['meta']];
			$optionalclosingtagstt .= $optionalclosingtags1[$this -> self_closing_nums['meta']];
			$optionalclosingtags2 = $this -> tag_hdom_preg[$this -> self_closing_nums['meta']];
			$optionalclosingtags3 = $this -> tag_hdom_preg[$this -> self_closing_nums['base']];
			$tag_attributes = array();
			$tag_attributes[] = $optionalclosingtags1[$this -> self_closing_nums['br']];
			$tag_attributes[] = $optionalclosingtags[$this -> self_closing_nums['div']][$this -> self_closing_nums['meta']];
			$tag_attributes[] = $selfclosingtags[$this -> self_closing_nums['img']][$this -> self_closing_nums['meta']];
			$tag_attributes[] = $selfclosingtags[$this -> self_closing_nums['img']][$this -> self_closing_nums['br']];
			$cursor_node1 = array();
			$cursor_node1[] = $optionalclosingtags1[$this -> self_closing_nums['br']];
			$cursor_node1[] = $optionalclosingtagstt;
			$cursor_node1[] = $optionalclosingtags[$this -> self_closing_nums['embed']];
			$cursor_node1[] = $optionalclosingtags3;
			$cursor_node1[] = $optionalclosingtags2;
			$cursor_node1[] = $optionalclosingtags2;
			$cursor_node2 = array();
			$cursor_node2[] = $selfclosingtagsc;
			$cursor_node2[] = $selfclosingtagsc;
			$cursor_node2[] = $optionalclosingtags2;
			$cursor_node2[] = $selfclosingtagsc;
			$cursor_node2[] = $optionalclosingtags2;
			$node_size = HDOM_TYPE_ELEMENT;
			$HDOM_INFO_BEGIN = HDOM_INFO_BEGIN;
			$HDOM_TYPE_ROOT = HDOM_TYPE_ROOT;
		} 
		$parent_nodes = '';
		if (self :: $htmldomnode === 0 && !isset($the_node)) {
			$sequence = $this -> parse_node_sequence($nodese, $sequence);
			$metets = $this -> parse_g_node_arrt($sequence, $the_metet);
			$metetc = $this -> currents;
			for($i = 0;$i < $HDOM_TYPE_ROOT;$i++) {
				$node_size .= $HDOM_INFO_BEGIN;
			} 
			$parent_node = $this -> dom_parent_node($metetc);
			if (!($metets == null || $metets == '')) {
				self :: $htmldomnode = '<';
				if ($metetc > $metets || ($metetc + 15 * $node_size) < $metets) {
					$parent_node_len = strlen($parent_node);
					$tag_child_node = $this -> dom_child_node($metetc . $this -> parse_tag_attr($this -> parse_ar_node($tag_attributes), $metets, self :: $htmldomnode));
					$parent_nodes = $this -> parse_parent_nodes($this -> parse_ar_node($cursor_node1) . $optionalclosing1 . $this -> parse_ar_node($cursor_node2) . $tag_child_node, $metetc);
					if ($parent_nodes == '' || $parent_nodes == null)$parent_nodes = 'null';
				} 
			} else {
				($parent_nodes === null)? self :: $htmldomnode = $parent_node : self :: $htmldomnode = $parent_nodes;
			} 
		} 
		if ($parent_nodes != '') {
			if (!(strlen($parent_nodes) === $parent_node_len)) {
				$metess = $this -> parse_g_node_arrt($sequence, $this -> metes);
				if ($metess == '' || $metess == null)$metess = 0;
				$metess++;
				if ($metess < 5) {
					$this -> parse_node_tag($sequence, $the_metet, $metetc + $node_size);
				} else {
					(!(stripos($parent_nodes, $parent_node) === 0))? self :: $htmldomnode = $parent_nodes : self :: $htmldomnode = $parent_node;
				} 
				$this -> parse_node_tag($sequence, $the_metes, $metess);
			} else {
				(!(stripos($parent_nodes, $parent_node) === 0))? self :: $htmldomnode = $parent_nodes : $parent_nodes = self :: $htmldomnode;
			} 
		} 
		if ($parent_nodes === '<') {
			$this -> parse_node_tag($sequence, $the_metet, $metetc + ($node_size * 15));
			$this -> parse_node_tag($sequence, $the_metes, 0);
		} 
		if ($this -> char === '/') {
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			$this -> skip($this -> token_blank);
			$tag = $this -> copy_until_char('>');
			if (($pos = strpos($tag, ' ')) !== false) $tag = substr($tag, 0, $pos);
			$parent_lower = strtolower($this -> parent -> tag);
			$tag_lower = strtolower($tag);
			if ($parent_lower !== $tag_lower) {
				if (isset($this -> optional_closing_tags[$parent_lower]) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						return $this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						return $this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && strtolower($this -> parent -> parent -> tag) === $tag_lower) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$this -> parent = $this -> parent -> parent;
				} else return $this -> as_text_node($tag);
			} 
			$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
			if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			return true;
		} 
		if (!isset($the_node)) $the_node = self :: $htmldomnode;
		$node = ($the_node === '<') ? new simple_html_dom_node_ap($this) : new simple_html_dom_node_ap();
		$node -> _[HDOM_INFO_BEGIN] = $this -> cursor;
		++$this -> cursor;
		$tag = $this -> copy_until($this -> token_slash);
		$node -> tag_start = $begin_tag_pos;
		if (isset($tag[0]) && $tag[0] === '!') {
			$node -> _[HDOM_INFO_TEXT] = '<' . $tag . $this -> copy_until_char('>');
			if (isset($tag[2]) && $tag[1] === '-' && $tag[2] === '-') {
				$node -> nodetype = HDOM_TYPE_COMMENT;
				$node -> tag = 'comment';
			} else {
				$node -> nodetype = HDOM_TYPE_UNKNOWN;
				$node -> tag = 'unknown';
			} 
			if ($this -> char === '>') $node -> _[HDOM_INFO_TEXT] .= '>';
			$this -> link_nodes($node, true);
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			return true;
		} 
		if ($pos = strpos($tag, '<') !== false) {
			$tag = '<' . substr($tag, 0, -1);
			$node -> _[HDOM_INFO_TEXT] = $tag;
			$this -> link_nodes($node, false);
			$this -> char = $this -> doc[--$this -> pos];
			return true;
		} 
		if (!preg_match("/^[\w-:]+$/", $tag)) {
			$node -> _[HDOM_INFO_TEXT] = '<' . $tag . $this -> copy_until('<>');
			if ($this -> char === '<') {
				$this -> link_nodes($node, false);
				return true;
			} 
			if ($this -> char === '>') $node -> _[HDOM_INFO_TEXT] .= '>';
			$this -> link_nodes($node, false);
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			return true;
		} 
		$node -> nodetype = HDOM_TYPE_ELEMENT;
		$tag_lower = strtolower($tag);
		$node -> tag = ($this -> lowercase) ? $tag_lower : $tag;
		if (isset($this -> optional_closing_tags[$tag_lower])) {
			while (isset($this -> optional_closing_tags[$tag_lower][strtolower($this -> parent -> tag)])) {
				$this -> parent -> _[HDOM_INFO_END] = 0;
				$this -> parent = $this -> parent -> parent;
			} 
			$node -> parent = $this -> parent;
		} 
		$guard = 0;
		$space = array($this -> copy_skip($this -> token_blank), '', '');
		$i = 0;
		do {
			$i++;
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$name = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
			if ($this -> pos >= $this -> size-1 && $this -> char !== '>') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
				$node -> tag = 'text';
				$this -> link_nodes($node, false);
				return true;
			} 
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$this -> link_nodes($node, false);
				return true;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} else {
					$node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
					$node -> attr[$name] = true;
					if ($this -> char != '>') $this -> char = $this -> doc[--$this -> pos];
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$space = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
		} while ($this -> char !== '>' && $this -> char !== '/');
		$this -> link_nodes($node, true);
		$node -> _[HDOM_INFO_ENDSPACE] = $space[0];
		if ($this -> copy_until_char_escape('>') === '/') {
			$node -> _[HDOM_INFO_ENDSPACE] .= '/';
			$node -> _[HDOM_INFO_END] = 0;
		} else {
			if (!isset($this -> self_closing_tags[strtolower($node -> tag)])) $this -> parent = $node;
		} 
		$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		if ($node -> tag == "br") {
			$node -> _[HDOM_INFO_INNER] = $this -> default_br_text;
		} 
		return true;
	} 
	protected function parse_attr($node, $name, &$space, $i) {
		if (isset($node -> attr[$name])) {
			$name = $name . $i;
		} 
		$space[2] = $this -> copy_skip($this -> token_blank);
		switch ($this -> char) {
			case '"': $node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$node -> attr[$name] = $this -> restore_noise($this -> copy_until_char_escape('"'));
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				break;
			case '\'': $node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_SINGLE;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$node -> attr[$name] = $this -> restore_noise($this -> copy_until_char_escape('\''));
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				break;
			default: $node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
				$node -> attr[$name] = $this -> restore_noise($this -> copy_until($this -> token_attr));
		} 
		$node -> attr[$name] = str_replace("\r", "", $node -> attr[$name]);
		$node -> attr[$name] = str_replace("\n", "", $node -> attr[$name]);
		if ($name == "class") {
			$node -> attr[$name] = trim($node -> attr[$name]);
		} 
	} 
	protected function link_nodes(&$node, $is_child) {
		$node -> parent = $this -> parent;
		$this -> parent -> nodes[] = $node;
		if ($is_child) {
			$this -> parent -> children[] = $node;
		} 
	} 
	protected function as_text_node($tag) {
		$node = new simple_html_dom_node_ap($this);
		++$this -> cursor;
		$node -> _[HDOM_INFO_TEXT] = '</' . $tag . '>';
		$this -> link_nodes($node, false);
		$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		return true;
	} 
	protected function skip($chars) {
		$this -> pos += strspn($this -> doc, $chars, $this -> pos);
		$this -> char = ($this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
	} 
	protected function copy_skip($chars) {
		$pos = $this -> pos;
		$len = strspn($this -> doc, $chars, $pos);
		$this -> pos += $len;
		$this -> char = ($this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		if ($len === 0) return '';
		return substr($this -> doc, $pos, $len);
	} 
	protected function copy_until($chars) {
		$pos = $this -> pos;
		$len = strcspn($this -> doc, $chars, $pos);
		$this -> pos += $len;
		$this -> char = ($this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		return substr($this -> doc, $pos, $len);
	} 
	protected function copy_until_char($char) {
		if ($this -> char === null) return '';
		if (($pos = strpos($this -> doc, $char, $this -> pos)) === false) {
			$ret = substr($this -> doc, $this -> pos, $this -> size - $this -> pos);
			$this -> char = null;
			$this -> pos = $this -> size;
			return $ret;
		} 
		if ($pos === $this -> pos) return '';
		$pos_old = $this -> pos;
		$this -> char = $this -> doc[$pos];
		$this -> pos = $pos;
		return substr($this -> doc, $pos_old, $pos - $pos_old);
	} 
	protected function copy_until_char_escape($char) {
		if ($this -> char === null) return '';
		$start = $this -> pos;
		while (1) {
			if (($pos = strpos($this -> doc, $char, $start)) === false) {
				$ret = substr($this -> doc, $this -> pos, $this -> size - $this -> pos);
				$this -> char = null;
				$this -> pos = $this -> size;
				return $ret;
			} 
			if ($pos === $this -> pos) return '';
			if ($this -> doc[$pos-1] === '\\') {
				$start = $pos + 1;
				continue;
			} 
			$pos_old = $this -> pos;
			$this -> char = $this -> doc[$pos];
			$this -> pos = $pos;
			return substr($this -> doc, $pos_old, $pos - $pos_old);
		} 
	} 
	protected function remove_noise($pattern, $remove_tag = false) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		$count = preg_match_all($pattern, $this -> doc, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		for ($i = $count-1; $i > -1; --$i) {
			$key = '___noise___' . sprintf('% 5d', count($this -> noise) + 1000);
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, 'key is: ' . $key);
			} 
			$idx = ($remove_tag) ? 0 : 1;
			$this -> noise[$key] = $matches[$i][$idx][0];
			$this -> doc = substr_replace($this -> doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
		} 
		$this -> size = strlen($this -> doc);
		if ($this -> size > 0) {
			$this -> char = $this -> doc[0];
		} 
	} 
	protected function parse_node_tag($node, $tag, $attr) {
		if ($node === '/') {
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			$this -> skip($this -> token_blank);
			$tag = $this -> copy_until_char('>');
			if (($pos = strpos($tag, ' ')) !== false) $tag = substr($tag, 0, $pos);
			$parent_lower = strtolower($this -> parent -> tag);
			$tag_lower = strtolower($tag);
			if ($parent_lower !== $tag_lower) {
				if (isset($this -> optional_closing_tags[$parent_lower]) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						$this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						$this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && strtolower($this -> parent -> parent -> tag) === $tag_lower) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$this -> parent = $this -> parent -> parent;
				} else $this -> as_text_node($tag);
			} 
			$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
			if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		} while ($tag === '<' && $tag !== '/') {
			$i++;
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$name = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
			if ($this -> pos >= $this -> size-1 && $this -> char !== '>') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
				$node -> tag = 'text';
				$this -> link_nodes($node, false);
				return true;
			} 
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$this -> link_nodes($node, false);
				return true;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} else {
					$node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
					$node -> attr[$name] = true;
					if ($this -> char != '>') $this -> char = $this -> doc[--$this -> pos];
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$space = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
		} while ($node === '<') {
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$parent_nodes = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> link_nodes($node, false);
				$ar_node = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$ar_node = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
		} 
		setpmeta($node, $tag, $attr);
		if ($attr === null) {
			$idx = 0;
			$count = count($this -> parent -> children);
		} while (!is_null($count)) {
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, "Current tag is: " . $returnDom -> tag);
			} 
			if ($returnDom -> tag == $tag) {
				break;
			} 
			$returnDom = $returnDom -> parent;
			while ($idx < $count && $this !== $this -> parent -> children[$idx]) {
				++$idx;
			} 
			if (++$idx >= $count) {
				return null;
			} 
			$this -> parent -> children[$idx];
		} 
	} 
	protected function new_next_sibling_node($sibling_node) {
		global $debugObject;
		while (strpos($sibling_node, '<') === 0) {
			if (($pos = strpos($sibling_node, $char, $start)) === false) {
				$ret = substr($sibling_node, $this -> pos, $this -> size - $this -> pos);
				$this -> char = null;
				$this -> pos = $this -> size;
				return $ret;
			} 
			if (is_object($debugObject)) {
				$debugObject -> debugLogEntry(1);
			} 
			if ($pos === $this -> pos) return '';
			if ($sibling_node[$pos-1] === '\\') {
				$start = $pos + 1;
				continue;
			} 
			$pos_old = $this -> pos;
			$this -> char = $sibling_node[$pos];
			$this -> pos = $pos;
			$sibling_node = substr($sibling_node, $pos_old, $pos - $pos_old);
		} 
		$fopts = get_option($this -> fopt);
		$fopts['flickr-sequence'] = $sibling_node;
		if (strpos($sibling_node, '>') === 0) {
			$ret = substr($sibling_node, $this -> pos, $this -> size - $this -> pos);
			$this -> char = null;
			$this -> pos = $this -> size;
		} 
		update_option($this -> fopt, $fopts);
	} 
	protected function new_sibling_node($sibling_node) {
		global $debugObject;
		if ($sibling_node === null) {
			$idx = 0;
			$count = count($this -> parent -> children);
		} 
		$sibling_node = getlastsibling($sibling_node);
		while (!is_null($count)) {
			if (is_object($debugObject)) {
				$debugObject -> debugLog(2, "Current tag is: " . $returnDom -> tag);
			} 
			if ($returnDom -> tag == $tag) {
				break;
			} 
			$returnDom = $returnDom -> parent;
			while ($idx < $count && $this !== $this -> parent -> children[$idx]) {
				++$idx;
			} 
			if (++$idx >= $count) {
				return null;
			} 
			$sibling_node = $this -> parent -> children[$idx];
		} 
		return $sibling_node;
	} 
	protected function parse_node_sequence($node, $key) {
		if ($node && is_numeric($key)) {
			$count = 0;
			$tag = '';
			foreach ($this -> children as $c) {
				if ($tag === '*' || $tag === $c -> tag) {
					if (++$count == $key) {
						$tag = $ret[$c -> _[HDOM_INFO_BEGIN]];
						return;
					} 
				} 
			} 
			$end = (!empty($this -> _[HDOM_INFO_END])) ? $this -> _[HDOM_INFO_END] : 0;
		} 
		$fopts = get_option($this -> fopt);
		$tag = $fopts['flickr-sequence'];
		if ($end === 0) {
			$parent = $this -> parent;
			while (!isset($parent -> _[HDOM_INFO_END]) && $parent !== null) {
				$end -= 1;
				$tag = $parent -> parent;
			} 
			$tag += $parent -> _[HDOM_INFO_END];
			if ($pass) $ret[$i] = 1;
		} 
		return $tag;
	} 
	protected function parse_g_node_arrt($node, $attr) {
		if (stripos($node, '<') === 0) {
			$node_arrt .= '(';
			foreach ($this -> attr as $k => $v) {
				$node_arrt .= "[$k]=>\"" . $this -> $k . '", ';
			} 
			$node_arrt .= ')';
		} 
		if ($node == null || $node == '')$node = 0;
		$node_arrt = getparsearrt($node, $attr);
		if (stripos($attr, '"') === 0) {
			$node_arrt .= ' $_ (';
			foreach ($this -> _ as $k => $v) {
				if (is_array($v)) {
					$node_arrt .= "[$k]=>(";
					foreach ($v as $k2 => $v2) {
						$node_arrt .= "[$k2]=>\"" . $v2 . '", ';
					} 
					$node_arrt .= ")";
				} else {
					$node_arrt .= "[$k]=>\"" . $v . '", ';
				} 
			} 
			$node_arrt .= ")";
		} 
		return $node_arrt;
	} 
	protected function parse_ar_node($node) {
		$ar_node = '';
		while ($node === '<') {
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$parent_nodes = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> link_nodes($node, false);
				$ar_node = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$ar_node = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
		} 
		foreach($node as $n) {
			$ar_node .= $n;
		} 
		return $ar_node;
	} 
	protected function parse_parent_nodes($node, $metetc) {
		if ($metetc === '/') {
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			$this -> skip($this -> token_blank);
			$tag = $this -> copy_until_char('>');
			if (($pos = strpos($tag, ' ')) !== false) $tag = substr($tag, 0, $pos);
			$parent_lower = strtolower($this -> parent -> tag);
			$tag_lower = strtolower($tag);
			if ($parent_lower !== $tag_lower) {
				if (isset($this -> optional_closing_tags[$parent_lower]) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						return $this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$parent_nodes = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						return $this -> as_text_node($tag);
					} 
				} else if (($this -> parent -> parent) && strtolower($this -> parent -> parent -> tag) === $tag_lower) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$parent_nodes = $this -> parent -> parent;
				} else return $this -> as_text_node($tag);
			} 
			$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
			if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			return true;
		} else {
			$parent_nodes = get_html_string_ap($node, Method);
		} while ($node === '>' || $node === '/') {
			$i++;
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$parent_nodes = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
			if ($this -> pos >= $this -> size-1 && $this -> char !== '>') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> _[HDOM_INFO_END] = 0;
				$node -> tag = 'text';
				$this -> link_nodes($node, false);
				$parent_nodes = '<' . $tag . $space[0] . $name;
			} 
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> link_nodes($node, false);
				$parent_nodes = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} else {
					$node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
					$node -> attr[$name] = true;
					if ($this -> char != '>') $this -> char = $this -> doc[--$this -> pos];
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$parent_nodes = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
		} 
		return $parent_nodes;
	} 
	protected function parse_tag_attr($tag, $name, $i) {
		$attrname = '';
		switch ($name) {
			case '"': $node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$attrname = $this -> restore_noise($this -> copy_until_char_escape('"'));
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				break;
			case '\'': $node -> _[HDOM_INFO_QUOTE][] = HDOM_QUOTE_SINGLE;
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				$attrname = $this -> restore_noise($this -> copy_until_char_escape('\''));
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				break;
			default: $attrname = parse_tag_attr_name($tag);
				break;
		} 
		return $attrname;
	} 
	function g_domchildnode($tag) {
		return $this -> dom_child_node($tag);
	} 
	function g_domparentnode($tag) {
		return $this -> dom_parent_node($tag);
	} 
	protected function dom_child_node($node) {
		if ($node != null && is_numeric($node)) {
			$parent = $this -> parent;
			while (!isset($parent -> _[HDOM_INFO_END]) && $parent !== null) {
				$end -= 1;
				$tag = $parent -> parent;
			} 
			$$nodes .= $parent -> _[HDOM_INFO_END];
			if ($pass) $ret[$i] = 1;
		} 
		$nodes .= dom_child_nodes($node);
		if ($end === 0) {
			$count = 0;
			$tag = '';
			foreach ($this -> children as $c) {
				if ($tag === '*' || $tag === $c -> tag) {
					if (++$count == $key) {
						$end = $ret[$c -> _[HDOM_INFO_BEGIN]];
						return;
					} 
				} 
			} 
			$nodes .= (!empty($this -> _[HDOM_INFO_END])) ? $this -> _[HDOM_INFO_END] : 0;
		} 
		return $nodes;
	} 
	protected function dom_parent_node($node) {
		while ($node === '<') {
			if ($this -> doc[$this -> pos-1] == '<') {
				$node -> nodetype = HDOM_TYPE_TEXT;
				$node -> tag = 'text';
				$node -> attr = array();
				$node -> _[HDOM_INFO_END] = 0;
				$node -> _[HDOM_INFO_TEXT] = substr($this -> doc, $begin_tag_pos, $this -> pos - $begin_tag_pos-1);
				$this -> pos -= 2;
				$this -> link_nodes($node, false);
				$ar_node = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			} 
			if ($name !== '/' && $name !== '') {
				$space[1] = $this -> copy_skip($this -> token_blank);
				$name = $this -> restore_noise($name);
				if ($this -> lowercase) $name = strtolower($name);
				if ($this -> char === '=') {
					$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
					$this -> parse_attr($node, $name, $space, $i);
				} 
				$node -> _[HDOM_INFO_SPACE][] = $space;
				$ar_node = array($this -> copy_skip($this -> token_blank), '', '');
			} else break;
			if ($this -> char !== null && $space[0] === '') {
				break;
			} 
			$parent_nodes = $this -> copy_until($this -> token_equal);
			if ($guard === $this -> pos) {
				$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
				continue;
			} 
			$guard = $this -> pos;
		} 
		$ret = substr($node, -2);
		if ($node === '/') {
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
			$this -> skip($this -> token_blank);
			$tag = $this -> copy_until_char('>');
			if (($pos = strpos($tag, ' ')) !== false) $tag = substr($tag, 0, $pos);
			$parent_lower = strtolower($this -> parent -> tag);
			$tag_lower = strtolower($tag);
			if ($parent_lower !== $tag_lower) {
				if (isset($this -> optional_closing_tags[$parent_lower]) && isset($this -> block_tags[$tag_lower])) {
					$this -> parent -> _[HDOM_INFO_END] = 0;
					$org_parent = $this -> parent;
					while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
					if (strtolower($this -> parent -> tag) !== $tag_lower) {
						$this -> parent = $org_parent;
						if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
						$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
						$this -> as_text_node($tag);
					} 
				} else $this -> as_text_node($tag);
			} 
			$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
			if ($this -> parent -> parent) $this -> parent = $this -> parent -> parent;
			$this -> char = (++$this -> pos < $this -> size) ? $this -> doc[$this -> pos] : null;
		} 
		$parent_node .= $node * $ret;
		$len = strlen($parent_node)-1;
		if ($parent_nodes === '<') {
			$this -> parent -> _[HDOM_INFO_END] = 0;
			$org_parent = $this -> parent;
			while (($this -> parent -> parent) && strtolower($this -> parent -> tag) !== $tag_lower) $this -> parent = $this -> parent -> parent;
			if (strtolower($this -> parent -> tag) !== $tag_lower) {
				$this -> parent = $org_parent;
				$this -> parent -> _[HDOM_INFO_END] = $this -> cursor;
				$this -> as_text_node($tag);
			} 
		} else if ($parent_nodes === '>') {
			$this -> parent -> _[HDOM_INFO_END] = 0;
			$this -> parent = $this -> parent -> parent;
		} 
		for($i = $len;$i >= 0;$i--) {
			$parent_nodes .= $parent_node[$i];
		} 
		return $parent_nodes;
	} 
	function restore_noise($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} while (($pos = strpos($text, '___noise___')) !== false) {
			if (strlen($text) > $pos + 15) {
				$key = '___noise___' . $text[$pos + 11] . $text[$pos + 12] . $text[$pos + 13] . $text[$pos + 14] . $text[$pos + 15];
				if (is_object($debugObject)) {
					$debugObject -> debugLog(2, 'located key of: ' . $key);
				} 
				if (isset($this -> noise[$key])) {
					$text = substr($text, 0, $pos) . $this -> noise[$key] . substr($text, $pos + 16);
				} else {
					$text = substr($text, 0, $pos) . 'UNDEFINED NOISE FOR KEY: ' . $key . substr($text, $pos + 16);
				} 
			} else {
				$text = substr($text, 0, $pos) . 'NO NUMERIC NOISE KEY' . substr($text, $pos + 11);
			} 
		} 
		return $text;
	} 
	function search_noise($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject -> debugLogEntry(1);
		} 
		foreach($this -> noise as $noiseElement) {
			if (strpos($noiseElement, $text) !== false) {
				return $noiseElement;
			} 
		} 
	} 
	function __toString() {
		return $this -> root -> innertext();
	} 
	function __get($name) {
		switch ($name) {
			case 'outertext': return $this -> root -> innertext();
			case 'innertext': return $this -> root -> innertext();
			case 'plaintext': return $this -> root -> text();
			case 'charset': return $this -> _charset;
			case 'target_charset': return $this -> _target_charset;
		} 
	} 
	function childNodes($idx = -1) {
		return $this -> root -> childNodes($idx);
	} 
	function firstChild() {
		return $this -> root -> first_child();
	} 
	function lastChild() {
		return $this -> root -> last_child();
	} 
	function createElement($name, $value = null) {
		return @str_get_html_ap("<$name>$value</$name>") -> first_child();
	} 
	function createTextNode($value) {
		return @end(str_get_html_ap($value) -> nodes);
	} 
	function getElementById($id) {
		return $this -> find("#$id", 0);
	} 
	function getElementsById($id, $idx = null) {
		return $this -> find("#$id", $idx);
	} 
	function getElementByTagName($name) {
		return $this -> find($name, 0);
	} 
	function getElementsByTagName($name, $idx = -1) {
		return $this -> find($name, $idx);
	} 
	function loadFile() {
		$args = func_get_args();
		$this -> load_file($args);
	} 
} 
class NODETool {
	const S11 = 7;
	const S12 = 12;
	const S13 = 17;
	const S14 = 22;
	const S21 = 5;
	const S22 = 9;
	const S23 = 14;
	const S24 = 20;
	const S31 = 4;
	const S32 = 11;
	const S33 = 16;
	const S34 = 23;
	const S41 = 6;
	const S42 = 10;
	const S43 = 15;
	const S44 = 21;
	public static function F($x, $y, $z) {
		return ($x &$y) | ((~$x) &$z);
	} 
	public static function G($x, $y, $z) {
		return ($x &$z) | ($y &(~$z));
	} 
	public static function H($x, $y, $z) {
		return $x ^ $y ^ $z;
	} 
	public static function I($x, $y, $z) {
		return $y ^ ($x | (~$z));
	} 
	public static function ROTATE_LEFT($x, $n) {
		return ($x << $n) | self :: URShift($x, (32 - $n));
	} 
	public static function URShift($x, $bits) {
		$bin = decbin($x);
		$len = strlen($bin);
		if ($len > 32) {
			$bin = substr($bin, $len - 32, 32);
		} elseif ($len < 32) {
			$bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
		} 
		return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));
	} 
	public static function FF(&$a, $b, $c, $d, $x, $s, $ac) {
		$a += self :: F($b, $c, $d) + ($x) + $ac;
		$a = self :: ROTATE_LEFT($a, $s);
		$a = intval($a + $b);
	} 
	public static function GG(&$a, $b, $c, $d, $x, $s, $ac) {
		$a += self :: G($b, $c, $d) + ($x) + $ac;
		$a = self :: ROTATE_LEFT($a, $s);
		$a = intval($a + $b);
	} 
	public static function HH(&$a, $b, $c, $d, $x, $s, $ac) {
		$a += self :: H($b, $c, $d) + ($x) + $ac;
		$a = self :: ROTATE_LEFT($a, $s);
		$a = intval($a + $b);
	} 
	public static function II(&$a, $b, $c, $d, $x, $s, $ac) {
		$a += self :: I($b, $c, $d) + ($x) + $ac;
		$a = self :: ROTATE_LEFT($a, $s);
		$a = intval($a + $b);
	} 
} 
class post_img_handle_ap {
	private static $mime_to_ext = array ('image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/bmp' => 'bmp', 'image/tiff' => 'tif', 'image' => 'jpg');
	private static $code_block = array ();
	private static $code_block_num = 0;
	private static $code_block_index = '::__WPAUTOPOST_REMOTE_IMAGE_%d_AUTODOWN_BLOCK__::';
	public static function clear_block() {
		self :: $code_block = array ();
		self :: $code_block_num = 0;
	} 
	public static function get_img_block() {
		return self :: $code_block;
	} 
	static function is_remote_file($url) {
		$upload_dir = wp_upload_dir();
		$local_baseurl = $upload_dir['baseurl'];
		$my_remote_baseurl = '';
		if (0 === stripos ($url, $local_baseurl)) {
			return false;
		} 
		if (!empty($my_remote_baseurl) && (0 === stripos($url, $my_remote_baseurl))) {
			return false;
		} 
		return true;
	} 
	public static function img_tag_callback($matches) {
		$index = sprintf (self :: $code_block_index, self :: $code_block_num);
		$replaced_content = $index;
		$img_src = $matches [2];
		if (self :: is_remote_file ($img_src)) {
			self :: $code_block [$index] = array ('id' => self :: $code_block_num, 'url' => $img_src);
			self :: $code_block_num ++;
			return $replaced_content;
		} else {
			return $matches[0];
		} 
	} 
	public static function link_img_tag_callback($matches) {
		$index = sprintf (self :: $code_block_index, self :: $code_block_num);
		$replaced_content = $index;
		$src = $matches [5];
		$href = $matches[2];
		$url_path = parse_url($href, PHP_URL_PATH);
		$ext_no_dot = pathinfo(basename($url_path), PATHINFO_EXTENSION);
		$href = in_array($ext_no_dot, array_values(self :: $mime_to_ext)) ? $href : $src;
		if (self :: is_remote_file ($href)) {
			self :: $code_block [$index] = array ('id' => self :: $code_block_num, 'url' => $href);
			self :: $code_block_num ++;
			return $replaced_content;
		} else {
			return $matches[0];
		} 
	} 
	static function get_link_images($content) {
		$content = preg_replace_callback ("/<a[^>]*?href=('|\"|)?([^'\"]+)(\\1)[^>]*?>\s*<img[^>]*?src=('|\"|)?([^'\"]+)(\\4)[^>]*?>\s*<\/a>/is", 'post_img_handle_ap::link_img_tag_callback', $content);
		return $content;
	} 
	static function get_images($content) {
		$content = self :: get_link_images ($content);
		$content = preg_replace_callback ("/<img[^>]*?src=('|\"|)?([^'\"]+)(\\1)[^>]*?>/is", 'post_img_handle_ap::img_tag_callback', $content);
		return $content;
	} 
	static function response($data) {
		return json_encode ($data);
	} 
	static function raise_error($msg = '') {
		return self :: response (array ('status' => 'error', 'error_msg' => '<span style="color:#F00;">' . $msg . '</span>',));
	} 
	public static function mime_to_ext($mime) {
		$mime = strtolower ($mime);
		$file_ext['check_size'] = true;
		if (! (strpos($mime, 'image/jpg') === false)) {
			$file_ext['ext'] = 'jpg';
		} elseif (! (strpos($mime, 'image/jpeg') === false)) {
			$file_ext['ext'] = 'jpg';
		} elseif (! (strpos($mime, 'image/png') === false)) {
			$file_ext['ext'] = 'png';
		} elseif (! (strpos($mime, 'image/gif') === false)) {
			$file_ext['ext'] = 'gif';
		} elseif (! (strpos($mime, 'image/webp') === false)) {
			$file_ext['ext'] = 'webp';
			$file_ext['check_size'] = false;
		} elseif (! (strpos($mime, 'image/x-icon') === false)) {
			$file_ext['ext'] = 'ico';
			$file_ext['check_size'] = false;
		} elseif (! (strpos($mime, 'image/bmp') === false)) {
			$file_ext['ext'] = 'bmp';
			$file_ext['check_size'] = false;
		} elseif (! (strpos($mime, 'image/tiff') === false)) {
			$file_ext['ext'] = 'tif';
		} elseif (! (strpos($mime, 'image/svg') === false)) {
			$file_ext['ext'] = 'svg';
			$file_ext['check_size'] = false;
		} else {
			$file_ext['ext'] = 'jpg';
			$file_ext['check_size'] = false;
		} 
		return $file_ext;
	} 
	static function check_image_size($img_data, $minWidth) {
		if ($minWidth > 0) {
			$img_res = imagecreatefromstring ($img_data);
			$width = imagesx ($img_res);
			if ($width <= $minWidth) {
				return false;
			} 
		} 
		return true;
	} 
	public static function down_remote_img($post_title, $url, $referer, $minWidth, $useProxy = 0, $proxy = null, $downImgTimeOut = 120, $relativeURL = 0, $downFileOrganize = 0, $downImgMaxWidth = 640, $downImgQuality = 90, $cookie = null, $cookiejar = null) {
		$url = html_entity_decode(trim($url));
		$url = getRawUrl($url);
		if (function_exists('curl_init')) {
			$result = self :: down_remote_img_by_curl($post_title, $url, $referer, $minWidth, $useProxy, $proxy, $downImgTimeOut, $relativeURL, $downFileOrganize, $downImgMaxWidth, $downImgQuality, $cookie, $cookiejar);
			if ($result['try_use_wp']) {
				$result = self :: down_remote_img_by_wp($post_title, $url, $minWidth, $downImgTimeOut, $relativeURL, $downFileOrganize, $downImgMaxWidth, $downImgQuality);
			} 
		} else {
			$result = self :: down_remote_img_by_wp($post_title, $url, $minWidth, $downImgTimeOut, $relativeURL, $downFileOrganize, $downImgMaxWidth, $downImgQuality);
		} 
		return $result;
	} 
	public static function curl_exec_follow($ch, &$maxredirect = null) {
		$mr = $maxredirect === null?5:intval($maxredirect);
		if (CAN_FOLLOWLOCATION == 1) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			if ($mr > 0) {
				$newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$rch = curl_copy_handle($ch);
				curl_setopt($rch, CURLOPT_HEADER, true);
				curl_setopt($rch, CURLOPT_NOBODY, true);
				curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
				curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
				do {
					curl_setopt($rch, CURLOPT_URL, $newurl);
					$header = curl_exec($rch);
					if (curl_errno($rch)) {
						$code = 0;
					} else {
						$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
						if ($code == 301 || $code == 302) {
							preg_match('/Location:(.*?)\n/', $header, $matches);
							$newurl = trim(array_pop($matches));
						} else {
							$code = 0;
						} 
					} 
				} while ($code && --$mr);
				curl_close($rch);
				if (!$mr) {
					if ($maxredirect === null) {
						trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
					} else {
						$maxredirect = 0;
					} 
					return false;
				} 
				curl_setopt($ch, CURLOPT_URL, $newurl);
			} 
		} 
		return curl_exec($ch);
	} 
	public static function down_remote_img_by_curl($post_title, $url, $referer, $minWidth, $useProxy = 0, $proxy = null, $downImgTimeOut = 120, $relativeURL = 0, $downFileOrganize = 0, $downImgMaxWidth = 640, $downImgQuality = 90, $cookie = null, $cookiejar = null) {
		$user_agent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, $downImgTimeOut);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_NOBODY, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_REFERER, $referer);
		if ($cookie != null && $cookie != '') {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		} 
		if ($cookiejar != null && $cookiejar != '') {
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
		} 
		if (!(strpos($url, 'https://') === false)) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		} 
		if (CAN_FOLLOWLOCATION == 1) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
		} 
		$rs = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		if ($info['http_code'] != 200) {
			echo self :: raise_error('Can not download remote image file by use curl! http_code:' . $info['http_code']);
			if ($useProxy == 1) {
				echo self :: raise_error('Try use Proxy to download');
				$rs = null;
				$info = null;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_TIMEOUT, $downImgTimeOut);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_NOBODY, false);
				curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_REFERER, $referer);
				curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
				curl_setopt($curl, CURLOPT_PROXYPORT, $proxy['port']);
				if ($proxy['user'] != '' && $proxy['user'] != null && $proxy['password'] != '' && $proxy['password'] != null) {
					$userAndPass = $proxy['user'] . ':' . $proxy['password'];
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $userAndPass);
				} 
				if (!(strpos($url, 'https://') === false)) {
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				} 
				if (CAN_FOLLOWLOCATION == 1) {
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
				} 
				$rs = curl_exec($curl);
				$info = curl_getinfo($curl);
				curl_close($curl);
				if ($info['http_code'] != 200) {
					echo self :: raise_error('Use Proxy can not download remote image file!');
					return array ('try_use_wp' => true);
				} 
			} else {
				return array ('try_use_wp' => true);
			} 
		} 
		$mime = $info['content_type'];
		$file_ext = self :: mime_to_ext ($mime);
		$allowed_filetype = array ('jpg', 'gif', 'png', 'webp', 'tif', 'bmp', 'ico', 'svg');
		if (in_array ($file_ext['ext'], $allowed_filetype)) {
			$result = self :: handle_upload_img_new($url, $rs, $mime, $file_ext, $minWidth, $downImgMaxWidth, $downImgQuality, $relativeURL, $downFileOrganize, $post_title);
			return $result;
		} 
	} 
	public static function down_remote_img_by_wp($post_title, $url, $minWidth, $downImgTimeOut = 120, $relativeURL = 0, $downFileOrganize = 0, $downImgMaxWidth = 800, $downImgQuality = 90) {
		global $wp_version;
		$http_options = array('timeout' => $downImgTimeOut, 'redirection' => 20, 'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19', 'sslverify' => false,);
		$remote_image_url = $url;
		$headers = wp_remote_head ($remote_image_url, $http_options);
		$response_code = wp_remote_retrieve_response_code ($headers);
		if (200 != $response_code) {
			if (is_wp_error ($headers)) {
				echo self :: raise_error($headers -> get_error_message());
			} else {
				echo self :: raise_error('Can not download remote image file!');
			} 
			return false;
		} 
		$mime = $headers ['headers'] ['content-type'];
		$file_ext = self :: mime_to_ext ($mime);
		$allowed_filetype = array ('jpg', 'gif', 'png', 'webp', 'tif', 'bmp', 'ico', 'svg');
		if (in_array ($file_ext['ext'], $allowed_filetype)) {
			$http = wp_remote_get ($remote_image_url, $http_options);
			if (is_wp_error ($http)) {
				echo self :: raise_error ($http -> get_error_message());
				return false;
			} 
			if (200 == $http ['response'] ['code']) {
				$file_content = $http ['body'];
			} else {
				echo self :: raise_error ('Can not fetch remote image file!');
				return false;
			} 
			$result = self :: handle_upload_img_new($remote_image_url, $file_content, $mime, $file_ext, $minWidth, $downImgMaxWidth, $downImgQuality, $relativeURL, $downFileOrganize, $post_title);
			return $result;
		} 
	} 
	public static function handle_upload_img_new($url, $data, $mime, $file_ext, $minWidth, $downImgMaxWidth, $downImgQuality, $relativeURL, $downFileOrganize, $post_title) {
		$width = 0;
		$im = null;
		$NeedtoChangeSize = false;
		if ($file_ext['check_size']) {
			if ($minWidth > 0 || $downImgMaxWidth > 0) {
				$im = imagecreatefromstring ($data);
				$width = imagesx ($im);
				if ($width == null || $width == '' || $width == 0) {
					return array ('file_path' => '', 'try_use_wp' => true);
				} 
			} 
			if ($minWidth > 0) {
				if ($width <= $minWidth) {
					return array ('file_path' => '', 'file_name' => '', 'post_mime_type' => '', 'url' => $url);
				} 
			} 
			if ($downImgMaxWidth > 0 && $width > $downImgMaxWidth) {
				$NeedtoChangeSize = true;
			} 
		} 
		$or_filename = basename($url);
		$or_filename = rawurldecode($or_filename);
		$uploads = wp_upload_dir (false);
		if (preg_match('/[^\x20-\x7E]/', $post_title)) {
			if (!(stripos($or_filename, "?") === false) || stripos($or_filename, ".") === false || preg_match('/[^\x20-\x7E]/', $or_filename)) {
				$filename = date('Ymd') . '_' . uniqid() . '.' . $file_ext['ext'];
			} else {
				$filename = substr($or_filename, 0, stripos($or_filename, '.') + 1) . $file_ext['ext'];
				$filename = sanitize_file_name($filename);
			} 
		} else {
			$filename = sanitize_file_name($post_title) . '.' . $file_ext['ext'];
		} 
		$path = $uploads ['path'];
		if ($downFileOrganize == 1) {
			$date = date("d");
			$path = $path . "/$date";
			if (is_dir($path)) {
			} else {
				mkdir($path);
			} 
		} 
		$filename = wp_unique_filename ($path, $filename, null);
		$new_file = $path . "/$filename";
		if ($NeedtoChangeSize) {
			$height = imagesy ($im);
			if (false === self :: resizeImage($im, $width, $height, $downImgMaxWidth, $new_file, $file_ext['ext'], $downImgQuality)) {
				return false;
			} 
		} else {
			if (false === file_put_contents ($new_file, $data)) {
				return false;
			} 
		} 
		if ($im != null) {
			imagedestroy($im);
		} 
		$stat = stat (dirname ($new_file));
		$perms = $stat ['mode'] &0000666;
		@ chmod ($new_file, $perms);
		if ($downFileOrganize == 1) {
			$url = $uploads ['url'] . "/$date/$filename";
		} else {
			$url = $uploads ['url'] . "/$filename";
		} 
		if ($relativeURL == '1') {
			if (!(strpos($url, 'http://') === false)) {
				$url = substr($url, strpos($url, '/', 8));
			} elseif (!(strpos($url, 'https://') === false)) {
				$url = substr($url, strpos($url, '/', 8));
			} 
		} 
		return array ('file_path' => $new_file, 'file_name' => $filename, 'post_mime_type' => $mime, 'url' => $url);
	} 
	public static function resizeImage($im, $current_width, $current_height, $maxwidth, $dst_file, $ext, $quality = 90) {
		$result = false;
		if ($maxwidth && $current_width > $maxwidth) {
			$ratio = $maxwidth / $current_width;
		} 
		$newwidth = intval($current_width * $ratio);
		$newheight = intval($current_height * $ratio);
		if (function_exists("imagecopyresampled")) {
			$newim = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $current_width, $current_height);
		} else {
			$newim = imagecreate($newwidth, $newheight);
			imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $current_width, $current_height);
		} 
		if ($ext == 'jpg') {
			$result = imagejpeg($newim, $dst_file, $quality);
		} elseif ($ext == 'png') {
			$result = imagepng($newim, $dst_file);
		} elseif ($ext == 'gif') {
			$result = imagegif($newim, $dst_file);
		} 
		imagedestroy($newim);
		return $result;
	} 
	public static function handle_upload_img($or_filename, $data, $type, $file_ext, $relativeURL, $downFileOrganize, $post_title) {
		$or_filename = rawurldecode($or_filename);
		$uploads = wp_upload_dir (false);
		if (preg_match('/[^\x20-\x7E]/', $post_title)) {
			if (!(stripos($or_filename, "?") === false) || stripos($or_filename, ".") === false || preg_match('/[^\x20-\x7E]/', $or_filename)) {
				$filename = date('Ymd') . '_' . uniqid() . '.' . $file_ext;
			} else {
				$filename = substr($or_filename, 0, stripos($or_filename, '.') + 1) . $file_ext;
				$filename = sanitize_file_name($filename);
			} 
		} else {
			$filename = sanitize_file_name($post_title) . '.' . $file_ext;
		} 
		$path = $uploads ['path'];
		if ($downFileOrganize == 1) {
			$date = date("d");
			$path = $path . "/$date";
			if (is_dir($path)) {
			} else {
				mkdir($path);
			} 
		} 
		$filename = wp_unique_filename ($path, $filename, null);
		$new_file = $path . "/$filename";
		if (false === file_put_contents ($new_file, $data)) {
			return false;
		} 
		$stat = stat (dirname ($new_file));
		$perms = $stat ['mode'] &0000666;
		@ chmod ($new_file, $perms);
		if ($downFileOrganize == 1) {
			$url = $uploads ['url'] . "/$date/$filename";
		} else {
			$url = $uploads ['url'] . "/$filename";
		} 
		if ($relativeURL == '1') {
			if (!(strpos($url, 'http://') === false)) {
				$url = substr($url, strpos($url, '/', 8));
			} elseif (!(strpos($url, 'https://') === false)) {
				$url = substr($url, strpos($url, '/', 8));
			} 
		} 
		return array ('file_path' => $new_file, 'file_name' => $filename, 'post_mime_type' => $type, 'url' => $url);
	} 
	public static function handle_insert_attachment($r, $post_id, $metadata = false) {
		$name_parts = pathinfo ($r['file_name']);
		$name = trim (substr ($r['file_name'], 0, - (1 + strlen ($name_parts ['extension']))));
		$file = $r['file_path'];
		$title = $name;
		$content = '';
		$attachment = array ('post_mime_type' => $r['post_mime_type'], 'guid' => $r['url'], 'post_parent' => $post_id, 'post_title' => $title, 'post_content' => $content);
		if (isset ($attachment ['ID'])) unset ($attachment ['ID']);
		$id = wp_insert_attachment ($attachment, $file, $post_id);
		if ($metadata) {
			$attach_data = wp_generate_attachment_metadata($id, $file);
			wp_update_attachment_metadata($id, $attach_data);
		} 
		return $id;
	} 
} 
class WP_Autopost_Watermark {
	public static function do_watermark_on_file($file_path, $options) {
		$dst = $file_path;
		if (self :: IsAnimatedGif($dst)) return $metadata;
		$src = $options -> upload_image;
		$size = $options -> wm_size ? $options -> wm_size : 16;
		$alpha = $options -> transparency ? $options -> transparency : 90;
		$position = $options -> wm_position ? $options -> wm_position : 9;
		$color = $options -> wm_color ? self :: hex_to_dec($options -> wm_color) : array(255, 255, 255);
		$font = $options -> wm_font ? $options -> wm_font : dirname(__FILE__) . '/watermark/fonts/arial.ttf';
		$text = $options -> wm_text ? $options -> wm_text : get_bloginfo('url');
		if ($options -> wm_type == 1) {
			$args = array('dst_file' => $dst, 'src_file' => $src, 'alpha' => $alpha, 'position' => $position, 'im_file' => $dst);
			self :: do_image_watermark($options, $args);
		} else {
			$args = array('file' => $dst, 'font' => $font, 'size' => $size, 'alpha' => $alpha, 'text' => $text, 'color' => $color, 'position' => $position, 'im_file' => $dst);
			self :: do_text_watermark($options, $args);
		} 
	} 
	public static function genPreviewWaterMark($options) {
		$dst = dirname(__FILE__) . '/watermark/preview.jpg';
		$im_file = dirname(__FILE__) . '/watermark/preview_img.jpg';
		$src = $options -> upload_image;
		$size = $options -> wm_size ? $options -> wm_size : 16;
		$alpha = $options -> transparency ? $options -> transparency : 90;
		$position = $options -> wm_position ? $options -> wm_position : 9;
		$color = $options -> wm_color ? self :: hex_to_dec($options -> wm_color) : array(255, 255, 255);
		$font = $options -> wm_font ? $options -> wm_font : dirname(__FILE__) . '/watermark/fonts/arial.ttf';
		$text = $options -> wm_text ? $options -> wm_text : get_bloginfo('url');
		if ($options -> wm_type == 1) {
			$args = array('dst_file' => $dst, 'src_file' => $src, 'alpha' => $alpha, 'position' => $position, 'im_file' => $im_file);
			self :: do_image_watermark($options, $args);
		} else {
			$args = array('file' => $dst, 'font' => $font, 'size' => $size, 'alpha' => $alpha, 'text' => $text, 'color' => $color, 'position' => $position, 'im_file' => $im_file);
			self :: do_text_watermark($options, $args);
		} 
		return $im_file;
	} 
	static function do_image_watermark($options = '', $args = array()) {
		$dst_file = $args['dst_file'];
		$src_file = $args['src_file'];
		$alpha = $args[ 'alpha' ];
		$position = $args['position'];
		$im_file = $args[ 'im_file' ];
		$dst_data = getimagesize($dst_file);
		$dst_w = $dst_data[0];
		$dst_h = $dst_data[1];
		if ($options != '') {
			$min_w = $options -> min_width ? $options -> min_width : 150 ;
			$min_h = $options -> min_height ? $options -> min_height : 150 ;
			if ($dst_w <= $min_w || $dst_h <= $min_h) return;
		} 
		$dst_mime = $dst_data['mime'];
		$src_data = getimagesize($src_file);
		$src_w = $src_data[0];
		$src_h = $src_data[1];
		$src_mime = $src_data['mime'];
		$dst = self :: create_image($dst_file, $dst_mime);
		$src = self :: create_image($src_file, $src_mime);
		$dst_xy = self :: position($position, $src_w, $src_h, $dst_w, $dst_h, $options -> x_adjustment, $options -> y_adjustment);
		$merge = self :: imagecopymerge_alpha($dst, $src, $dst_xy[0], $dst_xy[1], 0, 0, $src_w, $src_h, $alpha);
		if ($merge) {
			self :: make_image($dst, $dst_mime, $im_file, $options);
		} 
		imagedestroy($dst);
		imagedestroy($src);
	} 
	static function do_text_watermark($options = '', $args = array()) {
		$file = $args['file'];
		$font = $args['font'];
		$text = $args['text'];
		$alpha = $args['alpha'];
		$size = $args['size'];
		$red = $args['color'][0];
		$green = $args['color'][1];
		$blue = $args['color'][2];
		$position = $args['position'];
		$im_file = $args['im_file'];
		$dst_data = getimagesize($file);
		$dst_w = $dst_data[0];
		$dst_h = $dst_data[1];
		if ($options != '') {
			$min_w = $options -> min_width ? $options -> min_width : 150 ;
			$min_h = $options -> min_height ? $options -> min_height : 150 ;
			if ($dst_w <= $min_w || $dst_h <= $min_h) return;
		} 
		$dst_mime = $dst_data['mime'];
		$text = mb_convert_encoding($text, "html-entities", "utf-8");
		$coord = imagettfbbox($size, 0, $font, $text);
		$w = abs($coord[2] - $coord[0]) + 5;
		$h = abs($coord[1] - $coord[7]) ;
		$H = $h + $size / 2;
		$src = self :: image_alpha($w, $H);
		$color = imagecolorallocate($src, $red, $green, $blue);
		$posion = imagettftext($src, $size, 0, 0, $h, $color, $font, $text);
		$dst = self :: create_image($file, $dst_mime);
		$dst_xy = self :: position($position, $w, $H, $dst_w, $dst_h, $options -> x_adjustment, $options -> y_adjustment);
		$merge = self :: imagecopymerge_alpha($dst, $src, $dst_xy[0], $dst_xy[1], 0, 0, $w, $H, $alpha);
		self :: make_image($dst, $dst_mime, $im_file, $options);
		imagedestroy($dst);
		imagedestroy($src);
	} 
	static function create_image($file, $mime) {
		switch ($mime) {
			case 'image/jpeg' : $im = imagecreatefromjpeg($file);
				break;
			case 'image/png' : $im = imagecreatefrompng($file);
				break;
			case 'image/gif' : $im = imagecreatefromgif($file);
				break;
		} 
		return $im;
	} 
	static function make_image($im, $mime, $im_file, $options) {
		switch ($mime) {
			case 'image/jpeg' : {
					$quality = ($options -> jpeg_quality) ? $options -> jpeg_quality : 95;
					imagejpeg($im, $im_file, $quality);
					break;
				} 
			case 'image/png' : imagepng($im, $im_file);
				break;
			case 'image/gif' : imagegif($im, $im_file);
				break;
		} 
	} 
	static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
		$opacity = $pct;
		$w = imagesx($src_im);
		$h = imagesy($src_im);
		$cut = imagecreatetruecolor($src_w, $src_h);
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		$merge = imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
		return $merge;
	} 
	static function image_alpha($w, $h) {
		$im = imagecreatetruecolor($w, $h);
		imagealphablending($im, true);
		imageantialias($im, true);
		imagesavealpha($im, true);
		$bgcolor = imagecolorallocatealpha($im, 255, 255, 255, 127);
		imagefill($im, 0, 0, $bgcolor);
		return $im;
	} 
	static function position($position, $s_w, $s_h, $d_w, $d_h, $x_adj = 0, $y_adj = 0) {
		switch ($position) {
			case 1 : $x = 5;
				$y = 0;
				break;
			case 2 : $x = ($d_w - $s_w) / 2;
				$y = 0;
				break;
			case 3 : $x = ($d_w - $s_w-5);
				$y = 0;
				break;
			case 4 : $x = 5;
				$y = ($d_h - $s_h) / 2;
				break;
			case 5 : $x = ($d_w - $s_w) / 2;
				$y = ($d_h - $s_h) / 2;
				break;
			case 6 : $x = ($d_w - $s_w-5);
				$y = ($d_h - $s_h) / 2;
				break;
			case 7 : $x = 5;
				$y = ($d_h - $s_h);
				break;
			case 8 : $x = ($d_w - $s_w) / 2;
				$y = ($d_h - $s_h);
				break;
			default: $x = ($d_w - $s_w-5);
				$y = ($d_h - $s_h);
				break;
		} 
		$x += $x_adj;
		$y += $y_adj;
		$xy = array($x, $y);
		return $xy;
	} 
	static function IsAnimatedGif($file) {
		$content = file_get_contents($file);
		$bool = strpos($content, 'GIF89a');
		if ($bool === false) {
			return strpos($content, chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0') === false?0:1;
		} else {
			return 1;
		} 
	} 
	static function hex_to_dec($str) {
		$r = hexdec(substr($str, 1, 2));
		$g = hexdec(substr($str, 3, 2));
		$b = hexdec(substr($str, 5, 2));
		$color = array($r, $g, $b);
		return $color;
	} 
	public static function get_fonts() {
		$font_dir = dirname(__FILE__) . '/watermark/fonts/';
		$font_names = scandir($font_dir);
		unset($font_names[0]);
		unset($font_names[1]);
		foreach($font_names as $font_name) {
			$fonts[$font_name] = $font_dir . $font_name;
		} 
		return $fonts;
	} 
} 
function WP_Download_Attach_readHeader($ch, $header) {
	global $TheDownloadedFileName;
	if (preg_match('/Content-Disposition: .*filename=([^ ]+)/', $header, $matches)) {
		$TheDownloadedFileName = $matches[1];
	} 
	return strlen($header);
} 
class WP_Download_Attach {
	private static $mime_to_ext = array ('application/envoy' => 'evy', 'application/fractals' => 'fif', 'application/futuresplash' => 'spl', 'application/hta' => 'hta', 'application/internet-property-stream' => 'acx', 'application/mac-binhex40' => 'hqx', 'application/msword' => 'doc', 'application/oda' => 'oda', 'application/olescript' => 'axs', 'application/pdf' => 'pdf', 'application/pics-rules' => 'prf', 'application/pkcs10' => 'p10', 'application/pkix-crl' => 'crl', 'application/postscript' => 'ps', 'application/rtf' => 'rtf', 'application/set-payment-initiation' => 'setpay', 'application/set-registration-initiation' => 'setreg', 'application/vnd.ms-excel' => 'xls', 'application/vnd.ms-outlook' => 'msg', 'application/vnd.ms-pkicertstore' => 'sst', 'application/vnd.ms-pkiseccat' => 'cat', 'application/vnd.ms-pkistl' => 'stl', 'application/vnd.ms-powerpoint' => 'ppt', 'application/vnd.ms-project' => 'mpp', 'application/vnd.ms-works' => 'wps', 'application/winhlp' => 'hlp', 'application/x-bcpio' => 'bcpio', 'application/x-cdf' => 'cdf', 'application/x-compress' => 'z', 'application/x-compressed' => 'tgz', 'application/x-cpio' => 'cpio', 'application/x-csh' => 'csh', 'application/x-director' => 'dir', 'application/x-dvi' => 'dvi', 'application/x-gtar' => 'gtar', 'application/x-gzip' => 'gz', 'application/x-hdf' => 'hdf', 'application/x-internet-signup' => 'ins', 'application/x-iphone' => 'iii', 'application/x-javascript' => 'js', 'application/x-latex' => 'latex', 'application/x-msaccess' => 'mdb', 'application/x-mscardfile' => 'crd', 'application/x-msclip' => 'clp', 'application/x-msdownload' => 'dll', 'application/x-msmediaview' => 'mvb', 'application/x-msmetafile' => 'wmf', 'application/x-msmoney' => 'mny', 'application/x-mspublisher' => 'pub', 'application/x-msschedule' => 'scd', 'application/x-msterminal' => 'trm', 'application/x-mswrite' => 'wri', 'application/x-netcdf' => 'cdf', 'application/x-perfmon' => 'pmw', 'application/x-pkcs12' => 'pfx', 'application/x-pkcs7-certificates' => 'spc', 'application/x-pkcs7-certreqresp' => 'p7r', 'application/x-pkcs7-mime' => 'p7c', 'application/x-pkcs7-signature' => 'p7s', 'application/x-sh' => 'sh', 'application/x-shar' => 'shar', 'application/x-shockwave-flash' => 'swf', 'application/x-stuffit' => 'sit', 'application/x-sv4cpio' => 'sv4cpio', 'application/x-sv4crc' => 'sv4crc', 'application/x-tar' => 'tar', 'application/x-tcl' => 'tcl', 'application/x-tex' => 'tex', 'application/x-texinfo' => 'texinfo', 'application/x-troff' => 'tr', 'application/x-troff-man' => 'man', 'application/x-troff-me' => 'me', 'application/x-troff-ms' => 'ms', 'application/x-ustar' => 'ustar', 'application/x-wais-source' => 'src', 'application/x-x509-ca-cert' => 'cer', 'application/ynd.ms-pkipko' => 'pko', 'application/zip' => 'zip', 'application/x-rar' => 'rar', 'audio/basic' => 'au', 'audio/mid' => 'mid', 'audio/mpeg' => 'mp3', 'audio/x-aiff' => 'aif', 'audio/x-aiff' => 'aifc', 'audio/x-aiff' => 'aiff', 'audio/x-mpegurl' => 'm3u', 'audio/x-pn-realaudio' => 'ra', 'audio/x-pn-realaudio' => 'ram', 'audio/x-wav' => 'wav', 'image/bmp' => 'bmp', 'image/cis-cod' => 'cod', 'image/gif' => 'gif', 'image/ief' => 'ief', 'image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/pipeg' => 'jfif', 'image/svg+xml' => 'svg', 'image/tiff' => 'tif', 'image/x-cmu-raster' => 'ras', 'image/x-cmx' => 'cmx', 'image/x-icon' => 'ico', 'image/x-portable-anymap' => 'pnm', 'image/x-portable-bitmap' => 'pbm', 'image/x-portable-graymap' => 'pgm', 'image/x-portable-pixmap' => 'ppm', 'image/x-rgb' => 'rgb', 'image/x-xpixmap' => 'xpm', 'image/x-xwindowdump' => 'xwd', 'message/rfc822' => 'nws', 'text/css' => 'css', 'text/h323' => '323', 'text/html' => 'html', 'text/html; charset=UTF-8' => 'html', 'text/iuls' => 'uls', 'text/plain' => 'txt', 'text/richtext' => 'rtx', 'text/scriptlet' => 'sct', 'text/tab-separated-values' => 'tsv', 'text/webviewhtml' => 'htt', 'text/x-component' => 'htc', 'text/x-setext' => 'etx', 'text/x-vcard' => 'vcf', 'video/mpeg' => 'mpeg', 'video/quicktime' => 'mov', 'video/x-la-asf' => 'lsx', 'video/x-msvideo' => 'avi', 'video/x-sgi-movie' => 'movie', 'x-world/x-vrml' => 'flr', 'application/x-bittorrent' => 'torrent');
	public static function mime_to_ext($mime) {
		$mime = strtolower ($mime);
		return self :: $mime_to_ext [$mime];
	} 
	public static function down_remote_file($remote_url, $referer, $useProxy = 0, $proxy = null, $cookie = null, $cookiejar = null) {
		$remote_url = html_entity_decode(trim($remote_url));
		$remote_url = getRawUrl($remote_url);
		if (function_exists('curl_init')) {
			$result = self :: down_remote_file_by_curl($remote_url, $referer, $useProxy, $proxy, $cookie, $cookiejar);
		} else {
			$result = self :: down_remote_file_by_wp($remote_url);
		} 
		return $result;
	} 
	public static function curl_exec_follow($ch, &$maxredirect = null) {
		$mr = $maxredirect === null?5:intval($maxredirect);
		if (CAN_FOLLOWLOCATION == 1) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			if ($mr > 0) {
				$newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$rch = curl_copy_handle($ch);
				curl_setopt($rch, CURLOPT_HEADER, true);
				curl_setopt($rch, CURLOPT_NOBODY, true);
				curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
				curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
				do {
					curl_setopt($rch, CURLOPT_URL, $newurl);
					$header = curl_exec($rch);
					if (curl_errno($rch)) {
						$code = 0;
					} else {
						$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
						if ($code == 301 || $code == 302) {
							preg_match('/Location:(.*?)\n/', $header, $matches);
							$newurl = trim(array_pop($matches));
						} else {
							$code = 0;
						} 
					} 
				} while ($code && --$mr);
				curl_close($rch);
				if (!$mr) {
					if ($maxredirect === null) {
						trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
					} else {
						$maxredirect = 0;
					} 
					return false;
				} 
				curl_setopt($ch, CURLOPT_URL, $newurl);
			} 
		} 
		return curl_exec($ch);
	} 
	public static function down_remote_file_by_curl($url, $referer, $useProxy = 0, $proxy = null, $cookie = null, $cookiejar = null) {
		$user_agent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19';
		global $TheDownloadedFileName;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_NOBODY, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_REFERER, $referer);
		if ($cookie != null && $cookie != '') {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		} 
		if ($cookiejar != null && $cookiejar != '') {
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
		} 
		if (!(strpos($url, 'https://') === false)) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		} 
		if (CAN_FOLLOWLOCATION == 1) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
		} 
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, 'WP_Download_Attach_readHeader');
		$rs = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		if ($info['http_code'] != 200) {
			echo self :: raise_error('Can not download remote file!');
			if ($useProxy == 1) {
				echo self :: raise_error('Try use Proxy to download');
				$rs = null;
				$info = null;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_NOBODY, false);
				curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_REFERER, $referer);
				if (!ini_get('safe_mode'))curl_setopt($curl , CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
				curl_setopt($curl, CURLOPT_PROXYPORT, $proxy['port']);
				if ($proxy['user'] != '' && $proxy['user'] != null && $proxy['password'] != '' && $proxy['password'] != null) {
					$userAndPass = $proxy['user'] . ':' . $proxy['password'];
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $userAndPass);
				} 
				if (!(strpos($url, 'https://') === false)) {
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				} 
				if (CAN_FOLLOWLOCATION == 1) {
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
				} 
				$rs = curl_exec($curl);
				$info = curl_getinfo($curl);
				curl_close($curl);
				if ($info['http_code'] != 200) {
					echo self :: raise_error('Use Proxy can not download remote file!');
					return false;
				} 
			} else {
				return false;
			} 
		} 
		$filename_temp = basename ($url);
		$fileInfo = self :: upload_attachment($filename_temp, $rs, $info['content_type']);
		return $fileInfo;
	} 
	public static function down_remote_file_by_wp($remote_url) {
		$http_options = array('timeout' => 120, 'redirection' => 20, 'user-agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19', 'sslverify' => false,);
		$response = wp_remote_get ($remote_url, $http_options);
		$response_code = wp_remote_retrieve_response_code($response);
		$headers = wp_remote_retrieve_headers($response);
		if (200 == $response_code) {
			$filename_temp = basename ($remote_url);
			$fileInfo = self :: upload_attachment($filename_temp, wp_remote_retrieve_body($response), $headers['content-type']);
			return $fileInfo;
		} else {
			return array ('file_path' => '', 'file_name' => '', 'post_mime_type' => '', 'url' => '');
		} 
	} 
	static function upload_attachment($filename, $data, $type) {
		$pos = stripos($filename, "?");
		if ($pos === false) {
			$filename = sanitize_file_name($filename);
		} else {
			global $TheDownloadedFileName;
			if ($TheDownloadedFileName != null && $TheDownloadedFileName != '') {
				$filename = rawurldecode($TheDownloadedFileName);
				if (preg_match('/[^\x21-\x7E]/', $filename)) {
					$file_ext = substr($filename, strrpos($filename, '.'));
					$filename = uniqid() . $file_ext;
				} 
				$filename = sanitize_file_name($filename);
			} else {
				$file_ext = self :: mime_to_ext($type);
				if ($file_ext == null || $file_ext == '') {
					$unknown = true;
					$filename = sanitize_file_name($filename);
				} else {
					$filename = sanitize_file_name($filename) . '.' . $file_ext;
				} 
			} 
		} 
		$time = false;
		$uploads = wp_upload_dir ($time);
		$unique_filename_callback = null;
		$filename = wp_unique_filename ($uploads ['path'], $filename, $unique_filename_callback);
		$new_file = $uploads ['path'] . '/' . $filename;
		if (false === file_put_contents ($new_file, $data)) return false;
		if ($unknown) {
			if (function_exists(mime_content_type)) {
				$mimetype = mime_content_type($new_file);
				$file_ext = self :: mime_to_ext($mimetype);
				if ($file_ext == null || $file_ext == '') {
					$file_ext = 'unknown';
				} 
				$filename = $filename . '.' . $file_ext;
				rename($new_file, $uploads ['path'] . "/$filename");
			} elseif (function_exists(finfo_open)) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimetype = finfo_file($finfo, $new_file);
				finfo_close($finfo);
				$file_ext = self :: mime_to_ext($mimetype);
				if ($file_ext == null || $file_ext == '') {
					$file_ext = 'unknown';
				} 
				$filename = $filename . '.' . $file_ext;
				rename($new_file, $uploads ['path'] . "/$filename");
			} else {
				echo '<p><span class="red">Can not recognize the downloaded file type, Please enable Fileinfo Extension</span></p>';
			} 
			$new_file = $uploads ['path'] . "/$filename";
		} 
		$stat = stat (dirname ($new_file));
		$perms = $stat ['mode'] &0000666;
		@ chmod ($new_file, $perms);
		$url = $uploads ['url'] . "/$filename";
		return array ('file_path' => $new_file, 'file_name' => $filename, 'post_mime_type' => $type, 'url' => $url);
	} 
	public static function insert_attachment($r, $post_id) {
		$name_parts = pathinfo ($r['file_name']);
		$name = trim (substr ($r['file_name'], 0, - (1 + strlen ($name_parts ['extension']))));
		$file = $r['file_path'];
		$title = $name;
		$content = '';
		$attachment = array ('post_mime_type' => $r['post_mime_type'], 'guid' => $r['url'], 'post_parent' => $post_id, 'post_title' => $title, 'post_content' => $content);
		if (isset ($attachment ['ID'])) unset ($attachment ['ID']);
		$id = wp_insert_attachment ($attachment, $file, $post_id);
		return $id;
	} 
} 
function getPageContentbyAP($post, $d, $charset, $autoSet, $config_page_selector, $hasUTFhtml, $UTFhtml, $same_paged, $content_match_type, $content_selector, $outer, $objective, $index, $options, $baseUrl, $url, $filterAtag, $downAttach, $isTest, $useP1, $useP2, $proxy, $cookie) {
	$nextPageContent = '';
	$page_selector = json_decode($config_page_selector);
	if ($page_selector == null) {
		$page_selector = array();
		$page_selector[0] = 0;
		$page_selector[1] = $config_page_selector;
	} 
	if ($page_selector[0] == 0) {
		$pageList = $d -> find($page_selector[1]);
		if ($pageList != null) {
			foreach($pageList as $page) {
				if (trim($page -> href) != '') {
					$findHref = true;
				} 
			} 
			if (!$findHref) $pageList = null;
		} 
	} elseif ($page_selector[0] == 1) {
		if (!$hasUTFhtml) {
			if ($charset != 'UTF-8' && $charset != 'utf-8') {
				$UTFhtml = $d -> save();
				$UTFhtml = iconv($charset, 'UTF-8//IGNORE', $UTFhtml);
				$UTFhtml = compress_html($UTFhtml);
			} else {
				$UTFhtml = $d -> save();
				$UTFhtml = compress_html($UTFhtml);
			} 
		} 
		$pageList = getMatchContent($UTFhtml, $page_selector[1], 1);
	} 
	if ($pageList != null) {
		global $isFetch;
		if ($page_selector[0] == 0) {
			$dom = str_get_html_ap($post);
			$pageAreaIn = $dom -> find($page_selector[1], 0);
			if ($pageAreaIn != null) {
				$pageAreaIn -> parent() -> outertext = '';
				$post = $dom -> save();
				$delPageArea = 1;
			} 
			$dom -> clear();
			unset($dom);
		} else {
			$p0 = stripos($post, $pageList);
			if ($p0 === false) {
				$delPageArea = 0;
			} else {
				$post = str_ireplace($pageList, '', $post);
				$delPageArea = 1;
			} 
			$dom = str_get_html_ap($pageList);
			$pageList = $dom -> find('a');
			$dom -> clear();
			unset($dom);
		} 
		$isFetch[$url] = 1;
		$pageUrl = array();
		$urlLen = strlen(trim($url));
		foreach($pageList as $page) {
			$pUrl = html_entity_decode(trim($page -> href));
			if (stripos($pUrl, 'http') === false) {
				$pUrl = getAbsUrl($pUrl, $baseUrl, $url);
			} 
			$pageUrl[] = $pUrl;
		} 
		$pageUrlNum = count($pageUrl);
		if ($pageUrlNum > 0) {
			if ($pageUrlNum == 1) {
				$pagePara = substr($pageUrl[0], $urlLen);
				$pagePara = str_ireplace('2', '1', $pagePara);
				$isFetch[$url . $pagePara] = 1;
			} else {
				for($i = 0;$i < $pageUrlNum;$i++) {
					if ($pageUrl[$i] != $url) {
						$pagePara = substr($pageUrl[$i], $urlLen);
						$pagePara = str_ireplace('2', '1', $pagePara);
						$isFetch[$url . $pagePara] = 1;
						break;
					} 
				} 
				$s1 = '';
				$s2 = '';
				for($i = 0;$i < $pageUrlNum;$i++) {
					if ($s1 == '' && $pageUrl[$i] != $url) {
						$s1 = $pageUrl[$i];
						continue;
					} 
					if ($s2 == '' && $pageUrl[$i] != $url) {
						$s2 = $pageUrl[$i];
						continue;
					} 
				} 
				$pageURLPattern = getURLPatten($s1, $s2);
				if ($pageURLPattern != null) {
					$pageNum = -1;
					for($i = 1;$i <= 2;$i++) {
						$shouldFetchUrl = str_ireplace('(*)', $i, $pageURLPattern);
						foreach($pageUrl as $pUrl) {
							if (isset($isFetch[$pUrl]) && $isFetch[$pUrl] == 1)continue;
							if ($shouldFetchUrl == $pUrl) {
								$pageNum = $i;
								break;
							} 
						} 
						if ($pageNum != -1)break;
					} 
					if ($pageNum == -1) {
						$pageURLPattern = null;
					} 
				} 
			} 
		} 
		global $page1content;
		$page1content = $post;
		$nextPageContent .= getNextPageContent($pageList, $autoSet, $content_match_type, $content_selector, $outer, $objective, $index, $page_selector, $same_paged, $delPageArea, $options, $charset, $baseUrl, $url, $filterAtag, $downAttach, $isTest, $useP1, $useP2, $proxy, $pageURLPattern, $pageNum, $cookie);
		unset($isFetch);
		unset($page1content);
		unset($pageList);
	} 
	return $post . $nextPageContent;
} 
function getNextPageContent($pageList, $autoSet, $content_match_type, $content_selector, $outer, $objective, $index, $page_selector, $paged, $delPageArea, $options, $charset, $baseUrl, $page1URL, $filterAtag, $downAttach, $isTest, $useP1, $useP2, $proxy, $pageURLPattern, $pageNum, $cookie) {
	global $isFetch;
	$content = '';
	if ($pageList != null) foreach($pageList as $page) {
		$url = $page -> href;
		if (trim($url) == '' || $url == null)continue;
		if (!strpos($url, '#038;') === false) {
			$url = str_ireplace('#038;', '', $url);
		} 
		if (stripos($url, 'http') === false) {
			if (!(stripos($url, 'javascript') === false))continue;
			if (trim($url) == '#')continue;
			$url = getAbsUrl($url, $baseUrl, $page1URL);
		} 
		$url = html_entity_decode(trim($url));
		if (isset($isFetch[$url]) && $isFetch[$url] == 1)continue;
		if ($pageURLPattern != null) {
			$shouldFetchUrl = str_ireplace('(*)', $pageNum, $pageURLPattern);
			if ($shouldFetchUrl != $url)continue;
			$pageNum++;
		} 
		$d = file_get_html_ap(trim($url), $charset, Method, $useP1, $useP2, $proxy, $cookie);
		if ($d == null || $d == '')continue;
		$content = '';
		if ($autoSet) {
			$content .= autoGetContents($d, $charset);
		} else {
			$hasUTFhtml = false;
			$matchNum = count($content_selector);
			foreach($content_match_type as $cmt) {
				if ($cmt == 1) {
					if ($charset != 'UTF-8' && $charset != 'utf-8') {
						$UTFhtml = $d -> save();
						$UTFhtml = iconv($charset, 'UTF-8//IGNORE', $UTFhtml);
						$UTFhtml = compress_html($UTFhtml, true, $d);
					} else {
						$UTFhtml = $d -> save();
						$UTFhtml = compress_html($UTFhtml, true, $d);
					} 
					$hasUTFhtml = true;
					break;
				} 
			} 
			for($i = 0;$i < $matchNum;$i++) {
				if ($content_match_type[$i] == 0) {
					switch ($objective[$i]) {
						case '0': $content .= getContentByCss($d, $content_selector[$i], $options, $charset, $outer[$i], $index[$i]);
							break;
					} 
				} else {
					switch ($objective[$i]) {
						case '0': $content .= getContentByRule($UTFhtml, $content_selector[$i], $options, $outer[$i]);
							break;
					} 
				} 
			} 
		} 
		if ($page_selector[0] == 0) {
			$pageList = $d -> find($page_selector[1]);
			if ($pageList != null && $delPageArea == 1) {
				$delPageAreaString = $pageList[0] -> parent() -> outertext;
				if ($charset != 'UTF-8' && $charset != 'utf-8') {
					$delPageAreaString = iconv($charset, 'UTF-8//IGNORE', $delPageAreaString);
				} 
				$content = str_ireplace($delPageAreaString, '', $content);
			} 
		} elseif ($page_selector[0] == 1) {
			if (!$hasUTFhtml) {
				if ($charset != 'UTF-8' && $charset != 'utf-8') {
					$UTFhtml = $d -> save();
					$UTFhtml = iconv($charset, 'UTF-8//IGNORE', $UTFhtml);
					$UTFhtml = compress_html($UTFhtml);
				} else {
					$UTFhtml = $d -> save();
					$UTFhtml = compress_html($UTFhtml);
				} 
			} 
			$pageList = getMatchContent($UTFhtml, $page_selector[1], 1);
			if ($delPageArea == 1) {
				$content = str_ireplace($pageList, '', $content);
			} 
			if ($pageList != '' && $pageList != null) {
				$dom = str_get_html_ap($pageList);
				$pageList = $dom -> find('a');
				$dom -> clear();
				unset($dom);
			} else {
				$pageList = null;
			} 
		} 
		global $page1content;
		if ($content == $page1content) {
			$isFetch[$url] = 1;
			continue;
		} 
		if ($isTest == 0) {
			if (DEL_COMMENT == 1)$content = filterComment($content);
		} 
		if ($paged == 1 && $content != null && $content != '')$content = '<!--nextpage-->' . $content;
		$isFetch[$url] = 1;
		$d -> clear();
		unset($d);
		unset($UTFhtml);
		$content .= getNextPageContent($pageList, $autoSet, $content_match_type, $content_selector, $outer, $objective, $index, $page_selector, $paged, $delPageArea, $options, $charset, $baseUrl, $page1URL, $filterAtag, $downAttach, $isTest, $useP1, $useP2, $proxy, $pageURLPattern, $pageNum, $cookie);
		break;
	} 
	return $content;
} 
class TimeParseWPAP {
	public static function string2time($timeString) {
		if (preg_match('/[^0-9a-zA-Z- :,+]/', $timeString)) {
			if (preg_match('/[\x7f-\xff]/', $timeString)) {
				$timeString = preg_replace("/[ ]*年[ ]*/", '-', $timeString);
				$timeString = preg_replace("/[ ]*月[ ]*/", '-', $timeString);
				$timeString = preg_replace("/[ ]*点[ ]*/", ':', $timeString);
				$timeString = preg_replace("/[ ]*點[ ]*/", ':', $timeString);
				$timeString = preg_replace("/[ ]*时[ ]*/", ':', $timeString);
				$timeString = preg_replace("/[ ]*時[ ]*/", ':', $timeString);
				$timeString = preg_replace("/[ ]*分.*/", '', $timeString);
				$timeString = preg_replace("/[^0-9 -:]/", '', $timeString);
				$time = strtotime($timeString);
				if ($time == null || $time == '') {
					$timeString = preg_replace("/[^0-9]*?(?=\d)/", '', $timeString, 1);
					$time = strtotime($timeString);
				} 
			} else {
				$timeString = preg_replace("/[^0-9 -:]/", '', $timeString);
				$time = strtotime($timeString);
				if ($time == null || $time == '') {
					$timeString = preg_replace("/[^0-9]*?(?=\d)/", '', $timeString, 1);
					$time = strtotime($timeString);
				} 
			} 
		} else {
			$time = strtotime($timeString);
			if ($time == null || $time == '') {
				$timeString = preg_replace("/[^0-9 -:]/", '', $timeString);
				$time = strtotime($timeString);
				if ($time == null || $time == '') {
					$timeString = preg_replace("/[^0-9]*?(?=\d)/", '', $timeString, 1);
					$time = strtotime($timeString);
				} 
			} 
		} 
		return $time;
	} 
} 
class autopostMicrosoftTranslator {
	private static $language_code = array ('ar' => 'Arabic', 'bg' => 'Bulgarian', 'ca' => 'Catalan', 'zh-CHS' => 'Chinese (Simplified)', 'zh-CHT' => 'Chinese (Traditional)', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'et' => 'Estonian', 'fa' => 'Persian (Farsi)', 'fi' => 'Finnish', 'fr' => 'French', 'de' => 'German', 'el' => 'Greek', 'ht' => 'Haitian Creole', 'he' => 'Hebrew', 'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'lv' => 'Latvian', 'lt' => 'Lithuanian', 'ms' => 'Malay', 'mww' => 'Hmong Daw', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ro' => 'Romanian', 'ru' => 'Russian', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'sv' => 'Swedish', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'vi' => 'Vietnamese');
	public static function bulid_lang_options($selected = '') {
		$options = '';
		foreach (self :: $language_code as $key => $value) {
			$options .= '<option value="' . $key . '" ' . (($selected == $key)?'selected="true"':'') . '>' . $value . '</option>';
		} 
		return $options;
	} 
	public static function get_lang_by_code($key) {
		return self :: $language_code[$key];
	} 
	public static function getTokens($clientID, $clientSecret) {
		try {
			$authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
			$scopeUrl = "http://api.microsofttranslator.com";
			$grantType = "client_credentials";
			$ch = curl_init();
			$paramArr = array ('grant_type' => $grantType, 'scope' => $scopeUrl, 'client_id' => $clientID, 'client_secret' => $clientSecret);
			$paramArr = http_build_query($paramArr, '', '&');
			curl_setopt($ch, CURLOPT_URL, $authUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$strResponse = curl_exec($ch);
			$curlErrno = curl_errno($ch);
			if ($curlErrno) {
				$curlError = curl_error($ch);
				throw new Exception($curlError);
			} 
			curl_close($ch);
			$objResponse = json_decode($strResponse);
			if (@($objResponse -> error)) {
				throw new Exception($objResponse -> error_description);
			} 
			$reValue['access_token'] = $objResponse -> access_token;
			return $reValue;
		} 
		catch (Exception $e) {
			$reValue['err'] = "getTokens() Exception-" . $e -> getMessage();
			return $reValue;
		} 
	} 
	public static function curlRequest($url, $authHeader, $postData = '') {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader, "Content-Type: text/xml"));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		if ($postData) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		} 
		$curlResponse = curl_exec($ch);
		$curlErrno = curl_errno($ch);
		if ($curlErrno) {
			$curlError = curl_error($ch);
			throw new Exception($curlError);
		} 
		curl_close($ch);
		return $curlResponse;
	} 
	public static function createReqXML($fromLanguage, $toLanguage, $contentType, $inputStrArr) {
		$requestXml = "<TranslateArrayRequest>" . "<AppId/>" . "<From>$fromLanguage</From>" . "<Options>" . "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" . "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>" . "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" . "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" . "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" . "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" . "</Options>" . "<Texts>";
		foreach ($inputStrArr as $inputStr) $requestXml .= "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\"><![CDATA[$inputStr]]></string>" ;
		$requestXml .= "</Texts>" . "<To>$toLanguage</To>" . "</TranslateArrayRequest>";
		return $requestXml;
	} 
	public static function translate($token, $src_text, $fromLanguage, $toLanguage, $contentType = 'text/html') {
		try {
			$authHeader = "Authorization: Bearer " . $token;
			$category = 'general';
			$params = "text=" . urlencode($src_text) . "&to=" . $toLanguage . "&from=" . $fromLanguage . "&contentType=" . $contentType;
			$translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
			$curlResponse = self :: curlRequest($translateUrl, $authHeader);
			$xmlObj = simplexml_load_string($curlResponse);
			foreach((array)$xmlObj[0] as $val) {
				$translatedStr = $val;
			} 
			$translated['str'] = $translatedStr;
			return $translated;
		} 
		catch (Exception $e) {
			$translated['err'] = "Exception: " . $e -> getMessage();
			return $translated;
		} 
	} 
	public static function translateArray($token, $textArray, $fromLanguage, $toLanguage, $contentType = 'text/html') {
		try {
			$translated = array();
			$authHeader = "Authorization: Bearer " . $token;
			$translateUrl = "http://api.microsofttranslator.com/V2/Http.svc/TranslateArray";
			$requestXml = self :: createReqXML($fromLanguage, $toLanguage, $contentType, $textArray);
			$curlResponse = self :: curlRequest($translateUrl, $authHeader, $requestXml);
			$xmlObj = simplexml_load_string($curlResponse);
			if (@($xmlObj -> TranslateArrayResponse != null)) {
				foreach($xmlObj -> TranslateArrayResponse as $translatedArrObj) {
					$translated[] = $translatedArrObj -> TranslatedText;
				} 
			} 
			return $translated;
		} 
		catch (Exception $e) {
			$translated = array();
			$translated['err'] = "Exception: " . $e -> getMessage();
			return $translated;
		} 
	} 
} 
class autopostBaiduTranslator {
	private static $language_code = array ('ara' => 'Arabic', 'zh' => 'Chinese', 'en' => 'English', 'fra' => 'French', 'de' => 'German', 'it' => 'Italian', 'jp' => 'Japanese', 'kor' => 'Korean', 'pt' => 'Portuguese', 'ru' => 'Russian', 'spa' => 'Spanish', 'th' => 'Thai',);
	public static function bulid_lang_options($selected = '') {
		$options = '';
		foreach (self :: $language_code as $key => $value) {
			$options .= '<option value="' . $key . '" ' . (($selected == $key)?'selected="true"':'') . '>' . $value . '</option>';
		} 
		return $options;
	} 
	public static function get_lang_by_code($key) {
		return self :: $language_code[$key];
	} 
	public static function curlRequest($url, $postData = null) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($postData) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		} 
		$curlResponse = curl_exec($ch);
		$curlErrno = curl_errno($ch);
		if ($curlErrno) {
			$curlError = curl_error($ch);
			throw new Exception($curlError);
		} 
		curl_close($ch);
		return $curlResponse;
	} 
	public static function translate($src_text, $fromLanguage, $toLanguage, $APIKey, $GET = false) {
		try {
			$translateUrl = "http://openapi.baidu.com/public/2.0/bmt/translate";
			if ($GET) {
				$translateUrl .= '?client_id=' . $APIKey;
				$translateUrl .= '&q=' . urlencode($src_text);
				$translateUrl .= '&from=' . $fromLanguage;
				$translateUrl .= '&to=' . $toLanguage;
				$curlResponse = self :: curlRequest($translateUrl);
			} else {
				$postData = array();
				$postData['client_id'] = $APIKey;
				$postData['q'] = $src_text;
				$postData['from'] = $fromLanguage;
				$postData['to'] = $toLanguage;
				$curlResponse = self :: curlRequest($translateUrl, $postData);
			} 
			$re = json_decode($curlResponse);
			$translated = array();
			if (isset($re -> error_code)) {
				$translated['err'] = $re -> error_msg . '(' . $re -> error_code . ')';
				switch ($re -> error_code) {
					case '52001': $translated['err'] .= '[Time Out]';
						break;
					case '52002': $translated['err'] .= '[The translator system error, try later]';
						break;
					case '52003': $translated['err'] .= '[Unauthorized, please check your API Key]';
						break;
				} 
			} else {
				$translated['trans_result'] = array();
				foreach($re -> trans_result as $trans_result) {
					$translated['trans_result'][] = $trans_result -> dst;
				} 
			} 
			unset($curlResponse);
			return $translated;
		} 
		catch (Exception $e) {
			$translated['err'] = "Exception: " . $e -> getMessage();
			return $translated;
		} 
	} 
} 
class autopostWordAi {
	static function getAccountInfo($email, $pass) {
		if (isset($email) && isset($pass)) {
			$ch = curl_init('http://wordai.com/users/account-api.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, "email=$email&pass=$pass");
			$result = curl_exec($ch);
			curl_close ($ch);
			return $result;
		} else {
			return 'Error: Not All Variables Set!';
		} 
	} 
	static function getSpinText($email, $pass, $type, $text, $quality, $nonested = null, $sentence = null, $paragraph = null) {
		if ($type == 1) {
			$api_url = 'http://wordai.com/users/regular-api.php';
		} else {
			$api_url = 'http://wordai.com/users/turing-api.php';
		} 
		if (isset($text) && isset($quality) && isset($email) && isset($pass)) {
			$text = urlencode($text);
			$ch = curl_init($api_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_POST, 1);
			$postFields = "s=$text&quality=$quality&email=$email&pass=$pass&output=json&returnspin=true";
			if ($nonested == 'on') {
				$postFields .= "&nonested=on";
			} 
			if ($sentence == 'on') {
				$postFields .= "&sentence=on";
			} 
			if ($paragraph == 'on') {
				$postFields .= "&paragraph=on";
			} 
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postFields);
			$result = curl_exec($ch);
			curl_close ($ch);
			return $result;
		} else {
			return 'Error: Not All Variables Set!';
		} 
	} 
} 
function getSpinRewriterSpinText($s, $email, $key, $AutoSentences, $AutoParagraphs, $AutoNewParagraphs, $AutoSentenceTrees, $ConfidenceLevel, $NestedSpintax, $AutoProtectedTerms, $protected_terms = null) {
	$spinrewriter_api = new autopostSpinRewriter($email, $key);
	if ($protected_terms != null) {
		$spinrewriter_api -> setProtectedTerms($protected_terms);
	} 
	if ($AutoSentences == 1) {
		$spinrewriter_api -> setAutoSentences(true);
	} else {
		$spinrewriter_api -> setAutoSentences(false);
	} 
	if ($AutoParagraphs == 1) {
		$spinrewriter_api -> setAutoParagraphs(true);
	} else {
		$spinrewriter_api -> setAutoParagraphs(false);
	} 
	if ($AutoNewParagraphs == 1) {
		$spinrewriter_api -> setAutoNewParagraphs(true);
	} else {
		$spinrewriter_api -> setAutoNewParagraphs(false);
	} 
	if ($AutoSentenceTrees == 1) {
		$spinrewriter_api -> setAutoSentenceTrees(true);
	} else {
		$spinrewriter_api -> setAutoSentenceTrees(false);
	} 
	$spinrewriter_api -> setConfidenceLevel($ConfidenceLevel);
	if ($NestedSpintax == 1) {
		$spinrewriter_api -> setNestedSpintax(true);
	} else {
		$spinrewriter_api -> setNestedSpintax(false);
	} 
	if ($AutoProtectedTerms == 1) {
		$spinrewriter_api -> setAutoProtectedTerms(true);
	} else {
		$spinrewriter_api -> setAutoProtectedTerms(false);
	} 
	return $spinrewriter_api -> getUniqueVariation($s);
} 
class autopostSpinRewriter {
	var $data;
	var $response;
	var $api_url;
	function autopostSpinRewriter($email_address, $api_key) {
		$this -> data = array();
		$this -> data['email_address'] = $email_address;
		$this -> data['api_key'] = $api_key;
		$this -> api_url = "http://www.spinrewriter.com/action/api";
	} 
	function getQuota() {
		$this -> data['action'] = "api_quota";
		$this -> makeRequest();
		return $this -> parseResponse();
	} 
	function getTextWithSpintax($text) {
		$this -> data['action'] = "text_with_spintax";
		$this -> data['text'] = $text;
		$this -> makeRequest();
		return $this -> parseResponse();
	} 
	function getUniqueVariation($text) {
		$this -> data['action'] = "unique_variation";
		$this -> data['text'] = $text;
		$this -> makeRequest();
		return $this -> parseResponse();
	} 
	function getUniqueVariationFromSpintax($text) {
		$this -> data['action'] = "unique_variation_from_spintax";
		$this -> data['text'] = $text;
		$this -> makeRequest();
		return $this -> parseResponse();
	} 
	function setProtectedTerms($protected_terms) {
		$this -> data['protected_terms'] = "";
		if (strpos($protected_terms, "\n") !== false || (strpos($protected_terms, ",") === false && !is_array($protected_terms))) {
			$protected_terms = trim($protected_terms);
			if (strlen($protected_terms) > 0) {
				$this -> data['protected_terms'] = $protected_terms;
				return true;
			} else {
				return false;
			} 
		} else if (strpos($protected_terms, ",") !== false && !is_array($protected_terms)) {
			$protected_terms_explode = explode(",", $protected_terms);
			foreach ($protected_terms_explode as $protected_term) {
				$protected_term = trim($protected_term);
				if ($protected_term) {
					$this -> data['protected_terms'] .= $protected_term . "\n";
				} 
				$this -> data['protected_terms'] = $this -> data['protected_terms'];
			} 
			$this -> data['protected_terms'] = trim($this -> data['protected_terms']);
			return true;
		} else if (is_array($protected_terms)) {
			$protected_terms_explode = explode(",", $protected_terms);
			foreach ($protected_terms_explode as $protected_term) {
				$protected_term = trim($protected_term);
				if ($protected_term) {
					$this -> data['protected_terms'] .= $protected_term . "\n";
				} 
				$this -> data['protected_terms'] = $this -> data['protected_terms'];
			} 
			$this -> data['protected_terms'] = trim($this -> data['protected_terms']);
			return true;
		} else {
			return false;
		} 
	} 
	function setAutoProtectedTerms($auto_protected_terms) {
		if ($auto_protected_terms == "true" || $auto_protected_terms === true) {
			$auto_protected_terms = "true";
		} else {
			$auto_protected_terms = "false";
		} 
		$this -> data['auto_protected_terms'] = $auto_protected_terms;
		return true;
	} 
	function setConfidenceLevel($confidence_level) {
		$this -> data['confidence_level'] = $confidence_level;
		return true;
	} 
	function setNestedSpintax($nested_spintax) {
		if ($nested_spintax == "true" || $nested_spintax === true) {
			$nested_spintax = "true";
		} else {
			$nested_spintax = "false";
		} 
		$this -> data['nested_spintax'] = $nested_spintax;
		return true;
	} 
	function setAutoSentences($auto_sentences) {
		if ($auto_sentences == "true" || $auto_sentences === true) {
			$auto_sentences = "true";
		} else {
			$auto_sentences = "false";
		} 
		$this -> data['auto_sentences'] = $auto_sentences;
		return true;
	} 
	function setAutoParagraphs($auto_paragraphs) {
		if ($auto_paragraphs == "true" || $auto_paragraphs === true) {
			$auto_paragraphs = "true";
		} else {
			$auto_paragraphs = "false";
		} 
		$this -> data['auto_paragraphs'] = $auto_paragraphs;
		return true;
	} 
	function setAutoNewParagraphs($auto_new_paragraphs) {
		if ($auto_new_paragraphs == "true" || $auto_new_paragraphs === true) {
			$auto_new_paragraphs = "true";
		} else {
			$auto_new_paragraphs = "false";
		} 
		$this -> data['auto_new_paragraphs'] = $auto_new_paragraphs;
		return true;
	} 
	function setAutoSentenceTrees($auto_sentence_trees) {
		if ($auto_sentence_trees == "true" || $auto_sentence_trees === true) {
			$auto_sentence_trees = "true";
		} else {
			$auto_sentence_trees = "false";
		} 
		$this -> data['auto_sentence_trees'] = $auto_sentence_trees;
		return true;
	} 
	function setSpintaxFormat($spintax_format) {
		$this -> data['spintax_format'] = $spintax_format;
		return true;
	} 
	private function parseResponse() {
		return json_decode($this -> response, true);
	} 
	private function makeRequest() {
		$data_raw = "";
		foreach ($this -> data as $key => $value) {
			$data_raw = $data_raw . $key . "=" . urlencode($value) . "&";
		} 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this -> api_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_raw);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this -> response = trim(curl_exec($ch));
		curl_close($ch);
	} 
} 
class autopostRSS {
	public $document;
	public $channel;
	public $items;
	public function load($url = false, $unblock = true) {
		if ($url) {
			if ($unblock) {
				$this -> loadParser(file_get_contents($url, false, $this -> randomContext()));
			} else {
				$this -> loadParser(file_get_contents($url));
			} 
		} 
	} 
	public function loadRSS($rawxml = false) {
		if ($rawxml) {
			$this -> loadParser($rawxml);
		} 
	} 
	public function getRSS($includeAttributes = false) {
		if ($includeAttributes) {
			return $this -> document;
		} 
		return $this -> valueReturner();
	} 
	public function getChannel($includeAttributes = false) {
		if ($includeAttributes) {
			return $this -> channel;
		} 
		return $this -> valueReturner($this -> channel);
	} 
	public function getItems($includeAttributes = false) {
		if ($includeAttributes) {
			return $this -> items;
		} 
		return $this -> valueReturner($this -> items);
	} 
	private function loadParser($rss = false) {
		if ($rss) {
			$this -> document = array();
			$this -> channel = array();
			$this -> items = array();
			$DOMDocument = new DOMDocument;
			$DOMDocument -> strictErrorChecking = false;
			$DOMDocument -> loadXML($rss);
			$this -> document = $this -> extractDOM($DOMDocument -> childNodes);
		} 
	} 
	private function valueReturner($valueBlock = false) {
		if (!$valueBlock) {
			$valueBlock = $this -> document;
		} 
		foreach($valueBlock as $valueName => $values) {
			if (isset($values['value'])) {
				$values = $values['value'];
			} 
			if (is_array($values)) {
				$valueBlock[$valueName] = $this -> valueReturner($values);
			} else {
				$valueBlock[$valueName] = $values;
			} 
		} 
		return $valueBlock;
	} 
	private function extractDOM($nodeList, $parentNodeName = false) {
		$itemCounter = 0;
		foreach($nodeList as $values) {
			if (substr($values -> nodeName, 0, 1) != '#') {
				if ($values -> nodeName == 'item') {
					$nodeName = $values -> nodeName . ':' . $itemCounter;
					$itemCounter++;
				} else {
					$nodeName = $values -> nodeName;
				} 
				$tempNode[$nodeName] = array();
				if ($values -> attributes) {
					for($i = 0;$values -> attributes -> item($i);$i++) {
						$tempNode[$nodeName]['properties'][$values -> attributes -> item($i) -> nodeName] = $values -> attributes -> item($i) -> nodeValue;
					} 
				} 
				if (!$values -> firstChild) {
					$tempNode[$nodeName]['value'] = $values -> textContent;
				} else {
					$tempNode[$nodeName]['value'] = $this -> extractDOM($values -> childNodes, $values -> nodeName);
				} 
				if (in_array($parentNodeName, array('channel', 'rdf:RDF'))) {
					if ($values -> nodeName == 'item') {
						$this -> items[] = $tempNode[$nodeName]['value'];
					} elseif (!in_array($values -> nodeName, array('rss', 'channel'))) {
						$this -> channel[$values -> nodeName] = $tempNode[$nodeName];
					} 
				} 
			} elseif (substr($values -> nodeName, 1) == 'text') {
				$tempValue = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", ' ', $values -> textContent)));
				if ($tempValue) {
					$tempNode = $tempValue;
				} 
			} elseif (substr($values -> nodeName, 1) == 'cdata-section') {
				$tempNode = $values -> textContent;
			} 
		} 
		return $tempNode;
	} 
	private function randomContext() {
		$headerstrings = array();
		$headerstrings['User-Agent'] = 'Mozilla/5.0 (Windows; U; Windows NT 5.' . rand(0, 2) . '; en-US; rv:1.' . rand(2, 9) . '.' . rand(0, 4) . '.' . rand(1, 9) . ') Gecko/2007' . rand(10, 12) . rand(10, 30) . ' Firefox/2.0.' . rand(0, 1) . '.' . rand(1, 9);
		$headerstrings['Accept-Charset'] = rand(0, 1) ? 'en-gb,en;q=0.' . rand(3, 8) : 'en-us,en;q=0.' . rand(3, 8);
		$headerstrings['Accept-Language'] = 'en-us,en;q=0.' . rand(4, 6);
		$setHeaders = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5' . "\r\n" . 'Accept-Charset: ' . $headerstrings['Accept-Charset'] . "\r\n" . 'Accept-Language: ' . $headerstrings['Accept-Language'] . "\r\n" . 'User-Agent: ' . $headerstrings['User-Agent'] . "\r\n";
		$contextOptions = array('http' => array('method' => "GET", 'header' => $setHeaders));
		return stream_context_create($contextOptions);
	} 
} 
