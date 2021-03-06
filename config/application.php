<?php

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);
define('SITE_ROOT', $root_dir);

/** @var string Document Root */
$webroot_dir = $root_dir . '/web';

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = new Dotenv\Dotenv($root_dir);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
    $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'WP_HOME', 'WP_SITEURL']);
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * URLs
 */
// define('WP_CACHE', true);
if ( !empty( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] === 'dev.houstonpublicmedia.org' && strpos( $_SERVER['HTTP_X_ORIGINAL_HOST'], 'ngrok.io' ) !== FALSE ) :
	define('WP_HOME', 'https://' . $_SERVER['HTTP_X_ORIGINAL_HOST'] );
	define('WP_SITEURL', 'https://' . $_SERVER['HTTP_X_ORIGINAL_HOST'] . '/wp' );
else :
	define('WP_HOME', env('WP_HOME'));
	define('WP_SITEURL', env('WP_SITEURL'));
endif;

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/app');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);

/**
 * DB settings
 */
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASSWORD', env('DB_PASSWORD'));
define('DB_HOST', env('DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

/**
 * Authentication Unique Keys and Salts
 */
define('AUTH_KEY', env('AUTH_KEY'));
define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
define('NONCE_KEY', env('NONCE_KEY'));
define('AUTH_SALT', env('AUTH_SALT'));
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
define('FORCE_SSL_ADMIN', true);
define('WP_AUTO_UPDATE_CORE', false);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
define('DISALLOW_FILE_EDIT', true);
define('EMPTY_TRASH_DAYS', 30);
define('WP_POST_REVISIONS', 7);
define('WP_MAX_MEMORY_LIMIT', '1024M');
define('AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID'));
define('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY'));
define('HPM_SFTP_PASSWORD', env('HPM_SFTP_PASSWORD'));
define('HPM_PBS_TVSS', env('HPM_PBS_TVSS'));
define('HPM_MVAULT_ID', env('HPM_MVAULT_ID'));
define('HPM_MVAULT_SECRET', env('HPM_MVAULT_SECRET'));
define('WP_CACHE_KEY_SALT', env('WP_HOME'));

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
