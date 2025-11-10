<?php
/**
 *  Functions for admin
 *
 * @package ghu-update-puc
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// linkes Menu
function ghupuc_add_sub_page() {
	add_submenu_page(
		'options-general.php',
		'Github Update PUC',
		'Github Update PUC',
		'manage_options',
		GHUPUC_NAME,
		'ghupuc_update_admin'
	);
}
add_action( 'admin_menu', 'ghupuc_add_sub_page' );

// Old menu
function ghupuc_add_menu() {
	add_menu_page(
		'Github Update PUC',
		'Github Update PUC',
		'manage_options',
		'github-settings',
		'ghupuc_update_admin'
	);
}
add_action( 'admin_menu', 'ghupuc_add_menu', 10 );

function ghupuc_remove_menu() {
	remove_menu_page(
		'github-settings',
	);
	// Deprecated:  strip_tags(): Passing null to parameter #1 ($string) of type string is
	// deprecated in /wp-admin/admin-header.php on line 41
	global $title;
	//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$title = 'Github Update PUC';
}
add_action( 'admin_menu', 'ghupuc_remove_menu', 99 );

// Admin page for the plugin
function ghupuc_update_admin() {
	echo '<h3>' . esc_html__( 'Updates by hupe13 hosted on GitHub', 'ghu-update-puc' ) . '</h3>';
	ghupuc_token_form();
	echo '<h3>' . esc_html__( 'Plugins by hupe13', 'ghu-update-puc' ) . '</h3>';
	ghupuc_table_repos();
	echo '<h3>' . esc_html__( 'Github Repositories managed by Plugin Update Checker', 'ghu-update-puc' ) . '</h3>';
	echo '<pre>';
	//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
	var_dump( ghupuc_get_repos() );
	echo '</pre>';
}
