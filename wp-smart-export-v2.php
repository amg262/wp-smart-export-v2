<?php

/*
 * Plugin Name: WP Smart Export v2
 * Version:     1.0
 * Description: A smart and highly customizable data exporter capable of outputting any post type and user data in human readable form.
 * Author:      Sebet
 * Author URI:  
 * Plugin URI:  
 *
 * @fs_premium_only /includes/premium/
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
### Freemius Start
/**
 * Create a helper function for easy SDK access.
 */
function wse_fs()
{
    global  $wpxprt_fs ;
    
    if ( !isset( $wpxprt_fs ) ) {
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
        $wpxprt_fs = fs_dynamic_init( array(
            'id'         => '168',
            'slug'       => 'wp-smart-export',
            'public_key' => 'pk_b78288ecc9fa36f259d643146e9ac',
            'menu'       => array(
            'slug' => 'wp-smart-export',
        ),
            'is_live'    => true,
            'is_premium' => false,
        ) );
    }
    
    return $wpxprt_fs;
}


if ( is_admin() ) {
    wse_fs();
    wse_fs()->add_action( 'after_uninstall', 'wse_fs_uninstall_cleanup' );
}

### Freemius End
define( 'WP_XPRT_VERSION', '1.4.2' );
define( 'WP_XPRT_ID', 'wp_xprt' );
define( 'WP_XPRT_URI', plugins_url( '', __FILE__ ) );
define( 'WP_XPRT_SCHEDULE_POST_TYPE', 'wp_xprt_schedule' );
add_action( 'plugins_loaded', '_wp_xprt_init', 9999 );
add_action( 'wp_xprt_scheduler', 'wp_xprt_scheduler_manager' );
register_deactivation_hook( __FILE__, 'wp_xprt_deactivate' );
/**
 * Initialize the plugin.
 */
function _wp_xprt_init()
{
    global  $wp_xprt_options ;
    require_once dirname( __FILE__ ) . '/includes/framework/load.php';
    require_once dirname( __FILE__ ) . '/includes/lib/class-table.php';
    require_once dirname( __FILE__ ) . '/includes/lib/utils.php';
    require_once dirname( __FILE__ ) . '/includes/lib/helper.php';
    require_once dirname( __FILE__ ) . '/includes/lib/core.php';
    require_once dirname( __FILE__ ) . '/includes/lib/options.php';
    
    if ( is_admin() ) {
        require_once dirname( __FILE__ ) . '/includes/admin/class-admin.php';
        require_once dirname( __FILE__ ) . '/includes/admin/class-guided-tour.php';
        new WP_Smart_Export_Admin_Page( __FILE__, $wp_xprt_options );
    }
    
    do_action( 'wp_smart_export_loaded' );
}

/*
 * On deactivation, remove plugin related stuff.
 */
function wp_xprt_deactivate()
{
    wp_clear_scheduled_hook( 'wp_xprt_scheduler' );
}