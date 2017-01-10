<?php
# @charset utf-8
if ( ! function_exists( 'add_filter' ) )
	exit;

$cfg = get_option( 'WPeMatico_Options' );
$cfg = apply_filters('wpematico_check_options',$cfg );


if ( !class_exists( 'WPeMatico' ) ) {
	class WPeMatico extends WPeMatico_functions {
		const TEXTDOMAIN = 'wpematico';
		const PROREQUIRED = '1.4';
		const OPTION_KEY = 'WPeMatico_Options';
		public static $name = '';
		public static $version = '';
		public static $basen;		/** Plugin basename * @var string	 */
		public static $uri = '';
		public static $dir = '';		/** filesystem path to the plugin with trailing slash */

		public $options = array();
		public static function init() {
			$plugin_data = self::plugin_get_version(WPEMATICO_ROOTFILE);
			self :: $name = $plugin_data['Name'];
			self :: $version = $plugin_data['Version'];
			self :: $uri = plugin_dir_url( WPEMATICO_ROOTFILE );
			self :: $dir = plugin_dir_path( WPEMATICO_ROOTFILE );
			self :: $basen = plugin_basename(WPEMATICO_ROOTFILE);
			
			new self( TRUE );
		}
		
		/**
		 * constructor
		 *
		 * @access public
		 * @param bool $hook_in
		 * @return void
		 */
		public function __construct( $hook_in = FALSE ) {
			//Admin message
			//add_action('admin_notices', array( &$this, 'wpematico_admin_notice' ) ); 
			if ( ! $this->wpematico_env_checks() )
				return;
			$this->load_options();

			if($this->options['nonstatic'] && !class_exists( 'NoNStatic' )){
				$this->options['nonstatic'] = false; 
				$this->update_options();
			}
			
			$this->Create_campaigns_page();
			if ( $hook_in ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_print_styles', array($this, 'all_WP_admin_styles') );
				add_action('in_admin_header', array($this, 'writing_settings_help') );

				wp_register_style( 'WPematStylesheet', self :: $uri .'app/css/wpemat_styles.css' );
				wp_register_script( 'WPemattiptip', self :: $uri .'app/js/jquery.tipTip.minified.js','jQuery' );
				wp_register_script('jquery-vsort', self ::$uri .'app/js/jquery.vSort.min.js', array('jquery'));

				add_filter(	'wpematico_check_campaigndata', array( __CLASS__,'check_campaigndata'),10,1);
				add_filter(	'wpematico_check_options', array( __CLASS__,'check_options'),10,1);
								
				//add Dashboard widget
				if (!$this->options['disabledashboard']){
					global $current_user;      
					wp_get_current_user();	
					$user_object = new WP_User($current_user->ID);
					$roles = $user_object->roles;
					$display = false;
					if (!is_array($this->options['roles_widget'])) $this->options['roles_widget']= array( "administrator" => "administrator" );
					foreach( $roles as $cur_role ) {
						if ( array_search($cur_role, $this->options['roles_widget']) ) {
							$display = true;
						}
					}	
					if ( $current_user->ID && ( $display == true ) && current_user_can(get_post_type_object( 'wpematico' )->cap->edit_others_posts) ) {	
						add_action('wp_dashboard_setup', array( &$this, 'wpematico_add_dashboard'));
					}
				}
			}
			//add Empty Trash folder buttons
			if ($this->options['emptytrashbutton']){
				// Add button to list table for all post types
				add_action( 'restrict_manage_posts', array( &$this, 'add_button' ), 90 );
			}
			//Check timeout of running campaigns
			if ($this->options['campaign_timeout'] > 0 ) {
				$args = array( 'post_type' => 'wpematico', 'orderby' => 'ID', 'order' => 'ASC', 'numberposts' => -1 );
				$campaigns = get_posts( $args );
				foreach( $campaigns as $post ) {
					$campaign = $this->get_campaign( $post->ID );
					$starttime = @$campaign['starttime']; 
					if ($starttime>0) {
						$runtime=current_time('timestamp')-$starttime;
						if(($this->options['campaign_timeout'] <= $runtime)) {
							$campaign['lastrun'] = $starttime;
							$campaign['lastruntime'] = ' <span style="color:red;">Timeout: '.$this->options['campaign_timeout'].'</span>';
							$campaign['starttime']   = '';
							$campaign['lastpostscount'] = 0; 
							$this->update_campaign($post->ID, $campaign);  //Save Campaign new data
						}

					}
				}
			}
		}

		/**
		 * Display empty trash button on list tables
		 * @return void
		 */
		public function add_button() {
			global $typenow,$post_type, $pagenow;
			// Don't show on comments list table
			if( 'edit-comments.php' == $pagenow ) return;
			// Don't show on trash page
			if( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash' ) return;
			// Don't show if current user is not allowed to edit other's posts for this post type
			if( empty( $typenow ) ) $typenow = $post_type;
			// Don't show if current user is not allowed to edit other's posts for this post type
			if ( ! current_user_can( get_post_type_object( $typenow )->cap->edit_others_posts ) ) return;
			// Don't show if there are no items in the trash for this post type
			if( 0 == intval( wp_count_posts( $typenow, 'readable' )->trash ) ) return;
			
			$display = false;
			$args=array();
			$output = 'names'; // names or objects
			$post_types=get_post_types($args,$output); 
			foreach ($post_types  as $post_type ) {
				if($post_type != $typenow) continue;
				if( isset($this->options['cpt_trashbutton'][$post_type]) && $this->options['cpt_trashbutton'][$post_type] ) {
					$display = true;
				}
			}
			if ( !$display ) return;
			?>
			<div class="alignright empty_trash">
				<input type="hidden" name="post_status" value="trash" />
				<?php submit_button( __( 'Empty Trash' ), 'apply', 'delete_all', false ); ?>
			</div>
			<?php
		}
		
		//add dashboard widget
		function wpematico_add_dashboard() {
			wp_add_dashboard_widget( 'wpematico_widget', 'WPeMatico' , array( &$this, 'wpematico_dashboard_widget') );
		}

		 //Dashboard widget
		function wpematico_dashboard_widget() {
			$campaigns= $this->get_campaigns();
			echo '<div style="background-color: #E1DC9C;border: 1px solid #DDDDDD; height: 20px; margin: -10px -10px 2px; padding: 5px 10px 0px;';
			echo "background: -moz-linear-gradient(center bottom,#FCF6BC 0,#E1DC9C 98%,#FFFEA8 0);
				background: -webkit-gradient(linear,left top,left bottom,from(#FCF6BC),to(#E1DC9C));
				-ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FCF6BC',endColorstr='#E1DC9C');
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FCF6BC',endColorstr='#E1DC9C');\">";
			echo '<strong>'.__('Last Processed Campaigns:', self :: TEXTDOMAIN ).'</strong></div>';
			@$campaigns2 = $this->filter_by_value($campaigns, 'lastrun', '');  
			$this->array_sort($campaigns2,'!lastrun');
			if (is_array($campaigns2)) {
				$count=0;
				foreach ($campaigns2 as $key => $campaign_data) {
					echo '<a href="'.wp_nonce_url('post.php?post='.$campaign_data['ID'].'&action=edit', 'edit').'" title="'.__('Edit Campaign', self :: TEXTDOMAIN ).'">';
						if ($campaign_data['lastrun']) {
							echo " <i><strong>".$campaign_data['campaign_title']."</i></strong>, ";
							echo  date_i18n( (get_option('date_format').' '.get_option('time_format') ), $campaign_data['lastrun'] ).', <i>'; 
							if ($campaign_data['lastpostscount']>0)
								echo ' <span style="color:green;">'. sprintf(__('Processed Posts: %1s', self :: TEXTDOMAIN ),$campaign_data['lastpostscount']).'</span>, ';
							else
								echo ' <span style="color:red;">'. sprintf(__('Processed Posts: %1s', self :: TEXTDOMAIN ), '0').'</span>, ';
								
							if ($campaign_data['lastruntime']<10)
								echo ' <span style="color:green;">'. sprintf(__('Fetch done in %1s sec.', self :: TEXTDOMAIN ),$campaign_data['lastruntime']) .'</span>';
							else
								echo ' <span style="color:red;">'. sprintf(__('Fetch done in %1s sec.', self :: TEXTDOMAIN ),$campaign_data['lastruntime']) .'</span>';
						} 
					echo '</i></a><br />';
					$count++;
					if ($count>=5)
						break;
				}		
			}
			unset($campaigns2);
			echo '<br />';
			echo '<div style="background-color: #E1DC9C;border: 1px solid #DDDDDD; height: 20px; margin: -10px -10px 2px; padding: 5px 10px 0px;';
			echo "background: -moz-linear-gradient(center bottom,#FCF6BC 0,#E1DC9C 98%,#FFFEA8 0);
				background: -webkit-gradient(linear,left top,left bottom,from(#FCF6BC),to(#E1DC9C));
				-ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FCF6BC',endColorstr='#E1DC9C');
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FCF6BC',endColorstr='#E1DC9C');\">";
			echo '<strong>'.__('Next Scheduled Campaigns:', self :: TEXTDOMAIN ).'</strong>';
			echo '</div>';
			echo '<ul style="list-style: circle inside none; margin-top: 2px; margin-left: 9px;">';
			$this->array_sort($campaigns,'cronnextrun');
			foreach ($campaigns as $key => $campaign_data) {
				if ($campaign_data['activated']) {
					echo '<li><a href="'.wp_nonce_url('post.php?post='.$campaign_data['ID'].'&action=edit', 'edit').'" title="'.__('Edit Campaign', self :: TEXTDOMAIN ).'">';
					echo '<strong>'.$campaign_data['campaign_title'].'</strong>, ';
					if ($campaign_data['starttime']>0 and empty($campaign_data['stoptime'])) {
						$runtime=current_time('timestamp')-$campaign_data['starttime'];
						echo __('Running since:', self :: TEXTDOMAIN ).' '.$runtime.' '.__('sec.', self :: TEXTDOMAIN );
					} elseif ($campaign_data['activated']) {
						//echo date(get_option('date_format'),$campaign_data['cronnextrun']).' '.date(get_option('time_format'),$campaign_data['cronnextrun']);
						echo date_i18n( (get_option('date_format').' '.get_option('time_format') ), $campaign_data['cronnextrun'] );
					}
					echo '</a></li>';
				}
			}
			$campaigns=$this->filter_by_value($campaigns, 'activated', '');
			if (empty($campaigns)) 
				echo '<i>'.__('None', self :: TEXTDOMAIN ).'</i><br />';
			echo '</ul>';

		}
		

		/**
		 * admin menu custom post type
		 *
		 * @access public
		 * @return void
		 */
		 public static function Create_campaigns_page() {
		  $labels = array(
			'name' => __('Campaigns',  self :: TEXTDOMAIN ),
			'singular_name' => __('Campaign',  self :: TEXTDOMAIN ),
			'add_new' => __('Add New', self :: TEXTDOMAIN ),
			'add_new_item' => __('Add New Campaign', self :: TEXTDOMAIN ),
			'edit_item' => __('Edit Campaign', self :: TEXTDOMAIN ),
			'new_item' => __('New Campaign', self :: TEXTDOMAIN ),
			'all_items' => __('All Campaigns', self :: TEXTDOMAIN ),
			'view_item' => __('View Campaign', self :: TEXTDOMAIN ),
			'search_items' => __('Search Campaign', self :: TEXTDOMAIN ),
			'not_found' =>  __('No campaign found', self :: TEXTDOMAIN ),
			'not_found_in_trash' => __('No Campaign found in Trash', self :: TEXTDOMAIN ), 
			'parent_item_colon' => '',
			'menu_name' => 'WPeMatico');
		  $args = array(
			'labels' => $labels,
			//'public' => true,
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => (get_option('wpem_menu_position')) ? 999 : 7,
			'menu_icon' => self :: $uri.'/images/robotico_orange-25x25.png',
			'register_meta_box_cb' => array( 'WPeMatico_Campaign_edit', 'create_meta_boxes'),
			'map_meta_cap' => true,
			'supports' => array( 'title', 'excerpt' ) ); 
		  register_post_type('wpematico',$args);
		}  //
		

		/**
		 * admin_init
		 *
		 * @access public
		 * @return void
		 */
		public function admin_init() {
			$sect_title='<img src="' . self :: $uri.'/images/robotico_orange-50x50.png'.'" style="margin: 0pt 2px -2px 0pt;">'.' WPeMatico '.WPEMATICO_VERSION;
			add_settings_section('wpematico', $sect_title, array($this, 'writing_settings'), 'writing');
			register_setting( 'writing', 'wpem_menu_position'); //, 'sanitize_callback' );
			register_setting( 'writing', 'wpem_hide_reviews'); //, 'sanitize_callback' );
			add_settings_field( 
				'wpem_menu_position',
				'Reset Menu Position',                
				array($this, 'writing_wp_form'), 
				'writing',                        
				'wpematico',         
				array(        //The array of arguments to pass to the callback.
					'id' => 'wpem_menu_position',
					'description' => __('Activate this setting if you can\'t see WPeMatico menu at left under Posts menu item.', self :: TEXTDOMAIN )
				)
			);
			add_settings_field( 
				'wpem_hide_reviews',                 
				'Hide Reviews on Settings',
				array($this, 'writing_wp_form'),
				'writing',                        
				'wpematico',         
				array(        //The array of arguments to pass to the callback. In this case, just a description.
					'id' => 'wpem_hide_reviews',
					'description' => __('Activate this setting if you don\'t see the WPeMatico Settings page complete or can\'t read externals URLs.', self :: TEXTDOMAIN ),
				)
			);

		}

		/**
		 * Wordpress writing settings 
		 *
		 * @access public
		 * @return void
		 */
		public function writing_settings($arg) {
			echo "<p></p>";
		}
		
		public function writing_wp_form($args) {
			// Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
			$html = '<input type="checkbox" id="' . $args['id'] . '" name="' . $args['id'] . '" value="1" ' . checked(1, get_option($args['id']), false) . '/>'; 

			// Here, we will take the first argument of the array and add it to a label next to the checkbox
			$html .= '<label for="' . $args['id'] . '"> '  . $args['description'] . '</label>'; 

			echo $html;
			//echo print_r($args);

		}
		
		/**
		 * Add to Wordpress writing settings help
		 *
		 * @access public
		 * @return void
		 */
		public function writing_settings_help($arg) {
			$screen = get_current_screen();
			if ('options-writing' === $screen->base ) {
				$screen->add_help_tab( array(
					'id'      => 'wpematico',
					'title'   => __('WPeMatico'),
					'content' => '<p>' . __('If you don\'t see the WPeMatico Menu may be another plugin or a custom menu added by your theme are "overwritten" the WPeMatico menu position.', self :: TEXTDOMAIN ) . '<br />' .
						'' . __('Click the checkbox "Reset Menu Position" to show the menu on last position in your Wordpress menu.', self :: TEXTDOMAIN ) . '</p>'.
						'<p></p>'.
						'<p>' . __('If you can\'t see well the WPeMatico Settings page is probable that you are having problems to read external wordpress web pages from your server.', self :: TEXTDOMAIN ) . '<br />' .
						'' . __('Click the checkbox "Hide Reviews on Settings" to avoid this and show just a link to Wordpress reviews page.', self :: TEXTDOMAIN ) . '</p>'.
						'<p></p>'.
						'<p><a href="http://www.wpematico.com" target="_blank">WPeMatico WebPage</a>  -  <a href="https://etruel.com/downloads/category/wpematico-add-ons/" target="_blank">WPeMatico Add-Ons</a>  -  <a href="https://etruel.com/support/" target="_blank">etruel\'s Custom Support</a></p>'.
						'<p></p>'.
						'<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
				) );
			}
		}
		
		/**
		 * admin menu
		 *
		 * @access public
		 * @return void
		 */
		public function admin_menu() {
			$page = add_submenu_page(
				'edit.php?post_type=wpematico',
				__( 'Settings', self :: TEXTDOMAIN ),
				__( 'Settings', self :: TEXTDOMAIN ),
				'manage_options',
				'wpematico_settings',
				'wpematico_settings_page'
			);
			add_action( 'admin_print_styles-' . $page, array(&$this, 'WPemat_admin_styles') );
		}
		
		public function all_WP_admin_styles(){
			?><style type="text/css">
			.menu-icon-wpematico img {
				margin-top: -5px;
			}
			</style><?php
 		}


		public static function WPemat_admin_styles() {
			wp_enqueue_style( 'WPematStylesheet' );
			wp_enqueue_script( 'WPemattiptip' );
			add_action('admin_head', 'wpematico_settings_head');
		}

		/**
		 * load_options in class options attribute
		 * 
		 * @access public 
		 * load array with options in class options attribute 
		 * @return void
		 */
		public function load_options() {
			$cfg= get_option( self :: OPTION_KEY );
			if ( !$cfg ) {
				$this->options = $this->check_options( array() );
				add_option( self :: OPTION_KEY, $this->options , '', 'yes' );
			}else {
				$this->options = $this->check_options( $cfg );
			}
			return;
		}

		public static function check_options($options) {
			$cfg['mailmethod']		= (!isset($options['mailmethod'])) ?'mail':$options['mailmethod'];
			$cfg['mailsndemail']	= (!isset($options['mailsndemail'])) ? '':sanitize_email($options['mailsndemail']);
			$cfg['mailsndname']		= (!isset($options['mailsndname'])) ? '':$options['mailsndname'];
			$cfg['mailsendmail']	= (!isset($options['mailsendmail'])) ? '': untrailingslashit(str_replace('//','/',str_replace('\\','/',stripslashes($options['mailsendmail']))));
			$cfg['mailsecure']		= (!isset($options['mailsecure'])) ? '': $options['mailsecure'];
			$cfg['mailhost']		= (!isset($options['mailhost'])) ? '': $options['mailhost'];
			$cfg['mailport']		= (!isset($options['mailport'])) ? '': $options['mailport'];
			$cfg['mailuser']		= (!isset($options['mailuser'])) ? '': $options['mailuser'];			
			$cfg['mailpass']		= (!isset($options['mailpass'])) ? '': $options['mailpass'];
			$cfg['disabledashboard']= (!isset($options['disabledashboard']) || empty($options['disabledashboard'])) ? false : ($options['disabledashboard']==1) ? true : false;
			$cfg['roles_widget']	= (!isset($options['roles_widget']) || !is_array($options['roles_widget'])) ? array( "administrator" => "administrator" ): $options['roles_widget'];
			$cfg['dontruncron']		= (!isset($options['dontruncron']) || empty($options['dontruncron'])) ? false: ($options['dontruncron']==1) ? true : false;
			$cfg['disablewpcron']	= (!isset($options['disablewpcron']) || empty($options['disablewpcron'])) ? false: ($options['disablewpcron']==1) ? true : false;
			$cfg['set_cron_code']	= (!isset($options['set_cron_code']) || empty($options['set_cron_code'])) ? false: ($options['set_cron_code']==1) ? true : false;
			$cfg['cron_code']		= (!isset($options['cron_code'])) ? '': $options['cron_code'];
			$cfg['logexternalcron']	= (!isset($options['logexternalcron']) || empty($options['logexternalcron'])) ? false: ($options['logexternalcron']==1) ? true : false;
			$cfg['disable_credits']	= (!isset($options['disable_credits']) || empty($options['disable_credits'])) ? false: ($options['disable_credits']==1) ? true : false;
			$cfg['disablecheckfeeds']=(!isset($options['disablecheckfeeds']) || empty($options['disablecheckfeeds'])) ? false: ($options['disablecheckfeeds']==1) ? true : false;
			$cfg['enabledelhash']	= (!isset($options['enabledelhash']) || empty($options['enabledelhash'])) ? false: ($options['enabledelhash']==1) ? true : false;
			$cfg['enableseelog']	= (!isset($options['enableseelog']) || empty($options['enableseelog'])) ? false: ($options['enableseelog']==1) ? true : false;
			$cfg['enablerewrite']	= (!isset($options['enablerewrite']) || empty($options['enablerewrite'])) ? false: ($options['enablerewrite']==1) ? true : false;
			$cfg['enableword2cats']	= (!isset($options['enableword2cats']) || empty($options['enableword2cats'])) ? false: ($options['enableword2cats']==1) ? true : false;
			$cfg['customupload']	= (!isset($options['customupload']) || empty($options['customupload'])) ? false: ($options['customupload']==1) ? true : false;
			$cfg['imgattach']		= (!isset($options['imgattach']) || empty($options['imgattach'])) ? false: ($options['imgattach']==1) ? true : false;
			$cfg['imgcache']		= (!isset($options['imgcache']) || empty($options['imgcache'])) ? false: ($options['imgcache']==1) ? true : false;
			$cfg['gralnolinkimg']	= (!isset($options['gralnolinkimg']) || empty($options['gralnolinkimg'])) ? false: ($options['gralnolinkimg']==1) ? true : false;
			$cfg['featuredimg']		= (!isset($options['featuredimg']) || empty($options['featuredimg'])) ? false: ($options['featuredimg']==1) ? true : false;
			$cfg['rmfeaturedimg']	= (!isset($options['rmfeaturedimg']) || empty($options['rmfeaturedimg'])) ? false: ($options['rmfeaturedimg']==1) ? true : false;
			$cfg['force_mysimplepie']	= (!isset($options['force_mysimplepie']) || empty($options['force_mysimplepie'])) ? false: ($options['force_mysimplepie']==1) ? true : false;
			$cfg['set_stupidly_fast']	= (!isset($options['set_stupidly_fast']) || empty($options['set_stupidly_fast'])) ? false: ($options['set_stupidly_fast']==1) ? true : false;
			$cfg['simplepie_strip_htmltags'] = (!isset($options['simplepie_strip_htmltags']) || empty($options['simplepie_strip_htmltags'])) ? false: ($options['simplepie_strip_htmltags']==1) ? true : false;
			$cfg['simplepie_strip_attributes'] = (!isset($options['simplepie_strip_attributes']) || empty($options['simplepie_strip_attributes'])) ? false: ($options['simplepie_strip_attributes']==1) ? true : false;
			$cfg['strip_htmltags']	= (!isset($options['strip_htmltags'])) ? '': $options['strip_htmltags'];			
			$cfg['strip_htmlattr']	= (!isset($options['strip_htmlattr'])) ? '': $options['strip_htmlattr'];			
			$cfg['woutfilter']		= (!isset($options['woutfilter']) || empty($options['woutfilter'])) ? false: ($options['woutfilter']==1) ? true : false;
			$cfg['campaign_timeout']= (!isset($options['campaign_timeout']) ) ? 300: (int)$options['campaign_timeout'];
			$cfg['throttle']		= (!isset($options['throttle']) ) ? 0: (int)$options['throttle'];
			$cfg['allowduplicates']	= (!isset($options['allowduplicates']) || empty($options['allowduplicates'])) ? false: ($options['allowduplicates']==1) ? true : false;
			$cfg['allowduptitle']	= (!isset($options['allowduptitle']) || empty($options['allowduptitle'])) ? false: ($options['allowduptitle']==1) ? true : false;
			$cfg['allowduphash']	= (!isset($options['allowduphash']) || empty($options['allowduphash'])) ? false: ($options['allowduphash']==1) ? true : false;
			$cfg['jumpduplicates']	= (!isset($options['jumpduplicates']) || empty($options['jumpduplicates'])) ? false: ($options['jumpduplicates']==1) ? true : false;
			$cfg['disableccf']	= (!isset($options['disableccf']) || empty($options['disableccf'])) ? false: ($options['disableccf']==1) ? true : false;
			$cfg['nonstatic']		= (!isset($options['nonstatic']) || empty($options['nonstatic'])) ? false: ($options['nonstatic']==1) ? true : false;
			$cfg['emptytrashbutton']= (!isset($options['emptytrashbutton']) || empty($options['emptytrashbutton'])) ? false: ($options['emptytrashbutton']==1) ? true : false;
			$cfg['cpt_trashbutton']	= (!isset($options['cpt_trashbutton']) || !is_array($options['cpt_trashbutton'])) ? array( 'post' => 1,	'page' => 1 ): $options['cpt_trashbutton'];

			return apply_filters('wpematico_more_options', $cfg, $options);
		}
		
		/**
		 * update_options
		 *
		 * @access protected
		 * @return bool True, if option was changed
		 */
		public function update_options() {
			return update_option( self :: OPTION_KEY, $this->options );
		}

	} // Class WPeMatico
}

