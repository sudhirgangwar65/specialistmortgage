<?php
# Database Configuration
define( 'DB_NAME', 'wp_specialistmdev' );
define( 'DB_USER', 'specialistmdev' );
define( 'DB_PASSWORD', 'd4Ap94u02kjPQrK1HYvC' );
define( 'DB_HOST', '127.0.0.1:3306' );
define( 'DB_HOST_SLAVE', '127.0.0.1:3306' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
define( 'WP_POST_REVISIONS', FALSE );
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         'vY{X<=#9km|/Orb]a| 0Si|Q<2_6jMZtkSh+$LR5&uQH&Cd5f~1QY_&`%KoadUdO');
define('SECURE_AUTH_KEY',  '!VJO!f rB@4fla >WUHH|HQ{$Y9- A0D*aC%18w_p*[_3~bTmr$<imSy(|%9S#,W');
define('LOGGED_IN_KEY',    'rjSNpn73Gk7(wD|O$6z~FO0qe%QLQlH[hI3Ql(E*-a<V!EQ.Q;5C:I&:O_[Su>^7');
define('NONCE_KEY',        'Yjh#c^@s1#LekH?n=4sFg{;EA]A<1]m!:fk<aL%Hy0%z FHejyb|}0nxf|ZgHlI]');
define('AUTH_SALT',        'Y;%=A P,!B|{@99(G51{3.-:v=b(WOTU@qH-du)Mng&6jF{|AD80:<j*!apV>^E)');
define('SECURE_AUTH_SALT', '9Qg?B>?v|&|w~/pfwUD}0r!0p[;5Thuv6mjC-ihzFrgZ(oh@&9b$eO3rh4}Bn-q3');
define('LOGGED_IN_SALT',   '%_eGCGN.oW~-p+pYL-_NyJJ20oy:&p]dwb@Etrj4h=TG>{TJv^7|4v7s0[@ 5`iO');
define('NONCE_SALT',       '84HJCbW6YOW]m]?C+8-s%/P]z`ub5n.s(Y6oL:u&7G^F!t%(:-NiiQfbewNr7Nmk');


# Localized Language Stuff
define('WP_DEBUG', false);

define( 'WP_CACHE', TRUE );

define( 'WP_AUTO_UPDATE_CORE', false );

define( 'PWP_NAME', 'specialistmdev' );

define( 'FS_METHOD', 'direct' );

define( 'FS_CHMOD_DIR', 0775 );

define( 'FS_CHMOD_FILE', 0664 );

define( 'WPE_APIKEY', 'f4ac419f3d48d2895fa8922a759fb0a722435c3e' );

define( 'WPE_CLUSTER_ID', '141131' );

define( 'WPE_CLUSTER_TYPE', 'pod' );

define( 'WPE_ISP', true );

define( 'WPE_BPOD', false );

define( 'WPE_RO_FILESYSTEM', false );

define( 'WPE_LARGEFS_BUCKET', 'largefs.wpengine' );

define( 'WPE_SFTP_PORT', 2222 );

define( 'WPE_SFTP_ENDPOINT', '' );

define( 'WPE_LBMASTER_IP', '' );

define( 'WPE_CDN_DISABLE_ALLOWED', true );

define( 'DISALLOW_FILE_MODS', FALSE );

define( 'DISALLOW_FILE_EDIT', FALSE );

define( 'DISABLE_WP_CRON', false );

define( 'WPE_FORCE_SSL_LOGIN', false );

define( 'FORCE_SSL_LOGIN', false );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define( 'WPE_EXTERNAL_URL', false );

define( 'WP_POST_REVISIONS', FALSE );

define( 'WPE_WHITELABEL', 'wpengine' );

define( 'WP_TURN_OFF_ADMIN_BAR', false );

define( 'WPE_BETA_TESTER', false );

umask(0002);

$wpe_cdn_uris=array ( );

$wpe_no_cdn_uris=array ( );

$wpe_content_regexs=array ( );

$wpe_all_domains=array ( 0 => 'specialistmdev.wpenginepowered.com', 1 => 'specialistmortgage.com', 2 => 'www.specialistmortgage.com', 3 => 'specialistmdev.wpengine.com', );

$wpe_varnish_servers=array ( 0 => 'pod-141131', );

$wpe_special_ips=array ( 0 => '34.87.250.152', );

$wpe_netdna_domains=array ( );

$wpe_netdna_domains_secure=array ( );

$wpe_netdna_push_domains=array ( );

$wpe_domain_mappings=array ( );

$memcached_servers=array ( 'default' =>  array ( 0 => 'unix:///tmp/memcached.sock', ), );
define('WPLANG','');

# WP Engine ID


# WP Engine Settings






# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', __DIR__ . '/');
require_once(ABSPATH . 'wp-settings.php');
