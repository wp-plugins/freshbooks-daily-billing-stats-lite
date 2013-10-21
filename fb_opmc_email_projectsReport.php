<?php
/*
Plugin Name: FreshBooks Daily Billing Stats Lite
Description: This plugin allows you to send an email to an email address you specify showing the total amount invoiced weekly via your FreshBooks account over the past two weeks. It also contains the amount invoiced for each day of the week.
Version: 1.0
Author: OPMC
Author URI: http://www.opmc.com.au
Network: true
*/

////my////

/**
 * Globals
 */
require('lib/FreshBooksRequest.php');

// Setup the login credentials
if( get_option( 'afbspre_freshbooks_subdomain' ) ) $domain = get_option( 'afbspre_freshbooks_subdomain' );
if( get_option( 'afbspre_freshbooks_token' ) ) $token = get_option( 'afbspre_freshbooks_token' );
FreshBooksRequest::init($domain, $token);


/**
 * Add admin interface
 */
 
/* admin side*/
function afbspre_admin() {  include('admin/afbspre_events_admin.php');}

add_action('admin_menu', 'afbspre_admin_actions');
function afbspre_admin_actions() {
	add_menu_page("FreshBooks Daily Billing Stats Lite" , "FreshBooks Reports" , "level_3"   , "afbspre_events"  , "afbspre_admin", plugins_url('/images/ASREIcon.png',__FILE__) );
}

/**
 * cron job setup.
 */

 /* When plugin is activated*/
function afbspre_install() {
	//cron job setup
	wp_schedule_event( current_time( 'timestamp' ), 'daily', 'my_daily_event');	//other options for 2nd parameter: hourly, twicedaily, daily
}
register_activation_hook( __FILE__, 'afbspre_install' );

/* When plugin is deactivated*/
function afbspre_remove() {
	//cron job removal
	wp_clear_scheduled_hook('my_daily_event');
}
register_deactivation_hook( __FILE__, 'afbspre_remove' );

