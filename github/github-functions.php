<?php
/**
 *  Functions to use for PUC
 *
 * @package ghu-update-puc
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

/**
 * For translating
 */
function ghupuc_update_textdomain() {
	if ( file_exists( GHUPUC_DIR . 'github/lang/ghu-update-puc-' . get_locale() . '.mo' ) ) {
		$mofile = GHUPUC_DIR . 'github/lang/ghu-update-puc-' . get_locale() . '.mo';
		load_textdomain( 'ghu-update-puc', $mofile );
	}
}
add_action( 'plugins_loaded', 'ghupuc_update_textdomain' );

// Display array as table
function ghupuc_html_table( $data = array() ) {
	$rows      = array();
	$cellstyle = ( is_singular() || is_archive() ) ? "style='border:1px solid #195b7a;'" : '';
	foreach ( $data as $row ) {
		$cells = array();
		foreach ( $row as $cell ) {
			$cells[] = '<td ' . $cellstyle . ">{$cell}</td>";
		}
		$rows[] = '<tr>' . implode( '', $cells ) . '</tr>' . "\n";
	}
	$head = '<div style="width:' . ( ( is_singular() || is_archive() ) ? '100' : '80' ) . '%;">';
	$head = $head . '<figure class="wp-block-table aligncenter is-style-stripes"><table border=1>';
	return $head . implode( '', $rows ) . '</table></figure></div>';
}

// Repos on Github
function ghupuc_get_repos() {
	$releases  = array(
		'extensions-leaflet-map'         => false,
		'extensions-leaflet-map-testing' => false,
	);
	$git_repos = array();
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	foreach ( $all_plugins as $plugin => $plugin_data ) {
		if ( $plugin_data['Author'] === 'hupe13' ) {
			if ( strpos( $plugin_data['UpdateURI'], 'https://github.com/hupe13/' ) !== false
				|| strpos( $plugin_data['PluginURI'], 'https://github.com/hupe13/' ) !== false
				|| file_exists( WP_PLUGIN_DIR . '/' . dirname( $plugin ) . '/github' )
			) {
				$slug    = basename( $plugin, '.php' );
				$release = isset( $releases[ $slug ] ) ? $releases[ $slug ] : true;
				$url     = $plugin_data['UpdateURI'];
				if ( $url === '' ) {
					$url = $plugin_data['PluginURI'];
				}
				if ( $url !== '' ) {
					$git_repos[ $slug ] = array(
						'url'     => $url,
						'local'   => WP_PLUGIN_DIR . '/' . $plugin,
						'release' => $release,
					);
				}
			}
		}
	}
	return $git_repos;
}

function ghupuc_table_repos() {
	$slugs = array_keys( ghupuc_get_repos() );
	$table = array();

	foreach ( $slugs as $slug ) {
		$ghupuc_plugins = glob( WP_PLUGIN_DIR . '/*/' . $slug . '.php/' );
		if ( count( $ghupuc_plugins ) > 0 ) {
			foreach ( $ghupuc_plugins as $ghupuc_plugin ) {
				$entry         = array();
				$plugin_data   = get_plugin_data( $ghupuc_plugin );
				$entry['name'] = $plugin_data['Name'];
				if ( strpos( $plugin_data['UpdateURI'], 'https://github.com/hupe13/' ) !== false ) {
					$entry['hosted'] = 'Github';
				} elseif ( file_exists( dirname( $ghupuc_plugin ) . '/github' ) ) {
					$entry['hosted'] = 'Github';
				} elseif ( strpos( $plugin_data['PluginURI'], 'https://github.com/hupe13/' ) !== false ) {
					$entry['hosted'] = 'Github';
				} else {
					$entry['hosted'] = 'WordPress';
				}
				$entry['active'] = array();
				$blogs           = array();
				if ( function_exists( 'get_sites' ) ) {
					$sites = get_sites();

					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						if ( is_plugin_active( plugin_basename( $ghupuc_plugin ) ) ) {
							$entry['active'][] = $site->blog_id;
						}
						restore_current_blog();
					}
					if ( count( $entry['active'] ) === count( $sites ) && $slug === 'ghu-update-puc' ) {
						$entry['active'] = array( '1' );
					}
					foreach ( $entry['active'] as $site ) {
						$blogs[] = '<a href="' . get_admin_url( $site ) .
						'plugins.php?s=leaflet&plugin_status=all">' .
						get_blog_option( $site, 'blogname' )
						. '</a>';
					}
				} else {
					if ( is_plugin_active( plugin_basename( $ghupuc_plugin ) ) ) {
						$entry['active'][] = 1;
					}
					foreach ( $entry['active'] as $site ) {
						$blogs[] = '<a href="' . get_admin_url( $site ) .
						'plugins.php?s=leaflet&plugin_status=all">' .
						get_option( 'blogname' )
						. '</a>';
					}
				}
				$entry['hosted'] = '<div style="text-align:center">' . $entry['hosted'] . '</div>';
				$entry['active'] = '<ul><li style="text-align:center">' . implode( '<li style="text-align:center">', $entry['active'] ) . '</ul>';
				$entry['links']  = '<ul><li>' . implode( '<li>', $blogs ) . '</ul>';
				$table[]         = $entry;
			}
		}
	}
	$header = array(
		'<b>' . __( 'Name', 'ghu-update-puc' ) . '</b>',
		'<b>' . __( 'hosted on', 'ghu-update-puc' ) . '</b>',
		'<b>' . __( 'active', 'ghu-update-puc' ) . '</b>',
		'<b>' . __( 'link to blog', 'ghu-update-puc' ) . '</b>',
	);

	array_unshift( $table, $header );
	echo wp_kses_post( ghupuc_html_table( $table ) );
}
