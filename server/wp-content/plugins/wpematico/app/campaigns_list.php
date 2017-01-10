<?php 
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

add_filter('manage_edit-wpematico_columns' , array( 'WPeMatico_Campaigns', 'set_edit_wpematico_columns'));
add_action('manage_wpematico_posts_custom_column',array('WPeMatico_Campaigns','custom_wpematico_column'),10,2);
add_filter('post_row_actions' , array( 'WPeMatico_Campaigns', 'wpematico_quick_actions'), 10, 2);
add_filter("manage_edit-wpematico_sortable_columns", array( 'WPeMatico_Campaigns', "sortable_columns") );
add_action('pre_get_posts', array( 'WPeMatico_Campaigns', 'column_orderby') );

add_action('restrict_manage_posts', array( 'WPeMatico_Campaigns', 'custom_filters') );
add_action('pre_get_posts', array( 'WPeMatico_Campaigns', 'query_set_custom_filters') );

// Messages 
add_filter( 'post_updated_messages', array( 'WPeMatico_Campaigns' , 'wpematico_updated_messages') );
//LIST FILTER ACTIONS 
add_filter('views_edit-wpematico', array( 'WPeMatico_Campaigns', 'my_views_filter') );
add_filter('disable_months_dropdown', array( 'WPeMatico_Campaigns' , 'disable_list_filters'),10,2);
add_filter('disable_categories_dropdown', array( 'WPeMatico_Campaigns' , 'disable_list_filters'),10,2);

add_action('admin_print_styles-edit.php', array( 'WPeMatico_Campaigns','list_admin_styles'));
add_action('admin_print_scripts-edit.php', array( 'WPeMatico_Campaigns','list_admin_scripts'));
//QUICK ACTIONS
add_action('admin_action_wpematico_copy_campaign', array( 'WPeMatico_Campaigns', 'wpematico_copy_campaign'));
add_action('admin_action_wpematico_toggle_campaign', array( 'WPeMatico_Campaigns', 'wpematico_toggle_campaign'));
add_action('admin_action_wpematico_reset_campaign', array( 'WPeMatico_Campaigns', 'wpematico_reset_campaign'));
add_action('admin_action_wpematico_clear_campaign', array( 'WPeMatico_Campaigns', 'wpematico_clear_campaign'));		

add_filter('editable_slug', array('WPeMatico_Campaigns','inline_custom_fields'),999,1);
//CUSTOM BULK & EDIT ACTIONS
add_action( 'quick_edit_custom_box', array( 'WPeMatico_Campaigns', 'wpematico_add_to_quick_edit_custom_box'), 10, 2 );
add_action( 'wp_ajax_manage_wpematico_save_bulk_edit', array( 'WPeMatico_Campaigns', 'manage_wpematico_save_bulk_edit') );
add_action( 'wp_ajax_get_wpematico_categ_bulk_edit', array( 'WPeMatico_Campaigns', 'get_wpematico_categ_bulk_edit') );

add_action('in_admin_header', array( 'WPeMatico_Campaigns', 'campaigns_list_help'));

if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php?post_type=wpematico')  
  || strstr($_SERVER['REQUEST_URI'], 'wp-admin/admin.php?action=wpematico_') ) 
	add_action( 'init', array( 'WPeMatico_Campaigns', 'init' ) );
	else return;

// just in campaign list
	add_filter('bulk_actions-edit-wpematico', array( 'WPeMatico_Campaigns', 'old_bulk_actions' ), 90,1 );
	add_action('restrict_manage_posts', array( 'WPeMatico_Campaigns', 'run_selected_campaigns' ), 1, 2 );


if ( class_exists( 'WPeMatico_Campaigns' ) ) return;
class WPeMatico_Campaigns {

	public static function init() {
		new self();
	}
	
	public static function campaigns_list_help() {
		global $post_type, $current_screen; 
		if($post_type != 'wpematico') return;		
		if($current_screen->id=='edit-wpematico')
			require(  dirname( __FILE__ ) . '/campaigns_list_help.php' );

	}
	
	public function __construct( $hook_in = FALSE ) {
		global $_bulk_actions;
		$cfg = get_option( WPeMatico :: OPTION_KEY);
		$cfg = apply_filters('wpematico_check_options', $cfg);
		if ( (isset($cfg['enabledelhash']) && !empty($cfg['enabledelhash']) ) && $cfg['enabledelhash'] )    // Si está habilitado en settings, lo muestra 
			add_action('admin_action_wpematico_delhash_campaign', array( 'WPeMatico_Campaigns', 'wpematico_delhash_campaign'));
		}

	public static function custom_filters($options) {
		global $typenow, $wp_query, $current_user, $pagenow, $cfg;
		if($pagenow=='edit.php' && is_admin() && $typenow=='wpematico') {

		$options = WPeMatico_Campaign_edit::campaign_type_options();
		$readonly = ( count($options) == 1 ) ? 'disabled' : '';
		$campaign_type = (isset($_GET['campaign_type']) && !empty($_GET['campaign_type']) ) ? $_GET['campaign_type'] : '';
		if(!empty($campaign_type)) $campaign_type = sanitize_text_field($campaign_type);
		$echoHtml = '<div style="display: inline-block;"><select id="campaign_type" '.$readonly.' name="campaign_type" style="display:inline;">';
		$echoHtml .= '<option value=""'.  selected( '', $campaign_type, false ).'>'.__('Campaign Type', 'wpematico').'</option>';
		foreach($options as $key => $option) {
			$echoHtml .= '<option value="'.$option["value"].'"'.  selected( $option["value"], $campaign_type, false ).'>'.$option["text"].'</option>';
		}
		$echoHtml .= '</select></div>';

		echo $echoHtml;
				
		}
	}
	// Show only posts and media related to logged in author
	public static function query_set_custom_filters( $wp_query ) {
		global $current_user, $pagenow, $typenow;
		if($pagenow=='edit.php' && is_admin() && $typenow=='wpematico') {
			$campaign_type = (isset($_GET['campaign_type']) && !empty($_GET['campaign_type']) ) ? $_GET['campaign_type'] : '';

			$filtering = false;
			if(!empty($campaign_type)) { 
				$filtering = true;				
				$campaign_type = sanitize_text_field($campaign_type);
				$meta_query[] =	array(
					array(
						'key' => 'campaign_data',
						'value' => serialize($campaign_type),
						'compare' => 'LIKE'
					)
				);
			}
			if(	$filtering ) {
				$wp_query->set( 'meta_query', $meta_query);
//				add_filter('views_edit-wpsellerevents',  array(__CLASS__,'fix_post_counts'));				
			}
		}
	}


