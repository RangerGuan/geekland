=== CyberSyn ===
Contributors: CyberSEO
Author: CyberSEO
Author URI: http://www.cyberseo.net/
Donate link: http://www.cyberseo.net/
Plugin URI: http://www.cyberseo.net/cybersyn/
Tags: autoblog, autoblogging, content curation, parser, rss, feed, rss feeds to post, wordpress autoblog aggregator, syndication, multiple feed import, full text rss, translator, yandex translate
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 4.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A powerful, lightweight and easy to use Atom/RSS aggregation and content curation plugin for WordPress with Full Text RSS import feature.

== Description ==

The CyberSyn plugin (developed by [CyberSEO.net](http://www.cyberseo.net/ "CyberSEO.net")) is powerful, lightweight and easy to use Atom/RSS aggregation and content curation plugin for WordPress. The CyberSyn plugin has one very unique feature which allows you to import full text articles.

Features:

* Automatically generates WordPress posts from [Atom](http://en.wikipedia.org/wiki/Atom_%28standard%29 "Atom")/[RSS](http://en.wikipedia.org/wiki/RSS "Really Simple Syndication") feeds and runs your site on autopilot.
* Embeds videos from YouTube, Vimeo, Flickr, IGN, Ustream.tv and DailyMotion RSS feeds. Just enable the "Embed videos" option and the plugin will embed the videos with descriptions directly into your posts.
* Generates featured images (post thumbnails).
* Full Text RSS extractor is 100% free and allows one to import full-text articles. This feature is based on freeware GPL library by fivefilters.org. It is recommended to use the Full Text RSS extractor with the "link to source" option enabled to give credit to the original content owner. This built-in feature works right out of the box. No 3rd-party services or paid API keys!
* Allows one to hotlink the syndicated post images or upload them to your server.
* Allows one to embed media attachments (images and video) into posts.
* Translates the syndicated articles from and to more than 100 languages via free Yandex Translate and premium Google Translate services.
* Advanced RSS/Atom parsing algorithm has the ability to pull the feeds fully automatically. Furthermore, you can assign the updating period to each particular feed. Also you can set up a maximum number of posts that will be syndicated at once. This is a very useful feature for SEO of your blogs.
* Adjustable post duplicate check by GUID, post title or both.
* Allows one to add a user-defined HTML code (custom footers) to every syndicated post.
* Converts character encoding if the source feed has a non UTF-8 one. E.g. KOI8-R, KOI8-U, Windows-1251, Windows-1256, Windows-1255 etc. Thus the plugin is compatible with all existing national character encoding systems.
* The plugin can be scheduled by a server-side cron and by WordPress pseudo-cron.

Requirements:

* PHP 5.2.4 or greater
* MySQL 5.0 or greater
* PHP mbstring extension
* PHP cURL extension (recommended)
* PHP variable safe_mode must be disabled (if cURL is not installed)
* PHP variable allow_url_fopen must be enabled (if cURL is not installed)
* Access to cron on server (recommended)

Missing some features? Please make sure to check out the following similar plugins: WP-o-Matic, FeedWordPress, RSS Post Importer / FeedsAPI, RSSImport, Syndicate Press, FeedWeb, RSS Just Better, CSV 2 Post, Simple Feed Copyright, WP Syndicate, HungryFEED, Feedzy, WP Pipes.

== Installation ==

1. Upload 'cybersyn' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where can I get support? =

The support forums can be found at http://www.cyberseo.net/forum/.

== Screenshots ==

1. Syndicating a new RSS feed.

== Changelog ==

= 5.24 =
* Fixed full text RSS feed.

= 5.23 =
* Bug fix for https://wordpress.org/support/topic/problem-with-5-22/.

= 5.22 =
* Google Translate added.

= 5.21 =
* Better media attachment handling.
* Fully compatible with WordPress 4.7.

= 5.20 =
* Fixed issue with permalinks when the "Link to source" option is enabled.

= 5.19 =
* Yandex Translate added.

= 5.18 =
* Fixed issue with media attachments in shortened excerpts.

= 5.17 =
* "Shorten post excerpts" option added.

= 5.16 =
* "[Disable post footers in excerpt](https://wordpress.org/support/topic/cybersyn-disable-post-footer-in-excerpt/)" bug fixed.

= 5.15 =
* Better compatibility with PHP 7.

= 5.14 =
* Added support for Ustream.tv and DailyMotion video feeds.

= 5.13 =
* Improved Flickr and IGN video feed support.

= 5.12 =
* Added support for Vimeo, Flickr and IGN video feeds.

= 5.11 =
* RSS media attachments of the following types are suppoted now: mp4, m4v, webm, ogv, wmv, flv.
* Media attachment thumbnails can be used as featured images.
* Improved compatibility with CyberSEO 7.

= 5.10 =
* Featured image (post thumbnail) generation has been implemented.
* The plugin is now compatibe with CyberSEO 7.

= 5.01 =
* The Full Text RSS engine has been upgraded to version 3.4.1. Backporting Dave Vasilevsky cookie patch. Fixes issues with certain sites. See https://gist.github.com/fivefilters/0a758b6d64ce4fb5728c
* Fully compatible with WordPress 4.6.1

= 5.00 =
* Free Full Text RSS feed extractor feature added.
* Fully compatible with WordPress 4.5.2

= 4.02 =
* Fixed the ["cyberseo_post_link custom field not good for first post"](https://wordpress.org/support/topic/cyberseo_post_link-custom-field-not-good-for-first-post "cyberseo_post_link custom field not good for first post") bug. Thanks to marcetin.

= 4.01 =
* Fixed issue with adding of new feeds.

= 4.00 =
* Fixed CSRF vulnerability.
* The content spinning feature has been removed by request of WordPress.org.

= 3.29 =
* Fully compatible with WordPress 4.2.2
* Video import has been adjusted according to the recent youtube API changes

= 3.28 =
* Fully compatible with WordPress 4.1.1

= 3.27 =
* Fully compatible with WordPress 4.1

= 3.26 =
* Fully compatible with WordPress 4.0.1

= 3.25 =
* Fixed issue with WordAi spinner.

= 3.24 =
* Fully compatible with WordPress 4.0.

= 3.23 =
* WordAi support improved.

= 3.21 =
* Fixed bug that caused "Fatal error: Call to undefined function".
* Fully compatible with WordPress 3.8.1

= 3.20 =
* The plugin is now integrated with WordAi spinner that uses artificial intelligence to understand text and is able to automatically rewrite the syndicated articles with the same readability as a human writer.

= 3.13 =
* Fully compatible with WordPress 3.6.

= 3.12 =
* The default user agent header has been removed from HTTP requests. It was causing problems with FeedBurner feeds.

= 3.11 =
* The Best Spinner integration has been improved.

= 3.10 =
* Added possibility to automatically embed videos from standard YouTube RSS feeds.

= 3.02 =
* Fixed bug which forced the plugin to use "The Best Spinner" even if disabled.

= 3.01 =
* Minor changes.

= 3.00 =
* The plugin is now integrated with TBS (The Best Spinner) - the most popular content spinning service.
* The post images now can be stored locally (copied to your own host).
* The syndicated posts now can be attributed to the chosen author.
* Now one can specify a list of tags for for the each feed.
* The media attachment handling has been sufficiently improved.
* The character encoding conversion has been added.
* Now one can specify the HTML code with will be inserted to the bottom of each syndicated post (so-called post footers).

= 2.11 =
* The "[loss of permalink](http://www.cyberseo.net/forum/support-eng/loss-of-permalink/)" issue fixed.

= 2.10 =
* The "Link syndicated posts to the original source" option has been added.
* The RSS auto pull mode now uses the built-in WP pseudo cron.
* Minor bugs fixed.

= 2.1 =
* All known bugs were fixed.

= 2.0 =
* The CyberSyn plugin is now 100% compatible with WordPress 3.3.
* The UI has been improved.
* Default settings have been removed from the "XML Syndicator" page. Use the "Alter default settings" button instead.

= 1.4 =
* The feed import function has been improved. Now the status of 'safe_mode' and 'allow_url_fopen' PHP variables is not important in case if the PHP cURL extension is installed.
* The user interface has been slightly improved.

= 1.3 =
* "RSS Pull Mode" option switching issue has been fixed.

= 1.2 =
* First public GPL release.

== Upgrade Notice ==

Upgrade using the automatic upgrade in Wordpress Admin.
