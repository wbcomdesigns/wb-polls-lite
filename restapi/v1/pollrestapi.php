<?php
/**
 * The helper functionality of the plugin.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    wbpoll
 * @subpackage wbpoll/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Helper functionality of the plugin.
 *
 * lots of micro methods that help get set
 *
 * @package    wbpoll
 * @subpackage wbpoll/includes
 * @author     codeboxr <info@codeboxr.com>
 */

class Pollrestapi {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
	}

	/**
	 * Check if user can create polls.
	 *
	 * @return bool|WP_Error True if user can create polls, WP_Error otherwise.
	 */
	public function can_create_poll() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to create polls.', 'buddypress-polls' ),
				array( 'status' => 401 )
			);
		}

		// Check if user has permission to create polls based on settings.
		$can_create = apply_filters( 'bpolls_user_can_create_poll', true, get_current_user_id() );
		if ( ! $can_create ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to create polls.', 'buddypress-polls' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Check if user can manage a specific poll (pause/delete/publish/unpublish).
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool|WP_Error True if user can manage the poll, WP_Error otherwise.
	 */
	public function can_manage_poll( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to manage polls.', 'buddypress-polls' ),
				array( 'status' => 401 )
			);
		}

		$parameters = $request->get_params();
		$poll_id    = isset( $parameters['pollid'] ) ? absint( $parameters['pollid'] ) : 0;

		if ( ! $poll_id ) {
			return new WP_Error(
				'rest_invalid_poll',
				__( 'Invalid poll ID.', 'buddypress-polls' ),
				array( 'status' => 400 )
			);
		}

		$post = get_post( $poll_id );
		if ( ! $post || 'wbpoll' !== $post->post_type ) {
			return new WP_Error(
				'rest_poll_not_found',
				__( 'Poll not found.', 'buddypress-polls' ),
				array( 'status' => 404 )
			);
		}

		$current_user_id = get_current_user_id();

		// Allow if user is the author or has admin capabilities.
		if ( absint( $post->post_author ) === $current_user_id || current_user_can( 'manage_options' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
			__( 'You do not have permission to manage this poll.', 'buddypress-polls' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Check if user can view polls (logged in user).
	 *
	 * @return bool|WP_Error True if user can view polls, WP_Error otherwise.
	 */
	public function can_view_user_polls() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to view your polls.', 'buddypress-polls' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	/**
	 * Register routes.
	 */
	public function registerRoutes() {

		register_rest_route(
			'wbpoll/v1', '/postpoll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_wbpoll' ),
				'permission_callback' => array( $this, 'can_create_poll' ),
			)
		);

		// wbpoll listall poll - public endpoint for published polls.
		register_rest_route(
			'wbpoll/v1', '/listpoll', array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list_all_poll' ),
				'permission_callback' => '__return_true',
			)
		);

		// wbpoll list poll by poll-id - public for published polls.
		register_rest_route(
			'wbpoll/v1', '/listpoll/id', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_by_id' ),
				'permission_callback' => '__return_true',
			)
		);

		// wbpoll list poll by user - requires login.
		register_rest_route(
			'wbpoll/v1', '/listpoll/user/id', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_by_user' ),
				'permission_callback' => array( $this, 'can_view_user_polls' ),
			)
		);

		// wbpoll list poll pause by user - requires ownership.
		register_rest_route(
			'wbpoll/v1', '/listpoll/pause/poll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_pause_by_user' ),
				'permission_callback' => array( $this, 'can_manage_poll' ),
			)
		);

		// wbpoll list poll delete by user - requires ownership.
		register_rest_route(
			'wbpoll/v1', '/listpoll/delete/poll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_delete_by_user' ),
				'permission_callback' => array( $this, 'can_manage_poll' ),
			)
		);

		// wbpoll list poll unpublish by user - requires ownership.
		register_rest_route(
			'wbpoll/v1', '/listpoll/unpublish/poll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_unpublish_by_user' ),
				'permission_callback' => array( $this, 'can_manage_poll' ),
			)
		);

		// wbpoll list poll publish by user - requires ownership.
		register_rest_route(
			'wbpoll/v1', '/listpoll/publish/poll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_publish_by_user' ),
				'permission_callback' => array( $this, 'can_manage_poll' ),
			)
		);

		// wbpoll list poll result - requires login.
		register_rest_route(
			'wbpoll/v1', '/listpoll/result/poll', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'listpoll_result_by_user' ),
				'permission_callback' => array( $this, 'can_view_user_polls' ),
			)
		);

	}

	// Callback function
	public function create_wbpoll( $request ) {

		$parameters = $request->get_params();
		$prefix     = '_wbpoll_';

		// Security: Always use the current logged-in user as the author.
		// This prevents author spoofing where a user could claim to be someone else.
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to create or update polls.', 'buddypress-polls' ),
				array( 'status' => 401 )
			);
		}

		// Retrieve the post data from the request body
		$post_title   = sanitize_text_field( $parameters['title'] );

		$option_value = get_site_option( 'wbpolls_settings' );

		// Check if simple textarea mode is enabled (plain text, no HTML).
		$use_simple_textarea = isset( $option_value['use_simple_textarea'] ) && 'yes' === $option_value['use_simple_textarea'];

		// Sanitize content based on editor mode.
		if ( $use_simple_textarea ) {
			// Simple textarea mode: Strip ALL HTML tags for plain text only.
			$post_content = wp_strip_all_tags( $parameters['content'] );
		} else {
			// Visual/HTML editor mode: Allow safe HTML tags.
			$post_content = wp_kses_post( $parameters['content'] );
		}

		// Get character limit settings for server-side validation.
		$poll_title_limit       = isset( $option_value['poll_title_limit'] ) && intval( $option_value['poll_title_limit'] ) > 0 ? intval( $option_value['poll_title_limit'] ) : 0;
		$poll_description_limit = isset( $option_value['poll_description_limit'] ) && intval( $option_value['poll_description_limit'] ) > 0 ? intval( $option_value['poll_description_limit'] ) : 0;
		$poll_answer_limit      = isset( $option_value['poll_answer_limit'] ) && intval( $option_value['poll_answer_limit'] ) > 0 ? intval( $option_value['poll_answer_limit'] ) : 0;

		// Enforce character limits on title and description.
		if ( $poll_title_limit > 0 && mb_strlen( $post_title ) > $poll_title_limit ) {
			$post_title = mb_substr( $post_title, 0, $poll_title_limit );
		}
		if ( $poll_description_limit > 0 && mb_strlen( wp_strip_all_tags( $post_content ) ) > $poll_description_limit ) {
			// For HTML content, truncate by stripping tags first, then allow up to limit.
			$stripped = wp_strip_all_tags( $post_content );
			$post_content = mb_substr( $stripped, 0, $poll_description_limit );
		}

		$wbpolls_submit_status = 'pending'; // Default status for new polls.
		if ( ! empty( $option_value ) && isset( $option_value['wbpolls_submit_status'] ) ) {
			$wbpolls_submit_status = sanitize_text_field( $option_value['wbpolls_submit_status'] );
		}

		// Validate poll type against settings.
		$poll_type = isset( $parameters['poll_type'] ) ? sanitize_text_field( $parameters['poll_type'] ) : 'default';
		$allowed_types = array( 'default' ); // Text type is always allowed.

		// Check which poll types are enabled.
		$enable_image_poll = ! isset( $option_value['enable_image_poll'] ) || 'yes' === $option_value['enable_image_poll'];
		$enable_video_poll = ! isset( $option_value['enable_video_poll'] ) || 'yes' === $option_value['enable_video_poll'];
		$enable_audio_poll = ! isset( $option_value['enable_audio_poll'] ) || 'yes' === $option_value['enable_audio_poll'];
		$enable_html_poll  = isset( $option_value['enable_html_poll'] ) && 'yes' === $option_value['enable_html_poll'];

		if ( $enable_image_poll ) {
			$allowed_types[] = 'image';
		}
		if ( $enable_video_poll ) {
			$allowed_types[] = 'video';
		}
		if ( $enable_audio_poll ) {
			$allowed_types[] = 'audio';
		}
		if ( $enable_html_poll ) {
			$allowed_types[] = 'html';
		}

		// Reject disabled poll types.
		if ( ! in_array( $poll_type, $allowed_types, true ) ) {
			return new WP_Error(
				'poll_type_disabled',
				__( 'This poll type is not allowed. Please select a different poll type.', 'buddypress-polls' ),
				array( 'status' => 403 )
			);
		}

		// Check if this is an update (poll_id provided) or a new poll.
		$updatepost_id = isset( $parameters['poll_id'] ) ? absint( $parameters['poll_id'] ) : 0;
		$post          = get_post( $updatepost_id );

		// Update existing poll - requires ownership verification.
		if ( $post ) {
			// Security: Verify the current user owns the poll or is an admin.
			if ( absint( $post->post_author ) !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
				return new WP_Error(
					'rest_forbidden',
					__( 'You do not have permission to update this poll.', 'buddypress-polls' ),
					array( 'status' => 403 )
				);
			}

			// Security: Don't allow changing the author via API (keep original author).
			$updated_post = array(
				'ID'           => $updatepost_id,
				'post_title'   => $post_title,
				'post_content' => $post_content,
				// Keep the original post author - don't allow author changes via API.
			);
			$post_id = wp_update_post( $updated_post, true );

			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'poll_update_failed',
					sprintf( __( 'Failed to update poll: %s', 'buddypress-polls' ), $post_id->get_error_message() ),
					array( 'status' => 500 )
				);
			}
		} else {
			// Create new poll - use current user as author.
			$new_post = array(
				'post_title'   => $post_title,
				'post_content' => $post_content,
				'post_status'  => $wbpolls_submit_status,
				'post_type'    => 'wbpoll',
				'post_author'  => $current_user_id, // Security: Always use logged-in user.
			);
			$post_id = wp_insert_post( $new_post, true );

			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'poll_create_failed',
					sprintf( __( 'Failed to create poll: %s', 'buddypress-polls' ), $post_id->get_error_message() ),
					array( 'status' => 500 )
				);
			}
		}

		 // option type (default, image, video, audio, html)
		if ( isset( $parameters[ $prefix . 'answer_extra' ] ) ) {

				$extra = [];
			foreach ( $parameters[ $prefix . 'answer_extra' ] as $key => $extra_type ) {
				if ( $extra_type == $parameters['poll_type'] ) {
					$extra[]['type'] = $extra_type;
				}
			}
				 update_post_meta( $post_id, $prefix . 'answer_extra', $extra );

		} else {
			delete_post_meta( $post_id, $prefix . 'answer_extra' );
		}

		// Option label - sanitize all answer text values and enforce character limit.
		if ( isset( $parameters[ $prefix . 'answer' ] ) ) {

			$titles = array();
			foreach ( $parameters[ $prefix . 'answer' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$answer_text = sanitize_text_field( $extra_type );
					// Enforce character limit on answer options.
					if ( $poll_answer_limit > 0 && mb_strlen( $answer_text ) > $poll_answer_limit ) {
						$answer_text = mb_substr( $answer_text, 0, $poll_answer_limit );
					}
					$titles[] = $answer_text;
				}
			}
			update_post_meta( $post_id, $prefix . 'answer', $titles );

		} else {
			delete_post_meta( $post_id, $prefix . 'answer' );
		}

		// Full size image answer - sanitize URLs properly.
		if ( isset( $parameters[ $prefix . 'full_size_image_answer' ] ) ) {

			$images = array();
			foreach ( $parameters[ $prefix . 'full_size_image_answer' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$images[] = esc_url_raw( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'full_size_image_answer', $images );

		} else {
			delete_post_meta( $post_id, $prefix . 'full_size_image_answer' );
		}

		// Video URL - sanitize all URL values.
		if ( isset( $parameters[ $prefix . 'video_answer_url' ] ) ) {

			$videos = array();
			foreach ( $parameters[ $prefix . 'video_answer_url' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$videos[] = esc_url_raw( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'video_answer_url', $videos );

		} else {
			delete_post_meta( $post_id, $prefix . 'video_answer_url' );
		}

		// Video suggestion - sanitize text values.
		if ( isset( $parameters[ $prefix . 'video_import_info' ] ) ) {

			$suggestion = array();
			foreach ( $parameters[ $prefix . 'video_import_info' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$suggestion[] = sanitize_text_field( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'video_import_info', $suggestion );

		} else {
			delete_post_meta( $post_id, $prefix . 'video_import_info' );
		}

		// Audio URL - sanitize all URL values.
		if ( isset( $parameters[ $prefix . 'audio_answer_url' ] ) ) {

			$audios = array();
			foreach ( $parameters[ $prefix . 'audio_answer_url' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$audios[] = esc_url_raw( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'audio_answer_url', $audios );

		} else {
			delete_post_meta( $post_id, $prefix . 'audio_answer_url' );
		}

		// Audio suggestion - sanitize text values.
		if ( isset( $parameters[ $prefix . 'audio_import_info' ] ) ) {
			$suggestion = array();
			foreach ( $parameters[ $prefix . 'audio_import_info' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$suggestion[] = sanitize_text_field( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'audio_import_info', $suggestion );

		} else {
			delete_post_meta( $post_id, $prefix . 'audio_import_info' );
		}

		// HTML content - sanitize with wp_kses_post to allow safe HTML.
		if ( isset( $parameters[ $prefix . 'html_answer' ] ) ) {
			$htmls = array();
			foreach ( $parameters[ $prefix . 'html_answer' ] as $extra_type ) {
				if ( ! empty( $extra_type ) ) {
					$htmls[] = wp_kses_post( $extra_type );
				}
			}

			update_post_meta( $post_id, $prefix . 'html_answer', $htmls );

		} else {
			delete_post_meta( $post_id, $prefix . 'html_answer' );
		}

		// Start date meta
		if ( isset( $parameters[ $prefix . 'start_date' ] ) ) {
			$start_date = $parameters[ $prefix . 'start_date' ];
			update_post_meta( $post_id, $prefix . 'start_date', $start_date );
		} else {
			delete_post_meta( $post_id, $prefix . 'start_date' );
		}

		// poll type
		if ( isset( $parameters['poll_type'] ) ) {
			$poll_type = $parameters['poll_type'];
			update_post_meta( $post_id, 'poll_type', $poll_type );
		} else {
			delete_post_meta( $post_id, 'poll_type' );
		}
		// End date meta
		if ( isset( $parameters[ $prefix . 'end_date' ] ) ) {
			$end_date = $parameters[ $prefix . 'end_date' ];
			update_post_meta( $post_id, $prefix . 'end_date', $end_date );
		} else {
			delete_post_meta( $post_id, $prefix . 'end_date' );
		}

		// Who can vote meta
		if ( isset( $parameters[ $prefix . 'user_roles' ] ) ) {
			$user_roles = $parameters[ $prefix . 'user_roles' ];
			update_post_meta( $post_id, $prefix . 'user_roles', $user_roles );
		} else {
			delete_post_meta( $post_id, $prefix . 'user_roles' );
		}

		// show description meta (toggle for showing description on poll)
		if ( isset( $parameters[ $prefix . 'content' ] ) ) {
			$content = sanitize_text_field( $parameters[ $prefix . 'content' ] );
			update_post_meta( $post_id, $prefix . 'content', $content );
		} else {
			delete_post_meta( $post_id, $prefix . 'content' );
		}

		// never expire meta
		if ( isset( $parameters[ $prefix . 'never_expire' ] ) ) {
			$never_expire = $parameters[ $prefix . 'never_expire' ];
			update_post_meta( $post_id, $prefix . 'never_expire', $never_expire );
		} else {
			delete_post_meta( $post_id, $prefix . 'never_expire' );
		}

		// show result after Expire meta
		if ( isset( $parameters[ $prefix . 'show_result_before_expire' ] ) ) {
			$show_result_before_expire = $parameters[ $prefix . 'show_result_before_expire' ];
			update_post_meta( $post_id, $prefix . 'show_result_before_expire', $show_result_before_expire );
		} else {
			delete_post_meta( $post_id, $prefix . 'show_result_before_expire' );
		}

		// multivote meta
		if ( isset( $parameters[ $prefix . 'multivote' ] ) ) {
			$multivote = $parameters[ $prefix . 'multivote' ];
			update_post_meta( $post_id, $prefix . 'multivote', $multivote );
		} else {
			delete_post_meta( $post_id, $prefix . 'multivote' );
		}

		 // add additional fields meta
		if ( isset( $parameters[ $prefix . 'add_additional_fields' ] ) ) {
			$multivote = $parameters[ $prefix . 'add_additional_fields' ];
			update_post_meta( $post_id, $prefix . 'add_additional_fields', $multivote );
		} else {
			delete_post_meta( $post_id, $prefix . 'add_additional_fields' );
		}
		// vote per session meta
		if ( isset( $parameters[ $prefix . 'vote_per_session' ] ) ) {
			$vote_per_session = $parameters[ $prefix . 'vote_per_session' ];
			update_post_meta( $post_id, $prefix . 'vote_per_session', $vote_per_session );
		} else {
			delete_post_meta( $post_id, $prefix . 'vote_per_session' );
		}

		if ( empty( trim( $updatepost_id ) ) || trim( $updatepost_id ) == '' ) {

			$type = $wbpolls_submit_status;

			// Return the response data
			if ( $type == 'publish' ) {
				$data = array(
					'success' => true,
					'message' => esc_html__( 'Your poll is published.', 'buddypress-polls' ),
					'post_id' => $post_id,
					'url'     => get_permalink( $post_id ),
				);
			} else {
				$option_value        = get_site_option( 'wbpolls_settings' );
				$poll_dashboard_page = isset( $option_value['poll_dashboard_page'] ) ? absint( $option_value['poll_dashboard_page'] ) : 0;

				$dashboard_url = $poll_dashboard_page ? get_permalink( $poll_dashboard_page ) : home_url();
				$data          = array(
					'success' => true,
					/* translators: %s: poll status (e.g., pending, draft) */
					'message' => sprintf( esc_html__( 'Your poll is in %s. It will be published after admin review.', 'buddypress-polls' ), esc_html( $type ) ),
					'post_id' => $post_id,
					'url'     => $dashboard_url,
				);
			}
		} else {

			$option_value        = get_site_option( 'wbpolls_settings' );
			$poll_dashboard_page = isset( $option_value['poll_dashboard_page'] ) ? absint( $option_value['poll_dashboard_page'] ) : 0;

			$dashboard_url = $poll_dashboard_page ? get_permalink( $poll_dashboard_page ) : home_url();
			$data          = array(
				'success' => true,
				'message' => esc_html__( 'Your poll update successfully', 'buddypress-polls' ),
				'post_id' => $post_id,
				'url'     => $dashboard_url,
			);

		}

		return rest_ensure_response( $data );
	}

	// Callback function
	public function list_all_poll( $request ) {

		$args  = array(
			'post_type'      => 'wbpoll',
			'post_status'    => 'publish', // Security: Only return published polls to public.
			'posts_per_page' => 100,
			'no_found_rows'  => true, // Performance: Skip pagination count when not needed.
		);
		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		// Prime the meta cache for all polls to avoid N+1 queries.
		if ( ! empty( $posts ) ) {
			$poll_ids = wp_list_pluck( $posts, 'ID' );
			update_meta_cache( 'post', $poll_ids );
		}

		// Format the response data
		$data = array();
		foreach ( $posts as $post ) {

			$post_id = $post->ID;

			$meta_value_ans = get_post_meta( $post_id, '_wbpoll_answer', true );
			$meta_value_ans = is_array( $meta_value_ans ) ? $meta_value_ans : array();

			$image_answer_url = get_post_meta( $post_id, '_wbpoll_full_size_image_answer', true );
			$image_answer_url = is_array( $image_answer_url ) ? $image_answer_url : array();

			$video_answer_url = get_post_meta( $post_id, '_wbpoll_video_answer_url', true );
			$video_answer_url = is_array( $video_answer_url ) ? $video_answer_url : array();

			$audio_answer_url = get_post_meta( $post_id, '_wbpoll_audio_answer_url', true );
			$audio_answer_url = is_array( $audio_answer_url ) ? $audio_answer_url : array();

			$html_content = get_post_meta( $post_id, '_wbpoll_html_answer', true );
			$html_content = is_array( $html_content ) ? $html_content : array();

			$options_data = [];
			foreach ( $meta_value_ans as $key => $meta_value ) {

				$options_data[ $key ]['label'] = $meta_value;

				if ( isset( $image_answer_url[ $key ] ) && ! empty( $image_answer_url[ $key ] ) ) {
					$options_data[ $key ]['image'] = $image_answer_url[ $key ];
				}

				if ( isset( $video_answer_url[ $key ] ) && ! empty( $video_answer_url[ $key ] ) ) {
					$options_data[ $key ]['video'] = $video_answer_url[ $key ];
				}

				if ( isset( $audio_answer_url[ $key ] ) && ! empty( $audio_answer_url[ $key ] ) ) {
					$options_data[ $key ]['audio'] = $audio_answer_url[ $key ];
				}

				if ( isset( $html_content[ $key ] ) && ! empty( $html_content[ $key ] ) ) {
					$options_data[ $key ]['html'] = $html_content[ $key ];
				}
			}
			$data[] = array(

				'id'                       => $post->ID,
				'title'                    => $post->post_title,
				'content'                  => $post->post_content,
				'date'                     => $post->post_date,
				'options'                  => $options_data,
				'start_time'               => get_post_meta( $post_id, '_wbpoll_start_date', true ),
				'end_date'                 => get_post_meta( $post_id, '_wbpoll_end_date', true ),
				'user_role'                => get_post_meta( $post_id, '_wbpoll_user_roles', true ),
				'show_description'         => get_post_meta( $post_id, '_wbpoll_content', true ),
				'never_expire'             => get_post_meta( $post_id, '_wbpoll_never_expire', true ),
				'show_result_after_expire' => get_post_meta( $post_id, '_wbpoll_show_result_before_expire', true ),
				'multivote'                => get_post_meta( $post_id, '_wbpoll_multivote', true ),
				'add_additional_fields'    => get_post_meta( $post_id, '_wbpoll_add_additional_fields', true ),
				'vote_per_session'         => get_post_meta( $post_id, '_wbpoll_vote_per_session', true ),
				'result'                   => WBPollHelper::show_backend_single_poll_result( $post_id, 'shortcode', 'text' ),
			);
		}

		// Return the response data
		return rest_ensure_response( $data );
	}

	public function listpoll_by_id( $request ) {

		// Accept both 'pollid' (from JS) and 'id' for backwards compatibility
		$post_id = isset( $request['pollid'] ) ? absint( $request['pollid'] ) : ( isset( $request['id'] ) ? absint( $request['id'] ) : 0 );
		$post    = get_post( $post_id );

		// If post not found, return a 404 error
		if ( empty( $post ) || is_wp_error( $post ) || 'wbpoll' !== $post->post_type ) {
			return new WP_Error( '404', 'Post not found', array( 'status' => 404 ) );
		}

		// Security: Only return published polls to public, or own polls to authors.
		$current_user_id = get_current_user_id();
		$is_author       = $current_user_id && absint( $post->post_author ) === $current_user_id;
		$is_admin        = current_user_can( 'manage_options' );

		if ( 'publish' !== $post->post_status && ! $is_author && ! $is_admin ) {
			return new WP_Error( '404', 'Post not found', array( 'status' => 404 ) );
		}

		// Format the response data
		$data = array(
			'id'             => $post->ID,
			'title'          => $post->post_title,
			'content'        => $post->post_content,
			'date'           => $post->post_date,
			'featured_image' => get_the_post_thumbnail( $post_id, 'large' ),
		);

		$meta_value_ans = get_post_meta( $post_id, '_wbpoll_answer', true );
		$meta_value_ans = is_array( $meta_value_ans ) ? $meta_value_ans : array();

		$image_answer_url = get_post_meta( $post_id, '_wbpoll_full_size_image_answer', true );
		$image_answer_url = is_array( $image_answer_url ) ? $image_answer_url : array();

		$video_answer_url = get_post_meta( $post_id, '_wbpoll_video_answer_url', true );
		$video_answer_url = is_array( $video_answer_url ) ? $video_answer_url : array();

		$audio_answer_url = get_post_meta( $post_id, '_wbpoll_audio_answer_url', true );
		$audio_answer_url = is_array( $audio_answer_url ) ? $audio_answer_url : array();

		$html_content = get_post_meta( $post_id, '_wbpoll_html_answer', true );
		$html_content = is_array( $html_content ) ? $html_content : array();

		$options_data = [];
		foreach ( $meta_value_ans as $key => $meta_value ) {

			$options_data[ $key ]['label'] = $meta_value;
			if ( isset( $image_answer_url[ $key ] ) && ! empty( $image_answer_url[ $key ] ) ) {
				$options_data[ $key ]['image'] = $image_answer_url[ $key ];
			}

			if ( isset( $video_answer_url[ $key ] ) && ! empty( $video_answer_url[ $key ] ) ) {
				$options_data[ $key ]['video'] = $video_answer_url[ $key ];
			}

			if ( isset( $audio_answer_url[ $key ] ) && ! empty( $audio_answer_url[ $key ] ) ) {
				$options_data[ $key ]['audio'] = $audio_answer_url[ $key ];
			}

			if ( isset( $html_content[ $key ] ) && ! empty( $html_content[ $key ] ) ) {
				$options_data[ $key ]['html'] = $html_content[ $key ];
			}
		}
		$data['options']                  = $options_data;
		$data['start_time']               = get_post_meta( $post_id, '_wbpoll_start_date', true );
		$data['end_date']                 = get_post_meta( $post_id, '_wbpoll_end_date', true );
		$data['user_role']                = get_post_meta( $post_id, '_wbpoll_user_roles', true );
		$data['show_description']         = get_post_meta( $post_id, '_wbpoll_content', true );
		$data['never_expire']             = get_post_meta( $post_id, '_wbpoll_never_expire', true );
		$data['show_result_after_expire'] = get_post_meta( $post_id, '_wbpoll_show_result_before_expire', true );
		$data['multivote']                = get_post_meta( $post_id, '_wbpoll_multivote', true );
		$data['add_additional_fields']    = get_post_meta( $post_id, '_wbpoll_add_additional_fields', true );
		$data['vote_per_session']         = get_post_meta( $post_id, '_wbpoll_vote_per_session', true );
		$data['result']                   = WBPollHelper::show_backend_single_poll_result( $post_id, 'shortcode', 'text' );

		// Return the response data
		return rest_ensure_response( $data );
	}

	// Callback function
	public function listpoll_by_user( $request ) {

		$author_id       = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
		$current_user_id = get_current_user_id();

		// Security: Users can only view their own polls, unless admin.
		if ( $author_id !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You can only view your own polls.', 'buddypress-polls' ),
				array( 'status' => 403 )
			);
		}

		$status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'any';
		$args   = array(
			'author'         => $author_id,
			'post_type'      => 'wbpoll',
			'posts_per_page' => 100,
			'no_found_rows'  => true, // Performance: Skip pagination count when not needed.
			'post_status'    => $status,
		);

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		// If no posts found, return empty array (not error, for better UX).
		if ( empty( $posts ) ) {
			return rest_ensure_response( array() );
		}

		// Prime the meta cache for all polls to avoid N+1 queries.
		$poll_ids = wp_list_pluck( $posts, 'ID' );
		update_meta_cache( 'post', $poll_ids );

		// Batch fetch all vote counts in a single query.
		$vote_counts = WBPollHelper::getBatchVoteCounts( $poll_ids );

		// Format the response data
		$data = array();
		foreach ( $posts as $post ) {
			$post_id = $post->ID;

			$data[] = array(
				'id'           => $post->ID,
				'title'        => $post->post_title,
				'slug'         => $post->post_name,
				'content'      => $post->post_content,
				'date'         => $post->post_date,
				'status'       => $post->post_status,
				'start_time'   => get_post_meta( $post_id, '_wbpoll_start_date', true ),
				'end_date'     => get_post_meta( $post_id, '_wbpoll_end_date', true ),
				'never_expire' => get_post_meta( $post_id, '_wbpoll_never_expire', true ),
				'totalvote'    => isset( $vote_counts[ $post_id ] ) ? $vote_counts[ $post_id ] : 0,
				'pausetype'    => get_post_meta( $post_id, '_wbpoll_pause_poll', true ),
			);

		}

		// Return the response data
		return rest_ensure_response( $data );

	}

	public function listpoll_pause_by_user( $request ) {
		$parameters = $request->get_params();
		$prefix     = '_wbpoll_';
		// Retrieve the post data from the request body
		$pollid = sanitize_text_field( $parameters['pollid'] );

		// Who can vote meta
		if ( isset( $parameters[ $prefix . 'pause_poll' ] ) ) {
			$pause_poll = $parameters[ $prefix . 'pause_poll' ];
			update_post_meta( $pollid, $prefix . 'pause_poll', $pause_poll );
		} else {
			delete_post_meta( $pollid, $prefix . 'pause_poll' );
		}

		$data = array(
			'success' => true,
			'message' => esc_html__( 'Poll pause successfully.', 'buddypress-polls' ),
			'post_id' => $pollid,
		);
		return rest_ensure_response( $data );
	}


	public function listpoll_delete_by_user( $request ) {
		$parameters = $request->get_params();
		// Retrieve the post data from the request body
		$pollid = absint( $parameters['pollid'] );

		// Delete the post
		$result = wp_delete_post( $pollid, true );

		if ( false === $result || null === $result ) {
			return new WP_Error(
				'poll_delete_failed',
				__( 'Failed to delete poll. The poll may not exist.', 'buddypress-polls' ),
				array( 'status' => 400 )
			);
		}

		$data = array(
			'success' => true,
			'message' => esc_html__( 'Poll deleted successfully!', 'buddypress-polls' ),
			'post_id' => $pollid,
		);
		return rest_ensure_response( $data );
	}

	public function listpoll_unpublish_by_user( $request ) {
		$parameters = $request->get_params();
		// Retrieve the post data from the request body
		$pollid = absint( $parameters['pollid'] );

		// Get the current post object
		$post = get_post( $pollid );

		// Check if the post is a poll and its status is 'publish'
		if ( $post && $post->post_type === 'wbpoll' && $post->post_status === 'publish' ) {
			// Set the new post status to 'draft'
			$updated_post = array(
				'ID'          => $pollid,
				'post_status' => 'draft',
			);

			// Update the post status
			$result = wp_update_post( $updated_post, true );

			if ( is_wp_error( $result ) ) {
				return new WP_Error(
					'poll_unpublish_failed',
					sprintf( __( 'Failed to unpublish poll: %s', 'buddypress-polls' ), $result->get_error_message() ),
					array( 'status' => 500 )
				);
			}
		}

		$data = array(
			'success' => true,
			'message' => esc_html__( 'Poll unpublished successfully!', 'buddypress-polls' ),
			'post_id' => $pollid,
		);
		return rest_ensure_response( $data );
	}

	public function listpoll_publish_by_user( $request ) {
		$parameters = $request->get_params();
		// Retrieve the post data from the request body
		$pollid = absint( $parameters['pollid'] );

		// Get the current post object
		$post = get_post( $pollid );

		// Check if the post is a poll and its status is 'draft'
		if ( $post && $post->post_type === 'wbpoll' && $post->post_status === 'draft' ) {
			// Set the new post status to 'publish'
			$updated_post = array(
				'ID'          => $pollid,
				'post_status' => 'publish',
			);

			// Update the post status
			$result = wp_update_post( $updated_post, true );

			if ( is_wp_error( $result ) ) {
				return new WP_Error(
					'poll_publish_failed',
					sprintf( __( 'Failed to publish poll: %s', 'buddypress-polls' ), $result->get_error_message() ),
					array( 'status' => 500 )
				);
			}
		}

		$data = array(
			'success' => true,
			'message' => esc_html__( 'Poll published successfully!', 'buddypress-polls' ),
			'post_id' => $pollid,
		);
		return rest_ensure_response( $data );
	}

	public function listpoll_result_by_user( $request ) {
		$parameters      = $request->get_params();
		$pollid          = isset( $parameters['pollid'] ) ? absint( $parameters['pollid'] ) : 0;
		$current_user_id = get_current_user_id();

		// Validate poll exists.
		$post = get_post( $pollid );
		if ( ! $post || 'wbpoll' !== $post->post_type ) {
			return new WP_Error(
				'poll_not_found',
				__( 'Poll not found.', 'buddypress-polls' ),
				array( 'status' => 404 )
			);
		}

		// Security: Only poll author or admin can view detailed results.
		if ( absint( $post->post_author ) !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You can only view results for your own polls.', 'buddypress-polls' ),
				array( 'status' => 403 )
			);
		}

		$result = WBPollHelper::show_backend_single_poll_widget_result( $pollid, 'shortcode', 'text' );
		$data   = array(
			'success' => true,
			'message' => esc_html__( 'Poll report', 'buddypress-polls' ),
			'result'  => $result,
		);
		return rest_ensure_response( $data );
	}

}
$custom_endpoint = new Pollrestapi();

