<?php
/*
 * Plugin Name:       Woo Set Event Date
 * Description:       Set the date of your event. It is ideally suited to selling workshops/courses on WooCommerce.
 * Version:           1.0.1
 * Author:            tomeckiStudio
 * Author URI:        https://tomecki.studio/
 * Text Domain:       woo-set-event-date
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') or die('You do not have permissions to this file!');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
	add_action('init', 'wsed_init');
}else{
	add_action('admin_init', 'wsed_plugin_deactivate');
	add_action('admin_notices', 'wsed_woocommerce_missing_notice');
}

function wsed_init(){
	if(is_admin()){
		include_once 'includes/wsed-backend.php';
	}
	include_once 'includes/wsed-frontend.php';
}

function wsed_woocommerce_missing_notice(){
	echo '<div class="error"><p>' . __( 'You need an active WooCommerce for the Woo Set Event Date plugin to work!', 'woo-set-event-date') . '</p></div>';
	if (isset($_GET['activate']))
		unset($_GET['activate']);	
}

function wsed_plugin_deactivate(){
	deactivate_plugins(plugin_basename(__FILE__));
}
