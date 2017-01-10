<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *  PLUGINS PAGES ADDONS
 *  Experimental.  Uses worpdress plugins.php file filtered
 */



add_action( 'admin_init', 'redirect_to_wpemaddons',0  );
function redirect_to_wpemaddons() {
	global $pagenow;
	$getpage = (isset($_REQUEST['page']) && !empty($_REQUEST['page']) ) ? $_REQUEST['page'] : '';
	if ($pagenow != 'admin-ajax.php' || $getpage == 'wpemaddons')
	if ($pagenow == 'plugins.php' && ($getpage=='')  ){
		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
//		$plugin_status = isset($_REQUEST['plugin_status']) ? $_REQUEST['plugin_status'] : '';
		$s = isset($_REQUEST['s']) ? urlencode($_REQUEST['s']) : '';

		$location = '';
//		if( !empty($plugin_status) && in_array($plugin_status,array('all','active','inactive','upgrade'))) {
//			$location = add_query_arg('plugin_status',$plugin_status );
//		}
		$actioned = array_multi_key_exists( array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce' ), $_REQUEST, false );
		if( ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'page=wpemaddons') ) && $actioned ) {
			$location = add_query_arg('page','wpemaddons', $location);// $_SERVER['REQUEST_URI'];
			wp_redirect($location);
		}
	}
}

add_action( 'admin_menu', 'admin_menu',99 );
function admin_menu() {
	$page = add_submenu_page(
		'plugins.php',
		__( 'Add-ons', 'wpematico' ),
		__( 'WPeMatico Add-ons', 'wpematico' ),
		'manage_options',
		'wpemaddons',
		'add_admin_plugins_page'
	);
	add_action( 'admin_print_scripts-' . $page, 'WPeAddon_admin_scripts' );
	$page = add_submenu_page(
		'edit.php?post_type=wpematico',
		__( 'Add-ons', 'wpematico' ),
		__( 'Extensions', 'wpematico' ),
		'manage_options',
		'plugins.php?page=wpemaddons'
	);

}
function WPeAddon_admin_scripts() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'plupload-all' );
	wp_enqueue_style( 'plugin-install' );
	wp_enqueue_script( 'plugin-install' );
	add_thickbox();
	wp_enqueue_script( 'wpematico-update', WPeMatico :: $uri . 'app/js/wpematico_updates.js', array( 'jquery', 'inline-edit-post' ), '', true );
}

add_action( 'admin_head', 'WPeAddon_admin_head' );
function WPeAddon_admin_head(){
	global $pagenow, $page_hook;
	if($pagenow=='plugins.php' && $page_hook=='plugins_page_wpemaddons'){
	?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.wrap h1').html('WPeMatico Add-Ons Plugins');
//		$('.update-link').removeClass('update-link');
		var $all= $('.subsubsub .all a').attr('href');
		var $act= $('.subsubsub .active a').attr('href');
		var $ina= $('.subsubsub .inactive a').attr('href');
		var $rec= $('.subsubsub .recently_activated a').attr('href');
		var $upg= $('.subsubsub .upgrade a').attr('href');
		$('.subsubsub .all a').attr('href',$all+'&page=wpemaddons');
		$('.subsubsub .active a').attr('href',$act+'&page=wpemaddons');
		$('.subsubsub .inactive a').attr('href',$ina+'&page=wpemaddons');
		$('.subsubsub .recently_activated a').attr('href',$rec+'&page=wpemaddons');
		$('.subsubsub .upgrade a').attr('href',$upg+'&page=wpemaddons');
	});
	
</script>
	<?php 
	}
}

add_action( 'admin_init', 'activate_desactivate_plugins',0  );
function activate_desactivate_plugins() {
	global $plugins, $status, $wp_list_table;
	if (!defined('WPEM_ADMIN_DIR')) {
		define('WPEM_ADMIN_DIR' , ABSPATH . basename(admin_url()));
	}
	
	if (isset($_REQUEST['checked'])){
		$status ='all'; 
		$page=  (!isset($page) or is_null($page))? 1 : $page;
		$plugins['all']=get_plugins();
		
		require WPEM_ADMIN_DIR . '/plugins.php' ;
		exit;
	}
	
}

