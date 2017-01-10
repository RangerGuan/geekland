<?php
/**
 * Welcome Page Class
 * @package     WPEMATICO
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPEMATICO_Welcome Class
 *
 * A general class for About and changelog page.
 *
 * @since 1.4
 */
class WPEMATICO_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.4
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ), 11 );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and changelog pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to WPeMatico', 'wpematico' ),
			__( 'WPeMatico News', 'wpematico' ),
			$this->minimum_capability,
			'wpematico-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'WPeMatico Changelog', 'wpematico' ),
			__( 'WPeMatico Changelog', 'wpematico' ),
			$this->minimum_capability,
			'wpematico-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with WPeMatico', 'wpematico' ),
			__( 'Getting started with WPeMatico', 'wpematico' ),
			$this->minimum_capability,
			'wpematico-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
//		remove_submenu_page( 'index.php', 'wpematico-about' );
		remove_submenu_page( 'index.php', 'wpematico-changelog' );
		remove_submenu_page( 'index.php', 'wpematico-getting-started' );
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.wpematico-about-wrap .wpematico-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
			.wpematico-about-wrap #wpematico-header { margin-bottom: 15px; }
			.wpematico-about-wrap #wpematico-header h1 { margin-bottom: 15px !important; }
			.wpematico-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.wpematico-about-wrap .feature-section { margin-top: 20px; }
			.wpematico-about-wrap .feature-section-content,
			.wpematico-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.wpematico-about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.wpematico-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.wpematico-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
			.wpematico-about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.wpematico-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.wpematico-about-wrap .feature-section-content,
				.wpematico-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.wpematico-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Welcome message
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', WPEMATICO_VERSION );
		?>
		<div id="wpematico-header">
			<img class="wpematico-badge" src="<?php echo WPEMATICO_PLUGIN_URL . '/images/icon-256x256.png'; ?>" alt="<?php _e( 'WPeMatico', 'wpematico' ); ?>" / >
			<h1><?php printf( __( 'Welcome to WPeMatico %s', 'wpematico' ), $display_version ); ?></h1>
			<p class="about-text">
				<?php printf( __( 'Thank you for updating to the latest version! WPeMatico %s is ready to make your autoblogging faster, safer, and better!', 'wpematico' ), $display_version ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'wpematico-about';
		?>
		<h1 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'wpematico-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpematico-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'wpematico' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'wpematico-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpematico-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'wpematico' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'wpematico-changelog' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpematico-changelog' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'wpematico' ); ?>
			</a>
		</h1>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap wpematico-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			<div class="changelog">
				<h3><?php _e( 'Including Campaign Types.', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL.'images/campaigntype.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Youtube Fetcher', 'wpematico' );?></h4>
						<p><?php _e( 'We implemented a new feature that, until now, was not working and that many asked for it: Fetch the feeds of channels or users of YouTube videos!', 'wpematico' );?></p>
						<p><?php _e( 'Is a first step using them <b>Youtube standard feeds</b> without need of the API to make a content with the image outstanding, the video embedded and the description below.', 'wpematico' );?></p>

						<h4><?php _e( 'Introducing Campaign Types', 'wpematico' );?></h4>
						<p><?php _e( 'In addition to the feeds from Youtube, we have incorporated a new feature that will allow a powerful fetching. The types of campaign will improve the plugin for new plug-ins and will allow new sources different of RSS.', 'wpematico' );?></p>

						<p><?php _e('The','wpematico'); ?> <a href="https://etruel.com/downloads/wpematico-facebook-fetcher/" target="_blank">WPeMatico Facebook Fetcher Add-On</a> <?php _e( 'uses this new feature allowing the users to use WPeMatico to get content from Facebook Pages and/or Groups.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'WPeMatico is changing its look.', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'screenshot-2.png'; ?>" class="wpematico-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Tweaks on Campaigns List', 'wpematico' );?></h4>
						<p><?php _e( 'Addition to changing the graphics and banners, the 1.3.6 version shows some important changes in the list of campaigns, in which not only the appearance was improved, also was accompanied by several improvements in the interaction with the user to get an easier campaign management.', 'wpematico' );?></p>

						<h4><?php _e( 'Improved Data Export/Import', 'wpematico' );?></h4>
						<p><?php _e( 'As all WPeMatico campaigns are saved following the Wordpress standards, you can export and import campaigns from the tools menu, just as is done for export/import the blog posts.', 'wpematico' );?></p>
						<p><?php _e('The','wpematico'); ?> <a href="https://etruel.com/downloads/wpematico-professional/" target="_blank">WPeMatico Professional add-on</a> <?php _e( 'brings an extra and very practical option to export and import campaigns individually from the same list of campaigns.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Nice colorized interface allows to manage all the feeds easily.', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'images/feeds.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'This version comes with several important improvements. Among them, you will find the “order by drag and drop” of the feeds to be consulted. This is an important upgrade, because it allows the user to set the order in which the new posts will be added when running a campaign.', 'wpematico' );?></p>

						<h4><?php _e( 'Coloured Titles in Campaign Metaboxes', 'wpematico' );?></h4>
						<p><?php _e( 'Added colours to the background of the titles. This is a very useful feature at the campaign editing stage, that will allow you to find almost immediately, what you are looking for.', 'wpematico' );?></p>

						<h4><?php _e( 'Tips in every campaign field option.', 'wpematico' );?></h4>
						<p><?php _e( 'Beside each field is an icon of information, when mouse over it displays a tip or a help for that field. Hover over again to hide it.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better CRON Setups', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'images/cron.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'WPeMatico version 1.3 allows set the automatic cron as you want.', 'wpematico' );?></p>

						<h4><?php _e( 'Three type of CRON Schedules', 'wpematico' );?></h4>
						<p><?php _e( 'By default WPeMatico uses WP Cron with just one cron job every 5 minutes.', 'wpematico' );?></p>

						<h4><?php _e( 'You can use external third party services.', 'wpematico' );?></h4>
						<p><?php _e( 'Deactivates only WPeMatico functions and uses a PHP file to be called from an external server by console or URL with password access.', 'wpematico' );?></p>

						<h4><?php _e( 'You can use a cron of your server.', 'wpematico' );?></h4>
						<p><?php _e( 'Allows deactivate the entire WP Cron and uses a server/hosting cron.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'wpematico' );?></h3>
				<div class="feature-section three-col">
					<div class="col">
						<h4><?php _e( 'New Add-ons System', 'wpematico' );?></h4>
						<p><?php printf( __( 'Since version 1.3, it was implemented in %s, the online store for purchases and automatic updating of extensions.', 'wpematico' ),'<a href="https://etruel.com" target="_blank">https://etruel.com</a>');?></p>
					</div>
					<div class="col">
						<h4><a href="https://etruel.com/my-account/support/" target="_blank"><?php _e('Support ticket system for free', 'wpematico'); ?></a></h4>
						<p><?php _e( 'Ask for any problem you may have and you\'ll get support for free. If it is necessay we will see into your website to solve your issue.', 'wpematico' );?></p>
					</div>
					<div class="col">
						<h4><a href="https://etruel.com/downloads/premium-support/" target="_blank"><?php _e('Premium Support', 'wpematico'); ?></a></h4>
						<p><?php _e( 'Get access to in-depth setup assistance. We\'ll dig in and do our absolute best to resolve issues for you. Any support that requires code or setup your site will need this service.' ,'wpematico' );?></p>
					</div>
					<div class="clear">
						<div class="col">
							<h4><?php _e( 'Bulk Edit for campaigns.', 'wpematico' );?></h4>
							<p><?php _e( 'A comfortable "Bulk Edit" feature was added for campaigns in list of campaigns. Select the campaigns by clicking the checkboxes and select "Edit" in "Bulk Actions".', 'wpematico' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Excludes Add-ons From WP Plugins', 'wpematico' );?></h4>
							<p><?php _e( 'The extensions plugins were separated from the standard Wordpress plugins page to get a better management of all WPeMatico Addons.', 'wpematico' );?></p>
						</div>
						<div class="col">
							<h4><a href="https://wordpress.org/support/view/plugin-reviews/wpematico?filter=5&rate=5#postform" target="_blank"><?php _e( 'Rate 5 stars on Wordpress', 'wpematico' );?></a><div class="wporg-ratings" title="5 out of 5 stars" style="color:#ffb900;float: right;"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></div></h4>
							<p><?php _e( 'We need your positive rating of 5 stars in WordPress. Your comment will be published on the bottom of the website and besides it will help making the plugin better.', 'wpematico' );?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'wpematico', 'page' => 'wpematico_settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to WPeMatico Settings', 'wpematico' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpematico-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'wpematico' ); ?></a>  &middot; 
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpemaddons' ), 'plugins.php' ) ) ); ?>"><?php _e( 'Go to WPeMatico Extensions', 'wpematico' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since 2.0.3
	 * @return void
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap wpematico-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'wpematico' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'wpematico', 'page' => 'wpematico-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to WPeMatico Settings', 'wpematico' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function getting_started_screen() {
		?>
		<div class="wrap about-wrap wpematico-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'Autoblogging in the blink of an eye! On complete autopilot WpeMatico gets new content regularly for your site!  WPeMatico is a very easy to use autoblogging plugin. Organized into campaigns, it publishes your posts automatically from the RSS/Atom feeds of your choice.', 'wpematico' ); ?></p>
			<p class="about-description"><?php _e( 'Use the tips below to get started using WPeMatico. You will be up and running in no time!', 'wpematico' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Fill in the Settings', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media" style="max-height: 400px; overflow: hidden;">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'screenshot-5.png'; ?>" class="wpematico-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'RSS', 'wpematico' );?></h4>
						<p><?php _e( 'RSS is a technology to facilitate the distribution of information in a centralized way. Usually daily visit several websites to see if there is anything new in our favorite places. The fundamental principle behind RSS is that "the receiver is no longer in search of information, is the information that goes in search of the receiver." If you use an RSS aggregators not have to visit each of these sites because they receive all the news in one place. The aggregator checks your favorite websites in search of new content and features directly without any effort on your part.', 'wpematico' );?></p>
						<p><?php _e( 'Blogs contain in its main page a XML file. In the case of blogs on WordPress the feed is defined as follows:', 'wpematico' );?></p><code>http://domain.com/feed</code>
						<p><?php _e( 'We have to add this URL to the RSS field to receive the items.', 'wpematico' );?></p>
						<br />
						<h4><a href="<?php echo admin_url( 'edit.php?post_type=wpematico&page=wpematico_settings' ) ?>"><?php _e( 'WPeMatico &rarr; Settings', 'wpematico' ) ; ?></a></h4>
						<p><?php _e( 'The WPeMatico &rarr; Settings menu is where you\'ll set all global aspects for the operation of the plugin and the global options for campaigns, advanced options and tools.', 'wpematico' ) ; ?></p>
						<p><?php _e( 'There are also here the tests and the configuration options for the SimplePie library to get differnet behaviour when fetch the feed items.', 'wpematico' ) ; ?></p>
						<p><?php _e( 'Set to an external or internal Wordpress CRON scheduler and look at for the configuration tabs of all plugin extensions and Add-ons.', 'wpematico' ) ; ?></p>
					</div>
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'screenshot-7.jpg'; ?>" class="wpematico-welcome-screenshots"/>
						<p style="text-align:center;margin:0;"><?php _e( 'Testing SimplePie library', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Campaign', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media" style="max-height: 300px; overflow: hidden;">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'screenshot-6.png'; ?>" class="wpematico-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><a href="<?php echo admin_url( 'post-new.php?post_type=wpematico' ) ?>"><?php printf( __( '%s &rarr; Add New', 'wpematico' ), 'WPeMatico' ); ?></a></h4>
						<p><?php printf( __( 'The WPeMatico &rarr; All Campaigns menu is your access point for all aspects of your Feed campaigns creation and setup to fetch the items and insert them as posts or any Custom Post Type. To create your first campaign, simply click Add New and then fill out the campaign details.', 'wpematico' ) ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'wpematico' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo WPEMATICO_PLUGIN_URL . 'screenshot-4.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Inline Documentation','wpematico' );?></h4>
						<p><?php _e( 'Are those small sentences and/or phrases that you see alongside, or underneath, a feature in WPeMatico that give a short but very helpful explanation of what the feature is and serve as guiding tips that correspond with each feature. These tips sometimes even provide basic, recommended settings.', 'wpematico' );?></p>

						<h4><?php _e( 'Help Tab', 'wpematico' );?></h4>
						<p><?php _e( 'In addition to the inline documentation that you see scattered throughout the Dashboard, you’ll find a helpful tab in the upper-right corner of your Dashboard labeled Help. Click this tab and a panel drops down that contains a lot of text providing documentation relevant to the page you are currently viewing on your Dashboard.', 'wpematico' );?></p>
						<p><?php _e( 'For example, if you’re viewing the WPeMatico Settings page, the Help tab drops down documentation relevant to the WPeMatico Settings page. Likewise, if you’re viewing the Add New Campaign page, clicking the Help tab drops down documentation with topics relevant to the settings and features you find on the Add New Campaign page within your Dashboard.', 'wpematico' );?></p>
						<p><?php _e( 'Just click the Help tab again to close the Help panel.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need more Help?', 'wpematico' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Phenomenal Support','wpematico' );?></h4>
						<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a target="_blank" href="https://etruel.com/my-account/support">support form</a>.', 'wpematico' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Need Even Faster Support?', 'wpematico' );?></h4>
						<p><?php _e( 'Our <a target="_blank" href="https://etruel.com/downloads/premium-support/">Premium Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'wpematico' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Get Notified of Extension Releases','wpematico' );?></h4>
						<p><?php _e( 'New extensions that make WPeMatico even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/bX2ANz" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'wpematico' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Get Alerted About New Tutorials', 'wpematico' );?></h4>
						<p><?php _e( '<a href="http://eepurl.com/bX2ANz" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take WPeMatico further.', 'wpematico' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'WPeMatico Add-ons', 'wpematico' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Extend the plugin features','wpematico' );?></h4>
						<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of WPeMatico. There are a Professional extension for extend the parsers of the feed contents, The Full Content add-on to scratch the source webpage looking to get the entire article, and many more.', 'wpematico' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Visit the Extension Store', 'wpematico' );?></h4>
						<p><?php _e( '<a href="https://etruel.com/downloads" target="_blank">The etruel store</a> has a list of all available extensions for WPeMatico, also other Worpdress plugins, some of them for free. Including convenient category filters so you can find exactly what you are looking for.', 'wpematico' );?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Parse the WPEMATICO readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( WPEMATICO_PLUGIN_DIR . 'readme.txt' ) ? WPEMATICO_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'wpematico' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	}


	/**
	 * Sends user to the Welcome page on first activation of WPEMATICO as well as each
	 * time WPEMATICO is upgraded to a new version
	 *
	 * @access public
	 * @since 1.3.8
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_wpematico_activation_redirect' ) )
			return;
		
		// redirect if ! AJAX
		if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']))
			return;

		// Delete the redirect transient
		delete_transient( '_wpematico_activation_redirect' );

		// Delete the etruel_wpematico_addons_data transient to create again when access the addon page
		delete_transient( 'etruel_wpematico_addons_data' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;
		
		$upgrade = get_option( 'wpematico_db_version' );
		update_option( 'wpematico_db_version', WPEMATICO_VERSION );
			
		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=wpematico-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=wpematico-about' ) ); exit;
		}
	}
}
new WPEMATICO_Welcome();
