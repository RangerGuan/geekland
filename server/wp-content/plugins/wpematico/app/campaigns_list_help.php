<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$helpcampaignlist = array( 
	'Campaigns List' => array( 
		'columns' => array( 
			'title' => __('Columns.', 'wpematico' ),
			'tip' => '<b>'.__('Campaign Name', 'wpematico' ).'</b>: '.__('The given name. Hover it to display the Quick actions below the name.', 'wpematico' ).'<br>'.
				'<b>'.__('Publish as', 'wpematico' ).'</b>: '.__('Post type and Post Status that will be used for the new entries.', 'wpematico' ).'<br>'.
				'<b>'.__('Campaign Type', 'wpematico' ).'</b>: '.__('Useful when you have more campaign types from Addons. You can hide the column in Screen Options Tab.', 'wpematico' ).'<br>'.
				'<b>'.__('Current State', 'wpematico' ).'</b>: '.__('Button Bar to Run, Activate or Stop the campaign. Its states are given by the colors.', 'wpematico' ).'<br>'.
				'<b>'.__('Last Run', 'wpematico' ).'</b>: '.__('Last date that the campaign was executed and the seconds it took to complete. If the campaign is Activated also shows the Datetime for the Next Run.', 'wpematico' ).'<br>'.
				'<b>'.__('Posts', 'wpematico' ).'</b>: '.__('The posts quantity fetched by this campaign since its start or the last Reset.', 'wpematico' ).'<br>',
		),
	),
	'Quick Actions' => array( 
		'actions' => array( 
			'title' => __('Quick Actions in every Row.', 'wpematico' ),
			'tip' => 
				'<b>'.__('Edit', 'wpematico' ).'</b>: '.__('Like clicking in the name, open the campaign to edit its details.', 'wpematico' ).'<br>'.
				'<b>'.__('Quick Edit', 'wpematico' ).'</b>: '.__('Edit some main fields, without enter in the campaign editor.', 'wpematico' ).'<br>'.
				'<b>'.__('Trash', 'wpematico' ).'</b>: '.__('Send the campaign to the trash.', 'wpematico' ).'<br>'.
				'<b>'.__('Copy', 'wpematico' ).'</b>: '.__('Creates a new campaign by copying all the fields of this.  The word "(copy)" is added to its title.', 'wpematico' ).'<br>'.
				'<b>'.__('Reset', 'wpematico' ).'</b>: '.__('Deletes the Log, Last Run data and the Posts fetched by the campaign.', 'wpematico' ).'<br>'.
				'<b>'.__('Del Hash', 'wpematico' ).'</b>: '.__('Every campaign save the hash of the last feed item fetched to avoid duplicates. This action deletes it.  Useful to make tests to allow fetch an item again. ', 'wpematico' ).'<br>'.
				'<b>'.__('See Log', 'wpematico' ).'</b>: '.__('Open a popup window to show the Last Log of the campaign. No other logs are saved.', 'wpematico' ).'<br>'.
				'<b>'.__('Export', 'wpematico' ).'</b>: '.__('Professional feature. Exports only this campaign data to a file that can be imported later by the plugin in other Wordpress website.', 'wpematico' ).'<br>',
		),
	),
	'Run Selected Campaigns' => array( 
		'run_selected' => array( 
			'title' => __('Run Multiple Campaigns at once.', 'wpematico' ),
			'tip' => 
				__('You can select all or some campaigns by clicking in its checkbox in the first column.', 'wpematico' ).'<br>'.
				__('When click the orange button, the process starts and can take a long time.', 'wpematico' ).'<br>'.
				__('A notice for each campaign will be displayed at top of the page. The Post column will be updated in red if at least one post was fetched.', 'wpematico' ).'<br>',
		),
	),
	'Bulk Actions' => array( 
		'bulk_actions' => array( 
			'title' => __('Bulk actions.', 'wpematico' ),
			'tip' => 
				__('There are few fields that can be edited via Bulk Edit select field, by selecting some campaigns at a time.', 'wpematico' ).'<br>'.
				__('There is also the option to delete some selected campaigns at once with "Send to Trash" bulk action.', 'wpematico' ).'<br>'.
				__('Select the campaigns, then select the bulk action and click the "Apply" Button.', 'wpematico' ),
		),
	),
);
$helpcampaignlist = apply_filters('wpematico_help_campaign_list', $helpcampaignlist);

$screen = $current_screen; //WP_Screen::get('wpematico_page_wpematico_settings ');
foreach($helpcampaignlist as $key => $section){
	$tabcontent = '';
	foreach($section as $section_key => $sdata){
		$helptip[$section_key] = htmlentities($sdata['tip']);
		$tabcontent .= '<p><strong>' . $sdata['title'] . '</strong><br />'.
				$sdata['tip'] . '</p>';
		$tabcontent .= (isset($sdata['plustip'])) ?	'<p>' . $sdata['plustip'] . '</p>' : '';
	}
	$screen->add_help_tab( array(
		'id'	=> $key,
		'title'	=> $key,
		'content'=> $tabcontent,
	) );
}
