<?php
// Full-Text RSS: Simple extraction - results in JSON
// Author: Keyvan Minoukadeh
// Copyright (c) 2014 Keyvan Minoukadeh
// License: AGPLv3
// Version: 3.3
// Date: 2014-05-07
// More info: http://fivefilters.org/content-only/
// Help: http://help.fivefilters.org

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Usage
// -----
// Request this file passing it a web page URL in the querystring: extract.php?url=example.org
// You can use GET and POST requests.
// You'll get a simple JSON response:
/*
HTTP/1.0 200 OK
{
    "title": "Blowing Smoke with Boxing's Big Voice",	
    "content" <div><p>Content here</p><p>More content</p></div>",
    "author": "Rafi Kohan",
    "excerpt": "Short extract from the beginning of the article.",
    "language": "en",
    "url": "http://example.org/article.html",
    "effective_url": "http://example.org/article.html",
    "date": "2014-05-10"
}
*/

define('_FF_FTR_MODE', 'simple');

// Don't process URL as feed
$_POST['accept'] = 'html';
// JSON output only
$_POST['format'] = 'json';
// Enable excerpts
$_POST['summary'] = '1';
// Don't produce result if extraction fails
$_POST['exc'] = '1';
// Enable XSS filtering (unless explicitly disabled)
if (isset($_POST['xss']) && $_POST['xss'] !== '0') {
	$_POST['xss'] = '1';
} elseif (isset($_GET['xss']) && $_GET['xss'] !== '0') {
	$_GET['xss'] = '1';
} else {
	$_POST['xss'] = '1';
}

require 'makefulltextfeed.php';