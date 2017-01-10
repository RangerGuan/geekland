=== WP-AutoPost ===
Contributors: wp-autopost.org
Tags: posts, auto
Requires at least: 2.8.5
Tested up to: 4.0
Stable tag: 3.6.2

WP-AutoPost Plugin can automatically post content from any other source

== Description ==

WP-AutoPost Plugin can automatically post content from any other source

== Installation ==

1.Unzip ?ZIP?file and a directory will be created.
2.Upload the unzipped directories to your site¡¯s ¡®wp-contents/plugins¡¯ directory.
3.Log in to your site, go to Plugins and enable it.

== Changelog ==
*Version 3.6.1
       * Fix some bugs and optimization

*Version 3.6
       * Add a feature: If no images in the post can use default featured image
       * Add a feature: Downloaded images size optimization, when the original image's width exceed the setting PX, will resize this image
       * Add a feature: Can set all downloaded images generate thumbnail
       * Add a feature: Can set enable Cookie, some few sites need to open this feature can normal extracted contents
       * Optimization: Title/Content keyword replacement can support use wildcards, the symbol (*) is wildcards
       * Optimization: Content keyword replacement can support use variables
       * Optimization: Content keyword replacement can set "Not Replace Tag and Attribute Contents"
       * Fix some bugs and optimization

*Version 3.5
       * Add a feature: Add a new translate function - Baidu Translator
       * Fix some bugs and optimization    
       
*Version 3.4
       * Add a feature: Support Automatic Set that will more easy to use
       * Add a feature: Support can set Specify Publish Date
       * Optimization: Optimizing the feature of "Insert Content In Anywhere"
       * Fix some bugs and optimization

*Version 3.3
       * Add a feature: Support use RSS Feed
       * Add a feature: Support set rule extraction contents to Wordpress Taxonomy
       * Optimization: The downloaded images can organize uploads into day- and month- and year-based folders
       * Fix some bugs and optimization

*Version 3.2
       * Optimization: Optimizing flickr function, support the newest flickr API
       * Optimization: Optimizing the Use URL wildcards match pattern
       * Optimization: Optimizing extract the post date
       * Optimization: Optimizing the download remote attachment
       * Optimization: Optimizing the download remote images
       * Fix a fatal bug

*Version 3.1
       * Add a feature: Can set Extraction Pause Time, reduce CPU resource consumption.
       * Add a feature: Support keyword replacement in custom fields
       * Optimization: Optimizing the use of variables
       * Fix some bugs
         
*Version 3.0.1
       * Fix some bugs

*Version 3.0
       * Optimization most core function, more powerful and more flexible. 
       * Add a feature: Support extraction Categories and auto create the Categories. 
       * Add a feature: Support different tasks can use different watermarks.  
              
*Version 2.9.7
       * Add a feature: Support extraction content base on keyword.
       * Optimization: Optimizing the feature of Microsoft Translator.
       * Fix some bugs and optimization.       

*Version 2.9.6
       * Add a feature: Support set Cookie, can extracted content that need to login.
       * Add a feature: Support copy task settings. 
       * Add a feature: Support use random author. 
       * Optimization: Can set more flexible scheduled publish date.
       * Optimization: Optimizing the feature of downloaded images, compatible with different image formats. 
       * Fix some bugs and optimization. 

*Version 2.9.5
       * Add a feature: Support remove any HTML attribute.
       * Add a feature: Filter content can use CSS selector and Index.  
       * Fix some bugs and optimization.  

*Version 2.9.4
       * Fix some bugs and optimization.        

*Version 2.9.3
       * Add a feature: Support use variables like {post_id}, {post_title}, {post_permalink}, {custom_field_name}, [html_attribute_name], make a lot easier to use and flexible.
       * Add a feature: Support automatic detection charset.        

*Version 2.9.2
       * Add a feature: Support custom the style of extracted article.
       * Optimization: Optimization the function of download remote image, support extracted and downloaded vast images.

