<?php
/*
Plugin Name: My-AutoPost Pro
Plugin URI: http://www.ithtw.com
Description: My-AutoPost Plugin can automatically post content from any other site. It is very simple to use, without complicated setting, and powerful enough.
Version: 3.2
Author: My-AutoPost
Author URI: http://www.ithtw.com
*/

define('WPAPPRO_PATH',WP_PLUGIN_DIR.'/wp-autopost-pro');

remove_action('init', 'kses_init');
remove_action('set_current_user', 'kses_init');

load_plugin_textdomain('wp-autopost', WP_PLUGIN_URL.'/wp-autopost-pro/languages/', 'wp-autopost-pro/languages/');
if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}
add_action('admin_menu', 'wp_autopost_pro_menu');
function wp_autopost_pro_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page('Auto Post Pro','Auto Post Pro', 'administrator', 'wp-autopost-pro/wp-autopost-tasklist.php', '',WP_PLUGIN_URL.'/wp-autopost-pro/images/menu_icon.png');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Posts'), __('Posts'),  'administrator', 'wp-autopost-pro/wp-autopost-updatedpost.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Auto Link','wp-autopost'), __('Auto Link','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-link.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Options','wp-autopost'), __('Options','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-options.php');
        add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Watermark Options','wp-autopost'), __('Watermark Options','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-watermark.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Microsoft Translator','wp-autopost'), __('Microsoft Translator','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-translator.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Baidu Translator','wp-autopost'), __('Baidu Translator','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-translator-baidu.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Proxy','wp-autopost'), __('Proxy','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-proxy.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Flickr','wp-autopost'), __('Flickr','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-flickr.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Qiniu','wp-autopost'), __('Qiniu','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-qiniu.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Upyun','wp-autopost'), __('Upyun','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-upyun.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Logs','wp-autopost'), __('Logs','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-logs.php');
		add_submenu_page('wp-autopost-pro/wp-autopost-tasklist.php', __('Documentation','wp-autopost'), __('Documentation','wp-autopost'),  'administrator', 'wp-autopost-pro/wp-autopost-documentation.php');
	
	}
}

global  $wp_autopost_root,$table_prefix,$t_ap_config,$t_ap_config_option,$t_ap_config_url_list,$t_ap_updated_record,$t_ap_log,$t_autolink,$t_ap_more_content,$t_ap_flickr_img,$t_ap_flickr_oauth,$t_ap_qiniu_img,$t_ap_upyun_img,$t_ap_download_img_temp,$t_ap_watermark;
$wp_autopost_root = WP_PLUGIN_URL."/wp-autopost-pro/";
$t_ap_config = $table_prefix.'ap_config';
$t_ap_config_option = $table_prefix.'ap_config_option';
$t_ap_config_url_list = $table_prefix.'ap_config_url_list';
$t_ap_updated_record = $table_prefix.'ap_updated_record';
$t_ap_log = $table_prefix.'ap_log';
$t_autolink = $table_prefix.'autolink';
$t_ap_more_content = $table_prefix.'ap_more_content';
$t_ap_flickr_img = $table_prefix.'ap_flickr_img';
$t_ap_qiniu_img = $table_prefix.'ap_qiniu_img';
$t_ap_upyun_img = $table_prefix.'ap_upyun_img';
$t_ap_flickr_oauth = $table_prefix.'ap_flickr_oauth';
$t_ap_download_img_temp = $table_prefix.'ap_download_img_temp';
$t_ap_watermark = $table_prefix.'ap_watermark';

