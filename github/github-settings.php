<?php
/**
 *  Admin PUC Settings
 *
 * @package ghu-update-puc
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Init settings fuer update
function ghupuc_update_init() {
	add_settings_section( 'updating_settings', '', '', 'ghupuc_settings_updating' );
	add_settings_field( 'ghupuc_updating', esc_html__( 'Github token', 'ghu-update-puc' ), 'ghupuc_form_updating', 'ghupuc_settings_updating', 'updating_settings' );
	if ( get_option( 'leafext_updating' ) !== false && get_option( 'ghupuc_updating' ) === false ) {
		add_option( 'ghupuc_updating', get_option( 'leafext_updating' ) );
		delete_option( 'leafext_updating' );
	}
	register_setting( 'ghupuc_settings_updating', 'ghupuc_updating', 'ghupuc_validate_updating' );
}
add_action( 'admin_init', 'ghupuc_update_init' );

// Baue Abfrage der Params
function ghupuc_form_updating() {
	$setting = get_option( 'ghupuc_updating', array( 'token' => '' ) );
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}
	// var_dump($setting);
	echo '<input ' . esc_html( $disabled ) . ' type="text" size="30" name="ghupuc_updating[token]" value="' . esc_html( $setting['token'] ) . '" />';
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function ghupuc_validate_updating( $input ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'ghupuc_updating', 'ghupuc_updating_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			return $input;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'ghupuc_updating' );
		}
		return false;
	}
}

function ghupuc_query_token_needed() {
	$main_site_id = get_main_site_id();
	if ( is_multisite() ) {
		$setting = get_blog_option( $main_site_id, 'ghupuc_updating', array( 'token' => '' ) );
	} else {
		$setting = get_option( 'ghupuc_updating', array( 'token' => '' ) );
	}
	if ( $setting && isset( $setting['token'] ) && $setting['token'] !== '' ) {
		$ghupuc_update_token = $setting['token'];
	} else {
		$ghupuc_update_token = '';
	}
	if ( is_multisite() ) {
		switch_to_blog( $main_site_id );
		$ghupuc_github_denied = get_transient( 'ghupuc_github_403' );
		restore_current_blog();
	} else {
		$ghupuc_github_denied = get_transient( 'ghupuc_github_403' );
	}
	return array(
		'ghupuc_update_token'  => $ghupuc_update_token,
		'ghupuc_github_denied' => $ghupuc_github_denied,
	);
}

// Github Token form
function ghupuc_token_form() {
	$token = ghupuc_query_token_needed();
	echo wp_kses_post(
		wp_sprintf(
			/* translators: %s is a link and the name of Plugin Update Checker. */
			__( 'You will get updates with the %s.', 'ghu-update-puc' ),
			'<a href="https://github.com/YahnisElsts/plugin-update-checker">Plugin Update Checker</a>'
		)
	) . ' ';
	if ( $token['ghupuc_update_token'] === '' ) {
		// var_dump($ghupuc_github_denied);
		if ( false !== $token['ghupuc_github_denied'] ) {
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %s is a link. */
					__( 'You need a %1$sGithub token%2$s to receive updates successfully.', 'ghu-update-puc' ),
					'<a href="https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens">',
					'</a>'
				)
			) . '<br>';
		} else {
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %s is a link. */
					__( 'Maybe you need a %1$sGithub token%2$s to receive updates successfully.', 'ghu-update-puc' ),
					'<a href="https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens">',
					'</a>'
				)
			) . '<br>';
		}
	}
	echo '<form method="post" action="options.php">';
	settings_fields( 'ghupuc_settings_updating' );
	do_settings_sections( 'ghupuc_settings_updating' );
	if ( current_user_can( 'manage_options' ) ) {
		wp_nonce_field( 'ghupuc_updating', 'ghupuc_updating_nonce' );
		submit_button();
		submit_button( esc_html__( 'Reset', 'ghu-update-puc' ), 'delete', 'delete', false );
	}
	echo '</form>';
}
