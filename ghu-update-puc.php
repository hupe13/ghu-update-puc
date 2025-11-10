<?php
/**
 * Plugin Name:       Updates by hupe13 hosted on GitHub
 * Description:       If you have installed any plugins from hupe13 hosted on Github, you can receive the updates here.
 * Plugin URI:        https://leafext.de/en/
 * Update URI:        https://github.com/hupe13/ghu-update-puc
 * Version:           251110
 * Requires at least: 6.3
 * Requires PHP:      8.1
 * Author:            hupe13
 * Author URI:        https://leafext.de/en/
 * Network:           true
 * License:           GPL v2 or later
 *
 * @package ghu-update-puc
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// hide the plugin on sites that are not the main site.
add_filter(
	'all_plugins',
	function ( $plugins ) {
		if ( get_current_blog_id() !== 1 ) {
			$plugin = trailingslashit( basename( __DIR__ ) ) . basename( __FILE__ );
			unset( $plugins[ $plugin ] );
		}
		return $plugins;
	}
);

// Return if not admin
if ( ! is_admin() ) {
	return;
}

if ( ! is_main_site() ) {
	return;
}

define( 'GHUPUC_DIR', plugin_dir_path( __FILE__ ) ); // /pfad/wp-content/plugins/ghu-update-puc/ .
define( 'GHUPUC_NAME', basename( GHUPUC_DIR ) ); // ghu-update-puc
define( 'GHUPUC_BASENAME', plugin_basename( __FILE__ ) ); // ghu-update-puc/ghu-update-puc.php

require_once __DIR__ . '/admin.php';

// Add settings to network plugin page
function ghupuc_network_add_action_update_links( $actions, $plugin ) {
	if ( $plugin === GHUPUC_BASENAME ) {
			$actions[] = '<a href="' . esc_url( admin_url( 'admin.php' ) . '?page=ghu-update-puc' ) . '">' . esc_html__( 'Settings', 'ghu-update-puc' ) . '</a>';
	}
	return $actions;
}
add_filter( 'network_admin_plugin_action_links', 'ghupuc_network_add_action_update_links', 10, 4 );

// Add settings to plugin page
function ghupuc_add_action_update_links( $actions ) {
	$actions[] = '<a href="' . esc_url( admin_url( 'admin.php' ) . '?page=ghu-update-puc' ) . '">' . esc_html__( 'Settings', 'ghu-update-puc' ) . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . GHUPUC_NAME . '/ghu-update-puc.php', 'ghupuc_add_action_update_links' );

// Github Update
require_once __DIR__ . '/github/github-functions.php';
require_once __DIR__ . '/github/github-settings.php';
require_once __DIR__ . '/github/github-check-update.php';
