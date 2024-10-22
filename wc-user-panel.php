<?php

/**
 * Plugin Name: WooCommerce User Panel with SMS Verification
 * Description: A comprehensive user panel for WooCommerce with SMS verification using Satfade and OTP Kavenagar.
 ** Version: 1.0
 * * * Author: milad jafari gavzan
 * * * Author URI: https://miladjafarigavzan.ir
 * * * License: GPL-2.0+
 * */
/*

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register the user panel page in My Account
add_action( 'woocommerce_account_dashboard', 'wc_custom_user_panel' );

function wc_custom_user_panel() {
	echo '<h2>Your User Dashboard</h2>';
	echo '<p>Welcome, ' . wp_get_current_user()->display_name . '!</p>';
	// Add custom user panel content here, e.g., orders, profile settings, SMS verification
	echo '<a href="' . esc_url( wc_logout_url() ) . '" class="button">Logout</a>';
}

// Add the SMS verification option in the user panel
add_action( 'init', 'wc_user_panel_add_verification' );

function wc_user_panel_add_verification() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Handle SMS verification request
	if ( isset( $_POST['send_verification_sms'] ) ) {
		$user_id      = get_current_user_id();
		$phone_number = get_user_meta( $user_id, 'billing_phone', true );
		if ( $phone_number ) {
			// Send OTP using Kavenegar API
			$otp = rand( 100000, 999999 );
			update_user_meta( $user_id, 'otp_code', $otp );
			send_sms_kavenegar( $phone_number, $otp );
		}
	}

	// Handle OTP verification submission
	if ( isset( $_POST['verify_otp'] ) ) {
		$user_id       = get_current_user_id();
		$submitted_otp = sanitize_text_field( $_POST['otp_code'] );
		$stored_otp    = get_user_meta( $user_id, 'otp_code', true );

		if ( $submitted_otp === $stored_otp ) {
			update_user_meta( $user_id, 'is_verified', true );
			echo '<p>Your phone number has been verified!</p>';
		} else {
			echo '<p>Invalid OTP. Please try again.</p>';
		}
	}
}

// Send SMS using Kavenegar API
function send_sms_kavenegar( $phone_number, $otp ) {
	$api_key = 'YOUR_KAVENEGAR_API_KEY';
	$url     = 'https://api.kavenegar.com/v1/' . $api_key . '/verify/lookup.json?receptor=' . $phone_number . '&token=' . $otp . '&template=YourTemplate';

	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body   = wp_remote_retrieve_body( $response );
	$result = json_decode( $body, true );

	if ( $result && isset( $result['return']['status'] ) && $result['return']['status'] == 200 ) {
		return true;
	}

	return false;
}

// Add user profile section for SMS verification
add_action( 'woocommerce_edit_account_form', 'wc_user_profile_sms_verification' );

function wc_user_profile_sms_verification() {
	$user_id     = get_current_user_id();
	$is_verified = get_user_meta( $user_id, 'is_verified', true );

	if ( ! $is_verified ) {
		echo '<h3>Phone Number Verification</h3>';
		echo '<form method="post">';
		echo '<p><label for="phone_number">Phone Number:</label>';
		echo '<input type="text" name="phone_number" value="' . esc_attr( get_user_meta( $user_id, 'billing_phone', true ) ) . '" disabled></p>';

		echo '<p><input type="submit" name="send_verification_sms" value="Send Verification SMS" class="button"></p>';
		echo '<p><label for="otp_code">Enter OTP:</label>';
		echo '<input type="text" name="otp_code" value=""></p>';
		echo '<p><input type="submit" name="verify_otp" value="Verify OTP" class="button"></p>';
		echo '</form>';
	} else {
		echo '<p>Your phone number has been verified.</p>';
	}
}

