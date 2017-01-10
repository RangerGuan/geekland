<?php
/*
 * Plugin Name: WPeMatico
 * Plugin URI: http://www.wpematico.com
 * Description: Enables administrators to create posts automatically from RSS/Atom feeds with multiples filters.  If you like it, please rate it 5 stars.
 * Version: 1.4.2
 * Author: etruel <esteban@netmdp.com>
 * Author URI: http://www.netmdp.com
 * Text Domain: wpematico
 */
# @charset utf-8
if ( ! function_exists( 'add_filter' ) )
	exit;
if (!class_exists('Main_WPeMatico') ) {
class Main_WPeMatico{
	private static $instance;

	private function setup_constants() {
		if(!defined( 'WPEMATICO_VERSION' ) ) define( 'WPEMATICO_VERSION', '1.4.2' );
		if(!defined( 'WPEMATICO_BASENAME' ) ) define( 'WPEMATICO_BASENAME', plugin_basename( __FILE__ ) );
		if(!defined( 'WPEMATICO_ROOTFILE' ) ) define( 'WPEMATICO_ROOTFILE', __FILE__ );
		if(!defined( 'WPEMATICO_PLUGIN_URL' ) ) define( 'WPEMATICO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		if(!defined( 'WPEMATICO_PLUGIN_DIR' ) ) define( 'WPEMATICO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

	public static function required_php_notice(){
		$class = "error";
		$message = '<b>WPeMatico:</b> '.__('PHP 5.3.0 or higher needed!', 'wpematico' ) . '<br />';
		echo"<div class=\"$class\"> <p>$message</p></div>"; 
	}
	
	
    public static function instance() {
		if (version_compare(phpversion(), '5.3.0', '<')) { // check PHP Version
			add_action( 'admin_notices', array(__CLASS__, 'required_php_notice') );
			return false; 
		}
        if( !self::$instance ) {
            self::$instance = new Main_WPeMatico();
			self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->load_textdomain();
            self::$instance->hooks();
            self::$instance->setup_cron();
        }
        return self::$instance;
    }
	private function includes() {
		if (is_admin()) {
		if(file_exists('app/nonstatic.php'))
			require_once('app/nonstatic.php');
			require_once('app/plugin_functions.php');
			require_once('app/campaigns_list.php');
			require_once("app/campaign_edit_functions.php");
			require_once('app/campaigns_edit.php');
			require_once("app/settings_page.php");
			require_once("app/debug_page.php");
			require_once("app/addons_page.php");
			require_once('app/lib/licenses_handlers.php');	
			require_once("app/lib/welcome.php");
		}
		require_once('app/cron_functions.php');
		require_once('app/wpematico_functions.php');
		require_once('wpematico_class.php');
	}

	private function hooks() {
		add_action( 'init', array( 'WPeMatico', 'init' ) );
		add_action( 'the_permalink', array( 'WPeMatico', 'wpematico_permalink' ) );
		add_filter( 'post_link', array( 'WPeMatico', 'wpematico_permalink' ) );
	
	}
	
	/**
	 * setup_cron 
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function setup_cron() {
		//Disable WP_Cron
		if( isset($cfg['disablewpcron']) && $cfg['disablewpcron'] ){ 
			define('DISABLE_WP_CRON',true);
		}
		if( isset($cfg['dontruncron']) && $cfg['dontruncron'] ) {
			wp_clear_scheduled_hook('wpematico_cron');
		}else{
			add_filter('cron_schedules', 'wpematico_intervals' ); //add cron intervals
			add_action('wpematico_cron', 'wpem_cron_callback' );  //Actions for Cron job
			//test if cron active
			if( !wp_next_scheduled('wpematico_cron') ) {
				wp_schedule_event(time(), 'wpematico_int', 'wpematico_cron');
			}
		}
	}
	/**
	 * Internationalization
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = WPEMATICO_PLUGIN_DIR . '/lang/';
		$lang_dir = apply_filters( 'wpematico_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpematico' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'wpematico', $locale );

		// Setup paths to current locale file
		$mofile_local   = $lang_dir . $mofile;
		$mofile_global  = WP_LANG_DIR . '/wpematico/' . $mofile;

		if( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/wpematico/ folder
			load_textdomain( 'wpematico', $mofile_global );
		} elseif( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/wpematico/languages/ folder
			load_textdomain( 'wpematico', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'wpematico', false, $lang_dir );
		}
	}

}  //class WPeMatico
}
$WPeMatico = Main_WPeMatico::instance();