<?php
/**
 *  Github Plugin Update Checker
 *
 * @package ghu-update-puc
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p6\Vcs\GitHubApi;

$ghupuc_token = ghupuc_query_token_needed();

if ( false === $ghupuc_token['ghupuc_github_denied'] || $ghupuc_token['ghupuc_update_token'] !== '' ) {
	$ghupuc_git_repos         = ghupuc_get_repos();
	$ghupuc_my_update_checker = array();
	foreach ( $ghupuc_git_repos as $ghupuc_git_repo => $ghupuc_value ) {
		if ( $ghupuc_git_repos[ $ghupuc_git_repo ]['local'] !== $ghupuc_git_repo ) {
			$ghupuc_my_update_checker[ $ghupuc_git_repo ] = PucFactory::buildUpdateChecker(
				$ghupuc_git_repos[ $ghupuc_git_repo ]['url'],
				$ghupuc_git_repos[ $ghupuc_git_repo ]['local'],
				basename( dirname( $ghupuc_git_repos[ $ghupuc_git_repo ]['local'] ) ),
			);

			// Set the branch that contains the stable release.
			$ghupuc_my_update_checker[ $ghupuc_git_repo ]->setBranch( 'main' );

			if ( $ghupuc_token['ghupuc_update_token'] !== '' ) {
				// Optional: If you're using a private repository, specify the access token like this:
				$ghupuc_my_update_checker[ $ghupuc_git_repo ]->setAuthentication( $ghupuc_token['ghupuc_update_token'] );
			}

			// update tags or release
			if ( ! $ghupuc_git_repos[ $ghupuc_git_repo ]['release'] ) {
				$ghupuc_my_update_checker[ $ghupuc_git_repo ]->addFilter(
					'vcs_update_detection_strategies',
					function ( $strategies ) {
						unset( $strategies[ GitHubApi::STRATEGY_LATEST_RELEASE ] );
						return $strategies;
					}
				);
			}
			// enable assets and download_count
			$ghupuc_my_update_checker[ $ghupuc_git_repo ]->getVcsApi()->enableReleaseAssets();
		}
	}
}

function ghupuc_update_puc_error( $error, $response = null, $url = null, $slug = null ) {
	if ( ! isset( $slug ) ) {
		return;
	}
	$ghupuc_git_repos = ghupuc_get_repos();
	$valid_slug       = false;
	foreach ( $ghupuc_git_repos as $ghupuc_git_repo => $value ) {
		if ( $slug === dirname( plugin_basename( $ghupuc_git_repos[ $ghupuc_git_repo ]['local'] ) ) ) {
			$valid_slug = true;
		}
	}
	if ( ! $valid_slug ) {
		return;
	}
	if ( wp_remote_retrieve_response_code( $response ) === 403 ) {
		// var_dump( 'Permission denied' );
		set_transient( 'ghupuc_github_403', true, DAY_IN_SECONDS );
	}
}
add_action( 'puc_api_error', 'ghupuc_update_puc_error', 10, 4 );
