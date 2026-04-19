<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost:/Users/erpian/Library/Application Support/Local/run/uwEf5g4S-m/mysql/mysqld.sock' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '(Z!9=j^w>uzP9R*7aLdCUxt&.F/^oQKQk7HXnY9,aWn7[?<._/C5lQW)YLA{`P`4' );
define( 'SECURE_AUTH_KEY',   'noG#,}}|95bQj |8f^BLpkQ9P 0KAUvNer><Iuu&)y9NJ{FeQT%WWHi.IE6s.5Vl' );
define( 'LOGGED_IN_KEY',     'j[;u+uZdgb(R-5^!Mx*jh3yIs4{MGeM.KHQ;?l<|b`McF6=JlhooZc(>{zN;knC(' );
define( 'NONCE_KEY',         '.*uA=6qJ6!EbaZUXcxE5=}ch%uQ0~1TZj{raP1.)=S+8]2WYd`oD OnPYCu,b@@x' );
define( 'AUTH_SALT',         'aS[>J`pX<sQn8xhGt8:^6M]gm=.TB_WCK6N8&nL18fphVh0N;o)$P0ccNx=UM! H' );
define( 'SECURE_AUTH_SALT',  ';2H4Y@sifqpz;t/)=[P>F5Ek+GTvs#AN%KekwWYjM,vt_fnyU<fk^0lu>9>@S2B5' );
define( 'LOGGED_IN_SALT',    '/^LNE0gI!Q}QMcDb/vj$)2[k]keC#d@Uy5Tl2s2Rn{ P-x/X /`>vw{P2:-I)XD5' );
define( 'NONCE_SALT',        '8hcw?7Q7n-|Tt9Mv ;%$4xPD*NnCxpXbF1s#5*V(dE66a]KG>SlNR9B7??fj+ {t' );
define( 'WP_CACHE_KEY_SALT', '`0aiv9Hu,>R$wD)Dj6[F3e4d:3Gd|r&Q_z)MW.*6! 4NtzqGxdK`DXv`,/ DCzd5' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
	define( 'WP_ENVIRONMENT_TYPE', getenv( 'WP_ENVIRONMENT_TYPE' ) ?: 'local' );
}

if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 10 );
}

if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
	define( 'AUTOSAVE_INTERVAL', 180 );
}

if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
	define( 'EMPTY_TRASH_DAYS', 7 );
}

if ( ! defined( 'WP_MEMORY_LIMIT' ) ) {
	define( 'WP_MEMORY_LIMIT', '128M' );
}

if ( ! defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
	define( 'WP_MAX_MEMORY_LIMIT', '256M' );
}

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

if ( ! defined( 'IMAGE_EDIT_OVERWRITE' ) ) {
	define( 'IMAGE_EDIT_OVERWRITE', true );
}



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', 'local' === WP_ENVIRONMENT_TYPE );
}

if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	define( 'WP_DEBUG_LOG', 'local' === WP_ENVIRONMENT_TYPE );
}

if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', 'local' === WP_ENVIRONMENT_TYPE );
}

@ini_set( 'display_errors', WP_DEBUG_DISPLAY ? '1' : '0' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
