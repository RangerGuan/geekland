<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Display the debug info tab
 *
 * @since       1.2.4
 * @return      void
 */
function wpematico_debug_info() {   
	$danger = get_option( 'WPeMatico_danger');
	$danger['wpemdeleoptions']	 = (isset($danger['wpemdeleoptions']) && !empty($danger['wpemdeleoptions']) ) ? $danger['wpemdeleoptions'] : false;
	$danger['wpemdelecampaigns'] = (isset($danger['wpemdelecampaigns']) && !empty($danger['wpemdelecampaigns']) ) ? $danger['wpemdelecampaigns'] : false;
?>
	<p class="text">
		<?php _e('Use this file to get support on '); ?><a href="https://etruel.com/support/" target="_blank" rel="follow">etruel's website</a>.
	</p>
	<p></p>
	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=wpematico&page=wpematico_settings&tab=debug_info' ) ); ?>" method="post" dir="ltr">
		<label><input class="checkbox" value="1" type="checkbox" name="alsophpinfo" /> <?php _e('Include also PHPInfo() if available.', 'wpematico' ); ?></label><br/>
		<label><input class="checkbox" value="1" type="checkbox" checked="checked" name="alsocampaignslogs" /> <?php _e('Include also Last Campaigns Log.', 'wpematico' ); ?></label><br/>
		<input type="hidden" name="wpematico-action" value="download_debug_info" />
		<p class="submit">
			<?php submit_button( 'Download Debug Info File', 'primary', 'wpematico-download-debug-info', false ); ?>
		</p>
		<div style="max-width: 650px;">
		<textarea readonly="readonly" id="debug-info-textarea" name="wpematico-sysinfo" 
				  title="<?php _e('To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).',  'wpematico'); ?>"
				  style="width: 100%;min-height: 370px;"
		><?php 
			echo wpematico_debug_info_get(); 
		?></textarea>
			<?php  wp_nonce_field('wpematico-settings'); ?>
			<label onclick="jQuery('#debug-info-textarea').focus();jQuery('#debug-info-textarea').select()" ><?php _e('SELECT ALL', 'wpematico'); ?></label>
		</div>
		
	</form>
	<h2 style="color: red;">Danger Zone</h2>
	<form action="options.php" method="post" dir="ltr">
		<h3><?php _e('Select actions to Uninstall','wpematico'); ?></h3>
		<label><input class="checkbox" value="1" type="checkbox" <?php checked($danger['wpemdeleoptions'],true);?> name="wpemdeleoptions" /> <?php _e('Delete all Options.', 'wpematico' ); ?></label><br/>
		<label><input class="checkbox" value="1" type="checkbox" <?php checked($danger['wpemdelecampaigns'],true);?> name="wpemdelecampaigns" /> <?php _e('Delete all Campaigns.', 'wpematico' ); ?></label><br/>
		<?php  wp_nonce_field('wpematico-danger'); ?>
		<input type="hidden" name="wpematico-action" value="set_danger_data" />
		<p class="submit">
			<?php submit_button( 'Save Actions to Uninstall.', 'primary', 'wpematico-set-danger-data', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'wpematico_settings_tab_debug_info', 'wpematico_debug_info' );

add_action( 'wpematico_set_danger_data', 'wpematico_save_danger_data' );
function wpematico_save_danger_data() {
	if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] ) {
		if ( get_magic_quotes_gpc() ) {
			$_POST = array_map( 'stripslashes_deep', $_POST );
		}	
		check_admin_referer('wpematico-danger');
		$danger['wpemdeleoptions'] = (isset($_POST['wpemdeleoptions']) && !empty($_POST['wpemdeleoptions']) ) ? $_POST['wpemdeleoptions'] : false;
		$danger['wpemdelecampaigns'] = (isset($_POST['wpemdelecampaigns']) && !empty($_POST['wpemdelecampaigns']) ) ? $_POST['wpemdelecampaigns'] : false;
		if( update_option( 'WPeMatico_danger', $danger ) or add_option( 'WPeMatico_danger', $danger ) ) {
			WPeMatico::add_wp_notice( array('text' => __('Actions to Uninstall saved.',  'wpematico').'<br>'.__('The actions are executed when the plugin is uninstalled.',  'wpematico'), 'below-h2'=>false ) );
		}
		wp_redirect( admin_url( 'edit.php?post_type=wpematico&page=wpematico_settings&tab=debug_info') );
	}
}
/**
 * Get system info
 *
 * @since       1.2.4
 * @access      public
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @return      string $return A string containing the info to output
 */
