<?php 
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( !class_exists( 'WPeMatico_functions' ) ) {

class WPeMatico_functions {
	public static function wpematico_env_checks() {
		global $wp_version, $user_ID;
		$message = $wpematico_admin_message = '';
		$message='';
		$checks=true;
		if(!is_admin()) return false;
		if (version_compare($wp_version, '3.9', '<')) { // check WP Version
			$message.=__('- WordPress 3.9 or higher needed!', WPeMatico :: TEXTDOMAIN ) . '<br />';
			$checks=false;
		}
		if (version_compare(phpversion(), '5.3.0', '<')) { // check PHP Version
			$message.=__('- PHP 5.3.0 or higher needed!', WPeMatico :: TEXTDOMAIN ) . '<br />';
			$checks=false;
		}
		// Check if PRO version is installed and its required version
		$active_plugins = get_option( 'active_plugins' );
		$active_plugins_names = array_map('basename', $active_plugins );
		$is_pro_active = array_search( 'wpematicopro.php', $active_plugins_names );
		if( $is_pro_active !== FALSE ) {
			//$proplugin_data['Name']=  WPeMaticoPRO::NAME;
			$plpath = trailingslashit(WP_PLUGIN_DIR).$active_plugins[$is_pro_active];
			$proplugin_data = self::plugin_get_version($plpath);
			if( $proplugin_data['Name'] == 'WPeMatico Professional' && version_compare($proplugin_data['Version'], WPeMatico::PROREQUIRED, '<') ) {
				$message.= __('You are using WPeMatico Professional too old.', WPeMatico :: TEXTDOMAIN ).'<br />';
				$message.= __('Must install at least <b>WPeMatico Professional</b> '.WPeMatico::PROREQUIRED, WPeMatico :: TEXTDOMAIN );
				$message.= ' <a href="'.admin_url('plugins.php?page=wpemaddons').'#wpematico-pro"> '. __('Go to upgrade Now', WPeMatico :: TEXTDOMAIN ). '</a>';
				$message.= '<script type="text/javascript">jQuery(document).ready(function($){$("#wpematico-pro").css("backgroundColor","yellow");});</script>';
				//Commented to allow access to the settings page
				//$checks=false;
			}
		}

		if (wp_next_scheduled('wpematico_cron')!=0 and wp_next_scheduled('wpematico_cron')>(time()+360)) {  //check cron jobs work
			$message.=__("- WP-Cron don't working please check it!", WPeMatico :: TEXTDOMAIN ) .'<br />';
		}
		//put massage if one
		if (!empty($message))
			$wpematico_admin_message = '<div id="message" class="error fade"><strong>WPeMatico:</strong><br />'.$message.'</div>';
		
//		$notice = delete_option('wpematico_notices');
		$notice = get_option('wpematico_notices');
		if (!empty($notice)) {
			foreach($notice as $key => $mess) {
				if($mess['user_ID'] == $user_ID) {
					$class = ($mess['error']) ? "notice notice-error" : "notice notice-success";
					$class .= ($mess['is-dismissible']) ? " is-dismissible" : "";
					$class .= ($mess['below-h2']) ? " below-h2" : "";
					$wpematico_admin_message .= '<div id="message" class="'.$class.'"><p>'.$mess['text'].'</p></div>';
					unset( $notice[$key] );
				}
			}
			update_option('wpematico_notices',$notice);
		}
		
		if (!empty($wpematico_admin_message)) {
			//send response to admin notice : ejemplo con la función dentro del add_action
			add_action('admin_notices', function() use ($wpematico_admin_message) {
				//echo '<div class="error"><p>', esc_html($wpematico_admin_message), '</p></div>';
				echo $wpematico_admin_message;
			});
		}
		return $checks;
	}

/* 	//Admin header notify
	function wpematico_admin_notice() {
		global $wpematico_admin_message;
		echo $wpematico_admin_message;
	}
 */	
	/** add_wp_notice
	 * 
	 * @param type mixed array/string  $new_notice 
	 *	optional   ['user_ID'] to shows the notice default = currentuser
	 *	optional   ['error'] true or false to define style. Default = false
	 *	optional   ['is-dismissible'] true or false to hideable. Default = true
	 *	optional   ['below-h2'] true or false to shows above page Title. Default = true
	 *	   ['text'] The Text to be displayed. Default = ''
	 * 
	 */
	Public static function add_wp_notice($new_notice) {
		if(is_string($new_notice)) $adm_notice['text'] = $new_notice;
			else $adm_notice['text'] = (!isset($new_notice['text'])) ? '' : $new_notice['text'];
		$adm_notice['error'] = (!isset($new_notice['error'])) ? false : $new_notice['error'];
		$adm_notice['below-h2'] = (!isset($new_notice['below-h2'])) ? true : $new_notice['below-h2'];
		$adm_notice['is-dismissible'] = (!isset($new_notice['is-dismissible'])) ? true : $new_notice['is-dismissible'];
		$adm_notice['user_ID'] = (!isset($new_notice['user_ID'])) ? get_current_user_id() : $new_notice['user_ID'];
		
		$notice = get_option('wpematico_notices');
		$notice[] = $adm_notice;
		update_option('wpematico_notices',$notice);
	}
	
	
	//file size
	public static function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	//************************* CARGA CAMPAÑASS *******************************************************
 /**
   * Load all campaigns data
   * 
   * @return an array with all campaigns data 
   **/	
	public static function get_campaigns() {
		$campaigns_data = array();
		$args = array(
			'orderby'         => 'ID',
			'order'           => 'ASC',
			'post_type'       => 'wpematico', 
			'numberposts' => -1
		);
		$campaigns = get_posts( $args );
		foreach( $campaigns as $post ):
			$campaigns_data[] = self::get_campaign( $post->ID );	
		endforeach; 
		return $campaigns_data;
	}
 
	//************************* CARGA CAMPAÑA *******************************************************
 /**
   * Load campaign data
   * Required @param   integer  $post_id    Campaign ID to load
   * 		  @param   boolean  $getfromdb  if set to true run get_post($post_ID) and retuirn object post
   * 
   * @return an array with campaign data 
   **/	
	public static function get_campaign( $post_id , $getfromdb = false ) {
		if ( $getfromdb ){
			$campaign = get_post($post_id);
		}
		$campaign_data = get_post_meta( $post_id , 'campaign_data' );
		$campaign_data = ( isset($campaign_data[0]) ) ? $campaign_data[0] : array(0) ;
		$campaign_data = apply_filters('wpematico_check_campaigndata', $campaign_data);
		return $campaign_data;
	}
	
    //************************* Check campaign data *************************************
    /**
    * Check campaign data
    * Required @param $campaigndata array with campaign data values
    * 
    * @return an array with campaign data fixed all empty values
    **/	
	/************** CHECK DATA *************************************************/
	public static function check_campaigndata( $post_data ) {
		global $post, $cfg;
		if(is_null($cfg)) $cfg = get_option(WPeMatico::OPTION_KEY);

		$campaigndata = array();
		if(isset($post_data['ID']) && !empty($post_data['ID']) ) {
			$campaigndata['ID']=$post_data['ID'];
		}elseif(isset($post_data['campaign_id']) && !empty($post_data['campaign_id']) ) {
			$campaigndata['ID']= $post_data['campaign_id'];
		}elseif( isset($post->ID) && $post->ID > 0 ) {
			$campaigndata['ID']=$post->ID;
		}else{
			$campaigndata['ID']=0;
		}
		
		//$campaigndata['campaign_id'] = $post_id;
		$campaigndata['campaign_title'] = (isset($post_data['campaign_title']) && !empty($post_data['campaign_title']) ) ? $post_data['campaign_title'] : get_the_title($campaigndata['ID']);

		$campaigndata['campaign_type']	= (!isset($post_data['campaign_type']) ) ? 'feed': $post_data['campaign_type'];

		$campaigndata['campaign_posttype']	= (!isset($post_data['campaign_posttype']) ) ? 'publish': $post_data['campaign_posttype'];
		$campaigndata['campaign_customposttype']	= (!isset($post_data['campaign_customposttype']) ) ? 'post': $post_data['campaign_customposttype'];
		$arrTaxonomies = get_object_taxonomies($campaigndata['campaign_customposttype']);
		if (in_array('post_format', $arrTaxonomies)) {
			$campaigndata['campaign_post_format']	= (!isset($post_data['campaign_post_format']) ) ? '0': $post_data['campaign_post_format'];
		} else {
			$campaigndata['campaign_post_format']	= '0';
		}
		$campaigndata['activated']	= (!isset($post_data['activated']) || empty($post_data['activated'])) ? false: ($post_data['activated']==1) ? true : false;
		$campaigndata['campaign_feeddate']	= (!isset($post_data['campaign_feeddate']) || empty($post_data['campaign_feeddate'])) ? false: ($post_data['campaign_feeddate']==1) ? true : false;

		$campaign_feeds = array();
		$all_feeds = ( isset($post_data['campaign_feeds']) && !empty($post_data['campaign_feeds']) ) ? $post_data['campaign_feeds'] : Array();
		if( !empty($all_feeds) ) {  // Proceso los feeds sacando los que estan en blanco
			foreach($all_feeds as $id => $feedname) {
				if(!empty($feedname)) 
					$campaign_feeds[]=$feedname ;
			}
		}
		$campaigndata['campaign_feeds'] = (array)$campaign_feeds ;
		
		$campaigndata['cron'] = (!isset($post_data['cronminutes']) ) ? ( (!isset($post_data['cron']) ) ? '0 3 * * *' : $post_data['cron'] ) : WPeMatico :: cron_string($post_data);
		
		$campaigndata['cronnextrun']= (isset($post_data['cronnextrun']) && !empty($post_data['cronnextrun']) ) ? (int)$post_data['cronnextrun'] :  (int)WPeMatico :: time_cron_next($campaigndata['cron']);
/*		//Patch Cron Next run
		if (isset($post_data['cronnextrun']) && !empty($post_data['cronnextrun']) ) {
			if ( $post_data['cronnextrun'] <= current_time('timestamp') && $campaigndata['activated'] ) {
				$campaigndata['cronnextrun']= (int)WPeMatico :: time_cron_next($campaigndata['cron']);
			}else{
				$campaigndata['cronnextrun']= (int)$post_data['cronnextrun'];
			}
		}else{
			$campaigndata['cronnextrun']= (int)WPeMatico :: time_cron_next($campaigndata['cron']);
		}
*/		
		// Direccion de e-mail donde enviar los logs
		$campaigndata['mailerroronly']	= (!isset($post_data['mailerroronly']) || empty($post_data['mailerroronly'])) ? false: ($post_data['mailerroronly']==1) ? true : false;
		$campaigndata['mailaddresslog']	= (!isset($post_data['mailaddresslog']) ) ? '' : sanitize_email( $post_data['mailaddresslog'] );

		// *** Campaign Options
		$campaigndata['campaign_max']	= (!isset($post_data['campaign_max']) ) ? 5: (int)$post_data['campaign_max'];		
		$campaigndata['campaign_author']	= (!isset($post_data['campaign_author']) ) ? 0: (int)$post_data['campaign_author'];		
		$campaigndata['campaign_linktosource']=(!isset($post_data['campaign_linktosource']) || empty($post_data['campaign_linktosource'])) ? false: ($post_data['campaign_linktosource']==1) ? true : false;
		$campaigndata['avoid_search_redirection']=(!isset($post_data['avoid_search_redirection']) || empty($post_data['avoid_search_redirection'])) ? false: ($post_data['avoid_search_redirection']==1) ? true : false;
		$campaigndata['campaign_strip_links']=(!isset($post_data['campaign_strip_links']) || empty($post_data['campaign_strip_links'])) ? false: ($post_data['campaign_strip_links']==1) ? true : false;
		$campaigndata['campaign_commentstatus']	= (!isset($post_data['campaign_commentstatus']) ) ? 'closed' :  $post_data['campaign_commentstatus'];
		$campaigndata['campaign_allowpings']=(!isset($post_data['campaign_allowpings']) || empty($post_data['campaign_allowpings'])) ? false: ($post_data['campaign_allowpings']==1) ? true : false;
		$campaigndata['campaign_woutfilter']=(!isset($post_data['campaign_woutfilter']) || empty($post_data['campaign_woutfilter'])) ? false: ($post_data['campaign_woutfilter']==1) ? true : false;
		$campaigndata['campaign_striphtml']	= (!isset($post_data['campaign_striphtml']) || empty($post_data['campaign_striphtml'])) ? false: ($post_data['campaign_striphtml']==1) ? true : false;

	// *** Campaign Images
		$campaigndata['campaign_imgcache']=(!isset($post_data['campaign_imgcache']) || empty($post_data['campaign_imgcache'])) ? false: ($post_data['campaign_imgcache']==1) ? true : false;
		$campaigndata['campaign_cancel_imgcache']=(!isset($post_data['campaign_cancel_imgcache']) || empty($post_data['campaign_cancel_imgcache'])) ? false: ($post_data['campaign_cancel_imgcache']==1) ? true : false;
		if (isset($cfg['imgcache']) && $cfg['imgcache']) {
			if ($campaigndata['campaign_cancel_imgcache']) $campaigndata['campaign_imgcache'] = false;
		}else{
			if ($campaigndata['campaign_imgcache']) $campaigndata['campaign_cancel_imgcache'] = false;
		}
		$campaigndata['campaign_nolinkimg']=(!isset($post_data['campaign_nolinkimg']) || empty($post_data['campaign_nolinkimg'])) ? false: ($post_data['campaign_nolinkimg']==1) ? true : false;
		$campaigndata['campaign_solo1ra']=(!isset($post_data['campaign_solo1ra']) || empty($post_data['campaign_solo1ra'])) ? false: ($post_data['campaign_solo1ra']==1) ? true : false;

		// *** Campaign Template
		$campaigndata['campaign_enable_template']=(!isset($post_data['campaign_enable_template']) || empty($post_data['campaign_enable_template'])) ? false: ($post_data['campaign_enable_template']==1) ? true : false;
		if(isset($post_data['campaign_template']))
			$campaigndata['campaign_template'] = $post_data['campaign_template'];
		else{
			$campaigndata['campaign_enable_template'] = false;
			$campaigndata['campaign_template'] = '';
		}

	// *** Processed posts count
		$campaigndata['postscount']	= (!isset($post_data['postscount']) ) ? 0: (int)$post_data['postscount'];
		$campaigndata['lastpostscount']	= (!isset($post_data['lastpostscount']) ) ? 0: (int)$post_data['lastpostscount'];
		$campaigndata['lastrun']	= (!isset($post_data['lastrun']) ) ? 0: (int)$post_data['lastrun'];
		$campaigndata['lastruntime']	= (!isset($post_data['lastruntime']) ) ? 0: $post_data['lastruntime'];  // can be string

		$campaigndata['starttime']	= (!isset($post_data['starttime']) ) ? 0: (int)$post_data['starttime'];		

		//campaign_categories & tags		
		if (in_array('post_tag', $arrTaxonomies)) {
			$campaigndata['campaign_tags']	= (!isset($post_data['campaign_tags']) ) ? '': $post_data['campaign_tags'];
		} else {
			$campaigndata['campaign_tags']	= '';
		}

		$campaigndata['campaign_autocats']	= (!isset($post_data['campaign_autocats']) || empty($post_data['campaign_autocats'])) ? false: ($post_data['campaign_autocats']==1) ? true : false;
		// Primero proceso las categorias nuevas si las hay y las agrego al final del array
		# New categories
		if(isset($post_data['campaign_newcat'])) {
		  foreach($post_data['campaign_newcat'] as $k => $on) {
			$catname = $post_data['campaign_newcatname'][$k];
			if(!empty($catname))  {
			  //$post_data['post_category'][] = wp_insert_category(array('cat_name' => $catname));
			  $arg = array('description' => "Auto Added by WPeMatico", 'parent' => "0");
			  $newcat = wp_insert_term($catname, "category", $arg);
			  $post_data['post_category'][] = (is_array($newcat)) ? $newcat['term_id'] : $newcat;
			}
		  }
		}
		# All: Las elegidas + las nuevas ya agregadas
		if (in_array('category', $arrTaxonomies)) {
			$campaigndata['campaign_categories'] = (!isset($post_data['post_category']) ) ? ( (!isset($post_data['campaign_categories']) ) ? array() : (array)$post_data['campaign_categories'] ) : (array)$post_data['post_category'];
		} else {
			$campaigndata['campaign_categories']	= array();
		}

		#Proceso las Words to Category sacando los que estan en blanco
		//campaign_wrd2cat, campaign_wrd2cat_regex, campaign_wrd2cat_category 
		$campaign_wrd2cat = Array();
		if( isset($post_data['campaign_wrd2cat']['word']) ) {
			//for ($i = 0; $i <= count(@$campaign_wrd2cat['word']); $i++) {
			foreach($post_data['campaign_wrd2cat']['word'] as $id => $value) {       
				//$word = ( isset($post_data['_wp_http_referer']) ) ? addslashes($post_data['campaign_wrd2cat']['word'][$id]): $post_data['campaign_wrd2cat']['word'][$id];
				$word = ($post_data['campaign_wrd2cat']['word'][$id]);
				$regex = ($post_data['campaign_wrd2cat']['regex'][$id]==1) ? true : false ;
				$cases = ($post_data['campaign_wrd2cat']['cases'][$id]==1) ? true : false ;
				$w2ccateg = $post_data['campaign_wrd2cat']['w2ccateg'][$id];
				if(!empty($word))  {
					$campaign_wrd2cat['word'][]= ($regex) ? $word : htmlspecialchars($word) ;
					$campaign_wrd2cat['regex'][]= $regex;
					$campaign_wrd2cat['cases'][]= $cases;
					$campaign_wrd2cat['w2ccateg'][]=$w2ccateg ;
				}
			}
		}
		$_wrd2cat = array('word'=>array(''),'regex'=>array(false),'w2ccateg'=>array(''),'cases'=>array(false));
		$campaigndata['campaign_wrd2cat'] = (!empty($campaign_wrd2cat) ) ?(array) $campaign_wrd2cat : (array)$_wrd2cat;
		
		// *** Campaign Rewrites	
		// Proceso los rewrites sacando los que estan en blanco
//		$campaign_rewrites = Array();
		$campaign_rewrites = ( isset($post_data['campaign_rewrites']) && !empty($post_data['campaign_rewrites']) ) ? $post_data['campaign_rewrites'] : Array();
		if(isset($post_data['campaign_word_origin'])) {
			$post_data['campaign_word_origin'] = stripslashes_deep($post_data['campaign_word_origin']);
			$post_data['campaign_word_rewrite'] = stripslashes_deep($post_data['campaign_word_rewrite']);
			$post_data['campaign_word_relink'] = stripslashes_deep($post_data['campaign_word_relink']);
			foreach($post_data['campaign_word_origin'] as $id => $rewrite) {       
				$origin = addslashes($post_data['campaign_word_origin'][$id]);
				$regex = $post_data['campaign_word_option_regex'][$id]==1 ? true : false ;
				$title = $post_data['campaign_word_option_title'][$id]==1 ? true : false ;
				$rewrite = addslashes($post_data['campaign_word_rewrite'][$id]);
				$relink = addslashes($post_data['campaign_word_relink'][$id]);
				if(!empty($origin))  {
					$campaign_rewrites['origin'][]=$origin ;
					$campaign_rewrites['regex'][]= $regex;
					$campaign_rewrites['title'][]= $title;
					$campaign_rewrites['rewrite'][]=$rewrite ;
					$campaign_rewrites['relink'][]=$relink ;
				}
			}
		}
		$campaigndata['campaign_rewrites'] = !empty($campaign_rewrites) ? (array)$campaign_rewrites : array('origin'=>array(''),'title'=>array(false),'regex'=>array(false),'rewrite'=>array(''),'relink'=>array(''));
		if(has_filter('pro_check_campaigndata')) $campaigndata =  apply_filters('pro_check_campaigndata', $campaigndata, $post_data);
		return $campaigndata;
	}
	
	
    //************************* GUARDA CAMPAÑA *******************************************************
    /**
    * save campaign data
    * Required @param   integer  $post_id    Campaign ID to load
    * 		  @param   boolean  $getfromdb  if set to true run get_post($post_ID) and retuirn object post
    * 
    * @return an array with campaign data 
    **/	
	public static function update_campaign( $post_id , $campaign = array() ) {
		$campaign['cronnextrun']= (int)WPeMatico :: time_cron_next($campaign['cron']);
		$campaign = apply_filters('wpematico_before_update_campaign', $campaign);
		
		add_post_meta( $post_id, 'postscount', $campaign['postscount'], true )  or
          update_post_meta( $post_id, 'postscount', $campaign['postscount'] );
		
		add_post_meta( $post_id, 'cronnextrun', $campaign['cronnextrun'], true )  or
          update_post_meta( $post_id, 'cronnextrun', $campaign['cronnextrun'] );
		
		add_post_meta( $post_id, 'lastrun', $campaign['lastrun'], true )  or
          update_post_meta( $post_id, 'lastrun', $campaign['lastrun'] );
		
					// *** Campaign Rewrites	
		// Proceso los rewrites agrego slashes	
		if (isset($campaign['campaign_rewrites']['origin']))
			for ($i = 0; $i < count($campaign['campaign_rewrites']['origin']); $i++) {
				$campaign['campaign_rewrites']['origin'][$i] = addslashes($campaign['campaign_rewrites']['origin'][$i]);
				$campaign['campaign_rewrites']['rewrite'][$i] = addslashes($campaign['campaign_rewrites']['rewrite'][$i]);
				$campaign['campaign_rewrites']['relink'][$i] = addslashes($campaign['campaign_rewrites']['relink'][$i]);
			}
		if (isset($campaign['campaign_wrd2cat']['word']))
			for ($i = 0; $i < count($campaign['campaign_wrd2cat']['word']); $i++) {
				$campaign['campaign_wrd2cat']['word'][$i] = addslashes($campaign['campaign_wrd2cat']['word'][$i]);
			}
		
		return add_post_meta( $post_id, 'campaign_data', $campaign, true )  or
          update_post_meta( $post_id, 'campaign_data', $campaign );
		  
	}
	
	/*********** 	 Funciones para procesar campañas ******************/
	//DoJob
	public static function wpematico_dojob($jobid) {
		global $campaign_log_message;
		$campaign_log_message = "";
		if (empty($jobid))
			return false;
		require_once(dirname(__FILE__).'/campaign_fetch.php');
		$fetched= new wpematico_campaign_fetch($jobid);
		unset($fetched);
		return $campaign_log_message;
	}

	// Processes all campaigns
 	public static function processAll() {
		$args = array( 'post_type' => 'wpematico', 'orderby' => 'ID', 'order' => 'ASC' );
		$campaignsid = get_posts( $args );
		$msglogs = "";
		foreach( $campaignsid as $campaignid ) {
			@set_time_limit(0);    
			$msglogs .= WPeMatico :: wpematico_dojob( $campaignid->ID ); 
		}
		return $msglogs;
	}
	

	//Permalink to Source
	/*** Determines what the title has to link to   * @return string new text   **/
	public static function wpematico_permalink($url) {
		// if from admin panel
		if(get_the_ID()) {
			$campaign_id = (int) get_post_meta(get_the_ID(), 'wpe_campaignid', true);
			if($campaign_id) {
				$campaign = self::get_campaign( $campaign_id );
				if( isset($campaign['campaign_linktosource']) && $campaign['campaign_linktosource'] )
					return get_post_meta(get_the_ID(), 'wpe_sourcepermalink', true);
			}
		}
		return $url;      
	}
 
	
//*********************************************************************************************************
  /**
   * Parses a feed with SimplePie
   *
   * @param   boolean     $stupidly_fast    Set fast mode. Best for checks
   * @param   integer     $max              Limit of items to fetch
   * @return  SimplePie_Item    Feed object
   **/
  public static function fetchFeed($url, $stupidly_fast = false, $max = 0) {  # SimplePie
	$cfg = get_option(WPeMatico :: OPTION_KEY);
	if ( $cfg['force_mysimplepie']){
		if (class_exists('SimplePie')) {
			echo '<div id="message" class="notice notice-error is-dismissible"><p>'.
				__('It seems that another plugin are opening Wordpress SimplePie before that WPeMatico can open its own library. This gives a PHP error on duplicated classes.', 'wpematico')
			.'<br />'.
				__('You must disable the other plugin to allow Force WPeMatico Custom SimplePie library.')
			.'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.
				__('Dismiss this notice.')
			. '</span></button></div>';
			
		}else {
			require_once dirname( __FILE__) . '/lib/simplepie.inc.php';
		}
	}else{
		if (!class_exists('SimplePie')) {
			if (is_file( ABSPATH . WPINC . '/class-simplepie.php'))
				include_once( ABSPATH. WPINC . '/class-simplepie.php' );
			else if (is_file( ABSPATH.'wp-admin/includes/class-simplepie.php'))
				include_once( ABSPATH.'wp-admin/includes/class-simplepie.php' );
			else
				include_once( dirname( __FILE__) . '/lib/simplepie.inc.php' );
		}		
	}
    $feed = new SimplePie();
    $feed->enable_order_by_date(false);

	$feed->set_feed_url($url);
    $feed->feed_url = rawurldecode($feed->feed_url);
    $feed->set_item_limit($max);
	$feed->set_stupidly_fast($stupidly_fast);
	if(!$stupidly_fast) {
		if($cfg['simplepie_strip_htmltags']) { 
			$strip_htmltags =  sanitize_text_field( $cfg['strip_htmltags'] );
			$strip_htmltags =  (isset($strip_htmltags) && empty($strip_htmltags) ) ? $strip_htmltags=array() : explode(',', $strip_htmltags);
			$strip_htmltags = array_map( 'trim', $strip_htmltags );
			$feed->strip_htmltags( $strip_htmltags );
			$feed->strip_htmltags = $strip_htmltags;
		}
		if($cfg['simplepie_strip_attributes']){ 
			$feed->strip_attributes( $cfg['strip_htmlattr'] ); 
		}
	}
	if(has_filter('wpematico_fetchfeed')) $feed =  apply_filters('wpematico_fetchfeed', $feed, $url);
    $feed->enable_cache(false);    
    $feed->init();
    $feed->handle_content_type(); 
    
    return $feed;
  }
 
	/**
	* Tests a feed
	*
	*/
	public static function Test_feed($args='') {
		if (is_array($args)) {
			extract($args);
			$ajax=false;
		} else {
			if(!isset($_POST['url'])) return false;
			$url=$_POST['url'];
			$ajax=true;
		}
		$feed = self :: fetchFeed($url, true);
		$works = !$feed->error(); // if no error returned
		if ($ajax) {
			if($works) {
				$response['message'] = sprintf(__('The feed %s has been parsed successfully.', WPeMatico :: TEXTDOMAIN ), $url);
				$response['success'] = true;
			}else{
				$response['message'] = sprintf(__('The feed %s cannot be parsed. Simplepie said: %s', WPeMatico :: TEXTDOMAIN ), $url, $works);
				$response['success'] = false;
			}
			wp_send_json($response);  //echo json & die
			//echo intval($works);
			//die();
		}else {
			if($works) printf(__('The feed %s has been parsed successfully.', WPeMatico :: TEXTDOMAIN ), $url);
			else	printf(__('The feed %s cannot be parsed. Simplepie said: %s', WPeMatico :: TEXTDOMAIN ), $url, $works);
			return;
		}

	}
  
	################### ARRAYS FUNCS
	/* * filtering an array   */
    public static function filter_by_value ($array, $index, $value){
		$newarray=array();
        if(is_array($array) && count($array)>0){
            foreach(array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];                
                if ($temp[$key] != $value){
                    $newarray[$key] = $array[$key];
                }
            }
        }
      return $newarray;
    } 
	 //Example: array_sort($my_array,'!group','surname');
	//Output: sort the array DESCENDING by group and then ASCENDING by surname. Notice the use of ! to reverse the sort order. 
	public static function array_sort_func($a,$b=NULL) {
		static $keys;
		if($b===NULL) return $keys=$a;
		foreach($keys as $k) {
			if(@$k[0]=='!') {
				$k=substr($k,1);
				if(@$a[$k]!==@$b[$k]) {
					return strcmp(@$b[$k],@$a[$k]);
				}
			}
			else if(@$a[$k]!==@$b[$k]) {
				return strcmp(@$a[$k],@$b[$k]);
			}
		}
		return 0;
	}

	public static function array_sort(&$array) {
		if(!$array) return false;
		$keys=func_get_args();
		array_shift($keys);
		self::array_sort_func($keys);
		usort($array, array(__CLASS__,"array_sort_func"));
	} 
	################### END ARRAYS FUNCS

// ********************************** CRON FUNCTIONS
	public static function cron_string($array_post){
		if ($array_post['cronminutes'][0]=='*' or empty($array_post['cronminutes'])) {
			if(!empty($array_post['cronminutes'][1])){
				$array_post['cronminutes'] = array('*/' . $array_post['cronminutes'][1]);
			}else{
				$array_post['cronminutes'] = array('*');
			}
		}
		if ($array_post['cronhours'][0]=='*' or empty($array_post['cronhours'])) {
			if(!empty($array_post['cronhours'][1]))
				$array_post['cronhours'] = array('*/' . $array_post['cronhours'][1]);
			else
				$array_post['cronhours'] = array('*');
		}
		if ($array_post['cronmday'][0]=='*' or empty($array_post['cronmday'])) {
			if (!empty($array_post['cronmday'][1]))
				$array_post['cronmday']=array('*/'.$array_post['cronmday'][1]);
			else
				$array_post['cronmday']=array('*');
		}
		if ($array_post['cronmon'][0]=='*' or empty($array_post['cronmon'])) {
			if (!empty($array_post['cronmon'][1]))
				$array_post['cronmon']=array('*/'.$array_post['cronmon'][1]);
			else
				$array_post['cronmon']=array('*');
		}
		if ($array_post['cronwday'][0]=='*' or empty($array_post['cronwday'])) {
			if (!empty($array_post['cronwday'][1]))
				$array_post['cronwday']=array('*/'.$array_post['cronwday'][1]);
			else
				$array_post['cronwday']=array('*');
		}
		return implode(",",$array_post['cronminutes']).' '.implode(",",$array_post['cronhours']).' '.implode(",",$array_post['cronmday']).' '.implode(",",$array_post['cronmon']).' '.implode(",",$array_post['cronwday']);
	}
	
	//******************************************************************************
	//Calcs next run for a cron string as timestamp
	public static function time_cron_next($cronstring) {
		//Cronstring zerlegen
		list($cronstr['minutes'],$cronstr['hours'],$cronstr['mday'],$cronstr['mon'],$cronstr['wday'])=explode(' ',$cronstring,5);

		//make arrys form string
		foreach ($cronstr as $key => $value) {
			if (strstr($value,','))
				$cronarray[$key]=explode(',',$value);
			else
				$cronarray[$key]=array(0=>$value);
		}
		//make arrys complete with ranges and steps
		foreach ($cronarray as $cronarraykey => $cronarrayvalue) {
			$cron[$cronarraykey]=array();
			foreach ($cronarrayvalue as $key => $value) {
				//steps
				$step=1;
				if (strstr($value,'/'))
					list($value,$step)=explode('/',$value,2);
				//replase weekeday 7 with 0 for sundays
				if ($cronarraykey=='wday') $value=str_replace('7','0',$value);
				//ranges
				if (strstr($value,'-')) {
					list($first,$last)=explode('-',$value,2);
					if (!is_numeric($first) or !is_numeric($last) or $last>60 or $first>60) //check
						return false;
					if ($cronarraykey=='minutes' and $step<5) $step=5; //set step in num to 5 min.

					$range=array();
					for ($i=$first;$i<=$last;$i=$i+$step) $range[]=$i;
					
					$cron[$cronarraykey]=array_merge($cron[$cronarraykey],$range);
				} elseif ($value=='*') {
					$range=array();
					if ($cronarraykey=='minutes') {
						if ($step<5) $step=5; //set step in mum to 5 min.
						for ($i=0;$i<=59;$i=$i+$step) $range[]=$i;
					}
					if ($cronarraykey=='hours') {
						for ($i=0;$i<=23;$i=$i+$step) $range[]=$i;
					}
					if ($cronarraykey=='mday') {
						for ($i=$step;$i<=31;$i=$i+$step) $range[]=$i;
					}
					if ($cronarraykey=='mon') {
						for ($i=$step;$i<=12;$i=$i+$step) $range[]=$i;
					}
					if ($cronarraykey=='wday') {
						for ($i=0;$i<=6;$i=$i+$step) $range[]=$i;
					}
					$cron[$cronarraykey]=array_merge($cron[$cronarraykey],$range);
				} else {
					//Month names
					if (strtolower($value)=='jan') $value=1;
					if (strtolower($value)=='feb') $value=2;
					if (strtolower($value)=='mar') $value=3;
					if (strtolower($value)=='apr') $value=4;
					if (strtolower($value)=='may') $value=5;
					if (strtolower($value)=='jun') $value=6;
					if (strtolower($value)=='jul') $value=7;
					if (strtolower($value)=='aug') $value=8;
					if (strtolower($value)=='sep') $value=9;
					if (strtolower($value)=='oct') $value=10;
					if (strtolower($value)=='nov') $value=11;
					if (strtolower($value)=='dec') $value=12;
					//Week Day names
					if (strtolower($value)=='sun') $value=0;
					if (strtolower($value)=='mon') $value=1;
					if (strtolower($value)=='tue') $value=2;
					if (strtolower($value)=='wed') $value=3;
					if (strtolower($value)=='thu') $value=4;
					if (strtolower($value)=='fri') $value=5;
					if (strtolower($value)=='sat') $value=6;
					if (!is_numeric($value) or $value>60) //check
						return false;
					$cron[$cronarraykey]=array_merge($cron[$cronarraykey],array(0=>$value));
				}
			}
		}
		
		//calc next timestamp
		$currenttime=current_time('timestamp');
		foreach (array(date('Y'),date('Y')+1) as $year) {
			foreach ($cron['mon'] as $mon) {
				foreach ($cron['mday'] as $mday) {
					foreach ($cron['hours'] as $hours) {
						foreach ($cron['minutes'] as $minutes) {
							$timestamp=mktime($hours,$minutes,0,$mon,$mday,$year);
							if (in_array(date('w',$timestamp),$cron['wday']) and $timestamp>$currenttime) {
									return $timestamp;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Returns current plugin version.
	 * 
	 * @return string Plugin version
	 */
	public static function plugin_get_version( $file = '') {
		if(empty($file) ) $file = __FILE__ ;
		if ( ! function_exists( 'get_plugins' ) )	require_once( ABSPATH . basename(admin_url()) . '/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( $file ) ) );
		$plugin_file = basename( ( $file ) );
		$plugin_info = array();
		$plugin_info['Name'] = $plugin_folder[$plugin_file]['Name'];
		$plugin_info['Version'] = $plugin_folder[$plugin_file]['Version'];
		return $plugin_info;
	}

	public static function throttling_inserted_post($post_id=0, $campaign=array() ){
		global $cfg;
		sleep($cfg['throttle']);		
	}
	
	/**
	 * if installed try to use CURL, else file_get_contents
	 * @since 1.2.4
	 * @param string $url  URL to get content from
	 * @param bool $curl if exist, force to use CURL. Default true. DEPRECATED
	 * @param bool $curl if not bool used as $args: array('key'=>'value')
	 * 
	 * @return mixed String Content or False if error on get remote file content.
	 */
	public static function wpematico_get_contents($url, $curl=true) {
		if(is_bool($curl)) {
			$args = array(
				'curl' => $curl,
			);
		}else {
			$args = $curl;
		}
		$defaults = array(
			'curl' => true,
			'curl_setopt' => array(
				'CURLOPT_HEADER'=> 0,
				'CURLOPT_RETURNTRANSFER'=> 1,
				'CURLOPT_FOLLOWLOCATION'=> 0,
				//'CURLOPT_USERAGENT'=> "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1",
				'CURLOPT_USERAGENT'=> "Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0 Firefox/5.0",
			),
		);
		
		$r = wp_parse_args( $args, $defaults );
		$curl = $r['curl'];
		
		$data = false;
		if($curl && function_exists('curl_version') )   // (in_array  ('curl', get_loaded_extensions()))
			$data = self::file_get_contents_curl($url, $r);
		
		if(!$data || !$curl || is_null($data) ) {
			$data = file_get_contents($url);
		}
		
		if(!$data){ // if stil getting error on get file content try WP func, this may give timeouts 
			$response = wp_remote_request( $url ,  array( 'timeout' => 5 ));
			if( !is_wp_error( $response ) ) {
				if(isset( $response['response']['code'] ) && 200 === $response['response']['code'] ) {
					$data = wp_remote_retrieve_body( $response );
				}else{
					trigger_error(__('Error with wp_remote_request:', WPeMatico :: TEXTDOMAIN ) . print_r($response,1) ,E_USER_NOTICE);
				}
			}else{
				trigger_error(__('Error with wp_remote_get:', WPeMatico :: TEXTDOMAIN ) . $response->get_error_message(),E_USER_NOTICE);
			}
		}
		
		return $data;
	}
	
	public static function file_get_contents_curl($url, $args = '') {
		if ( empty( $args ) ) {
			$args = array();
		}
		$defaults = array(
			'safemode' => false,
			'curl_setopt' => array(
				'CURLOPT_HEADER'=> 0,
				'CURLOPT_RETURNTRANSFER'=> 1,
				'CURLOPT_FOLLOWLOCATION'=> 0,
				//'CURLOPT_SSL_VERIFYPEER'=> 0,
				'CURLOPT_USERAGENT'=> "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1",
			),
		);
		$r = wp_parse_args( $args, $defaults );
		$ch = curl_init();
		if(!$ch) return false;
		
		$safemode = ini_get('safe_mode');
		if(!$r['safemode']) {ini_set('safe_mode', false);}
		else{ini_set('safe_mode', true);}
		
		curl_setopt($ch, CURLOPT_URL, $url);
		foreach($r['curl_setopt'] as $key => $value) {
			curl_setopt($ch, constant($key), $value);
		}

		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if(function_exists('ini_set')){ ini_set('safe_mode', $safemode);}

		
		return ($httpcode>=200 && $httpcode<300) ? $data : false;
	}
}  // Class WPeMatico_functions
}  // if Class exist

/******* FUNCTIONS  ***********/

add_action( 'admin_init', 'wpematico_process_actions' );
function wpematico_process_actions() {
	if ( isset( $_POST['wpematico-action'] ) ) {
		do_action( 'wpematico_' . $_POST['wpematico-action'], $_POST );
	}

	if ( isset( $_GET['wpematico-action'] ) ) {
		do_action( 'wpematico_' . $_GET['wpematico-action'], $_GET );
	}
}

/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.2.4
 * @return mixed string $host if detected, false otherwise
 */
function wpematico_get_host() {
	$host = false;

	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}

/**
 * wpematico_is_pro_active
 *
 * Returns if installed & active PRO VERSION
 *
 * @since 1.2.4
 * @return bool true if installed & active
 */
function wpematico_is_pro_active() {		// Check if PRO version is installed & active
	$active_plugins = get_option( 'active_plugins' );
	$active_plugins_names = array_map('basename', $active_plugins );
	$is_pro_active = array_search( 'wpematicopro.php', $active_plugins_names );
	if( $is_pro_active !== FALSE ) {
		return true;
	}
}

//add_action( 'wpematico_wp_ratings', 'wpematico_wp_ratings' );
function wpematico_wp_ratings() {
?><div class="postbox">
	<h3 class="handle"><?php _e( '5 Stars Ratings on Wordpress', WPeMatico :: TEXTDOMAIN );?></h3>
	<?php if(get_option('wpem_hide_reviews')) : ?>
	<div class="inside" style="max-height:300px;overflow-x: hidden;">
		<p style="text-align: center;">
			<a href="https://wordpress.org/support/view/plugin-reviews/wpematico?filter=5&rate=5" id="linkgo" class="button" target="_Blank" title="Click to see 5 stars Reviews on Wordpress"> Click to see 5 stars Reviews </a>
		</p>
	</div>
	<?php else: ?>
	<div class="inside" style="max-height:300px;overflow-y: scroll;overflow-x: hidden;">
		<?php require_once('lib/wp_ratings.php'); ?>
	</div>
	<?php endif;	?>
</div>
<?php	
}


/**
 * array_multi_key_exists	http://php.net/manual/es/function.array-key-exists.php#106449
 * @param array $arrNeedles
 * @param array $arrHaystack
 * @param type $blnMatchAll
 * @return boolean
 */
function array_multi_key_exists(array $arrNeedles, array $arrHaystack, $blnMatchAll=true){
    $blnFound = array_key_exists(array_shift($arrNeedles), $arrHaystack);
   
    if($blnFound && (count($arrNeedles) == 0 || !$blnMatchAll))
        return true;
   
    if(!$blnFound && count($arrNeedles) == 0 || $blnMatchAll)
        return false;
   
    return array_multi_key_exists($arrNeedles, $arrHaystack, $blnMatchAll);
}

