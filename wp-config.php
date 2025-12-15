<?php /* BEGIN KINSTA DEVELOPMENT ENVIRONMENT - DO NOT MODIFY THIS CODE BLOCK */ ?>
<?php if ( !defined('KINSTA_DEV_ENV') ) { define('KINSTA_DEV_ENV', true); /* Kinsta development - don't remove this line */ } ?>
<?php if ( !defined('JETPACK_STAGING_MODE') ) { define('JETPACK_STAGING_MODE', true); /* Kinsta development - don't remove this line */ } ?>
<?php /* END KINSTA DEVELOPMENT ENVIRONMENT - DO NOT MODIFY THIS CODE BLOCK */ ?>
<?php
# Database Configuration
define( 'DB_NAME', 'The_Compliance_Store___Secure_1');
define( 'DB_USER', 'root');
define( 'DB_PASSWORD', 'uXzKg5g2V2CHgwrd' );
define( 'DB_HOST', 'devkinsta_db');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';
if (! defined('WP_DEBUG') ) { define( 'WP_DEBUG', false ); } // line added by the MyKinsta
# Security Salts, Keys, Etc
define('AUTH_KEY',         '`/E~YTBk83}^L:VBD3a%W4M0b..vC&c;-(<3m&xNGI&={YE(uvcIvGDF)<qdgEZC');
define('SECURE_AUTH_KEY',  '+;7Zul;_m7dlYV3t.3#5<C$?./;H3c]Wd<uk4Ba8,Rv9F/|:#>-6`}ST;5*fW/iX');
define('LOGGED_IN_KEY',    'IN9nW8<3=J>g4%au0rW]YeO#<*14SDwU*#]hMVu1.&oDK9=+l;-C[lk<cK5w>=%)');
define('NONCE_KEY',        ';YqAFy,;Y|1(; 7O}eT()5ms0&l>4c@~LuiylsLviTqe%8sJXctVpq<+&{p]-KLz');
define('AUTH_SALT',        '+<YJOyT@-#>EVF~Yx0x~I&HVuYAQo>008&SIge8mG^ s A&ngnQim1$8aPOMPX/;');
define('SECURE_AUTH_SALT', '!HuRgrVZB@n>r~l[51Vo[D1a]#jEk5ZBPS.1`yPfV@x@_[W_&Qfm%V+7_`4hYXdI');
define('LOGGED_IN_SALT',   'RH%| aglnt~Igp7w%lgGQb?o1NKUe;i!$Q!y,g#+%dt&NSdj(03`?[f/v,di?K3B');
define('NONCE_SALT',       'CHv([^o/X<pE)/993BLpiF=yan,83.|$*70|.r7s3BL;Bl_HVYAlHtH#ryKeY9,');
# Localized Language Stuff
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', 0755 );
define( 'FS_CHMOD_FILE', 0644 );
define( 'DISALLOW_FILE_MODS', FALSE );
define( 'DISALLOW_FILE_EDIT', FALSE );
define( 'DISABLE_WP_CRON', false );
define( 'FORCE_SSL_LOGIN', true );
define( 'WP_POST_REVISIONS', FALSE );
define( 'WP_TURN_OFF_ADMIN_BAR', false );
// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_DISPLAY', true );

define( 'WP_CACHE', TRUE );
define('WPLANG','');

define('WP_MEMORY_LIMIT', '1024M');
define( 'WP_MAX_MEMORY_LIMIT', '1024M' );

# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', __DIR__ . '/');
require_once(ABSPATH . 'wp-settings.php');