function wpematico_debug_info_get() {
	global $wpdb;
	$cfg = get_option(WPeMatico :: OPTION_KEY);
	$cfg = apply_filters('wpematico_check_options', $cfg);  

	if( !class_exists( 'Browser' ) )
		require_once dirname( __FILE__) . '/lib/browser.php';  //https://github.com/cbschuld/Browser.php

	$browser = new Browser();

	// Get theme info
	if( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}

	// Try to identify the hosting provider
	$host = wpematico_get_host();

	$return  = '### Begin Debug Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'wpematico_sysinfo_after_site_info', $return );

	// Can we determine the site's host?
	if( $host ) {
		$return .= "\n" . '-- Hosting Provider' . "\n\n";
		$return .= 'Host:                     ' . $host . "\n";

		$return  = apply_filters( 'wpematico_sysinfo_after_host_info', $return );
	}

	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'wpematico_sysinfo_after_user_browser', $return );

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language WPLANG:          ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
	$return .= 'Language Setting:         ' . ( get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : 'Default' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'WPEMATICO/' . WPeMatico::$version,
		'body'          => $request
	);
	
	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";

	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'wpematico_sysinfo_after_wordpress_config', $return );

	// WPeMatico configuration
	$return .= "\n" . '-- WPeMatico Configuration' . "\n\n";
	$return .= 'Version:                  ' . WPeMatico::$version . "\n";

	foreach($cfg as $name => $value): 
		if ( wpematico_option_blacklisted($name)) continue; 
		$value = sanitize_option($name, $value); 
		$return .= $name . ":\t\t" . ((is_array($value))? print_r($value,1): esc_html($value)) . "\n";
	endforeach;

	$return  = apply_filters( 'wpematico_sysinfo_after_wpematico_config', $return );


    // Must-use plugins
    $muplugins = get_mu_plugins();
    if( count( $muplugins > 0 ) ) {
        $return .= "\n" . '-- Must-Use Plugins' . "\n\n";

        foreach( $muplugins as $plugin => $plugin_data ) {
            $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
        }

        $return = apply_filters( 'wpematico_sysinfo_after_wordpress_mu_plugins', $return );
    }

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return  = apply_filters( 'wpematico_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return = apply_filters( 'wpematico_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}

		$return  = apply_filters( 'wpematico_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'wpematico_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	
	$return .= 'Safe Mode:                ' . ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' . "\n" );
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Disabled Functions:       ' . ini_get( 'disable_functions' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	$return  = apply_filters( 'wpematico_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'wpematico_sysinfo_after_php_ext', $return );

	// SimplePie required extensions and such
	$return .= "\n" . '-- SimplePie required Extensions' . "\n\n";
	$php_ok = (function_exists('version_compare') && version_compare(phpversion(), '5.2.0', '>='));
	$pcre_ok = extension_loaded('pcre');
	$curl_ok = function_exists('curl_exec');
	$zlib_ok = extension_loaded('zlib');
	$mbstring_ok = extension_loaded('mbstring');
	$iconv_ok = extension_loaded('iconv');
	if (extension_loaded('xmlreader')) {
		$xml_ok = true;
	}elseif (extension_loaded('xml')) {
		$parser_check = xml_parser_create();
		xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
		xml_parser_free($parser_check);
		$xml_ok = isset($values[0]['value']);
	}else{
		$xml_ok = false;
	}
	$return .= 'PHP 5.2.0 or higher:     ' . ( ($php_ok) ? 'Supported' : 'Not Supported') . "\n";
	$return .= 'XML (php.net/xml):       ' . ( ($xml_ok) ? 'Enabled, and sane' : 'Disabled, or broken' ) . "\n";
	$return .= 'PCRE (php.net/pcre):     ' . ( ($pcre_ok) ? 'Enabled' : 'Disabled' ) . "\n";
	$return .= 'PCRE (php.net/curl):     ' . ( (extension_loaded('curl')) ? 'Enabled' : 'Disabled' ) . "\n";
	$return .= 'Zlib (php.net/zlib):     ' . ( ($zlib_ok) ? 'Enabled' : 'Disabled' ) . "\n";
	$return .= 'php.net/mbstring:        ' . ( ($mbstring_ok) ? 'Enabled' : 'Disabled' ) . "\n";
	$return .= 'iconv (php.net/iconv):   ' . ( ($iconv_ok) ? 'Enabled' : 'Disabled' ) . "\n";
					 
	$return  = apply_filters( 'wpematico_sysinfo_after_simplepie_ext', $return );

	// Session stuff
	$return .= "\n" . '-- Session Configuration' . "\n\n";
	$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	// The rest of this is only relevant is session is enabled
	if( isset( $_SESSION ) ) {
		$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	}

	$return  = apply_filters( 'wpematico_sysinfo_after_session_config', $return );

	// WordPress CONSTANTS filtering users & passwords
	$return .= "\n" . '-- WordPress user Defined Constants' . "\n\n";

	$wp_constants = get_defined_constants(1);
	unset($wp_constants['user']['DB_USER']);
	unset($wp_constants['user']['DB_PASSWORD']);
	unset($wp_constants['user']['AUTH_KEY']);
	unset($wp_constants['user']['SECURE_AUTH_KEY']);
	unset($wp_constants['user']['LOGGED_IN_KEY']);
	unset($wp_constants['user']['NONCE_KEY']);
	unset($wp_constants['user']['AUTH_SALT']);
	unset($wp_constants['user']['SECURE_AUTH_SALT']);
	unset($wp_constants['user']['LOGGED_IN_SALT']);
	unset($wp_constants['user']['NONCE_SALT']);
	unset($wp_constants['user']['COOKIEHASH']);
	unset($wp_constants['user']['USER_COOKIE']);
	unset($wp_constants['user']['PASS_COOKIE']);
	unset($wp_constants['user']['AUTH_COOKIE']);
	unset($wp_constants['user']['SECURE_AUTH_COOKIE']);
	unset($wp_constants['user']['LOGGED_IN_COOKIE']);
	unset($wp_constants['user']['TEST_COOKIE']);
	
	$return .= print_r($wp_constants['user'], 1);

	$return  = apply_filters( 'wpematico_sysinfo_after_get_defined_constants', $return );

	$return .= "\n\n" . '### End Debug Info ###';

	return $return;
}


