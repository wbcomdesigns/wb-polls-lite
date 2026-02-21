<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/includes
 * @author     wbcomdesigns <admin@wbcomdesigns.com>
 */
if ( ! class_exists( 'Buddypress_Polls' ) ) {

	/** Buddypress_Polls */
	class Buddypress_Polls {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Buddypress_Polls_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			if ( defined( 'BPOLLS_PLUGIN_VERSION' ) ) {
				$this->version = BPOLLS_PLUGIN_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'buddypress-polls';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Buddypress_Polls_Loader. Orchestrates the hooks of the plugin.
		 * - Buddypress_Polls_i18n. Defines internationalization functionality.
		 * - Buddypress_Polls_Admin. Defines all hooks for the admin area.
		 * - Buddypress_Polls_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-polls-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-polls-i18n.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-buddypress-polls-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-buddypress-polls-public.php';

			/**
			 * The class responsible for initiating bp poll activity graph widget.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/inc/bp-poll-activity-graph.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/inc/class-bp-poll-activity-graph.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/inc/class-wb-poll-report-graph.php';

			/* Enqueue wbcom plugin folder file. */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wbcom/wbcom-admin-settings.php';

			/* Enqueue wbcom plugin folder file. */

			$this->loader = new Buddypress_Polls_Loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Buddypress_Polls_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new Buddypress_Polls_i18n();

			$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			$plugin_admin = new Buddypress_Polls_Admin( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

			$this->loader->add_action( 'admin_menu', $plugin_admin, 'bpolls_add_menu_buddypress_polls' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'bpolls_admin_register_settings' );
			$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'bpolls_add_dashboard_widgets' );
			$this->loader->add_action( 'init', $plugin_admin, 'bpolls_activity_polls_data_export' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'wbcom_hide_all_admin_notices_from_setting_page' );

			// init cookie and custom post types

			$this->loader->add_action( 'wp_before_admin_bar_render', $plugin_admin, 'change_admin_bar_edit_text' );
			$this->loader->add_filter( 'enter_title_here', $plugin_admin, 'change_post_title_placeholder' );
			$this->loader->add_action( 'init', $plugin_admin, 'init_wbpoll_type' );

			$this->loader->add_filter( 'manage_posts_columns', $plugin_admin, 'add_new_poll_columns' );
			$this->loader->add_action( 'manage_posts_custom_column', $plugin_admin, 'manage_poll_columns', 10, 2 );
			$this->loader->add_filter( 'manage_edit-wppolls_sortable_columns', $plugin_admin, 'wbpoll_columnsort' );

			// adding shortcode
			$this->loader->add_filter( 'wbpoll_display_options', $plugin_admin, 'poll_display_methods_text' );/* Will Do It */
			$this->loader->add_filter( 'wbpoll_display_options_backend', $plugin_admin, 'poll_display_methods_text_backend' );
			$this->loader->add_filter( 'wbpoll_display_options_widget_result', $plugin_admin, 'poll_display_methods_text_widget_result' );

			// add meta box and hook save meta box
			$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'bpolls_metaboxes_display' );
			$this->loader->add_action( 'save_post', $plugin_admin, 'bppolls_metabox_save' );
			$this->loader->add_action( 'wp_ajax_wbpoll_get_answer_template', $plugin_admin, 'bpolls_get_answer_template' );
			// TODO: Implement admin email notification method.
		// $this->loader->add_action( 'publish_wbpoll', $plugin_admin, 'bpolls_send_admin_email_on_post_publish' );
			$this->loader->add_action( 'wp_ajax_wbpoll_log_delete', $plugin_admin, 'wbpolls_log_delete' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {

			$plugin_public = new Buddypress_Polls_Public( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'enqueue_embed_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			$this->loader->add_action( 'enqueue_embed_scripts', $plugin_public, 'enqueue_scripts' );

			$this->loader->add_action( 'wp_ajax_bpolls_set_poll_type_true', $plugin_public, 'bpolls_set_poll_type_true' );

			/* adds polls html in whats new area */
			$this->loader->add_action( 'bp_activity_post_form_options', $plugin_public, 'bpolls_polls_update_html', 10 );
			$this->loader->add_action( 'bp_activity_post_form_options', $plugin_public, 'bppolls_polls_options_container', 50 );

			// Conditionally remove and reassign actions if Youzify is active.
			add_action(
				'plugins_loaded',
				function () use ( $plugin_public ) {
					if ( class_exists( 'Youzify' ) ) {
						// Remove existing actions from the 'bp_activity_post_form_options' hook.
						remove_action( 'bp_activity_post_form_options', array( $plugin_public, 'bpolls_polls_update_html' ), 10 );
						remove_action( 'bp_activity_post_form_options', array( $plugin_public, 'bppolls_polls_options_container' ), 50 );

						// Add actions to new hooks as per requirements.
						add_action( 'bp_activity_post_form_tools', array( $plugin_public, 'bpolls_polls_update_html' ), 10 );
						add_action( 'bp_activity_post_form_after_actions', array( $plugin_public, 'bppolls_polls_options_container' ), 50 );
					}
				},
				20
			);

			/* adds new activity type poll */
			$this->loader->add_filter( 'bp_activity_check_activity_types', $plugin_public, 'bpolls_add_polls_type_activity', 10, 1 );

			/* register poll type activity action */
			$this->loader->add_action( 'bp_register_activity_actions', $plugin_public, 'bpolls_register_activity_actions' );

			$this->loader->add_filter( 'bp_get_activity_action_pre_meta', $plugin_public, 'bpolls_activity_action_wall_posts', 9999, 2 );

			/* update poll type activity on post update */
			$this->loader->add_action( 'bp_activity_before_save', $plugin_public, 'bpolls_update_poll_type_activity', 10, 1 );

			/* update poll activity meta */
			$this->loader->add_action( 'bp_activity_posted_update', $plugin_public, 'bpolls_update_poll_activity_meta', 10, 4 );

			/* update group poll activity meta */
			$this->loader->add_action( 'bp_groups_posted_update', $plugin_public, 'bpolls_update_poll_activity_meta', 10, 4 );

			/* ypuzer update activity meta */
			$this->loader->add_action( 'yz_activity_posted_update', $plugin_public, 'bpolls_update_poll_activity_meta', 10, 4 );
			$this->loader->add_action( 'yz_groups_posted_update', $plugin_public, 'bpolls_update_poll_activity_meta', 10, 4 );

			/* Update poll activity content */

			// $this->loader->add_action( 'bp_activity_entry_content', $plugin_public, 'bpolls_update_poll_activity_content', 10, 1 );

			$this->loader->add_filter( 'bp_get_activity_content_body', $plugin_public, 'bpquotes_update_pols_activity_content', 10, 2 );
			/* update widget poll activity content */
			$this->loader->add_action( 'bp_polls_activity_entry_content', $plugin_public, 'bpolls_update_poll_activity_content', 10, 1 );

			/* ajax request to save note */
			$this->loader->add_action( 'wp_ajax_bpolls_save_poll_vote', $plugin_public, 'bpolls_save_poll_vote' );

			/* set poll type activity action in groups */
			if ( defined( 'BP_VERSION' ) ) {
				if ( version_compare( BP_VERSION, '5.0.0', '>=' ) ) {
					$this->loader->add_filter( 'bp_groups_format_activity_action_group_activity_update', $plugin_public, 'bpolls_groups_activity_new_update_action', 10, 1 );
				} else {
					$this->loader->add_filter( 'groups_activity_new_update_action', $plugin_public, 'bpolls_groups_activity_new_update_action', 10, 1 );
				}
			}
			/* set poll activity content in embed */
			$this->loader->add_filter( 'bp_activity_get_embed_excerpt', $plugin_public, 'bpolls_bp_activity_get_embed_excerpt', 10, 2 );
			/* embed poll activity css */
			$this->loader->add_action( 'embed_head', $plugin_public, 'bpolls_activity_embed_add_inline_styles', 20 );

			// update total poll votes.
			// $this->loader->add_action( 'bp_init', $plugin_public, 'bpolls_update_prev_polls_total_votes', 20 );

			$this->loader->add_action( 'wp_ajax_bpolls_save_image', $plugin_public, 'bpolls_save_image' );
			$this->loader->add_action( 'wp_ajax_bpolls_save_video', $plugin_public, 'bpolls_save_video' );
			$this->loader->add_action( 'wp_ajax_bpolls_save_audio', $plugin_public, 'bpolls_save_audio' );

			// Restrict media library to user's own uploads for security.
			$this->loader->add_filter( 'ajax_query_attachments_args', $plugin_public, 'bpolls_restrict_media_library', 10, 1 );

			$this->loader->add_filter( 'bp_activity_user_can_edit', $plugin_public, 'bpolls_activity_can_edit', 10, 2 );

			$this->loader->add_action( 'wp_ajax_bpolls_activity_all_voters', $plugin_public, 'bpolls_activity_all_voters' );
			$this->loader->add_action( 'wp_ajax_nopriv_bpolls_activity_all_voters', $plugin_public, 'bpolls_activity_all_voters' );

			/* Embed polls activity data in rest api */
			$this->loader->add_filter( 'bp_rest_activity_prepare_value', $plugin_public, 'bpolls_activity_data_embed_rest_api', 10, 3 );

			$this->loader->add_shortcode( 'bp_polls', $plugin_public, 'bppolls_rest_api_shortcode' );
			$this->loader->add_action( 'rest_api_init', $plugin_public, 'bppolls_register_user_meta' );

			$this->loader->add_action( 'wp_ajax_bpolls_activity_add_user_option', $plugin_public, 'bpolls_activity_add_user_option' );

			$this->loader->add_action( 'wp_ajax_bpolls_activity_delete_user_option', $plugin_public, 'bpolls_activity_delete_user_option' );

			$this->loader->add_action( 'wp_footer', $plugin_public, 'bpolls_wp_footer', 999 );

			//attach template for plugin pages
			$this->loader->add_filter( 'the_content', $plugin_public, 'wb_poll_add_new_content' );

			if ( ! is_admin() ) {
				//$this->loader->add_filter( 'the_content', $plugin_public, 'wbpoll_the_content' );
				//$this->loader->add_filter( 'the_excerpt', $plugin_public, 'wbpoll_the_excerpt' );
			}

			// ajax for voting
			$this->loader->add_action( 'wp_ajax_wbpoll_user_vote', $plugin_public, 'wbpoll_user_vote' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_user_vote', $plugin_public, 'wbpoll_user_vote' );

			$this->loader->add_action( 'wp_ajax_wbpoll_additional_field', $plugin_public, 'wbpoll_additional_field' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_additional_field', $plugin_public, 'wbpoll_additional_field' );
			$this->loader->add_action( 'wp_ajax_wbpoll_additional_field_image', $plugin_public, 'wbpoll_additional_field_image' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_additional_field_image', $plugin_public, 'wbpoll_additional_field_image' );
			$this->loader->add_action( 'wp_ajax_wbpoll_additional_field_video', $plugin_public, 'wbpoll_additional_field_video' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_additional_field_video', $plugin_public, 'wbpoll_additional_field_video' );
			$this->loader->add_action( 'wp_ajax_wbpoll_additional_field_audio', $plugin_public, 'wbpoll_additional_field_audio' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_additional_field_audio', $plugin_public, 'wbpoll_additional_field_audio' );
			$this->loader->add_action( 'wp_ajax_wbpoll_additional_field_html', $plugin_public, 'wbpoll_additional_field_html' );
			$this->loader->add_action( 'wp_ajax_nopriv_wbpoll_additional_field_html', $plugin_public, 'wbpoll_additional_field_html' );
			$this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );

			$this->loader->add_filter( 'archive_template', $plugin_public, 'wbpoll_archive_template', 10, 3 );
			$this->loader->add_filter( 'single_template', $plugin_public, 'wbpoll_profile_single_template', 99, 3 );

			$this->loader->add_filter( 'wp', $plugin_public, 'prepareWBPoll' );
			//$this->loader->add_action( 'embed_head', $plugin_public, 'bpolls_embed_scripts', 20 );

			if ( ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) || in_array( 'buddypress-search/buddypress-search.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$this->loader->add_filter( 'bp_activity_search_where_conditions', $plugin_public, 'wbpoll_buddyboss_polls_search', 10, 2 );
			}
			
			add_filter( 'bp_editable_types_activity', function( $args){ $args[] = 'activity_poll'; return $args;} );			
			
			$this->loader->add_action( 'bp_get_addition_activity_content', $plugin_public, 'wbpoll_get_poll_activity_content' );
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Buddypress_Polls_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}
}
