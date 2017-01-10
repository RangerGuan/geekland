<?php
require_once(dirname(__FILE__).'/config.php');
// check for custom index.php (custom_index.php)
if (!defined('_FF_FTR_INDEX')) {
	define('_FF_FTR_INDEX', true);
	if (file_exists(dirname(__FILE__).'/custom_index.php')) {
		include(dirname(__FILE__).'/custom_index.php');
		exit;
	}
}
?><!DOCTYPE html>
<html>
  <head>
    <title>Full-Text RSS Feeds | from fivefilters.org</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
	<meta name="robots" content="noindex, follow" />
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-tooltip.js"></script>
	<script type="text/javascript" src="js/bootstrap-popover.js"></script>
	<script type="text/javascript" src="js/bootstrap-tab.js"></script>
	<script type="text/javascript">
	var baseUrl = 'http://'+window.location.host+window.location.pathname.replace(/(\/index\.php|\/)$/, '');
	$(document).ready(function() {
		// remove http scheme from urls before submitting
		$('#form').submit(function() {
			$('#url').val($('#url').val().replace(/^http:\/\//i, ''));
			return true;
		});
		// popovers
		$('#url').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#key').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#max').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#links').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#exc').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		// tooltips
		$('a[rel=tooltip]').tooltip();
	});
	</script>
	<style>
	html, body { background-color: #eee; }
	body { margin: 0; line-height: 1.4em; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; }
	label, input, select, textarea { font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; }
	li { color: #404040; }
	li.active a { font-weight: bold; color: #666 !important; }
	form .controls { margin-left: 220px !important; }
	label { width: 200px !important; }
	fieldset legend { padding-left: 220px; line-height: 20px !important; margin-bottom: 10px !important;}
	.form-actions { padding-left: 220px !important; }
	.popover-inner { width: 205px; }
	h1 { margin-bottom: 18px; }

	/* JSON Prettify CSS from http://chris.photobooks.com/json/default.htm */
	.jsonOutput.PRETTY {
		font-family: Consolas, "Courier New", monospace;
		background-color: #333;
		color: #fff;
		padding: 10px; 
		border-radius: 4px;
	}
	.ERR             { color: #FF0000; font-weight: bold; }
	.FUNC            { color: #FF0000; font-weight: bold; }
	.IDK             { color: #FF0000; font-weight: bold; }
	.KEY             { color: #FFFFFF; font-weight: bold; }
	.BOOL            { color: #00FFFF; }
	.NUMBER          { color: #7FFF00; }
	.DATE            { color: #6495ED; }
	.REGEXP          { color: #DEB887; }
	.STRING          { color: #D8FFB0; }
	.UNDEF           { color: #91AA9D; font-style: italic; }
	.NULL            { color: #91AA9D; font-style: italic; }
	.EMPTY           { color: #91AA9D; font-style: italic; }
	.HTML span.ARRAY { color: #91AA9D; font-style: italic; }
	.HTML span.OBJ   { color: #91AA9D; font-style: italic; }
	table.OBJ        { background-color: #22353C; }
	table.ARRAY      { background-color: #252C47; }

	</style>
  </head>
  <body>
	<div class="container" style="width: 800px; padding-bottom: 60px;">
	<h1 style="padding-top: 5px;">Full-Text RSS <?php echo _FF_FTR_VERSION; ?> <span style="font-size: .7em; font-weight: normal;">&mdash; from <a href="http://fivefilters.org">FiveFilters.org</a></span></h1>
    <form method="get" action="makefulltextfeed.php" id="form" class="form-horizontal">
	<fieldset>
		<legend>Create full-text feed from feed or webpage URL</legend>
		<div class="control-group">
			<label class="control-label" for="url">Enter URL</label>
			<div class="controls"><input type="text" id="url" name="url" style="width: 450px;" title="URL" data-content="Typically this is a URL for a partial feed which we transform into a full-text feed. But it can also be a standard web page URL, in which case we'll extract its content and return it in a 1-item feed." /></div>
		</div>
	</fieldset>
	<fieldset>
	<legend>Options</legend>
	<?php if (isset($options->api_keys) && !empty($options->api_keys)) { ?>
	<div class="control-group">
	<label class="control-label" for="key">Access key</label>
	<div class="controls">
	<input type="text" id="key" name="key" class="input-medium" <?php if ($options->key_required) echo 'required'; ?> title="Access Key" data-content="<?php echo ($options->key_required) ? 'An access key is required to generate a feed' : 'If you have an access key, enter it here.'; ?>" />
	</div>
	</div>
	<?php } ?>
	<div class="control-group">
	<label class="control-label" for="max">Max items</label>
	<div class="controls">
	<?php
	// echo '<select name="max" id="max" class="input-medium">'
	// for ($i = 1; $i <= $options->max_entries; $i++) {
	//	printf("<option value=\"%s\"%s>%s</option>\n", $i, ($i==$options->default_entries) ? ' selected="selected"' : '', $i);
	// } 
	// echo '</select>';
	if (!empty($options->api_keys)) {
		$msg = 'Limit: '.$options->max_entries.' (with key: '.$options->max_entries_with_key.')';
		$msg_more = 'If you need more items, change <tt>max_entries</tt> (and <tt>max_entries_with_key</tt>) in config.';
	} else {
		$msg = 'Limit: '.$options->max_entries;
		$msg_more = 'If you need more items, change <tt>max_entries</tt> in config.';
	}
	?>	
	<input type="text" name="max" id="max" class="input-mini" value="<?php echo $options->default_entries; ?>" title="Feed item limit" data-content="Set the maximum number of feed items we should process. The smaller the number, the faster the new feed is produced.<br /><br />If your URL refers to a standard web page, this will have no effect: you will only get 1 item.<br /><br /> <?php echo $msg_more; ?>" />
	<span class="help-inline" style="color: #888;"><?php echo $msg; ?></span>
	</div>
	</div>
	<div class="control-group">
	<label class="control-label" for="links">Links</label>
	<div class="controls">
	<select name="links" id="links" class="input-medium" title="Link handling" data-content="By default, links within the content are preserved. Change this field if you'd like links removed, or included as footnotes.">
		<option value="preserve" selected="selected">preserve</option>
		<option value="footnotes">add to footnotes</option>
		<option value="remove">remove</option>
	</select>
	</div>
	</div>
	<?php if ($options->exclude_items_on_fail == 'user') { ?>
	<div class="control-group">
	<label class="control-label" for="exc">If extraction fails</label>
	<div class="controls">
	<select name="exc" id="exc" title="Item handling when extraction fails" data-content="If extraction fails, we can remove the item from the feed or keep it in.<br /><br />Keeping the item will keep the title, URL and original description (if any) found in the feed. In addition, we insert a message before the original description notifying you that extraction failed.">
		<option value="" selected="selected">keep item in feed</option>
		<option value="1">remove item from feed</option>
	</select>
	</div>
	</div>
	<?php } ?>
	
	<?php if ($options->summary == 'user') { ?>
	<div class="control-group">
	<label class="control-label" for="summary">Include excerpt</label>
	<div class="controls">
	<input type="checkbox" name="summary" value="1" id="summary" style="margin-top: 7px;" />
	</div>
	</div>
	<?php } ?>

	<div class="control-group" style="margin-top: -15px;">
	<label class="control-label" for="json">JSON output</label>
	<div class="controls">
	<input type="checkbox" name="format" value="json" id="json" style="margin-top: 7px;" />
	</div>
	</div>
	
	<div class="control-group" style="margin-top: -15px;">
	<label class="control-label" for="debug">Debug</label>
	<div class="controls">
	<input type="checkbox" name="debug" value="1" id="debug" style="margin-top: 7px;" />
	</div>
	</div>	
	
	</fieldset>
	<div class="form-actions">
		<input type="submit" id="sudbmit" name="submit" value="Create Feed" class="btn btn-primary" />
	</div>
	</form>
	
	
	<ul class="nav nav-tabs">
	<li class="active"><a href="#start" data-toggle="tab">Getting Started</a></li>
	<li><a href="#general" data-toggle="tab">General Info</a></li>
	<li><a href="#request" data-toggle="tab">Request and Response</a></li>	
	<li><a href="#updates" data-toggle="tab">Updates</a></li>
	<li><a href="#license" data-toggle="tab">License</a></li>
	</ul>
	
	<div class="tab-content">
	
	<!-- GETTING STARTED TAB -->
	
	<div class="active tab-pane" id="start">
	
	<h3>Quick start</h3>
	<ol>
		<li><a href="ftr_compatibility_test.php">Check server compatibility</a> to make sure this server meets the requirements</li>
		<li>Enter a feed or article URL in the form above and click 'Create Feed' <a href="http://help.fivefilters.org/customer/portal/articles/223127-suggested-feeds-and-articles" rel="tooltip" title="Need suggestions? We've got a number of feeds and articles you can try" class="label">?</a></li>
		<li>If the generated full-text feed looks okay, copy the URL from your browser's address bar and use it in your news reader or application</li>
		<li><strong>That's it!</strong> (Although see below if you'd like to customise further.)</li>
	</ol>
	
	<h3>Configure</h3>
	<p>In addition to the options above, Full-Text RSS comes with a configuration file which allows you to control how the application works. <a href="http://help.fivefilters.org/customer/portal/articles/223410-configure">Find out more.</a></p>
	<p>Features include:</p>
	<ul>
		<li>Site patterns for better control over extraction (<a href="http://help.fivefilters.org/customer/portal/articles/223153-site-patterns">more info</a>)</li>
		<li>Restrict access to those with an access key and/or to a pre-defined set of URLs</li>
		<li>Restrict the maximum number of feed items to be processed</li>
		<li>Prepend or append an HTML fragment to each feed item processed</li>
		<li>Caching</li>		
	</ul>
	<p><?php if (!file_exists('custom_config.php')) { ?>To change the configuration, save a copy of <tt>config.php</tt> as <tt>custom_config.php</tt> and make any changes you like to it.<?php } else { ?>To change the configuration, edit <tt>custom_config.php</tt> and make any changes you like.<?php } ?></p>

	<h3>Manage and update site config files</h3>
	<p>For best results, we suggest you update the site config files bundled with Full-Text RSS.</p>
	<p>The easiest way to update these is via the <a href="admin/">admin area</a>. (For advanced users, you'll also be able to edit and test the extraction rules contained in the site config files from the admin area.)</p>

	<h3>Customise this page</h3>
	<p>If everything works fine, feel free to modify this page by following the steps below:</p>
	<ol>
		<li>Save a copy of <tt>index.php</tt> as <tt>custom_index.php</tt></li>
		<li>Edit <tt>custom_index.php</tt></li>
	</ol>
	<p>Next time you load this page, it will automatically load custom_index.php instead.</p>
	
	<h3 id="support">Support</h3>
	<p>Check our <a href="http://help.fivefilters.org">help centre</a> if you need help. You can also email us at <a href="mailto:help@fivefilters.org">help@fivefilters.org</a>.</p>
	
	<h3>Thank you!</h3>
	<p>Thanks for downloading and setting up Full-Text RSS. This software is developed and maintained by FiveFilters.org. If you find it useful, but have not purchased this from us, please consider supporting us by purchasing from <a href="http://fivefilters.org/content-only/">FiveFilters.org</a>.</p>

	</div>

	<!-- REQUEST PARAMS -->
	
	<div id="request" class="tab-pane">
	
	<h3>Request and Response</h3>

	<p>The details on this page are mainly intended for developers who'd like to use Full-Text RSS for article extraction and feed conversion. 
	News enthusiasts who simply want to subscribe to a full-text feed in their news reading application can safely ignore the details here and use the form above.</p>

	<p>This page describes the two endpoints offered by Full-Text RSS: <a href="#article-extraction">Article Extraction</a> and <a href="#feed-conversion">Feed Conversion</a>. If you've restricted access to Full-Text RSS, the final section on <a href="#api-keys">API keys</a> will tell you how to pass your key along in the request.</p>

	<hr />
	<h3 id="article-extraction">1. Article Extraction</h3>
	<p>To extract article content from a web page and get a simple JSON response, use the following endpoint:</p>
	<ul>
		<li style="font-family: monospace;"><script type="text/javascript">document.write(baseUrl);</script>/extract.php?url=<strong>[url]</strong></li>
	</ul>
	
	<h3>Request Parameters</h3>
	
	<p>When making HTTP requests, you can pass the following parameters to <tt>extract.php</tt> in a GET or POST request.</p>
	<p>Note: for many of these parameters, the configuration file will ultimately determine if and how they can be used.</p>
	<table width="100%" border="0" class="parameters table table-bordered">
		<thead>
		<tr style="background-color: #ddd">
			<th width="13%">Parameter</th>
			<th width="19%">Value</th>
			<th width="68%">Description</th>
		</tr>
		</thead>
		<tbody>

		<tr>
			<td>url</td>
			<td>string (URL)</td>
			<td>This is the only required parameter. It should be the URL to a standard HTML page. You can omit the 'http://' prefix if you like.</td>
		</tr>
		
		<tr>
			<td>inputhtml</td>
			<td>string (HTML)</td>
			<td>If you already have the HTML, you can pass it here. We will not make any HTTP requests for the content if this parameter is used. Note: The input HTML should be UTF-8 encoded. And you will still need to give us the URL associated with the content (the URL may determine how the content is extracted, if we have extraction rules associated with it).</td>
		</tr>

		<tr>
			<td>content</td>
			<td><tt>0</tt>, <tt>1</tt> (default)</td>
			<td>If set to 0, the extracted content will not be included in the output.</td>
		</tr>
		
		<tr>
			<td>links</td>
			<td><tt>preserve</tt> (default), <tt>footnotes</tt>, <tt>remove</tt></td>
			<td>Links can either be preserved, made into footnotes, or removed. None of these options affect the link text, only the hyperlink itself.</td>
		</tr>
		
		<tr>
			<td>xss</td>
			<td><tt>0</tt>, <tt>1</tt> (default)</td>
			<td><p>Use this to enable/disable XSS filtering. It is enabled by default, but if your application/framework/CMS already filters HTML for XSS vulnerabilities, you can disable XSS filtering here.</p>
<p>If enabled, we'll pass retrieved HTML content through htmLawed (safe flag on and style attributes denied). Note: when enabled this will remove certain elements you may want to preserve, such as iframes.</p></td>
		</tr>

		<tr>
			<td>lang</td>
			<td><tt>0</tt>, <tt>1</tt> (default), <tt>2</tt>, <tt>3</tt></td>
			<td><p>Language detection. If you'd like Full-Text RSS to find the language of the articles it processes, you can use one of the following values:</p>
			<dl>
				<dt>0</dt><dd>Ignore language</dd>
				<dt>1</dt><dd>Use article metadata (e.g. HTML lang attribute) (Default value)</dd>
				<dt>2</dt><dd>As above, but guess the language if it's not specified.</dd>
				<dt>3</dt><dd>Always guess the language, whether it's specified or not.</dd>
			</dl>
			</td>
		</tr>
		
		<tr>
			<td>debug</td>
			<td>[no value], <tt>rawhtml</tt>, <tt>parsedhtml</tt></td>
			<td><p>If this parameter is present, Full-Text RSS will output the steps it is taking behind the scenes to help you debug problems.</p>
			<p>If the parameter value is <tt>rawhtml</tt>, Full-Text RSS will output the HTTP response (headers and body) of the first response after redirects.</p> 
			<p>If the parameter value is <tt>parsedhtml</tt>, Full-Text RSS will output the reconstructed HTML (after its own parsing). This version is what the extraction rules are applied to, and it may differ from the original (<tt>rawhtml</tt>) output. If your extraction rules are not picking out any elements, this will likely help identify the problem.</p>
			<p>Note: Full-Text RSS will stop execution after HTML output if one of the last two parameter values are passed. Otherwise it will continue showing debug output until the end.</p></td>
		</tr>
		
		<tr>
			<td>parser</td>
			<td><tt>html5php</tt>, <tt>libxml</tt></td>
			<td>The default parser is libxml as it's the fastest. HTML5-PHP is an HTML5 parser implemented in PHP. It's slower than libxml, but can often produce better results. You can request HTML5-PHP be used as the parser in a site-specific config file (to ensure it gets used for all URLs for that site), or explicitly via this request parameter.</td>
		</tr>

		<tr>
			<td>siteconfig</td>
			<td>string</td>
			<td>Site-specific extraction rules are usually stored in text files in the site_config folder. You can also submit <a href="http://help.fivefilters.org/customer/portal/articles/223153-site-patterns">extraction rules</a> directly in your request using this parameter.</td>
		</tr>
		
		<tr>
			<td>proxy</td>
			<td><tt>0</tt>, <tt>1</tt>, string (proxy name)</td>
			<td>This parameter has no effect if proxy servers have not been entered in the config file. If they have been entered and enabled, you can pass the following values: 0 to disable proxy use (uses direct connection). 1 for default proxy behaviour (whatever is set in the config), or a string to identify a specific proxy server (has to match the name given to the proxy in the config file).</td>
		</tr>
	
		</tbody>
	</table>


	<h3>Response (example)</h3>
	<p>Simple JSON output containing extracted article title, content, and more. It was produced from the following input URL: http://chomsky.info/articles/20131105.htm</p>
	<!-- Generated by http://chris.photobooks.com/json/default.htm -->
	<output style="display: block;" for="jsonInput jsonStrict jsonEval json2HTML json2JSON jsonTrunc jsonDate jsonData jsonSpace" class="jsonOutput PRETTY"><span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"De-Americanizing the World"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"excerpt": <span title="String" class="STRING">"During the latest episode of the Washington farce that has astonish…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"date": <span title="null" class="NULL">null</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"author": <span title="String" class="STRING">"Noam Chomsky"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"language": <span title="String" class="STRING">"en"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://chomsky.info/articles/20131105.htm"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"effective_url": <span title="String" class="STRING">"http://chomsky.info/articles/20131105.htm"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"content": <span title="String" class="STRING">"&lt;p&gt;During the latest episode of the Washington farce that has aston…"</span></span><br>}</span></output>
	<p>Note: For brevity the output above is truncated.</p>

	<hr />
	<h3 id="feed-conversion">2. Feed Conversion</h3>
	<p>To transform a partial feed to a full-text feed, pass the URL (<a href="http://meyerweb.com/eric/tools/dencoder/">encoded</a>) in the querystring to the following URL:</p>
	<ul>
		<li style="font-family: monospace;"><script type="text/javascript">document.write(baseUrl);</script>/makefulltextfeed.php?url=<strong>[url]</strong></li>
	</ul>
	
	<p>All the parameters in the form at the top of this page can be passed in this way. Examine the URL in the address bar after you click 'Create Feed' to see the values.</p>
	
	<h3>Request Parameters</h3>
	
	<p>When making HTTP requests, you can pass the following parameters to <tt>makefulltextfeed.php</tt> in a GET request. Most of these parameters have default values suitable for news enthusiasts who simply want to subscribe to a full-text feed in their news reading application. If that's what you're doing, you can safely ignore the details here. For developers, or others who need more control over the output produced by Full-Text RSS, this section should give you an idea of what you can do.</p>
	<p>We do not provide form fields for all of these parameters, but you can modify the URL in your browser after clicking 'Create Feed' to use them.</p>
	<p>Note: for many of these parameters, the configuration file will ultimately determine if and how they can be used.</p>
	<table width="100%" border="0" class="parameters table table-bordered">
		<thead>
		<tr style="background-color: #ddd">
			<th width="13%">Parameter</th>
			<th width="19%">Value</th>
			<th width="68%">Description</th>
		</tr>
		</thead>
		<tbody>

		<tr>
			<td>url</td>
			<td>string (URL)</td>
			<td>This is the only required parameter. It should be the URL to a partial feed or a standard HTML page. You can omit the 'http://' prefix if you like.</td>
		</tr>

		<tr>
			<td>format</td>
			<td><tt>rss</tt> (default), <tt>json</tt></td>
			<td>The default Full-Text RSS output is RSS. The only other valid output format is JSON. To get JSON output, pass format=json in the querystring. Exclude it from the URL (or set it to ‘rss’) if you’d like RSS.</td>
		</tr>	

		<tr>
			<td>summary</td>
			<td><tt>0</tt> (default), <tt>1</tt></td>
			<td>If set to 1, an excerpt will be included for each item in the output.</td>
		</tr>			

		<tr>
			<td>content</td>
			<td><tt>0</tt>, <tt>1</tt> (default)</td>
			<td>If set to 0, the extracted content will not be included in the output.</td>
		</tr>

		<tr>
			<td>links</td>
			<td><tt>preserve</tt> (default), <tt>footnotes</tt>, <tt>remove</tt></td>
			<td>Links can either be preserved, made into footnotes, or removed. None of these options affect the link text, only the hyperlink itself.</td>
		</tr>
		
		<tr>
			<td>exc</td>
			<td><tt>0</tt> (default), <tt>1</tt></td>
			<td>If Full-Text RSS fails to extract the article body, the generated feed item will include a message saying extraction failed followed by the original item description (if present in the original feed). You ask Full-Text RSS to remove such items from the generated feed completely by passing 1 in this parameter.</td>
		</tr>
		
		<tr>
			<td>accept</td>
			<td><tt>auto</tt> (default), <tt>feed</tt>, <tt>html</tt></td>
			<td><p>Tell Full-Text RSS what it should expect when fetching the input URL. By default Full-Text RSS tries to guess whether the response is a feed or regular HTML page. It's a good idea to be explicit by passing the appropriate type in this parameter. This is useful if, for example, a feed stops working and begins to return HTML or redirecs to a HTML page as a result of site changes. In such a scenario, if you've been explicit about the URL being a feed, Full-Text RSS will not parse HTML returned in response. If you pass accept=html (previously html=1), Full-Text RSS will not attempt to parse the response as a feed. This increases performance slightly and should be used if you know that the URL is not a feed.</p>

			<p>Note: If excluded, or set to <tt>auto</tt>, Full-Text RSS first tries to parse the server's response as a feed, and only if it fails to parse as a feed will it revert to HTML parsing. In the default parse-as-feed-first mode, Full-Text RSS will identify itself as PHP first and only if a valid feed is returned will it identify itself as a browser in subsequent requests to fetch the feed items. In parse-as-html mode, Full-Text RSS will identify itself as a browser from the very first request.</p></td>
		</tr>
		
		<tr>
			<td>xss</td>
			<td><tt>0</tt> (default), <tt>1</tt></td>
			<td><p>Use this to enable XSS filtering. We have not enabled this by default because we assume the majority of our users do not display the HTML retrieved by Full-Text RSS in a web page without further processing. If you subscribe to our generated feeds in your news reader application, it should, if it's good software, already filter the resulting HTML for XSS attacks, making it redundant for Full-Text RSS do the same. Similarly with frameworks/CMSs which display feed content - the content should be treated like any other user-submitted content.</p>

			<p>If you are writing an application yourself which is processing feeds generated by Full-Text RSS, you can either filter the HTML yourself to remove potential XSS attacks or enable this option. This might be useful if you are processing our generated feeds with JavaScript on the client side - although there's client side xss filtering available too.</p>

			<p>If enabled, we'll pass retrieved HTML content through htmLawed (safe flag on and style attributes denied). Note: if enabled this will also remove certain elements you may want to preserve, such as iframes.</p></td>
		</tr>
		
		<tr>
			<td>callback</td>
			<td>string</td>
			<td>This is for JSONP use. If you're requesting JSON output, you can also specify a callback function (Javascript client-side function) to receive the Full-Text RSS JSON output.</td>
		</tr>				
		
		<tr>
			<td>lang</td>
			<td><tt>0</tt>, <tt>1</tt> (default), <tt>2</tt>, <tt>3</tt></td>
			<td><p>Language detection. If you'd like Full-Text RSS to find the language of the articles it processes, you can use one of the following values:</p>
			<dl>
				<dt>0</dt><dd>Ignore language</dd>
				<dt>1</dt><dd>Use article metadata (e.g. HTML lang attribute) or feed metadata. (Default value)</dd>
				<dt>2</dt><dd>As above, but guess the language if it's not specified.</dd>
				<dt>3</dt><dd>Always guess the language, whether it's specified or not.</dd>
			</dl>
			<p>If language detection is enabled and a match is found, the language code will be returned in the &lt;dc:language&gt; element inside the &lt;item&gt; element.</p>
			</td>
		</tr>				
		
		<tr>
			<td>debug</td>
			<td>[no value], <tt>rawhtml</tt>, <tt>parsedhtml</tt></td>
			<td><p>If this parameter is present, Full-Text RSS will output the steps it is taking behind the scenes to help you debug problems.</p>
			<p>If the parameter value is <tt>rawhtml</tt>, Full-Text RSS will output the HTTP response (headers and body) of the first response after redirects.</p> 
			<p>If the parameter value is <tt>parsedhtml</tt>, Full-Text RSS will output the reconstructed HTML (after its own parsing). This version is what the extraction rules are applied to, and it may differ from the original (<tt>rawhtml</tt>) output. If your extraction rules are not picking out any elements, this will likely help identify the problem.</p>
			<p>Note: Full-Text RSS will stop execution after HTML output if one of the last two parameter values are passed. Otherwise it will continue showing debug output until the end.</p></td>
		</tr>
		
		<tr>
			<td>parser</td>
			<td><tt>html5php</tt>, <tt>libxml</tt></td>
			<td>The default parser is libxml as it's the fastest. HTML5-PHP is an HTML5 parser implemented in PHP. It's slower than libxml, but can often produce better results. You can request HTML5-PHP be used as the parser in a site-specific config file (to ensure it gets used for all URLs for that site), or explicitly via this request parameter.</td>
		</tr>

		<tr>
			<td>siteconfig</td>
			<td>string</td>
			<td>Site-specific extraction rules are usually stored in text files in the site_config folder. You can also submit <a href="http://help.fivefilters.org/customer/portal/articles/223153-site-patterns">extraction rules</a> directly in your request using this parameter.</td>
		</tr>
		
		<tr>
			<td>proxy</td>
			<td><tt>0</tt>, <tt>1</tt>, string (proxy name)</td>
			<td>This parameter has no effect if proxy servers have not been entered in the config file. If they have been entered and enabled, you can pass the following values: 0 to disable proxy use (uses direct connection). 1 for default proxy behaviour (whatever is set in the config), or a string to identify a specific proxy server (has to match the name given to the proxy in the config file).</td>
		</tr>			
	
		</tbody>
	</table>

	<p><strong>Feed-only parameters</strong> &mdash; These parameters only apply to web feeds. They have no effect when the input URL points to a web page.</p>
	
	<table width="100%" border="0" class="parameters table table-bordered">
		<thead>
		<tr style="background-color: #ddd">
			<th width="13%">Parameter</th>
			<th width="19%">Value</th>
			<th width="68%">Description</th>
		</tr>
		</thead>
		<tbody>
		
		<tr>
			<td>use_extracted_title</td>
			<td>[no value]</td>
			<td>By default, if the input URL points to a feed, item titles in the generated feed will not be changed - we assume item titles in feeds are not truncated. If you'd like them to be replaced with titles Full-Text RSS extracts, use this parameter in the request (the value does not matter). To enable/disable this for for all feeds, see the config file - specifically <tt>$options->favour_feed_titles</tt></td>
		</tr>			

		<tr>
			<td>max</td>
			<td>number</td>
			<td>The maximum number of feed items to process. (The default and upper limit will be found in the configuration file.)</td>
		</tr>	
		
		</tbody>
	</table>

	<h3>Response (example)</h3>
	<p>JSON output produced for the BBC feed http://feeds.bbci.co.uk/news/rss.xml. You can also request regular RSS.</p>
	<!-- Generated by http://chris.photobooks.com/json/default.htm -->
	<output style="display: block;" for="jsonInput jsonStrict jsonEval json2HTML json2JSON jsonTrunc jsonDate jsonData jsonSpace" class="jsonOutput PRETTY"><span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;<span>"rss": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"version": <span title="String" class="STRING">"2.0"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"channel": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"BBC News - Home"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"link": <span title="String" class="STRING">"http://www.bbc.co.uk/news/#sa-ns_mchannel=rss&amp;amp;ns_source=PublicR…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"description": <span title="String" class="STRING">"The latest stories from the Home section of the BBC News web site."</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"ttl": <span title="Number" class="NUMBER">15</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"image": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"BBC News - Home"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"link": <span title="String" class="STRING">"http://www.bbc.co.uk/news/#sa-ns_mchannel=rss&amp;amp;ns_source=PublicR…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/nol/shared/img/bbc_news_120x60.gif"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"item": <span class="ARRAY">[<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"Russia's Putin visits annexed Crimea"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"link": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-europe-27344029#sa-ns_mchannel=rss&amp;…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"guid": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-europe-27344029#sa-ns_mchannel=rss&amp;…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"description": <span title="String" class="STRING">"President Putin: \"[Crimeans have] proved their loyalty to a histor…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"content_encoded": <span title="String" class="STRING">"&lt;!-- Adding hypertab --&gt;&amp;#13;\n&amp;#13;\n&amp;#13;\n&lt;!-- end of hypertab -…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"pubDate": <span title="String" class="STRING">"Fri, 09 May 2014 15:02:04 +0000"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_language": <span title="String" class="STRING">"en-gb"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_format": <span title="String" class="STRING">"text/html"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_identifier": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-europe-27344029"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"media_thumbnail": <span class="ARRAY">[<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74751000/jpg/_74751301_ycst2i…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74751000/jpg/_74751302_ycst2i…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"Harris 'assaulted daughter's friend'"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"link": <span title="String" class="STRING">"http://www.bbc.co.uk/news/uk-27340134#sa-ns_mchannel=rss&amp;ns_source=…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"guid": <span title="String" class="STRING">"http://www.bbc.co.uk/news/uk-27340134#sa-ns_mchannel=rss&amp;amp;ns_sou…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"description": <span title="String" class="STRING">"Rolf Harris arrives at court flanked by his wife and daughter Rolf …"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"content_encoded": <span title="String" class="STRING">"&lt;!--  Embedding the video player --&gt;&amp;#13;\n&lt;!--  This is the embedd…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"pubDate": <span title="String" class="STRING">"Fri, 09 May 2014 15:21:52 +0000"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_language": <span title="String" class="STRING">"en-gb"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_format": <span title="String" class="STRING">"text/html"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_identifier": <span title="String" class="STRING">"http://www.bbc.co.uk/news/uk-27340134"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"media_thumbnail": <span class="ARRAY">[<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74740000/jpg/_74740642_hi0221…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74740000/jpg/_74740643_hi0221…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"title": <span title="String" class="STRING">"Nigeria 'ignored' school warning"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"link": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-africa-27344863#sa-ns_mchannel=rss&amp;…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"guid": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-africa-27344863#sa-ns_mchannel=rss&amp;…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"description": <span title="String" class="STRING">"Nigeria's military had advance warning of the attack on a school at…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"content_encoded": <span title="String" class="STRING">"&lt;div class=\"caption full-width\"&gt;&amp;#13;\n  &lt;img src=\"http://news.b…"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"pubDate": <span title="String" class="STRING">"Fri, 09 May 2014 15:48:34 +0000"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_language": <span title="String" class="STRING">"en-gb"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_format": <span title="String" class="STRING">"text/html"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"dc_identifier": <span title="String" class="STRING">"http://www.bbc.co.uk/news/world-africa-27344863"</span></span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"media_thumbnail": <span class="ARRAY">[<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74749000/jpg/_74749855_747495…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span>,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"@attributes": <span class="OBJ">{<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>"url": <span title="String" class="STRING">"http://news.bbcimg.co.uk/media/images/74749000/jpg/_74749856_747495…"</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>&nbsp;&nbsp;&nbsp;&nbsp;}</span></span><br>}</span></output>	
	<p>Note: For brevity the output above is truncated.</p>

	<hr />
	<h3 id="api-keys">API Keys</h3>
	<p>To restrict access to your copy of Full-Text RSS, you can specify API keys in the config file.</p>
	<p>Note: Full-text feeds produced by Full-Text RSS are intended to be publically accessible to work with feed readers. As such, the API key should not appear in the final URL for feeds.</p>
	
	<table width="100%" border="0" class="parameters table table-bordered">
		<thead>
		<tr style="background-color: #ddd">
			<th width="13%">Parameter</th>
			<th width="19%">Value</th>
			<th width="68%">Description</th>
		</tr>
		</thead>
		<tbody>
			<tr>
			<td>key</td>
			<td>string or number</td>
			<td><p>This parameter has two functions.</p><p>If you're calling Full-Text RSS programattically, it's better to use this parameter to provide the API key index number together with the hash parameter (see below) so that the actual API key does not get sent in the HTTP request.</p><p>If you pass the actual API key in this parameter, the hash parameter is not required. If you pass the actual API key, Full-Text RSS will find the index number and generate the hash value automatically and redirect to a new URL to hide the API key. If you'd like to link to a generated feed publically while protecting your API key, make sure you copy and paste the URL that results after the redirect.</p><p>If you've configured Full-Text RSS to require a key, an invalid key will result in an error message.</p></td>
			</tr>
	    
			<tr>
			<td>hash</td>
			<td>string</td>
			<td>A SHA-1 hash value of the API key (actual key, not index number) and the URL supplied in the <tt>url</tt> parameter, concatenated. This parameter must be passed along with the API key's index number using the <tt>key</tt> parameter (see above). In PHP, for example: <tt>$hash = sha1($api_key.$url);</tt></td>
			</tr>

			<tr>
			<td>key_redirect</td>
			<td>0 or 1 (default)</td>
			<td><p>When supplying the API key with the <tt>key</tt> parameter, Full-Text RSS will generate a new URL and issue a HTTP redirect to the new URL to hide the API key (see description above). If you'd like to avoid an HTTP redirect, you can pass 0 in this parameter. We do not recommend you subscribe to feeds generated in this way.</p></td>
			</tr>
		</tbody>
	</table>

	
	
	</div>

	<!-- GENERAL TAB -->
	
	<div id="general" class="tab-pane">
	
	<h3>About</h3>
	<p>This is a free software project to enable article extraction from web pages. It can extract content from a standard HTML page and return a 1-item feed or it can transform an existing feed into a full-text feed. It is being developed as part of the <a href="http://fivefilters.org">Five Filters</a> project to promote independent, non-corporate media.</p>

	<h3>Bookmarklet</h3>
	<p>Rather than copying and pasting URLs into this form, you can add the bookmarklet on this page to your browser. Simply drag the link below to your browser's bookmarks toolbar.
	Then whenever you'd like a full-text feed, click the bookmarklet.</p>
	<p>Drag this: 
	<script type="text/javascript">
	document.write('<a class="btn info" style="cursor: move;" onclick="alert(\'drag to bookmarks toolbar\'); return false;" href="javascript:location.href=\''+baseUrl+'/makefulltextfeed.php?url=\'+encodeURIComponent(document.location.href);">Full-Text RSS</a>');
	</script>
	<p>Note: This uses the default options and does not include your access key (if configured).</p>	
	
	<h3>Free Software</h3>
	<p>Note: 'Free' as in 'free speech' (see the <a href="https://www.gnu.org/philosophy/free-sw.html">free software definition</a>)</p>
	
	<p>If you're the owner of this site and you plan to offer this service to others through your hosted copy, please keep a download link so users can grab a copy of the code if they 
	want it (you can either offer a free download yourself, or link to the purchase option on fivefilters.org to support us).</p>
	
	<p>For full details, please refer to the <a href="http://www.gnu.org/licenses/agpl-3.0.html" title="AGPLv3">license</a>.</p>
	
	<p>If you're not the owner of this site (ie. you're not hosting this yourself), you do not have to rely on an external service if you don't want to. You can <a href="http://fivefilters.org/content-only/#download">download your own copy</a> of Full-Text RSS under the AGPL license.</p>
	
	<h3>Software Components</h3>
	<p>Full-Text RSS is written in PHP and relies on the following <strong>primary</strong> components:</p>
	<ul>
		<li><a href="http://www.keyvan.net/2010/08/php-readability/">PHP Readability</a></li>
		<li><a href="http://simplepie.org/">SimplePie</a></li>
		<li>FeedWriter</li>
		<li>Humble HTTP Agent</li>
	</ul>
	<p>Depending on your configuration, these <strong>secondary</strong> components may also be used:</p> 
	<ul>
		<li><a href="https://github.com/Masterminds/html5-php">HTML5-PHP</a></li>
		<li><a href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/">htmLawed</a></li>		
		<li><a href="http://code.google.com/p/rolling-curl/">Rolling Curl</a></li>
		<li><a href="http://framework.zend.com/manual/en/zend.cache.introduction.html">Zend Cache</a></li>
		<li><a href="http://pear.php.net/package/Text_LanguageDetect">Text_LanguageDetect</a> or <a href="https://github.com/lstrojny/php-cld">PHP-CLD</a> if available</li>
	</ul>

	<h3>System Requirements</h3>
	
	<p>PHP 5.2 or above is required. A simple shared web hosting account will work fine.
	The code has been tested on Windows and Linux using the Apache web server. If you're a Windows user, you can try it on your own machine using <a href="http://www.wampserver.com/en/index.php">WampServer</a>. It has also been reported as working under IIS, but we have not tested this ourselves.</p>
	
	<h3 id="download">Download</h3>
	<p>Download from <a href="http://fivefilters.org/content-only/#download">fivefilters.org</a> &mdash; old versions are available in our <a href="http://code.fivefilters.org">code repository</a>.</p>

	</div>
	
	<!-- UPDATES TAB -->
	<div id="updates" class="tab-pane">
	<?php 
	$site_config_version_file = dirname(__FILE__).'/site_config/standard/version.txt';
	if (file_exists($site_config_version_file)) {
		$site_config_version = file_get_contents($site_config_version_file);
	}
	?>
	<p>Your version of Full-Text RSS: <strong><?php echo _FF_FTR_VERSION; ?></strong><br />
	Your version of Site Patterns: <strong><?php echo (isset($site_config_version) ? $site_config_version : 'Unknown'); ?></strong>
	</p>
	<p>To see if you have the latest versions, <a href="http://fivefilters.org/content-only/latest_version.php?version=<?php echo urlencode(_FF_FTR_VERSION).'&site_config='.urlencode(@$site_config_version); ?>">check for updates</a>.</p>
	<p>If you've purchased this from FiveFilters.org, you'll receive notification when we release a new version or update the site patterns.</p>
	</div>	
	
	<!-- LICENSE TAB -->
	<div id="license" class="tab-pane">
	<p><a href="http://en.wikipedia.org/wiki/Affero_General_Public_License" style="border-bottom: none;"><img src="images/agplv3.png" alt="AGPL logo" /></a></p>
	<p>Full-Text RSS is licensed under the <a href="http://en.wikipedia.org/wiki/Affero_General_Public_License">AGPL version 3</a> &mdash; more information about why we use this license can be found on <a href="http://fivefilters.org/content-only/http://fivefilters.org/content-only/#license">FiveFilters.org</a></p> 
	<p>The software components in this application are licensed as follows...</p>
	<ul>
		<li>PHP Readability: <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License v2</a></li>
		<li>SimplePie: <a href="http://en.wikipedia.org/wiki/BSD_license">BSD</a></li>
		<li>FeedWriter: <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html">GPL v2</a></li>
		<li>Humble HTTP Agent: <a href="http://en.wikipedia.org/wiki/Affero_General_Public_License">AGPL v3</a></li>
		<li>Zend: <a href="http://framework.zend.com/license/new-bsd">New BSD</a></li>
		<li>Rolling Curl: <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License v2</a></li>
		<li>HTML5-PHP: <a href="http://opensource.org/licenses/mit-license.php">MIT</a></li>
		<li>htmLawed: <a href="http://en.wikipedia.org/wiki/GNU_Lesser_General_Public_License">LGPL v3</a></li>
		<li>Text_LanguageDetect: <a href="http://en.wikipedia.org/wiki/BSD_license">BSD</a></li>		
	</ul>
	</div>
	
	</div>
  </body>
</html>