<?php
// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
class wpematico_licenses_handlers {
	function __construct() {
		add_action('admin_init', array(__CLASS__, 'plugin_updater'), 0 );
		add_action('wpempro_licenses_forms', array(__CLASS__, 'license_page') );
		add_action('init', array(__CLASS__, 'disable_old_updater'), 0 );
		add_filter( 'wpematico_plugins_updater_args', array(__CLASS__, 'activate_new_updater'), 10, 1);
		add_action('admin_print_scripts', array(__CLASS__, 'scripts'));
		add_action('admin_print_styles', array(__CLASS__, 'styles'));
		
		add_action('wp_ajax_wpematico_check_license', array(__CLASS__, 'ajax_check_license'));
		add_action('wp_ajax_wpematico_status_license', array(__CLASS__, 'ajax_change_status_license'));
		
		add_action( 'admin_post_wpematico_save_licenses', array(__CLASS__, 'save_licenses'));

	}
	public static function disable_old_updater() {
		if (defined( 'MAKE_ME_FEED_VER' )) {
			if (version_compare(MAKE_ME_FEED_VER, '1.4', '<=')) {
				remove_action( 'admin_init', 'make_me_feed_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'make_me_feed_license_page' );
				remove_action('admin_init', 'make_me_feed_register_option');
				remove_action('admin_init', 'make_me_feed_activate_license');
				remove_action('admin_init', 'make_me_feed_deactivate_license');
			}
		}
		
		if (defined( 'WPECATS2TAGS_VERSION' )) {
			if (version_compare(WPECATS2TAGS_VERSION, '1.1', '<=')) {
				remove_action( 'admin_init', 'WPeMatico_cats2tags_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_cats2tags_license_page' );
				remove_action('admin_init', 'wpematico_cats2tags_register_option');
				remove_action('admin_init', 'wpematico_cats2tags_activate_license');
				remove_action('admin_init', 'wpematico_cats2tags_deactivate_license');
			}
		}
		
		if (defined( 'WPEMCHINESE_VERSION' )) {
			if (version_compare(WPEMCHINESE_VERSION, '1.2', '<=')) {
				remove_action('admin_init', 'wpematico_chinesetags_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_chinesetags_license_page' );
				remove_action('admin_init', 'wpematico_chinesetags_register_option');
				remove_action('admin_init', 'wpematico_chinesetags_activate_license');
				remove_action('admin_init', 'wpematico_chinesetags_deactivate_license');
			}
		}
		
		
		if (defined( 'WPEMEMAIL_VERSION' )) {
			if (version_compare(WPEMEMAIL_VERSION, '1.3', '<=')) {
				remove_action( 'admin_init', 'wpematico_publish2email_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_publish2email_license_page' );
				remove_action('admin_init', 'wpematico_publish2email_register_option');
				remove_action('admin_init', 'wpematico_publish2email_activate_license');
				remove_action('admin_init', 'wpematico_publish2email_deactivate_license');
			}
		}
		
		if (defined( 'WPEBETTEREXCERPTS_VERSION' )) {
			if (version_compare(WPEBETTEREXCERPTS_VERSION, '1.6', '<=')) {
				remove_action( 'admin_init', 'WPeMatico_better_excerpts_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_better_excerpts_license_page' );
				remove_action('admin_init', 'wpematico_better_excerpts_register_options');
				remove_action('admin_init', 'wpematico_better_excerpts_activate_license');
				remove_action('admin_init', 'wpematico_better_excerpts_deactivate_license');
			}
		}
		
		if (defined( 'WPESMTP_VERSION' )) {
			if (version_compare(WPESMTP_VERSION, '1.2', '<=')) {
				remove_action( 'admin_init', 'WPeMatico_smtp_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_smtp_license_page' );
				remove_action('admin_init', 'wpematico_smtp_register_option');
				remove_action('admin_init', 'wpematico_smtp_activate_license');
				remove_action('admin_init', 'wpematico_smtp_deactivate_license');
			}
		}
		
		if (defined( 'WPEMATICOPRO_VERSION' )) {
			if (version_compare(WPEMATICOPRO_VERSION, '1.3.8.1', '<=')) {
				global $PRO_Licenser;				
				remove_action('admin_init', array($PRO_Licenser, 'admin_plugin_updater'), 0 );
				remove_action('wpempro_licenses_forms', array($PRO_Licenser, 'license_page' ),1 );
				remove_action('admin_init', array($PRO_Licenser, 'register_option') );
				remove_action('admin_init', array($PRO_Licenser, 'wpempro_activate_license') );
				remove_action('admin_init', array($PRO_Licenser, 'wpempro_deactivate_license') );
			}
			
		}
		
		if (defined( 'WPEFULLCONTENT_VERSION' )) {
			if (version_compare(WPEFULLCONTENT_VERSION, '1.3.8', '<=')) {
				remove_action( 'admin_init', 'WPeMatico_fullcontent_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_fullcontent_license_page' );
				remove_action('admin_init', 'wpematico_fullcontent_register_option');
				remove_action('admin_init', 'wpematico_fullcontent_activate_license');
				remove_action('admin_init', 'wpematico_fullcontent_deactivate_license');
			}
			
		}
		
		
		if (defined( 'FACEBOOKFETCHER_VER' )) {
			if (version_compare(FACEBOOKFETCHER_VER, '1.4', '<=')) {
				remove_action( 'admin_init', 'facebook_fetcher_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'facebook_fetcher_license_page' );
				remove_action('admin_init', 'facebookfetcher_register_option');
				remove_action('admin_init', 'facebookfetcher_activate_license');
				remove_action('admin_init', 'facebookfetcher_deactivate_license');
			}
			
		}
		
		if (defined( 'WPEMATICO_THUMBNAIL_SCRATCHER_VER' )) {
			if (version_compare(WPEMATICO_THUMBNAIL_SCRATCHER_VER, '1.3', '<=')) {
				remove_action( 'admin_init', 'thumbnail_scratcher_plugin_updater', 0 );
				remove_action('wpempro_licenses_forms', 'wpematico_thumbnail_scratcher_license_page' );
				remove_action('admin_init', 'thumbnail_scratcher_register_option');
				remove_action('admin_init', 'thumbnail_scratcher_activate_license');
				remove_action('admin_init', 'thumbnail_scratcher_deactivate_license');
			}
		}
		
	}
	public static function activate_new_updater($args) {
		
		if (defined( 'MAKE_ME_FEED_VER' )) {
			if (version_compare(MAKE_ME_FEED_VER, '1.4', '<=')) {
				if (empty($args['make_me_feed'])) {
					$args['make_me_feed'] = array();
					$args['make_me_feed']['api_url'] = 'https://etruel.com';
					$args['make_me_feed']['plugin_file'] = MAKE_ME_FEED_DIR . 'make-me-feed.php';
					$args['make_me_feed']['api_data'] = array(
															'version' 	=> MAKE_ME_FEED_VER, 				// current version number
															'item_name' => MAKE_ME_FEED_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'WPECATS2TAGS_VERSION' )) {
			if (version_compare(WPECATS2TAGS_VERSION, '1.1', '<=')) {
				if (empty($args['cats2tags'])) {
					$args['cats2tags'] = array();
					$args['cats2tags']['api_url'] = 'https://etruel.com';
					$args['cats2tags']['plugin_file'] = WP_PLUGIN_DIR. '/wpematico_cats2tags/wpematico_cats2tags.php';
					$args['cats2tags']['api_data'] = array(
															'version' 	=> WPECATS2TAGS_VERSION, 				// current version number
															'item_name' => WPECATS2TAGS_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'WPEMCHINESE_VERSION' )) {
			if (version_compare(WPEMCHINESE_VERSION, '1.2', '<=')) {
				if (empty($args['chinese_tags'])) {
					$args['chinese_tags'] = array();
					$args['chinese_tags']['api_url'] = 'https://etruel.com';
					$args['chinese_tags']['plugin_file'] = WP_PLUGIN_DIR. '/wpematico_chinese_tags/index.php';
					$args['chinese_tags']['api_data'] = array(
															'version' 	=> WPEMCHINESE_VERSION, 				// current version number
															'item_name' => WPEMCHINESE_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'WPEMEMAIL_VERSION' )) {
			if (version_compare(WPEMEMAIL_VERSION, '1.3', '<=')) {
				if (empty($args['publish2email'])) {
					$args['publish2email'] = array();
					$args['publish2email']['api_url'] = 'https://etruel.com';
					$args['publish2email']['plugin_file'] = WP_PLUGIN_DIR. '/wpematico_better_excerpts/wpem_better_excerpts.php';
					$args['publish2email']['api_data'] = array(
															'version' 	=> WPEMEMAIL_VERSION, 				// current version number
															'item_name' => WPEMEMAIL_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'WPEBETTEREXCERPTS_VERSION' )) {
			if (version_compare(WPEBETTEREXCERPTS_VERSION, '1.6', '<=')) {
				if (empty($args['better_excerpts'])) {
					$args['better_excerpts'] = array();
					$args['better_excerpts']['api_url'] = 'https://etruel.com';
					$args['better_excerpts']['plugin_file'] = WP_PLUGIN_DIR. '/wpematico_better_excerpts/wpem_better_excerpts.php';
					$args['better_excerpts']['api_data'] = array(
															'version' 	=> WPEBETTEREXCERPTS_VERSION, 				// current version number
															'item_name' => WPEBETTEREXCERPTS_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		
		if (defined( 'WPESMTP_VERSION' )) {
			if (version_compare(WPESMTP_VERSION, '1.2', '<=')) {
				if (empty($args['wpematico_smtp'])) {
					$args['wpematico_smtp'] = array();
					$args['wpematico_smtp']['api_url'] = 'https://etruel.com';
					$args['wpematico_smtp']['plugin_file'] = WP_PLUGIN_DIR. '/wpematico_smtp/wpematico_smtp.php';
					$args['wpematico_smtp']['api_data'] = array(
															'version' 	=> WPESMTP_VERSION, 				// current version number
															'item_name' => WPESMTP_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		
		if (defined( 'WPEMATICOPRO_VERSION' )) {
			if (version_compare(WPEMATICOPRO_VERSION, '1.3.8.1', '<=')) {
				if (empty($args['pro_licenser'])) {
					$args['pro_licenser'] = array();
					$args['pro_licenser']['api_url'] = 'https://etruel.com';
					$args['pro_licenser']['plugin_file'] = WPeMaticoPRO::$dir.'wpematicopro.php';
					$args['pro_licenser']['api_data'] = array(
															'version' 	=> WPEMATICOPRO_VERSION, 				// current version number
															'item_name' => PRO_Licenser::NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'WPEFULLCONTENT_VERSION' )) {
			if (version_compare(WPEFULLCONTENT_VERSION, '1.3.8', '<=')) {
				if (empty($args['fullcontent'])) {
					$args['fullcontent'] = array();
					$args['fullcontent']['api_url'] = 'https://etruel.com';
					$args['fullcontent']['plugin_file'] = WPEFULLCONTENT_PATH.'/wpematico_fullcontent.php';
					$args['fullcontent']['api_data'] = array(
															'version' 	=> WPEFULLCONTENT_VERSION, 				// current version number
															'item_name' => WPEFULLCONTENT_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		
		if (defined( 'FACEBOOKFETCHER_VER' )) {
			if (version_compare(FACEBOOKFETCHER_VER, '1.4', '<=')) {
				if (empty($args['facebookfetcher'])) {
					$args['facebookfetcher'] = array();
					$args['facebookfetcher']['api_url'] = 'https://etruel.com';
					$args['facebookfetcher']['plugin_file'] = FACEBOOKFETCHER_ROOT_FILE;
					$args['facebookfetcher']['api_data'] = array(
															'version' 	=> FACEBOOKFETCHER_VER, 				// current version number
															'item_name' => FACEBOOKFETCHER_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		if (defined( 'WPEMATICO_THUMBNAIL_SCRATCHER_VER' )) {
			if (version_compare(WPEMATICO_THUMBNAIL_SCRATCHER_VER, '1.3', '<=')) {
				if (empty($args['thumbnail_scratcher'])) {
					$args['thumbnail_scratcher'] = array();
					$args['thumbnail_scratcher']['api_url'] = 'https://etruel.com';
					$args['thumbnail_scratcher']['plugin_file'] = WPEMATICO_THUMBNAIL_SCRATCHER_DIR.'/wpematico_thumbnail_scratcher.php';
					$args['thumbnail_scratcher']['api_data'] = array(
															'version' 	=> WPEMATICO_THUMBNAIL_SCRATCHER_VER, 				// current version number
															'item_name' => WPEMATICO_THUMBNAIL_SCRATCHER_ITEM_NAME, 	// name of this plugin
															'author' 	=> 'Esteban Truelsegaard'  // author of this plugin
														);
					
				}
			}
		}
		return $args;
	}
	public static function plugin_updater() {
		$plugins_args = array();
		$plugins_args = apply_filters('wpematico_plugins_updater_args', $plugins_args);
		
		if(!class_exists( 'EDD_SL_Plugin_Updater') && !empty($plugins_args)) {
			if(file_exists(WPEMATICO_PLUGIN_DIR . 'app/lib/Plugin_Updater.php')) {
				require_once(WPEMATICO_PLUGIN_DIR . 'app/lib/Plugin_Updater.php');
			} 
		}
		
		
		foreach ($plugins_args as $plugin_name => $args) {
			$license_key = self::get_key($plugin_name);
			$edd_updater = new EDD_SL_Plugin_Updater($args['api_url'], $args['plugin_file'], array(
					'version' 	=> $args['api_data']['version'], 
					'license' 	=> $license_key, 		
					'item_name' => $args['api_data']['item_name'], 	
					'author' 	=> $args['api_data']['author']
				)
			);
			
			if( ! is_multisite() ) {
				//$current = get_site_transient( 'update_plugins' );
				//error_log(var_export($current->response, true));
				add_action( 'after_plugin_row_' . plugin_basename($args['plugin_file']), 'wp_plugin_update_row', 10, 2 );
			}
			
		}
	}
	public static function get_key($plugin_name) {
		$keys = get_option('wpematico_license_keys');
		if ($keys === false) {
			$keys = array();
		}
		if (empty($keys[$plugin_name])) {
			return false;
		}
		return $keys[$plugin_name];
	}
	public static function get_license_status($plugin_name) {
		$keys = get_option('wpematico_license_status');
		if ($keys === false) {
			$keys = array();
		}
		if (empty($keys[$plugin_name])) {
			return false;
		}
		return $keys[$plugin_name];
	}
	public static function set_license_status($plugin_name, $status) {
		$keys = get_option('wpematico_license_status');
		if ($keys === false) {
			$keys = array();
		}
		$keys[$plugin_name] = $status;
		update_option( 'wpematico_license_status', $keys);
	}
	public static function change_status_license($plugin_name, $action) {
		$plugins_args = array();
		$plugins_args = apply_filters('wpematico_plugins_updater_args', $plugins_args);
		if (empty($plugins_args[$plugin_name])) {
			return false;
		}	
		$license = self::get_key($plugin_name);
		
		$api_params = array(
			'edd_action'=> $action,
			'license' 	=> $license,
			'item_name' => urlencode($plugins_args[$plugin_name]['api_data']['item_name']),
			'url'       => home_url()
		);

			
		$response = wp_remote_post( esc_url_raw($plugins_args[$plugin_name]['api_url']), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		if (is_wp_error($response)) {
			return false;
		}
				
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		self::set_license_status($plugin_name, $license_data->license);
		return $license_data;
	}
	public static function ajax_change_status_license() {
		if (!empty($_POST['plugin_name']) && !empty($_POST['status'])) {
			$action_return = self::change_status_license($_POST['plugin_name'], $_POST['status']);
			echo json_encode($action_return);
			wp_die();
			
		}
		
	}
	public static function ajax_check_license() {
		$plugin_name = $_POST['plugin_name'];
		$plugins_args = array();
		$plugins_args = apply_filters('wpematico_plugins_updater_args', $plugins_args);
		if (empty($plugins_args[$plugin_name])) {
			wp_die('error');
		}
		$license = $_POST['license'];
		$args = array(
			'license' 	=> $license,
			'item_name' => urlencode($plugins_args[$plugin_name]['api_data']['item_name']),
			'url'       => home_url(),
			'version' 	=> $plugins_args[$plugin_name]['api_data']['version'],
			'author' 	=> 'Esteban Truelsegaard'	
		);
		$api_url = $plugins_args[$plugin_name]['api_url'];
		$lisense_object = self::check_license($api_url, $args);
		echo json_encode($lisense_object);
		wp_die();
	}
	public static function check_license($api_url, $args) {
		$args['edd_action'] = 'check_license';
		$api_params = $args;
		$response = wp_remote_post( esc_url_raw($api_url), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		if (is_wp_error($response)) {
			return false;
		}
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		return $license_data;
		
	}
	public static function styles() {
		$screen = get_current_screen();
		if ($screen->id == 'wpematico_page_wpematico_settings') {
			wp_enqueue_style('wpematico-settings-licenses', WPEMATICO_PLUGIN_URL.'app/css/licenses_handlers.css');	
		}
		
	}
	public static function scripts() {
		$screen = get_current_screen();
		if ($screen->id == 'wpematico_page_wpematico_settings') {
			wp_enqueue_script( 'wpematico-jquery-settings-licenses', WPEMATICO_PLUGIN_URL. 'app/js/licenses_handlers.js', array( 'jquery' ), WPEMATICO_VERSION, true );
			wp_localize_script('wpematico-jquery-settings-licenses', 'wpematico_license_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'txt_check_license' => __('Check License', WPeMatico::TEXTDOMAIN),
				)
			);
		}
	}
	public static function save_licenses() {
		if (!isset($_POST['wpematico_save_licenses_nonce']) || !wp_verify_nonce($_POST['wpematico_save_licenses_nonce'], 'wpematico_save_licenses')) {
			wp_redirect(admin_url('edit.php?post_type=wpematico&page=wpematico_settings&tab=pro_licenses'));
			exit();
		}
		$keys = (isset($_POST['license_key']) && !empty($_POST['license_key']) ) ? $_POST['license_key'] : array();
		$plugins_args = array();
		$plugins_args = apply_filters('wpematico_plugins_updater_args', $plugins_args);
		update_option( 'wpematico_license_keys', $keys);
		foreach ($keys as $plugin_name => $key) {
			if (empty($plugins_args[$plugin_name])) {
				continue;
			}
			$license = $keys[$plugin_name];
			$args = array(
				'license' 	=> $license,
				'item_name' => urlencode($plugins_args[$plugin_name]['api_data']['item_name']),
				'url'       => home_url(),
				'version' 	=> $plugins_args[$plugin_name]['api_data']['version'],
				'author' 	=> 'Esteban Truelsegaard'	
			);
			$api_url = $plugins_args[$plugin_name]['api_url'];
			$lisense_object = self::check_license($api_url, $args);
			self::set_license_status($plugin_name, $lisense_object->license);
		}
		wp_redirect(admin_url('edit.php?post_type=wpematico&page=wpematico_settings&tab=pro_licenses'));
		exit();
	}
	public static function license_page() {
		
		$plugins_args = array();
		$plugins_args = apply_filters('wpematico_plugins_updater_args', $plugins_args);
		echo '<form method="post" action="'.admin_url('admin-post.php' ).'">
				<input type="hidden" name="action" value="wpematico_save_licenses">
				'.wp_nonce_field('wpematico_save_licenses', 'wpematico_save_licenses_nonce').'
		';
		foreach ($plugins_args as $plugin_name => $args) {
			$license = self::get_key($plugin_name);
			$plugin_title_name = $args['api_data']['item_name'];
			$license_status = self::get_license_status($plugin_name);
			$status_license_html = '';
			if ($license_status != false && $license_status == 'valid') {
				$status_license_html = '<strong>'.__('Status', WPeMatico::TEXTDOMAIN).':</strong> '.__('Valid', WPeMatico::TEXTDOMAIN).'<span class="validcheck"> </span>
										<br/>
										<input id="'.$plugin_name.'_btn_license_deactivate" class="btn_license_deactivate button-secondary" name="'.$plugin_name.'_btn_license_deactivate" type="button" value="'.__('Deactivate License', WPeMatico::TEXTDOMAIN).'" style="vertical-align: middle;"/>';
			} else if ($license_status === 'invalid' || $license_status === 'expired' || $license_status === 'item_name_mismatch' ) {
				$status_license_html = '<strong>'.__('Status', WPeMatico::TEXTDOMAIN).':</strong> '.__('Invalid', WPeMatico::TEXTDOMAIN).'<i class="renewcheck"></i>';
			} elseif($license_status === 'inactive' || $license_status === 'deactivated' || $license_status === 'site_inactive' ) {
				$status_license_html = '<strong>'.__('Status', WPeMatico::TEXTDOMAIN).':</strong> '.__('Inactive', WPeMatico::TEXTDOMAIN).'<i class="warningcheck"></i>
				<br/>
				<input id="'.$plugin_name.'_btn_license_activate" class="btn_license_activate button-secondary" name="'.$plugin_name.'_btn_license_activate" type="button" value="'.__('Activate License', WPeMatico::TEXTDOMAIN).'"/>
				';
			}
			
			
			$html_addons = '
			<div class="postbox ">
			<div class="inside">
			<h2><span class="dashicons-before dashicons-admin-plugins"></span>'.__($plugin_title_name.' License', WPeMatico::TEXTDOMAIN).'</h2>
			<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						'.__('License Key', WPeMatico::TEXTDOMAIN).'
					</th>
					<td>
						<input id="license_key_'.$plugin_name.'" data-plugin="'.$plugin_name.'" class="regular-text inp_license_key" name="license_key['.$plugin_name.']" type="text" value="'.esc_attr( $license ).'" /><br />
						<label class="description" for="license_key_'.$plugin_name.'">'.__('Enter your license key', WPeMatico::TEXTDOMAIN).'</label>
					</td>
				</tr>';
				if ($license != false) {
					$html_div = '';
					$args_check = array(
						'license' 	=> $license,
						'item_name' => urlencode($args['api_data']['item_name']),
						'url'       => home_url(),
						'version' 	=> $args['api_data']['version'],
						'author' 	=> 'Esteban Truelsegaard'	
					);
					$api_url = $args['api_url'];
					$license_data = self::check_license($api_url, $args_check);
					if (is_object($license_data)) {
						
						$currentActivations = $license_data->site_count;
						$activationsLeft = $license_data->activations_left;
						$activationsLimit = $license_data->license_limit;
						$expires = $license_data->expires;
						$expires = substr( $expires, 0, strpos( $expires, " "));
						
						if (!empty($license_data->payment_id) && !empty($license_data->license_limit)) {
							
							$html_div .= '<small>';
							if ($license_status !== 'valid' && $activationsLeft === 0) {
								$accountUrl = 'http://etruel.com/my-account/?action=manage_licenses&payment_id=' . $license_data->payment_id;
								$html_div .= '<a href="'.$accountUrl.'">'.__("No activations left. Click here to manage the sites you've activated licenses on.", WPeMatico::TEXTDOMAIN).'</a>
										<br/>';
								
							}
							if ( strtotime($expires) < strtotime("+2 weeks") ) {
								$renewalUrl = esc_attr($args['api_url']. '/checkout/?edd_license_key=' . $license); 
								$html_div .= '<a href="'.$renewalUrl.'">'.__('Renew your license to continue receiving updates and support.', WPeMatico::TEXTDOMAIN).'</a>
										<br/>';
								
							}
							$html_div .= '<strong>'.__('Activations', WPeMatico::TEXTDOMAIN).':</strong>
										'.$currentActivations.'/'.$activationsLimit.' ('.$activationsLeft.' left)
									<br/>
									<strong>'.__('Expires on', WPeMatico::TEXTDOMAIN).':</strong>
										<code>'.$expires.'</code>
									<br/>
									<strong>'.__('Registered to', WPeMatico::TEXTDOMAIN).':</strong>
										'.$license_data->customer_name.' (<code>'.$license_data->customer_email.'</code>)
								</small>';			
							
						}
					}
								
					$html_addons .= '<tr id="tr_license_status_'.$plugin_name.'" class="tr_license_status" style="vertical-align: middle;">
						<th scope="row" style="vertical-align: middle;">
							'.__('Activated for updates', WPeMatico::TEXTDOMAIN).'
						</th>
						<td id="td_license_status_'.$plugin_name.'">
						<p>'.$status_license_html.'</p>
						<div id="'.$plugin_name.'_ajax_status_license">'.$html_div.'</div>
						</td>
					</tr>';
				} else {
					$html_addons .= '<tr id="tr_license_status_'.$plugin_name.'" class="tr_license_status" style="vertical-align: middle; display:none;">
						<th scope="row" style="vertical-align: middle;">
							'.__('Activated for updates', WPeMatico::TEXTDOMAIN).'
						</th>
						<td id="td_license_status_'.$plugin_name.'">
							
							<input id="'.$plugin_name.'_btn_license_check" class="btn_license_check button-secondary" name="'.$plugin_name.'_btn_license_check" type="button" value="'.__('Check License', WPeMatico::TEXTDOMAIN).'"/>
							<div id="'.$plugin_name.'_ajax_status_license" style="display:none;"></div>
						</td>
					</tr>';
					
				}
				
						
			$html_addons .= '</tbody>
			</table>
			</div>
			</div>
			';
			
			echo $html_addons;
			
		}
		submit_button();
		echo '</form>';
	}
	
}
$wpematico_licenses_handlers = new wpematico_licenses_handlers();
?>