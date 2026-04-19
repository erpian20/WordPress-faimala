<?php
/**
 * PowerUp Theme Security Functions
 *
 * @package PowerUp_Theme
 * @subpackage Security
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add security headers.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_security_headers() {
    // Prevent clickjacking.
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing.
    header('X-Content-Type-Options: nosniff');

    // Enable XSS protection.
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy.
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions policy.
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // Content Security Policy (CSP) - adjust as needed.
    $csp = array(
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https:",
        "style-src 'self' 'unsafe-inline' https:",
        "img-src 'self' data: https:",
        "font-src 'self' https:",
        "connect-src 'self' https:",
        "frame-src 'self' https:",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    );
    header("Content-Security-Policy: " . implode('; ', $csp));
}
add_action('send_headers', 'powerup_security_headers');

/**
 * Sanitize user input.
 *
 * @since 1.0.0
 * @param mixed $input User input to sanitize.
 * @return mixed Sanitized input.
 */
function powerup_sanitize_input($input) {
    if (is_array($input)) {
        return array_map('powerup_sanitize_input', $input);
    }

    if (is_string($input)) {
        $input = wp_unslash($input);
        $input = sanitize_text_field($input);
        $input = esc_html($input);
    }

    return $input;
}

/**
 * Validate and sanitize email address.
 *
 * @since 1.0.0
 * @param string $email Email address to validate.
 * @return string|bool Validated email or false.
 */
function powerup_validate_email($email) {
    $email = sanitize_email($email);
    if (!is_email($email)) {
        return false;
    }
    return $email;
}

/**
 * Create nonce for forms and AJAX requests.
 *
 * @since 1.0.0
 * @param string $action Action name for nonce.
 * @return string Nonce value.
 */
function powerup_create_nonce($action = 'powerup_action') {
    return wp_create_nonce($action);
}

/**
 * Verify nonce.
 *
 * @since 1.0.0
 * @param string $nonce  Nonce to verify.
 * @param string $action Action name for nonce.
 * @return bool True if valid, false otherwise.
 */
function powerup_verify_nonce($nonce, $action = 'powerup_action') {
    return wp_verify_nonce($nonce, $action);
}

/**
 * Add nonce field to forms.
 *
 * @since 1.0.0
 * @param string $action Action name for nonce.
 * @param string $name   Name of the nonce field.
 * @param bool   $referer Whether to set the referer field.
 * @return string Nonce field HTML.
 */
function powerup_nonce_field($action = 'powerup_action', $name = 'powerup_nonce', $referer = true) {
    return wp_nonce_field($action, $name, $referer, false);
}

/**
 * Secure file upload validation.
 *
 * @since 1.0.0
 * @param array $file File array from $_FILES.
 * @return array|WP_Error Validated file or error.
 */
function powerup_validate_upload($file) {
    $errors = new WP_Error();

    // Check file size (max 5MB).
    $max_size = 5 * 1024 * 1024; // 5MB in bytes.
    if ($file['size'] > $max_size) {
        $errors->add('file_too_large', __('File is too large. Maximum size is 5MB.', 'powerup-theme'));
    }

    // Check file type.
    $allowed_types = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    );

    $filetype = wp_check_filetype($file['name']);
    if (!in_array($filetype['type'], $allowed_types, true)) {
        $errors->add('invalid_file_type', __('Invalid file type.', 'powerup-theme'));
    }

    // Check for PHP files disguised as images.
    $filename = $file['name'];
    if (preg_match('/\.php[0-9]*$/i', $filename)) {
        $errors->add('php_file_disguised', __('PHP files are not allowed.', 'powerup-theme'));
    }

    if ($errors->has_errors()) {
        return $errors;
    }

    return $file;
}

/**
 * Sanitize file name.
 *
 * @since 1.0.0
 * @param string $filename Original filename.
 * @return string Sanitized filename.
 */
