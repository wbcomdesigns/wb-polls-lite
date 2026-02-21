<?php
/**
 * WB Polls Lite — Community Polls for WordPress
 *
 * @link              https://wbcomdesigns.com
 * @since             1.0.0
 * @package           WB_Polls_Lite
 *
 * @wordpress-plugin
 * Plugin Name:       WB Polls Lite — Community Polls
 * Plugin URI:        https://wbcomdesigns.com/downloads/buddypress-polls/
 * Description:       Create polls on your WordPress site or in BuddyPress activity feeds and groups. Frontend dashboard, poll creation, guest voting, scheduling, multi-select, AJAX results, shortcodes. Upgrade to Pro for image/video/audio polls, surveys, CSV export, and more.
 * Version:           1.0.0
 * Author:            Wbcom Designs
 * Author URI:        https://wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Tested up to:      6.7
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Text Domain:       buddypress-polls
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Prevent activation if Pro version is active.
if ( defined( 'BPOLLS_PLUGIN_VERSION' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>';
		echo '<strong>WB Polls Lite</strong> cannot run while <strong>WB Polls Pro</strong> is active. Please deactivate one of them.';
		echo '</p></div>';
	} );
	return;
}

/**
 * Plugin version.
 */
if ( ! defined( 'BPOLLS_PLUGIN_VERSION' ) ) {
	define( 'BPOLLS_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'BPOLLS_PLUGIN_FILE' ) ) {
	define( 'BPOLLS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'BPOLLS_PLUGIN_URL' ) ) {
	define( 'BPOLLS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'BPOLLS_PLUGIN_PATH' ) ) {
	define( 'BPOLLS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BPOLLS_PLUGIN_BASENAME' ) ) {
	define( 'BPOLLS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

defined( 'BPOLLS_COOKIE_EXPIRATION' ) or define( 'BPOLLS_COOKIE_EXPIRATION', time() + 1209600 );
defined( 'BPOLLS_COOKIE_NAME' ) or define( 'BPOLLS_COOKIE_NAME', 'wbpoll-cookie' );
defined( 'BPOLLS_RAND_MIN' ) or define( 'BPOLLS_RAND_MIN', 0 );
defined( 'BPOLLS_RAND_MAX' ) or define( 'BPOLLS_RAND_MAX', 999999 );
defined( 'BPOLLS_COOKIE_EXPIRATION_14DAYS' ) or define( 'BPOLLS_COOKIE_EXPIRATION_14DAYS', time() + 1209600 );
defined( 'BPOLLS_COOKIE_EXPIRATION_7DAYS' ) or define( 'BPOLLS_COOKIE_EXPIRATION_7DAYS', time() + 604800 );

/**
 * Activation: create vote table and default settings.
 * Uses the SAME table schema as Pro — seamless upgrade.
 */
function activate_buddypress_polls() {

	WBPollHelper::install_table();

	if ( false === get_option( 'bpolls_settings' ) || empty( get_option( 'bpolls_settings' ) ) ) {
		global $wp_roles;
		$bpolls_settings['limit_poll_activity']    = 'no';
		$bpolls_settings['options_limit']          = '5';
		$bpolls_settings['poll_options_result']    = 'yes';
		$bpolls_settings['poll_list_voters']       = 'yes';
		$bpolls_settings['poll_limit_voters']      = '3';
		$bpolls_settings['polls_background_color'] = '#4caf50';
		$bpolls_settings['multiselect']            = 'no';
		$bpolls_settings['user_additional_option'] = 'no';
		$bpolls_settings['hide_results']           = 'no';
		$bpolls_settings['close_date']             = 'no';
		$bpolls_settings['enable_image']           = 'no';
		$bpolls_settings['enable_video']           = 'no';
		$bpolls_settings['enable_audio']           = 'no';
		$bpolls_settings['url_input_only']         = '';
		$bpolls_settings['restrict_media_library'] = '';
		$bpolls_settings['poll_revoting']          = 'no';
		$roles                                     = $wp_roles->get_names();
		foreach ( $roles as $role => $role_name ) {
			$bpolls_settings['poll_user_role'][] = $role;
		}
		update_option( 'bpolls_settings', $bpolls_settings );
	}

	$wbpolls_settings = get_site_option( 'wbpolls_settings' );

	// Create Poll Dashboard page.
	$page_title          = 'Poll Dashboard';
	$poll_dashboard_page = bpolls_get_page_by_title( $page_title );
	$dashboard_page_id   = 0;
	if ( empty( $poll_dashboard_page ) && ( empty( $wbpolls_settings ) || ! isset( $wbpolls_settings['poll_dashboard_page'] ) ) ) {
		$dashboard_page_id = wp_insert_post(
			array(
				'post_title'     => $page_title,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
	}

	// Create Create Poll page.
	$page_title       = 'Create Poll';
	$create_poll_page = bpolls_get_page_by_title( $page_title );
	$create_page_id   = 0;
	if ( empty( $create_poll_page ) && ( empty( $wbpolls_settings ) || ! isset( $wbpolls_settings['create_poll_page'] ) ) ) {
		$create_page_id = wp_insert_post(
			array(
				'post_title'     => $page_title,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
	}

	if ( false === get_site_option( 'wbpolls_settings' ) ) {
		global $wp_roles;
		$settings['poll_dashboard_page']       = $dashboard_page_id;
		$settings['create_poll_page']          = $create_page_id;
		$settings['wbpolls_user_add_extra_op'] = 'no';
		$settings['wbpolls_submit_status']     = 'publish';
		$settings['wppolls_show_result']       = 'yes';
		$settings['wbpolls_logoutuser']        = 'no';
		$settings['wbpolls_background_color']  = '#4caf50';
		$roles                                 = $wp_roles->get_names();
		foreach ( $roles as $role => $role_name ) {
			$settings['wppolls_who_can_vote'][] = $role;
			$settings['wppolls_create_poll'][]  = $role;
		}
		update_site_option( 'wbpolls_settings', $settings );
	}
}

function deactivate_buddypress_polls() {
	// Nothing to clean up.
}

register_activation_hook( __FILE__, 'activate_buddypress_polls' );
register_deactivation_hook( __FILE__, 'deactivate_buddypress_polls' );

/**
 * Core dependencies — same classes as Pro (minus surveys, CLI, sample data).
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wbpoll-helper.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-polls.php';

// Poll REST API (needed for frontend dashboard + create form).
require plugin_dir_path( __FILE__ ) . 'restapi/v1/pollrestapi.php';

// [wbpoll_list] shortcode class.
require plugin_dir_path( __FILE__ ) . 'includes/class-wbpoll-shortcodes.php';

// No EDD license in free.
// No survey REST API in free.
// No WP-CLI in free.
// No surveys in free.
// No sample data generator in free.

/**
 * Initialize the plugin.
 */
function run_buddypress_polls() {
	global $pagenow;

	$admin_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : 'buddypress-polls';
	if ( ! get_option( 'bpolls_update_3_8_2' ) && ( isset( $admin_page ) && 'buddypress-polls' === $admin_page || 'plugins.php' === $pagenow ) ) {
		$bpolls_settings                           = get_option( 'bpolls_settings', array() );
		$bpolls_settings['options_limit']          = '5';
		$bpolls_settings['poll_options_result']    = 'yes';
		$bpolls_settings['poll_list_voters']       = 'yes';
		$bpolls_settings['poll_limit_voters']      = '3';
		$bpolls_settings['polls_background_color'] = '#4caf50';
		update_option( 'bpolls_settings', $bpolls_settings );
		update_option( 'bpolls_update_3_8_2', 1 );
	}

	$plugin = new Buddypress_Polls();
	$plugin->run();
}

function bpolls_plugin_init() {
	if ( bp_polls_check_config() ) {
		run_buddypress_polls();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bpolls_plugin_links' );
	}

	// [wbpoll_list] shortcode — display polls on any page.
	if ( class_exists( 'WBPoll_Shortcodes' ) ) {
		WBPoll_Shortcodes::get_instance();
	}

	// No survey init in free.
}
bpolls_plugin_init();

/**
 * Check BuddyPress configuration.
 */
function bp_polls_check_config() {
	global $bp;
	$check  = array();
	$config = array(
		'blog_status'    => false,
		'network_active' => false,
		'network_status' => true,
	);
	if ( function_exists( 'bp_get_root_blog_id' ) && get_current_blog_id() == bp_get_root_blog_id() ) {
		$config['blog_status'] = true;
	}

	$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

	if ( class_exists( 'BuddyPress' ) && empty( $network_plugins ) ) {
		$check[] = $bp->basename;
	}

	$check[] = 'buddypress/bp-loader.php';

	if ( ! empty( $network_plugins ) ) {
		// Existing logic — BuddyPress could be network-activated.
		if ( isset( $network_plugins[ $bp->basename ] ) ) {
			$config['network_active'] = true;
			$config['network_status'] = bp_is_network_activated();
		}
	}

	// If BuddyPress is active (any way), or standalone mode.
	if ( class_exists( 'BuddyPress' ) || ! class_exists( 'BuddyPress' ) ) {
		return true;
	}
	return false;
}

/**
 * Plugin action links.
 */
function bpolls_plugin_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=buddypress-polls' ) . '">' . __( 'Settings', 'buddypress-polls' ) . '</a>';
	$pro_link      = '<a href="https://wbcomdesigns.com/downloads/buddypress-polls/" style="color:#3db634;font-weight:bold;" target="_blank">' . __( 'Upgrade to Pro', 'buddypress-polls' ) . '</a>';
	array_unshift( $links, $settings_link );
	array_push( $links, $pro_link );
	return $links;
}

/**
 * Get page by title helper.
 */
function bpolls_get_page_by_title( $title ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'title'          => $title,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);
	return ! empty( $pages ) ? $pages[0] : null;
}
