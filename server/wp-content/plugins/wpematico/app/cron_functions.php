<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
* Add cron interval
*
* @access protected
* @param array $schedules
* @return array
*/
function wpematico_intervals($schedules) {
	$schedules['wpematico_int'] = array('interval' => '300', 'display' => __('WPeMatico'));
//			$schedules = array_merge( $intervals, $schedules);
	return $schedules;
}

function wpem_cron_callback() {
	$args = array( 'post_type' => 'wpematico', 'orderby' => 'ID', 'order' => 'ASC', 'post_status' => 'publish', 'numberposts' => -1 );
	$campaigns = get_posts( $args );
	foreach( $campaigns as $post ) {
		$campaign = WPeMatico :: get_campaign( $post->ID );
		$activated = $campaign['activated'];
		$cronnextrun = $campaign['cronnextrun'];
		if ( !$activated )
			continue;
		if ( $cronnextrun <= current_time('timestamp') ) {
			WPeMatico :: wpematico_dojob( $post->ID );
		}
	}
}
