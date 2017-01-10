<?php 
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( class_exists( 'WPeMatico_Campaign_edit_functions' ) ) return;

class WPeMatico_Campaign_edit_functions {
	public static function create_meta_boxes() {
		global $post, $current_screen, $campaign_data, $cfg, $helptip; 
		require( dirname( __FILE__ ) . '/campaign_help.php' );
		$campaign_data = WPeMatico :: get_campaign ($post->ID);
//		$campaign_data = apply_filters('wpematico_check_campaigndata', $campaign_data);
		$cfg = get_option(WPeMatico :: OPTION_KEY);
		$cfg = apply_filters('wpematico_check_options', $cfg);

	//	add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
		add_meta_box( 'campaign_types', __( 'Campaign Type', 'etruel-del-post-copies' ),  array('WPeMatico_Campaign_edit', 'campaign_type_box'), 'wpematico', 'side', 'high' );
		if ( current_theme_supports( 'post-formats' ) )
			add_meta_box( 'post_format-box',__('Campaign Posts Format',WPeMatico::TEXTDOMAIN). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['postformat'].'"></span>', array( 'WPeMatico_Campaign_edit' ,'format_box'),'wpematico','side', 'default' );
		add_meta_box( 'category-box',__('Campaign Categories',WPeMatico::TEXTDOMAIN). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['category'].'"></span>', array( 'WPeMatico_Campaign_edit' ,'cat_box'),'wpematico','side', 'default' );
		add_meta_box( 'post_tag-box', __('Tags generation', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['tags'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'tags_box' ),'wpematico','side', 'default' );
		add_meta_box( 'log-box', __('Send log', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['sendlog'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'log_box' ),'wpematico','side', 'default' );
		add_meta_box( 'feeds-box', __('Feeds for this Campaign', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['feeds'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'feeds_box' ),'wpematico','normal', 'default' );
		add_meta_box( 'options-box', __('Options for this campaign', 'wpematico' ), array(  'WPeMatico_Campaign_edit'  ,'options_box' ),'wpematico','normal', 'default' );
		add_meta_box( 'cron-box', __('Schedule Cron', 'wpematico' ), array(  'WPeMatico_Campaign_edit'  ,'cron_box' ),'wpematico','normal', 'default' );
		add_meta_box( 'images-box', __('Options for images', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['imgoptions'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'images_box' ),'wpematico','normal', 'default' );
		add_meta_box( 'template-box', __('Post Template', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['postemplate'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'template_box' ),'wpematico','normal', 'default' );
		if ($cfg['enableword2cats'])   // Si está habilitado en settings, lo muestra 
			add_meta_box( 'word2cats-box', __('Word to Category options', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['wordcateg'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'word2cats_box' ),'wpematico','normal', 'default' );
		if ($cfg['enablerewrite'])   // Si está habilitado en settings, lo muestra 
			add_meta_box( 'rewrite-box', __('Rewrite options', 'wpematico' ). '<span class="mya4_sprite infoIco help_tip" title="'. $helptip['rewrites'].'"></span>', array(  'WPeMatico_Campaign_edit'  ,'rewrite_box' ),'wpematico','normal', 'default' );
		//***** Call nonstatic
		if( $cfg['nonstatic'] ) { NoNStatic :: meta_boxes($campaign_data, $cfg); }
		// Publish Meta_box edited
		add_action('post_submitbox_start', array( __CLASS__ ,'post_submitbox_start')); 
		
		do_action('wpematico_create_metaboxes',$campaign_data,$cfg); 
	
	}
	
	
		//*************************************************************************************
	static function campaign_type_box() {
		global $post, $campaign_data;
		$options = self::campaign_type_options();
		$readonly = ( count($options) == 1 ) ? 'disabled' : '';			
		$echoHtml = '<select id="campaign_type" '.$readonly.' name="campaign_type" style="display:inline;">';
		foreach($options as $key => $option) {
			$echoHtml .= '<option value="'.$option["value"].'"'.  selected( $option["value"], $campaign_data["campaign_type"], false ).'>'.$option["text"].'</option>';
		}
		$echoHtml .= '</select>';

		echo $echoHtml;
	}
	static function campaign_type_options() {
		$options=array(
			array( 'value'=> 'feed', 'text' => __('Feed Fetcher (Default)', 'wpematico' ), "show"=>array('feeds-box') ),
		//	array( 'value'=> 'youtube','text' => __('You Tube Fetcher', 'wpematico' ) ),
		);
		$options = apply_filters('wpematico_campaign_type_options', $options);

		return $options;
	}	
	static function get_campaign_type_by_field($value, $field='value', $return='text') {
		$options =  self::campaign_type_options();
		foreach($options as $key => $option) {
			if ($option[$field]==$value) {
				if($return=='key') return $key;
				else return $option[$return];
			}
		}
		return FALSE;
	}	
	
	
		//*************************************************************************************
	public static function format_box( $post, $box ) {
		global $post, $campaign_data, $helptip;
		if ( current_theme_supports( 'post-formats' ) ) :
		$post_formats = get_theme_support( 'post-formats' );
		$campaign_post_format = $campaign_data['campaign_post_format'];

		if ( is_array( $post_formats[0] ) ) :
			$campaign_post_format = ( @!$campaign_post_format )? '0' : $campaign_data['campaign_post_format'];
		?>
		<div id="post-formats-select">
			<input type="radio" name="campaign_post_format" class="post-format" id="post-format-0" value="0" <?php checked( $campaign_post_format, '0' ); ?> /> <label for="post-format-0" class="post-format-icon post-format-standard"><?php echo get_post_format_string( 'standard' ); ?></label>
			<?php foreach ( $post_formats[0] as $format ) : ?>
				<br /><input type="radio" name="campaign_post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" <?php checked( $campaign_post_format, $format ); ?> /> <label for="post-format-<?php echo esc_attr( $format ); ?>" class="post-format-icon post-format-<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></label>
			<?php endforeach; ?><br />
		</div>
		<?php endif; endif;
	}
	
		//************************************************************************************* 
	public static function rewrite_box( $post ) { 
		global $post, $campaign_data, $helptip;
		$campaign_rewrites = $campaign_data['campaign_rewrites'];
		?>
		<p class="he20">
		<span class="left"><?php _e('Replaces words or phrases by other that you want or turns into link.', 'wpematico' ) ?></span>
		</p>
		<div id="rewrites_edit" class="inlinetext">		
			<?php for ($i = 0; $i < count($campaign_rewrites['origin']); $i++) : ?>			
			<div class="<?php if(($i % 2) == 0) echo 'bw'; else echo 'lightblue'; ?> <?php if($i==count($campaign_rewrites['origin'])) echo 'hide'; ?>">
				<div class="pDiv jobtype-select p7" id="nuevorew">
					<div id="rw1" class="wi30 left p4"><div class="rowflex">
						<?php _e('Origin:','wpematico') ?>&nbsp;&nbsp;
						<label class="rowblock"><input name="campaign_word_option_title[<?php echo $i; ?>]" class="campaign_word_option_title" class="checkbox" value="1" type="checkbox"<?php checked($campaign_rewrites['title'][$i],true) ?> onclick="relink=jQuery(this).parent().parent().children('#rw3');if(true==jQuery(this).is(':checked')) relink.fadeOut(); else relink.fadeIn();"/><?php _e('Title','wpematico') ?></label>
						&nbsp;<label class="rowblock"><input name="campaign_word_option_regex[<?php echo $i; ?>]" class="campaign_word_option_regex" class="checkbox" value="1" type="checkbox"<?php checked($campaign_rewrites['regex'][$i],true) ?> /><?php _e('RegEx','wpematico') ?></label>
						</div>
						<textarea class="large-text he35 campaign_word_origin" name="campaign_word_origin[<?php echo $i; ?>]" /><?php echo stripslashes($campaign_rewrites['origin'][$i]) ?></textarea>
					</div>
					<div class="wi30 left p4">
						 <?php _e('Rewrite to:','wpematico') ?>
						<textarea class="large-text he35" id="campaign_word_rewrite" name="campaign_word_rewrite[<?php echo $i; ?>]" /><?php echo stripslashes($campaign_rewrites['rewrite'][$i]) ?></textarea>
					</div>
					<div id="rw3" class="wi30 left p4" <?php if(checked($campaign_rewrites['title'][$i],true,false)) echo 'style="display:none"'; ?>>
						 <?php _e('ReLink to:','wpematico') ?>
						<textarea class="large-text he35" id="campaign_word_relink" name="campaign_word_relink[<?php echo $i; ?>]" /><?php echo stripslashes($campaign_rewrites['relink'][$i]) ?></textarea>
					</div>
					<div class="rowactions">
						<span class="" id="w2cactions">
							<label title="<?php _e('Delete this item', 'wpematico' ); ?>" onclick=" jQuery(this).parent().parent().parent().children('#rw1').children('.campaign_word_origin').text(''); jQuery(this).parent().parent().parent().fadeOut();disable_run_now();" class="bicon delete left"></label>
						</span>
					</div>
				</div>
			</div>
			<?php endfor ?>
			<input id="rew_max" value="<?php echo $i-1; ?>" type="hidden" name="rew_max">
			
		  </div>
		  <div class="clear"></div>
		  <div id="paging-box">
				<a href="JavaScript:void(0);" class="button-primary add" id="addmorerew" style="font-weight: bold; text-decoration: none;"> <?php _e('Add more', 'wpematico' ); ?>.</a>
		  </div>

		<?php 
	}
	
	//**************************************************************************
	public static function word2cats_box( $post ) { 
		global $post, $campaign_data, $helptip;
		$campaign_wrd2cat = $campaign_data['campaign_wrd2cat'];
		?>
		<p class="he20">
			<span class="left"><?php _e('Assigning categories based on content words.', 'wpematico' ) ?></span> 
		</p>	
		<div id="wrd2cat_edit" class="inlinetext">		
			<?php for ($i = 0; $i <= count($campaign_wrd2cat['word']); $i++) : ?>
			<div class="clear"></div>
			<div id="w2c_ID<?php echo $i; ?>" class="<?php if(($i % 2) == 0) echo 'bw'; else echo 'lightblue'; ?> <?php if($i==count($campaign_wrd2cat['word'])) echo 'hide'; ?>">
				<div class="pDiv jobtype-select p7" id="nuevow2c">
					<div id="w1" class="left">
						<label><?php _e('Word:', 'wpematico' ) ?> <input type="text" size="25" class="regular-text" id="campaign_wrd2cat" name="campaign_wrd2cat[word][<?php echo $i; ?>]" value="<?php echo stripslashes(htmlspecialchars_decode(@$campaign_wrd2cat['word'][$i])); ?>" /></label><br />
						<label><input name="campaign_wrd2cat[regex][<?php echo $i; ?>]" id="campaign_wrd2cat_regex" class="checkbox w2cregex" value="1" type="checkbox"<?php checked($campaign_wrd2cat['regex'][$i],true) ?> /> <?php _e('RegEx', 'wpematico' ) ?></label>
						<label><input <?php echo ($campaign_wrd2cat['regex'][$i]) ? 'disabled' : '';?> name="campaign_wrd2cat[cases][<?php echo $i; ?>]" id="campaign_wrd2cat_cases" class="checkbox w2ccases" value="1" type="checkbox"<?php checked($campaign_wrd2cat['cases'][$i],true) ?> /> <?php _e('Case sensitive', 'wpematico' ) ?></label>
					</div>
					<div id="c1" class="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php _e('To Category:', 'wpematico' ) ?>
						<?php 
						$catselected='selected='.$campaign_wrd2cat['w2ccateg'][$i];
						$catname="name=campaign_wrd2cat[w2ccateg][".$i."]";
						$catid="id=campaign_wrd2cat_category_".$i;
						wp_dropdown_categories('hide_empty=0&hierarchical=1&show_option_none='.__('Select category', 'wpematico' ).'&'.$catselected.'&'.$catname.'&'.$catid);
						?>
					</div>
					<span class="wi10" id="w2cactions">
						<label title="<?php _e('Delete this item', 'wpematico' ); ?>" onclick="delete_row_input('#w2c_ID<?php echo $i; ?>')" class="bicon delete left"></label>
					</span>
				</div>
			</div>
			<?php endfor ?>
			<input id="wrd2cat_max" value="<?php echo $i; ?>" type="hidden" name="wrd2cat_max">
		</div>
		<div class="clear"></div>
		<div id="paging-box">
			<a href="JavaScript:void(0);" class="button-primary add" id="addmorew2c" style="font-weight: bold; text-decoration: none;"> <?php _e('Add more', 'wpematico' ); ?>.</a>
		</div>
		<?php 
	}
	
	
	//*************************************************************************************
	public static function template_box( $post ) { 
		global $post, $campaign_data, $cfg, $helptip;
		$campaign_enable_template = $campaign_data['campaign_enable_template'];
		$campaign_template = $campaign_data['campaign_template'];
		//$cfg = get_option(WPeMatico :: OPTION_KEY);
		?>
		<p class="he20"><?php _e('Modify, manage or add extra content to every post fetched.', 'wpematico' ) ?></p>		
		<div id="wpe_post_template_edit" class="inlinetext">
			<input name="campaign_enable_template" id="campaign_enable_template" class="checkbox" value="1" type="checkbox"<?php checked($campaign_enable_template,true) ?> />
			<label for="campaign_enable_template"> <?php _e('Enable Post Template', 'wpematico' ) ?></label>
			<textarea class="large-text" id="campaign_template" name="campaign_template" /><?php echo stripslashes($campaign_template) ?></textarea>
			<p class="he20" id="tags_note" class="note left"><?php _e('Allowed tags', 'wpematico' ); ?>: </p>
			<p id="tags_list" style="border-left: 3px solid #EEEEEE; color: #999999; font-size: 11px; padding-left: 6px;margin-top: 0;">
				<span class="tag">{title}</span>, <span class="tag">{content}</span>, <span class="tag">{itemcontent}</span>, <span class="tag">{image}</span>, <span class="tag">{author}</span>, <span class="tag">{authorlink}</span>, <span class="tag">{permalink}</span>, <span class="tag">{feedurl}</span>, <span class="tag">{feedtitle}</span>, <span class="tag">{feeddescription}</span>, <span class="tag">{feedlogo}</span>, <span class="tag">{campaigntitle}</span>, <span class="tag">{campaignid}</span>
			</p>
			<p><a href="javascript:void(0);" title="<?php _e('Click to Show/Hide', 'wpematico' ); ?>" onclick="jQuery('#tags_note,#tags_list').fadeToggle(); jQuery('#tags_list_det').fadeToggle();" class="m4"><?php _e('Click here to toggle more info.','wpematico') ?></a></p>
			<div id="tags_list_det" style="display: none;">
				<b><?php _e('Supported tags', 'wpematico' ); ?></b>
				<p><?php _e('A tag is a piece of text that gets replaced dynamically when the post is created. Currently, these tags are supported:', 'wpematico' ); ?></p>
				<ul style='list-style-type: square;margin:0 0 5px 20px;font:0.92em "Lucida Grande","Verdana";'>
				  <li><strong class="tag">{title}</strong> <?php _e('The feed item title.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{content}</strong> <?php _e('The parsed post content.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{itemcontent}</strong> <?php _e('The feed item description.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{image}</strong> <?php _e('Put the featured image on content.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{author}</strong> <?php _e('The feed item author.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{authorlink}</strong> <?php _e('The feed item author link (If exist).', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{permalink}</strong> <?php _e('The feed item permalink.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{feedurl}</strong> <?php _e('The feed URL.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{feedtitle}</strong> <?php _e('The feed title.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{feeddescription}</strong> <?php _e('The description of the feed.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{feedlogo}</strong> <?php _e('The feed\'s logo image URL.', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{campaigntitle}</strong> <?php _e('This campaign title', 'wpematico' ); ?> </li>
				  <li><strong class="tag">{campaignid}</strong> <?php _e('This campaign ID.', 'wpematico' ); ?> </li>
				  <?php do_action('wpematico_print_template_tags', $campaign_data); ?>
				</ul>
				<p><b><?php _e('Examples:', 'wpematico' ); ?></b></p>
				<div id="tags_list_examples" style="display: block;">
					<span><?php _e('If you want to add a link to the source at the bottom of every post and the author, the post template would look like this:', 'wpematico' ); ?></span>
					<div class="code">{content}<br>&lt;a href="{permalink}"&gt;<?php _e('Go to Source', 'wpematico' ); ?>&lt;/a&gt;&lt;br /&gt;<br>Author: {author}</div>
					<p><em>{content}</em> <?php _e('will be replaced with the feed item content', 'wpematico' ); ?>, <em>{permalink}</em> <?php _e('by the source feed item URL, which makes it a working link and', 'wpematico' ); ?> <em>{author}</em> <?php _e('with the original author of the feed item.', 'wpematico' ); ?></p>
					<span><?php _e('Also you can add a gallery with three columns with all thumbnails images clickables at the bottom of every content, but before source link and author name, the post template would look like this:', 'wpematico' ); ?></span>
					<div class="code">{content}<br>[gallery link="file" columns="3"]<br>&lt;a href="{permalink}"&gt;<?php _e('Go to Source', 'wpematico' ); ?>&lt;/a&gt;&lt;br /&gt;<br>Author: {author}</div>
					<p><em>[gallery link="file" columns="3"]</em> <?php _e('it\'s a WP shortcode for insert a gallery into the post.  You can use any shortcode here; will be processed by Wordpress.', 'wpematico' ); ?></p>
				</div>
			</div>

		</div>
		<?php if( $cfg['nonstatic'] ) { NoNStatic :: last_html_tag($post, $cfg); } ?>

		<?php
	}
	//*************************************************************************************
	public static function images_box( $post ) { 
		global $post, $campaign_data, $cfg, $helptip;
		$campaign_imgcache = $campaign_data['campaign_imgcache'];
		$campaign_cancel_imgcache = $campaign_data['campaign_cancel_imgcache'];
		$campaign_nolinkimg = $campaign_data['campaign_nolinkimg'];
		if (!$cfg['imgcache']) : ?>
			<p>
				<input name="campaign_imgcache" id="campaign_imgcache" class="checkbox left" value="1" type="checkbox" <?php checked($campaign_imgcache,true); ?> style="width: 19px;" />
				<label for="campaign_imgcache"><?php echo __('Enable Cache Images for this campaign.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['imgcache']; ?>"></span>
			</p>
			<div id="nolinkimg" <?php if (!$campaign_imgcache) echo 'style="display:none;"';?>>
				<p>
					<input name="campaign_nolinkimg" id="campaign_nolinkimg" class="checkbox" value="1" type="checkbox" <?php checked($campaign_nolinkimg,true); ?> />
					<b><?php echo '<label for="campaign_nolinkimg">' . __('No link to source images', 'wpematico' ) . '</label>'; ?></b> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['gralnolinkimg']; ?>"></span>
				</p>
			</div>
		<?php else : ?>
			<p>
				<input name="campaign_cancel_imgcache" id="campaign_cancel_imgcache" class="checkbox" value="1" type="checkbox" <?php checked($campaign_cancel_imgcache,true); ?> />
				<label for="campaign_cancel_imgcache"><?php echo __('Cancel Cache Images for this campaign', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['cancel_imgcache']; ?>"></span>
			</p>
		<?php endif ?>
	<?php	/*	<p><input name="campaign_solo1ra" id="campaign_solo1ra" class="checkbox" value="1" type="checkbox" <?php checked($campaign_solo1ra,true); ?> />
		<b><?php echo '<label for="campaign_solo1ra">' . __('Left just first image on every post.', 'wpematico' ) . '</label>'; ?></b></p>   */  ?>
	<?php
	}
	//*************************************************************************************
	public static function options_box( $post ) { 
		global $post, $campaign_data, $cfg, $helptip ;
		$campaign_max = $campaign_data['campaign_max'];
		$campaign_feeddate = $campaign_data['campaign_feeddate'];
		$campaign_author = $campaign_data['campaign_author'];
		$campaign_linktosource = $campaign_data['campaign_linktosource'];
		$avoid_search_redirection = $campaign_data['avoid_search_redirection'];
		$campaign_commentstatus = $campaign_data['campaign_commentstatus'];
		$campaign_allowpings = $campaign_data['campaign_allowpings'];
		$campaign_woutfilter = $campaign_data['campaign_woutfilter'];
		$campaign_strip_links = $campaign_data['campaign_strip_links'];
		$campaign_striphtml = $campaign_data['campaign_striphtml'];
		?>
	<div id="optionslayer" class="ibfix vtop">
		<p>
			<input name="campaign_max" type="number" min="0" size="3" value="<?php echo $campaign_max;?>" class="small-text" id="campaign_max"/> 
			<label for="campaign_max"><?php echo __('Max items to create on each fetch.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['itemfetch']; ?>"></span>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked($campaign_feeddate ,true);?> name="campaign_feeddate" value="1" id="campaign_feeddate"/>
			<label for="campaign_feeddate"><?php echo __('Use feed item date.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['itemdate']; ?>"></span>
		</p>				
		<p>
			<input class="checkbox" type="checkbox"<?php checked($campaign_allowpings ,true);?> name="campaign_allowpings" value="1" id="campaign_allowpings"/> 
			<label for="campaign_allowpings"><?php echo __('Pingbacks y trackbacks.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['allowpings']; ?>"></span>
		</p>
		<p>
			<label for="campaign_commentstatus"><?php echo __('Discussion options:', 'wpematico' ); ?></label>
			<select id="campaign_commentstatus" name="campaign_commentstatus">
			<option value="open"<?php echo ($campaign_commentstatus =="open" || $campaign_commentstatus =="") ? 'SELECTED' : ''; ?> >Open</option>
			<option value="closed" <?php echo ($campaign_commentstatus =="closed") ? 'SELECTED' : ''; ?> >Closed</option>
			<option value="registered_only" <?php echo ($campaign_commentstatus =="registered_only") ? 'SELECTED' : ''; ?> >Registered only</option>
			</select>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['commentstatus']; ?>"></span>
		</p>
		<p>
			<label for="campaign_author"><?php echo __('Author:', 'wpematico' ); ?></label> 
			<?php wp_dropdown_users(array('name' => 'campaign_author','selected' => $campaign_author )); ?> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['postsauthor']; ?>"></span>
		</p>
	</div>	
	<div id="optionslayer-right" class="ibfix vtop">
		<p><input class="checkbox" type="checkbox"<?php checked($campaign_striphtml,true);?> name="campaign_striphtml" value="1" id="campaign_striphtml"/>
			<label for="campaign_striphtml"><?php echo __('Strip All HTML Tags', 'wpematico' ); ?></label>  <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['striphtml']; ?>"></span>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked($campaign_strip_links ,true);?> name="campaign_strip_links" value="1" id="campaign_strip_links"/> 
			<label for="campaign_strip_links"><?php echo __('Strip links from content.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['striplinks']; ?>"></span>
		</p>
		
		<?php if ($cfg['woutfilter']) : ?>
		<p>
			<input class="checkbox" value="1" type="checkbox" <?php checked($campaign_woutfilter,true); ?> name="campaign_woutfilter" id="campaign_woutfilter" /> 
			<label for="campaign_woutfilter"><?php echo __('Post Content Unfiltered.', 'wpematico' ); ?></label><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['woutfilter']; ?>"></span>
		</p>
		<?php endif; ?>
		<p>
			<input class="checkbox" type="checkbox"<?php checked($campaign_linktosource ,true);?> name="campaign_linktosource" value="1" id="campaign_linktosource"/> 
			<label for="campaign_linktosource"><?php echo __('Post title links to source.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['linktosource']; ?>"></span>
			<?php if($cfg['disableccf']) echo '<br /><small>'. __('Feature deactivated on Settings. Needs Metadata.', 'wpematico' ).'</small>'; ?>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked($avoid_search_redirection ,true);?> name="avoid_search_redirection" value="1" id="avoid_search_redirection"/> 
			<label for="avoid_search_redirection"><?php echo __('Avoid search redirection to source permalink.', 'wpematico' ); ?></label> <span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['avoid_search_redirection']; ?>"></span>
		</p>
		<?php do_action('wpematico_permalinks_tools',$campaign_data,$cfg);  ?>
	</div>
		<?php
	}
	//*************************************************************************************
	public static function cron_box( $post ) { 
		global $post, $campaign_data, $cfg, $helptip ;
		$activated = $campaign_data['activated'];
		$cron = $campaign_data['cron'];
		//Select en campaña que rellene el cron: Cada 15 min, cada 1hs, cada 3hs, cada 6hs.  2 veces por dia. 1 vez por dia 
		$cronperiods = array(
			'every5'=>array(
				'text'=>__('Every 5 minutes', 'wpematico'),
				'min'=>'*',
				'hours'=>'*',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
			'every15'=>array(
				'text'=>__('Every 15 minutes', 'wpematico'),
				'min'=>'0,15,30,45',
				'hours'=>'*',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
/*			'every30'=>array(
				'text'=>__('Every half an hour', 'wpematico'),
				'min'=>'0,30',
				'hours'=>'*',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
*/			'every60'=>array(
				'text'=>__('Once per hour', 'wpematico'),
				'min'=>'0',
				'hours'=>'*',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
			'every3h'=>array(
				'text'=>__('Every 3 hours', 'wpematico'),
				'min'=>'0',
				'hours'=>'0,3,6,9,12,15,18,21',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
			'every6h'=>array(
				'text'=>__('Every 6 hours', 'wpematico'),
				'min'=>'0',
				'hours'=>'0,6,12,18',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
			'every12h'=>array(
				'text'=>__('Every 12 hours', 'wpematico'),
				'min'=>'0',
				'hours'=>'0,12',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
			'every1day'=>array(
				'text'=>__('Every day at 3 o\'clock', 'wpematico'),
				'min'=>'0',
				'hours'=>'3',
				'days'=>'*',
				'months'=>'*',
				'weeks'=>'*'),
		);
		$cronperiods = apply_filters('wpematico_cronperiods', $cronperiods);
		?><script type="text/javascript">
			jQuery(document).ready(function($){
				$('#cronperiod').on( 'change', function(){
					switch( $(this).val() ) {
						<?php
						foreach($cronperiods as $key => $values) {
							echo "case '".$key."':
							min	   = '".$values['min']."'; 
							$('#cronminutes').val(min.split(','));
							hours  = '".$values['hours']."';
							$('#cronhours').val(hours.split(','));
							days   = '".$values['days']."';
							$('#crondays').val(days.split(','));
							months = '".$values['months']."';
							$('#cronmonths').val(months.split(','));
							weeks  = '".$values['weeks']."';
							$('#cronwday').val(weeks.split(','));
							break;
						";
						}
						?>
					}
				});
			});
		</script>
	<div id="schedulelayer" class="ibfix vtop">
		<p>
		<input class="checkbox" value="1" type="checkbox" <?php checked($activated,true); ?> name="activated" id="activated" /> <label for="activated"><?php _e('Activate scheduling', 'wpematico' ); ?></label><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['schedule']; ?>"></span>
		</p>
		<?php 
		_e('Working as <a href="http://wikipedia.org/wiki/Cron" target="_blank">Cron</a> job schedule:', 'wpematico' ); 
		echo ' <i>'.$cron.'</i> <br />'; 
		_e('Next runtime:', 'wpematico' ); echo ' '.date_i18n( (get_option('date_format').' '.get_option('time_format') ),WPeMatico :: time_cron_next($cron) );
		//_e('Next runtime:', 'wpematico' ); echo ' '.date('D, M j Y H:i',WPeMatico :: time_cron_next($cron));
		?>
		<p>
			<label for="cronperiod">
				<?php _e('Preselected schedules.', 'wpematico' ); ?>
			</label><span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['cronperiod']; ?>"></span>
			<br />
			<select name="cronperiod" id="cronperiod">
				<option value=""><?php _e('Select an option to change the values.','wpematico'); ?></option>
			<?php
			foreach($cronperiods as $key => $values) {
				//echo "<option value=\"".$key."\"".selected(in_array("$i",$minutes,true),true,false).">".$values['text']."</option>";
				echo "<option value=\"".$key."\">".$values['text']."</option>";
			}
			?>
			</select>
		</p>
	</div>
	<div id="cronboxes" class="ibfix vtop">
			<?php @list($cronstr['minutes'],$cronstr['hours'],$cronstr['mday'],$cronstr['mon'],$cronstr['wday']) = explode(' ',$cron,5);    ?>
			<div>
				<b><?php _e('Minutes: ','wpematico'); ?></b><br />
				<?php 
				if (strstr($cronstr['minutes'],'*/'))
					$minutes=explode('/',$cronstr['minutes']);
				else
					$minutes=explode(',',$cronstr['minutes']);
				?>
				<select name="cronminutes[]" id="cronminutes" multiple="multiple">
				<option value="*"<?php selected(in_array('*',$minutes,true),true,true); ?>><?php _e('Any (*)','wpematico'); ?></option>
				<?php
				for ($i=0;$i<60;$i=$i+5) {
					echo "<option value=\"".$i."\"".selected(in_array("$i",$minutes,true),true,false).">".$i."</option>";
				}
				?>
				</select>
			</div>
			<div>
				<b><?php _e('Hours:','wpematico'); ?></b><br />
				<?php 
				if (strstr($cronstr['hours'],'*/'))
					$hours=explode('/',$cronstr['hours']);
				else
					$hours=explode(',',$cronstr['hours']);
				?>
				<select name="cronhours[]" id="cronhours" multiple="multiple">
				<option value="*"<?php selected(in_array('*',$hours,true),true,true); ?>><?php _e('Any (*)','wpematico'); ?></option>
				<?php
				for ($i=0;$i<24;$i++) {
					echo "<option value=\"".$i."\"".selected(in_array("$i",$hours,true),true,false).">".$i."</option>";
				}
				?>
				</select>
			</div>
			<div>
				<b><?php _e('Days:','wpematico'); ?></b><br />
				<?php 
				if (strstr($cronstr['mday'],'*/'))
					$mday=explode('/',$cronstr['mday']);
				else
					$mday=explode(',',$cronstr['mday']);
				?>
				<select name="cronmday[]" id="cronmday" multiple="multiple">
				<option value="*"<?php selected(in_array('*',$mday,true),true,true); ?>><?php _e('Any (*)','wpematico'); ?></option>
				<?php
				for ($i=1;$i<=31;$i++) {
					echo "<option value=\"".$i."\"".selected(in_array("$i",$mday,true),true,false).">".$i."</option>";
				}
				?>
				</select>
			</div>
			<div>
				<b><?php _e('Months:','wpematico'); ?></b><br />
				<?php 
				if (strstr($cronstr['mon'],'*/'))
					$mon=explode('/',$cronstr['mon']);
				else
					$mon=explode(',',$cronstr['mon']);
				?>
				<select name="cronmon[]" id="cronmon" multiple="multiple">
				<option value="*"<?php selected(in_array('*',$mon,true),true,true); ?>><?php _e('Any (*)','wpematico'); ?></option>
				<option value="1"<?php selected(in_array('1',$mon,true),true,true); ?>><?php _e('January'); ?></option>
				<option value="2"<?php selected(in_array('2',$mon,true),true,true); ?>><?php _e('February'); ?></option>
				<option value="3"<?php selected(in_array('3',$mon,true),true,true); ?>><?php _e('March'); ?></option>
				<option value="4"<?php selected(in_array('4',$mon,true),true,true); ?>><?php _e('April'); ?></option>
				<option value="5"<?php selected(in_array('5',$mon,true),true,true); ?>><?php _e('May'); ?></option>
				<option value="6"<?php selected(in_array('6',$mon,true),true,true); ?>><?php _e('June'); ?></option>
				<option value="7"<?php selected(in_array('7',$mon,true),true,true); ?>><?php _e('July'); ?></option>
				<option value="8"<?php selected(in_array('8',$mon,true),true,true); ?>><?php _e('August'); ?></option>
				<option value="9"<?php selected(in_array('9',$mon,true),true,true); ?>><?php _e('September'); ?></option>
				<option value="10"<?php selected(in_array('10',$mon,true),true,true); ?>><?php _e('October'); ?></option>
				<option value="11"<?php selected(in_array('11',$mon,true),true,true); ?>><?php _e('November'); ?></option>
				<option value="12"<?php selected(in_array('12',$mon,true),true,true); ?>><?php _e('December'); ?></option>
				</select>
			</div>
			<div>
				<b><?php _e('Weekday:','wpematico'); ?></b><br />
				<select name="cronwday[]" id="cronwday" multiple="multiple">
				<?php 
				if (strstr($cronstr['wday'],'*/'))
					$wday=explode('/',$cronstr['wday']);
				else
					$wday=explode(',',$cronstr['wday']);
				?>
				<option value="*"<?php selected(in_array('*',$wday,true),true,true); ?>><?php _e('Any (*)','wpematico'); ?></option>
				<option value="0"<?php selected(in_array('0',$wday,true),true,true); ?>><?php _e('Sunday'); ?></option>
				<option value="1"<?php selected(in_array('1',$wday,true),true,true); ?>><?php _e('Monday'); ?></option>
				<option value="2"<?php selected(in_array('2',$wday,true),true,true); ?>><?php _e('Tuesday'); ?></option>
				<option value="3"<?php selected(in_array('3',$wday,true),true,true); ?>><?php _e('Wednesday'); ?></option>
				<option value="4"<?php selected(in_array('4',$wday,true),true,true); ?>><?php _e('Thursday'); ?></option>
				<option value="5"<?php selected(in_array('5',$wday,true),true,true); ?>><?php _e('Friday'); ?></option>
				<option value="6"<?php selected(in_array('6',$wday,true),true,true); ?>><?php _e('Saturday'); ?></option>
				</select>
			</div>
			<br class="clear" />
		</div>
	<?php
	}

	//*************************************************************************************
	public static function feeds_box( $post ) {  
		global $post, $campaign_data, $cfg, $helptip;
		$campaign_feeds = $campaign_data['campaign_feeds'];
		?>  
		<div class="feed_header">
			<div class="feed_column"><?php _e('Feed URL', 'wpematico'  ) ?></div>
			<?php do_action('wpematico_campaign_feed_header_column'); ?>
			<label id="msgdrag"></label>
			<div class="right ">
				<div style="float:left;margin-left:2px;">
					<input id="psearchtext" name="psearchtext" class="srchbdr0" type="text" value=''>
				</div>
				<div id="productsearch" class="left mya4_sprite searchIco" style="margin-top:4px;"></div>
			</div>
		</div>
		<div id="feeds_list" class="maxhe290" data-callback="jQuery('#msgdrag').html('<?php _e('Update Campaign to save Feeds order', 'wpematico'  ); ?>').fadeIn();"> <!-- callback script to run on successful sort -->
			<?php //foreach($campaign_feeds as $i => $feed): 
				for ($i = 0; $i <= count(@$campaign_feeds); $i++) : ?>
				<?php $feed = @$campaign_feeds[$i]; ?>			
				<?php $lastitem = $i==count(@$campaign_feeds); ?>			
				<div id="feed_ID<?php echo $i; ?>" class="sortitem <?php if(($i % 2) == 0) echo 'bw'; else echo 'lightblue'; ?> <?php if($lastitem) echo 'feed_new_field'; ?> " <?php if($lastitem) echo 'style="display:none;"'; ?> > <!-- sort item -->
					<div class="sorthandle"> </div> <!-- sort handle -->
					<div class="feed_column" id="">
						<input name="campaign_feeds[<?php echo $i; ?>]" type="text" value="<?php echo $feed ?>" class="large-text feedinput"/><a href="<?php echo $feed ?>" title="<?php _e('Open URL in a new browser tab', 'wpematico' ); ?>" target="_Blank" class="wpefeedlink"></a>
					</div>
					<?php do_action('wpematico_campaign_feed_body_column',$feed,$cfg); ?>
					<?php do_action('nonstatic_feedat','', $cfg); //deprecated!!! 20160309  ?>

					<div class="" id="feed_actions">
						<label title="<?php _e('Delete this item',  'wpematico'  ); ?>" onclick="delete_feed_url('#feed_ID<?php echo $i; ?>');" class="bicon delete left"></label>
						<label title="<?php _e('Check if this item work', 'wpematico' ); ?>" id="checkfeed" class="check1feed bicon warning left"></label>
						
					</div>
				</div>
				<?php $a=$i;
			endfor; ?>
		</div>
		<input id="feedfield_max" value="<?php echo $a; ?>" type="hidden" name="feedfield_max">
		<?php do_action('wpematico_campaign_feed_panel'); ?>
		<div id="paging-box">		  
			<a href="JavaScript:void(0);" class="button-primary add" id="addmorefeed" style="font-weight: bold; text-decoration: none;"> <?php _e('Add Feed', 'wpematico'  ); ?>.</a>
			<span class="button-primary" id="checkfeeds" style="font-weight: bold; text-decoration: none;" ><?php _e('Check all feeds', 'wpematico' ); ?>.</span>
			<?php do_action('wpematico_campaign_feed_panel_buttons'); ?>
			<?php // if($cfg['nonstatic']){NoNStatic::bimport();} ?>
			<div class="pbfeet right">
				<?php _e('Displaying', 'wpematico' ); ?> <span id="pb-totalrecords" class="b"><?php echo $i-1; ?></span>&nbsp;<span id="pb-ptext">feeds </span>
				<label class="right ui-icon select_down" onclick="jQuery('#feeds_list').toggleClass('maxhe290');jQuery(this).toggleClass('select_up');" title="<?php _e('Display all feeds', 'wpematico' ); ?>"></label>
			</div>
		</div>
	<?php
	}
		
	
	//********************************
	public static function log_box( $post ) {
		global $post, $campaign_data, $helptip;
		$mailaddresslog = $campaign_data['mailaddresslog'];
		$mailerroronly = $campaign_data['mailerroronly'];
		?>
		<?php _e('E-Mail-Adress:', 'wpematico' ); ?>
		<input name="mailaddresslog" id="mailaddresslog" type="text" value="<?php echo $mailaddresslog; ?>" class="large-text" /><br />
		<input class="checkbox" value="1" type="checkbox" <?php checked($mailerroronly,true); ?> name="mailerroronly" /> <?php _e('Send only E-Mail on errors.', 'wpematico' ); ?>
		<?php
	}

	//********************************
	public static function tags_box( $post ) {
		global $post, $campaign_data, $cfg, $helptip;
		$campaign_tags = $campaign_data['campaign_tags'];
		?>			
		<?php if( $cfg['nonstatic'] ) { NoNStatic :: protags($post); }  ?>
		<p><b><?php echo '<label for="campaign_tags">' . __('Tags:', 'wpematico' ) . '</label>'; ?></b>
		<textarea style="" class="large-text" id="campaign_tags" name="campaign_tags"><?php echo stripslashes($campaign_tags); ?></textarea><br />
		<?php echo __('Enter comma separated list of Tags.', 'wpematico' ); ?></p>
		<?php if( $cfg['nonstatic'] ) { NoNStatic :: protags1($post); }  ?>
		<?php
	}

	//********************************
	public static function cat_box( $post ) {
		global $post, $campaign_data, $helptip;
		$campaign_categories = $campaign_data['campaign_categories'];
		$campaign_autocats = $campaign_data['campaign_autocats'];
		//get_categories()
		$args = array(
			'descendants_and_self' => 0,
			'selected_cats' => $campaign_categories,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => 'category',
			'checked_ontop' => true
		);

		//$aa = wp_terms_checklist( 0, $args );
		?>
		<input class="checkbox" type="checkbox"<?php checked($campaign_autocats ,true);?> name="campaign_autocats" value="1" id="campaign_autocats"/> <b><?php echo '<label for="campaign_autocats">' . __('Add auto Categories', 'wpematico' ) . '</label>'; ?></b>
		<span class="mya4_sprite infoIco help_tip" title="<?php echo $helptip['autocats']; ?>"></span>
		<div class="inside" style="overflow-y: scroll; overflow-x: hidden; max-height: 250px;">
			<b><?php _e('Current Categories', 'wpematico' ); ?></b>
			<div class="right ">
				<div style="float:left;margin-left:2px;display:none;" id="catfield">
					<input id="psearchcat" name="psearchcat" class="srchbdr0" type="text" value=''>
				</div>
				<div id="catsearch" class="left mya4_sprite searchIco" style="margin-top:4px;"></div>
			</div>

			<ul id="categories" style="font-size: 11px;">
				<?php 
				wp_terms_checklist( 0, $args );
				//self :: Categories_box($campaign_categories) ?>
			</ul> 
		</div>
		<div id="major-publishing-actions">
			<a href="JavaScript:void(0);" id="quick_add" onclick="arand=Math.floor(Math.random()*101);jQuery('#categories').append('&lt;li&gt;&lt;input type=&quot;checkbox&quot; name=&quot;campaign_newcat[]&quot; checked=&quot;checked&quot;&gt; &lt;input type=&quot;text&quot; id=&quot;campaign_newcatname'+arand+'&quot; class=&quot;input_text&quot; name=&quot;campaign_newcatname[]&quot;&gt;&lt;/li&gt;');jQuery('#campaign_newcatname'+arand).focus();" style="font-weight: bold; text-decoration: none;" ><?php _e('Quick add',  'wpematico' ); ?>.</a>
		</div>
	<?php
	}

	// ** Muestro Categorías seleccionables 
/*	private static function _wpe_edit_cat_row($category, $level, &$data) {  
		$category = get_category( $category );
		$name = $category->cat_name;
		echo '
		<li style="margin-left:'.$level.'5px" class="jobtype-select checkbox">
		<input type="checkbox" value="' . $category->cat_ID . '" id="category_' . $category->cat_ID . '" name="campaign_categories[]" ';
		echo (in_array($category->cat_ID, $data )) ? 'checked="checked"' : '' ;
		echo '>
		<label for="category_' . $category->cat_ID . '">' . $name . '</label></li>';
	}

	private static function Categories_box(&$data, $parent = 0, $level = 0, $categories = 0)  {    
		if ( !$categories )
			$categories = get_categories(array('hide_empty' => 0));

		if(function_exists('_get_category_hierarchy'))
		  $children = _get_category_hierarchy();
		elseif(function_exists('_get_term_hierarchy'))
		  $children = _get_term_hierarchy('category');
		else
		  $children = array();

		if ( $categories ) {
			ob_start();
			foreach ( $categories as $category ) {
				if ( $category->parent == $parent) {
					echo "\t" . self :: _wpe_edit_cat_row($category, $level, $data);
					if ( isset($children[$category->term_id]) )
						self :: Categories_box($data, $category->term_id, $level + 1, $categories );
				}
			}
			$output = ob_get_contents();
			ob_end_clean();

			echo $output;
		} else {
			return false;
		}
	}
*/
	// Action handler - The 'Save' button is about to be drawn on the advanced edit screen.
	public static function post_submitbox_start()	{
		global $post, $campaign_data, $helptip;
		if($post->post_type != 'wpematico') return $post->ID;
		
		$campaign_posttype = $campaign_data['campaign_posttype'];
		$campaign_customposttype = $campaign_data['campaign_customposttype'];
		wp_nonce_field( 'edit-campaign', 'wpematico_nonce' ); 
		?><div class="clear"></div><div style="margin: 0 0 15px 0;">
		<div class="postbox inside" style="min-width:30%;float:left;padding: 5px;">
			<b><?php _e('Status',  'wpematico' ); ?></b><br />
			<label><input type="radio" name="campaign_posttype" <?php echo checked('publish',$campaign_posttype,false); ?> value="publish" /> <?php _e('Published'); ?></label><br />
			<label><input type="radio" name="campaign_posttype" <?php echo checked('private',$campaign_posttype,false); ?> value="private" /> <?php _e('Private'); ?></label><br />
			<label><input type="radio" name="campaign_posttype" <?php echo checked('pending',$campaign_posttype,false); ?> value="pending" /> <?php _e('Pending'); ?></label><br />
			<label><input type="radio" name="campaign_posttype" <?php echo checked('draft',$campaign_posttype,false); ?> value="draft" /> <?php _e('Draft'); ?></label>
		</div>
		<div class="postbox inside" style="float: right; min-width: 40%;padding: 5px 10px;">
		<b><?php _e('Post type',  'wpematico' ); ?></b><br />
		<?php
			$args=array(
			  'public'   => true
			); 
			$output = 'names'; // names or objects, note names is the default
			$output = 'objects'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'
			$post_types=get_post_types($args,$output,$operator); 
			foreach ($post_types  as $post_type_obj ) {
				$post_type = $post_type_obj->name;
				$post_label = $post_type_obj->labels->name;
				if ($post_type == 'wpematico') continue;
/*				echo '<input class="radio" type="radio" '.checked($post_type,$campaign_customposttype,false).' name="campaign_customposttype" value="'. $post_type. '" id="customtype_'. $post_type. '" /> <label for="customtype_'. $post_type. '">'.
						__( $post_label ) .' ('. __( $post_type ) .')</label><br />';
*/
				echo '<input class="radio" type="radio" '.checked($post_type,$campaign_customposttype,false).' name="campaign_customposttype" value="'. $post_type. '" id="customtype_'. $post_type. '" /> <label for="customtype_'. $post_type. '">'.
						__( $post_label ) .'</label><br />';
			}
		?>
		</div>
		</div><div class="clear"></div>	<?php 
	}

}
?>