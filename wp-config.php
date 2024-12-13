<?php
define( 'WP_CACHE', false ); // By Speed Optimizer by SiteGround

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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbsdyruuxuw1ic' );

/** Database username */
define( 'DB_USER', 'uefebiokpezs9' );

/** Database password */
define( 'DB_PASSWORD', '1#fD6rfC1@%r' );

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
define( 'AUTH_KEY',         'cd>MR~A9Ay^YpSmM(L E(^iFRl<980fEB>y >v+Qx;n~}0t6M^,JS_$/L-J-iWF%' );
define( 'SECURE_AUTH_KEY',  'LT<Ql=9*a$N?[m,G[F=ObKNE_[`|y3#Tnr~QVd]NCdbGs$xcXzy[I: QY=v,<7BJ' );
define( 'LOGGED_IN_KEY',    'Bm2/Gi<v9jIH7Cq%td,bw{v4*Rk{_e;45{:y,Y! `EvjZ[^1o5VH!{=B.7/9TCeK' );
define( 'NONCE_KEY',        '`N6scjDqsP;w+7c-o.xqX4.EydlaW`[]N[FzD`F(zhjE]n#9LX_Sr cM,yZwlLPE' );
define( 'AUTH_SALT',        'A@Yt!@?v%R,ozi)1dS%ss9+r]x`p`Tfh<MvGH}y,S9ecvs16yQWmn8VDkZ|p@vAH' );
define( 'SECURE_AUTH_SALT', 'ZgbHjV<)Y}RW:<&0>XE^b)l]|,h;/HDk!~WgxJN3t+<%}a1tnUyKrgIHi)`$u:<G' );
define( 'LOGGED_IN_SALT',   'EW,$YDi!Gn0fDKQvL*e0lQp`{MmCj(go|*cx7[Oq&&H4%Tc8pmA8KV$CmPH`S<]=' );
define( 'NONCE_SALT',       'cWbqf:E%<bXf66UivraAU`zB%=_ EvT/gPw)hJ$8}te(?SZW?j]<HI|7^r#(v[fU' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ng_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
@include_once('/var/lib/sec/wp-settings-pre.php'); // Added by SiteGround WordPress management system
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
