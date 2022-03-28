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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'test2' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         '&w#DQ1%d.^fsrSt`KLiu4=T}hsfgso5iX/.W>$9jm<!/5Thj<l@`wD%cD+wXk]:_' );
define( 'SECURE_AUTH_KEY',  'h>&DLb8~.?4H^LIf [i=.,#6-@EC,C;JSp[bUmp~>.X2EDBL .Wr#SBGP*9RLs }' );
define( 'LOGGED_IN_KEY',    '.>-![g@gS/o=e5ObxFT9}gyHfRC2Y<+AYnF|p+^E5iT*.M!~HYJ*r4I_V#88Q-Ge' );
define( 'NONCE_KEY',        'RW3JjpN uyU?/MQ}Z4kT[UhCt!xL3E`9(lLvV6Qsb2(?Pi-$)DyPkfy2JB^Br;-5' );
define( 'AUTH_SALT',        'zYdVeLdQ`SX 5>QsV?2+ZY@kHZr{=e,)+/ObpsC?D-v:^,Zmux4>fT/p9$@q-DoW' );
define( 'SECURE_AUTH_SALT', '@hddHil)3_^ l<9^R%-<<g^G[Unyb9cY.O!T(<P;nK;&LZx)~?/?mie%BB?ti*{Y' );
define( 'LOGGED_IN_SALT',   '7-{;beUle8fCnJ)3j{JLcXDmSv8D?:k5d[T%j+1&L`U/L!B91!_{+lhewLyVAHBw' );
define( 'NONCE_SALT',       '&i()`oA;7:BI|ma:aW=/b/t+,:z?Y,BO:94|=1q}@Uku[^*4QIWE]sBcx:$(jc Q' );

/**#@-*/

/**
 * WordPress database table prefix.
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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