function add_admin_plugins_page() {
	if (!defined('WPEM_ADMIN_DIR')) {
		define('WPEM_ADMIN_DIR' , ABSPATH . basename(admin_url()));
	}
	
	
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once WPEM_ADMIN_DIR . '/includes/class-wp-list-table.php';
	}
	
	if ( ! class_exists( 'WP_Plugins_List_Table' ) ) {
		require WPEM_ADMIN_DIR .'/includes/class-wp-plugins-list-table.php';
		
	}
	
	global $plugins, $status, $wp_list_table;
	$status ='all'; 
	$page=  (!isset($page) or is_null($page))? 1 : $page;
	$plugins['all']=get_plugins();

	require WPEM_ADMIN_DIR . '/plugins.php' ;
	exit;

}


add_filter( "manage_plugins_page_wpemaddons_columns", 'wpematico_addons_get_columns' );
function wpematico_addons_get_columns() {
	global $status;

	return array(
		'cb'          => !in_array( $status, array( 'mustuse', 'dropins' ) ) ? '<input type="checkbox" />' : '',
		'name'        => __( 'Add On' ),
		'description' => __( 'Description' ),
		'test' => __( 'Adquire' ),
	);
}

add_action('manage_plugins_custom_column', 'wpematico_addons_custom_columns',10,3);
function wpematico_addons_custom_columns($column_name, $plugin_file, $plugin_data ) {
	// Return if don't have the wpematico word in its name or uri
	if (strpos($plugin_data['Name'], 'WPeMatico ') === false && strpos($plugin_data['PluginURI'], 'wpematico') === false ) {
		return true;
	}
	$caption = ( (isset($plugin_data['installed']) && ($plugin_data['installed']) ) || !isset($plugin_data['Remote'])) ? __('Installed','wpematico') : __('Purchase', 'wpematico');
	if (isset($plugin_data['installed']) && ($plugin_data['installed']) ) {
		if(!isset($plugin_data['Remote'])) {
			$caption = __('Installed','wpematico');
			$title = __('See details and prices on etruel\'s store','wpematico');
			$url   = 'https'.strstr( $plugin_data['PluginURI'], '://');
//		}else{
//			$caption = __('Buy', 'wpematico');
		}
	}else{
		if(!isset($plugin_data['Remote'])) {
			$caption = __('Locally','wpematico');
			$title = __('Go to plugin URI','wpematico');
			$url   = '#'.$plugin_data['Name'];
		}else{
			$caption = __('Purchase', 'wpematico'); //**** 
			$title = __('Go to purchase on the etruel\'s store','wpematico');
			$url   = 'https'.strstr( $plugin_data['buynowURI'], '://');
		}
	}
			
	$target = ( $caption == __('Locally','wpematico' ) ) ? '_self' : '_blank';
	$class = ( $caption == __('Purchase','wpematico' ) ) ? 'button-primary' : '';
	//echo '<a target="_Blank" class="button '.$class.'" title="'.$title.'" href="https'.strstr( $plugin_data['PluginURI'], '://').'">' . $caption . '</a>';
	echo '<a target="'.$target.'" class="button '.$class.'" title="'.$title.'" href="'.$url.'">' . $caption . '</a>';
	return true;
}
	