	// Functions to allow customize the bulk actions in campaigns list.  (WP 4.7 fix this)
	public static function old_bulk_actions($bulk_actions) {
		// Don't show on trash page
		if( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash' ) return $bulk_actions;

		return array();
	}
	
	protected static function get_bulk_actions() {
		$actions = array();
		$post_type_obj = get_post_type_object( 'wpematico' );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			$actions['edit'] = __( 'Edit' );
		}
		if ( current_user_can( $post_type_obj->cap->delete_posts ) ) {
			$actions['trash'] = __( 'Move to Trash' );
		}
		return $actions;
	}
	
	static function bulk_actions( $which = '' ) {
		global $_bulk_actions;
		if ( is_null( $_bulk_actions ) ) {
			$_bulk_actions = self::get_bulk_actions();
			/**
			 * Filters the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `self::screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$_bulk_actions = apply_filters( "new_bulk_actions-wpematico", $_bulk_actions );
			//$_bulk_actions = array_intersect_assoc( $_bulk_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $_bulk_actions ) )
			return;

		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $_bulk_actions as $name => $title ) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

			echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}
	// END Functions to allow customize the bulk actions in campaigns list.  (WP 4.7 fix this)

	public static function run_selected_campaigns($post_type, $which) {
		global $typenow,$post_type, $pagenow;
		if($post_type != 'wpematico') return;		
		// Don't show on trash page
		if( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash' ) return;
		// Don't show if current user is not allowed to edit other's posts for this post type
		if( empty( $typenow ) ) $typenow = $post_type;
		// Don't show if current user is not allowed to edit other's posts for this post type
		if ( ! current_user_can( get_post_type_object( $typenow )->cap->edit_others_posts ) ) return;
		
		echo '<div style="margin: 1px 5px 0 0;float:left;background-color: #EB9600;" id="run_all" onclick="javascript:run_all();" class="button">'. __('Run Selected Campaigns', 'wpematico' ) . '</div>';
		self::bulk_actions($which);
	}
	
	public static function disable_list_filters($disable , $post_type) {
		global $post_type;
		if($post_type == 'wpematico') return true;
		else return $disable;
	}

	public static function my_views_filter($links) {
		global $post_type;
		if($post_type != 'wpematico') return $links;		
		$links['wpematico'] = __('Visit', 'wpematico').' <a href="http://www.wpematico.com" target="_Blank" class="wpelinks">www.wpematico.com</a> ';
		$links['etruelcom'] = ' <a href="https://etruel.com" target="_Blank" class="wpelinks">AddOns Store</a>';
		return $links;
	}


	
  	public static function list_admin_styles(){
		global $post_type;
		if($post_type != 'wpematico') return;		
		wp_enqueue_style('campaigns-list',WPeMatico :: $uri .'app/css/campaigns_list.css');
		wp_enqueue_style('wpematstyles',WPeMatico :: $uri .'app/css/wpemat_styles.css');
//		add_action('admin_head', array(  'WPeMatico_Campaigns' ,'campaigns_admin_head_style'));
	}
	public static function list_admin_scripts(){
		global $post_type;
		if($post_type != 'wpematico') return;		
		add_action('admin_head', array(  'WPeMatico_Campaigns' ,'campaigns_list_admin_head'));
//		wp_register_script('jquery-input-mask', 'js/jquery.maskedinput-1.2.2.js', array( 'jquery' ));
//		wp_enqueue_script('color-picker', 'js/colorpicker.js', array('jquery'));
		wp_enqueue_script( 'wpematico-Date.phpformats', WPeMatico :: $uri . 'app/js/Date.phpformats.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'wpematico-bulk-quick-edit', WPeMatico :: $uri . 'app/js/bulk_quick_edit.js', array( 'jquery', 'inline-edit-post' ), '', true );
	}

	public static function campaigns_list_admin_head() {
		global $post, $post_type;
		if($post_type != 'wpematico') return $post->ID;
			
			$clockabove = '<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">'
			. '<button type="button" id="show-clock" class="button show-clock" aria-controls="clock-wrap" aria-expanded="false">'
			. date_i18n( get_option('date_format').' '. get_option('time_format') )
			. '</button>'
			. '</div>';
		?>		
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function($){
				theclock();
	            $('span:contains("<?php _e('Slug'); ?>")').each(function (i) {
					$(this).parent().hide();
				});
				$('span:contains("<?php _e('Password'); ?>")').each(function (i) {
					$(this).parent().parent().hide();
				});
				$('select[name="_status"]').each(function (i) {
					$(this).parent().parent().parent().parent().hide();
				});
				$('span:contains("<?php _e('Date'); ?>")').each(function (i) {
					$(this).parent().hide();
				});
				$('.inline-edit-date').each(function (i) {
					$(this).hide();
				});
				$('.inline-edit-col-left').append(	$('#optionscampaign').html() );
				$('#optionscampaign').remove();
				
				$('#screen-meta-links').append('<?php echo $clockabove; ?>');
				
				$("#cb-select-all-1, #cb-select-all-2").change (function() {
					$("input[name='post[]']").each(function() {
						if($(this).is(':checked')){
							$("tr#post-"+ $(this).val() ).css('background-color', '#dbb27e');
						}else{
							$("tr#post-"+ $(this).val() ).attr('style','');
						}
					});
				});
				$("input[name='post[]']").change (function() {
					if($(this).is(':checked')){
						$("tr#post-"+ $(this).val() ).css('background-color', '#dbb27e');
					}else{
						$("tr#post-"+ $(this).val() ).attr('style','');
					}
				});

			});

			function theclock(){
				nowdate = new Date();
				now = nowdate.format("<?php echo get_option('date_format').' '. get_option('time_format'); ?>");
				char=(nowdate.getSeconds()%2==0 )?' ':':';
				jQuery('#show-clock').html(now.replace(':', char) );
				setTimeout("theclock()",1000);
			} 
			
			function run_now(c_ID) {
				jQuery('html').css('cursor','wait');
				jQuery('#post-'+c_ID+' .statebutton.play').addClass('green');
				jQuery("div[id=fieldserror]").remove();
				msgdev="<p><img width='16' src='<?php echo admin_url('images/wpspin_light.gif'); ?>'> <span style='vertical-align: top;margin: 10px;'><?php _e('Running Campaign...', 'wpematico' ); ?></span></p>";
				jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+msgdev+'</div>');
				var data = {
					campaign_ID: c_ID ,
					action: "wpematico_run"
				};
				jQuery.post(ajaxurl, data, function(msgdev) {  //si todo ok devuelve LOG sino 0
					jQuery('#fieldserror').remove();
					if( msgdev.substring(0, 5) == 'ERROR' ){
						jQuery(".subsubsub").before('<div id="fieldserror" class="error fade">'+msgdev+'</div>');
					}else{
						jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+msgdev+'</div>');
						var floor = Math.floor;
						var bef_posts = floor( jQuery("tr#post-"+c_ID+" > .count").html() );
						var ret_posts = floor( bef_posts + floor(jQuery("#ret_lastposts").html()) );
						if(bef_posts == ret_posts)
							jQuery("tr#post-"+c_ID+" > .count").attr('style', 'font-weight: bold;color:#555;');
						else
							jQuery("tr#post-"+c_ID+" > .count").attr('style', 'font-weight: bold;color:#F00;');
						jQuery("tr#post-"+c_ID+" > .count").html( ret_posts.toString() );
						jQuery("#lastruntime").html( jQuery("#ret_lastruntime").html());
						jQuery("#lastruntime").attr( 'style', 'font-weight: bold;');
					}
					jQuery('html').css('cursor','auto');
					jQuery('#post-'+c_ID+' .statebutton.play').removeClass('green');
				});
			}
 			function run_all() {
				var selectedItems = 0;
				jQuery("input[name='post[]']:checked").each(function() {selectedItems++;});
				if (selectedItems == 0) {alert("<?php _e('Please select campaign(s) to Run.', 'wpematico' ); ?>"); return; }
				
				jQuery('html').css('cursor','wait');
				jQuery('#fieldserror').remove();
				msgdev="<p><img width='16' src='<?php echo admin_url('images/wpspin_light.gif'); ?>'> <span style='vertical-align: top;margin: 10px;'><?php _e('Running Campaign...', 'wpematico' ); ?></span></p>";
				jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade ajaxstop">'+msgdev+'</div>');
				jQuery("input[name='post[]']:checked").each(function() {
					var c_ID = jQuery(this).val();
					jQuery('#post-'+c_ID+' .statebutton.play').addClass('green');
					var data = {
						campaign_ID: c_ID ,
						action: "wpematico_run"
					};
					jQuery.post(ajaxurl, data, function(msgdev) {  //si todo ok devuelve LOG sino 0
						if( msgdev.substring(0, 5) == 'ERROR' ){
							jQuery(".subsubsub").before('<div id="fieldserror" class="error fade">'+msgdev+'</div>');
						}else{
							jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+msgdev+'</div>');
							var floor = Math.floor;
							var bef_posts = floor( jQuery("tr#post-"+c_ID+" > .count").html() );
							var ret_posts = floor( bef_posts + floor(jQuery('#log_message_'+c_ID).next().next("#ret_lastposts").html()) );
							if(bef_posts == ret_posts)
								jQuery("tr#post-"+c_ID+" > .count").attr('style', 'font-weight: bold;color:#555;');
							else
								jQuery("tr#post-"+c_ID+" > .count").attr('style', 'font-weight: bold;color:#F00;');
							jQuery("tr#post-"+c_ID+" > .count").html( ret_posts.toString() );
							jQuery("#lastruntime").html( jQuery("#ret_lastruntime").html());
							jQuery("#lastruntime").attr( 'style', 'font-weight: bold;');
						}
						jQuery('#post-'+c_ID+' .statebutton.play').removeClass('green');

					});
				}).ajaxStop(function() {
					jQuery('html').css('cursor','auto');
					jQuery('.ajaxstop').remove().ajaxStop();
				});
			}
 		</script>
		<?php
	}

	/**
	 ************ACCION COPIAR 
	 */
	function copy_duplicate_campaign($post, $status = '', $parent_id = '') {
		if ($post->post_type != 'wpematico') return;
		$prefix = "";
		$suffix = __("(Copy)",  'wpematico') ;
		if (!empty($prefix)) $prefix.= " ";
		if (!empty($suffix)) $suffix = " ".$suffix;
		$status = 'publish';

		$new_post = array(
		'menu_order' => $post->menu_order,
		'guid' => $post->guid,
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'pinged' => $post->pinged,
		'post_author' => @$post->author,
		'post_content' => $post->post_content,
		'post_excerpt' => $post->post_excerpt,
		'post_mime_type' => $post->post_mime_type,
		'post_parent' => $post->post_parent,
		'post_password' => $post->post_password,
		'post_status' => $status,
		'post_title' => $prefix.$post->post_title.$suffix,
		'post_type' => $post->post_type,
		'to_ping' => $post->to_ping, 
		'post_date' => $post->post_date,
		'post_date_gmt' => get_gmt_from_date($post->post_date)
		);	

		$new_post_id = wp_insert_post($new_post);

		$post_meta_keys = get_post_custom_keys($post->ID);
		if (!empty($post_meta_keys)) {
			foreach ($post_meta_keys as $meta_key) {
				$meta_values = get_post_custom_values($meta_key, $post->ID);
				foreach ($meta_values as $meta_value) {
					$meta_value = maybe_unserialize($meta_value);
					add_post_meta($new_post_id, $meta_key, $meta_value);
				}
			}
		}
		$campaign_data = WPeMatico :: get_campaign( $new_post_id );
		$campaign_data['activated'] = false;

		WPeMatico :: update_campaign( $new_post_id, $campaign_data );

		// If the copy is not a draft or a pending entry, we have to set a proper slug.
		/*if ($new_post_status != 'draft' || $new_post_status != 'auto-draft' || $new_post_status != 'pending' ){
			$post_name = wp_unique_post_slug($post->post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent);

			$new_post = array();
			$new_post['ID'] = $new_post_id;
			$new_post['post_name'] = $post_name;

			// Update the post into the database
			wp_update_post( $new_post );
		} */

		return $new_post_id;
	}

	function wpematico_copy_campaign($status = ''){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpematico_copy_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'wpematico'));
		}

		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$post = get_post($id);

