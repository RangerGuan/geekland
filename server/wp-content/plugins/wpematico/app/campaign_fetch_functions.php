<?php
// don't load directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( class_exists( 'wpematico_campaign_fetch_functions' ) ) return;

class wpematico_campaign_fetch_functions {

	function WPeisDuplicated($campaign, $feed, $item) {
		// Post slugs must be unique across all posts.
		global $wpdb, $wp_rewrite;
		$post_ID = 0;
		$cpost_type = $campaign['campaign_customposttype'];
		$dev = false;

		$wfeeds = $wp_rewrite->feeds;
		if ( ! is_array( $wfeeds ) )
			$wfeeds = array();
		$title = $item->get_title();
		$slug = sanitize_title( $title );
		$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $cpost_type, $post_ID ) );
		if ( $post_name_check || in_array( $slug, $wfeeds ) || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $cpost_type ) ) {
			$dev = true;
		}
		if(has_filter('wpematico_duplicates')) $dev =  apply_filters('wpematico_duplicates', $dev, $campaign, $item);
		//  http://wordpress.stackexchange.com/a/72691/65771
		//  https://codex.wordpress.org/Function_Reference/get_page_by_title
	
		$dupmsg = ($dev) ? __('Yes') : __('No');
		trigger_error(sprintf(__('Checking duplicated title \'%1s\'', WPeMatico :: TEXTDOMAIN ),$title).': '. $dupmsg ,E_USER_NOTICE);

		return $dev;
	}

		
	/**
   * Filters for skip item or not
   * @param   $current_item   array    Current post data to be saved
   * @param   $campaign       array    Current campaign data
   * @param   $feed           object    Feed database object
   * @param   $item           object    SimplePie_Item object
   *
   * Return TRUE if skip the item 
   */
   
	function exclude_filters(&$current_item, &$campaign, &$feed, &$item) {  
		$categories = (isset($current_item['categories']) && !empty($current_item['categories']) ) ? $current_item['categories'] : '';
		$post_id = $this->campaign_id;
		$skip = false;

		/* deprecated since 1.3.8.4
		 * if( $this->cfg['nonstatic'] ) { $skip = NoNStatic :: exclfilters($current_item,$campaign,$item ); };  */
		
		$skip =  apply_filters('wpematico_excludes', $skip, $current_item, $campaign, $item );
		
		return $skip;
	} // End exclude filters

  /**
   * Parses an item content
   *
   * @param   $current_item   array    Current post data to be saved
   * @param   $campaign       array    Current campaign data
   * @param   $feed           object    Feed database object
   * @param   $item           object    SimplePie_Item object
   */
	function Item_parsers(&$current_item, &$campaign, &$feed, &$item, $count, $feedurl ) {

		$post_id = $this->campaign_id;
		$current_item = apply_filters('wpematico_item_parsers', $current_item, $campaign, $feed, $item );
		//if( $this->cfg['nonstatic'] ) { $current_item = NoNStatic :: content($current_item,$campaign,$item); }

		if($current_item == -1 ) return -1; //Hack to allow skip the post in this instance

		// strip all HTML tags before apply template 
		if($campaign['campaign_striphtml']) {
			trigger_error(sprintf(__('Deleting html tags: %1s','wpematico'),$item->get_title()),E_USER_NOTICE);
			$current_item['content'] = strip_tags($current_item['content'], apply_filters('wpem_dont_strip_tags', '') );
		}
		// take out links before apply template (if don't strip before html tags
		if ($campaign['campaign_strip_links'] && !$campaign['campaign_striphtml'] ){
			trigger_error(__('Cleaning Links from content.', WPeMatico :: TEXTDOMAIN ),E_USER_NOTICE);
			$current_item['content'] = $this->strip_links((string)$current_item['content']);
		}

		// Template parse           
		if ($campaign['campaign_enable_template']){
			trigger_error('<b>'.__('Parsing Post template.', WPeMatico :: TEXTDOMAIN ).'</b>',E_USER_NOTICE);
			if ( !empty( $current_item['images'][0] ) ) {
				$img_str = "<img class=\"wpe_imgrss\" src=\"" . $current_item['images'][0] . "\">";  //Solo la imagen
			}elseif( !empty( $current_item['featured_image'] ) ) {
				$img_str = "<img class=\"wpe_imgrss\" src=\"" . $current_item['featured_image'] . "\">";  //Solo la imagen
			}else{
				trigger_error(__('Can\'t find the featured image to add to the content.'),E_USER_WARNING);
				$img_str = '<!-- no image -->';
			}

			$vars = array(
				'{title}',
				'{content}',
				'{itemcontent}',
				'{image}',
				'{author}',
				'{authorlink}',
				'{permalink}',
				'{feedurl}',
				'{feedtitle}',
				'{feeddescription}',
				'{feedlogo}',
				'{campaigntitle}',
				'{campaignid}'
			);
			$vars = apply_filters('wpematico_post_template_tags', $vars, $current_item, $campaign, $feed, $item );

			$autor="";
			$autorlink = "";
			if ($author = $item->get_author())	{
				$autor = $author->get_name();
				$autorlink = $author->get_link();
			}		

			$replace = array(
				$current_item['title'],
				$current_item['content'],
				$item->get_description(),
				$img_str,
				$autor,
				$autorlink,
				$this->current_item['permalink'],
				$feed->feed_url,
				$feed->get_title(),
				$feed->get_description(),
				$feed->get_image_url(),
				get_the_title($post_id),
				$post_id
			);
			$replace = apply_filters('wpematico_post_template_replace', $replace, $current_item, $campaign, $feed, $item );

			$current_item['content'] = str_ireplace($vars, $replace, ( $campaign['campaign_template'] ) ? stripslashes( $campaign['campaign_template'] ) : '{content}');
		}

	 // Rewrite
		//$rewrites = $campaign['campaign_rewrites'];
		if (isset($campaign['campaign_rewrites']['origin']))
			for ($i = 0; $i < count($campaign['campaign_rewrites']['origin']); $i++) {
				$on_title = ($campaign['campaign_rewrites']['title'][$i]) ? true: false ;
				$origin = stripslashes($campaign['campaign_rewrites']['origin'][$i]);
				if(isset($campaign['campaign_rewrites']['rewrite'][$i])) {
					$reword = !empty($campaign['campaign_rewrites']['relink'][$i]) 
								  ? '<a href="'. stripslashes($campaign['campaign_rewrites']['relink'][$i]) .'">' . stripslashes($campaign['campaign_rewrites']['rewrite'][$i]) . '</a>' 
								  : stripslashes($campaign['campaign_rewrites']['rewrite'][$i]);
				  
					if($campaign['campaign_rewrites']['regex'][$i]) {
						if($on_title) 
							$current_item['title'] = preg_replace($origin, $reword, $current_item['title']);
						else
							$current_item['content'] = preg_replace($origin, $reword, $current_item['content']);
					}else
						if($on_title) 
							$current_item['title'] = str_ireplace($origin, $reword, $current_item['title']);
						else
							$current_item['content'] = str_ireplace($origin, $reword, $current_item['content']);
				}else if(!empty($campaign['campaign_rewrites']['relink'][$i]))
					$current_item['content'] = str_ireplace($origin, '<a href="'. stripslashes($campaign['campaign_rewrites']['relink'][$i]) .'">' . $origin . '</a>', $current_item['content']);
			}
		// End rewrite

		if ( !$this->cfg['disable_credits']) {$current_item['content'] .= '<p class="wpematico_credit"><small>Powered by <a href="http://www.wpematico.com" target="_blank">WPeMatico</a></small></p>'; }

		$current_item = apply_filters('wpematico_after_item_parsers', $current_item, $campaign, $feed, $item );
		//if($current_item == -1 ) return -1; //Hack to allow skip the post in this instance
		
		return $current_item;
	} // End ParseItemContent
	
	/**
   * Filters an item content
   * @param   $current_item   array    Current post data to be saved
   * @param   $campaign       array    Current campaign data
   * @param   $feed           object    Feed database object
   * @param   $item           object    SimplePie_Item object
   */
	function Item_filters(&$current_item, &$campaign, &$feed, &$item) {  
		$categories = $current_item['categories'];
		$post_id = $this->campaign_id;
		//Proceso Words to Category y si hay las agrego al array
		if ( $this->cfg['enableword2cats']) {
			if( isset($campaign['campaign_wrd2cat']['word']) 
					&& (!empty($campaign['campaign_wrd2cat']['word'][0]) )
					&& (!empty($campaign['campaign_wrd2cat']['w2ccateg'][0]) )
				)
			{	trigger_error(sprintf(__('Processing Words to Category %1s', WPeMatico :: TEXTDOMAIN ), $current_item['title'] ),E_USER_NOTICE);
				for ($i = 0; $i < count($campaign['campaign_wrd2cat']['word']); $i++) {
					$foundit = false;
					$word = stripslashes(htmlspecialchars_decode(@$campaign['campaign_wrd2cat']['word'][$i]));
					if(isset($campaign['campaign_wrd2cat']['w2ccateg'][$i])) {
						$tocat = $campaign['campaign_wrd2cat']['w2ccateg'][$i];
						if($campaign['campaign_wrd2cat']['regex'][$i]) {
							$foundit = (preg_match($word, $current_item['content'])) ? true : false; 
						}else{
							if($campaign['campaign_wrd2cat']['cases'][$i]) 
								$foundit = strpos($current_item['content'], $word);
							else $foundit = stripos($current_item['content'], $word); //insensible a May/min
						}
						if ($foundit !== false ) {
							trigger_error(sprintf(__('Found!: word %1s to Cat_id %2s', WPeMatico :: TEXTDOMAIN ),$word,$tocat),E_USER_NOTICE);
							$current_item['categories'][] = $tocat;
						}else{
							trigger_error(sprintf(__('Not found word %1s', WPeMatico :: TEXTDOMAIN ),$word),E_USER_NOTICE);
						}
					}
				}
			}
		}	// End Words to Category

		//Tags
		if(has_filter('wpematico_pretags')) $current_item['campaign_tags'] =  apply_filters('wpematico_pretags', $current_item, $item, $this->cfg);
		if( $this->cfg['nonstatic'] ) {
			$current_item = NoNStatic :: postags($current_item,$campaign, $item );
			$current_item['campaign_tags'] = $current_item['tags'];
		}else 
			$current_item['campaign_tags'] = explode(',', $campaign['campaign_tags']);
		if(has_filter('wpematico_postags')) $current_item['campaign_tags'] =  apply_filters('wpematico_postags', $current_item, $item, $this->cfg);

		return $current_item;
	} // End item filters
    
    /**
     * Get relative path
     * @param $baseUrl base url
     * @param $relative relative url
     * @return absolute url version of relative url
     */
    static function getRelativeUrl($baseUrl, $relative){
        $schemes = array('http', 'https', 'ftp');
        foreach($schemes as $scheme){
            if(strpos($relative, "{$scheme}://") === 0) //if not relative
                return $relative;
        }
        
        $urlInfo = parse_url($baseUrl);
        
        $basepath = $urlInfo['path'];
        $basepathComponent = explode('/', $basepath);
        $resultPath = $basepathComponent;
        $relativeComponent = explode('/', $relative);
        $last = array_pop($relativeComponent);
        foreach($relativeComponent as $com){
            if($com === ''){
                $resultPath = array('');
            } else if ($com == '.'){
                $cur = array_pop($resultPath);
                if($cur === ''){
                    array_push($resultPath, $cur);
                } else {
                    array_push($resultPath, '');
                }
            } else if ($com == '..'){
                if(count($resultPath) > 1)
                    array_pop($resultPath);
                array_pop($resultPath);
                array_push($resultPath, '');
            } else {
                if(count($resultPath) > 1)
                    array_pop($resultPath);
                array_push($resultPath, $com);
                array_push($resultPath, '');
            }
        }
        array_pop($resultPath);
        array_push($resultPath, $last);
        $resultPathReal = implode('/', $resultPath);
        return $urlInfo['scheme'] . '://' . $urlInfo['host'] . $resultPathReal;
    }
    
    static function getReadUrl($url, $campaign){
		$permalink = htmlspecialchars_decode($url);
		// Colons in URL path segments get encoded by SimplePie, yet some sites expect them unencoded
		$permalink = str_replace('%3A', ':', $permalink);
		$permalink = apply_filters('wpepro_full_permalink', $permalink);
		
		//if is this same host return 
		$urlInfo = parse_url($permalink);
		if($urlInfo['host'] == $_SERVER['SERVER_NAME']) return $permalink;
		
		//search for redirections
		if(!$campaign['avoid_search_redirection']) {
			$headers = get_headers($permalink);
			foreach($headers as $header){
				$parts = explode(':', $header, 2);
				if(strtolower($parts[0]) == 'location')
					return trim($parts[1]);
			}
        }
        return $permalink;
    }
	
		
 	/**
   * Filters images, upload and replace on text item content
   * @param   $current_item   array    Current post data to be saved
   * @param   $campaign       array    Current campaign data
   * @param   $feed           object    Feed database object
   * @param   $item           object    SimplePie_Item object
   */
	function Item_images(&$current_item, &$campaign, &$feed, &$item) { 
		if( ($this->cfg['imgcache'] || $this->campaign['campaign_imgcache'] ) && !($this->campaign['campaign_cancel_imgcache'])) {
            $itemUrl = $this->current_item['permalink'];
			
			if( sizeof($current_item['images']) ) { // Si hay alguna imagen en el contenido
				trigger_error('<b>'.__('Looking for images in content.', WPeMatico :: TEXTDOMAIN ).'</b>',E_USER_NOTICE);
				//trigger_error(print_r($current_item['images'],true),E_USER_NOTICE);
				$featured = false; 
				$img_new_url = array();
				foreach($current_item['images'] as $imagen_src) {
					if($this->cfg['featuredimg'] && $current_item['featured_image'] == $imagen_src) {
						$featured = true;  
					}
					if($this->campaign['campaign_cancel_imgcache']) {
						if($this->cfg['gralnolinkimg'] || $this->campaign['campaign_nolinkimg']) {
							//trigger_error( __('Deleted media img=', WPeMatico :: TEXTDOMAIN ).$imagen_src ,E_USER_WARNING);
							$current_item['content'] = self::strip_Image_by_src($imagen_src, $current_item['content']);
							// Si no quiere linkar las img al server la borro del contenido
						}
					}else {
					    trigger_error(__('Uploading media...', WPeMatico :: TEXTDOMAIN ).$imagen_src,E_USER_NOTICE);
						$imagen_src_real = $this->getRelativeUrl($itemUrl, $imagen_src);						
						$imagen_src_real = apply_filters('wpematico_img_src_url', $imagen_src_real ); // original source
						$allowed = (isset($this->cfg['allowed']) && !empty($this->cfg['allowed']) ) ? $this->cfg['allowed'] : 'jpg,gif,png,tif,bmp,jpeg' ;
						$allowed = apply_filters('wpematico_allowext', $allowed );
						//Fetch and Store the Image	
						///////////////***************************************************************************************////////////////////////
						$newimgname = apply_filters('wpematico_newimgname', sanitize_file_name(urlencode(basename($imagen_src_real))), $current_item, $campaign, $item  );  // new name here
						// Primero intento con mi funcion mas rapida
						$upload_dir = wp_upload_dir();
						$imagen_dst = trailingslashit($upload_dir['path']). $newimgname; 
						$imagen_dst_url = trailingslashit($upload_dir['url']). $newimgname;
						if(in_array(str_replace('.','',strrchr( strtolower($imagen_dst), '.')), explode(',', $allowed))) {   // -------- Controlo extensiones permitidas
							trigger_error('Uploading media='.$imagen_src.' <b>to</b> imagen_dst='.$imagen_dst.'',E_USER_NOTICE);
							$newfile = ($this->cfg['customupload']) ? $this->guarda_imagen($imagen_src_real, $imagen_dst) : false;
							if($newfile) { //subió
								trigger_error('Uploaded media='.$newfile,E_USER_NOTICE);
								$imagen_dst = $newfile; 
								$imagen_dst_url = trailingslashit($upload_dir['url']). basename($newfile);
								$current_item['content'] = str_replace($imagen_src, $imagen_dst_url, $current_item['content']);
								$img_new_url[] = $imagen_dst_url;
							} else { // falló -> intento con otros
								$bits = WPeMatico::wpematico_get_contents($imagen_src_real);
								$mirror = wp_upload_bits( $newimgname, NULL, $bits);
								if(!$mirror['error']) {
									trigger_error($mirror['url'],E_USER_NOTICE);
									$current_item['content'] = str_replace($imagen_src, $mirror['url'], $current_item['content']);
									$img_new_url[] = $mirror['url'];
								} else {  
									trigger_error('wp_upload_bits error:'.print_r($mirror,true).'.',E_USER_WARNING);
									// Si no quiere linkar las img al server borro el link de la imagen
									trigger_error( __('Upload file failed:', WPeMatico :: TEXTDOMAIN ).$imagen_dst,E_USER_WARNING);
									if($this->cfg['gralnolinkimg'] || $this->campaign['campaign_nolinkimg']) {
									//	trigger_error( __('Deleted media img.', WPeMatico :: TEXTDOMAIN ),E_USER_WARNING);
										$current_item['content'] = self::strip_Image_by_src($imagen_src, $current_item['content']);
									}
								}
							}
						}else {
							trigger_error( __('Extension not allowed: ', WPeMatico :: TEXTDOMAIN ). urldecode($imagen_dst_url),E_USER_WARNING);
							if($this->cfg['gralnolinkimg'] || $this->campaign['campaign_nolinkimg']) { // Si no quiere linkar las img al server borro el link de la imagen
								trigger_error( __('Stripped src.', WPeMatico :: TEXTDOMAIN ),E_USER_WARNING);
								$current_item['content'] = self::strip_Image_by_src($imagen_src, $current_item['content']);
							}
						}
					}
				}
				$current_item['images'] = (array)$img_new_url;
				if($featured) $current_item['featured_image'] = $current_item['images'][0]; //change to new url
			}  // // Si hay alguna imagen en el contenido
		}else {
			trigger_error('<b>'.__('Looking for remote images in content. No changes.', WPeMatico :: TEXTDOMAIN ).'</b>',E_USER_NOTICE);
			$current_item['images']=array();
		}
		return $current_item;		
	}  // item images


	function guarda_imagen ($url_origen,$new_file){ 
		$ch = curl_init ($url_origen); 
		if(!$ch) return false;
		$dest_file = apply_filters('wpematico_overwrite_file', $new_file);
		if( $dest_file===FALSE ) return $new_file;  // Don't upload it and return the name like it was uploaded
		$new_file = $dest_file;  
		$i = 1;
		while (file_exists( $new_file )) {
			$file_extension  = strrchr($new_file, '.');    //Will return .JPEG   substr($url_origen, strlen($url_origen)-4, strlen($url_origen));
			if($i==1){
				$file_name = substr($new_file, 0, strlen($new_file)-strlen($file_extension) );
				$new_file = $file_name."-$i".$file_extension;
			}else{
				$file_name = substr( $new_file, 0, strlen($new_file)-strlen($file_extension)-strlen("-$i") );
				$new_file = $file_name."-$i".$file_extension;
			}
			$i++;
		}
		$fs_archivo = fopen ($new_file, "w"); 
		//curl_setopt ($ch, CURLOPT_URL, $url_origen);
		curl_setopt ($ch, CURLOPT_FILE, $fs_archivo); 
		curl_setopt ($ch, CURLOPT_HEADER, 0); 
		curl_exec ($ch); 
		
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch); 
		fclose ($fs_archivo); 

		if(!($httpcode>=200 && $httpcode<300)) unlink($new_file);
		return ($httpcode>=200 && $httpcode<300) ? $new_file : false;
	} 
	

	/*** Adjunta un archivo ya subido al postid dado  */
 	function insertfileasattach($filename,$postid) {
  		$wp_filetype = wp_check_filetype(basename($filename), null );
		$wp_upload_dir = wp_upload_dir();
		$relfilename = $wp_upload_dir['path'] . '/' . basename( $filename );
		$guid = $wp_upload_dir['url'] . '/' . basename( $filename );
		$attachment = array(
		  'guid' => $guid,
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		  'post_content' => '',
		  'post_status' => 'inherit'
		);
		trigger_error(__('Attaching file:').$filename,E_USER_NOTICE);
		$attach_id = wp_insert_attachment( $attachment,  $relfilename, $postid );
		if (!$attach_id)
			trigger_error(__('Sorry, your attach could not be inserted. Something wrong happened.').print_r($filename,true),E_USER_WARNING);
		// must include the image.php file for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $relfilename );
		wp_update_attachment_metadata( $attach_id,  $attach_data );
		
		return $attach_id;
	}