function powerup_sanitize_filename($filename) {
    $filename = remove_accents($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = strtolower($filename);
    $filename = preg_replace('/_+/', '_', $filename);

    // Add timestamp to prevent overwriting.
    $info = pathinfo($filename);
    $name = $info['filename'];
    $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

    $timestamp = current_time('timestamp');
    $filename = $name . '_' . $timestamp . $ext;

    return $filename;
}

/**
 * Escape output for different contexts.
 *
 * @since 1.0.0
 * @param string $output Output to escape.
 * @param string $context Context for escaping.
 * @return string Escaped output.
 */
function powerup_escape_output($output, $context = 'html') {
    switch ($context) {
        case 'html':
            return esc_html($output);
        case 'attr':
            return esc_attr($output);
        case 'url':
            return esc_url($output);
        case 'js':
            return esc_js($output);
        case 'textarea':
            return esc_textarea($output);
        default:
            return esc_html($output);
    }
}

/**
 * Prevent username enumeration.
 *
 * @since 1.0.0
 * @param array $query_vars Query variables.
 * @return array Modified query variables.
 */
function powerup_prevent_username_enumeration($query_vars) {
    if (!is_admin() && isset($query_vars['author'])) {
        wp_die(__('Author archives are disabled.', 'powerup-theme'), 403);
    }
    return $query_vars;
}
add_filter('query_vars', 'powerup_prevent_username_enumeration');

/**
 * Hide WordPress version.
 *
 * @since 1.0.0
 * @return string Empty string.
 */
function powerup_remove_version() {
    return '';
}
add_filter('the_generator', 'powerup_remove_version');

/**
 * Disable XML-RPC if not needed.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_disable_xmlrpc() {
    if (!apply_filters('powerup_enable_xmlrpc', false)) {
        add_filter('xmlrpc_enabled', '__return_false');
    }
}
add_action('init', 'powerup_disable_xmlrpc');

/**
 * Limit login attempts.
 *
 * @since 1.0.0
 * @param WP_Error $errors WP_Error object.
 * @return WP_Error Modified error object.
 */
function powerup_limit_login_attempts($errors) {
    $login_attempts = get_transient('powerup_login_attempts');
    if (false === $login_attempts) {
        $login_attempts = 0;
    }

    $login_attempts++;
    set_transient('powerup_login_attempts', $login_attempts, 15 * MINUTE_IN_SECONDS);

    if ($login_attempts > 5) {
        $errors->add('too_many_attempts', __('Too many login attempts. Please try again in 15 minutes.', 'powerup-theme'));
    }

    return $errors;
}
add_filter('wp_authenticate_user', 'powerup_limit_login_attempts', 10, 1);

/**
 * Reset login attempts on successful login.
 *
 * @since 1.0.0
 * @param string $user_login Username.
 * @param WP_User $user      WP_User object.
 * @return void
 */
function powerup_reset_login_attempts($user_login, $user) {
    delete_transient('powerup_login_attempts');
}
add_action('wp_login', 'powerup_reset_login_attempts', 10, 2);

/**
 * Add security checks to theme activation.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_theme_activation_checks() {
    // Check for required PHP version.
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        wp_die(__('This theme requires PHP 7.4 or higher. Please upgrade your PHP version.', 'powerup-theme'));
    }

    // Check for required WordPress version.
    global $wp_version;
    if (version_compare($wp_version, '5.8', '<')) {
        wp_die(__('This theme requires WordPress 5.8 or higher. Please upgrade WordPress.', 'powerup-theme'));
    }
}
add_action('after_switch_theme', 'powerup_theme_activation_checks');

/**
 * Log security events.
 *
 * @since 1.0.0
 * @param string $event     Event description.
 * @param string $severity  Event severity (info, warning, error).
 * @param array  $data      Additional data.
 * @return void
 */
function powerup_log_security_event($event, $severity = 'info', $data = array()) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $log_entry = sprintf(
        '[%s] [%s] %s %s',
        current_time('mysql'),
        strtoupper($severity),
        $event,
        !empty($data) ? json_encode($data) : ''
    );

    error_log($log_entry);
}

/**
 * Validate and sanitize SQL queries.
 *
 * @since 1.0.0
 * @param string $query SQL query.
 * @return string|bool Sanitized query or false.
 */
function powerup_sanitize_sql_query($query) {
    // Remove potentially dangerous SQL keywords.
    $dangerous_keywords = array(
        'DROP', 'DELETE', 'TRUNCATE', 'ALTER', 'CREATE', 'EXEC', 'EXECUTE',
        'INSERT', 'UPDATE', 'REPLACE', 'UNION', 'LOAD_FILE', 'OUTFILE',
        'DUMPFILE', 'INTO OUTFILE', 'INTO DUMPFILE'
    );

    foreach ($dangerous_keywords as $keyword) {
        if (stripos($query, $keyword) !== false) {
            powerup_log_security_event(
                'Potentially dangerous SQL keyword detected',
                'warning',
                array('keyword' => $keyword, 'query' => $query)
            );
            return false;
        }
    }

    return $query;
}