*Version 2.9.1
       * Add a feature: Can set some rules to extract source post's image to be your post's featured image.
       * Add a feature: Support rewrite (spinning) by use Spin Rewriter, get unique and readable article.
       * Fix some bugs and optimization.

*Version 2.9.0
       * Add a feature: Support rewrite (spinning) by use WordAi, get unique and readable article.
       * Add a feature: Support rewrite (spinning) by use Microsoft Translator, get unique article.
       * Add a feature: Support Post Scheduled.
       * Optimization: Optimize the function of translate.
       * Optimization: Optimize the extraction of redirecting URL.    
  
*Version 2.8.3
       * Add a feature: Support automatically upload the downloaded images to Upyun, save bandwidth and space, speed up your website.
       * Fix some bugs and optimization.

*Version 2.8.2
       * Optimization: Optimization the memory usage when process of extraction, significantly reduce the memory usage.
       * Optimization: Can be set to run only one task at the same time, other tasks queued, significantly reduce the impact on server performance.
       * Optimization: Optimization the function of download remote image, can set featured image without downloaded all images. 
       * Add a feature: Can set some keywords, when post's title contains these keywords then extract posts£¨or filter out posts). 
       * Add a feature: Can set some rules to extract source post's tags, to be your post's tags.
       * Fix some bugs and optimization.
       
*Version 2.8.1
       * Add a feature: Support  "Wordpress Custom Fields" on each task.
       * Add a feature: When active Auto Tags, can use Wordpress Tags Library.
       * Add a feature: Post management functions can display the not exist posts that deleted by wordpress.
       * Add a feature: Can use the URL and Title detect duplicates post.
       * Fix a bug: When use translation functions will not translate the excerpt, has been fixed.     

*Version 2.8
        * Add a feature: Support  "Wordpress Post Type",  "Custom Post Type" and "Custom Taxonomy"
        * Add a feature: Support  "Wordpress Post Format"
        * Optimization: Optimization the download remote image and attachments function, more stable
        * Fix a bug: When use automatic update posts, sometimes will filter out <iframe> <embed> tags that may affect the video display, has been fixed   

* Version 2.7.2
        * Fix a bug: When use auto update can not set featured images, has been fixed          

* Version 2.7.1
        * Optimization: Optimization the <Featured Image> function, support Index and fix thumbnails problem
        * Optimization: Optimization the <Auto Excerpt> function, support Index
        * Optimization: Optimization the <Download Remote Attachments> function, more stable
        * Optimization: When uploaded images to Flickr, can save a copy on local server  

* Version 2.7
        * Add a feature: Support use Proxy
        * Add a feature: Support hide IP address
        * Add a feature:  When use CSS selector to extract content support use Index, will be more accurate
        * Optimization: Enhancing the use of  "Wildcards Match Pattern" to extract content
        * Fix some bugs and optimization. 

* Version 2.6.3
        * Optimization: Can downloaded anti-hotlinking images.
        * Optimization: If a link use relative URL will converted into absolute URL.

* Version 2.6.2
        * Optimization: Optimization tasks processes, preventing tasks unresponsive.
        * Optimization: Optimization download remote images, can set "The attribute of image URL".
        * Optimization: When upload images to Qiniu, optimization the URL path. 
        * Optimization: When first use, can create Example Task, as a reference can quickly master use of this plugin.

* Version 2.6.1
        * Optimization: Optimize the feature about "Auto Excerpt".
        * Fix a bug: When fetch Paginated Content, some special cases, can not extraction, has been fixed.         

* Version 2.6
        * Add a feature: Can use Microsoft Translator automatically translated posts to other languages, support for 41 languages.
        * Add a feature: Can fetch any content to "Post Excerpt".
        * Optimization: Optimize the feature about "Insert Content In Anywhere".

* Version 2.5
        * Add a feature: Support automatically upload the downloaded images to Qiniu (10GB Free Storage), save bandwidth and space, speed up your website.
        * Add a feature: Automatically add ALT attributes on <img> tags, good for SEO.
        * Add a feature: Can check for duplicate posts.
        * Optimization: Optimize the test feature.