/*	static function Item_parseimg(&$current_item, &$campaign, &$feed, &$item) {
		if ( stripos($current_item['content'], "[[[wpe1stimg]]]") !== FALSE ) {  // en el content
			if (isset( $current_item['images'][0] )) {
				$imgenc = $current_item['images'][0];
				$imgstr = "<img class=\"wpe_imgrss\" src=\"" . $imgenc . "\">";  //Solo la imagen
			}else{
				trigger_error(__('Can\'t find the featured image to add to the content.'),E_USER_NOTICE);
				$imgstr = '<!-- no image -->';
			}
			$current_item['content'] = str_ireplace("[[[wpe1stimg]]]",$imgstr, $current_item['content']);
		}
		return $current_item;
	}
*/	
 	/**
   * Filters images, upload and replace on text item content
   * @param   $current_item   array    Current post data to be saved
   * @param   $campaign       array    Current campaign data
   * @param   $item           object    SimplePie_Item object
   */
	function Get_Item_images($current_item, $campaign, $feed, $item) {        
		if($this->cfg['imgcache'] || $campaign['campaign_imgcache'] || $this->cfg['featuredimg']) {
			$images = $this->parseImages($current_item['content']);
			$current_item['images'] = $images[2];  //lista de url de imagenes
 			
			if( $this->cfg['nonstatic'] ) { $current_item['images'] = NoNStatic :: imgfind($current_item,$campaign,$item ); }
			$current_item['images'] = array_values(array_unique($current_item['images']));

		}
		return $current_item;
	}

	/*** Delete images for its src	*/
	static function strip_Image_by_src($src, $content, $withlink=true){
		trigger_error( sprintf( __("Removing: %s from content." , WPeMatico :: TEXTDOMAIN ),'"'. $src .'"' ) , E_USER_NOTICE);
		if($withlink){
			$imgtag = '|<a(.+?)><img(.+?)src=["\']'.addslashes($src).'["\'](.*?)><\/a>|';
			$current_content = preg_replace( $imgtag, '',  $content );  //for tag img with a
			$content = ( is_null($current_content) ) ? $content : $current_content ;  //for tag img with a
		}		
		$imgtag = '|<img(.+?)src=["\']'.addslashes($src).'["\'](.*?)>|';
		$current_content = preg_replace( $imgtag, '',  $content );  //for tag img without a
		$content = ( is_null($current_content) ) ? $content : $current_content ;  //for tag img with a
		return $content;
	}

	/*** Devuelve todas las imagenes del contenido	*/
	static function parseImages($text){
		preg_match_all('/<img[^>]+>/i',$text, $result);
		$imgstr = implode('', $result[0]);
//		preg_match_all('/<img(.+?)src=["\'](.+?)["\'](.*?)>/', $imgstr , $out);  //for tag img con ' o "
		preg_match_all('/<\s*img[^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/', $imgstr, $out);  // patch to ignore iframes src
		$out[2] = $out[1];
		preg_match_all('/<link rel=\"(.+?)\" type=\"image\/jpg\" href=\"(.+?)\"(.+?)\/>/', $text, $out2); // for rel=enclosure
		array_push($out,$out2);  // sum all items to array 
		return $out;
	}

	function strip_links($text) {
	    $tags = array('a','iframe','script');
	    foreach ($tags as $tag){
	        while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
	            $text = str_replace($found[0],$found[2],$text);
	        }
	    }
	    return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
	}

	static function wpematico_get_yt_rss_tags( $content, $campaign, $feed, $item ) {		
		if( strpos( $feed->feed_url, 'https://www.youtube.com/feeds/videos.xml' ) !== false ) {
			$ytvideoId = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
			$video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $ytvideoId[0]['data'] .'" frameborder="0" allowfullscreen></iframe>';
			$enclosures = $item->get_enclosures();
			$title = apply_filters('wpematico_yt_altimg', $enclosures[0]->title );
			$img = apply_filters('wpematico_yt_thumbnails', $enclosures[0]->thumbnails[0]);
			$description = apply_filters('wpematico_yt_description', $enclosures[0]->description);
			
			$content = "<img src=\"$img\" alt=\"$title\"><br>$video<p>$description</p>";
		}
		return $content;
	}

} // class