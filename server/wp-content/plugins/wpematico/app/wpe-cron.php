<?php
ignore_user_abort(true);

if ( !empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON') )
	die();
	
if ( !defined('ABSPATH') ) {
	/** Set up WordPress environment */
	//require_once( '/wp-load.php');
	if( !(@include $_SERVER['DOCUMENT_ROOT'].'/wp-load.php') )
		if( !(@include $_SERVER['DOCUMENT_ROOT'].'../wp-load.php') )
		if( !(@include 'wp-load.php') )
		if( !(@include '../../../wp-load.php') )
		if( !(@include '../../../../wp-load.php') )
		if( !(@include '../../../../../wp-load.php') )
		if( !(@include __DIR__ .'/../../../../wp-load.php') )
			die('<H1>Can\'t include wp-load. Report to Technical Support form on https://etruel.com/support</H1>');
}
	
Function linelog($handle, $msg){
	if($handle!==FALSE) {
		fwrite($handle , $msg.PHP_EOL);
	}
}

/**
 * Arguments by command line or by HTTP to use with any external cron
 */
//if (PHP_SAPI === 'cli') {
//    $argument1 = $argv[1];
//    $argument2 = $argv[2];
//}
//else {
//    $argument1 = $_GET['argument1'];
//    $argument2 = $_GET['argument2'];
//}
if(!class_exists('WPeMatico')){
	?>
	<h2>Can\'t find class WPeMatico</h2>
	<p>To use this file go to Settings page, Cron and Scheduler Settings:</p>
	<p>Select: Disable WPeMatico schedulings.</p>
	<p>Un-select: Disable all WP_Cron.</p>
	<hr>
	<p>If the issue persists report to Technical Support form on https://etruel.com/my-account/support.</p>
	<?php
	die();
}
$cfg = WPeMatico::check_options( get_option( 'WPeMatico_Options' ) );

if($cfg['logexternalcron']) {
	$upload_dir = wp_upload_dir(); 
	//try open log file on uploads dir 
	if($upload_dir['error']==FALSE) {
		$filedir = $upload_dir['basedir'].'/';
	}else {  //if can't open in uploads dir try in this dir
		$filedir = '';	
	}
}

/**
 *  check password only when set_cron_code=true
 */
if( $cfg['set_cron_code'] ) 
if(!isset($_REQUEST['code']) || !($_REQUEST['code'] == $cfg['cron_code']) ) {
	die('Warning! cron.php was called with the wrong password or without one!');
}

/**
 * WP Cron deactivated, works in normal way with campaign squeduler cronnextrun
 */
$disablewpcron = false;
if($cfg['disablewpcron']) {
	$disablewpcron = true;
}
/**
 * WPeMatico schedulers deactivated, works running all campaigns at once without check cronnextrun
 * @todo check parameters to run every campaign individually with a campaign ID and a password
 */
$dontruncron = false;
if($cfg['dontruncron']) { 
	$dontruncron = true;
}

/**
 * 
 */
if(!$disablewpcron && !$dontruncron) {
	die( "To use this file you must deactivate cron on WPeMatico Settings Page in Wordpress admin." );
}

$args = array( 'post_type' => 'wpematico', 'orderby' => 'ID', 'order' => 'ASC', 'numberposts' => -1 );
$campaigns = get_posts( $args );
foreach( $campaigns as $post ) {
	$campaign = WPeMatico :: get_campaign( $post->ID );
	$activated = $campaign['activated'];
	$cronnextrun = $campaign['cronnextrun'];
	if ( !$activated )
		continue;
	if ( $cronnextrun >= current_time('timestamp') || $dontruncron ) {
		if($cfg['logexternalcron']) {
			@$file_handle = fopen($filedir.sanitize_file_name($post->post_title.".txt.log"), "w+");
			$msg = 'Running WPeMatico external WP-Cron'."\n";
			linelog($file_handle , $msg.PHP_EOL); 
			echo $msg;
			$msg = $post->post_title.' '."\n";
			linelog($file_handle , $msg.PHP_EOL); 
			echo $msg;
		}
		$msg = WPeMatico :: wpematico_dojob( $post->ID );
		
		if($cfg['logexternalcron']) {
			$msg = strip_tags($msg);
			$msg .= "\n";
			linelog($file_handle , $msg.PHP_EOL); 
			echo '<pre>'.$msg.'</pre>';
		}	
	}
}

if($cfg['logexternalcron'] && $file_handle != false ) {
	$msg = ' Success !'."\n";
	linelog($file_handle , $msg.PHP_EOL); echo $msg;
	if($file_handle!==FALSE) {
		fclose($file_handle ); 
	}
}

die('Completed.');

?>