add_filter( 'all_plugins', 'wpematico_showhide_addons');
function wpematico_showhide_addons($plugins) {
	global $current_screen;
	if ($current_screen->id == 'plugins_page_wpemaddons'){
		$plugins = apply_filters( 'etruel_wpematico_addons_array', read_wpem_addons($plugins), 10, 1 );
		foreach ($plugins as $key => $value) {
			if(strpos($key, 'wpematico_')===FALSE) {		
				unset( $plugins[$key] );
			}else{
				if(isset($plugins[$key]['Remote'])){
					add_filter( "plugin_action_links_{$key}", 'wpematico_addons_row_actions',15,4);					
				}
			}
		}		
	}else{
		foreach ($plugins as $key => $value) {
			if(strpos($key, 'wpematico_')!==FALSE) {
				unset( $plugins[$key] );
			}
		}
	}
//	unset( $plugins['akismet/akismet.php'] );
	
	return $plugins;
}
function wpematico_addons_row_actions($actions, $plugin_file, $plugin_data, $context ){
	$actions = array();
	$actions['buynow'] =  '<a target="_Blank" class="edit" aria-label="' . esc_attr( sprintf( __( 'Go to %s WebPage','wpematico' ), $plugin_data['Name'] ) ) . '" title="' . esc_attr( sprintf( __( 'Open %s WebPage in new window.','wpematico' ), $plugin_data['Name'] ) ) . '" href="'.$plugin_data['PluginURI'].'">' . __('Details','wpematico') . '</a>';
	return $actions;
}

/**
 * Return the array of plugins plus WPeMatico Add-on found on etruel.com website
 * @param type $plugins array of current plugins
 */
Function read_wpem_addons($plugins){
	$cached 	= get_transient( 'etruel_wpematico_addons_data' );
	if ( !is_array( $cached ) ) { // If no cache read source feed
		$addonitems = WPeMatico::fetchFeed('http://etruel.com/downloads/category/wpematico-add-ons/feed/', true, 10);
		$addon = array();
		foreach($addonitems->get_items() as $item) {
			$itemtitle = $item->get_title();
			$versions = $item->get_item_tags('', 'version');
			$version = (is_array($versions)) ? $versions[0]['data'] : '';
			$guid = $item->get_item_tags('', 'guid');
			$guid = (is_array($guid)) ? $guid[0]['data'] : '';
			wp_parse_str($guid, $query ); 
			if(isset($query ) && !empty($query ) ) {
				if(isset($query['p'])){
					$download_id = $query['p'];
				}
			}
			
			$plugindirname = str_replace('-','_', strtolower( sanitize_file_name( $itemtitle )));
			$addon[ $plugindirname ] = Array (
				'Name'		  => $itemtitle,
				'PluginURI'	  => $item->get_permalink(),
				'buynowURI'	  => 'https://etruel.com/checkout?edd_action=add_to_cart&download_id='.$download_id.'&edd_options[price_id]=2',
				'Version'	  => $version,	// $item->get_date('U'),
				'Description' => $item->get_description(),
				'Author'	  => 'etruel', 
				'AuthorURI'   => 'https://etruel.com',
				'TextDomain'  => '',
				'DomainPath'  => '',
				'Network'	  => '',
				'Title'		  => $itemtitle,
				'AuthorName'  => 'etruel', 
				'Remote'	  => true 
			);
		}
		$addons = apply_filters( 'etruel_wpematico_addons_array', array_filter( $addon ) );
		$length = apply_filters( 'etruel_wpematico_addons_transient_length', DAY_IN_SECONDS );
		set_transient( 'etruel_wpematico_addons_data', $addons, $length );
		$cached = $addons;
	}
	//recorre plugins a ver si existe compara por URI y lo borro de cached (addons)
	foreach($plugins as $key => $plugin) {
		foreach($cached as $Akey => $addon) {
			if( ( strstr( $plugin['PluginURI'], '://') == strstr( $addon['PluginURI'], '://') ) 
/*				|| ('WPeMatico PRO' == $plugin['Name'] && 'WPeMatico Professional' == $addon['Name']) 
				|| ('WPeMatico PRO' == $plugin['Name'] && 'WPeMatico PRO' == $addon['Name']) 
*/				) { // Saco bundled
				unset( $cached[ $Akey ] );
				$plugins[$key]['installed'] = true;
			}
		}
	}
	$plugins = array_merge( $plugins, $cached );
	
	return $plugins;	
}