<?php
class DisableSimplePieSanitize extends SimplePie_Sanitize {
	function sanitize($data, $type, $base = '') {
		return $data;
	}
}