//cron job related code
add_action('my_daily_event', 'do_this_daily');
function do_this_daily() {

	$msg = '
<table style="font-family: arial; color:#616264; width:60%;padding:25px;margin: auto;">
<tbody><tr style="font-size: 25px;"><td colspan="2">FreshBooks Daily Billing Stats Lite</td></tr>
<tr style="font-size: 13px; color: rgb(0, 164, 233);"><td colspan="2">by <a href="http://www.opmc.com.au">OPMC</a></td></tr>';

 // determine which day is today
	$currentDayOfWeek = date('N', current_time( 'timestamp' ) ) - 1; 
	$currentTimeStamp = current_time( 'timestamp' );//time();
	
/*
 * This week
 */

	$startOfWeeksTimeStamp = $currentTimeStamp - ( $currentDayOfWeek * 24 * 60 * 60 );
	$start_date = date( 'Y-m-d', $startOfWeeksTimeStamp );
	$end_date = date( 'Y-m-d', current_time( 'timestamp' ) );	//date( 'Y-m-d', time() );
	
	$msg_col1 = ' ';

	$allInvoices = findInvoices($start_date, $end_date);
//$allInvoices = findInvoices("2013-10-05", "2013-10-15");
	$allInvoices = (array_slice($allInvoices,1));
	$allInvoices = (array_slice($allInvoices['invoices'],1));


	$totalAmount = 0.0;
	$eachDayOfWeek = '';
	$daysofWeek = array();
	foreach($allInvoices['invoice'] as $invoice){
		$f_dayFound = false;
		$wDay = date('l', strtotime($invoice['date'] .'GMT'));
		foreach($daysofWeek as $eDay => $eAmount){
			if($eDay == $wDay){
				$f_dayFound = true;
				$daysofWeek[$eDay] = floatval($eAmount) +  floatval($invoice['amount']);
				break;
			}
		}
		if(!$f_dayFound){
			$daysofWeek[$wDay] = floatval($invoice['amount']);
		}
		$totalAmount += floatval($invoice['amount']);
	}
	
	$daysofWeek1 = array();

		if(isset($daysofWeek['Monday']))
			$daysofWeek1['Monday'] = $daysofWeek['Monday'];
		else
			$daysofWeek1['Monday'] = 0.0;
			
		if(isset($daysofWeek['Tuesday']))
			$daysofWeek1['Tuesday'] = $daysofWeek['Tuesday'];
		else
			$daysofWeek1['Tuesday'] = 0.0;

		if(isset($daysofWeek['Wednesday']))
			$daysofWeek1['Wednesday'] = $daysofWeek['Wednesday'];
		else
			$daysofWeek1['Wednesday'] = 0.0;

		if(isset($daysofWeek['Thursday']))
			$daysofWeek1['Thursday'] = $daysofWeek['Thursday'];
		else
			$daysofWeek1['Thursday'] = 0.0;

		if(isset($daysofWeek['Friday']))
			$daysofWeek1['Friday'] = $daysofWeek['Friday'];
		else
			$daysofWeek1['Friday'] = 0.0;

		if(isset($daysofWeek['Saturday']))
			$daysofWeek1['Saturday'] = $daysofWeek['Saturday'];
		else
			$daysofWeek1['Saturday'] = 0.0;
			
		if(isset($daysofWeek['Sunday']))
			$daysofWeek1['Sunday'] = $daysofWeek['Sunday'];
		else
			$daysofWeek1['Sunday'] = 0.0;


			
	$daysofWeek = array_slice($daysofWeek1,0,intval($currentDayOfWeek) + 1);
	
	foreach($daysofWeek as $key => $val){
		$eachDayOfWeek .= '<tr><td style="color: rgb(97, 98, 100);">'. $key .'</td><td align="right" style="color: rgb(0, 164, 233);float:right !important;padding-right:15px;">$'. number_format($val,2) .'</td></tr>';
	}
	$msg_col1 .= '
<tr><td style="font-size: 20px; padding-top: 15px;">Billed this week</td><td align="right" style="font-size: 25px; color: rgb(0, 164, 233);float:right !important;padding-top: 15px;padding-right: 15px;">$'. number_format($totalAmount,2) .'</td></tr>
<tr><td colspan="2" style="border-top: 1px solid #616264; font-size: 18px; color: rgb(97, 98, 100); ">&nbsp;</td></tr>
'. $eachDayOfWeek ;
 // $msg_col1 .= $eachDayOfWeek ;

$msg .= $msg_col1;


/*
 * Last week
 */
 	$startOf_lastWeek = $startOfWeeksTimeStamp - ( 7 * 24 * 60 * 60 );
	$endOf_lastWeek = $startOfWeeksTimeStamp - ( 1 * 24 * 60 * 60 );
	
	$start_date = date( 'Y-m-d', $startOf_lastWeek );
	$end_date = date( 'Y-m-d', $endOf_lastWeek );

	$msg_col1 = '';

	$allInvoices = findInvoices($start_date,$end_date);
	$allInvoices = (array_slice($allInvoices,1));
	$allInvoices = (array_slice($allInvoices['invoices'],1));
	// $msg = json_encode($allInvoices['invoice']);	

	$totalAmount = 0.0;
	$eachDayOfWeek = '';
	$daysofWeek = array();
	foreach($allInvoices['invoice'] as $invoice){
		$f_dayFound = false;
		$wDay = date('l', strtotime($invoice['date'] .'GMT'));
		foreach($daysofWeek as $eDay => $eAmount){
			if($eDay == $wDay){
				$f_dayFound = true;
				$daysofWeek[$eDay] = floatval($eAmount) +  floatval($invoice['amount']);
				break;
			}
		}
		if(!$f_dayFound){
			$daysofWeek[$wDay] = floatval($invoice['amount']);
		}
		$totalAmount += floatval($invoice['amount']);
	}
	$daysofWeek1 = array();
		if(isset($daysofWeek['Monday']))
			$daysofWeek1['Monday'] = $daysofWeek['Monday'];
		else
			$daysofWeek1['Monday'] = 0.0;
			
		if(isset($daysofWeek['Tuesday']))
			$daysofWeek1['Tuesday'] = $daysofWeek['Tuesday'];
		else
			$daysofWeek1['Tuesday'] = 0.0;

		if(isset($daysofWeek['Wednesday']))
			$daysofWeek1['Wednesday'] = $daysofWeek['Wednesday'];
		else
			$daysofWeek1['Wednesday'] = 0.0;

		if(isset($daysofWeek['Thursday']))
			$daysofWeek1['Thursday'] = $daysofWeek['Thursday'];
		else
			$daysofWeek1['Thursday'] = 0.0;

		if(isset($daysofWeek['Friday']))
			$daysofWeek1['Friday'] = $daysofWeek['Friday'];
		else
			$daysofWeek1['Friday'] = 0.0;

		if(isset($daysofWeek['Saturday']))
			$daysofWeek1['Saturday'] = $daysofWeek['Saturday'];
		else
			$daysofWeek1['Saturday'] = 0.0;
			
		if(isset($daysofWeek['Sunday']))
			$daysofWeek1['Sunday'] = $daysofWeek['Sunday'];
		else
			$daysofWeek1['Sunday'] = 0.0;

	$daysofWeek = $daysofWeek1;
	
	foreach($daysofWeek as $key => $val){
		$eachDayOfWeek .= '<tr><td style="color: rgb(97, 98, 100);">'. $key .'</td><td align="right" style="color: rgb(0, 164, 233);float:right !important;padding-right:15px;">$'. number_format($val,2) .'</td></tr>';
	}
	$msg_col1 .= '
<tr><td style="font-size: 20px; padding-top: 15px;">Billed last week</td><td align="right" style="font-size: 25px; color: rgb(0, 164, 233);float:right !important;padding-top: 15px;padding-right: 15px;">$'. number_format($totalAmount,2) .'</td></tr>
<tr><td colspan="2" style="border-top: 1px solid #616264; font-size: 18px; color: rgb(97, 98, 100);">&nbsp;</td></tr>
'. $eachDayOfWeek .'
<tr><td colspan="2" style="border-bottom: 1px solid #616264;  color: rgb(97, 98, 100);">&nbsp;</td></tr>
<tr>
	<td style="text-align:left;">Powered by <a style="color: rgb(0, 164, 233);"href="http://www.opmc.com.au" target="_blank">OPMC</a></td>
	<td style="text-align:right;"><a style="color: rgb(0, 164, 233);"href="http://www.opmc.com.au" target="_blank"><img style="padding-top: 10px;" height="60px" src="http://www.opmc.com.au/site/wp-content/uploads/opmc_logo1.png"/></a></td>
</tr>
</tbody></table>';


$msg .= $msg_col1;

//////////////////////sending email///////////////////////
	
	$subject = "FreshBooks Daily Billing Stats Lite";
	add_filter( 'wp_mail_content_type', 'set_html_content_type' );

	if( get_option( 'afbspre_receiver_email' ) )
		wp_mail( get_option( 'afbspre_receiver_email' ), $subject, $msg);
	else
		wp_mail( get_option( 'admin_email' ), $subject, $msg);

	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

}
/*
 * changing From and Name address and content type of email
 */

