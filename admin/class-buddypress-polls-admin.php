<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/admin
 * @author     wbcomdesigns <admin@wbcomdesigns.com>
 */
if ( ! class_exists( 'Buddypress_Polls_Admin' ) ) {

	/** Buddypress_Polls_Admin class */
	class Buddypress_Polls_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $plugin_name       The name of this plugin.
		 * @param      string $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 * @param hook $hook hook.
		 */
		public function enqueue_styles( $hook ) {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Buddypress_Polls_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Buddypress_Polls_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			// Only enqueue on plugin-related admin pages.
			if ( ! $this->is_plugin_admin_page( $hook ) ) {
				return;
			}

			$rtl_css = is_rtl() ? '-rtl' : '';

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_extension = '.css';
			} else {
				$css_extension = '.min.css';
			}

			$admin_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

			// BuddyPress activity admin page - load public CSS for poll display.
			if ( 'bp-activity' === $admin_page ) {
				wp_enqueue_style( $this->plugin_name, BPOLLS_PLUGIN_URL . 'public/css' . $rtl_css . '/buddypress-polls-public' . $css_extension, array(), $this->version, 'all' );
			}

			// Plugin settings page or wbpoll post type.
			if ( 'buddypress-polls' === $admin_page || $this->is_wbpoll_post_type_page( $hook ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-polls-admin' . $css_extension, array(), $this->version, 'all' );
			}

			// Survey admin styles.
			if ( $this->is_wbsurvey_post_type_page( $hook ) ) {
				wp_enqueue_style( 'wbsurvey-admin', plugin_dir_url( __FILE__ ) . 'css/survey-admin.css', array(), $this->version, 'all' );
			}

			// Vendor styles - only on plugin pages.
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/vendor/select2.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'wbpoll-ui-styles', plugin_dir_url( __FILE__ ) . 'css/vendor/jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'wbpoll-ui-styles-timepicker', plugin_dir_url( __FILE__ ) . 'css/vendor/jquery-ui-timepicker-addon.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'wbpoll-admin', plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/wbpoll-admin' . $css_extension, array(), $this->version, 'all' );

			// Logs page specific styles.
			if ( 'wbpoll_logs' === $admin_page ) {
				wp_enqueue_style( 'wbpoll-admin-log', plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/wbpoll-admin-log' . $css_extension, array(), $this->version, 'all' );
			}

		}

		/**
		 * Check if current admin page is a plugin-related page.
		 *
		 * @since 4.5.0
		 * @param string $hook The current admin page hook.
		 * @return bool True if on a plugin admin page.
		 */
		private function is_plugin_admin_page( $hook ) {
			global $pagenow;

			// Check for plugin settings pages.
			$admin_page    = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';
			$plugin_pages  = array( 'buddypress-polls', 'bp-activity', 'wbpoll_logs' );

			if ( in_array( $admin_page, $plugin_pages, true ) ) {
				return true;
			}

			// Check for wbpoll post type pages.
			if ( $this->is_wbpoll_post_type_page( $hook ) ) {
				return true;
			}

			// Check for wbsurvey post type pages.
			if ( $this->is_wbsurvey_post_type_page( $hook ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if current page is a wbpoll post type admin page.
		 *
		 * @since 4.5.0
		 * @param string $hook The current admin page hook.
		 * @return bool True if on a wbpoll post type page.
		 */
		private function is_wbpoll_post_type_page( $hook ) {
			global $pagenow;

			// Check GET parameter for post_type.
			if ( isset( $_GET['post_type'] ) && 'wbpoll' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}

			// Check if editing a wbpoll post.
			if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) && 'wbpoll' === get_post_type() ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if current page is a wbsurvey post type admin page.
		 *
		 * @since 4.6.0
		 * @param string $hook The current admin page hook.
		 * @return bool True if on a wbsurvey post type page.
		 */
		private function is_wbsurvey_post_type_page( $hook ) {
			global $pagenow;

			// Check GET parameter for post_type.
			if ( isset( $_GET['post_type'] ) && 'wbsurvey' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}

			// Check if editing a wbsurvey post.
			if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) && 'wbsurvey' === get_post_type() ) {
				return true;
			}

			return false;
		}

		/**
		 * Hide all notices from the setting page.
		 *
		 * @return void
		 */
		public function wbcom_hide_all_admin_notices_from_setting_page() {
			$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'buddypress-polls' );
			$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

			if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
			}
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 * @param hook $hook hook.
		 */
		public function enqueue_scripts( $hook ) {
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Buddypress_Polls_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Buddypress_Polls_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			// Only enqueue on plugin-related admin pages.
			if ( ! $this->is_plugin_admin_page( $hook ) ) {
				return;
			}

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$js_extension = '.js';
			} else {
				$js_extension = '.min.js';
			}

			global $pagenow;

			$admin_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';
			if ( 'buddypress-polls' === $admin_page || $this->is_wbpoll_post_type_page( $hook ) ) {
				wp_enqueue_script( 'wp-color-picker' );

				wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-polls-admin' . $js_extension, array( 'jquery' ), $this->version, false );

				wp_enqueue_script( $this->plugin_name );
				wp_set_script_translations( $this->plugin_name, 'buddypress-polls' );

			}

			// Register vendor scripts - only load on plugin pages.
			wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/vendor/select2.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( 'wbpoll-jseventManager', plugin_dir_url( __FILE__ ) . 'js/wbpolljsactionandfilter' . $js_extension, array(), $this->version, false );
			wp_register_script( 'wbpoll-ui-time-script', plugin_dir_url( __FILE__ ) . 'js/vendor/jquery-ui-timepicker-addon.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), $this->version, false );
			wp_register_script( 'wbpoll-switcheryjs', plugin_dir_url( __FILE__ ) . 'js/vendor/switchery.min.js', array(), $this->version, false );
			wp_register_script( 'chart-js', plugin_dir_url( __FILE__ ) . 'js/vendor/Chart.min.js', array(), $this->version, false );

			// Enqueue select2 only on plugin pages.
			wp_enqueue_script( 'select2' );

			if ( $this->is_wbpoll_post_type_page( $hook ) ) {

				// admin poll single edit.
				wp_register_script(
					'wbpolladminsingle',
					plugin_dir_url( __FILE__ ) . 'js/wbpoll-admin-single' . $js_extension,
					array(
						'jquery',
						'wp-color-picker',
						'jquery-ui-core',
						'jquery-ui-datepicker',
						'jquery-ui-sortable',
						'select2',
						'wp-tinymce',
						'wbpoll-jseventManager',
						'wbpoll-ui-time-script',
						'wbpoll-switcheryjs',
						'chart-js',
					),
					$this->version,
					false
				);


				wp_enqueue_script( 'wbpolladminsingle' );
				wp_set_script_translations( 'wbpolladminsingle' , 'buddypress-polls' );

				// adding translation and other variables from php to js for single post edit screen.
				$admin_single_arr = array(
					'copy'                  => esc_html__( 'Click to copy', 'buddypress-polls' ),
					'copied'                => esc_html__( 'Copied to clipboard', 'buddypress-polls' ),
					'remove_label'          => esc_html__( 'Remove', 'buddypress-polls' ),
					'move_label'            => esc_html__( 'Move', 'buddypress-polls' ),
					'move_title'            => esc_html__( 'Drag and Drop to reorder answers', 'buddypress-polls' ),
					'answer_label'          => esc_html__( 'Answer', 'buddypress-polls' ),
					'deleteconfirm'         => esc_html__( 'Are you sure you want to delete this answer?', 'buddypress-polls' ),
					'deleteconfirmok'       => esc_html__( 'Yes', 'buddypress-polls' ),
					'deleteconfirmcancel'   => esc_html__( 'No', 'buddypress-polls' ),
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'nonce'                 => wp_create_nonce( 'wbpoll' ),
					'teeny_editor_settings' => array(
						'teeny'         => true,
						'textarea_name' => '',
						'textarea_rows' => 10,
						'media_buttons' => false,
						'editor_class'  => '',
					),
					'please_select'         => esc_html__( 'Please select', 'buddypress-polls' ),
				);

				wp_localize_script( 'wbpolladminsingle', 'wbpolladminsingleObj', $admin_single_arr );

				wp_register_script( 'wbpoll-admin-log', plugin_dir_url( __FILE__ ) . 'js/wbpoll-admin-log' . $js_extension , array(), $this->version, false );

				wp_enqueue_script( 'wbpoll-admin-log' );

				wp_set_script_translations( 'wbpoll-admin-log' , 'buddypress-polls' );


			}

			// Survey builder scripts.
			if ( $this->is_wbsurvey_post_type_page( $hook ) ) {
				wp_enqueue_script( 'jquery-ui-sortable' );

				wp_register_script(
					'wbsurvey-builder',
					plugin_dir_url( __FILE__ ) . 'js/survey-builder.js',
					array( 'jquery', 'jquery-ui-sortable' ),
					$this->version,
					true
				);

				wp_enqueue_script( 'wbsurvey-builder' );

				// Localize survey builder strings.
				/* translators: %s: poll title */
				$confirm_remove_text    = esc_html__( 'Remove "%s" from the survey?', 'buddypress-polls' );
				$survey_builder_strings = array(
					'editUrl' => admin_url( 'post.php?action=edit&post=' ),
					'strings' => array(
						'selectPoll'    => esc_html__( 'Please select a poll.', 'buddypress-polls' ),
						'pollExists'    => esc_html__( 'This poll is already in the survey.', 'buddypress-polls' ),
						'confirmRemove' => $confirm_remove_text,
					),
				);

				wp_localize_script( 'wbsurvey-builder', 'wbsurveyBuilder', $survey_builder_strings );
			}

		}

		/**
		 * Register admin menu for plugin.
		 *
		 * @since    1.0.0
		 */
		public function bpolls_add_menu_buddypress_polls() {

			if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {

				add_menu_page( esc_html__( 'WB Plugins', 'buddypress-polls' ), esc_html__( 'WB Plugins', 'buddypress-polls' ), 'manage_options', 'wbcomplugins', array( $this, 'bpolls_buddypress_polls_settings_page' ), 'dashicons-lightbulb', 59 );
				add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'buddypress-polls' ), esc_html__( 'General', 'buddypress-polls' ), 'manage_options', 'wbcomplugins' );
			}
			add_submenu_page( 'wbcomplugins', esc_html__( 'WB Polls Settings', 'buddypress-polls' ), esc_html__( 'WB Polls', 'buddypress-polls' ), 'manage_options', 'buddypress-polls', array( $this, 'bpolls_buddypress_polls_settings_page' ) );

			add_submenu_page(
				'edit.php?post_type=wbpoll', // Parent menu slug (edit.php?post_type=custom_post_type).
				esc_html__( 'Logs', 'buddypress-polls' ),  // Page title.
				esc_html__( 'Logs', 'buddypress-polls' ),   // Menu title.
				'manage_options',   // Capability required to access the submenu.
				'wbpoll_logs',
				array( $this, 'wbpoll_logs_page_callback' ),
			);

		}

		/**
		 * Callable function for admin menu setting page.
		 *
		 * @since    1.0.0
		 */
		public function bpolls_buddypress_polls_settings_page() {
			$current = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : 'welcome';
			?>

			<div class="wrap">
				<div class="wbcom-bb-plugins-offer-wrapper">
					<div id="wb_admin_logo">
					</div>
				</div>
				<div class="wbcom-wrap buddyPress-polls-header">					
				<div class="blpro-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'WB Polls', 'buddypress-polls' ); ?>
							<?php /* translators: %s: */ ?>
							<span><?php printf( esc_html__( 'Version %s', 'buddypress-polls' ), esc_attr( BPOLLS_PLUGIN_VERSION ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
				</div>
				<div class="wbcom-admin-settings-page">
			<?php
			if ( class_exists( 'BuddyPress' ) ) {
				$bpolls_tabs = array(
					'welcome'        => esc_html__( 'Welcome', 'buddypress-polls' ),
					'general'        => esc_html__( 'Community', 'buddypress-polls' ),
					'wbpoll_setting' => esc_html__( 'Polls Settings', 'buddypress-polls' ),
					'support'        => esc_html__( 'FAQ', 'buddypress-polls' ),
				);
			} else {
				$bpolls_tabs = array(
					'welcome'        => esc_html__( 'Welcome', 'buddypress-polls' ),
					'wbpoll_setting' => esc_html__( 'Polls Settings', 'buddypress-polls' ),
					'support'        => esc_html__( 'FAQ', 'buddypress-polls' ),
				);
			}

			$tab_html = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html__( 'Menu', 'buddypress-polls' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
			foreach ( $bpolls_tabs as $bpolls_tab => $bpolls_name ) {
				$class     = ( $bpolls_tab == $current ) ? 'nav-tab-active' : '';
				$tab_html .= '<li class="' . $bpolls_tab . '"><a class="nav-tab ' . $class . '" href="admin.php?page=buddypress-polls&tab=' . $bpolls_tab . '">' . $bpolls_name . '</a></li>';
			}
			$tab_html .= '</div></ul></div>';
			echo wp_kses_post( $tab_html );
			include 'inc/bpolls-tabs-options.php';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Function to register admin settings.
		 *
		 * @since    1.0.0
		 */
		public function bpolls_admin_register_settings() {
			// Capability check - only admins can modify settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Activity Polls Settings (bpolls_settings).
			if ( isset( $_POST['bpolls_settings'] ) ) {
				if ( isset( $_POST['bp_polls_general_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bp_polls_general_settings_nonce'] ) ), 'bp_polls_general_settings_nonce_action' ) ) {
					$sanitized = $this->sanitize_bpolls_settings( wp_unslash( $_POST['bpolls_settings'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					update_site_option( 'bpolls_settings', $sanitized );
				}
				if ( isset( $_POST['_wp_http_referer'] ) ) {
					wp_safe_redirect( esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) );
					exit();
				}
			}

			// Standalone Polls Settings (wbpolls_settings).
			if ( isset( $_POST['wbpolls_settings'] ) ) {
				if ( isset( $_POST['bp_polls_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bp_polls_settings_nonce'] ) ), 'bp_polls_settings_nonce_action' ) ) {
					$sanitized = $this->sanitize_wbpolls_settings( wp_unslash( $_POST['wbpolls_settings'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					update_site_option( 'wbpolls_settings', $sanitized );
				}
				if ( isset( $_POST['_wp_http_referer'] ) ) {
					wp_safe_redirect( esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) );
					exit();
				}
			}

		}

		/**
		 * Sanitize Activity Polls settings (bpolls_settings).
		 *
		 * @since 4.4.2
		 * @param array $input Raw input array.
		 * @return array Sanitized settings.
		 */
		private function sanitize_bpolls_settings( $input ) {
			$sanitized = array();

			// Boolean checkbox fields.
			$checkbox_fields = array(
				'multiselect',
				'user_additional_option',
				'hide_results',
				'close_date',
				'enable_image',
				'enable_video',
				'enable_audio',
				'url_input_only',
				'restrict_media_library',
				'poll_options_result',
				'poll_revoting',
				'poll_list_voters',
				'enable_thank_you_message',
			);

			foreach ( $checkbox_fields as $field ) {
				$sanitized[ $field ] = ( isset( $input[ $field ] ) && 'yes' === $input[ $field ] ) ? 'yes' : '';
			}

			// Numeric fields.
			$sanitized['options_limit']          = isset( $input['options_limit'] ) ? absint( $input['options_limit'] ) : 5;
			$sanitized['poll_limit_voters']      = isset( $input['poll_limit_voters'] ) ? absint( $input['poll_limit_voters'] ) : 3;
			$sanitized['activity_option_limit']  = isset( $input['activity_option_limit'] ) ? absint( $input['activity_option_limit'] ) : '';
			$sanitized['thank_you_message_limit'] = isset( $input['thank_you_message_limit'] ) ? absint( $input['thank_you_message_limit'] ) : '';

			// Radio field with allowed values.
			$allowed_limit_values = array( 'no', 'user_role', 'member_type' );
			$sanitized['limit_poll_activity'] = ( isset( $input['limit_poll_activity'] ) && in_array( $input['limit_poll_activity'], $allowed_limit_values, true ) ) ? $input['limit_poll_activity'] : 'no';

			// Array fields (roles/member types).
			$sanitized['poll_user_role']  = isset( $input['poll_user_role'] ) && is_array( $input['poll_user_role'] ) ? array_map( 'sanitize_text_field', $input['poll_user_role'] ) : array();
			$sanitized['poll_member_type'] = isset( $input['poll_member_type'] ) && is_array( $input['poll_member_type'] ) ? array_map( 'sanitize_text_field', $input['poll_member_type'] ) : array();

			// Color field.
			$sanitized['polls_background_color'] = isset( $input['polls_background_color'] ) ? sanitize_hex_color( $input['polls_background_color'] ) : '#4caf50';
			if ( empty( $sanitized['polls_background_color'] ) ) {
				$sanitized['polls_background_color'] = '#4caf50'; // Default fallback if invalid color.
			}

			return $sanitized;
		}

		/**
		 * Sanitize Standalone Polls settings (wbpolls_settings).
		 *
		 * @since 4.4.2
		 * @param array $input Raw input array.
		 * @return array Sanitized settings.
		 */
		private function sanitize_wbpolls_settings( $input ) {
			$sanitized = array();

			// Page ID fields.
			$sanitized['create_poll_page']    = isset( $input['create_poll_page'] ) ? absint( $input['create_poll_page'] ) : '';
			$sanitized['poll_dashboard_page'] = isset( $input['poll_dashboard_page'] ) ? absint( $input['poll_dashboard_page'] ) : '';

			// Role array fields.
			$sanitized['wppolls_create_poll']  = isset( $input['wppolls_create_poll'] ) && is_array( $input['wppolls_create_poll'] ) ? array_map( 'sanitize_text_field', $input['wppolls_create_poll'] ) : array();
			$sanitized['wppolls_who_can_vote'] = isset( $input['wppolls_who_can_vote'] ) && is_array( $input['wppolls_who_can_vote'] ) ? array_map( 'sanitize_text_field', $input['wppolls_who_can_vote'] ) : array();

			// Boolean checkbox fields.
			$checkbox_fields = array(
				'wbpolls_user_add_extra_op',
				'wppolls_show_result',
				'wppolls_show_comment',
				'enable_image_poll',
				'enable_video_poll',
				'enable_audio_poll',
				'enable_html_poll',
				'disable_html_editor',
				'use_simple_textarea',
			);

			foreach ( $checkbox_fields as $field ) {
				$sanitized[ $field ] = ( isset( $input[ $field ] ) && 'yes' === $input[ $field ] ) ? 'yes' : '';
			}

			// Select field with allowed values.
			$allowed_status_values = array( 'pending', 'publish' );
			$sanitized['wbpolls_submit_status'] = ( isset( $input['wbpolls_submit_status'] ) && in_array( $input['wbpolls_submit_status'], $allowed_status_values, true ) ) ? $input['wbpolls_submit_status'] : 'publish';

			// Character limit fields.
			$sanitized['poll_title_limit']       = isset( $input['poll_title_limit'] ) ? absint( $input['poll_title_limit'] ) : '';
			$sanitized['poll_description_limit'] = isset( $input['poll_description_limit'] ) ? absint( $input['poll_description_limit'] ) : '';
			$sanitized['poll_answer_limit']      = isset( $input['poll_answer_limit'] ) ? absint( $input['poll_answer_limit'] ) : '';

			// Color field.
			$sanitized['wbpolls_background_color'] = isset( $input['wbpolls_background_color'] ) ? sanitize_hex_color( $input['wbpolls_background_color'] ) : '#4caf50';
			if ( empty( $sanitized['wbpolls_background_color'] ) ) {
				$sanitized['wbpolls_background_color'] = '#4caf50';
			}

			return $sanitized;
		}

		/**
		 * Bpolls_add_dashboard_widgets
		 *
		 * @since    1.0.0
		 */
		public function bpolls_add_dashboard_widgets() {

			if ( class_exists( 'BuddyPress' ) && bp_is_active( 'activity' ) ) {
				wp_add_dashboard_widget(
					'bpolls_stats_dashboard_widget', // Widget slug.
					__( 'Site Polls Data', 'buddypress-polls' ), // Title.
					array( $this, 'bpolls_stats_dashboard_widget_function' ) // Display function.
				);

				wp_add_dashboard_widget(
					'bpolls_graph_dashboard_widget', // Widget slug.
					__( 'Poll Graph', 'buddypress-polls' ), // Title.
					array( $this, 'bpolls_graph_dashboard_widget_function' ) // Display function.
				);

			}
		}

		/**
		 * Function to output the contents of polls stats widgets.
		 */
		public function bpolls_stats_dashboard_widget_function() {
			$args          = array(
				'show_hidden' => true,
				'action'      => 'activity_poll',
				'count_total' => true,
			);
			$polls_created = 0;
			if ( function_exists( 'bp_has_activities' ) && bp_has_activities( $args ) ) {
				global $activities_template;
				$polls_created = $activities_template->total_activity_count;
			}
			global $wpdb;

			$sql_query = $wpdb->prepare( "SELECT * from {$wpdb->prefix}bp_activity_meta where meta_key = %s group by activity_id having meta_value=max(meta_value) order by meta_value desc", 'bpolls_total_votes' );

			$results = $wpdb->get_row( $sql_query ); //phpcs:ignore

			$max_votes_act_link = '#';
			$title              = '';
			if ( isset( $results->activity_id ) ) {
				$max_votes          = $results->meta_value;
				$max_votes_act_link = bp_activity_get_permalink( $results->activity_id );
				$activity_obj       = bp_activity_get(
					array(
						'in'     => $results->activity_id,
						'max'    => 1,
						'action' => 'activity_poll',
						'type'   => 'activity_poll',
					)
				);
				$title              = $activity_content = $activity_obj['activities'][0]->content;
				$length             = strlen( $activity_content );
				if ( $length > 60 ) {
					$title = bp_create_excerpt(
						$activity_content,
						'50',
						array(
							'ending'            => '...',
							'exact'             => false,
							'html'              => true,
							'filter_shortcodes' => '',
							'strip_tags'        => false,
							'remove_links'      => false,
						)
					);
				}
			}

			$recent_poll = $wpdb->get_row( "SELECT * from {$wpdb->prefix}bp_activity where type = 'activity_poll' group by id having date_recorded=max(date_recorded) order by date_recorded desc" );

			$recent_poll_link = '#';
			if ( isset( $recent_poll->id ) ) {
				$recent_poll_link = bp_activity_get_permalink( $recent_poll->id );
				$recent_title     = $r_activity_content = $recent_poll->content;
				$length           = strlen( $r_activity_content );
				if ( $length > 60 ) {
					$recent_title = bp_create_excerpt(
						$r_activity_content,
						'50',
						array(
							'ending'            => '...',
							'exact'             => false,
							'html'              => true,
							'filter_shortcodes' => '',
							'strip_tags'        => false,
							'remove_links'      => false,
						)
					);
				}
			}
			if ( $polls_created ) {
				?>
				<div class="bpolls_stats_wrapper">
					<table class="form-table">
						<tr>
							<td><?php esc_html_e( 'Polls Created', 'buddypress-polls' ); ?></td>
							<td><?php echo esc_html( $polls_created ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Highest Voted Poll', 'buddypress-polls' ); ?></td>
							<td><a href="<?php echo esc_url( $max_votes_act_link ); ?>"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Recent Poll', 'buddypress-polls' ); ?></td>
							<td><a href="<?php echo esc_url( $recent_poll_link ); ?>"><?php echo esc_html( $recent_title ); ?><a></td>
						</tr>
					</table>
				</div>
				<?php
			} else {
				?>
				<div class="bpolls-empty-message"><?php esc_html_e( 'No polls have been created.', 'buddypress-polls' ); ?></div>
				<?php
			}
		}

		/**
		 * Bpolls_graph_dashboard_widget_function
		 */
		public function bpolls_graph_dashboard_widget_function() {

			global $wpdb;

			if ( class_exists( 'BuddyPress' ) && bp_is_active( 'activity' ) ) {

				$sql_query = $wpdb->prepare( "SELECT * from {$wpdb->prefix}bp_activity where type = %s group by id having date_recorded=max(date_recorded) order by date_recorded desc", 'activity_poll' ); 
				$results   = $wpdb->get_row( $sql_query ); //phpcs:ignore

				$poll_wdgt       = new BP_Poll_Activity_Graph_Widget();
				$poll_wdgt_stngs = $poll_wdgt->get_settings();
				$instance        = array(
					'title'            => __( 'Poll Graph', 'buddypress-polls' ),
					'max_activity'     => 50,
					'activity_default' => ( isset( $results->id ) ) ? $results->id : '',
				);
				the_widget( 'BP_Poll_Activity_Graph_Widget', $instance );

			}
		}

		/**
		 * Bpolls_activity_polls_data_export
		 */
		public function bpolls_activity_polls_data_export() {

			$contributor = get_role( 'contributor' );
			$subscriber  = get_role( 'subscriber' );

			if ( ! empty( $contributor ) ) {
				$contributor->add_cap( 'upload_files' );
			}

			if ( ! empty( $subscriber ) ) {
				$subscriber->add_cap( 'upload_files' );
			}
			
			if ( class_exists( 'BuddyPress' ) && function_exists( 'bp_is_active' ) && bp_is_active( 'activity' ) && isset( $_REQUEST['export_csv'] ) && 1 == $_REQUEST['export_csv'] && isset( $_REQUEST['buddypress_poll'] ) && 1 == $_REQUEST['buddypress_poll'] && isset( $_REQUEST['activity_id'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bp_polls_export_csv_nonce' ) ) { 
				
				$activity_id   = isset( $_REQUEST['activity_id'] ) ? absint( wp_unslash( $_REQUEST['activity_id'] ) ) : 0;

				// Get poll meta - try BuddyPress API first, fall back to direct query if needed.
				$activity_meta = bp_activity_get_meta( $activity_id, 'bpolls_meta', true );

				// Handle case where meta is returned as serialized string.
				if ( is_string( $activity_meta ) && ! empty( $activity_meta ) ) {
					$activity_meta = maybe_unserialize( $activity_meta );
				}

				// If still not an array, try direct database query as fallback.
				if ( ! is_array( $activity_meta ) ) {
					global $wpdb;
					$bp_prefix     = bp_core_get_table_prefix();
					$raw_meta      = $wpdb->get_var( $wpdb->prepare(
						"SELECT meta_value FROM {$bp_prefix}bp_activity_meta WHERE activity_id = %d AND meta_key = %s",
						$activity_id,
						'bpolls_meta'
					) );
					$activity_meta = maybe_unserialize( $raw_meta );
				}

				// Ensure activity_meta is an array with required keys.
				if ( ! is_array( $activity_meta ) || empty( $activity_meta['poll_option'] ) || ! is_array( $activity_meta['poll_option'] ) ) {
					wp_die( esc_html__( 'Invalid poll data. Cannot export CSV.', 'buddypress-polls' ) );
				}

				// Ensure poll_users exists and is not empty.
				if ( empty( $activity_meta['poll_users'] ) ) {
					wp_die( esc_html__( 'No votes recorded yet. Cannot export CSV.', 'buddypress-polls' ) );
				}

				// Get poll name from activity content for filename.
				global $wpdb;
				$bp_prefix = bp_core_get_table_prefix();
				$poll_name = $wpdb->get_var( $wpdb->prepare(
					"SELECT content FROM {$bp_prefix}bp_activity WHERE id = %d",
					$activity_id
				) );

				// Create filename based on poll name (sanitized) or fall back to activity ID.
				if ( ! empty( $poll_name ) ) {
					// Remove HTML tags and sanitize for filename.
					$poll_name = wp_strip_all_tags( $poll_name );
					// Limit length and sanitize.
					$poll_name = substr( $poll_name, 0, 50 );
					$poll_name = sanitize_file_name( $poll_name );
					// Replace spaces with hyphens.
					$poll_name = str_replace( ' ', '-', $poll_name );
					$file      = 'poll-' . $poll_name . '-' . $activity_id . '.csv';
				} else {
					$file = 'buddypress-poll-' . $activity_id . '.csv';
				}

				/**
				 * Filter the CSV export filename.
				 *
				 * @since 4.5.0
				 *
				 * @param string $file         The filename for the CSV export.
				 * @param int    $activity_id  The activity ID.
				 * @param string $poll_name    The poll name/question.
				 */
				$file = apply_filters( 'bpolls_csv_export_filename', $file, $activity_id, $poll_name );

				$uploads_path = ABSPATH . 'wp-content/uploads/';
				$fp           = fopen( $uploads_path . $file, 'a' ) or die( "Error Couldn't open $file for writing!" ); // phpcs:ignore WordPress.Security.EscapeOutput

				// Allow site owners to customize CSV export settings via filters.
				$csv_settings = array(
					'vote_yes'        => __( 'Yes', 'buddypress-polls' ),
					'vote_no'         => __( '-', 'buddypress-polls' ),
					'date_format'     => get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
					'include_date'    => true,
					'user_field'      => 'display_name', // Options: 'display_name', 'user_login', 'user_email'.
				);

				/**
				 * Filter CSV export settings.
				 *
				 * @since 4.5.0
				 *
				 * @param array $csv_settings {
				 *     CSV export configuration.
				 *
				 *     @type string $vote_yes     Value to display when user voted for option. Default 'Yes'.
				 *     @type string $vote_no      Value to display when user did not vote for option. Default '-'.
				 *     @type string $date_format  PHP date format for vote timestamp. Default WP date + time format.
				 *     @type bool   $include_date Whether to include vote date column. Default true.
				 *     @type string $user_field   User field to display. Options: 'display_name', 'user_login', 'user_email'.
				 * }
				 * @param int   $activity_id The activity ID being exported.
				 */
				$csv_settings = apply_filters( 'bpolls_csv_export_settings', $csv_settings, $activity_id );

				// Build CSV header with translatable column names.
				$csv_header = array(
					__( 'User ID', 'buddypress-polls' ),
					__( 'User Name', 'buddypress-polls' ),
				);

				// Add vote date column if enabled.
				if ( ! empty( $csv_settings['include_date'] ) ) {
					$csv_header[] = __( 'Vote Date', 'buddypress-polls' );
				}

				foreach ( $activity_meta['poll_option'] as $key => $value ) {
					$csv_header[] = $value;
				}

				/**
				 * Filter CSV header row.
				 *
				 * @since 4.5.0
				 *
				 * @param array $csv_header   Array of column headers.
				 * @param array $activity_meta The poll activity meta data.
				 * @param int   $activity_id   The activity ID.
				 */
				$csv_header = apply_filters( 'bpolls_csv_export_header', $csv_header, $activity_meta, $activity_id );

				fputs( $fp, $bom = ( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) );
				fputcsv( $fp, $csv_header, ',', '"', '\\' );
				$users         = $activity_meta['poll_users'];
				$args          = array(
					'include' => $users,
				);

				$users       = new WP_User_Query( $args );
				$users_found = $users->get_results();
				foreach ( $users_found as $user ) {
					$results['users'][] = $user->ID;
					$user_id            = $user->ID;

					// Get user display field based on settings.
					switch ( $csv_settings['user_field'] ) {
						case 'user_email':
							$user_display_name = $user->user_email;
							break;
						case 'user_login':
							$user_display_name = $user->user_login;
							break;
						case 'display_name':
						default:
							$user_display_name = $user->display_name;
							break;
					}

					$user_polls_data         = get_user_meta( $user_id, 'bpoll_user_vote', true );
					$user_activity_poll_data = isset( $user_polls_data[ $activity_id ] ) ? $user_polls_data[ $activity_id ] : array();

					// Get vote timestamp if available.
					$vote_timestamp = '';
					if ( ! empty( $csv_settings['include_date'] ) ) {
						$vote_time_meta = get_user_meta( $user_id, 'bpoll_vote_time_' . $activity_id, true );
						if ( ! empty( $vote_time_meta ) ) {
							$vote_timestamp = date_i18n( $csv_settings['date_format'], $vote_time_meta );
						} else {
							$vote_timestamp = __( 'N/A', 'buddypress-polls' );
						}
					}

					$fields = array( $user_id, $user_display_name );

					// Add vote date if enabled.
					if ( ! empty( $csv_settings['include_date'] ) ) {
						$fields[] = $vote_timestamp;
					}

					foreach ( $activity_meta['poll_option'] as $key => $value ) {
						if ( in_array( $key, $user_activity_poll_data, true ) ) {
							$fields[] = $csv_settings['vote_yes'];
						} else {
							$fields[] = $csv_settings['vote_no'];
						}
					}

					/**
					 * Filter CSV row data for each user.
					 *
					 * @since 4.5.0
					 *
					 * @param array   $fields        Array of field values for this row.
					 * @param WP_User $user          The user object.
					 * @param array   $activity_meta The poll activity meta data.
					 * @param int     $activity_id   The activity ID.
					 */
					$fields = apply_filters( 'bpolls_csv_export_row', $fields, $user, $activity_meta, $activity_id );

					$fields = array_map(function( $field ) {
						return mb_convert_encoding($field, 'ISO-8859-1', 'UTF-8');
					}, $fields);

					fputcsv( $fp, $fields, ',', '"', '\\' );

				}

				fclose( $fp );

				ignore_user_abort( true );
				set_time_limit( 0 ); // disable the time limit for this script.

				// change the path to fit your websites document structure.
				$dl_file = preg_replace( '([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})', '', $file ); // simple file name validation.
				$dl_file = filter_var( $dl_file, FILTER_SANITIZE_URL ); // Remove (more) invalid characters.
				
				$uploads_path = ABSPATH . 'wp-content/uploads/'; // change the path to fit your websites document structure.
				$full_path    = $uploads_path . $dl_file;

				if ( $fd = fopen( $full_path, 'r' ) ) {
					$path_parts = pathinfo( $full_path );

					header( 'Content-type: application/csv' );
					header( 'Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"' ); // use.
					header( 'Cache-control: private' ); // use this to open files directly.
					header( 'Content-Transfer-Encoding: binary' );
					while ( ! feof( $fd ) ) {
						$buffer = fread( $fd, 2048 );
						echo $buffer; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
				fclose( $fd );
				unlink( $uploads_path . $file );
				exit;
			}
		}

		/**
		 * change_admin_bar_edit_text change text Post to poll for single poll
		 *
		 * @since    4.3.0
		 */
		function change_admin_bar_edit_text() {
			global $wp_admin_bar;
			$current_post_type = get_post_type();
			// Find the "Edit Post" node in the admin bar
			if ( $current_post_type == 'wbpoll' ) {
				$edit_node = $wp_admin_bar->get_node( 'edit' );
				// Check if the node exists and its title is "Edit Post"
				if ( $edit_node && $edit_node->title === 'Edit Post' ) {
					// Change the title to "Edit Poll"
					$edit_node->title = esc_html_e( 'Edit Poll', 'buddypress-polls' );

					// Update the node in the admin bar
					$wp_admin_bar->add_node( $edit_node );
				}
				if ( $edit_node && $edit_node->title === 'View Post' ) {
					// Change the title to "Edit Poll"
					$edit_node->title = esc_html_e( 'View Poll', 'buddypress-polls' );

					// Update the node in the admin bar
					$wp_admin_bar->add_node( $edit_node );
				}
			}
		}

		/**
		 * change_post_title_placeholder change title placeholder for single poll
		 *
		 * @since    4.3.0
		 */
		function change_post_title_placeholder( $title_placeholder ) {
			$screen = get_current_screen();

			if ( $screen->post_type === 'wbpoll' ) {
				$title_placeholder = 'Poll Title';
			}

			return $title_placeholder;
		}

		public function init_wbpoll_type() {
			WBPollHelper::create_wbpoll_post_type();

		}

		/**
		 * wbpoll type post listing extra cols
		 *
		 * @param $wbpoll_columns
		 *
		 * @return mixed
		 */
		public function add_new_poll_columns( $wbpoll_columns ) {

			if ( get_post_type() == 'wbpoll' ) {

				$wbpoll_columns['title']      = esc_html__( 'Poll Title', 'buddypress-polls' );
				$wbpoll_columns['pollstatus'] = esc_html__( 'Status', 'buddypress-polls' );
				$wbpoll_columns['startdate']  = esc_html__( 'Start Date', 'buddypress-polls' );
				$wbpoll_columns['enddate']    = esc_html__( 'End Date', 'buddypress-polls' );
				$wbpoll_columns['date']       = esc_html__( 'Created', 'buddypress-polls' );
				$wbpoll_columns['pollvotes']  = esc_html__( 'Votes', 'buddypress-polls' );
				$wbpoll_columns['shortcode']  = esc_html__( 'Shortcode', 'buddypress-polls' );
			}

			return $wbpoll_columns;
		}//end add_new_poll_columns()

		/**
		 * wbpoll type post listing extra col values
		 *
		 * @param $column_name
		 */
		public function manage_poll_columns( $column_name, $post_id ) {

			global $post;

			// $post_id = $post->ID;

			$end_date     = get_post_meta( $post_id, '_wbpoll_end_date', true );
			$start_date   = get_post_meta( $post_id, '_wbpoll_start_date', true );
			$never_expire = intval( get_post_meta( $post_id, '_wbpoll_never_expire', true ) );
			$total_votes  = absint( get_post_meta( $post_id, '_wbpoll_total_votes', true ) );

			switch ( $column_name ) {

				case 'pollstatus':
					// Get number of images in gallery
					if ( $never_expire == 1 ) {
						if ( new DateTime( $start_date ) > new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ) ) {
							echo '<span class="dashicons dashicons-calendar"></span> ' . esc_html__(
								'Not started yet.',
								'buddypress-polls'
							);
						} else {
							echo '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Active', 'buddypress-polls' );
						}
					} else {
						if ( new DateTime( $start_date ) > new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ) ) {
							echo '<span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Not started yet.', 'buddypress-polls' );
						} else {
							if ( new DateTime( $start_date ) <= new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ) && new DateTime( $end_date ) > new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ) ) {
								echo '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Active', 'buddypress-polls' );
							} else {
								if ( new DateTime( $end_date ) <= new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) ) ) {
									echo '<span class="dashicons dashicons-lock"></span> ' . esc_html__( 'Expired', 'buddypress-polls' );
								}
							}
						}
					}
					break;
				case 'startdate':
					echo esc_html( $start_date );
					break;
				case 'enddate':
					echo esc_html( $end_date );
					break;
				case 'pollvotes':
					echo apply_filters( 'wbpoll_admin_listing_votes', $total_votes, $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					break;
				case 'shortcode':
					echo '<span id="wbpollshortcode-' . esc_attr( $post_id ) . '" class="wbpollshortcode wbpollshortcode-' . esc_attr( $post_id ) . '">[wbpoll id="' . esc_attr( $post_id ) . '"]</span><span class="wbpoll_ctp" aria-label="' . esc_attr__( 'Click to copy', 'buddypress-polls' ) . '" data-balloon-pos="down">&nbsp;</span>';
					break;

				default:
					break;
			} // end switch.

		}//end manage_poll_columns()

		/**
		 * wbpoll type post liting extra col sortable
		 *
		 * make poll table columns sortable
		 */
		function wbpoll_columnsort( $columns ) {
			$columns['startdate']  = 'startdate';
			$columns['enddate']    = 'enddate';
			$columns['pollstatus'] = 'pollstatus';
			$columns['pollvotes']  = 'pollvotes';

			return $columns;
		}

		/**
		 * Hook custom meta box
		 */
		function bpolls_metaboxes_display() {
			// add meta box in left side to show Poll Settings
			add_meta_box(
				'pollcustom_meta_box',
				esc_html__( 'Poll Options', 'buddypress-polls' ),
				array( $this, 'bpolls_metabox_setting_display' ),
				'wbpoll',
				'normal',
				'high'
			);

			// add meta box in right col to show the result
			add_meta_box(
				'pollresult_meta_box',
				esc_html__( 'Poll Result', 'buddypress-polls' ),
				array( $this, 'bpolls_metabox_result_display' ),
				'wbpoll',
				'side',
				'low'
			);

			// add meta box in right col to show the result
			add_meta_box(
				'pollshortcode_meta_box',
				esc_html__( 'Shortcode', 'buddypress-polls' ),
				array( $this, 'bpolls_metabox_shortcode_display' ),
				'wbpoll',
				'side',
				'low'
			);

			// add meta box in right col to show the result
			add_meta_box(
				'pollsembed_meta_box',
				esc_html__( 'Embed', 'buddypress-polls' ),
				array( $this, 'bpolls_metabox_embed_display' ),
				'wbpoll',
				'side',
				'low'
			);
		} //end metaboxes_display()

		/**
		 * Meta box display: Setting
		 */
		function bpolls_metabox_setting_display() {
			global $post;
			$post_meta_fields = WBPollHelper::get_meta_fields();

			$poll_postid = isset( $post->ID ) ? intval( $post->ID ) : 0;

			$prefix = '_wbpoll_';

			// $answer_counter = 0;
			$new_index = 0;

			$is_voted              = 0;
			$poll_answers          = array();
			$poll_colors           = array();
			$full_size_image       = array();
			$thumbnail_size_image  = array();
			$video_url             = array();
			$video_thumbnail_image = array();
			$audio_url             = array();
			$audio_thumbnail_image = array();
			$html_code             = array();
			$iframe_video_url      = array();
			$iframe_audio_url      = array();

			if ( $poll_postid > 0 ) :
				$is_voted = WBPollHelper::is_poll_voted( $poll_postid );

				$poll_answers       = get_post_meta( $poll_postid, '_wbpoll_answer', true );
				$poll_answers_extra = get_post_meta( $poll_postid, '_wbpoll_answer_extra', true );

				if ( ! is_array( $poll_answers_extra ) ) {
					$poll_answers_extra = array();
				}
				$poll_color = get_post_meta( $poll_postid, '_wbpoll_answer_color', true );
				if ( isset( $poll_color ) && ! empty( $poll_color ) ) {
					$poll_colors = $poll_color;
				}

				$full_size_images = get_post_meta( $poll_postid, '_wbpoll_full_size_image_answer', true );
				if ( isset( $full_size_images ) && ! empty( $full_size_images ) ) {
					$full_size_image = $full_size_images;
				}

				$thumbnail_size_images = get_post_meta( $poll_postid, '_wbpoll_full_thumbnail_image_answer', true );
				if ( isset( $thumbnail_size_images ) && ! empty( $thumbnail_size_images ) ) {
					$thumbnail_size_image = $thumbnail_size_images;
				}

				$video_urls = get_post_meta( $poll_postid, '_wbpoll_video_answer_url', true );
				if ( isset( $video_urls ) && ! empty( $video_urls ) ) {
					$video_url = $video_urls;
				}

				$iframe_video_urls = get_post_meta( $poll_postid, '_wbpoll_video_import_info', true );
				if ( isset( $iframe_video_url ) && ! empty( $iframe_video_urls ) ) {
					$iframe_video_url = $iframe_video_urls;
				}

				$video_thumbnail_images = get_post_meta( $poll_postid, '_wbpoll_video_thumbnail_image_url', true );
				if ( isset( $video_thumbnail_images ) && ! empty( $video_thumbnail_images ) ) {
					$video_thumbnail_image = $video_thumbnail_images;
				}

				$audio_urls = get_post_meta( $poll_postid, '_wbpoll_audio_answer_url', true );
				if ( isset( $audio_urls ) && ! empty( $audio_urls ) ) {
					$audio_url = $audio_urls;
				}

				$iframe_audio_urls = get_post_meta( $poll_postid, '_wbpoll_audio_import_info', true );
				if ( isset( $iframe_audio_urls ) && ! empty( $iframe_audio_urls ) ) {
					$iframe_audio_url = $iframe_audio_urls;
				}

				$audio_thumbnail_images = get_post_meta( $poll_postid, '_wbpoll_audio_thumbnail_image_url', true );
				if ( isset( $audio_thumbnail_images ) && ! empty( $audio_thumbnail_images ) ) {
					$audio_thumbnail_image = $audio_thumbnail_images;
				}

				$html_codes = get_post_meta( $poll_postid, '_wbpoll_html_answer', true );
				if ( isset( $html_codes ) && ! empty( $html_codes ) ) {
					$html_code = $html_codes;
				}

				$new_index = isset( $poll_answers_extra['answercount'] ) ? intval( $poll_answers_extra['answercount'] ) : 0;

				if ( is_array( $poll_answers ) ) {
					if ( $new_index == 0 && sizeof( $poll_answers ) > 0 ) {
						$old_index = $new_index;
						foreach ( $poll_answers as $index => $poll_answer ) {
							if ( $index > $old_index ) {
								$old_index = $index;
							} //find the greater index
						}

						if ( $old_index > $new_index ) {
							$new_index = intval( $old_index ) + 1;
						}
					}
				} else {
					$poll_answers = array();
				}
				$poll_type = get_post_meta( $poll_postid, 'poll_type', true );

				wp_nonce_field( 'wbpoll_meta_box', 'wbpoll_meta_box_nonce' );
				?>
				<div id="wbpoll_answer_wrap" class="wbpoll_answer_wrap" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
					<h3><?php echo esc_html__( 'Poll Answers', 'buddypress-polls' ); ?></h3>
					<div class="wb-poll-answers-items-content-wrapper">
					<div class="preloaderBg" id="preloader" onload="preloader()"><div class="preloader2"></div></div>
					<input type="hidden" id="poll_type" name="poll_type" value="<?php echo ! empty( $poll_type ) ? esc_attr( $poll_type ) : 'default'; ?>">
					<div class="wbpoll-buttons-horizontal">
						<div class="add-wb-poll-answer-wrap add-wb-poll-answer-wrap" data-busy="0" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
							<a data-type="default" id="add-wb-poll-answer-default" class="float-left button button-primary add-wb-poll-answer add-wb-poll-answer-default add-wb-poll-answer-<?php echo esc_attr( $poll_postid ); ?>">
								<i class="dashicons dashicons-editor-textcolor"></i> <?php echo esc_html__( 'Text Answer', 'buddypress-polls' ); ?>
							</a>
							<?php do_action( 'wbpolladmin_add_answertype', $poll_postid, $new_index ); ?>
						</div>
						<div class="add-wb-poll-answer-wrap add-wb-poll-answer-image-wrap" data-busy="0" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
							<a data-type="image" id="add-wb-poll-image-answer" class="float-left button button-primary add-wb-poll-image-answer add-wb-poll-answer-image add-wb-poll-image-answer-<?php echo esc_attr( $poll_postid ); ?>">
								<i class="dashicons dashicons-format-image"></i> <?php echo esc_html__( 'Image Answer', 'buddypress-polls' ); ?>
							</a>
							<?php do_action( 'wbpolladmin_add_answertype', $poll_postid, $new_index ); ?>
						</div>
						<div class="add-wb-poll-answer-wrap add-wb-poll-answer-video-wrap" data-busy="0" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
							<a data-type="video" id="add-wb-poll-video-answer" class="float-left button button-primary add-wb-poll-video-answer add-wb-poll-answer-video add-wb-poll-video-answer-<?php echo esc_attr( $poll_postid ); ?>">
								<i class="dashicons dashicons-format-video"></i> <?php echo esc_html__( 'Video Answer', 'buddypress-polls' ); ?>
							</a>
							<?php do_action( 'wbpolladmin_add_answertype', $poll_postid, $new_index ); ?>
						</div>
						<div class="add-wb-poll-answer-wrap add-wb-poll-answer-audio-wrap" data-busy="0" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
							<a data-type="audio" id="add-wb-poll-audio-answer" class="float-left button button-primary add-wb-poll-audio-answer add-wb-poll-answer-audio add-wb-poll-audio-answer-<?php echo esc_attr( $poll_postid ); ?>">
								<i class="dashicons dashicons-format-audio"></i> <?php echo esc_html__( 'Audio Answer', 'buddypress-polls' ); ?>
							</a>
							<?php do_action( 'wbpolladmin_add_answertype', $poll_postid, $new_index ); ?>
						</div>
						<div class="add-wb-poll-answer-wrap add-wb-poll-answer-html-wrap" data-busy="0" data-postid="<?php echo esc_attr( $poll_postid ); ?>">
							<a data-type="html" id="add-wb-poll-html-answer" class="float-left button button-primary add-wb-poll-html-answer add-wb-poll-html-answer add-wb-poll-html-answer-<?php echo esc_attr( $poll_postid ); ?>">
								<i class="dashicons dashicons-html"></i> <?php echo esc_html__( 'HTML Answer', 'buddypress-polls' ); ?>
							</a>
							<?php do_action( 'wbpolladmin_add_answertype', $poll_postid, $new_index ); ?>
						</div>
					</div>




				<?php
				echo '<ul id="wb_poll_answers_items" class="wb_poll_answers_items wb_poll_answers_items_' . esc_attr( $post->ID ) . '">';

				if ( sizeof( $poll_answers ) > 0 ) {
					$i = 1;
					foreach ( $poll_answers as $index => $poll_answer ) {
						$number = $i++;
						if ( isset( $poll_answer ) && ! empty( $poll_answer ) ) {
							$poll_answers_extra[ $index ] = isset( $poll_answers_extra[ $index ] ) ? $poll_answers_extra[ $index ] : '';
							// color
							$poll_colors[ $index ] = isset( $poll_colors[ $index ] ) ? $poll_colors[ $index ] : array();
							// image
							$thumbnail_size_image[ $index ] = isset( $thumbnail_size_image[ $index ] ) ? $thumbnail_size_image[ $index ] : array();
							$full_size_image[ $index ]      = isset( $full_size_image[ $index ] ) ? $full_size_image[ $index ] : array();

							// video
							$video_url[ $index ]             = isset( $video_url[ $index ] ) ? $video_url[ $index ] : array();
							$video_thumbnail_image[ $index ] = isset( $video_thumbnail_image[ $index ] ) ? $video_thumbnail_image[ $index ] : array();
							$iframe_video_url[ $index ]      = isset( $iframe_video_url[ $index ] ) ? $iframe_video_url[ $index ] : array();
							// audio

							$audio_url[ $index ]             = isset( $audio_url[ $index ] ) ? $audio_url[ $index ] : array();
							$audio_thumbnail_image[ $index ] = isset( $audio_thumbnail_image[ $index ] ) ? $audio_thumbnail_image[ $index ] : array();
							$iframe_audio_url[ $index ]      = isset( $iframe_audio_url[ $index ] ) ? $iframe_audio_url[ $index ] : array();
							// HTML
							$html_code[ $index ] = isset( $html_code[ $index ] ) ? $html_code[ $index ] : array();

							echo WBPollHelper::wbpoll_answer_field_template( $index, $poll_answer, $poll_colors[ $index ], $is_voted, $poll_answers_extra[ $index ], $poll_postid, $full_size_image[ $index ], $thumbnail_size_image[ $index ], $video_url[ $index ], $video_thumbnail_image[ $index ], $html_code[ $index ], $audio_url[ $index ], $audio_thumbnail_image[ $index ], $number, $iframe_video_url[ $index ], $iframe_audio_url[ $index ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
				}

				// $answer_counter = 3;
				if ( ! $is_voted && sizeof( $poll_answers ) == 0 ) {
					$default_answers_titles = array(
						esc_html__( 'Yes', 'buddypress-polls' ),
						esc_html__( 'No', 'buddypress-polls' ),
						esc_html__( 'No comments', 'buddypress-polls' ),
					);

					$default_answers_colors = array(
						'#2f7022',
						'#dd6363',
						'#e4e4e4',
					);

					$answers_extra         = array( 'type' => 'default' );
					$thumbnail_size_image  = array();
					$full_size_image       = array();
					$video_url             = array();
					$video_thumbnail_image = array();
					$audio_url             = array();
					$audio_thumbnail_image = array();
					$html_code             = array();
					$thumbnail_size_image  = array();

					foreach ( $default_answers_titles as $index => $answers_title ) {

						// image.
						$thumbnail_size_image[ $index ] = isset( $thumbnail_size_image[ $index ] ) ? $thumbnail_size_image[ $index ] : array();
						$full_size_image[ $index ]      = isset( $full_size_image[ $index ] ) ? $full_size_image[ $index ] : array();

						// video.
						$video_url[ $index ]             = isset( $video_url[ $index ] ) ? $video_url[ $index ] : array();
						$video_thumbnail_image[ $index ] = isset( $video_thumbnail_image[ $index ] ) ? $video_thumbnail_image[ $index ] : array();

						// audio.
						$audio_url[ $index ]             = isset( $audio_url[ $index ] ) ? $audio_url[ $index ] : array();
						$audio_thumbnail_image[ $index ] = isset( $audio_thumbnail_image[ $index ] ) ? $audio_thumbnail_image[ $index ] : array();

						// HTML.
						$html_code[ $index ] = isset( $html_code[ $index ] ) ? $html_code[ $index ] : array();
						// phpcs:disable
						echo WBPollHelper::wbpoll_answer_field_template( 
							intval( $index ) + $new_index,
							$default_answers_titles[ $index ],
							$default_answers_colors[ $index ],
							$is_voted,
							$answers_extra,
							$poll_postid,
							$full_size_image[ $index ],
							$thumbnail_size_image[ $index ],
							$video_url[ $index ],
							$video_thumbnail_image[ $index ],
							$html_code[ $index ],
							$audio_url[ $index ],
							$audio_thumbnail_image[ $index ],
						);
						// phpcs:enable
					}

					$new_index = intval( $index ) + $new_index + 1;
				}

				echo '</ul>';
				?>
				<input type="hidden" id="wbpoll_answer_extra_answercount" value="<?php echo intval( $new_index ); ?>" name="_wbpoll_answer_extra[answercount]" />
			</div><!-- .wb-poll-answers-items-content-wrapper -->

		<br />

				<?php
				echo '</div>';

				echo '<div class="wbcom-polls-option-wrap">';
				echo '<table class="form-table wbpoll-answer-options wbcom">';

				foreach ( $post_meta_fields as $field ) {

					$meta = get_post_meta( $poll_postid, $field['id'], true );

					if ( $meta == '' && isset( $field['default'] ) ) {

						$meta = $field['default'];
					}

					$label = isset( $field['label'] ) ? $field['label'] : '';

					echo '<tr class="' . esc_attr( $field['id'] ) . '">';
					echo '<th><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $label ) . '</label></th>';
					echo '<td>';

					switch ( $field['type'] ) {

						case 'text':
							echo '<input type="text" class="regular-text 111" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-text-' . esc_attr( $poll_postid ) . '" value="' . esc_attr( $meta ) . '" size="30" />
							<span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'number':
							echo '<input type="number" class="regular-text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-number-' . esc_attr( $poll_postid ) . '" value="' . esc_attr( $meta ) . '" size="30" />
							<span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'date':
							echo '<input type="text" class="wbpollmetadatepicker" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-date-' . esc_attr( $poll_postid ) . '" value="' . esc_attr( $meta ) . '" size="30" />
							<span class="description">' . ( $field['desc'] ) . '</span>'; // phpcs:ignore
							break;

						case 'colorpicker':
							echo '<input type="text" class="wbpoll-colorpicker" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-date-' . esc_attr( $poll_postid ) . '" value="' . esc_attr( $meta ) . '" size="30" />
							<span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'multiselect':
							echo '<div class="wbpoll-multiselect-wrapper">';
							echo '<select name="' . esc_attr( $field['id'] ) . '[]" id="' . esc_attr( $field['id'] ) . '-chosen-' . esc_attr( $poll_postid ) . '" class="selecttwo-select" multiple="multiple">';
							if ( isset( $field['optgroup'] ) && intval( $field['optgroup'] ) ) {
								foreach ( $field['options'] as $optlabel => $data ) {
									echo '<optgroup label="' . esc_attr( $optlabel ) . '">';
									foreach ( $data as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . ( is_array( $meta ) && in_array( $key, $meta ) ? ' selected="selected"' : '' ) . '>' . esc_html( $val ) . '</option>';
									}
									echo '<optgroup>';
								}
							} else {
								foreach ( $field['options'] as $key => $val ) {
									echo '<option value="' . esc_attr( $key ) . '" ' . ( is_array( $meta ) && in_array( $key, $meta ) ? ' selected="selected"' : '' ) . '>' . esc_html( $val ) . '</option>';
								}
							}
							echo '</select><span class="description">' . esc_html( $field['desc'] ) . '</span>';
							echo '<div>';
							break;

						case 'select':
							echo '<select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-select-' . esc_attr( $poll_postid ) . '" class="cb-select select-' . esc_attr( $poll_postid ) . '">';
							if ( isset( $field['optgroup'] ) && intval( $field['optgroup'] ) ) {
								foreach ( $field['options'] as $optlabel => $data ) {
									echo '<optgroup label="' . esc_attr( $optlabel ) . '">';
									foreach ( $data as $index => $option ) {
										echo '<option ' . ( ( $meta == $index ) ? ' selected="selected"' : '' ) . ' value="' . esc_attr( $index ) . '">' . esc_html( $option ) . '</option>';
									}
								}
							} else {
								foreach ( $field['options'] as $index => $option ) {
									echo '<option ' . ( ( $meta == $index ) ? ' selected="selected"' : '' ) . ' value="' . esc_attr( $index ) . '">' . esc_html( $option ) . '</option>';
								}
							}
							echo '</select><br/><span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'radio':
							echo '<fieldset class="radio_fields">
							<legend class="screen-reader-text"><span>input type="radio"</span></legend>';
							foreach ( $field['options'] as $key => $value ) {
								echo '<label title="g:i a" for="' . esc_attr( $field['id'] ) . '-radio-' . esc_attr( $poll_postid ) . '-' . esc_attr( $key ) . '">
									<input id="' . esc_attr( $field['id'] ) . '-radio-' . esc_attr( $poll_postid ) . '-' . esc_attr( $key ) . '" type="radio" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $key ) . '" ' . ( ( $meta == $key ) ? '  checked="checked" ' : '' ) . '  />
									<span>' . esc_html( $value ) . '</span>
								</label>';
							}
							echo '</fieldset>';
							echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'checkbox':
							echo '<input type="checkbox" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '-checkbox-' . esc_attr( $poll_postid ) . '" class="cb-checkbox checkbox-' . esc_attr( $poll_postid ) . '" ' . ( $meta ? ' checked="checked"' : '' ) . '/>
								<span for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['desc'] ) . '</span>';
							break;

						case 'checkbox_group':
							if ( $meta == '' ) {
								$meta = array();
								foreach ( $field['options'] as $option ) {
									array_push( $meta, $option['value'] );
								}
							}

							foreach ( $field['options'] as $option ) {
								echo '<input type="checkbox" value="' . esc_attr( $option['value'] ) . '" name="' . esc_attr( $field['id'] ) . '[]" id="' . esc_attr( $option['value'] ) . '-mult-chk-' . esc_attr( $poll_postid ) . '-field-' . esc_attr( $field['id'] ) . '" class="cb-multi-check mult-check-' . esc_attr( $poll_postid ) . '" ' . ( $meta && in_array( $option['value'], $meta ) ? ' checked="checked"' : '' ) . ' />
									<label for="' . esc_attr( $option['value'] ) . '">' . esc_html( $option['label'] ) . '</label><br/>';
							}
							echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
							break;
					}
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
				echo '</div>';

			else :
				echo esc_html__( 'Please save the post before entering poll answers.', 'buddypress-polls' );
			endif;
		} //end metabox_setting_display()

		/**
		 * Renders metabox in right col to show result
		 */
		function bpolls_metabox_result_display() {
			global $post;
			$poll_postid = $post->ID;

			$poll_output = WBPollHelper::show_backend_single_poll_result( $poll_postid, 'shortcode', 'text' );

			echo $poll_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} //end metabox_result_display()

		/**
		 * Renders metabox in right col to show  shortcode with copy to clipboard
		 */
		function bpolls_metabox_shortcode_display() {
			global $post;
			$post_id = $post->ID;
			// phpcs:disable
			echo wp_kses_post( '<span  id="wbpollshortcode-' . intval( $post_id ) . '" class="wbpollshortcode wbpollshortcode-single wbpollshortcode-' . intval( $post_id ) . '">[wbpoll id="' . intval( $post_id ) . '"]</span><span class="wbpoll_ctp" aria-label="' . esc_html__(
				'Click to copy',
				'buddypress-polls'
			) . '" data-balloon-pos="down">&nbsp;</span>' );
			echo wp_kses_post( '<div class="wbpollclear"></div>' );
			// phpcs:enable
		} //end metabox_shortcode_display()

		function bpolls_metabox_embed_display() {
			global $post;
			$post_id = $post->ID;
			$iframe  = esc_attr( sprintf( '<iframe id="%s" src="%s" frameborder="0" allowtransparency="true" width="100%%" height="400"></iframe>', 'wbpollemded-iframe-' . $post_id, add_query_arg( array( 'embed' => true ), get_permalink( $post_id ) ) ) );
			// phpcs:disable
			echo wp_kses_post( '<span  id="wbpollemded-' . intval( $post_id ) . '" class="wbpollemded wbpollemded-single wbpollemded-' . intval( $post_id ) . '">' . $iframe . '</span><span class="wbpoll_embed" aria-label="' . esc_html__(
				'Click to copy',
				'buddypress-polls'
			) . '" data-balloon-pos="down">&nbsp;</span>' );
			echo wp_kses_post( '<div class="wbpollclear"></div>' );
			// phpcs:enable
		}

		/**
		 * Save wbpoll metabox
		 *
		 * @param $post_id
		 *
		 * @return bool
		 */
		function bppolls_metabox_save( $post_id ) {
			// Check if our nonce is set.
			if ( ! isset( $_POST['wbpoll_meta_box_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wbpoll_meta_box_nonce'] ) ), 'wbpoll_meta_box' ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'wbpoll' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			global $post;
			$post   = get_post( $post_id );
			$status = $post->post_status;

			$prefix = '_wbpoll_';

			// handling extra fields
			if ( isset( $_POST[ $prefix . 'answer_extra' ] ) ) {
				$extra = map_deep(wp_unslash( $_POST[ $prefix . 'answer_extra' ] ),'sanitize_text_field');
				update_post_meta( $post_id, $prefix . 'answer_extra', $extra );
			} else {
				delete_post_meta( $post_id, $prefix . 'answer_extra' );
			}

			//Save Poll Type from back end
			if ( isset( $_POST['poll_type'] ) ) {
				update_post_meta( $post_id, 'poll_type', sanitize_text_field( wp_unslash( $_POST['poll_type'] ) ) );
			}

			// handle answer titles
			if ( isset( $_POST[ $prefix . 'answer' ] ) ) {
				$titles = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'answer' ] ) );

				foreach ( $titles as $index => $title ) {
					$titles[ $index ] = sanitize_text_field( $title );
				}

				update_post_meta( $post_id, $prefix . 'answer', $titles );
			} else {
				delete_post_meta( $post_id, $prefix . 'answer' );
			}

			// Full size image answer
			if ( isset( $_POST[ $prefix . 'full_size_image_answer' ] ) ) {
				$images = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'full_size_image_answer' ] ) );

				foreach ( $images as $index => $url ) {
					$images[ $index ] = sanitize_text_field( $url );
				}

				update_post_meta( $post_id, $prefix . 'full_size_image_answer', $images );
			} else {
				delete_post_meta( $post_id, $prefix . 'full_size_image_answer' );
			}

			// video url
			if ( isset( $_POST[ $prefix . 'video_answer_url' ] ) ) {
				$images = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'video_answer_url' ] ) );

				foreach ( $images as $index => $url ) {
					$images[ $index ] = $url;
				}

				update_post_meta( $post_id, $prefix . 'video_answer_url', $images );
			} else {
				delete_post_meta( $post_id, $prefix . 'video_answer_url' );
			}

			// video suggestion
			if ( isset( $_POST[ $prefix . 'video_import_info' ] ) ) {
				$suggestion = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'video_import_info' ] ) );
				foreach ( $suggestion as $index => $text ) {
					$suggestion[ $index ] = sanitize_text_field( $text );
				}

				update_post_meta( $post_id, $prefix . 'video_import_info', $suggestion );
			} else {
				delete_post_meta( $post_id, $prefix . 'video_import_info' );
			}

			// Audio url
			if ( isset( $_POST[ $prefix . 'audio_answer_url' ] ) ) {
				$images = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'audio_answer_url' ] ) );

				foreach ( $images as $index => $url ) {
					$images[ $index ] = $url;
				}

				update_post_meta( $post_id, $prefix . 'audio_answer_url', $images );
			} else {
				delete_post_meta( $post_id, $prefix . 'audio_answer_url' );
			}

			// audio suggestion
			if ( isset( $_POST[ $prefix . 'audio_import_info' ] ) ) {
				$suggestion = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'audio_import_info' ] ) );

				foreach ( $suggestion as $index => $text ) {
					$suggestion[ $index ] = sanitize_text_field( $text );
				}

				update_post_meta( $post_id, $prefix . 'audio_import_info', $suggestion );
			} else {
				delete_post_meta( $post_id, $prefix . 'audio_import_info' );
			}

			// HTML textarea answer
			if ( isset( $_POST[ $prefix . 'html_answer' ] ) ) {
				$htmls = array_map('sanitize_text_field', wp_unslash( $_POST[ $prefix . 'html_answer' ] ) );

				foreach ( $htmls as $index => $html ) {
					$htmls[ $index ] = $html;
				}

				update_post_meta( $post_id, $prefix . 'html_answer', $htmls );
			} else {
				delete_post_meta( $post_id, $prefix . 'html_answer' );
			}

			$this->bpolls_metabox_extra_save( $post_id );
		} //end metabox_save()

		public function bpolls_metabox_extra_save( $post_id ) {
			// global $post_meta_fields;
			$post_meta_fields = WBPollHelper::get_meta_fields();

			$prefix = '_wbpoll_';

			$cb_date_array = array();
			foreach ( $post_meta_fields as $field ) {

				$old = get_post_meta( $post_id, $field['id'], true );
				$new = '';
				
				//phpcs:disable
				if( isset( $_POST[$field['id']] ) && !is_array( $_POST[$field['id']] ) ) {
					$new = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) );
				} else if( isset( $_POST[$field['id']] ) && is_array( $_POST[$field['id']] ) ) {
					$new = map_deep( $_POST[ $field['id'] ] , 'sanitize_text_field' ); 
				}
				//phpcs:enable

				if ( ( $prefix . 'start_date' == $field['id'] && $new == '' ) || ( $prefix . 'end_date' == $field['id'] && $new == '' ) ) {

					$cbpollerror = '<div class="notice notice-error inline"><p>' . esc_html__(
						'Error: Start date or end date is missing. ',
						'buddypress-polls'
					) . '</p></div>';

					return false; // might stop processing here
				} else {
									
					update_post_meta( $post_id, $field['id'], $new );
				}
			}
		}

		/**
		 * Get Text answer templte
		 */
		public function bpolls_get_answer_template() {
			// security check
			check_ajax_referer( 'wbpoll', 'security' );

			// get the fields

			$index        = !empty( $_POST['answer_counter'] ) ? intval( $_POST['answer_counter'] ) : 0;
			$answer_color = !empty( $_POST['answer_color'] ) ? sanitize_text_field( wp_unslash( $_POST['answer_color'] ) ) : '';
			$is_voted     = !empty( $_POST['is_voted'] ) ? intval( $_POST['is_voted'] ) : 0;
			$poll_postid  = !empty( $_POST['poll_postid'] ) ? intval( $_POST['poll_postid'] ) : 0;
			$answer_type  = !empty( $_POST['answer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['answer_type'] ) ) : '';

			$answers_extra = array( 'type' => $answer_type );
			 /* translators: %d: */
			$poll_answer = sprintf( esc_html__( 'Answer %d', 'buddypress-polls' ), ( $index + 1 ) );

			$template = WBPollHelper::wbpoll_answer_field_template(
				$index,
				$poll_answer,
				$answer_color,
				$is_voted,
				$answers_extra,
				$poll_postid
			);

			echo json_encode( $template );
			die();
		}

		public function wbpolls_log_delete() {
				
			if( ! isset( $_POST['ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ajax_nonce'] ) ), 'wbpoll' ) ) {
				return;
			}
			
			// Capability check.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'buddypress-polls' ) ) );
			}

			// Sanitize input.
			$logid = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
			if ( 0 === $logid ) {
				wp_send_json_error( array( 'message' => __( 'Invalid log ID.', 'buddypress-polls' ) ) );
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'wppoll_log';
			$result     = $wpdb->delete( $table_name, array( 'id' => $logid ), array( '%d' ) );

			if ( false !== $result ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to delete log.', 'buddypress-polls' ) ) );
			}
		}

		public function wbpoll_logs_page_callback() {
			// Code to display the "Log" submenu page content
			global $wpdb;
			$polls_logs_results = $wpdb->get_results( "SELECT * from {$wpdb->prefix}wppoll_log order by created desc" );
			?>
			<h2><?php esc_html__( 'WB Poll Logs', 'buddypress-polls' ); ?></h2>
			<table class="wbpolls-log-table widefat fixed striped posts">
				<thead>
					<tr>
						<th><strong><?php echo esc_html__( 'Status', 'buddypress-polls' ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Poll', 'buddypress-polls' ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'User Name', 'buddypress-polls' ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Date', 'buddypress-polls' ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Action', 'buddypress-polls' ); ?></strong></th>
					</tr>
				</thead>				
				<tbody>
				<?php
				if ( ! empty( $polls_logs_results ) ) {
					foreach ( $polls_logs_results as $log ) {
						?>
					<tr>
						<td class="log-status"><?php echo esc_html__( $log->poll_status, 'buddypress-polls' ); ?></td>
						<td class="log-title"><?php echo esc_html__( get_the_title( $log->poll_id ), 'buddypress-polls' ); ?></td>
						<td class="log-user"><?php echo esc_html__( $log->user_name, 'buddypress-polls' ); ?></td>
						<td class="log-data"><?php echo esc_html( date_i18n( 'Y-m-d H:i:s', $log->created ), 'buddypress-polls' ); ?></td>
						<td class="log-action"><button class="button button-small action open_log" data-id="<?php echo esc_attr( $log->id ); ?>"><?php echo esc_html__( 'Open', 'buddypress-polls' ); ?></button><button class="button button-small action delete_log" data-id="<?php echo esc_attr( $log->id ); ?>"><?php echo esc_html__( 'Delete', 'buddypress-polls' ); ?></button></td>
					</tr>
					<div class="wbpolls-log-modal opendetails-<?php echo esc_attr( $log->id ); ?> openmodal" style="display:none;">
						<div class="modal-content">
							<div class="modal-header">
								<h2><?php echo esc_html__( 'Log', 'buddypress-polls' ); ?></h2>
								<span class="close"><?php echo esc_html__( 'close', 'buddypress-polls' ); ?></span>
							</div>
							<div class="modal-body">
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'Poll:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html__( get_the_title( $log->poll_id ), 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'Received choices:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<?php
										$poll_id       = $log->poll_id;
										$user_answer_t = maybe_unserialize( $log->details );
										$poll_answers  = get_post_meta( $poll_id, '_wbpoll_answer', true );
										foreach ( $user_answer_t as $ans ) {
											if ( isset( $poll_answers ) && ! empty( $poll_answers ) ) {
												$poll_ans_id    = $ans;
												$poll_ans_title = ( isset( $poll_answers[ $poll_ans_id ] ) ) ? $poll_answers[ $poll_ans_id ] : '';
											} else {
												$poll_ans_title = '';
											}
											?>
											<span><?php echo esc_html( $poll_ans_title ); ?></span>
										<?php } ?>												
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'IP:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html( $log->user_ip, 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'Date:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html( date_i18n( 'Y-m-d H:i:s', $log->created ), 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'Browser:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html__( $log->useragent, 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'User Id:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html( $log->user_id, 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'User Login:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html( $log->is_logged_in, 'buddypress-polls' ); ?></span>
									</div>
								</div>
								<div class="modal-body-group">
									<div class="modal-body-group-content left">
										<strong><?php echo esc_html__( 'User Name:', 'buddypress-polls' ); ?></strong>
									</div>
									<div class="modal-body-group-content right">
										<span><?php echo esc_html__( $log->user_name, 'buddypress-polls' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="5"><?php echo esc_html__( 'No logs found.', 'buddypress-polls' ); ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<?php
		}

		/**
		 *  Add Text type poll result display method
		 *
		 * @param  array $methods
		 *
		 * @return array
		 */
		public function poll_display_methods_text( $methods ) {
			$methods['text'] = array(
				'title'  => esc_html__( 'Text', 'buddypress-polls' ),
				'method' => array( $this, 'poll_display_methods_text_result' ),
			);

			return $methods;
		}//end poll_display_methods_text()

		/**
		 *  Add Text type poll result display method
		 *
		 * @param  array $methods
		 *
		 * @return array
		 */
		public function poll_display_methods_text_backend( $methods ) {
			$methods['text'] = array(
				'title'  => esc_html__( 'Text', 'buddypress-polls' ),
				'method' => array( $this, 'poll_display_methods_text_backend_result' ),
			);

			return $methods;
		}//end poll_display_methods_text_backend()

		/**
		 * Display poll Widget result as text method
		 *
		 * @param int    $poll_id
		 *
		 * @param string $poll_result
		 */
		public function poll_display_methods_text_widget_result() {
			 $methods['text'] = array(
				 'title'  => esc_html__( 'Text', 'buddypress-polls' ),
				 'method' => array( $this, 'poll_display_methods_widget_result' ),
			 );

			 return $methods;
		}

		/**
		 * Display poll result as text method
		 *
		 * @param int    $poll_id
		 *
		 * @param string $poll_result
		 */
		public function poll_display_methods_text_result( $poll_id, $reference = '', $poll_result = '' ) {

			$total = intval( $poll_result['total'] );

			$colors = $poll_result['colors'];

			$answers                  = isset( $poll_result['answer'] ) ? $poll_result['answer'] : array();
			$poll_ans_image           = isset( $poll_result['image'] ) ? $poll_result['image'] : array();
			$thumbnail_poll_ans_image = isset( $poll_result['thumb_image'] ) ? $poll_result['thumb_image'] : array();

			$poll_answers_video = isset( $poll_result['video'] ) ? $poll_result['video'] : array();

			$thumbnail_poll_answers_video = isset( $poll_result['thumb_video_img'] ) ? $poll_result['thumb_video_img'] : array();

			$poll_answers_audio           = isset( $poll_result['audio'] ) ? $poll_result['audio'] : array();
			$thumbnail_poll_answers_audio = isset( $poll_result['thumb_audio_img'] ) ? $poll_result['thumb_audio_img'] : array();

			$poll_answers_html = isset( $poll_result['html'] ) ? $poll_result['html'] : array();

			$poll_video_suggestion = isset( $poll_result['video_suggestion'] ) ? $poll_result['video_suggestion'] : array();
			$poll_audio_suggestion = isset( $poll_result['audio_suggestion'] ) ? $poll_result['audio_suggestion'] : array();

			$option_value = get_site_option( 'wbpolls_settings' );
			if ( ! empty( $option_value ) ) {
				$wbpolls_background_color = $option_value['wbpolls_background_color'];
			}

			$output_result = '';

			$class = array();
			foreach ( $poll_result['weighted_index'] as $index => $answer ) {
				if ( ! empty( $poll_ans_image[ $index ] ) || ! empty( $thumbnail_poll_ans_image[ $index ] ) ) {
					$class['class'] = 'wbpoll-image';
				} elseif ( ! empty( $poll_answers_video[ $index ] ) || ! empty( $thumbnail_poll_answers_video[ $index ] ) ) {
					$class['class'] = 'wbpoll-video';
				} elseif ( ! empty( $poll_answers_audio[ $index ] ) || ! empty( $thumbnail_poll_answers_audio[ $index ] ) ) {
					$class['class'] = 'wbpoll-audio';
				} else {
					$class['class'] = 'wbpoll-default';
				}
			}

			if ( $total > 0 ) {
				/* translators: %s: */
				$output  = '<p>' . sprintf( __( 'Total votes: %d', 'buddypress-polls' ), number_format( $total ) ) . '</p>';
				$output .= '<div class="wbpolls-question-results ' . ( isset( $class['class'] ) ? $class['class'] : '' ) . '">';

				$total_percent = 0;
				foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
					$answer_title = isset( $answers[ $index ] ) ? esc_html__( $answers[ $index ], 'buddypress-polls' ) : esc_html__(
						'Unknown Answer',
						'buddypress-polls'
					);
					$color_style  = $wbpolls_background_color;

					$percent = ( $vote_count * 100 ) / $total;

					$total_percent += $percent;

					$output_result .= '<li style="' . $color_style . '"><strong>' . $answer_title . ': ' . $vote_count . ' (' . number_format(
						$percent,
						2
					) . '%)</strong></li>';
					$output_result .= '<div class="bpolls-item-width-wrapper"><div class="wbpoll-question-choices-item-votes-bar" style="width:100%;background-color:#ea6464;"></div><div class="bpolls-check-radio-div"></div></div>';
				}

				if ( $total_percent > 0 ) {
					$output_result = '';

					foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {

						$answer_title   = isset( $answers[ $index ] ) ? esc_html__( $answers[ $index ], 'buddypress-polls' ) : esc_html__(
							'Unknown Answer',
							'buddypress-polls'
						);
						$color          = $wbpolls_background_color;
						$percent        = ( $vote_count * 100 ) / $total;
						$re_percent     = ( $percent * 100 ) / $total_percent;
						$output_result .= '<div class="wbpoll-question-choices-item">';
						$output_result .= '<div class="wbpoll-question-choices-item-container">';
						$output_result .= '<div class="wbpoll-single-answer-label">';

						// image.
						if ( isset( $poll_ans_image[ $index ] ) && ! empty( $poll_ans_image[ $index ] ) && empty( $thumbnail_poll_ans_image[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-image"><span class="poll-image-view" data-id="' . $index . '"></span><img src="' . $poll_ans_image[ $index ] . '"></div></div></div>';
							$output_result .= '<div class="wb-poll-lightbox poll-image-lightbox lightbox-' . $index . '" style="display:none;"><div class="close" data-id="' . $index . '"><svg class="pswp__icn" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M11.53 1.53A.75.75 0 0 0 10.47.47L6 4.94 1.53.47A.75.75 0 1 0 .47 1.53L4.94 6 .47 10.47a.75.75 0 1 0 1.06 1.06L6 7.06l4.47 4.47a.75.75 0 1 0 1.06-1.06L7.06 6l4.47-4.47Z"></path></svg></div><div class="content-area"><img src="' . $poll_ans_image[ $index ] . '"></div></div>';
						} elseif ( isset( $thumbnail_poll_ans_image[ $index ] ) && ! empty( $thumbnail_poll_ans_image[ $index ] ) && empty( $poll_ans_image[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-image"><img src="' . $thumbnail_poll_ans_image[ $index ] . '"></div></div></div>';
						} elseif ( isset( $thumbnail_poll_ans_image[ $index ] ) && ! empty( $thumbnail_poll_ans_image[ $index ] ) && isset( $poll_ans_image[ $index ] ) && ! empty( $poll_ans_image[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-thumb-image poll-image" data-id="' . $index . '"><img src="' . $thumbnail_poll_ans_image[ $index ] . '"></div>';
							$output_result .= '<div class="wb-poll-lightbox poll-image-lightbox lightbox-' . $index . '" style="display:none;"><div class="close" data-id="' . $index . '"><svg class="pswp__icn" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M11.53 1.53A.75.75 0 0 0 10.47.47L6 4.94 1.53.47A.75.75 0 1 0 .47 1.53L4.94 6 .47 10.47a.75.75 0 1 0 1.06 1.06L6 7.06l4.47 4.47a.75.75 0 1 0 1.06-1.06L7.06 6l4.47-4.47Z"></path></svg></div><div class="content-area"><img src="' . $poll_ans_image[ $index ] . '"></div></div></div></div>';
						}

						// video.
						if ( isset( $poll_answers_video[ $index ] ) && ! empty( $poll_answers_video[ $index ] ) && empty( $thumbnail_poll_answers_video[ $index ] ) ) {
							if ( isset( $poll_video_suggestion[ $index ] ) && $poll_video_suggestion[ $index ] == 'yes' ) {
								$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-video"><iframe width="420" height="345" src="' . $poll_answers_video[ $index ] . '"></iframe></div></div></div>';
							} else {
								$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-video"><video src="' . $poll_answers_video[ $index ] . '" controls="" poster="" preload="none"></video></div></div></div>';
							}
						} elseif ( isset( $thumbnail_poll_answers_video[ $index ] ) && ! empty( $thumbnail_poll_answers_video[ $index ] ) && empty( $poll_answers_video[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-thumb-image poll-video-image"><img src="' . $thumbnail_poll_answers_video[ $index ] . '"></div></div></div>';
						} elseif ( isset( $poll_answers_video[ $index ] ) && ! empty( $poll_answers_video[ $index ] ) && isset( $thumbnail_poll_answers_video[ $index ] ) && ! empty( $thumbnail_poll_answers_video[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-thumb-image poll-video-image poll-image"  data-id="' . $index . '"><img src="' . $thumbnail_poll_answers_video[ $index ] . '"></div>';
							$output_result .= '<div class="wb-poll-lightbox poll-video-lightbox lightbox-' . $index . '" style="display:none;"><div class="close" data-id="' . $index . '"><svg class="pswp__icn" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M11.53 1.53A.75.75 0 0 0 10.47.47L6 4.94 1.53.47A.75.75 0 1 0 .47 1.53L4.94 6 .47 10.47a.75.75 0 1 0 1.06 1.06L6 7.06l4.47 4.47a.75.75 0 1 0 1.06-1.06L7.06 6l4.47-4.47Z"></path></svg></div><div class="content-area"><video src="' . $poll_answers_video[ $index ] . '" controls="" poster="" preload="none"></video></div></div></div></div>';
						}

						// audio.
						if ( isset( $poll_answers_audio[ $index ] ) && ! empty( $poll_answers_audio[ $index ] ) && empty( $thumbnail_poll_answers_audio[ $index ] ) ) {
							if ( isset( $poll_audio_suggestion[ $index ] ) && $poll_audio_suggestion[ $index ] == 'yes' ) {
								$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-audio"><iframe width="420" height="345" src="' . $poll_answers_audio[ $index ] . '"></iframe></div></div></div>';
							} else {
								$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-audio"><audio src="' . $poll_answers_audio[ $index ] . '" controls="" preload="none"></audio></div></div></div>';
							}
						} elseif ( isset( $thumbnail_poll_answers_audio[ $index ] ) && ! empty( $thumbnail_poll_answers_audio[ $index ] ) && empty( $poll_answers_audio[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-thumb-image poll-video-image"><img src="' . $thumbnail_poll_answers_audio[ $index ] . '"></div></div></div>';
						} elseif ( isset( $poll_answers_audio[ $index ] ) && ! empty( $poll_answers_audio[ $index ] ) && isset( $thumbnail_poll_answers_audio[ $index ] ) && ! empty( $thumbnail_poll_answers_audio[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-thumb-image poll-audio-image poll-image" data-id="' . $index . '"><img src="' . $thumbnail_poll_answers_audio[ $index ] . '"></div>';
							$output_result .= '<div class="wb-poll-lightbox poll-audio-lightbox lightbox-' . $index . '" style="display:none;"><div class="close" data-id="' . $index . '"><svg class="pswp__icn" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M11.53 1.53A.75.75 0 0 0 10.47.47L6 4.94 1.53.47A.75.75 0 1 0 .47 1.53L4.94 6 .47 10.47a.75.75 0 1 0 1.06 1.06L6 7.06l4.47 4.47a.75.75 0 1 0 1.06-1.06L7.06 6l4.47-4.47Z"></path></svg></div><div class="content-area"><audio src="' . $poll_answers_audio[ $index ] . '" controls="" preload="none" ></audio></div></div></div></div>';
						}

						if ( isset( $poll_answers_html[ $index ] ) && ! empty( $poll_answers_html[ $index ] ) ) {
							$output_result .= '<div class="wbpoll-question-choices-item-content-container"><div class="wbpoll-question-choices-item-content"><div class="poll-html">' . $poll_answers_html[ $index ] . '</div></div></div>';
						}
						$output_result .= '<div class="wbpoll-question-choices-item-label">';

						$output_result .= '<div class="wbpoll-question-choices-item-votes">';
						$output_result .= '<div class="wbpoll-question-choices-item-text"><span class="wbpoll_single_answer">' . esc_html__( $answer_title, 'buddypress-polls' ) . '</span>';

						$output_result .= '</div>';
						$output_result .= '</div>';

						$output_result .= '<div class="bpolls-item-width-wrapper">';

						$output_result .= '<div class="wbpoll-question-choices-item-votes-bar" style="width:' . number_format( $re_percent, 2 ) . '%;background-color:' . $color . '"></div><div class="wbpoll-question-choices-item-votes-bar-data"></div>';

						$output_result .= '</div>'; // bpolls-item-width-wrapper.

						$output_result .= '<div class="wbpoll-vote-percent-data-wrapper">';

						$output_result .= '<div class="wbpoll-user-profile-data-wrapper">';
						$output_result .= '<div class="wbpoll-user-profile-data">';
						global $wpdb;
						$votes_name    = WBPollHelper::wb_poll_table_name();
						$escaped_title = '%' . $wpdb->esc_like( $answer_title ) . '%';
						$sql_select    = $wpdb->prepare( "SELECT * FROM $votes_name WHERE `answer_title` LIKE %s AND `poll_id` = %d", $escaped_title, absint( $poll_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$result_data   = $wpdb->get_results( $sql_select, 'ARRAY_A' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

						if ( isset( $result_data ) && ! empty( $result_data ) ) {
							$count   = count( $result_data );
							$results = array_slice( $result_data, 0, 3 );

							foreach ( $results as $res ) {
								$vote_ans = maybe_unserialize( $res['answer_title'] );
								if ( in_array( $answer_title, $vote_ans ) ) {
									if ( $res['user_id'] == 0 ) {
										$default_avatar_url = apply_filters( 'wbpoll_default_avatar', BPOLLS_PLUGIN_URL . 'public/images/default-avatar.svg' );
										$image              = '<img alt="User Avatar" src="' . $default_avatar_url . '" class="avatar avatar-150 photo avatar-default avatar-image" height="150" width="150" loading="lazy" decoding="async">';
										$users              = array( esc_html__( 'Guest User', 'buddypress-polls' ) );
									} else {
										$image = get_avatar( $res['user_id'], 150, '', 'User Avatar', array( 'class' => 'avatar-image' ) );

										$args  = array(
											'include' => $res['user_id'], // ID of users you want to get
											'fields'  => 'display_name',
										);
										$users = get_users( $args );
									}

									$output_result .= '<div class="user-profile">';
									$output_result .= '<div class="user-profile-image" data-polls-tooltip="' . $users[0] . '">' . $image . '</div>';
									$output_result .= '</div>';
								}
							}
							if ( $count > 3 ) {
								// profile modal more button
								$output_result .= '<div class="user-profile-load-more">';
								$output_result .= '<div class="user-profile-image load-more" data-id="' . $index . '">+' . ( $count - 3 ) . '</div>';
								$output_result .= '<div class="wbpoll-user-profile-image-modal user-profile-image-modal-' . $index . ' profile-modal">';
								// profile modal
								$output_result .= '<div class="wbpoll-profile-modal-content">';
								$output_result .= '<div class="wbpoll-profile-modal-header">';
								$output_result .= '<div class="wbpoll-profile-modal-title">';
								$output_result .= '<h4>' . esc_html__( 'Users who voted for this option', 'buddypress-polls' ) . '</h4>';
								$output_result .= '</div>';
								$output_result .= '<div class="close-profiles" data-id="' . $index . '"><svg class="pswp__icn" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M11.53 1.53A.75.75 0 0 0 10.47.47L6 4.94 1.53.47A.75.75 0 1 0 .47 1.53L4.94 6 .47 10.47a.75.75 0 1 0 1.06 1.06L6 7.06l4.47 4.47a.75.75 0 1 0 1.06-1.06L7.06 6l4.47-4.47Z"></path></svg></div>';
								$output_result .= '</div>';
								$output_result .= '<div class="wbpoll-user-profile-details-wrapper">';
								foreach ( $result_data as $result ) {
									$vote_ans = maybe_unserialize( $res['answer_title'] );
									if ( in_array( $answer_title, $vote_ans ) ) {

										if ( $result['user_id'] == 0 ) {
											$default_avatar_url = apply_filters( 'wbpoll_default_avatar', BPOLLS_PLUGIN_URL . 'public/images/default-avatar.svg' );
											$image              = '<img alt="User Avatar" src="' . $default_avatar_url . '" class="avatar avatar-150 photo avatar-default avatar-image" height="150" width="150" loading="lazy" decoding="async">';
											$users              = array( esc_html__( 'Guest User', 'buddypress-polls' ) );
										} else {
											$image = get_avatar( $result['user_id'], 150, '', 'User Avatar', array( 'class' => 'avatar-image' ) );
											$args  = array(
												'include' => $result['user_id'], // ID of users you want to get
												'fields'  => 'display_name',
											);
											$users = get_users( $args );
										}

										$output_result .= '<div class="wbpoll-user-profile-details">';
										if ( ! empty( $image ) && isset( $image ) || ! empty( $users[0] ) && isset( $users[0] ) ) {
											$output_result .= '<div class="user-profile-images">' . $image . '</div>';
											$output_result .= '<div class="user-profile-name">' . $users[0] . '</div>';
										}
										$output_result .= '</div>';
									}
								}
								$output_result .= '</div>'; // wbpoll-user-profile-details-wrapper.
								$output_result .= '</div>';
								$output_result .= '</div>';
								$output_result .= '</div>';
							}
						}
						$output_result .= '</div>'; // wbpoll-user-profile-data.

						if ( $vote_count > 1 ) {
							$output_result .= '<div class="wbpoll-votecount"> ' . $vote_count . ' ' . esc_html__( 'Votes', 'buddypress-polls' ) . '</div>';
						} else {
							$output_result .= '<div class="wbpoll-votecount"> ' . $vote_count . ' ' . esc_html__( 'Vote', 'buddypress-polls' ) . '</div>';
						}

						$output_result .= '</div>'; // wbpoll-vote-percent-data-wrapper.

						$output_result .= '<div class="wbpoll-vote-percent-data" style="' . $color_style . '">' . number_format( $re_percent, 2 ) . '%</div>';

						$output_result .= '</div>'; // wbpoll-vote-percent-data-wrapper.

						$output_result .= '</div>'; // wbpoll-question-choices-item-label.

						$output_result .= '</div>'; // wbpoll-single-answer-label.
						$output_result .= '</div>'; // wbpoll-question-choices-item-container.
						$output_result .= '</div>'; // wbpoll-question-choices-item.
					}
				}

				$output .= $output_result;
				$output .= '</div>';
			} else {
				$output = '<p>' . esc_html__( 'No approved vote yet', 'buddypress-polls' ) . '</p>';
			}

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}//end poll_display_methods_text_result()

		/**
		 * poll back graph for single poll
		 */
		public function poll_display_methods_text_backend_result( $poll_id, $reference = '', $poll_result = '' ) {

			$total         = intval( $poll_result['total'] );
			$answers       = isset( $poll_result['answer'] ) ? $poll_result['answer'] : array();
			$total_percent = 0;
			if ( ! empty( $total ) && $total > 0 ) {

				foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
					$answer_title   = isset( $answers[ $index ] ) ? esc_html__( $answers[ $index ], 'buddypress-polls' ) : esc_html__(
						'Unknown Answer',
						'buddypress-polls'
					);
					$percent        = ( $vote_count * 100 ) / $total;
					$total_percent += $percent;
				}
			}

			if ( $total_percent > 0 ) {
				$lablename        = array();
				$persentangevalue = array();
				foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
					$answer_title = isset( $answers[ $index ] ) ? esc_html__( $answers[ $index ], 'buddypress-polls' ) : esc_html__(
						'Unknown Answer',
						'buddypress-polls'
					);

					$percent            = ( $vote_count * 100 ) / $total;
					$re_percent         = ( $percent * 100 ) / $total_percent;
					$lablename[]        = $answer_title;
					$persentangevalue[] = number_format( $re_percent, 2 );
				}
			}

			/**********chart */
			if ( ! empty( $lablename ) && ! empty( $persentangevalue ) ) {
				$labels = $lablename;
				$values = $persentangevalue;
				$output = self::display_pie_chart( $labels, $values );
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				$labels = array();
				$values = array();
				echo 'No approved vote yet';
			}
		}//end poll_display_methods_text_backend_result()

		public function poll_display_methods_widget_result( $poll_id, $reference = '', $poll_result = '' ) {
			$total         = intval( $poll_result['total'] );
			$answers       = isset( $poll_result['answer'] ) ? $poll_result['answer'] : array();
			$total_percent = 0;
			if ( ! empty( $total ) && $total > 0 ) {

				foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
					$answer_title   = isset( $answers[ $index ] ) ? esc_html( $answers[ $index ] ) : esc_html__(
						'Unknown Answer',
						'buddypress-polls'
					);
					$percent        = ( $vote_count * 100 ) / $total;
					$total_percent += $percent;
				}
			}

			if ( $total_percent > 0 ) {
				$post_title = get_the_title( $poll_id );
				?>
				<h5><?php echo esc_html__( $post_title, 'buddypress-polls' ); ?></h5>
				<?php /* translators: %s: */ ?>
				<p> <?php echo sprintf( esc_html__( 'Total votes: %d', 'buddypress-polls' ), number_format( $total ) ); ?> <p>
				<table>
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Options', 'buddypress-polls' ); ?></th>
							<th><?php esc_attr_e( 'Vote %', 'buddypress-polls' ); ?></th>
							<th><?php esc_attr_e( 'vote', 'buddypress-polls' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $poll_result['weighted_index'] as $index => $vote_count ) {
							$answer_title = isset( $answers[ $index ] ) ? esc_html__( $answers[ $index ], 'buddypress-polls' ) : esc_html__(
								'Unknown Answer',
								'buddypress-polls'
							);

							$percent          = ( $vote_count * 100 ) / $total;
							$re_percent       = ( $percent * 100 ) / $total_percent;
							$lablename        = $answer_title;
							$persentangevalue = number_format( $re_percent, 0 ) . ' %';
							?>
							<tr>
								<td><?php echo esc_html__( $lablename, 'buddypress-polls' ); ?></td>
								<td><?php echo esc_html__( $persentangevalue, 'buddypress-polls' ); ?></td>
								<td><?php echo esc_html__( $vote_count, 'buddypress-polls' ); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<?php
			} else {
				echo esc_attr_e( 'No approved vote yet', 'buddypress-polls' );
			}
		}


		function display_pie_chart( $labels, $values ) {
			// Encode the chart data as JSON.
			$chart_data = array();
			for ( $i = 0; $i < count( $labels ); $i++ ) {
				$chart_data[] = array(
					'label' => $labels[ $i ],
					'value' => $values[ $i ],
				);
			}
			$chart_data_json = json_encode( $chart_data );

			// Generate a unique chart ID.
			$chart_id = 'pie-chart-' . rand( 1, 999 );

			// Output the canvas element for the chart.
			echo '<canvas id="' . esc_attr( $chart_id ) . '"></canvas>';

			// Output the JavaScript code to initialize the chart.
			// phpcs:disable
			echo '<script>
				var ctx = document.getElementById("' . $chart_id . '").getContext("2d");
				var chartData = ' . $chart_data_json . ';
				var chartColors = [];
				for (var i = 0; i < chartData.length; i++) {
					chartColors.push(getRandomColor());
				}
				var chartConfig = {
					type: "pie",
					data: {
						labels: chartData.map(d => d.label),
						datasets: [{
							data: chartData.map(d => d.value),
							backgroundColor: chartColors
						}]
					},
					options: {
						responsive: true
					}
				};
				var chart = new Chart(ctx, chartConfig);
				function getRandomColor() {
					var letters = "0123456789ABCDEF";
					var color = "#";
					for (var i = 0; i < 6; i++) {
						color += letters[Math.floor(Math.random() * 16)];
					}
					return color;
				}
			</script>';
			// phpcs:enable
		}


	}


}