function wp_autopost_pro_install () {
  add_option('wp_autopost_updateMethod', '0');
  add_option('wp_autopost_timeLimit', '0');
  add_option('wp_autopost_pauseTime', '0');
  add_option('wp_autopost_runOnlyOneTask', '1');
  add_option('wp_autopost_runOnlyOneTaskIsRunning', '0');
  add_option('wp_autopost_downImgMinWidth', '100');
  add_option('wp_autopost_downImgTimeOut', '120');
  add_option('wp_autopost_downImgMaxWidth', '800');
  add_option('wp_autopost_downImgQuality', '90');
  add_option('wp_autopost_downImgRelativeURL', '0');
  add_option('wp_autopost_downImgFailsNotPost', '0');
  add_option('wp_autopost_downImgThumbnail', '0');
  add_option('wp_autopost_downFileOrganize', '0');
  add_option('wp_autopost_delComment', '1');
  add_option('wp_autopost_delAttrId', '1');
  add_option('wp_autopost_delAttrClass', '1');
  add_option('wp_autopost_delAttrStyle', '0');
  //add_option('wp_autopost_delEmptyTag', '0');

  global $wpdb; $wp_autopost_db_version = '3.6.1';
  global $t_ap_config,$t_ap_config_option,$t_ap_config_url_list,$t_ap_updated_record,$t_ap_log,$t_autolink,$t_ap_more_content,$t_ap_flickr_img,$t_ap_flickr_oauth,$t_ap_qiniu_img,$t_ap_upyun_img,$t_ap_download_img_temp,$t_ap_watermark;
  $old_db_version = get_option('wp_autopost_db_version');
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  
  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_config'") != $t_ap_config)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_config . " (
    id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	m_extract TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	activation TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	name CHAR(200) NOT NULL ,
	page_charset CHAR(30) NOT NULL DEFAULT '0' ,
	content_test_url CHAR(255) NULL DEFAULT NULL ,
	a_match_type VARCHAR(300) NOT NULL DEFAULT '0',
	title_match_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
    content_match_type VARCHAR(300) NOT NULL DEFAULT '0',
	page_match_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	auto_set VARCHAR(500) NULL DEFAULT NULL ,
	a_selector VARCHAR(2000) NULL DEFAULT NULL ,
	title_selector VARCHAR(3000) NULL DEFAULT NULL ,
	content_selector VARCHAR(3000) NULL DEFAULT NULL ,
	page_selector VARCHAR(400) NULL DEFAULT NULL ,
	fecth_paged TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	same_paged TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	source_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	start_num SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	end_num SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    title_prefix VARCHAR(1000) NULL DEFAULT NULL ,
	title_suffix VARCHAR(1000) NULL DEFAULT NULL ,
	content_prefix TEXT NULL DEFAULT NULL ,
	content_suffix TEXT NULL DEFAULT NULL ,
	updated_num MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	cat VARCHAR(50) NULL DEFAULT NULL ,
	author SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
	update_interval SMALLINT(5) UNSIGNED NOT NULL DEFAULT '60',
	published_interval SMALLINT(5) UNSIGNED NOT NULL DEFAULT '60',
	post_scheduled VARCHAR(20)  NULL DEFAULT NULL ,
	post_scheduled_last_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	download_img CHAR(10) NULL DEFAULT NULL ,
	img_insert_attachment CHAR(40) NULL DEFAULT NULL ,
	auto_tags CHAR(10) NULL DEFAULT NULL ,
	whole_word TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	tags VARCHAR(500) NULL DEFAULT NULL ,
	use_trans VARCHAR(1000)  NULL DEFAULT NULL ,
	use_rewrite VARCHAR(1000) NULL DEFAULT '0' ,
	last_update_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	last_check_fetch_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	post_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
	last_error INT(10) UNSIGNED NOT NULL DEFAULT '0',
	is_running TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	reverse_sort TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	add_source_url VARCHAR(50) NULL DEFAULT NULL ,
	proxy CHAR(10) NULL DEFAULT NULL ,
	post_type VARCHAR(50) NULL DEFAULT 'post' ,
	post_format VARCHAR(20) NULL DEFAULT NULL ,
	check_duplicate TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	custom_field VARCHAR(2000) NULL DEFAULT NULL ,
	err_status TINYINT(3) NOT NULL DEFAULT '1',
	cookie VARCHAR(4000) NULL DEFAULT NULL ,
	zh_conversion CHAR(10) NULL DEFAULT NULL ,
	publish_date VARCHAR(500) NULL DEFAULT NULL ,
	default_image VARCHAR(100)  NULL DEFAULT NULL ,
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_config_option'") != $t_ap_config_option)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_config_option . " (
    id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	config_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	option_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	para1 CHAR(255) NOT NULL,
	para2 CHAR(255) NULL DEFAULT NULL,
	options CHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (id),
	INDEX config_id (config_id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_config_url_list'") != $t_ap_config_url_list)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_config_url_list . " (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	config_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	url VARCHAR(1000) NOT NULL,
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_updated_record'") != $t_ap_updated_record)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_updated_record . " (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	config_id SMALLINT(5) UNSIGNED NOT NULL,
	url VARCHAR(1000) NOT NULL ,
	title VARCHAR(255) NULL DEFAULT NULL ,
	post_id INT(10) UNSIGNED NOT NULL,
	date_time INT(10) UNSIGNED NOT NULL,
    url_status TINYINT(3) NOT NULL DEFAULT '1',
	PRIMARY KEY (id),
	INDEX url (url),
	INDEX title (title)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_log'") != $t_ap_log)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_log . " (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	config_id INT(10) UNSIGNED NULL DEFAULT NULL,
	date_time INT(10) UNSIGNED NULL DEFAULT NULL,
	info VARCHAR(2000) NULL DEFAULT NULL,
	url VARCHAR(1000) NULL DEFAULT NULL,
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_autolink'") != $t_autolink)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_autolink . " (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	keyword VARCHAR(50) NOT NULL ,  
	details VARCHAR(200) NOT NULL ,
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_more_content'") != $t_ap_more_content)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_more_content . " (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	config_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	option_type SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	content VARCHAR(1000) NOT NULL ,
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_flickr_img'") != $t_ap_flickr_img)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_flickr_img . " (
    id BIGINT(20) UNSIGNED NOT NULL,
	flickr_photo_id VARCHAR(20) NOT NULL,  
	url_info VARCHAR(100) NOT NULL,
	oauth_id SMALLINT(5) UNSIGNED NOT NULL,
	local_key VARCHAR(100) NULL DEFAULT NULL,
	in_local TINYINT(3) NOT NULL DEFAULT '0',
	date_time INT(10) UNSIGNED NULL DEFAULT NULL,
	INDEX id (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_flickr_oauth'") != $t_ap_flickr_oauth)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_flickr_oauth . " (
    oauth_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	oauth_token VARCHAR(100) NOT NULL ,  
	oauth_token_secret VARCHAR(50) NOT NULL ,
    PRIMARY KEY (oauth_id)
	) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_qiniu_img'") != $t_ap_qiniu_img)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_qiniu_img . " (
    id BIGINT(20) UNSIGNED NOT NULL,
	qiniu_key VARCHAR(100) NOT NULL,
	local_key VARCHAR(100) NULL DEFAULT NULL,
	in_local TINYINT(3) NOT NULL DEFAULT '0',
	date_time INT(10) UNSIGNED NULL DEFAULT NULL,
	INDEX id (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_upyun_img'") != $t_ap_upyun_img)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_upyun_img . " (
    id BIGINT(20) UNSIGNED NOT NULL,
	upyun_key VARCHAR(100) NOT NULL,
	local_key VARCHAR(100) NULL DEFAULT NULL,
	in_local TINYINT(3) NOT NULL DEFAULT '0',
	date_time INT(10) UNSIGNED NULL DEFAULT NULL,
	INDEX id (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_download_img_temp'") != $t_ap_download_img_temp)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_download_img_temp . " (
	config_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    url VARCHAR(1000) NOT NULL,
	save_type TINYINT(3) NOT NULL DEFAULT '0',
	remote_url VARCHAR(1000) NOT NULL,
    downloaded_url varchar(500) NOT NULL,
    local_key varchar(100) NULL DEFAULT NULL,
	remote_key varchar(100) NULL DEFAULT NULL,
    file_path varchar(500) NULL DEFAULT NULL,
	file_name varchar(100) NULL DEFAULT NULL,
	mime_type varchar(20) NULL DEFAULT NULL
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  //$t_ap_watermark
  if(($wpdb->get_var("SHOW TABLES LIKE '$t_ap_watermark'") != $t_ap_watermark)||$wp_autopost_db_version!=$old_db_version){
    $sql = "CREATE TABLE " . $t_ap_watermark . " (
	id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    wm_type TINYINT(3) NOT NULL DEFAULT '0',
	wm_position TINYINT(3) NOT NULL DEFAULT '9',
	wm_font VARCHAR(1000) NULL DEFAULT NULL,
    wm_text VARCHAR(1000) NULL DEFAULT NULL,
	wm_size SMALLINT(5) NULL DEFAULT '16',
	wm_color VARCHAR(100) NULL DEFAULT '#ffffff',
	x_adjustment SMALLINT(5) NULL DEFAULT '0',
	y_adjustment SMALLINT(5) NULL DEFAULT '0',
    transparency SMALLINT(5) NULL DEFAULT '80',
	upload_image VARCHAR(1000) NULL DEFAULT NULL,
	upload_image_url VARCHAR(1000) NULL DEFAULT NULL,
	min_width SMALLINT(5) NULL DEFAULT '150',
	min_height SMALLINT(5) NULL DEFAULT '150',
    jpeg_quality SMALLINT(5) NULL DEFAULT '90',
	PRIMARY KEY (id)
     ) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";

	 dbDelta($sql);  
  }

  if($wp_autopost_db_version!=$old_db_version){
    $MicroTransOptions = get_option('wp-autopost-micro-trans-options');
	if($MicroTransOptions!=null&&$MicroTransOptions['clientID']!=null&&$MicroTransOptions['clientSecret']!=null){
       $array = array();
       $newArray = array();
       $newArray['clientID']=$MicroTransOptions['clientID'];
       $newArray['clientSecret']=$MicroTransOptions['clientSecret'];
       $array[] = $newArray;
       update_option('wp-autopost-micro-trans-options',$array);
	}
  }

  $count = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_watermark);
  if($count<1){ // no water marker data
    $watermarkOptions = get_option('wp-watermark-options');
	if($watermarkOptions!=null){    
	  $wm_type = $watermarkOptions['type'];
	  $wm_position = $watermarkOptions['position'];
	  $wm_font = stripslashes($watermarkOptions['font']);

	  $wm_text = $watermarkOptions['text'];
	  $wm_size = $watermarkOptions['size'];
	  $wm_color = $watermarkOptions['color'];
	  $x_adjustment = $watermarkOptions['x-adjustment'];
	  $y_adjustment = $watermarkOptions['y-adjustment'];
	  $transparency = $watermarkOptions['transparency'];
	  $upload_image = $watermarkOptions['upload_image'];
	  $upload_image_url = $watermarkOptions['upload_image_url'];

	  $min_width = $watermarkOptions['min_width'];
	  $min_height = $watermarkOptions['min_height'];
	  $jpeg_quality = $watermarkOptions['jpeg_quality'];

	}else{

	  $wm_type = 1;
	  $wm_position = 9;
	  $wm_font = '';

	  $wm_text = get_bloginfo('url');
	  $wm_size = 16;
	  $wm_color = '#ffffff';
	  $x_adjustment = 0;
	  $y_adjustment =0;
	  $transparency = 80;
	  $upload_image = dirname(__FILE__).'/watermark/uploads/watermark.png';
	  $upload_image_url = plugins_url('/watermark/uploads/watermark.png', __FILE__ );

	  $min_width = 150;
	  $min_height = 150;
	  $jpeg_quality = 90;

	}
    $name='Default Watermark';
	$wpdb->query($wpdb->prepare("insert into $t_ap_watermark (name,wm_type,wm_position,wm_font,wm_text,wm_size,wm_color,x_adjustment,y_adjustment,transparency,upload_image,upload_image_url,min_width,min_height,jpeg_quality) values (%s,%d,%d,%s,%s,%d,%s,%d,%d,%d,%s,%s,%d,%d,%d)",$name,$wm_type,$wm_position,$wm_font,$wm_text,$wm_size,$wm_color,$x_adjustment,$y_adjustment,$transparency,$upload_image,$upload_image_url,$min_width,$min_height,$jpeg_quality));
  }


  

  update_option("wp_autopost_db_version", $wp_autopost_db_version);


   $flickrOptions = array(
	  'api_key' => 'fc1ec013e1bfb8f17b952a89efbe355e',
	  'api_secret' => 'bbba8595664cfd10',
	  'oauth_token' => '',
	  'oauth_token_secret' => '',
	  'user_id' => '',
	  'flickr_set'=>'',
	  'is_public' => 0
   );
   add_option( 'wp-autopost-flickr-options', $flickrOptions );

   $qiniuOptions = array(
	  'domain' => '',
	  'bucket' => '',
	  'access_key' => '',
	  'secret_key' => ''
   );
   add_option( 'wp-autopost-qiniu-options', $qiniuOptions );



}
register_activation_hook( __FILE__,'wp_autopost_pro_install');



include WPAPPRO_PATH.'/wp-autopost-utility-class.php';
include WPAPPRO_PATH.'/wp-autopost-function.php';

?>