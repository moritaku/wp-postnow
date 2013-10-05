<?php
/*
Plugin Name: WP-PostNow
Plugin URI: http://blog.duffytoy.com/plugin
Description: WP PostNow can encourage posts in the automated email on a regular schedule.
Author: moritaku
Version: 1.5.0
Author URI: http://blog.duffytoy.com
License: GPL2
Text Domain: WP-PostNow
Domain Path: /languages
*/

/*  Copyright 2013 moritaku (email : takuma@duffytoy.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WP_POSTNOW_UNIXTIME', 86400);
define('WP_POSTNOW_DOMAIN', 'WP-PostNow');
load_plugin_textdomain(WP_POSTNOW_DOMAIN, false, dirname(plugin_basename( __FILE__ ) ).'/languages' );

function wp_postnow_add_intervals($schedules) {
	$schedules['twodays'] = array(
		'interval' => 60*60*24*2,
		'display' => __('twodays')
	);
	$schedules['threedays'] = array(
		'interval' => 60*60*24*3,
		'display' => __('threedays')
	);
        $schedules['fourdays'] = array(
		'interval' => 60*60*24*4,
		'display' => __('fourdays')
	);
        $schedules['fivedays'] = array(
		'interval' => 60*60*24*5,
		'display' => __('fivedays')
	);
        $schedules['sixdays'] = array(
		'interval' => 60*60*24*6,
		'display' => __('sixdays')
	);
        $schedules['weekly'] = array(
		'interval' => 60*60*24*7,
		'display' => __('weekly')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'wp_postnow_add_intervals'); 

function wp_postnow_active() {
    
    $local_time = current_time('timestamp');
    $local_ymd = gmdate('Y-m-d',$local_time);
    $local_ymdhms = $local_ymd." 10:00:00";
    $local_timestamp = strtotime($local_ymdhms);
    $default_interval = 7;
    $default_email = get_bloginfo('admin_email');
    $default_sitename = get_bloginfo('name');
    $default_siteurl = get_bloginfo('url');
    $default_domain = str_replace('http://www.', '', get_bloginfo('url'));
    $default_title = $default_sitename . __(' post is stagnated.', WP_POSTNOW_DOMAIN);
    $default_message = $default_sitename . "[ ". $default_siteurl ." ]" . __(" Let's post the article right now", WP_POSTNOW_DOMAIN);

    if(!get_option('wp_postnow_lastposted') || get_option('wp_postnow_lastposted') !== $local_timestamp) {
        update_option('wp_postnow_lastposted',$local_timestamp);
    }
    if(!get_option('wp_postnow_csi')) {
        update_option('wp_postnow_csi','1');
    }
    if(!get_option('wp_postnow_futurepost')) {
        update_option('wp_postnow_futurepost','TRUE');
    }
    if(!get_option('wp_postnow_interval')) {
        update_option('wp_postnow_interval',$default_interval * WP_POSTNOW_UNIXTIME);
    }
    if(!get_option('wp_postnow_email')) {
        update_option('wp_postnow_email',$default_email);
    }
    if(!get_option('wp_postnow_fromdisp')) {
        update_option('wp_postnow_fromdisp','WP-PostNow');
    }
    if(!get_option('wp_postnow_mailfrom')) {
        update_option('wp_postnow_mailfrom','postnow@'.$default_domain);
    }
    if(!get_option('wp_postnow_title')) {
        update_option('wp_postnow_title',$default_title);
    }
    if(!get_option('wp_postnow_message')) {
        update_option('wp_postnow_message',$default_message);
    }
    //wp_schedule_event($local_timestamp + WP_POSTNOW_UNIXTIME, 'daily', 'wp_postnow_cron');
    wp_schedule_event( ceil( current_time('timestamp') / 86400 ) * 86400 + ( 10 - get_option( 'gmt_offset' ) ) * 3600, 'daily', 'wp_postnow_cron' );
}
register_activation_hook(__FILE__, 'wp_postnow_active');

function wp_postnow_get_latest_posttime() {
    global $wpdb;
    $futurepost = get_option('wp_postnow_futurepost');
    $post_status_flag = ($futurepost === 'TRUE')? "post_status IN ('publish','future')" : "post_status = 'publish'";
    $post_author_data = get_users(array('search' => get_option('wp_postnow_email'), 'search_columns' => 'user_email' ));
    $post_author_id = $post_author_data[0]->data->ID;
    $query = "SELECT post_date FROM ".$wpdb->posts." WHERE $post_status_flag AND post_type = 'post' AND post_author = ".$post_author_id." ORDER BY `".$wpdb->posts."`.`post_date` DESC LIMIT 0,1";
    $query = $wpdb->prepare($query);
    $lastpost_date = $wpdb->get_var($query);
    
    if(isset($lastpost_date)){
        return strtotime($wpdb->get_var($query));
    }else{
        return FALSE;
    }
}

function wp_postnow_compare() {
    $latest = wp_postnow_get_latest_posttime();
    
    if($latest){
        $terms = intval(get_option('wp_postnow_interval')) + intval($latest);
        if(intval($terms) < intval(current_time('timestamp'))) {
            $wp_postnow_title  = get_option('wp_postnow_title');
            $wp_postnow_message = get_option('wp_postnow_message');
            $send_flag = TRUE;
        }else{
            $send_flag = FALSE;
        }
    }else{
        $wp_postnow_title  = get_bloginfo('name'). __(' You have not posted yet!!', WP_POSTNOW_DOMAIN);
        $wp_postnow_message = get_bloginfo('name')."[ ".get_bloginfo('url')." ]". __(' You have not posted yet!! Visitor is waiting for your post!', WP_POSTNOW_DOMAIN);
        $send_flag =TRUE;
    }
    
    if($send_flag){
        $wp_postnow_email = get_option('wp_postnow_email');
        $wp_postnow_mailfrom = get_option('wp_postnow_mailfrom');
        $wp_postnow_ccmail = get_option('wp_postnow_ccmail');
        $wp_postnow_fromdisp = get_option('wp_postnow_fromdisp');
        
        $mail_header = "From: ".$wp_postnow_mailfrom;
        if(!empty($wp_postnow_fromdisp)){
            $mail_header = "From: ".mb_encode_mimeheader($wp_postnow_fromdisp)."<".$wp_postnow_mailfrom.">";
        }
        if(!empty($wp_postnow_ccmail)){
            $mail_header .= PHP_EOL;
            $mail_header .= "Cc: ".$wp_postnow_ccmail;
        }
        
        
        mb_send_mail($wp_postnow_email, $wp_postnow_title, $wp_postnow_message, $mail_header);
    }
}
add_action('wp_postnow_cron', 'wp_postnow_compare');

function wp_postnow_lastposted(){
    update_option('wp_postnow_lastposted',current_time('timestamp'));
}
add_action('publish_post','wp_postnow_lastposted');

/*
function wp_postnow_lastposted_future(){
    update_option('wp_postnow_lastposted',time());
}
add_action('future_to_publish','wp_postnow_lastposted_future');
*/

function wp_postnow_deactive() {
    wp_clear_scheduled_hook('wp_postnow_cron');
    delete_option('wp_postnow_lastposted');
    delete_option('wp_postnow_csi');
    delete_option('wp_postnow_futurepost');
    delete_option('wp_postnow_interval');
    delete_option('wp_postnow_message');
    delete_option('wp_postnow_title');
    delete_option('wp_postnow_email');
    delete_option('wp_postnow_ccmail');
    delete_option('wp_postnow_fromdisp');
    delete_option('wp_postnow_mailfrom');
}
register_deactivation_hook(__FILE__, 'wp_postnow_deactive');

add_action('admin_menu', 'wp_postnow_admin_menu');

require_once 'wp-postnow-view.php';

function wp_postnow_admin_menu(){
    add_options_page(
            'WP-PostNow',
            'WP-PostNow',
            'administrator',
            __FILE__,
            'wp_postnow_admin_page'
    );
}

?>