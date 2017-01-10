<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
* Retrieve tools tabs
* @since       1.2.4
* @return      array
*/
function wpematico_get_settings_tabs() {
	$tabs                  = array();
	$tabs['settings']      = __( 'Settings', 'wpematico' );
	//allways Licenses and debug file at end
	$tabs = apply_filters( 'wpematico_settings_tabs', $tabs );
	$tabs['pro_licenses']   = __( 'Licenses', 'wpematico' );
	$tabs['debug_info']   = __( 'Debug Info', 'wpematico' );

	return $tabs;
}


function wpematico_settings_page () {
	global $pagenow, $wp_roles, $current_user;			
	//$cfg = get_option(WPeMatico :: OPTION_KEY);
	$current_tab = (isset($_GET['tab']) ) ? $_GET['tab'] : 'settings' ;
	$tabs = wpematico_get_settings_tabs();

	?>
		<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $tabs as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

//				$tab_url = remove_query_arg( array(
//					'wpematico-message'
//				), $tab_url );

				$active = $current_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . ( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'wpematico_settings_tab_' . $current_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
	<?php

}

add_action( 'wpematico_settings_tab_pro_licenses', 'wpematicopro_licenses' );
function wpematicopro_licenses(){
	?>
	<div id="licenses">
		<div class="postbox ">
		<div class="inside">
		<?php	/*** Display license page */
		settings_errors();
		if(!has_action('wpempro_licenses_forms')) {
			echo '<div class="msg"><p>', __('This is where you would enter the license keys for one of our premium plugins, should you activate one.', 'wpematico'), '</p>';
			echo '<p>', __('See some of the WPeMatico Add-ons in the', 'wpematico'), ' <a href="', admin_url( 'plugins.php?page=wpemaddons').'">Extensions list</a>.</p></div>';
		}else {
			do_action('wpempro_licenses_forms');
		}
		?>
		</div>
		</div>
	</div>
	<?php
}


function wpematico_settings_head() {
	?>		
	<style type="text/css">
		.insidesec {display: inline-block; vertical-align: top;}
	</style>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function($){
			$('.handlediv').click(function() { 
				$(this).parent().toggleClass('closed');
			});
		});	
	</script>
	<?php
}

