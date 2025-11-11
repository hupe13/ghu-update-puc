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