* Version 2.4
        * Add a feature: Support automatically upload the downloaded images to Flickr (1TB Free Storage), save bandwidth and space, speed up your website.
        * Optimization: When delete the posts also delete the downloaded images.
        * Fix some little bug and optimization. 

* Version 2.3
        * Optimization: Support wordpress MU(Multisite) version
        * Optimization: Optimize some operating experience

* Version 2.2
        * Add a feature: "Manually Selective Extraction" that can manually select which article can be extraction and posted in your site
        * Add a feature: Can set post status: Published, Draft and Pending Review
        * Optimization: When fetch Paginated Content improved content filtering algorithm, more accurate and stable
        * Fix a bug: When fetch Paginated Content, some special cases, an error will occur, has been fixed  

* Version 2.1
        * Add a feature: Can set download remote attachment files to local server, like .zip or any other type of files
        * Add a feature: Can fetch any content to "Wordpress Custom Fields"
        * Add a feature: Can the first paragraph as an excerpt automatically
        * Add a feature: Can set automatically remove the empty HTML element (Remove empty html element, like <p> </p>)
       * Optimization: Improved HTML tag filtering algorithm, more accurate and stable

* Version 2.0
        * Add a feature: Can set add a watermark to downloaded images automatically
        * Add a feature: Can set the first downloaded image as featured image automatically
        * Add a feature: Can use "Wildcards Match Pattern" for "Article Pagination URLs"
        * Add a feature: Can set fetch some source date content as the post date
        * Add a feature: Can set add the source URL to wordpress custom fields
        * Optimization: Simulations using the browser to crawl URLs, preventing blocked
        * Fix a bug: When fetch Paginated Content can't recognize relative URLs, has been fixed
        * Fix a bug: When fetch Paginated Content some cases may result in duplication, has been fixed
        * Fix a bug: When download remote image sometimes can't recognize relative URLs, has been fixed
        * Fix a bug: When use "Insert Content In Anywhere" feature some cases may be invalid, has been fixed 
        
* Version 1.9
        * Optimization: Modify the algorithms of crawl pages, compatible with more servers, and crawl speed and stability has improved
        * Optimization: Can use CSS Selector match all elements 
	                For example:
			If set (.content img), can fetch all the matching images
			If set (.content p), can fetch all the matching paragraphs  
        * Fix a bug: If <img> tags use relative URL will not show the images, has been fixed

* Version 1.8
        * Add a feature: Can set Multi "The Article Content Matching Rules", so can fetch multi content on the same web page (the content on the different areas)
        * Add a feature: When publishing articles, can insert additional content in anywhere 
        * Optimization: In "The Article Content Matching Rules" can set "Contain The Outer HTML Text" 

* Version 1.7
        * Add a feature: Filtering content by use CSS Selector, can delete the content selected by CSS Selector
        * Add a feature: Can set automatically remove the HTML comments (Remove html element like <!-- *** -->)
        * Add a feature: Can set automatically remove the HTML ID attribute (Remove html element like id=" *** ")
        * Add a feature: Can set automatically remove the HTML CLASS attribute (Remove html element like class=" *** ")
        * Add a feature: Can set automatically remove the HTML STYLE attribute (Remove html element like style=" *** ")


* Version 1.6
        * Add a feature: auto link, can automatically add links on keywords when publish post. 
	                  Can also add keyword links all existing contents of your blog.

* Version 1.5
        * Add a feature:Use Wildcards Match Pattern for Article Title
        * Add a feature:Use Wildcards Match Pattern for Article Content
        * Add a feature:"Reverse the sort of articles" in Article Source Settings tabs
        * Optimization "Updated Post Menu" function
        * Optimization remote image download code
	
* Version 1.4
        * Optimization the task processing
        * Optimization randomzed post time

* Version 1.3
        * Fix a bug:Relative URL Crawl
        * Optimizing Database Structure

* Version 1.2
        * Add a feature:Use URL wildcards match pattern

* Version 1.1
	* Initial version