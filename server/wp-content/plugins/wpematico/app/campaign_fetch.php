<?php
// don't load directly
if ( !defined('ABSPATH') ){
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( class_exists( 'wpematico_campaign_fetch' ) ) return;
include_once("campaign_fetch_functions.php");

class wpematico_campaign_fetch extends wpematico_campaign_fetch_functions {
	public $cfg			   = array();
	public $campaign_id	   = 0;  // $post_id of campaign
	public $campaign	   = array();
	private $feeds		   = array();
	private $fetched_posts = 0;
	private $lasthash	   = array();
	private $currenthash   = array();
	public $current_item   = array();
	
	public function __construct($campaign_id) {
		global $wpdb,$campaign_log_message, $jobwarnings, $joberrors;
		$jobwarnings=0;
		$joberrors=0;
		//set function for PHP user defined error handling
		if (defined('WP_DEBUG') and WP_DEBUG){
			set_error_handler('wpematico_joberrorhandler',E_ALL | E_STRICT);
		}else{
			set_error_handler('wpematico_joberrorhandler',E_ALL & ~E_NOTICE);
		}
		
		if (!version_compare(phpversion(), '5.3.0', '>')) { // PHP Version
			wpematico_init_set('safe_mode', 'Off');   // deprecated after 5.3
		}
		wpematico_init_set('ignore_user_abort', 'On');
		
		//ignore_user_abort(true);			//user can't abort script (close windows or so.)
		$this->campaign_id=$campaign_id;			   //set campaign id
		$this->campaign = WPeMatico :: get_campaign($this->campaign_id);
		
		//$this->fetched_posts = $this->campaign['postscount'];
		$this->cfg = get_option(WPeMatico :: OPTION_KEY);
		$campaign_timeout = (int)$this->cfg['campaign_timeout'];

		wpematico_init_set('max_execution_time', $campaign_timeout);
		
		// new actions 
		if( (int)$this->cfg['throttle'] > 0 ) add_action('wpematico_inserted_post', array( 'WPeMatico', 'throttling_inserted_post' ));		

		//Set job start settings
		$this->campaign['starttime']	 = current_time('timestamp'); //set start time for job
		$this->campaign['lastpostscount'] = 0; // Lo pone en 0 y lo asigna al final		
		WPeMatico :: update_campaign($this->campaign_id, $this->campaign); //Save start time data
		//
		$this->set_actions_and_filters();
		
		if(has_action('Wpematico_init_fetching')) do_action('Wpematico_init_fetching', $this->campaign);

		//check max script execution tme
		if (ini_get('safe_mode') or strtolower(ini_get('safe_mode'))=='on' or ini_get('safe_mode')=='1')
			trigger_error(sprintf(__('PHP Safe Mode is on!!! Max exec time is %1$d sec.', 'wpematico' ),ini_get('max_execution_time')),E_USER_WARNING);
		// check function for memorylimit
		if (!function_exists('memory_get_usage')) {
			ini_set('memory_limit', apply_filters( 'admin_memory_limit', '256M' )); //Wordpress default
			trigger_error(sprintf(__('Memory limit set to %1$s ,because can not use PHP: memory_get_usage() function to dynamically increase the Memory!', 'wpematico' ),ini_get('memory_limit')),E_USER_WARNING);
		}
		//run job parts
		$postcount = 0;
		$this->feeds = $this->campaign['campaign_feeds'] ; // --- Obtengo los feeds de la campaña
		
		foreach($this->feeds as $feed) {
			// interrupt the script if timeout 
  			if (current_time('timestamp')-$this->campaign['starttime'] >= $campaign_timeout) {
				trigger_error(sprintf(__('Ending feed, reached running timeout at %1$d sec.', 'wpematico' ), $campaign_timeout ),E_USER_WARNING);
				break;
			}
			wpematico_init_set('max_execution_time', $campaign_timeout, true);
			$postcount += $this->processFeed($feed);         #- ---- Proceso todos los feeds      
		}

		$this->fetched_posts += $postcount; 

		$this->fetch_end(); // if everything ok call fetch_end  and end class
	}
	
	public static function set_actions_and_filters() {
		//hook for add actions and filter on init fetching
		//add_action('Wpematico_init_fetching', array($this, 'wpematico_init_fetching') ); 
		add_filter('wpematico_get_post_content_feed', array( 'wpematico_campaign_fetch_functions' , 'wpematico_get_yt_rss_tags'),999,4);
		$priority = 10;
	}
	/**
	* Processes every feed of a campaign
	* @param   $feed       URL string    Feed 
	* @return  The number of posts added
	*/
	private function processFeed($feed)  {
		global $realcount;
		@set_time_limit(0);
		trigger_error('<span class="coderr b"><b>'.sprintf(__('Processing feed %1s.', 'wpematico' ),$feed).'</b></span>' , E_USER_NOTICE);   // Log
		
		$items = array();
		$count = 0;
		$prime = true;

		// Access the feed
		if($this->campaign['campaign_type']=="feed") { 		// Access the feed
			$simplepie =  WPeMatico :: fetchFeed($feed, $this->cfg['set_stupidly_fast'], $this->campaign['campaign_max']);
		}else {
			$simplepie = apply_filters('Wpematico_process_fetching', $this->campaign);
		}
		do_action('Wpematico_process_fetching_'.$this->campaign['campaign_type'], $this);  // Wpematico_process_fetching_feed
		foreach($simplepie->get_items() as $item) {
			if($prime){
				//with first item get the hash of the last item (new) that will be saved.
				$this->lasthash[$feed] = md5($item->get_permalink()); 
				$prime=false;
			}

			$this->currenthash[$feed] = md5($item->get_permalink()); // el hash del item actual del feed feed 
			if( !$this->cfg['allowduplicates'] || !$this->cfg['allowduptitle'] || !$this->cfg['allowduphash'] ){
				if( !$this->cfg['allowduphash'] ){
					// chequeo a la primer coincidencia sale del foreach
					$lasthashvar = '_lasthash_'.sanitize_file_name($feed);
					$hashvalue = get_post_meta( $this->campaign_id, $lasthashvar, true );
					if (!isset( $this->campaign[$feed]['lasthash'] ) ) $this->campaign[$feed]['lasthash'] = '';
					
					$dupi = ( $this->campaign[$feed]['lasthash'] == $this->currenthash[$feed] ) || 
								( $hashvalue == $this->currenthash[$feed] ); 
					if ($dupi) {
						trigger_error(sprintf(__('Found duplicated hash \'%1s\'', 'wpematico' ),$item->get_permalink()).': '.$this->currenthash[$feed] ,E_USER_NOTICE);
						if( !$this->cfg['jumpduplicates'] ) {
							trigger_error(__('Filtering duplicated posts.', 'wpematico' ),E_USER_NOTICE);
							break;
						}else {
							trigger_error(__('Jumping duplicated post. Continuing.', 'wpematico' ),E_USER_NOTICE);
							continue;
						}
					}
				}
				if( !$this->cfg['allowduptitle'] ){
					if($this->WPeisDuplicated($this->campaign, $feed, $item)) {
						trigger_error(sprintf(__('Found duplicated title \'%1s\'', 'wpematico' ),$item->get_title()).': '.$this->currenthash[$feed] ,E_USER_NOTICE);
						if( !$this->cfg['jumpduplicates'] ) {
							trigger_error(__('Filtering duplicated posts.', 'wpematico' ),E_USER_NOTICE);
							break;
						}else {
							trigger_error(__('Jumping duplicated post. Continuing.', 'wpematico' ),E_USER_NOTICE);
							continue;
						}
					}
				}
			}
			$count++;
			array_unshift($items, $item); // add at Post stack in correct order by date 		  
			if($count == $this->campaign['campaign_max']) {
				trigger_error(sprintf(__('Campaign fetch limit reached at %1s.', 'wpematico' ),$this->campaign['campaign_max']),E_USER_NOTICE);
				break;
			}
		}
		
		$campaign_timeout = (int)$this->cfg['campaign_timeout'];
		// Processes post stack
		$realcount = 0;
		foreach($items as $item) {	
			// interrupt the script if timeout 
  			if (current_time('timestamp')-$this->campaign['starttime'] >= $campaign_timeout) {
				trigger_error(sprintf(__('Reached running timeout at %1$d sec.', 'wpematico' ), $campaign_timeout ),E_USER_WARNING);
				break;
			}
			// set timeout for rest of the items to Timeout setting less current run time
			wpematico_init_set('max_execution_time', $campaign_timeout, true); // - ( current_time('timestamp') - $this->campaign['starttime'] ), true);
			$realcount++;
			$this->currenthash[$feed] = md5($item->get_permalink()); // the hash of the current item feed 
			$suma=$this->processItem($simplepie, $item, $feed);

			$lasthashvar = '_lasthash_'.sanitize_file_name($feed);
			$hashvalue = $this->currenthash[$feed];
			add_post_meta( $this->campaign_id, $lasthashvar, $hashvalue, true )  or
				update_post_meta( $this->campaign_id, $lasthashvar, $hashvalue );

			if (isset($suma) && is_int($suma)) {
				$realcount = $realcount + $suma;
				$suma="";
			}
		}
		
		if($realcount) {
			trigger_error(sprintf(__('%s posts added', 'wpematico' ),$realcount),E_USER_NOTICE);
		}
		
		return $realcount;
	}
	
   /**
   * Processes an item: parses and filters
   * @param   $feed       object    Feed database object
   * @param   $item       object    SimplePie_Item object
   * @return true si lo procesó
   */
	function processItem($feed, $item, $feedurl) {
		global $wpdb, $realcount;
		trigger_error(sprintf('<b>' . __('Processing item %1s', 'wpematico' ),$item->get_title().'</b>' ),E_USER_NOTICE);
		$this->current_item = array();
		
		// Get the source Permalink trying to redirect if is set.
		$this->current_item['permalink'] = $this->getReadUrl($item->get_permalink(), $this->campaign);
		
		// First exclude filters
		if ( $this->exclude_filters($this->current_item,$this->campaign,$feed,$item )) {
			return -1 ;  // resta este item del total 
		}
		// Item date
		$itemdate = $item->get_date('U');
		$this->current_item['date'] = null;
		if($this->campaign['campaign_feeddate']) {
			if (($itemdate > $this->campaign['lastrun']) && $itemdate < current_time('timestamp', 1)) {  
				$this->current_item['date'] = $itemdate;
				trigger_error(__('Assigning original date to post.', 'wpematico' ),E_USER_NOTICE);
			}else{
				trigger_error(__('Original date out of range.  Assigning current date to post.', 'wpematico' ) ,E_USER_NOTICE);
			}
		}
		
		// Item title
		$this->current_item['title'] = $item->get_title();
		$this->current_item['title'] = htmlspecialchars_decode($this->current_item['title']);
		$from = mb_detect_encoding($this->current_item['title'], "auto");
		if ($from && $from != 'UTF-8') {
			$this->current_item['title'] = mb_convert_encoding($this->current_item['title'], 'UTF-8', $from);
		}
		
		if( $this->cfg['nonstatic'] ) { $this->current_item = NoNStatic :: title($this->current_item,$this->campaign,$item,$realcount ); }else $this->current_item['title'] = esc_attr($this->current_item['title']);

		
		$this->current_item['title'] = html_entity_decode($this->current_item['title'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
		
 		// Item author
		if( $this->cfg['nonstatic'] ) { $this->current_item = NoNStatic :: author($this->current_item,$this->campaign, $feedurl ); }else $this->current_item['author'] = $this->campaign['campaign_author'];

		// Item content
		$this->current_item['content'] = apply_filters('wpematico_get_post_content_feed', $item->get_content(), $this->campaign, $feed, $item );
		$this->current_item = apply_filters('wpematico_get_post_content', $this->current_item, $this->campaign, $feed, $item );

		$from = mb_detect_encoding($this->current_item['content'], "auto");
		if ($from && $from != 'UTF-8') {
			$this->current_item['content'] = mb_convert_encoding($this->current_item['content'], 'UTF-8', $from);
		}
		$this->current_item['content'] = html_entity_decode($this->current_item['content'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
		//********* Parse and upload images
		$this->current_item = apply_filters('wpematico_item_filters_pre_img', $this->current_item, $this->campaign );
		//gets images array 
		$this->current_item = $this->Get_Item_images($this->current_item,$this->campaign,$feed,$item);

		$this->current_item['featured_image'] = apply_filters('wpematico_set_featured_img', '', $this->current_item, $this->campaign, $feed, $item );
		if($this->cfg['featuredimg']){
			if(!empty($this->current_item['images'])){
				$this->current_item['featured_image'] = apply_filters('wpematico_get_featured_img', $this->current_item['images'][0], $this->current_item);
			}
		}
		if( $this->cfg['rmfeaturedimg'] && !empty($this->current_item['featured_image']) ){ // removes featured from content
			$this->current_item['content'] = $this->strip_Image_by_src($this->current_item['featured_image'], $this->current_item['content']);
		}
		
		if( $this->cfg['nonstatic'] ) { $this->current_item['images'] = NoNStatic :: img1s($this->current_item,$this->campaign,$item ); }

		// Uploads and changes img sources in content
		$this->current_item = $this->Item_images( $this->current_item, $this->campaign, $feed, $item );

		$this->current_item = apply_filters('wpematico_item_filters_pos_img', $this->current_item, $this->campaign );
		
		//********** Do parses contents and titles
		$this->current_item = $this->Item_parsers($this->current_item,$this->campaign,$feed,$item,$realcount, $feedurl );
		if($this->current_item == -1 ) return -1;

		// Primero proceso las categorias si las hay y las nuevas las agrego al final del array
		$this->current_item['categories'] = (array)$this->campaign['campaign_categories']; 
		if ($this->campaign['campaign_autocats']) {
			if ($autocats = $item->get_categories()) {
				trigger_error(__('Assigning Auto Categories.', 'wpematico' ) ,E_USER_NOTICE);
				foreach($autocats as $id => $catego) {
					$catname = $catego->term;
					if(!empty($catname)) {
						//$this->current_item['categories'][] = wp_create_category($catname);  //Si ya existe devuelve el ID existente  // wp_insert_category(array('cat_name' => $catname));  //
						$term = term_exists($catname, 'category');
						if ($term !== 0 && $term !== null) {  // ya existe
							trigger_error(__('Category exist: ', 'wpematico' ) . $catname ,E_USER_NOTICE);
						}else{	//si no existe la creo
							trigger_error(__('Adding Category: ', 'wpematico' ) . $catname ,E_USER_NOTICE);
							$arg = array('description' => __("Auto Added by WPeMatico", 'wpematico' ), 'parent' => "0");
							$term = wp_insert_term($catname, "category", $arg);
						}
						$this->current_item['categories'][] = $term['term_id'];
					}					
				}
			}
		}

		$this->current_item['posttype'] = $this->campaign['campaign_posttype'];
		$this->current_item['allowpings'] = $this->campaign['campaign_allowpings'];
		$this->current_item['commentstatus'] = $this->campaign['campaign_commentstatus'];
		$this->current_item['customposttype'] = $this->campaign['campaign_customposttype'];
		$this->current_item['campaign_post_format'] = $this->campaign['campaign_post_format'];

		//********** Do filters
		$this->current_item = $this->Item_filters($this->current_item,$this->campaign,$feed,$item );

		if( $this->cfg['nonstatic'] ) { $this->current_item = NoNStatic :: metaf($this->current_item, $this->campaign, $feed, $item ); }
		
		if( $this->cfg['nonstatic'] && !empty($this->current_item['tags']) ) $this->current_item['campaign_tags']=$this->current_item['tags'];
		
		// Meta
		if( isset($this->cfg['disableccf']) && $this->cfg['disableccf'] ) {
			 $this->current_item['meta'] = array();
		}else{
		   $arraycf = array(
			   'wpe_campaignid' => $this->campaign_id, 
			   'wpe_feed' => $feed->feed_url,
			   'wpe_sourcepermalink' => $this->current_item['permalink'],
		   ); 
		   $this->current_item['meta'] = (isset($this->current_item['meta']) && !empty($this->current_item['meta']) ) ? array_merge($this->current_item['meta'], $arraycf) :  $arraycf ;
		   $this->current_item['meta'] = apply_filters('wpem_meta_data', $this->current_item['meta'] );
		}
		
		// Create post
		$title = $this->current_item['title'];
		$content= $this->current_item['content'];
		$timestamp = $this->current_item['date'];
		$category = $this->current_item['categories'];
		$status = $this->current_item['posttype'];
		$authorid = $this->current_item['author'];
		$allowpings = $this->current_item['allowpings'];
		$comment_status = (isset($this->current_item['commentstatus']) && !empty($this->current_item['commentstatus']) ) ? $this->current_item['commentstatus'] : 'open';
		$meta = $this->current_item['meta'];
		$post_type = (isset($this->current_item['customposttype']) && !empty($this->current_item['customposttype']) ) ? $this->current_item['customposttype'] : 'post';
		$images = $this->current_item['images'];
		$campaign_tags = $this->current_item['campaign_tags'];
		$post_format = $this->current_item['campaign_post_format'];
		
		$date = ($timestamp) ? gmdate('Y-m-d H:i:s', $timestamp + (get_option('gmt_offset') * 3600)) : null;
		
		if($this->cfg['woutfilter'] && $this->campaign['campaign_woutfilter'] ) {
			$truecontent = $content;
			$content = '';
		}

		$args = array(
			'post_title' 	          => apply_filters('wpem_parse_title', $title),
			'post_content'  	      => apply_filters('wpem_parse_content', $content),
			'post_content_filtered'   => apply_filters('wpem_parse_content_filtered', $content),
			'post_status' 	          => apply_filters('wpem_parse_status', $status),
			'post_type' 	          => apply_filters('wpem_parse_post_type', $post_type),
			'post_author'             => apply_filters('wpem_parse_authorid', $authorid),
			'post_date'               => apply_filters('wpem_parse_date', $date),
			'comment_status'          => apply_filters('wpem_parse_comment_status', $comment_status),
			'ping_status'             => ($allowpings) ? "open" : "closed"
		);
		if(has_filter('wpematico_pre_insert_post')) $args =  apply_filters('wpematico_pre_insert_post', $args, $this->campaign);

		if( apply_filters('wpematico_allow_insertpost', true, $this, $args ) ) {
			remove_filter('content_save_pre', 'wp_filter_post_kses');
//			remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
			$post_id = wp_insert_post( $args );
			add_filter('content_save_pre', 'wp_filter_post_kses');
//			add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

			if(!empty($category)){ //solo muestra los tags si los tiene definidos
				$aaa = wp_set_post_terms( $post_id, $category, 'category');
				if(!empty($aaa)) trigger_error(__("Categories added: ", 'wpematico' ).implode(", ",$aaa) ,E_USER_NOTICE);
			}
			if(!empty($campaign_tags)){ //solo muestra los tags si los tiene definidos
				$aaa = wp_set_post_terms( $post_id, $campaign_tags);
				if(!empty($aaa)) trigger_error(__("Tags added: ", 'wpematico' ).implode(", ",$campaign_tags),E_USER_NOTICE);
			}else if(has_action('wpematico_chinese_tags')) do_action('wpematico_chinese_tags', $post_id, $content, $this->campaign );

			if(!empty($post_format)){ //inserto post format
				//$aaa = wp_set_post_terms( $post_id, $category, 'post_format');
				$aaa = set_post_format( $post_id , $post_format); 
				if(!empty($aaa)) trigger_error(__("Post format added: ", 'wpematico' ).$post_format,E_USER_NOTICE);
			}

			if($this->cfg['woutfilter'] && $this->campaign['campaign_woutfilter'] ) {
				global $wpdb, $wp_locale, $current_blog;
				$table_name = $wpdb->prefix . "posts";  
				$blog_id 	= @$current_blog->blog_id;
				$content = $truecontent;
				trigger_error(__('** Adding unfiltered content **', 'wpematico' ),E_USER_NOTICE);
				$wpdb->update( $table_name, array( 'post_content' => $content, 'post_content_filtered' => $content ), array( 'ID' => $post_id )	);
			}
			// insert PostMeta
			foreach($meta as $key => $value){
				add_post_meta($post_id, $key, $value, true);
			}

			if(has_action('wpematico_inserted_post')) do_action('wpematico_inserted_post', $post_id, $this->campaign, $item );

			// Attaching images uploaded to created post in media library 
			// Featured Image
			if(!empty($this->current_item['nofeatimg'])) {
				trigger_error('<strong>'.__('Skip Featured Image.', 'wpematico' ).'</strong>',E_USER_NOTICE);
			}else if( !empty($this->current_item['featured_image']) ) {
				trigger_error(__('Featuring Image Into Post.', 'wpematico' ),E_USER_NOTICE);
				if($this->current_item['images'][0] != $this->current_item['featured_image']){
					$itemUrl = $this->current_item['permalink'];
					$imagen_src = $this->current_item['featured_image'];
					$imagen_src_real = $this->getRelativeUrl($itemUrl, $imagen_src);						
					$imagen_src_real = apply_filters('wpematico_img_src_url', $imagen_src_real );
					$allowed = (isset($this->cfg['allowed']) && !empty($this->cfg['allowed']) ) ? $this->cfg['allowed'] : 'jpg,gif,png,tif,bmp,jpeg' ;
					$allowed = apply_filters('wpematico_allowext', $allowed );
					//Fetch and Store the Image	
					///////////////***************************************************************************************////////////////////////
					$newimgname = apply_filters('wpematico_newimgname', sanitize_file_name(urlencode(basename($imagen_src_real))), $this->current_item, $this->campaign, $item );  // new name here
					// Primero intento con mi funcion mas rapida
					$upload_dir = wp_upload_dir();
					$imagen_dst = trailingslashit($upload_dir['path']). $newimgname; 
					$imagen_dst_url = trailingslashit($upload_dir['url']). $newimgname;
					$img_new_url = "";
					if(in_array(str_replace('.','',strrchr( strtolower($imagen_dst), '.')), explode(',', $allowed))) {   // -------- Controlo extensiones permitidas
						trigger_error('Uploading media='.$imagen_src.' <b>to</b> imagen_dst='.$imagen_dst.'',E_USER_NOTICE);
						$newfile = ($this->cfg['customupload']) ? $this->guarda_imagen($imagen_src_real, $imagen_dst) : false;
						if($newfile) { //subió
							trigger_error('Uploaded media='.$newfile,E_USER_NOTICE);
							$imagen_dst = $newfile; 
							$imagen_dst_url = trailingslashit($upload_dir['url']). basename($newfile);
							$img_new_url = $imagen_dst_url;
						} else { // falló -> intento con otros
							$bits = WPeMatico::wpematico_get_contents($imagen_src_real);
							$mirror = wp_upload_bits( $newimgname, NULL, $bits);
							if(!$mirror['error']) {
								trigger_error($mirror['url'],E_USER_NOTICE);
								$img_new_url = $mirror['url'];
							}
						}
					}
				}else{
					$img_new_url=$this->current_item['featured_image'];
				}
				if(!empty($img_new_url)) { 
					$this->current_item['featured_image'] = $img_new_url;
					array_shift($this->current_item['images']);  //quito el 1er elemento para que no lo suba de nuevo abajo
					$attachid = $this->insertfileasattach( $this->current_item['featured_image'] , $post_id);
					set_post_thumbnail( $post_id, $attachid );
					//add_post_meta($post_id, '_thumbnail_id', $attachid);
				}else{
					trigger_error( __('Upload featured image failed:', 'wpematico' ).$imagen_dst,E_USER_WARNING);
				}
			}
			// Attach files in post content previously uploaded
			if(!$this->campaign['campaign_cancel_imgcache']) {
				if(($this->cfg['imgcache'] || $this->campaign['campaign_imgcache']) && ($this->cfg['imgattach'])) {
					if(is_array($this->current_item['images'])) {
						if(sizeof($this->current_item['images'])) { // Si hay alguna imagen 
							trigger_error(__('Attaching images', 'wpematico' ).": ".sizeof($this->current_item['images']),E_USER_NOTICE);
							foreach($this->current_item['images'] as $imagen_src) {
								$attachid = $this->insertfileasattach($imagen_src,$post_id);
							}
						}
					}
				}			
			}			

			 // If pingback/trackbacks
			if($this->campaign['campaign_allowpings']) {
				trigger_error(__('Processing item pingbacks', 'wpematico' ),E_USER_NOTICE);
				require_once(ABSPATH . WPINC . '/comment.php');
				pingback($this->current_item['content'], $post_id);      
			}

		} // wpematico_allow_insertpost
		
	}
  	

	
	private function fetch_end() {
		$this->campaign['lastrun'] 		  = $this->campaign['starttime'];
		$this->campaign['lastruntime'] 	  = current_time('timestamp') - $this->campaign['starttime'];
		$this->campaign['starttime'] 	  = '';
		$this->campaign['postscount'] 	 += $this->fetched_posts; // Suma los posts procesados 
		$this->campaign['lastpostscount'] = $this->fetched_posts; //  posts procesados esta vez

/*		foreach($this->campaign['campaign_feeds'] as $feed) {    // Grabo el ultimo hash de cada feed
			@$this->campaign[$feed]['lasthash'] = $this->lasthash[$feed]; // paraa chequear duplicados por el hash del permalink original
		}
*/		
		$this->campaign = apply_filters('Wpematico_end_fetching', $this->campaign, $this->fetched_posts );
		//if($this->cfg['nonstatic']){$this->campaign=NoNStatic::ending($this->campaign,$this->fetched_posts);}

		WPeMatico :: update_campaign($this->campaign_id, $this->campaign);  //Save Campaign new data

		trigger_error(sprintf(__('Campaign fetched in %1s sec.', 'wpematico' ),$this->campaign['lastruntime']),E_USER_NOTICE);
	}

	public function __destruct() {
		global $campaign_log_message, $joberrors;
		//Send mail with log
		$sendmail=false;
		if ($joberrors>0 and $this->campaign['mailerroronly'] and !empty($this->campaign['mailaddresslog']))
			$sendmail=true;
		if (!$this->campaign['mailerroronly'] and !empty($this->campaign['mailaddresslog']))
			$sendmail=true;
		if ($sendmail) {	
			switch($this->cfg['mailmethod']) {
			case 'SMTP':
				do_action( 'wpematico_smtp_email');
				break;
			default:
				$headers[] = 'From: '.$this->cfg['mailsndname'].' <'.$this->cfg['mailsndemail'].'>';
				//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
				//$headers[] = 'Cc: iluvwp@wordpress.org'; // note you can just use a simple email address
				break;
			}
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			//add_filter('wp_mail_content_type','wpe_change_content_type'); //function wpe_change_content_type(){ return 'text/html'; } 
			
			$to_mail = $this->campaign['mailaddresslog'];
					
			$title = get_the_title($this->campaign_id);
			$subject = __('WPeMatico Log ', 'wpematico' ).' '.current_time('Y-m-d H:i').': '.$title;
			
			$mailbody = "WPeMatico Log"."\n";
			$mailbody .= __("Campaign Name:", 'wpematico' )." ".$title."\n";
			if (!empty($joberrors))
				$mailbody.=__("Errors:", 'wpematico' )." ".$joberrors."\n";
			if (!empty($jobwarnings))
				$mailbody.=__("Warnings:", 'wpematico' )." ".$jobwarnings."\n";

			$mailbody.="\n".$campaign_log_message;
			$mailbody.= "\n\n\n<hr>";
			$mailbody.= __("WPeMatico by <a href='https://etruel.com'>etruel</a>", 'wpematico' ). "\n";;
			
			wp_mail($to_mail, $subject, $mailbody,$headers,'');
			
		}
		
		// Save last log as meta field in campaign, replace if exist
		add_post_meta( $this->campaign_id, 'last_campaign_log', $campaign_log_message, true )  or
          update_post_meta( $this->campaign_id, 'last_campaign_log', $campaign_log_message );
		  
		$Suss = sprintf(__('Campaign fetched in %1s sec.', 'wpematico' ),$this->campaign['lastruntime']) . '  ' . sprintf(__('Processed Posts: %1s', 'wpematico' ), $this->fetched_posts);
		$message = '<p>'. $Suss.'  <a href="JavaScript:void(0);" style="font-weight: bold; text-decoration:none; display:inline;" onclick="jQuery(\'#log_message_'.$this->campaign_id.'\').fadeToggle();">' . __('Show detailed Log', 'wpematico' ) . '.</a></p>';
		$campaign_log_message = $message .'<div id="log_message_'.$this->campaign_id.'" style="display:none;" class="error fade">'.$campaign_log_message.'</div><span id="ret_lastruntime" style="display:none;">'.$this->campaign["lastruntime"].'</span><span id="ret_lastposts" style="display:none;">'.$this->fetched_posts.'</span>';

		return;
	}
}

//function wpe_change_content_type(){ return 'text/html'; }
function wpematico_init_set($index, $value, $error_only_fail = false) {
	//$oldvalue = ini_get($index);
	$oldvalue = @ini_set($index, $value); //@return string the old value on success, <b>FALSE</b> on failure. 
	if ($error_only_fail) {
		if ($oldvalue === false) {
			trigger_error(sprintf(__('Trying to set %1$s = %2$s: <strong>%3$s</strong> - Old value:%4$s.', 'wpematico' ), $index, $value, (($oldvalue === FALSE) ? __('Failed', 'wpematico' ):__('Success', 'wpematico' )), $oldvalue),(($oldvalue === FALSE)?E_USER_WARNING:E_USER_NOTICE));
		}
	} else {
		trigger_error(sprintf(__('Trying to set %1$s = %2$s: <strong>%3$s</strong> - Old value:%4$s.', 'wpematico' ), $index, $value, (($oldvalue === FALSE)?__('Failed', 'wpematico' ):__('Success', 'wpematico' )), $oldvalue),(($oldvalue === FALSE)?E_USER_WARNING:E_USER_NOTICE));
	}
	
	return $oldvalue;
}
//function for PHP error handling
function wpematico_joberrorhandler($errno, $errstr, $errfile, $errline) {
	global $campaign_log_message, $jobwarnings, $joberrors;
    
	//genrate timestamp
	if (!version_compare(phpversion(), '6.9.0', '>')) { // PHP Version < 5.7 dirname 2nd 
		if (!function_exists('memory_get_usage')) { // test if memory functions compiled in
			$timestamp="<span style=\"background-color:c3c3c3;\" title=\"[Line: ".$errline."|File: ".trailingslashit(dirname($errfile)).basename($errfile)."\">".date_i18n('Y-m-d H:i.s').":</span> ";
		} else  {
			$timestamp="<span style=\"background-color:c3c3c3;\" title=\"[Line: ".$errline."|File: ".trailingslashit(dirname($errfile)).basename($errfile)."|Mem: ". WPeMatico :: formatBytes(@memory_get_usage(true))."|Mem Max: ". WPeMatico :: formatBytes( @memory_get_peak_usage(true))."|Mem Limit: ".ini_get('memory_limit')."]\">".date_i18n('Y-m-d H:i.s').":</span> ";
		}
	}else{
		if (!function_exists('memory_get_usage')) { // test if memory functions compiled in
			$timestamp="<span style=\"background-color:c3c3c3;\" title=\"[Line: ".$errline."|File: ".trailingslashit(dirname($errfile,2)).basename($errfile)."\">".date_i18n('Y-m-d H:i.s').":</span> ";
		} else  {
			$timestamp="<span style=\"background-color:c3c3c3;\" title=\"[Line: ".$errline."|File: ".trailingslashit(dirname($errfile,2)).basename($errfile)."|Mem: ". WPeMatico :: formatBytes(@memory_get_usage(true))."|Mem Max: ". WPeMatico :: formatBytes( @memory_get_peak_usage(true))."|Mem Limit: ".ini_get('memory_limit')."]\">".date_i18n('Y-m-d H:i.s').":</span> ";
		}
	}

	switch ($errno) {
    case E_NOTICE:
	case E_USER_NOTICE:
		$massage=$timestamp."<span>".$errstr."</span>";
        break;
    case E_WARNING:
    case E_USER_WARNING:
		$jobwarnings += 1;
		$massage=$timestamp."<span style=\"background-color:yellow;\">".__('[WARNING]', 'wpematico' )." ".$errstr."</span>";
        break;
	case E_ERROR: 
    case E_USER_ERROR:
		$joberrors += 1;
		$massage=$timestamp."<span style=\"background-color:red;\">".__('[ERROR]', 'wpematico' )." ".$errstr."</span>";
        break;
	case E_DEPRECATED:
	case E_USER_DEPRECATED:
		$massage=$timestamp."<span>".__('[DEPRECATED]', 'wpematico' )." ".$errstr."</span>";
		break;
	case E_STRICT:
		$massage=$timestamp."<span>".__('[STRICT NOTICE]', 'wpematico' )." ".$errstr."</span>";
		break;
	case E_RECOVERABLE_ERROR:
		$massage=$timestamp."<span>".__('[RECOVERABLE ERROR]', 'wpematico' )." ".$errstr."</span>";
		break;
	default:
		$massage=$timestamp."<span>[".$errno."] ".$errstr."</span>";
        break;
    }

	if (!empty($massage)) {

		$campaign_log_message .= $massage."<br />\n";

		if ($errno==E_ERROR or $errno==E_CORE_ERROR or $errno==E_COMPILE_ERROR) {//Die on fatal php errors.
			die("Fatal Error:" . $errno);
		}
		//300 is most webserver time limit. 0= max time! Give script 5 min. more to work.
		@set_time_limit(300); 
		//true for no more php error hadling.
		return true;
	} else {
		return false;
	}

	
}