function aaopmcfp_filter_wp_mail_from($email){
	$myfrom = get_option( 'admin_email' );
	return $myfrom;
}
add_filter("wp_mail_from", "aaopmcfp_filter_wp_mail_from");

function aaopmcfp_filter_wp_mail_from_name($from_name){
	$myfrom = get_bloginfo();
	return $myfrom;
}
add_filter("wp_mail_from_name", "aaopmcfp_filter_wp_mail_from_name");

function set_html_content_type() {return 'text/html';}

/*
 *cron job settings
 */
 
add_filter( 'cron_schedules', 'cron_add_5minutes' );
function cron_add_5minutes( $schedules ) {
	// Adds once weekly to the existing schedules.
 	$schedules['each5minutes'] = array(
 		'interval' => 60,
 		'display' => __( 'Once A Finve Minutes' )
 	);
 	return $schedules;
}

/*
 * Returns invoices
 * parameter @string dateFrom - to filter invoices from date
 * parameter @string dateTo - to filter invoices to date
 * parameter @array filters - all filters as array in key => value pairs
 */
function findInvoices($dateFrom=null, $dateTo=null, $filters=array()){
	$fbInvoices = array();
	/**********************************************
	 * Fetch all invoices within given dates if not null
	 **********************************************/
	$fb = new FreshBooksRequest('invoice.list');
	if($dateFrom != null && $dateTo != null){
		if(sizeof($filters) > 0)
			$fb->post(array_merge(array( 'per_page' => '250', 'date_from' => $dateFrom, 'date_to' => $dateTo), $filters));
		else
			$fb->post(array( 'per_page' => '250', 'date_from' => $dateFrom, 'date_to' => $dateTo ));
	}
	
	$fb->request();
	if($fb->success()){
		$fbInvoices = $fb->getResponse();
//		$fbInvoices = $fbInvoices[time_entries];//[time_entry];
	}else{
		return $fb->getError();
	}
	return $fbInvoices;
}


/*
 * Ajaxt callback function
 */

function cron_actionSaveData_callback() {

	if(isset($_POST['SubmitNewEmail'])) {
		$event_name = $_POST['afbspre_receiver_email'];

		if(get_option( 'afbspre_receiver_email' )) update_option('afbspre_receiver_email', $event_name);
		else add_option( 'afbspre_receiver_email', $event_name );
	}
	if(isset($_POST['SubmitNewEmail'])) {
		$event_name = $_POST['afbspre_freshbooks_subdomain'];
		
		if(get_option( 'afbspre_freshbooks_subdomain' )) update_option('afbspre_freshbooks_subdomain', $event_name);
		else add_option( 'afbspre_freshbooks_subdomain', $event_name );
	}
	if(isset($_POST['SubmitNewEmail'])) {
		$event_name = $_POST['afbspre_freshbooks_token'];
		
		if(get_option( 'afbspre_freshbooks_token' )) update_option('afbspre_freshbooks_token', $event_name);
		else add_option( 'afbspre_freshbooks_token', $event_name );
	}
		echo 1;
		die();

}
add_action('wp_ajax_cron_actionSaveData', 'cron_actionSaveData_callback');


 function cron_actionButton_callback() {
	do_this_daily();
	
	echo 1;
	die(); // this is required to return a proper result
}
add_action('wp_ajax_cron_actionButton', 'cron_actionButton_callback');


//////////


?>