add_action( 'wpematico_settings_tab_settings', 'wpematico_settings' );
function wpematico_settings(){
	global $cfg, $current_screen, $helptip;
	$cfg = get_option(WPeMatico :: OPTION_KEY);
	$cfg = apply_filters('wpematico_check_options', $cfg);  

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
	$simplepie = new SimplePie();
	$cfg['strip_htmltags']	= (!($cfg['simplepie_strip_htmltags'])) ? implode(',',$simplepie->strip_htmltags): $cfg['strip_htmltags'];
	$cfg['strip_htmlattr']	= (!($cfg['simplepie_strip_attributes'])) ? implode(',', $simplepie->strip_attributes) : $cfg['strip_htmlattr'];
	$cfg['mailsndemail']	= (!($cfg['mailsndemail']) || empty($cfg['mailsndemail']) ) ? 'noreply@'.str_ireplace('www.', '', parse_url(get_option('siteurl'), PHP_URL_HOST)) : $cfg['mailsndemail'];
	$cfg['mailsndname']		= (!($cfg['mailsndname']) or empty($cfg['mailsndname']) ) ? 'WPeMatico Log' : $cfg['mailsndname'];
	//$cfg['mailpass']		= (!($cfg['mailpass']) or empty($cfg['mailpass']) ) ? '' : bas 64_ d co d ($cfg['mailpass']);

	$helptip=wpematico_helpsettings('tips')
	
	?>
	<div class="wrap2">
		<h2><?php _e( 'WPeMatico settings', 'wpematico' );?></h2>
		<div id="poststuff" class="metabox-holder has-right-sidebar">
			<form method="post" action="" autocomplete="off" >
			<?php  wp_nonce_field('wpematico-settings'); ?>
			<div id="side-info-column" class="inner-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox inside">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="handle"><?php _e( 'About', 'wpematico' );?></h3>
						<div class="inside">
							<p id="left1" onmouseover="jQuery(this).css('opacity',0.9);" onmouseout="jQuery(this).css('opacity',0.5);" style="text-align:center;opacity: 0.5;"><a href="http://www.wpematico.com" target="_Blank" title="Go to new WPeMatico WebSite"><img style="width: 100%;" src="<?php echo WPeMatico :: $uri ; ?>/images/icon-512x512.jpg" title=""></a><br />
								<b>WPeMatico Free Version <?php echo WPeMatico :: $version ; ?></b></p>
							<p><?php _e( 'Thanks for test, use and enjoy this plugin.', 'wpematico' );?></p>
							<p></p>
							<p><?php _e( 'If you like it and want to thank, you can write a 5 star review on Wordpress.', 'wpematico' );?></p>
							<style type="text/css">#linkrate:before { content: "\2605\2605\2605\2605\2605";font-size: 18px;}
							#linkrate { font-size: 18px;}</style>
							<p style="text-align: center;">
								<a href="https://wordpress.org/support/view/plugin-reviews/wpematico?filter=5&rate=5#postform" id="linkrate" class="button" target="_Blank" title="Click here to rate plugin on Wordpress">  Rate </a>
							</p>
							<p></p>
							<p style="text-align: center;"><?php _e( 'Also you can donate a few dollars', 'wpematico' );?>
								<input type="button" class="button-secondary" name="donate" value="<?php _e( 'Click to Donate', 'wpematico' );?>" onclick="javascript:window.open('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B8V39NWK3NFQU');return false;"/>
							</p>
							<p></p>
							<p style="text-align: center;">
								<input type="button" class="button-primary" name="buypro" value="<?php _e( 'Buy PRO version online', 'wpematico' );?>" onclick="javascript:window.open('https://etruel.com/downloads/wpematico-pro/');return false;"/>
							</p>
							<p></p>
						</div>
					</div>
					<div class="postbox">						
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="handle"><?php _e( 'Sending e-Mails', 'wpematico' );?></h3>
						<div class="inside">
							<p><b><?php _e('Sender Email:', 'wpematico' ); ?></b><br /><input name="mailsndemail" id="mailsndemail" type="text" value="<?php echo $cfg['mailsndemail'];?>" class="large-text" /><span id="mailmsg"></span></p>
							<p><b><?php _e('Sender Name:', 'wpematico' ); ?></b><br /><input name="mailsndname" type="text" value="<?php echo $cfg['mailsndname'];?>" class="large-text" /></p>
							<input type="hidden" name="mailmethod" value="<?php echo $cfg['mailmethod']; // "mailmethod"="mail" or "mailmethod"="SMTP"  ?>">
							<label id="mailsendmail" <?php if ($cfg['mailmethod']!='Sendmail') echo 'style="display:none;"';?>><b><?php _e('Sendmail Path:', 'wpematico' ); ?></b><br /><input name="mailsendmail" type="text" value="<?php echo $cfg['mailsendmail'];?>" class="large-text" /><br /></label>
						</div>
					</div>

					<div class="postbox inside">
						<div class="inside">
							<p>
							<input type="hidden" name="wpematico-action" value="save_settings" />
							<?php submit_button( __( 'Save settings', 'wpematico' ), 'primary', 'wpematico-save-settings', false ); ?>
							</p>
						</div>
					</div>
					<div class="postbox inside">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h3 class="handle"><?php _e( 'Advanced', 'wpematico' );?></h3>
						<div class="inside">
							<p></p>
							<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['disablecheckfeeds'],true); ?> name="disablecheckfeeds" id="disablecheckfeeds" /> <?php _e('Disable <b><i>Check Feeds before Save</i></b>', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['disablecheckfeeds']; ?>"></span>
							<p></p>
							<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['enabledelhash'],true); ?> name="enabledelhash" id="enabledelhash" /><b>&nbsp;<?php _e('Enable <b><i>Del Hash</i></b>', 'wpematico' ); ?></b></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enabledelhash']; ?>"></span>
							<p></p>
							<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['enableseelog'],true); ?> name="enableseelog" id="enableseelog" /><b>&nbsp;<?php _e('Enable <b><i>See last log</i></b>', 'wpematico' ); ?></b></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enableseelog']; ?>"></span>
							<p></p>
							<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['disable_credits'],true); ?> name="disable_credits" id="disable_credits" /><b>&nbsp;<?php _e('Disable <i>WPeMatico Credits</i>', 'wpematico' ); ?></b> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['disable_credits']; ?>"></span>
							<span id="discredits" style="<?php echo ($cfg['disable_credits'])?'':'display:none;' ?>"><br /><?php 
							printf( __('If you can\'t show the WPeMatico credits in your posts, I really appreciate if you can take a minute to %s write a 5 star review on Wordpress %s. :) thanks.', 'wpematico' ),
								'<a href="https://wordpress.org/support/view/plugin-reviews/wpematico?filter=5&rate=5#postform" target="_Blank" title="Open a new window">',
								'</a>'); 
							?></span>
							<p></p>
						</div>
					</div>

					<div class="postbox inside">
						<h3 class="handle"><?php _e( 'About PRO', 'wpematico' );?></h3>
						<div class="inside">
							<p id="left1" onmouseover="jQuery(this).css('opacity',0.9);this.style.backgroundColor='#111'" onmouseout="jQuery(this).css('opacity',0.5);this.style.backgroundColor='#fff'" style="text-align:center;opacity: 0.5;border-radius: 14px 14px 0 0;"><a href="https://etruel.com/downloads/wpematico-pro/" target="_Blank" title="Go to etruel WebSite"><img style="width: 100%;" src="https://etruel.com/wp-content/uploads/2016/04/etruelcom2016_250x120.png" title=""></a><br />
							WPeMatico PRO Features</p>
						</div>
					</div>
					<div class="postbox inside">
						<h3 class="handle"><?php _e( 'The Perfect Package', 'wpematico' );?></h3>
						<div class="inside">
							<p id="left1" onmouseover="jQuery(this).css('opacity',0.9);this.style.backgroundColor='#111'" onmouseout="jQuery(this).css('opacity',0.5);this.style.backgroundColor='#fff'" style="text-align:center;opacity: 0.5;border-radius: 14px 14px 0 0;"><a href="https://etruel.com/downloads/wpematico-perfect-package/" target="_Blank" title="Go to etruel WebSite"><img style="width: 100%;" src="https://etruel.com/wp-content/uploads/edd/2016/09/wpematico_package_1024x512-300x150.png" title=""></a><br />
							WPeMatico The Perfect Package</p>
						</div>
					</div>
					<?php do_action('wpematico_wp_ratings'); ?>
				</div>
				<?php //include( WPeMatico :: $dir . 'myplugins.php');	?>
			</div>

			<div id="post-body">
				<div id="post-body-content">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

				<div id="imgs" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
					<h3 class="hndle"><span><?php _e('Global Settings for Images', 'wpematico' ); ?></span></h3>
					<div class="inside">
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['imgcache'],true); ?> name="imgcache" id="imgcache" />&nbsp;<b><label for="imgcache"><?php _e('Cache Images.', 'wpematico' ); ?></label></b><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['imgcache']; ?>"></span>
						<div id="nolinkimg" style="padding-left:20px; <?php if (!$cfg['imgcache']) echo 'display:none;';?>">
							<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['imgattach'],true); ?> name="imgattach" id="imgattach" /><b>&nbsp;<label for="imgattach"><?php _e('Attach Images to posts.', 'wpematico' ); ?></label></b><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['imgattach']; ?>"></span>
							<br/>
							<input name="gralnolinkimg" id="gralnolinkimg" class="checkbox" value="1" type="checkbox" <?php checked($cfg['gralnolinkimg'],true); ?> /><label for="gralnolinkimg"><?php _e('No link to source images', 'wpematico' ); ?></label><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['gralnolinkimg']; ?>"></span>
						</div>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['featuredimg'],true); ?> name="featuredimg" id="featuredimg" /><b>&nbsp;<label for="featuredimg"><?php _e('Enable first image found on content as Featured Image.', 'wpematico' ); ?></label></b><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['featuredimg']; ?>"></span>
						<br />
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['rmfeaturedimg'],true); ?> name="rmfeaturedimg" id="rmfeaturedimg" /><b>&nbsp;<label for="rmfeaturedimg"><?php _e('Remove Featured Image from content.', 'wpematico' ); ?></label></b> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['rmfeaturedimg']; ?>"></span>
						<p></p>
						<div id="custom_uploads" style="<?php if (!$cfg['imgcache'] && !$cfg['featuredimg']) echo 'display:none;';?>">
							<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['customupload'],true); ?> name="customupload" id="customupload" /><b>&nbsp;<label for="customupload"><?php _e('Custom function for uploads.', 'wpematico' ); ?></label></b><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['customupload']; ?>"></span>
							<br/>
						</div>
						<?php do_action('wpematico_settings_images',$cfg); ?>
					</div>
				</div>

				<div id="enablefeatures" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle"><span><?php _e('Enable Features', 'wpematico' ); ?></span><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablefeatures']; ?>"></span></h3>
					<div class="inside"> 
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['enablerewrite'],true); ?> name="enablerewrite" id="enablerewrite" /> <label for="enablerewrite"><?php _e('Enable <b><i>Rewrite</i></b> feature', 'wpematico' ); ?></label>
						<span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablerewrite']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['enableword2cats'],true); ?> name="enableword2cats" id="enableword2cats" /> <label for="enableword2cats"><?php _e('Enable <b><i>Words to Categories</i></b> feature', 'wpematico' ); ?></label>
						<span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enableword2cats']; ?>"></span>
						<p></p>

						<?php if( ! wpematico_is_pro_active() ) : ?>

					</div>
				</div>

				<div id="PROfeatures" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 style="float:right; background-color: yellow;"><?php _e('ONLY AVAILABLE AT PRO VERSION.', 'wpematico' ); ?></h3>
					<h3 class="hndle" style="background-color: yellow;"><span><?php _e('PRO Features', 'wpematico' ); ?></span> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['PROfeatures']; ?>"></span></h3>
					<div class="inside"> 
							<!-- a href="https://etruel.com/downloads/wpematico-pro/" target="_Blank" title="Go to WPeMatico WebSite"><img style="background: transparent;height: 86%;position: absolute;margin-left: -10px;overflow: hidden;width: 100%;border: 1px solid #CCC;" src="<?php echo WPeMatico :: $uri; ?>images/onlypro.png" title=""></a -->
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Keyword Filtering</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablekwordf']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Word count Filters</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablewcf']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Custom Title</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablecustomtitle']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable attempt to <b><i>Get Full Content</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['fullcontent']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Author per feed</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['authorfeed']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Import feed list</i></b> feature', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['importfeeds']; ?>"></span>
						<p></p>
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Auto Tags</i></b> feature.', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enabletags']; ?>"></span>
						<div id="badtags" style="margin-left:25px;">
						<b><label for="all_badtags"><?php _e('Bad Tags that will be not used on any post:', 'wpematico' ); ?></label></b><br />
						<textarea style="width:500px;" disabled >some, tags, not, allowed</textarea><br />
						<?php echo __('Enter comma separated list of excluded Tags in all campaigns.', 'wpematico' ); ?>
						</div><br />
						<input class="checkbox" value="1" type="checkbox" disabled /> <?php _e('Enable <b><i>Custom Fields</i></b> feature.', 'wpematico' ); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['enablecfields']; ?>"></span>

						<?php endif; ?>
					</div>
				</div>

				<div id="advancedfetching" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle"><span><?php _e('Advanced Fetching', 'wpematico' ); ?> <?php _e('(SimplePie Settings)', 'wpematico' ); ?></span></h3>
					<div class="inside">
						<p><b><?php _e('Test if SimplePie library works well on your server:', 'wpematico' ); ?></b>
							<a onclick="javascript:window.open(
								'<?php echo WPeMatico :: $uri; ?>app/lib/sp_compatibility_test.php'
								,'SimplePie',
								'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=630, height=600'); return false;" 
								href="javascript:Void(0);">	<?php _e('Click here', 'wpematico' ); ?></a>. <small> <?php _e('(open in popup)', 'wpematico' ); ?></small>
						</p>
						<p></p>
						<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['force_mysimplepie'],true); ?> name="force_mysimplepie" id="force_mysimplepie" /> <?php _e('Force <b><i>Custom Simplepie Library</i></b>', 'wpematico' ); ?></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['mysimplepie']; ?>"></span>
						<p></p>
						<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['set_stupidly_fast'],true); ?> name="set_stupidly_fast" id="set_stupidly_fast"  onclick="jQuery('#simpie').show();"  /> <?php _e('Set Simplepie <b><i>stupidly fast</i></b>', 'wpematico' ); ?></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['stupidly_fast']; ?>"></span>
						<p></p>
						<div id="simpie" style="margin-left: 25px;<?php if ($cfg['set_stupidly_fast']) echo 'display:none;';?>">
							<input name="simplepie_strip_htmltags" id="simplepie_strip_htmltags" class="checkbox" value="1" type="checkbox" <?php checked($cfg['simplepie_strip_htmltags'],true); ?> />
							<label for="simplepie_strip_htmltags"><b><?php _e('Change SimplePie HTML tags to strip', 'wpematico' ); ?></b></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['strip_htmltags']; ?>"></span>
							<br />
							<textarea style="width:500px;" <?php disabled($cfg['simplepie_strip_htmltags'],false,true); ?> name="strip_htmltags" id="strip_htmltags" ><?php echo $cfg['strip_htmltags'] ; ?></textarea>
							<p></p>
							<input name="simplepie_strip_attributes" id="simplepie_strip_attributes" class="checkbox" value="1" type="checkbox" <?php checked($cfg['simplepie_strip_attributes'],true); ?> />
							<label for="simplepie_strip_attributes"><b><?php _e('Change SimplePie HTML attributes to strip', 'wpematico' ); ?></b></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['strip_htmlattr']; ?>"></span>
							<br />
							<textarea style="width:500px;" <?php disabled($cfg['simplepie_strip_attributes'],false,true); ?> name="strip_htmlattr" id="strip_htmlattr" ><?php echo $cfg['strip_htmlattr']; ?></textarea>
						</div>
						<p></p>

						</div>
				</div>

				<div id="advancedfetching" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle"><span><?php _e('Advanced Fetching', 'wpematico' ); ?></span></h3>
					<div class="inside">
						<p></p>
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['woutfilter'],true); ?> name="woutfilter" id="woutfilter" /> <?php _e('<b><i>Allow option on campaign to skip the content filters</i></b>', 'wpematico' ); ?><br />
						<div id="hlpspl" style="padding-left:20px;">
							<?php _e('NOTE: It is extremely dangerous to allow unfiltered content.', 'wpematico' ); ?><br />
						</div> 
						<p></p>
						<p><b><?php _e('Timeout running campaign:', 'wpematico' ); ?></b> <input name="campaign_timeout" type="number" min="0" value="<?php echo $cfg['campaign_timeout'];?>" class="small-text" /> <?php _e('Seconds.', 'wpematico' ); ?>
						<span id="hlpspl" style="padding-left:20px;display: inline-block;">
							<?php _e('When a campaign running is interrupted, cannot be executed again until click "Clear Campaign".  This option clear campaign after this timeout then can run again on next scheduled cron. A value of "0" ignore this, means that remain until user make click.  Recommended 300 Seconds.', 'wpematico' ); ?>
						</span></p>
						<p></p>
						<label for="throttle"><b><?php _e('Add a throttle/delay in seconds after every post.', 'wpematico' ); ?></b></label> <input name="throttle" id="throttle" class="small-text" min="0" type="number" value="<?php echo $cfg['throttle']; ?>" /> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['throttle']; ?>"></span>

						<p></p>
						<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['allowduplicates'],true); ?> name="allowduplicates" id="allowduplicates" /><b>&nbsp;<?php echo '<label for="allowduplicates">' . __('Deactivate duplicate controls.', 'wpematico' ) . '</label>'; ?></b>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['allowduplicates']; ?>"></span>
						<br>
						<div id="enadup" style="padding-left:20px; <?php if (!$cfg['allowduplicates']) echo 'display:none;';?>">
							<small><?php _e('NOTE: If disable both controls, all items will be fetched again and again... and again, ad infinitum.  If you want allow duplicated titles, just activate "Allow duplicated titles".', 'wpematico' ); ?></small><br />
							<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['allowduptitle'],true); ?> name="allowduptitle" id="allowduptitle" /><b>&nbsp;<?php echo '<label for="allowduptitle">' . __('Allow duplicates titles.', 'wpematico' ) . '</label>'; ?></b><br />
							<input class="checkbox" value="1" type="checkbox" <?php checked($cfg['allowduphash'],true); ?> name="allowduphash" id="allowduphash" /><b>&nbsp;<?php echo '<label for="allowduphash">' . __('Allow duplicates hashes. (Not Recommended)', 'wpematico' ) . '</label>'; ?></b>
						</div>
						<p></p>
						<input name="jumpduplicates" id="jumpduplicates" class="checkbox" value="1" type="checkbox" <?php checked($cfg['jumpduplicates'],true); ?> />
						<label for="jumpduplicates"><b><?php _e('Continue Fetching if found duplicated items.', 'wpematico' ); ?></b></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['jumpduplicates']; ?>"></span>
						<p></p>
						<input name="disableccf" id="disableccf" class="checkbox" value="1" type="checkbox" <?php checked($cfg['disableccf'],true); ?> />
						<label for="disableccf"><b><?php _e('Disables Plugin Custom fields.', 'wpematico' ); ?></b></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['disableccf']; ?>"></span>
						<br /> 

					</div>
				</div>

				<div id="disablewpcron" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle"><span><?php _e('Cron and Scheduler Settings', 'wpematico' ); ?></span></h3>
					<div class="inside">
						<label><input class="checkbox" id="dontruncron" type="checkbox"<?php checked($cfg['dontruncron'],true);?> name="dontruncron" value="1"/> 
							<strong><?php _e('Disable WPeMatico schedulings', 'wpematico' ); ?></strong></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['dontruncron']; ?>"></span>
						<br />
						<?php $croncode = ($cfg['set_cron_code']) ? '?code=' . $cfg['cron_code'] : ''; ?>
						<div id="hlpcron" style="padding-left:20px;">
							<?php _e('You must set up a cron job that calls:', 'wpematico' ); ?><br />
							<span class="coderr b"><i> php -q <?php echo WPeMatico :: $dir . "app/wpe-cron.php".$croncode; ?></i></span><br />
							<?php _e('or URL:', 'wpematico' ); ?> &nbsp;&nbsp;&nbsp;<span class="coderr b"><i><?php echo WPeMatico :: $uri . "app/wpe-cron.php".$croncode; ?></i></span>
							<br /><br />
							<label><input class="checkbox" id="set_cron_code" type="checkbox"<?php checked($cfg['set_cron_code'],true);?> name="set_cron_code" value="1"/> 
								<strong><?php _e('Set a password to access the external CRON', 'wpematico' ); ?></strong></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['set_cron_code']; ?>"></span>
								<br /> 
								<label style="padding-left:20px;">
									<?php _e('Type the password to use the external CRON', 'wpematico' ); ?>: 
									<input type="hidden" id="autocode" value="<?php echo substr(md5(time()), 0, 8);?>"/> 
									<a style="font-size: 2.2em;" title="<?php _e('Paste a generated a ramdon string.'); ?>" class='dashicons dashicons-migrate' onclick="Javascript: jQuery('#cron_code').val( jQuery('#autocode').val() );" > &nbsp;&nbsp;</a> &nbsp;
									<input name="cron_code" title="<?php _e('See text.'); ?>" id="cron_code" type="text" value="<?php echo $cfg['cron_code'];?>" class="standard-text" /> 
									<?php /*<a class='dashicons dashicons-visibility' onclick="Javascript: jQuery('#cron_code').prop('type','text');" ></a>*/ ?>
								</label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['cron_code']; ?>"></span>
						</div>
						<br /> 

						<label><input class="checkbox" id="disablewpcron" type="checkbox"<?php checked($cfg['disablewpcron'],true);?> name="disablewpcron" value="1"/> 
							<strong><?php _e('Disable all WP_Cron', 'wpematico' ); ?></strong></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['disablewpcron']; ?>"></span>
						<div id="hlpcron2" style="padding-left:20px;">
							<?php _e('To run the wordpress cron with external cron you can set up a cron job that calls:', 'wpematico' ); ?><br />
							<span class="coderr b"><i> php -q <?php echo ABSPATH.'wp-cron.php'; ?></i></span><br /> 
							<?php _e('or URL:', 'wpematico' ); ?> &nbsp;&nbsp;&nbsp;<span class="coderr b"><i><?php echo trailingslashit(get_option('siteurl')).'wp-cron.php'; ?></i></span>
							<br /> 
							<div class="mphlp" style="margin-top: 10px;">
								<?php _e('This set <code>DISABLE_WP_CRON</code> to <code>true</code>, then the <a href="https://core.trac.wordpress.org/browser/tags/4.2.3/src/wp-includes/cron.php#L314" target="_blank">current cron process should be killed</a>.', 'wpematico' ); ?>
								<br /> 
								<?php _e('You can find more info about WP Cron and also few steps to configure external crons:', 'wpematico' ); ?>
								<a href="http://code.tutsplus.com/articles/insights-into-wp-cron-an-introduction-to-scheduling-tasks-in-wordpress--wp-23119" target="_blank"><?php _e('here', 'wpematico' ); ?></a>.
							</div>
						</div><br /> 

						<label><input class="checkbox" id="logexternalcron" type="checkbox"<?php checked($cfg['logexternalcron'],true);?> name="logexternalcron" value="1"/> 
							<strong><?php _e('Log file for external Cron', 'wpematico' ); ?></strong></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['logexternalcron']; ?>"></span>
							<br /> 
					</div>
				</div>				

				<div id="emptytrashdiv" class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle"><span><?php _e('Other Tools', 'wpematico' ); ?></span></h3>
					<div class="inside">
					<div class="insidesec" style="border-right: 1px lightgrey solid; margin-right: 5px;padding-right: 7px; ">
						<label><input class="checkbox" id="emptytrashbutton" type="checkbox"<?php checked($cfg['emptytrashbutton'],true);?> name="emptytrashbutton" value="1"/> 
						<?php _e('Shows Button to empty trash on lists.', 'wpematico' ); ?></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['emptytrashbutton']; ?>"></span>
						<br />
						<?php _e('Select (custom) post types you want.', 'wpematico' ); ?>
						<br />
						<div id="hlptrash" style="padding-left:20px; <?php if (!$cfg['emptytrashbutton']) echo 'display:none;';?>">
						<?php
							// publicos y privados para que pueda mostrar el boton en todos
							$args=array( 'public'   => false );
							$args=array( );
							$output = 'names'; // names or objects
							$output = 'objects'; // names or objects
							$cpostypes = $cfg['cpt_trashbutton'];
							//unset($cpostypes['attachment']);
							$post_types=get_post_types($args,$output);
							foreach ($post_types  as $post_type_obj ) {
								$post_type = $post_type_obj->name;
								$post_label = $post_type_obj->labels->name;
								if ($post_type=='revision') continue;  // ignore 'attachment'
								if ($post_type=='nav_menu_item') continue;  // ignore 'attachment'
								echo '<div><input type="checkbox" class="checkbox" name="cpt_trashbutton['.$post_type.']" value="1" '; 
								if(!isset($cpostypes[$post_type])) $cpostypes[$post_type] = false;
								checked( $cpostypes[$post_type],true);
								echo ' /> '. __( $post_label ) .' ('. __( $post_type ) .')</div>';
							}
						?>
						</div><br /> 
					</div>
					<div id="enabledashboard" class="insidesec">

						<label><input class="checkbox" value="1" type="checkbox" <?php checked($cfg['disabledashboard'],true); ?> name="disabledashboard" id="disabledashboard" /> <?php _e('Disable <b><i>WP Dashboard Widget</i></b>', 'wpematico' ); ?></label><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['disabledashboard']; ?>"></span>
							<div>
								<label id="roleslabel" <?php if ($cfg['disabledashboard']) echo 'style="display:none;"';?>><?php _e('User roles to show Dashboard widget:', 'wpematico' ); ?></label>
								<div id="roles" <?php if ($cfg['disabledashboard']) echo 'style="display:none;"';?>>
								<?php
									global $wp_roles;
									if(!isset($cfg['roles_widget'])) $cfg['roles_widget'] = array( "administrator" => "administrator" );
									$role_select = '<input type="hidden" name="role_name[]" value="administrator" />';
									foreach( $wp_roles->role_names as $role => $name ) {
										$name = _x($name, 'wpematico' );
										if ( $role != 'administrator' ) {
											if ( array_search($role, $cfg['roles_widget']) ) {
												$checked = 'checked="checked"';
											}else{
												$checked = '';
											}
										  $role_select .= '<label style="margin:0 5px;"><input style="margin:0 5px;" ' . $checked . ' type="checkbox" name="role_name[]" value="'.$role .'" />'. $name . '</label>';
										}
									}
									echo $role_select;
								?>
								</div>
							</div>

						<br /> 
					</div>
					</div>
				</div>				
				</div>				


				<div class="postbox inside">
					<div class="inside">
						<p>
						<?php submit_button( __( 'Save settings', 'wpematico' ), 'primary', 'wpematico-save-settings2', false ); ?>
						</p>
					</div>
				</div>
				</div>
				</div>
			</div>
			</form>
		</div>
	<script type="text/javascript" language="javascript">
		jQuery('#mailsndemail').blur(function() {
			var x = jQuery(this).val();
			var atpos = x.indexOf("@");
			var dotpos = x.lastIndexOf(".");
		  if (atpos< 1 || dotpos<atpos+2 || dotpos+2>=x.length) {
			jQuery('#mailmsg').text("<?php _e( 'Invalid email.', 'wpematico' );?>");
			return false;
		  }else{
			jQuery('#mailmsg').text("");
			return true;
		  }
		});

	//jQuery(document).ready(function($){
		jQuery('#imgcache').click(function() {
			if ( true == jQuery('#imgcache').is(':checked')) {
				jQuery('#nolinkimg').fadeIn();
			} else {
				jQuery('#nolinkimg').fadeOut();
			}
		});

		jQuery('#imgcache, #featuredimg').click(function() {
			if ( true == jQuery('#imgcache').is(':checked') || true == jQuery('#featuredimg').is(':checked') ) {
				jQuery('#custom_uploads').fadeIn();
			} else {
				jQuery('#custom_uploads').fadeOut();
			}
		});

		jQuery('#allowduplicates').click(function() {
			if ( true == jQuery('#allowduplicates').is(':checked')) {
				jQuery('#enadup').fadeIn();
			} else {
				jQuery('#allowduptitle').removeAttr("checked");
				jQuery('#allowduphash').removeAttr("checked");
				jQuery('#enadup').fadeOut();
			}
		});
		jQuery('#disabledashboard').click(function() {
			if ( true == jQuery('#disabledashboard').is(':checked')) {
				jQuery('#roles').fadeOut();
				jQuery('#roleslabel').fadeOut();
			} else {
				jQuery('#roles').fadeIn();
				jQuery('#roleslabel').fadeIn();
			}
		});

		jQuery('#disable_credits').click(function() {
			if ( jQuery('#disable_credits').is(':checked')) {
				jQuery('#discredits').fadeIn();
			} else {
				jQuery('#discredits').fadeOut();
			}
		});

		jQuery('#set_stupidly_fast').click(function() {
			if ( false == jQuery('#set_stupidly_fast').is(':checked')) {
				jQuery('#simpie').fadeIn();
			} else {
				jQuery('#simplepie_strip_attributes').removeAttr("checked");
				jQuery('#simplepie_strip_htmltags').removeAttr("checked");
				jQuery('#simpie').fadeOut();
			}
		});
		jQuery('#simplepie_strip_htmltags').click(function() {
			if ( false == jQuery('#simplepie_strip_htmltags').is(':checked')) {
				jQuery('#strip_htmltags').attr('disabled',true);
			} else {
				jQuery('#strip_htmltags').removeAttr("disabled");
			}
		});
		jQuery('#simplepie_strip_attributes').click(function() {
			if ( false == jQuery('#simplepie_strip_attributes').is(':checked')) {
				jQuery('#strip_htmlattr').attr('disabled',true);
			} else {
				jQuery('#strip_htmlattr').removeAttr("disabled");
			}
		});
		jQuery('#emptytrashbutton').click(function() {
			if ( true == jQuery('#emptytrashbutton').is(':checked')) {
				jQuery('#hlptrash').fadeIn();
			} else {
				jQuery('#hlptrash').fadeOut();
			}
		});
		jQuery(function(){
			jQuery(".help_tip").tipTip({maxWidth: "300px", edgeOffset: 5,fadeIn:50,fadeOut:50, keepAlive:true, defaultPosition: "right"});
		});
	//}
	</script>

	<?php
}  //wpematico_settings_tab_content