		// Copy the post and insert it
		if (isset($post) && $post!=null) {
			$new_id = self :: copy_duplicate_campaign($post, $status);

			if ($status == ''){
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
			}
			exit;

		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die(esc_attr(__('Copy campaign failed, could not find original:',  'wpematico')) . ' ' . $id);
		}
	}

	/**
	************FIN ACCION COPIAR 
	*/

	/**
	************ACCION TOGGLE 
	*/
	function wpematico_toggle_campaign($status = ''){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpematico_toggle_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'wpematico'));
		}
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);

		$campaign_data =   WPeMatico :: get_campaign( $id );
		$campaign_data['activated'] = !$campaign_data['activated'];
		WPeMatico :: update_campaign( $id, $campaign_data );
		
		$notice= ($campaign_data['activated']) ? __('Campaign activated',  'wpematico') : __('Campaign Deactivated',  'wpematico');
		WPeMatico::add_wp_notice( array('text' => $notice .' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );

		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpematico') );
	}

	/*********FIN ACCION TOGGLE 	*/
	
	/**	************ACCION RESET 	*/
	function wpematico_reset_campaign($status = ''){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpematico_reset_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'wpematico'));
		}
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$campaign_data =   WPeMatico :: get_campaign( $id );
		$campaign_data['postscount'] = 0;
		$campaign_data['lastpostscount'] = 0;
		$campaign_data['cronnextrun']= WPeMatico :: time_cron_next($campaign_data['cron']); //set next run
		WPeMatico :: update_campaign( $id, $campaign_data );
		delete_post_meta($id, 'last_campaign_log');

		WPeMatico::add_wp_notice( array('text' => __('Reset Campaign',  'wpematico').' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );
		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpematico') );
	}

	/**************FIN ACCION RESET 	*/
	
	/**	************ACCION DELHASH	 	*/
	function wpematico_delhash_campaign(){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpematico_delhash_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'wpematico'));
		}
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$campaign_data =   WPeMatico :: get_campaign( $id );
		foreach($campaign_data['campaign_feeds'] as $feed) {    // Grabo el ultimo hash de cada feed con 0
			$campaign_data[$feed]['lasthash']="0"; 
			$lasthashvar = '_lasthash_'.sanitize_file_name($feed);
			add_post_meta( $id, $lasthashvar, "0", true )  or
				update_post_meta( $id, $lasthashvar, "0" );
		}
		WPeMatico :: update_campaign( $id, $campaign_data );
		WPeMatico::add_wp_notice( array('text' => __('Hash deleted on campaign',  'wpematico').' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );

		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpematico') );
	}
	/**************FIN ACCION DELHASH	*/
	
	/**	************ACCION CLEAR: ABORT CAMPAIGN	 	*/
	function wpematico_clear_campaign(){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpematico_clear_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'wpematico'));
		}

		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$campaign_data =   WPeMatico :: get_campaign( $id );

		$campaign_data['cronnextrun']= WPeMatico :: time_cron_next($campaign_data['cron']); //set next run
		$campaign_data['stoptime']   = current_time('timestamp');
		$campaign_data['lastrun']  	 = $campaign_data['starttime'];
		$campaign_data['lastruntime']= $campaign_data['stoptime']-$campaign_data['starttime'];
		$campaign_data['starttime']  = '';

		WPeMatico :: update_campaign( $id, $campaign_data );
		WPeMatico::add_wp_notice( array('text' => __('Campaign cleared',  'wpematico').' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );

		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpematico') );
	}
	/**************FIN ACCION DELHASH	*/
	
	
	
	static function wpematico_updated_messages( $messages ) {
	  global $post, $post_ID;
	  $messages['wpematico'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Campaign updated.', 'wpematico')),
		2 => __('Custom field updated.', 'wpematico') ,
		3 => __('Custom field deleted.', 'wpematico'),
		4 => __('Campaign updated.', 'wpematico'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Campaign restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Campaign published.', 'wpematico')),
		7 => __('Campaign saved.'),
		8 => sprintf( __('Campaign submitted.', 'wpematico')),
		9 => sprintf( __('Campaign scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview campaign</a>'),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Campaign draft updated. <a target="_blank" href="%s">Preview campaign</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	  );

	  return $messages;
	}
	
	
	public static function wpematico_action_link( $id = 0, $context = 'display', $actionslug ) {
		global $post;
		if ( !$post == get_post( $id ) ) return;
		switch ($actionslug){ 
		case 'copy':
			$action_name = "wpematico_copy_campaign";
			break;
		case 'toggle':
			$action_name = "wpematico_toggle_campaign";
			break;
		case 'reset':
			$action_name = "wpematico_reset_campaign";
			break;
		case 'delhash':
			$action_name = "wpematico_delhash_campaign";
			break;
		case 'clear':
			$action_name = "wpematico_clear_campaign";
			break;			
		}
		if ( 'display' == $context ) 
			$action = '?action='.$action_name.'&amp;post='.$post->ID;
		else 
			$action = '?action='.$action_name.'&post='.$post->ID;
			
		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )	return;
		
		return apply_filters( 'wpematico_action_link', admin_url( "admin.php". $action ), $post->ID, $context );
	}

	//change actions from custom post type list
	static function wpematico_quick_actions( $actions ) {
		global $post, $post_type_object;
		if( $post->post_type == 'wpematico' ) {
			$can_edit_post = current_user_can( 'edit_post', $post->ID );
			$cfg = get_option( WPeMatico :: OPTION_KEY);
//	//		unset( $actions['edit'] );
//			unset( $actions['view'] );
//	//		unset( $actions['trash'] );
//	//		unset( $actions['inline hide-if-no-js'] );
//			unset( $actions['clone'] );
//			unset( $actions['edit_as_new_draft'] );
			$actions = array();
			if ( $can_edit_post && 'trash' != $post->post_status ) {
				$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
			}
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( 'trash' == $post->post_status )
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
				elseif ( EMPTY_TRASH_DAYS )
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
			}
			if ( 'trash' != $post->post_status ) {
				//++++++Toggle
				$campaign_data = WPeMatico :: get_campaign( $post->ID );
				$starttime = @$campaign_data['starttime']; 
				if (empty($starttime)) {
/*					$acnow = (bool)$campaign_data['activated'];
					$atitle = ( $acnow ) ? esc_attr(__("Deactivate this campaign", 'wpematico')) : esc_attr(__("Activate schedule", 'wpematico'));
					$alink = ($acnow) ? __("Deactivate", 'wpematico'): __("Activate",'wpematico');
					$actions['toggle'] = '<a href="'.self :: wpematico_action_link( $post->ID , 'display','toggle').'" title="' . $atitle . '">' .  $alink . '</a>';
*/
					//++++++Copy
					$actions['copy'] = '<a href="'.self :: wpematico_action_link( $post->ID , 'display','copy').'" title="' . esc_attr(__("Clone this item", 'wpematico')) . '">' .  __('Copy', 'wpematico') . '</a>';
					//++++++Reset
					$actions['reset'] = '<a href="'.self :: wpematico_action_link( $post->ID , 'display','reset').'" title="' . esc_attr(__("Reset post count", 'wpematico')) . '">' .  __('Reset', 'wpematico') . '</a>';
					//++++++runnow
					//$actions['runnow'] = '<a href="JavaScript:run_now(' . $post->ID . ');" title="' . esc_attr(__("Run Once", 'wpematico')) . '">' .  __('Run Now', 'wpematico') . '</a>';
					//++++++delhash
					if ( @$cfg['enabledelhash'])    // Si está habilitado en settings, lo muestra 
						$actions['delhash'] = '<a href="'.self :: wpematico_action_link( $post->ID , 'display','delhash').'" title="' . esc_attr(__("Delete hash code for duplicates", 'wpematico')) . '">' .  __('Del Hash', 'wpematico') . '</a>';
					//++++++seelog
					if ( @$cfg['enableseelog']) {   // Si está habilitado en settings, lo muestra 
						$nonce= wp_create_nonce  ('clog-nonce');
						$nombre = get_the_title($post->ID);
						$actionurl = WPeMatico :: $uri . 'app/campaign_log.php?p='.$post->ID.'&_wpnonce=' . $nonce;
						$actionjs = "javascript:window.open('$actionurl','$nombre','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=700, height=600');";

						$actions['seelog'] = '<a href="#" onclick="'.$actionjs.' return false;" title="' . esc_attr(__("See last log of campaign. (Open a PopUp window)", 'wpematico')) . '">' . __('See Log', 'wpematico') . '</a>';
					}
				} else {  // Está en ejecución o quedó a la mitad
					unset( $actions['edit'] );
					unset( $actions['inline hide-if-no-js'] );
					$actions['clear'] = '<a href="'.self :: wpematico_action_link( $post->ID , 'display','clear').'" title="' . esc_attr(__("Clear fetching and restore campaign", 'wpematico')) . '">' .  __('Clear campaign', 'wpematico') . '</a>';
				}
			}
		}
		return $actions;
	}


	static function inline_custom_fields( $text ) {
		global $post, $pagenow;
		if(	   ($pagenow=='edit.php' && isset($_GET['post_type']) && $_GET['post_type']=='wpematico' )
			|| ($pagenow=='admin-ajax.php' && isset($post) && $post->post_type=='wpematico' )) {
			$campaign_data = WPeMatico :: get_campaign ( $post->ID );
			/* Custom inline data for wpematico */
			$campaign_max = $campaign_data['campaign_max'];
			$campaign_feeddate = $campaign_data['campaign_feeddate'];
			$campaign_author = $campaign_data['campaign_author'];
			$campaign_linktosource = $campaign_data['campaign_linktosource'];
			$campaign_commentstatus = $campaign_data['campaign_commentstatus'];
			$campaign_allowpings = $campaign_data['campaign_allowpings'];
			$campaign_woutfilter = $campaign_data['campaign_woutfilter'];
			$campaign_strip_links = $campaign_data['campaign_strip_links'];
			$campaign_customposttype = $campaign_data['campaign_customposttype'];
			$campaign_posttype = $campaign_data['campaign_posttype'];
			$campaign_post_format = (isset($campaign_data['campaign_post_format']) && !empty($campaign_data['campaign_post_format']) ) ? $campaign_data['campaign_post_format'] : '0';
			$campaign_categories = $campaign_data['campaign_categories'];
			$campaign_tags = @$campaign_data['campaign_tags'];

			$text .= '</div>
					<div class="post_id">' . $post->ID . '</div>
					<div class="campaign_max">' . $campaign_max . '</div>
					<div class="campaign_feeddate">' . $campaign_feeddate . '</div>
					<div class="campaign_author">' . $campaign_author . '</div>
					<div class="campaign_linktosource">' . $campaign_linktosource . '</div>
					<div class="campaign_commentstatus">' . $campaign_commentstatus . '</div>
					<div class="campaign_allowpings">' . $campaign_allowpings . '</div>
					<div class="campaign_woutfilter">' . $campaign_woutfilter . '</div>
					<div class="campaign_strip_links">' . $campaign_strip_links . '</div>
					<div class="campaign_customposttype">' . $campaign_customposttype . '</div>
					<div class="campaign_posttype">' . $campaign_posttype . '</div>
					<div class="campaign_post_format">' . $campaign_post_format . '</div>
					<div class="campaign_categories">' . implode(',',$campaign_categories) . '</div>
					<div class="campaign_tags">' .  stripslashes($campaign_tags);
		}
		return $text;
	}


	static function set_edit_wpematico_columns($columns) { //this function display the columns headings
		return array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Campaign Name', 'wpematico'),
			'status' => __('Publish as', 'wpematico'),
			'campaign_type' => __('Campaign Type', 'wpematico'),
			'next' => __('Current State', 'wpematico'),
			'last' =>__('Last Run', 'wpematico'),
			'count' => __('Posts', 'wpematico'),
		);
	}
	
	static function custom_wpematico_column( $column, $post_id ) {
		global $post;
		$cfg = get_option( WPeMatico :: OPTION_KEY);
		$campaign_data = WPeMatico :: get_campaign ( $post_id );
		switch ( $column ) {
		  case 'aaaaaaaaaa_name':
			
//			$taxonomy_names = get_object_taxonomies( $campaign_customposttype );
//			foreach ( $taxonomy_names as $taxonomy_name) {
//				$taxonomy = get_taxonomy( $taxonomy_name );
//
//				if ( $taxonomy->hierarchical && $taxonomy->show_ui ) {
//
//					$terms = get_object_term_cache( $post->ID, $taxonomy_name );
//					if ( false === $terms ) {
//						$terms = wp_get_object_terms( $post->ID, $taxonomy_name );
//						wp_cache_add( $post->ID, $terms, $taxonomy_name . '_relationships' );
//					}
//					$term_ids = empty( $terms ) ? array() : wp_list_pluck( $terms, 'term_id' );
//
//					echo '<div class="post_category" id="' . $taxonomy_name . '_' . $post->ID . '">' . implode( ',', $campaign_categories ) . '</div>';
//
//				} elseif ( $taxonomy->show_ui ) {
//
//					echo '<div class="tags_input" id="'.$taxonomy_name.'_'.$post->ID.'">'
//						. esc_html( str_replace( ',', ', ', get_terms_to_edit( $post->ID, $taxonomy_name ) ) ) . '</div>';
//
//				}
//			}
			
			break;
		  case 'status':
			echo '<div id="campaign_posttype-' . $post_id . '" value="' . $campaign_data['campaign_posttype'] . '">' . get_post_type_object($campaign_data['campaign_customposttype'])->labels->singular_name . '<br />'; 
			echo '' . get_post_status_object($campaign_data['campaign_posttype'])->label . '</div>'; 
			break;
		  case 'campaign_type':
			$CampaignTypestr =  WPeMatico_Campaign_edit::get_campaign_type_by_field($campaign_data['campaign_type']);
			echo '<div class="center" id="campaign_type-' . $post_id . '" value="' . $campaign_data['campaign_type'] . '">' . str_replace( array(' (Default)','Fetcher'), '', $CampaignTypestr) . '</div>'; 
			break;
		  case 'count':
			$postscount = get_post_meta($post_id, 'postscount', true);
			echo (isset($postscount) && !empty($postscount) ) ? $postscount : $campaign_data['postscount']; 
			break;
		  case 'next':   // 'Current State' column
			$starttime = (isset($campaign_data['starttime']) && !empty($campaign_data['starttime']) ) ? $campaign_data['starttime'] : 0 ; 
			//print_r($campaign_data);
			$activated = (bool)$campaign_data['activated']; 
			$atitle = ( $activated ) ? __("Stop and deactivate this campaign", 'wpematico') : __("Start/Activate Campaign Scheduler", 'wpematico');
			if ($starttime>0) {  // Running play verde & grab rojo & stop gris
				$runtime=current_time('timestamp')-$starttime;
				if(($cfg['campaign_timeout'] <= $runtime) && ($cfg['campaign_timeout']>0)) {
					$campaign_data['lastrun'] = $starttime;
					$campaign_data['lastruntime'] = ' <span style="color:red;">Timeout: '.$cfg['campaign_timeout'].'</span>';
					$campaign_data['starttime']   = '';
					$campaign_data['lastpostscount'] = 0; //  posts procesados esta vez
					WPeMatico :: update_campaign($post_id, $campaign_data);  //Save Campaign new data
				}
				$ltitle = __('Running since:', 'wpematico' ).' '.$runtime.' '.__('sec.', 'wpematico' );
				$lbotones = "<span class='statebutton play green'></span>";
				if ($activated) { // Active play green & grab rojo & stop gris
					$lbotones.= "<span class='statebutton grab red'></span>"; // To stop
				} else {  // Inactive play verde & grab black & stop grey
					$lbotones.= "<a class='statebutton grab' href='".self :: wpematico_action_link( $post->ID , 'display','toggle')."' title='" . $atitle . "'></a>"; // To activate
				}
//				$lbotones.= "<span class='statebutton stop grey'></span>"; // To stop				
				$lbotones.= "<a class='statebutton stop' href='".self :: wpematico_action_link( $post->ID , 'display','clear')."' title='" . __("Break fetching and restore campaign", 'wpematico') . "'></a>"; // To activate
				
			}elseif ($activated) { // Running play gris & grab rojo & stop gris
				//$campaign_data['cronnextrun']= WPeMatico :: time_cron_next($campaign_data['cron']); //set next run, ver por que no actualizae el cron
//				$cronnextrun = $campaign_data['cronnextrun']; //get_post_meta($post_id, 'cronnextrun', true);
				$cronnextrun = WPeMatico :: time_cron_next($campaign_data['cron']);
				$cronnextrun = (isset($cronnextrun) && !empty($cronnextrun) && ($cronnextrun > 0 ) ) ? $cronnextrun : $campaign_data['cronnextrun']; 
				$ltitle =  __('Next Run:', 'wpematico' ).' '.date_i18n( get_option('date_format').' '. get_option('time_format'), $cronnextrun );
				$lbotones = "<a class='statebutton play' href='JavaScript:run_now({$post->ID});' title='" . esc_attr(__('Run Once', 'wpematico')) . "'></a>";// To run now
				$lbotones.= "<span class='statebutton grab red'></span>"; // To stop
				$lbotones.= "<a class='statebutton stop' href='".self :: wpematico_action_link( $post->ID , 'display','toggle')."' title='" . $atitle . "'></a>"; // To activate
				
			} else {  // Inactive play gris & grab gris & stop black
				$ltitle = __('Inactive', 'wpematico' );
				$lbotones = "<a class='statebutton play' href='JavaScript:run_now({$post->ID});' title='" . esc_attr(__('Run Once', 'wpematico')) . "'></a>";// To run now
				$lbotones.= "<a class='statebutton grab' href='".self :: wpematico_action_link( $post->ID , 'display','toggle')."' title='" . $atitle . "'></a>"; // To activate
				$lbotones.= "<span class='statebutton stop grey'></span>"; // To stop
				
			}
			echo "<div class='row-actions2' title='$ltitle'>$lbotones</div>";
			break;
		  case 'last':
			$lastrun = get_post_meta($post_id, 'lastrun', true);
			$lastrun = (isset($lastrun) && !empty($lastrun) ) ? $lastrun :  $campaign_data['lastrun']; 
			$lastruntime = (isset($campaign_data['lastruntime'])) ? $campaign_data['lastruntime'] : ''; 
			if ($lastrun) {
				echo date_i18n( get_option('date_format').' '. get_option('time_format'), $lastrun );
				if(isset($lastruntime) && !empty($lastruntime) ) {
					echo ' : '.__('Runtime:', 'wpematico' ).' <span id="lastruntime">'.$lastruntime.'</span> '.__('sec.', 'wpematico' );
				}
			} else {
				echo __('None', 'wpematico' );
			}
			$starttime = (isset($campaign_data['starttime']) && !empty($campaign_data['starttime']) ) ? $campaign_data['starttime'] : 0 ; 
			$activated = (bool)$campaign_data['activated']; 
			if ($starttime>0) {  // Running play verde & grab rojo & stop gris
				$runtime=current_time('timestamp')-$starttime;
				$ltitle = __('Running since:', 'wpematico' ).' '.$runtime.' '.__('sec.', 'wpematico' );
			}elseif ($activated) { // Running play gris & grab rojo & stop gris
				$cronnextrun = get_post_meta($post_id, 'cronnextrun', true);
				$cronnextrun = (isset($cronnextrun) && !empty($cronnextrun) && ($cronnextrun > 0 ) ) ? $cronnextrun : $campaign_data['cronnextrun']; 
				$ltitle = '<b>'. __('Next Run:', 'wpematico' ).'</b> '.date_i18n( get_option('date_format').' '. get_option('time_format'), $cronnextrun );
			} else {  // Inactive play gris & grab gris & stop black
				$ltitle = '';
			}
			echo "<div class=''>$ltitle</div>";
			break;
		}
	}

	// Make these columns sortable
	static function sortable_columns() {
	  return array(
		'title'      => 'title',
		'count'     => 'count',
		'next'     => 'next',
		'last'     => 'last'
	  );
	}
	
	public static function column_orderby($query ) {
		global $pagenow, $post_type;
		$orderby = $query->get( 'orderby');
		if( 'edit.php' != $pagenow || empty( $orderby ) || $post_type != 'wpematico' ) 	return;
		switch($orderby) {
			case 'count':
				$meta_group = array('key' => 'postscount','type' => 'numeric');
				$query->set( 'meta_query', array( 'sort_column'=>'count', $meta_group ) );
				$query->set( 'meta_key','postscount' );
				$query->set( 'orderby','meta_value_num' );

				break;
			case 'next':
				$meta_group = array('key' => 'cronnextrun','type' => 'numeric');
				$query->set( 'meta_query', array( 'sort_column'=>'next', $meta_group ) );
				$query->set( 'meta_key','cronnextrun' );
				$query->set( 'orderby','meta_value_num' );

				break;
			case 'last':
				$meta_group = array('key' => 'lastrun','type' => 'numeric');
				$query->set( 'meta_query', array( 'sort_column'=>'last', $meta_group ) );
				$query->set( 'meta_key','lastrun' );
				$query->set( 'orderby','meta_value_num' );

				break;

			default:
				break;
		}
	} 
	
	static function get_wpematico_categ_bulk_edit( $post_id, $post_type ) {
		$post_id = ( isset( $_POST[ 'post_id' ] ) && !empty( $_POST[ 'post_id' ] ) ) ? $_POST[ 'post_id' ] : $post_id;
		$post_type = ( isset( $_POST[ 'campaign_posttype' ] ) && !empty( $_POST[ 'campaign_posttype' ] ) ) ? $_POST[ 'campaign_posttype' ] : $post_type;
	}
	
	
	public static function wpematico_add_to_quick_edit_custom_box( $column_name, $post_type ) {
		
		$post = get_default_post_to_edit( $post_type );
		$post_type_object = get_post_type_object( 'post' );

		$taxonomy_names = get_object_taxonomies( 'post' );
		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomy_names as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );
			if ( !$taxonomy->show_ui )
				continue;

			if ( $taxonomy->hierarchical )
				$hierarchical_taxonomies[] = $taxonomy;
			else
				$flat_taxonomies[] = $taxonomy;
		}

		switch ( $post_type ) {
		case 'wpematico':
			switch( $column_name ) {
            case 'status':			
				    static $printNonce = TRUE;
					if ( $printNonce ) {
						$printNonce = FALSE;
						wp_nonce_field( plugin_basename( __FILE__ ), 'wpematico_edit_nonce' );
					}

					?>
				<fieldset class="" id="optionscampaign" style="display:none;">
					<div class="inline-edit-col">
					<h4><?php _e('Campaign Options', 'wpematico' ); ?></h4>
						<div class="inline-edit-group">
						<label class="alignleft">
							<span class="field-title"><?php _e('Max items to create on each fetch:', 'wpematico' ); ?></span>
							<span class="input-text">
								<input type="number" min="0" size="3" name="campaign_max" class="campaign_max small-text" value="">
							</span>
						</label>
						<label class="alignleft">
							<input type="checkbox" name="campaign_feeddate" value="1">
							<span class="checkbox-title"><?php _e('Use feed date', 'wpematico' ); ?></span>
						</label> 
						</div>
						<div class="inline-edit-group">						
						<label class="alignleft inline-edit-col">
							<span class="authortitle"><?php _e( 'Author:', 'wpematico' ); ?></span>
							<span class="input-text">
								<?php wp_dropdown_users(array('name' => 'campaign_author' ) ); ?>
							</span>
						</label>
						<label class="alignleft inline-edit-col">
							<span class="commenttitle"><?php _e( 'Discussion options:', 'wpematico' ); ?></span>
							<span class="input-text">
							<select class="campaign_commentstatus" name="campaign_commentstatus">
							<?php
								$options = array(
									'open' => __('Open', 'wpematico'),
									'closed' => __('Closed', 'wpematico'),
									'registered_only' => __('Registered only', 'wpematico')
								);
								foreach($options as $key => $value) {
									echo '<option value="' . esc_attr($key) . '">' . $value . '</option>';
								}
							?>
							</select>
							</span>
						</label>
							
						</div>
						<div class="inline-edit-group">
						<label class="alignleft">
							<input type="checkbox" name="campaign_allowpings" value="1">
							<span class="checkbox-title"><?php _e( 'Allow pings?', 'wpematico' ); ?>&nbsp;</span>
						</label>
						<label class="alignleft">
							<input type="checkbox" name="campaign_linktosource" value="1">
							<span class="checkbox-title"><?php _e( 'Post title links to source?', 'wpematico' ); ?>&nbsp;&nbsp;</span>
						</label>
						<label class="alignleft">
							<input type="checkbox" name="campaign_strip_links" value="1">
							<span class="checkbox-title"><?php _e( 'Strip links from content', 'wpematico' ); ?></span>
						</label>
						<br class="clear" />
						</div>
					</div>
				</fieldset>	
		
				<?php if ( !class_exists('WPeMaticoPRO') ) : ?>					
				<?php if ( count( $hierarchical_taxonomies ) ) : ?>					
				<fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">
					<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

					<span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
					<input type="hidden" name="<?php echo ( $taxonomy->name == 'category' ) ? 'post_category[]' : 'tax_input[' . esc_attr( $taxonomy->name ) . '][]'; ?>" value="0" />
					<ul class="cat-checklist <?php echo esc_attr( $taxonomy->name )?>-checklist">
						<?php wp_terms_checklist( null, array( 'taxonomy' => $taxonomy->name ) ) ?>
					</ul>

					<?php endforeach; //$hierarchical_taxonomies as $taxonomy ?>
					</div>
					
				</fieldset>
				<?php endif; // count( $hierarchical_taxonomies ) && !$bulk ?>

				<?php endif; // !class_exists('WPeMaticoPRO') ?>
				
				<?php if ( count( $flat_taxonomies ) ) : ?>
					<fieldset class="inline-edit-col-right">
						<div class="inline-edit-col">
					<?php foreach ( $flat_taxonomies as $taxonomy ) : ?>
						<?php if ( current_user_can( $taxonomy->cap->assign_terms ) ) : ?>
							<label class="inline-edit-tags">
								<span class="title"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
								<textarea cols="22" rows="1" name="campaign_tags" class="tax_input_<?php echo esc_attr( $taxonomy->name )?>"></textarea>
							</label>
						<?php endif; ?>
					<?php endforeach; //$flat_taxonomies as $taxonomy ?>
					
					<?php endif; // count( $flat_taxonomies ) && !$bulk  ?>
					
						<div class="inline-edit-radiosbox">
							<label>
								<span class="title"><?php _e('Post type',  'wpematico' ); ?></span>
								<br/>
								<span class="input-text"> <?php
									$args=array(
									  'public'   => true
									); 
									$output = 'names'; // names or objects, note names is the default
									$operator = 'and'; // 'and' or 'or'
									$post_types=get_post_types($args,$output,$operator); 
									foreach ($post_types  as $posttype ) {
										if ($posttype == 'wpematico') continue;
										echo '<label><input type="radio" name="campaign_customposttype" value="'. $posttype. '" id="customtype_'. $posttype. '" /> '. $posttype. '</label>';
									} ?>
								</span>
							</label>
						</div>
						<div class="inline-edit-radiosbox">
							<label>
								<span class="title"><?php _e('Status',  'wpematico' ); ?></span>
								<br/>
								<span class="input-text">
									<label><input type="radio" name="campaign_posttype" value="publish" /> <?php _e('Published'); ?></label>
									<label><input type="radio" name="campaign_posttype" value="private" /> <?php _e('Private'); ?></label>
									<label><input type="radio" name="campaign_posttype" value="pending" /> <?php _e('Pending'); ?></label>
									<label><input type="radio" name="campaign_posttype" value="draft" /> <?php _e('Draft'); ?></label>
								</span>
							</label>
						</div>
					<?php if ( current_theme_supports( 'post-formats' ) ) :
							$post_formats = get_theme_support( 'post-formats' );
							?>
						<div class="inline-edit-radiosbox qedscroll">
							<label>
								<span class="title" style="width: 100%;"><?php _e('Post Format',  'wpematico' ); ?></span>
								<br/>
								<span class="input-text"> <?php
									if ( is_array( $post_formats[0] ) ) :
										global $post, $campaign_data;
										$campaign_post_format = (!isset($campaign_post_format) || empty($campaign_post_format) ) ? '0' : $campaign_data['campaign_post_format'];
									?>
									<div id="post-formats-select">
										<label><input type="radio" name="campaign_post_format" class="post-format" id="post-format-0" value="0" /> <?php echo get_post_format_string( 'standard' ); ?></label>
										<?php foreach ( $post_formats[0] as $format ) : ?>
											<label><input type="radio" name="campaign_post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" /> <?php echo esc_html( get_post_format_string( $format ) ); ?></label>
										<?php endforeach; ?>
									</div>
									<?php endif; ?>
								</span>
							</label>
						</div>
					<?php endif; ?>
					</div>
					</fieldset><?php				
				break;

			case 'title': // No entra en title		
				break;
            case 'others':
/*               ?><fieldset class="inline-edit-col-right">
                  <div class="inline-edit-col">
                     <label>
                        <span class="title">Release Date</span>
                        <input type="text" name="next" value="" />
                     </label>
                  </div>
               </fieldset><?php
*/               break;
			}
			break;  //		case 'wpematico'

		}
	}

	
	static function save_quick_edit_post($post_id) {
		//wp_die('save_quick_edit_post'.print_r($_POST,1));
	    $slug = 'wpematico';
		if ( !isset($_POST['post_type']) || ( $slug !== $_POST['post_type'] ) ) return $post_id; 
		if ( !current_user_can( 'edit_post', $post_id ) ) 	return $post_id;
		$_POST += array("{$slug}_edit_nonce" => '');
		if ( !wp_verify_nonce( $_POST["{$slug}_edit_nonce"],  plugin_basename( __FILE__ ) ) ) {	wp_die('No verify nonce' /* .print_r($_POST,1) */ ); return;	}

		$nivelerror = error_reporting(E_ERROR | E_WARNING | E_PARSE);

		$campaign = WPeMatico :: get_campaign ($post_id);
		$posdata  = $_POST; //apply_filters('wpematico_check_campaigndata', $_POST );
		//parse disabled checkfields that dont send any data
		$posdata['campaign_feeddate']	= (!isset($posdata['campaign_feeddate']) || empty($posdata['campaign_feeddate'])) ? false: ($posdata['campaign_feeddate']==1) ? true : false;
		$posdata['campaign_allowpings']=(!isset($posdata['campaign_allowpings']) || empty($posdata['campaign_allowpings'])) ? false: ($posdata['campaign_allowpings']==1) ? true : false;
		$posdata['campaign_linktosource']=(!isset($posdata['campaign_linktosource']) || empty($posdata['campaign_linktosource'])) ? false: ($posdata['campaign_linktosource']==1) ? true : false;
		$posdata['campaign_strip_links']=(!isset($posdata['campaign_strip_links']) || empty($posdata['campaign_strip_links'])) ? false: ($posdata['campaign_strip_links']==1) ? true : false;
		
		$campaign = array_merge($campaign, $posdata);
		
		$campaign = apply_filters('wpematico_check_campaigndata', $campaign );

		error_reporting($nivelerror);
		
		WPeMatico :: update_campaign($post_id, $campaign);
		
		return $post_id ;	
	}
	
	
	
	/**
	 * Saving the 'Bulk Edit' data is a little trickier because we have
	 * to get JavaScript involved. WordPress saves their bulk edit data
	 * via AJAX so, guess what, so do we.
	 *
	 * Your javascript will run an AJAX function to save your data.
	 * This is the WordPress AJAX function that will handle and save your data.
	 */
	function manage_wpematico_save_bulk_edit() {
		// we need the post IDs
		$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;		
		// if we have post IDs
		if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
			$arrayData = array();
			// text or number fields
			if ($_POST['campaign_max']) {
				$arrayData['campaign_max'] = $_POST['campaign_max'];
			}

			// check box or select field
			$checkBoxFieldsName = array('campaign_author', 'campaign_feeddate', 'campaign_commentstatus', 'campaign_allowpings', 'campaign_linktosource', 'campaign_strip_links');
			foreach( $checkBoxFieldsName as $field ) {
				$arrayData[$field] = $_POST[$field];
			}

			// taxonomies
			if (isset($_POST['post_category']) && is_array($_POST['post_category'])) {
				$arrayData['post_category'] = $_POST['post_category'];
			}

			// update for each post ID
			foreach( $post_ids as $post_id ) {
				$campaign = WPeMatico :: get_campaign ($post_id);
				foreach ($arrayData as $key => $dataEntry) {
					$campaign[$key] = $dataEntry;
				}

				$campaign = apply_filters('wpematico_check_campaigndata', $campaign);
				if(has_filter('wpematico_presave_campaign')) $campaign = apply_filters('wpematico_presave_campaign', $campaign);
				
				// Grabo la campaña
				WPeMatico :: update_campaign($post_id, $campaign);
			}			
		}
	}	
}  // class
?>