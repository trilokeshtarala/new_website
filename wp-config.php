<?php
define('WP_CACHE', false); // Added by WP Rocket
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '1st_website' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '*>.]n}oU|S8&~>tb$A:V44_at2XFbHEE87$dTVo>V *B[~@$F-[I5O}i@r*6EfC]' );
define( 'SECURE_AUTH_KEY',  'C.AnpH|/A^$n%x }6r*b15~=|MSe;z/<S|9;7k!&bbJb}.4iFY#cU4ZbYM~n-dYj' );
define( 'LOGGED_IN_KEY',    'F0*dk/c|Af$A>{HEUmTy+k@$ML(Ht`Tp?6CdY|prC!b!^rZ9R_DAP4iYCAXs;,$M' );
define( 'NONCE_KEY',        '|io0Ul:=H@3mgs_I$P&yoy}?)l[%$e.6nrPy5[@hmn}~-uA4+#:9;Z/XZSN|u0$i' );
define( 'AUTH_SALT',        'rfE4Y-_{MyK@{;? d6$d4o:66/FDn6+QL+d0/ijB~*-+r2U$y&{c).(m.6y@m%6{' );
define( 'SECURE_AUTH_SALT', 'mP(h9WB0^Vw)${m$JDa9b?[J(msK6/n.=WjPNYM|<pHP&6cc%~#>FebR?/XO),T.' );
define( 'LOGGED_IN_SALT',   'vPG~RD5S5&-@$!u(^[ 40;c8utp#iX%S?1@t-im9^&?m6t6 pEpfTbB&ci,W(^+K' );
define( 'NONCE_SALT',       '~GCG-pQL3XR#Wbc]r*uQm;}.d&4H(~S%~ccbJ$#K)JAIU5*W;3KvS,IaQeu!QyL+' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