add_action( 'wpematico_save_settings', 'wpematico_settings_save' );
function wpematico_settings_save() {
	if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] ) {
		if ( get_magic_quotes_gpc() ) {
			$_POST = array_map( 'stripslashes_deep', $_POST );
		}
		# evaluation goes here
		check_admin_referer('wpematico-settings');
		$errlev = error_reporting();
		error_reporting(E_ALL & ~E_NOTICE);  // desactivo los notice que aparecen con los _POST

		$cfg = apply_filters('wpematico_check_options',$_POST);
		if(! wpematico_is_pro_active() ) $cfg['nonstatic'] = false;
		else $cfg['nonstatic'] = true;
		wp_get_current_user();
		$role_conf = array();
		foreach ( $_POST['role_name'] as $role_id => $role_val ) {
			$role_conf["$role_val"]= $role_val;
		}
		$cfg['roles_widget'] = $role_conf; 

		wp_clear_scheduled_hook('wpematico_cron');
		if( isset($cfg['disablewpcron']) && $cfg['disablewpcron'] ){ 
			define('DISABLE_WP_CRON',true);
		}
		if( !(isset($cfg['dontruncron']) && $cfg['dontruncron'] )) {
			wp_schedule_event(time(), 'wpematico_int', 'wpematico_cron');
		}
		
		if( update_option( WPeMatico::OPTION_KEY, $cfg ) ) {
			WPeMatico::add_wp_notice( array('text' => __('Settings saved.',  'wpematico'), 'below-h2'=>false ) );
			
		}
		error_reporting($errlev);
		wp_redirect( admin_url( 'edit.php?post_type=wpematico&page=wpematico_settings&tab=settings') );

	}
}



