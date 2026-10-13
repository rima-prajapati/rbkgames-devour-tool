<?php
/**
 * Plugin Name: DEVOUR one-click login
 * Description: Lets DEVOUR log you in as an administrator with a single-use link that expires after 60 seconds. Installed and updated by DEVOUR.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    if (empty($_GET['wpm_login'])) {
        return;
    }
    nocache_headers();

    $fail = function ($message) {
        wp_die(
            esc_html($message) . '<p><a href="' . esc_url(wp_login_url()) . '">Log in with your password</a></p>',
            'DEVOUR login',
            ['response' => 403]
        );
    };

    $token = (string) wp_unslash($_GET['wpm_login']);
    if (!preg_match('/^[A-Za-z0-9_-]{32,128}$/', $token)) {
        $fail('Invalid login link.');
    }

    // DEVOUR stores only the SHA-256 of the token, for 60 seconds.
    $key = 'wpm_login_' . hash('sha256', $token);
    $login = get_transient($key);
    // Single use: only the request that actually deletes the token may log in.
    if (!$login || !delete_transient($key)) {
        $fail('This login link has expired or was already used. Click "Login to Admin" in DEVOUR again.');
    }

    $user = get_user_by('login', $login);
    if (!$user || !user_can($user, 'manage_options')) {
        $admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID']);
        $user = $admins ? $admins[0] : null;
    }
    if (!$user) {
        $fail('No administrator account was found on this site.');
    }

    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, false, is_ssl());
    do_action('wp_login', $user->user_login, $user);

    wp_safe_redirect(admin_url());
    exit;
}, 1);