/**
 * Generates a System Info download file
 *
 * @since       2.0
 * @return      void
 */
function wpematico_debug_info_download() {
	check_admin_referer('wpematico-settings');
	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="wpematico-debug-info.txt"' );
	
	echo wp_strip_all_tags( $_POST['wpematico-sysinfo'] );

	if( !empty($_POST['alsophpinfo']) ) {
		echo "\n\n" . '-- PHPInfo --' . "\n\n";  
		echo 'PHPInfo:                  ' . ( (!strpos(ini_get( 'disable_functions' ),'phpinfo')) ? 'Enabled' : 'Disabled' ) . "\n\n";
		if (!strpos(ini_get( 'disable_functions' ),'phpinfo')) :
			unset( $_REQUEST["wpematico-sysinfo"]);
			unset( $_POST["wpematico-sysinfo"]);
			phpinfo();
		endif;
	}
	if( !empty($_POST['alsocampaignslogs']) ) {
		echo "\n\n" . '-- LAST CAMPAIGNS LOG --' . "<br />\n\n";  
		$args = array(
			'orderby'         => 'ID',
			'order'           => 'ASC',
			'post_type'       => 'wpematico', 
			'numberposts' => -1
		);
		$campaigns = get_posts( $args );
		foreach( $campaigns as $post ):
			echo "<br />\n\n" . '### CAMPAIGN ID Name:     ' . $post->ID .' '.get_the_title($post->ID) . "<br />\n\n";
			echo get_post_meta( $post->ID, 'last_campaign_log', true ); 	
		endforeach; 
	}
	echo "\n\n" . '-- ENDFILE --' . "\n";  
	die();
	
// +++ COMENTADO si lo quiero parseado sin html
//	$return = wp_strip_all_tags( $_POST['wpematico-sysinfo'] );
	
//	if( $_POST['alsophpinfo']==1 ) {
//		$return .= "\n\n" . '-- PHPInfo --' . "\n\n";  
//		$return .= 'PHPInfo:                  ' . ( (!strpos(ini_get( 'disable_functions' ),'phpinfo')) ? 'Enabled' : 'Disabled' ) . "\n\n";
//		if (!strpos(ini_get( 'disable_functions' ),'phpinfo')) :
//			ob_start();
//			phpinfo();
//			$phpinfo = ob_get_contents();
//			ob_end_clean();
//			$phpinfo = str_replace("</td","  </td",$phpinfo);
//			$return .= wp_strip_all_tags($phpinfo);
//			$return .= $phpinfo;
//		endif;
//	}
//	echo $return;
//	die();
	
}
add_action( 'wpematico_download_debug_info', 'wpematico_debug_info_download' );


function wpematico_option_blacklisted($setting) {
	// TODO: add other settings from premium modules
	$blacklisted = array(
		'mailsendmail',
		'mailsecure',
		'mailhost',
		'mailport',
		'mailuser',
		'mailpass',
	);
	return in_array($setting, $blacklisted);
}