function wpematico_helpsettings($dev=''){
	$helpsettings = array(
		'Global Settings' => array( 
			'imgoptions' => array( 
				'title' => __('Global Settings For Images.', 'wpematico' ),
				'tip' => __('Set this features for all campaigns and can be overridden inside any campaign.', 'wpematico' ),
			),
			'imgcache' => array( 
				'title' => __('Cache Images. (Uploads)', 'wpematico' ),
				'tip' => __('When Cache Images is on, a copy of every image found in content of every feed item (only in &lt;img&gt; tags) is downloaded to the Wordpress UPLOADS Dir.', 'wpematico' ) . "<br />" . 
					__('If not enabled all images will be linked to the image owner\'s server, but also make your website faster for your visitors.', 'wpematico' ) . "<br />".
					"<b>" . __('Caching all images', 'wpematico' ) . ":</b> " . 
					__('This featured in the general Settings section, will be overridden for the campaign-specific options.', 'wpematico' ),
			),
			'imgattach'	=> array( 
				'title' => __('Attach Images to post.', 'wpematico' ),
				'tip' => "<b>" . __('Image Attaching', 'wpematico' ).":</b> " . 
					__('When Uploads images to Wordpress (and everything is working fine), every image attached is added to the Wordpress Media.', 'wpematico' ). "<br />" . 
					__('If enable this feature all the images will be attached to its owner post in WP media library.', 'wpematico' ),
				'plustip' => __('If you see that the job process is too slowly you can deactivate this here.', 'wpematico' ). "<br />" . 
					__('This feature may not work if you use the Custom Function for Uploads.', 'wpematico' )
			),
			'gralnolinkimg' => array( 
				'title' => __('Don\'t link external images.', 'wpematico' ),
				'tip' => "<b>" . __('Note',  'wpematico' ). ":</b> " . 
					__('If is selected and the image upload give error, then will delete the &lt;img&gt; HTML tag from the content. Check this to don\'t link images from external sites.', 'wpematico' ),
				'plustip' => "<b>" . __('Note',  'wpematico' ). ":</b> " . 
					__('If the image are inside &lt;a&gt; tags, then the link is also removed from content.', 'wpematico' ),
			),
			'featuredimg' => array( 
				'title' => __('Set first image on content as Featured Image.', 'wpematico' ),
				'tip' => __('Check this to set first image found on every content to be uploaded, attached and made Featured.', 'wpematico' ),
				'plustip' => '<small> ' . __('Read about',  'wpematico' ). ' <a href="http://codex.wordpress.org/Post_Thumbnails" target="_Blank">' . __('Post Thumbnails',  'wpematico' ). '</a></small>',
			),
			'rmfeaturedimg' => array( 
				'title' => __('Remove Featured Image from content.', 'wpematico' ),
				'tip' => __('Check this to strip the Featured Image from the post content.', 'wpematico' ),
				'plustip' => __('Useful if you have double image in your posts pages or if you don\'t want to show the image in content for any reason.',  'wpematico' ),
			),
			'customupload'	=> array( 
				'title' => __('Custom Uploads for Images.', 'wpematico' ),
				'tip' => __('Use this instead of Wordpress functions to improve performance. This function uploads the image "as is" from the original to use it inside the post.', 'wpematico' ).
					'<br />'. __('This function may not work in all servers.', 'wpematico' ),
				'plustip' => __('Try it at your own risk, if you see that the images are not loading, uncheck it.', 'wpematico' ).
					'<br />'. __('Also uncheck this if you need all sizes of wordpress images. The WP process can take too much resources if many images are uploaded at a time.', 'wpematico' ),
			),
		),
		'Enable Features' => array( 
			'enablefeatures' => array( 
				'title' => __('Enable Features.', 'wpematico' ),
				'tip' => __('If you need these features in each campaign, you can activate them here. This is not recommended if you will not use the feature.', 'wpematico' ),
			),
			'enableword2cats' => array( 
					'title' => __('Word to Categories.', 'wpematico' ),
					'tip' => __('Assign a selected category to the post if a word is found in the content.', 'wpematico' ),
			),			
			'enablerewrite' => array( 
				'title' => __('Content Rewrites.', 'wpematico' ),
				'tip' => __('Rewrite a word or phrase for another in the content of every post.', 'wpematico' ),
			),
		),
		'SimplePie Settings' => array( 
			'mysimplepie' => array( 
				'title' => __('Force Custom Simplepie Library.', 'wpematico' ),
				'tip' => __('Check this if you want to ignore Wordpress Simplepie library.', 'wpematico' ) . " " . 
					__('Almost never be necessary.  Just if you have problems with version of Simplepie installed in Wordpress.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'stupidly_fast' => array( 
				'title' => __('Set Simplepie stupidly fast.', 'wpematico' ),
				'tip' => __('Forgoes a substantial amount of data sanitization in favor of speed. This turns SimplePie into a dumb parser of feeds.  This means all feed content is gotten without parsers or filters.', 'wpematico' ),
				'plustip' => __('Don\'t strip anything from the content.  All html, style and scripts codes are included in content.', 'wpematico' )."<br>".
					__('Recommended Just if you really trust in your source feeds', 'wpematico' ).", ".
					__('otherwise you can change the allowed HTML tags and attributes from options below.', 'wpematico' ),
			),
			'strip_htmltags' => array( 
				'title' => __('Change SimplePie HTML tags to strip.', 'wpematico' ),
				'tip' => __('By Default Simplepie strip these html tags from feed content.  You can change or allow some tags, for example if you want to allow iframes or embed code like videos.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'strip_htmlattr' => array( 
				'title' => __('Change SimplePie HTML attributes to strip.', 'wpematico' ),
				'tip' => __('Simplepie also strip these attributes from html tags in content.  You can change it if you want to retain some of them or add more attributes to strip.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
		),
		'Advanced Fetching' => array( 
			'woutfilter' => array( 
				'title' => __('Allow option on campaign to skip the content filters.', 'wpematico' ),
				'tip' => __('NOTE: It is extremely dangerous to allow unfiltered content because there may be some vulnerability in the source code.', 'wpematico' ).'<br>'.
					__('See How WordPress Processes Post Content: ', 'wpematico' ) . '<a href="http://codex.wordpress.org/How_WordPress_Processes_Post_Content" target="_blank">http://codex.wordpress.org/How_WordPress_Processes_Post_Content</a>',
				'plustip' => __('After Wordpress inserted the post, this option will make an update query to database with the content of the post to avoid Wordpress filters.', 'wpematico' )."<br />". 
					__('Use only with reliable sources.', 'wpematico' ),
			),
			'campaign_timeout' => array( 
				'title' => __('Allow option on campaign to skip the content filters.', 'wpematico' ),
				'tip' => __('When a campaign is running and is interrupted by some issue, it cannot be executed again until click "Clear Campaign".', 'wpematico' ).'<br>'.
					__('This option clear campaign after this timeout then can run again on next scheduled cron. A value of "0" ignore this, means that remain until user make click. ', 'wpematico' )."<br />". 
					__('Recommended 300 Seconds. ', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'throttle' => array( 
				'title' => __('Add a throttle/delay in seconds after every post.', 'wpematico' ),
				'tip' => __('This option make a delay after every action of insert a post.  May be useful if you want to give a break to the server while is fetching many posts.  Leave on 0 if you don\'t have any problem.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'allowduplicates' => array( 
				'title' => __('Deactivate duplicate controls.', 'wpematico' ),
				'tip' => __('When the running campaign found a duplicated post the process is interrupted because assume that all followed posts, are also duplicates.  You can disable these controls here.', 'wpematico' ).'<br>'.
					__('Duplicates checking by hash is a boost to checking for duplicates by title, which may fail many times.', 'wpematico' ),
				'plustip' => '&nbsp;&nbsp;&nbsp;&nbsp;<b>'. __('Allowing duplicated posts', 'wpematico' ) .':</b> '. __("There are two controls for duplicates, title of the post and a hash generated by last item's url obtained on campaign process.", 'wpematico' ).'<br>'.
					__('NOTE: If disable both controls, all items will be fetched again and again... and again, ad infinitum.  If you want allow duplicated titles, just activate "Allow duplicated titles".', 'wpematico' ),
			),
			'jumpduplicates' => array( 
				'title' => __('Continue Fetching if found duplicated items.', 'wpematico' ),
				'tip' => __('Unless it is the first time, when finds a duplicate, it means that all following items were read before. This option avoids and allows jump every duplicate and continues reading the feed searching more new items. Not recommended.', 'wpematico' ),
				'plustip' => '&nbsp;&nbsp;&nbsp;&nbsp;<b>' . __('How it works:','wpematico').':</b> '. __('The feed items are ordered by datetime in almost all cases. When the campaign runs, goes item by item from newest to oldest, and stops when found the first duplicated item, this mean that all items following (the old ones) are also duplicated.', 'wpematico' ).'<br>'.
					__('As the hash is checked only by the last retrieved item, selecting this option may generate duplicate posts if duplicate checking by title does not work well for a campaign.', 'wpematico' ),
			),
			'disableccf' => array( 
				'title' => __('Disables Plugin Custom fields.', 'wpematico' ),
				'tip' => __('This option nulls saving custom fields on every post that campaign publishes.', 'wpematico' ) .'<br>'
					. __('By default the plugin saves three custom fields on every post with campaign and source item data.', 'wpematico' ) .'<br>'
					. __('Necessary for use permalink to source feature, identify which campaign fetch the post or to make any bulk action on post types related with original campaign.', 'wpematico' ) .'<br>'
					. __('Not recommended unless you want to loose this data and features in order to save DB space.', 'wpematico' ) .'<br>'
					. __('(Enabling this feature don\'t deletes the previous saved data.)', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
		),
		'Cron and Scheduler Settings' => array( 
			'dontruncron' => array( 
				'title' => __('Disable WPeMatico schedulings.', 'wpematico' ),
				'tip' => __('This option deactivate WPeMatico plugin cron schedules.', 'wpematico' ).'<br>'.
					__('Affects all campaigns. To run campaigns you must do it manually or with external cron. (Recommended with External Cron).', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'set_cron_code' => array( 
				'title' => __('Set a password to access the external CRON.', 'wpematico' ),
				'tip' => __('Activate a code to allow or avoid the use of the external cron file.  Deactivated by default to backward compatibility, but strongly recommended.', 'wpematico' ),
				'plustip' => __('If this field is not checked the password will be ignored.', 'wpematico' ),
			),
			'cron_code' => array( 
				'title' => __('Type the password to use the external CRON.', 'wpematico' ),
				'tip' => __('This will be the code used in the command to run the cron.  Can be any string you want to use as ?code=this_code.  Recommended.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'disablewpcron' => array( 
				'title' => __('Disable all WP_Cron.', 'wpematico' ),
				'tip' => __('Check this to deactivate all Wordpress cron schedules. Affects to Wordpress itself and all other plugins.  Not recommended unless you want to use an external Cron for your wordpress.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'logexternalcron' => array( 
				'title' => __('Log file for external Cron.', 'wpematico' ),
				'tip' => __('Try to save a file with simple steps taken at run wpe-cron.php. "wpemextcron.txt.log" will be saved on uploads folder or inside plugin, "app" folder.  Recommended on issues with cron.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
		),
		'Other tools & Advanced' => array( 
			'emptytrashbutton' => array( 
				'title' => __('Shows Button to empty trash on lists.', 'wpematico' ),
				'tip' => __('Just an extra tool to display a button for empty trash folder on every custom post main screen. May be posts, pages or selects what you want.', 'wpematico' ),
			),
			'disabledashboard' => array( 
				'title' => __('Disable WP Dashboard Widget', 'wpematico' ),
				'tip' => __('Check this if you don\'t want to display the widget dashboard.  Anyway, only admins will see it.', 'wpematico' ),
				'plustip' => __('', 'wpematico' ),
			),
			'disablecheckfeeds' => array( 
				'title' => __('Disable Check Feeds before Save.', 'wpematico' ),
				'tip' => __('Check this if you don\'t want automatic check feed URLs before save every campaign.', 'wpematico' ),
			),
			'enabledelhash' => array( 
				'title' => __('Enable Del Hash.', 'wpematico' ),
				'tip' => __('Show `Del Hash` link on campaigns list.  This link delete all hash codes for check duplicates on every feed per campaign.', 'wpematico' ),
			),
			'enableseelog' => array( 
				'title' => __('Enable See last log.', 'wpematico' ),
				'tip' => __('Show `See Log` link on campaigns list.  This link show the last processed log of every campaign.', 'wpematico' ),
			),
			'disable_credits' => array( 
				'title' => __('Disable WPeMatico Credits.', 'wpematico' ),
				'tip' => __('I really appreciate if you can left this option blank to show the plugin\'s credits.', 'wpematico' ),
				'plustip' => sprintf( __('If you can\'t show the WPeMatico credits in your posts, I really appreciate if you can take a minute to %s write a 5 star review on Wordpress %s.  :-) thanks.', 'wpematico' ),
								'<a href="https://wordpress.org/support/view/plugin-reviews/wpematico?filter=5&rate=5#postform" target="_Blank" title="Open a new window">',
								'</a>'),
			),
		),
		'Sending e-Mails' => array( 
			'sendmail' => array( 
					'title' => __('Sender Email.', 'wpematico' ),
					'tip' => __('Email address used as "FROM" field in all emails sent by this plugin.', 'wpematico' ),
			),
			'namemail' => array( 
					'title' => __('Sender Name.', 'wpematico' ),
					'tip' => __('The Name that will show in your inbox related to previous email address for all emails sent by this plugin.', 'wpematico' ),
			),
		)
	);

	if ($dev=='tips') {
		foreach($helpsettings as $key => $section){
			foreach($section as $section_key => $sdata){
				$helptip[$section_key] = htmlentities($sdata['tip']);
			}
		}
		$helptip = array_merge($helptip, array(
			 'PROfeatures'		=> __('Features only available when you buy the PRO version.', 'wpematico' ),
			 'enablekwordf' 	=> __('This is for exclude or include posts according to the keywords <b>found</b> at content or title.', 'wpematico' ),
			 'enablewcf' 	 	=> __('This is for cut, exclude or include posts according to the letters o words <b>counted</b> at content.', 'wpematico' ),
			 'enablecustomtitle'=> __('If you want a custom title for posts of a campaign, you can activate here.', 'wpematico' ),
			 'enabletags'		=> __('This feature generate tags automatically on every published post, on campaign edit you can disable auto feature and manually enter a list of tags or leave empty.', 'wpematico' ),
			 'enablecfields'	=> __('Add custom fields with values as templates on every post.', 'wpematico' ),
			 'fullcontent'		=> __('If you want to attempt to obtain full items content from source site instead of the campaign feed, you can activate here.', 'wpematico' ),
			 'authorfeed'		=> __('This option allow you assign an author per feed when editing campaign. If no choice any author, the campaign author will be taken.', 'wpematico' ),
			 'importfeeds'		=> __('On campaign edit you can import, copy & paste in a textarea field, a list of feed addresses with/out author names.', 'wpematico' ),
			)
		);
		return apply_filters('wpematico_helptip_settings', $helptip);
	}
	return apply_filters('wpematico_help_settings', $helpsettings);
}

add_action('admin_init', 'wpematico_settings_help');
function wpematico_settings_help(){
	if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'wpematico_settings' ) && 
			( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wpematico' ) &&
			( (isset( $_GET['tab'] ) && $_GET['tab'] == 'settings' ) || !isset($_GET['tab']) ) 
		) {		
		$screen = WP_Screen::get('wpematico_page_wpematico_settings ');
		foreach(wpematico_helpsettings() as $key => $section){
			$tabcontent = '';
			foreach($section as $section_key => $sdata){
				$helptip[$section_key] = htmlentities($sdata['tip']);
				$tabcontent .= '<p><strong>' . $sdata['title'] . '</strong><br />'.
						$sdata['tip'] . '</p>';
				$tabcontent .= (isset($sdata['plustip'])) ?	'<p style="margin-top: 2px;margin-left: 7px;">' . $sdata['plustip'] . '</p>' : '';
			}
			$screen->add_help_tab( array(
				'id'	=> $key,
				'title'	=> $key,
				'content'=> $tabcontent,
			) );
		}
	}
}
