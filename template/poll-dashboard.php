<?php
/**
 * The unified poll dashboard page with create/edit functionality.
 *
 * Modern card-based design with slide-in form panel.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      4.5.0
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $current_user;
$temp_post = $post;

// Get poll creation settings using centralized helper.
$creation_settings   = WBPollHelper::get_poll_creation_settings();
$enabled_types       = $creation_settings['enabled_types'];
$can_create_poll     = WBPollHelper::current_user_can_create_poll();

// Extract enabled types for template use.
$enable_image_poll      = $enabled_types['image'];
$enable_video_poll      = $enabled_types['video'];
$enable_audio_poll      = $enabled_types['audio'];
$enable_html_poll       = $enabled_types['html'];
$disable_html_editor    = $creation_settings['disable_html_editor'];
$use_simple_textarea    = $creation_settings['use_simple_textarea'];
$poll_title_limit       = $creation_settings['poll_title_limit'];
$poll_description_limit = $creation_settings['poll_description_limit'];
$poll_answer_limit      = $creation_settings['poll_answer_limit'];
$option_value 			= get_site_option( 'wbpolls_settings' );

// Check if editing a poll.
$is_editing  = false;
$edit_error  = false;
$post_id     = 0;
$is_creating = isset( $_GET['create'] );

if ( ! empty( $_GET['poll_id'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_poll_' . absint( $_GET['poll_id'] ) ) ) {
		$edit_error = true;
	} else {
		$is_editing = true;
		$post_id    = absint( $_GET['poll_id'] );
		$post       = get_post( $post_id );

		// Validate post exists and is a poll.
		if ( ! $post || 'wbpoll' !== $post->post_type ) {
			$edit_error = true;
			$is_editing = false;
		}

		// Verify ownership - only author or admin can edit.
		if ( $post && (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			$edit_error = true;
			$is_editing = false;
		}

		// Only load poll meta if validation passed.
		if ( $is_editing ) {
			// Get poll meta.
			$poll_type                = get_post_meta( $post_id, 'poll_type', true );
			$start_time               = get_post_meta( $post_id, '_wbpoll_start_date', true );
			$end_date                 = get_post_meta( $post_id, '_wbpoll_end_date', true );
			$never_expire             = get_post_meta( $post_id, '_wbpoll_never_expire', true );
			$show_result_after_expire = get_post_meta( $post_id, '_wbpoll_show_result_before_expire', true );
			$multivote                = get_post_meta( $post_id, '_wbpoll_multivote', true );
			$add_additional_fields    = get_post_meta( $post_id, '_wbpoll_add_additional_fields', true );

			$answers           = get_post_meta( $post_id, '_wbpoll_answer', true );
			$image_answer_url  = get_post_meta( $post_id, '_wbpoll_full_size_image_answer', true );
			$video_answer_url  = get_post_meta( $post_id, '_wbpoll_video_answer_url', true );
			$audio_answer_url  = get_post_meta( $post_id, '_wbpoll_audio_answer_url', true );
			$html_content      = get_post_meta( $post_id, '_wbpoll_html_answer', true );
			$video_import_info = get_post_meta( $post_id, '_wbpoll_video_import_info', true );
			$audio_import_info = get_post_meta( $post_id, '_wbpoll_audio_import_info', true );

			$options = array();
			if ( 'default' === $poll_type && is_array( $answers ) ) {
				foreach ( $answers as $key => $ans ) {
					$options[ $key ] = $ans;
				}
			} elseif ( 'image' === $poll_type && is_array( $answers ) ) {
				foreach ( $answers as $key => $ans ) {
					$options[ $key ] = array(
						'ans'   => $ans,
						'image' => isset( $image_answer_url[ $key ] ) ? $image_answer_url[ $key ] : '',
					);
				}
			} elseif ( 'video' === $poll_type && is_array( $answers ) ) {
				foreach ( $answers as $key => $ans ) {
					$options[ $key ] = array(
						'ans'        => $ans,
						'video'      => isset( $video_answer_url[ $key ] ) ? $video_answer_url[ $key ] : '',
						'suggestion' => isset( $video_import_info[ $key ] ) ? $video_import_info[ $key ] : 'no',
					);
				}
			} elseif ( 'audio' === $poll_type && is_array( $answers ) ) {
				foreach ( $answers as $key => $ans ) {
					$options[ $key ] = array(
						'ans'        => $ans,
						'audio'      => isset( $audio_answer_url[ $key ] ) ? $audio_answer_url[ $key ] : '',
						'suggestion' => isset( $audio_import_info[ $key ] ) ? $audio_import_info[ $key ] : 'no',
					);
				}
			} elseif ( 'html' === $poll_type && is_array( $answers ) ) {
				foreach ( $answers as $key => $ans ) {
					$options[ $key ] = array(
						'ans'  => $ans,
						'html' => isset( $html_content[ $key ] ) ? $html_content[ $key ] : '',
					);
				}
			}
		}
	}
}

// Set defaults for new polls.
if ( ! $is_editing ) {
	$options                  = array();
	$poll_type                = 'default';
	$never_expire             = '1';
	$show_result_after_expire = '0';
	$multivote                = '0';
	$add_additional_fields    = '0';
	$start_time               = '';
	$end_date                 = '';
}

// Get poll counts for stats.
$userid = get_current_user_id();

// Admins can see all polls, regular users see only their own.
$is_admin_user = current_user_can( 'manage_options' );

$counts = array(
	'publish' => 0,
	'pending' => 0,
	'draft'   => 0,
);

if ( $is_admin_user ) {
	// For admins, use wp_count_posts() which is cached and efficient.
	$post_counts = wp_count_posts( 'wbpoll' );
	$counts['publish'] = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
	$counts['pending'] = isset( $post_counts->pending ) ? (int) $post_counts->pending : 0;
	$counts['draft']   = isset( $post_counts->draft ) ? (int) $post_counts->draft : 0;
} else {
	// For regular users, use a single optimized query to count their polls by status.
	global $wpdb;
	$user_counts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_status, COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'wbpoll' AND post_author = %d AND post_status IN ('publish', 'pending', 'draft') GROUP BY post_status",
			$userid
		)
	);
	if ( $user_counts ) {
		foreach ( $user_counts as $row ) {
			if ( isset( $counts[ $row->post_status ] ) ) {
				$counts[ $row->post_status ] = (int) $row->count;
			}
		}
	}
}
$total_polls = array_sum( $counts );
?>

<div class="polls-dashboard">

<?php if ( ! is_user_logged_in() ) : ?>
	<div class="polls-alert polls-alert--warning">
		<span class="polls-alert__icon">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
		</span>
		<span class="polls-alert__text"><?php esc_html_e( 'Please log in to view your polls.', 'buddypress-polls' ); ?></span>
	</div>
<?php else : ?>

	<?php if ( $edit_error ) : ?>
		<div class="polls-alert polls-alert--error">
			<span class="polls-alert__icon">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
			</span>
			<span class="polls-alert__text"><?php esc_html_e( 'You are not authorized to edit this poll.', 'buddypress-polls' ); ?></span>
		</div>
	<?php endif; ?>

	<!-- Dashboard Header -->
	<header class="polls-header">
		<div class="polls-header__title">
			<h1><?php echo $is_admin_user ? esc_html__( 'All Polls', 'buddypress-polls' ) : esc_html__( 'My Polls', 'buddypress-polls' ); ?></h1>
		</div>
		<?php if ( $can_create_poll ) : ?>
		<div class="polls-header__actions">
			<button type="button" class="polls-btn polls-btn--primary" id="polls-create-btn" aria-haspopup="dialog">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v10M3 8h10"/></svg>
				<span><?php esc_html_e( 'Create Poll', 'buddypress-polls' ); ?></span>
			</button>
		</div>
		<?php endif; ?>
	</header>

	<!-- Stats Bar -->
	<div class="polls-stats">
		<div class="polls-stat" data-status="all">
			<span class="polls-stat__count"><?php echo esc_html( $total_polls ); ?></span>
			<span class="polls-stat__label"><?php esc_html_e( 'Total', 'buddypress-polls' ); ?></span>
		</div>
		<div class="polls-stat polls-stat--success" data-status="publish">
			<span class="polls-stat__count"><?php echo esc_html( $counts['publish'] ); ?></span>
			<span class="polls-stat__label"><?php esc_html_e( 'Published', 'buddypress-polls' ); ?></span>
		</div>
		<div class="polls-stat polls-stat--warning" data-status="pending">
			<span class="polls-stat__count"><?php echo esc_html( $counts['pending'] ); ?></span>
			<span class="polls-stat__label"><?php esc_html_e( 'Pending', 'buddypress-polls' ); ?></span>
		</div>
		<div class="polls-stat polls-stat--muted" data-status="draft">
			<span class="polls-stat__count"><?php echo esc_html( $counts['draft'] ); ?></span>
			<span class="polls-stat__label"><?php esc_html_e( 'Drafts', 'buddypress-polls' ); ?></span>
		</div>
	</div>

	<?php
	// Get current filter and page from URL.
	$current_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';

	// Get current page - check multiple sources for compatibility.
	// Priority: query string > WordPress query vars.
	$current_page = 1;
	if ( isset( $_GET['paged'] ) ) {
		$current_page = absint( $_GET['paged'] );
	} elseif ( get_query_var( 'paged' ) ) {
		$current_page = absint( get_query_var( 'paged' ) );
	} elseif ( get_query_var( 'page' ) ) {
		// For static pages, WordPress uses 'page' instead of 'paged'.
		$current_page = absint( get_query_var( 'page' ) );
	}
	$current_page = max( 1, $current_page );

	// Get base URL and clean up any existing pagination from the URL.
	$base_url = get_permalink();
	// Remove /page/N/ from URL if present (WordPress static page pagination).
	$base_url = preg_replace( '/\/page\/\d+\/?/', '/', $base_url );
	// Remove trailing slashes for consistency, then add one back.
	$base_url = trailingslashit( untrailingslashit( $base_url ) );

	// Validate status.
	$valid_statuses = array( 'all', 'publish', 'pending', 'draft' );
	if ( ! in_array( $current_status, $valid_statuses, true ) ) {
		$current_status = 'all';
	}
	?>

	<!-- Filter Tabs -->
	<nav class="polls-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter polls by status', 'buddypress-polls' ); ?>">
		<a href="<?php echo esc_url( add_query_arg( array( 'status' => 'all', 'paged' => 1 ), $base_url ) ); ?>" class="polls-tab <?php echo 'all' === $current_status ? 'polls-tab--active' : ''; ?>" role="tab" aria-selected="<?php echo 'all' === $current_status ? 'true' : 'false'; ?>" id="polls-tab-all" data-status="all">
			<?php esc_html_e( 'All', 'buddypress-polls' ); ?>
			<span class="polls-tab__count"><?php echo esc_html( $total_polls ); ?></span>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'status' => 'publish', 'paged' => 1 ), $base_url ) ); ?>" class="polls-tab <?php echo 'publish' === $current_status ? 'polls-tab--active' : ''; ?>" role="tab" aria-selected="<?php echo 'publish' === $current_status ? 'true' : 'false'; ?>" id="polls-tab-publish" data-status="publish">
			<?php esc_html_e( 'Published', 'buddypress-polls' ); ?>
			<span class="polls-tab__count"><?php echo esc_html( $counts['publish'] ); ?></span>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'status' => 'pending', 'paged' => 1 ), $base_url ) ); ?>" class="polls-tab <?php echo 'pending' === $current_status ? 'polls-tab--active' : ''; ?>" role="tab" aria-selected="<?php echo 'pending' === $current_status ? 'true' : 'false'; ?>" id="polls-tab-pending" data-status="pending">
			<?php esc_html_e( 'Pending', 'buddypress-polls' ); ?>
			<span class="polls-tab__count"><?php echo esc_html( $counts['pending'] ); ?></span>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'status' => 'draft', 'paged' => 1 ), $base_url ) ); ?>" class="polls-tab <?php echo 'draft' === $current_status ? 'polls-tab--active' : ''; ?>" role="tab" aria-selected="<?php echo 'draft' === $current_status ? 'true' : 'false'; ?>" id="polls-tab-draft" data-status="draft">
			<?php esc_html_e( 'Drafts', 'buddypress-polls' ); ?>
			<span class="polls-tab__count"><?php echo esc_html( $counts['draft'] ); ?></span>
		</a>
	</nav>

	<!-- Poll Cards Grid -->
	<div class="polls-grid" id="polls-grid">
		<?php
		// Pagination settings.
		$polls_per_page = apply_filters( 'buddypress_polls_dashboard_per_page', 12 );

		// Build query args.
		$args = array(
			'post_type'      => 'wbpoll',
			'posts_per_page' => $polls_per_page,
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		// Set post status based on filter.
		if ( 'all' === $current_status ) {
			$args['post_status'] = array( 'publish', 'pending', 'draft' );
		} else {
			$args['post_status'] = $current_status;
		}

		// Only filter by author for non-admin users.
		if ( ! $is_admin_user ) {
			$args['author'] = $userid;
		}

		$query = new WP_Query( $args );
		$polls = $query->get_posts();

		// Prime the meta cache for all polls to avoid N+1 queries.
		$poll_ids = array();
		if ( ! empty( $polls ) ) {
			$poll_ids = wp_list_pluck( $polls, 'ID' );
			update_meta_cache( 'post', $poll_ids );
		}

		// Batch fetch all vote counts in a single query.
		$vote_counts = ! empty( $poll_ids ) ? WBPollHelper::getBatchVoteCounts( $poll_ids ) : array();

		foreach ( $polls as $poll_post ) :
				$poll_id      = $poll_post->ID;
				$poll_title   = $poll_post->post_title;
				$poll_name    = $poll_post->post_name;
				$poll_status  = $poll_post->post_status;
				$poll_date    = $poll_post->post_date;
				$totalvote    = isset( $vote_counts[ $poll_id ] ) ? $vote_counts[ $poll_id ] : 0;
				$pause        = get_post_meta( $poll_id, '_wbpoll_pause_poll', true );
				$p_start_date = get_post_meta( $poll_id, '_wbpoll_start_date', true );
				$p_end_date   = get_post_meta( $poll_id, '_wbpoll_end_date', true );
				$p_never_exp  = get_post_meta( $poll_id, '_wbpoll_never_expire', true );

				// Determine time status.
				$time_status = 'active';
				$time_label  = __( 'Active', 'buddypress-polls' );
				$now         = new DateTime( current_time( 'Y-m-d H:i:s' ) );

				if ( 'publish' !== $poll_status ) {
					$time_status = $poll_status;
					$time_label  = 'pending' === $poll_status ? __( 'Pending', 'buddypress-polls' ) : __( 'Draft', 'buddypress-polls' );
				} elseif ( '1' === $p_never_exp ) {
					if ( ! empty( $p_start_date ) && new DateTime( $p_start_date ) > $now ) {
						$time_status = 'scheduled';
						$time_label  = __( 'Scheduled', 'buddypress-polls' );
					}
				} elseif ( ! empty( $p_start_date ) && new DateTime( $p_start_date ) > $now ) {
						$time_status = 'scheduled';
						$time_label  = __( 'Scheduled', 'buddypress-polls' );
				} elseif ( ! empty( $p_end_date ) && new DateTime( $p_end_date ) <= $now ) {
					$time_status = 'expired';
					$time_label  = __( 'Expired', 'buddypress-polls' );
				}

				if ( ! empty( $pause ) && '1' === $pause && 'publish' === $poll_status ) {
					$time_status = 'paused';
					$time_label  = __( 'Paused', 'buddypress-polls' );
				}

				// Determine poll type from first answer.
				$poll_answers_extra = get_post_meta( $poll_id, '_wbpoll_answer_extra', true );
				$poll_type          = 'default';
				if ( is_array( $poll_answers_extra ) && ! empty( $poll_answers_extra ) ) {
					$first_answer = reset( $poll_answers_extra );
					if ( isset( $first_answer['type'] ) && ! empty( $first_answer['type'] ) ) {
						$poll_type = $first_answer['type'];
					}
				}

				// Poll type labels.
				$poll_type_config = array(
					'default' => array(
						'label' => __( 'Text', 'buddypress-polls' ),
						'class' => 'text',
					),
					'image'   => array(
						'label' => __( 'Image', 'buddypress-polls' ),
						'class' => 'image',
					),
					'video'   => array(
						'label' => __( 'Video', 'buddypress-polls' ),
						'class' => 'video',
					),
					'audio'   => array(
						'label' => __( 'Audio', 'buddypress-polls' ),
						'class' => 'audio',
					),
					'html'    => array(
						'label' => __( 'HTML', 'buddypress-polls' ),
						'class' => 'html',
					),
				);
				$type_config      = isset( $poll_type_config[ $poll_type ] ) ? $poll_type_config[ $poll_type ] : $poll_type_config['default'];

				$current_page_url = get_permalink();
				$edit_url         = add_query_arg(
					array(
						'poll_id'  => $poll_id,
						'_wpnonce' => wp_create_nonce( 'edit_poll_' . $poll_id ),
					),
					$current_page_url
				);
				$view_url         = site_url( '/poll/' . $poll_name );
				?>
		<article class="poll-card" data-poll-id="<?php echo esc_attr( $poll_id ); ?>" data-status="<?php echo esc_attr( $poll_status ); ?>" data-poll-type="<?php echo esc_attr( $poll_type ); ?>">
			<div class="poll-card__header">
				<h3 class="poll-card__title"><a href="<?php echo esc_url( $view_url ); ?>"><?php echo esc_html( $poll_title ); ?></a></h3>
				<div class="poll-card__badges">
					<span class="poll-card__type-badge poll-card__type-badge--<?php echo esc_attr( $type_config['class'] ); ?>">
						<?php echo esc_html( $type_config['label'] ); ?>
					</span>
					<span class="poll-card__badge poll-card__badge--<?php echo esc_attr( $time_status ); ?>">
						<?php echo esc_html( $time_label ); ?>
					</span>
				</div>
			</div>

			<div class="poll-card__meta">
				<div class="poll-card__stat">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 9a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M8 2a6 6 0 100 12A6 6 0 008 2zM2.5 8a5.5 5.5 0 1111 0 5.5 5.5 0 01-11 0z" clip-rule="evenodd"/></svg>
					<span><?php echo esc_html( sprintf( _n( '%s vote', '%s votes', $totalvote, 'buddypress-polls' ), number_format_i18n( $totalvote ) ) ); ?></span>
				</div>
				<div class="poll-card__stat">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H4zm1 2a1 1 0 000 2h6a1 1 0 100-2H5zm0 4a1 1 0 000 2h6a1 1 0 100-2H5zm0 4a1 1 0 100 2h3a1 1 0 100-2H5z" clip-rule="evenodd"/></svg>
					<span><?php echo esc_html( human_time_diff( strtotime( $poll_date ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'buddypress-polls' ) ); ?></span>
				</div>
			</div>

			<div class="poll-card__shortcode">
				<code class="poll-card__shortcode-code" data-poll-id="<?php echo esc_attr( $poll_id ); ?>">&#91;wbpoll id="<?php echo esc_attr( $poll_id ); ?>"&#93;</code>
				<button type="button" class="poll-card__shortcode-copy" data-poll-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php esc_attr_e( 'Copy shortcode', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'Copy shortcode to clipboard', 'buddypress-polls' ); ?>">
					<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V2zm2-1a1 1 0 00-1 1v8a1 1 0 001 1h8a1 1 0 001-1V2a1 1 0 00-1-1H6zM2 5a1 1 0 00-1 1v8a1 1 0 001 1h8a1 1 0 001-1v-1h1v1a2 2 0 01-2 2H2a2 2 0 01-2-2V6a2 2 0 012-2h1v1H2z" clip-rule="evenodd"/></svg>
				</button>
			</div>

			<div class="poll-card__actions">
				<button type="button" class="poll-card__action" data-action="view" data-id="<?php echo esc_attr( $poll_id ); ?>" data-url="<?php echo esc_url( $view_url ); ?>" title="<?php esc_attr_e( 'View Poll', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'View Poll', 'buddypress-polls' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
				</button>

				<?php
				// Only allow edit if user is author or admin. Matches server-side check.
				$can_edit_poll = ( intval( $poll_post->post_author ) === get_current_user_id() ) || current_user_can( 'manage_options' );
				if ( $totalvote < 1 && $can_edit_poll ) :
				?>
				<button type="button" class="poll-card__action" data-action="edit" data-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php esc_attr_e( 'Edit Poll', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'Edit Poll', 'buddypress-polls' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
				</button>
				<?php endif; ?>

				<?php if ( 'publish' === $poll_status ) : ?>
				<button type="button" class="poll-card__action" data-action="<?php echo empty( $pause ) || '0' === $pause ? 'pause' : 'resume'; ?>" data-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php echo empty( $pause ) || '0' === $pause ? esc_attr__( 'Pause Poll', 'buddypress-polls' ) : esc_attr__( 'Resume Poll', 'buddypress-polls' ); ?>" aria-label="<?php echo empty( $pause ) || '0' === $pause ? esc_attr__( 'Pause Poll', 'buddypress-polls' ) : esc_attr__( 'Resume Poll', 'buddypress-polls' ); ?>">
					<?php if ( empty( $pause ) || '0' === $pause ) : ?>
						<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
					<?php else : ?>
						<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
					<?php endif; ?>
				</button>
				<button type="button" class="poll-card__action" data-action="unpublish" data-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php esc_attr_e( 'Unpublish', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'Unpublish Poll', 'buddypress-polls' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/></svg>
				</button>
				<?php endif; ?>

				<?php if ( 'draft' === $poll_status ) : ?>
				<button type="button" class="poll-card__action poll-card__action--success" data-action="publish" data-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php esc_attr_e( 'Publish', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'Publish Poll', 'buddypress-polls' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
				</button>
				<?php endif; ?>

				<button type="button" class="poll-card__action poll-card__action--danger" data-action="delete" data-id="<?php echo esc_attr( $poll_id ); ?>" title="<?php esc_attr_e( 'Delete', 'buddypress-polls' ); ?>" aria-label="<?php esc_attr_e( 'Delete Poll', 'buddypress-polls' ); ?>">
					<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
				</button>
			</div>
		</article>
			<?php
		endforeach;

		// Empty State - show when no polls match the current filter.
		if ( empty( $polls ) ) :
			$empty_messages = array(
				'all'     => __( 'No polls yet', 'buddypress-polls' ),
				'publish' => __( 'No published polls', 'buddypress-polls' ),
				'pending' => __( 'No pending polls', 'buddypress-polls' ),
				'draft'   => __( 'No draft polls', 'buddypress-polls' ),
			);
			$empty_message = isset( $empty_messages[ $current_status ] ) ? $empty_messages[ $current_status ] : $empty_messages['all'];
			?>
		<div class="polls-empty" id="polls-empty">
			<div class="polls-empty__icon">
				<svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="48" height="48" rx="8"/><path d="M20 28h24M20 36h16"/></svg>
			</div>
			<h3 class="polls-empty__title"><?php echo esc_html( $empty_message ); ?></h3>
			<p class="polls-empty__text"><?php esc_html_e( 'Create your first poll to get started!', 'buddypress-polls' ); ?></p>
			<?php if ( $can_create_poll ) : ?>
			<button type="button" class="polls-btn polls-btn--primary" id="polls-create-btn-empty">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v10M3 8h10"/></svg>
				<span><?php esc_html_e( 'Create Your First Poll', 'buddypress-polls' ); ?></span>
			</button>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

	<?php
	// Pagination controls.
	$total_pages = $query->max_num_pages;
	if ( $total_pages > 1 ) :
		?>
	<nav class="polls-pagination" aria-label="<?php esc_attr_e( 'Poll dashboard pagination', 'buddypress-polls' ); ?>">
		<div class="polls-pagination__info">
			<?php
			$start_item = ( ( $current_page - 1 ) * $polls_per_page ) + 1;
			$end_item   = min( $current_page * $polls_per_page, $query->found_posts );			
			echo sprintf(
				/* translators: 1: First item number, 2: Last item number, 3: Total items */
				esc_html__( 'Showing %1$s-%2$s of %3$s polls', 'buddypress-polls' ),
				esc_html( number_format_i18n( absint( $start_item ) ) ),
				esc_html( number_format_i18n( absint( $end_item ) ) ),
				esc_html( number_format_i18n( absint( $query->found_posts ) ) )
			);
			?>
		</div>
		<div class="polls-pagination__links">
			<?php
			// Previous page link.
			if ( $current_page > 1 ) :
				$prev_url = add_query_arg(
					array(
						'status' => $current_status,
						'paged'  => $current_page - 1,
					),
					$base_url
				);
				?>
				<a href="<?php echo esc_url( $prev_url ); ?>" class="polls-pagination__link polls-pagination__link--prev" aria-label="<?php esc_attr_e( 'Previous page', 'buddypress-polls' ); ?>">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
					<span><?php esc_html_e( 'Previous', 'buddypress-polls' ); ?></span>
				</a>
			<?php else : ?>
				<span class="polls-pagination__link polls-pagination__link--prev polls-pagination__link--disabled" aria-disabled="true">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
					<span><?php esc_html_e( 'Previous', 'buddypress-polls' ); ?></span>
				</span>
			<?php endif; ?>

			<span class="polls-pagination__numbers">
				<?php
				// Page numbers with ellipsis.
				$range = 2; // Pages to show on each side of current.
				for ( $i = 1; $i <= $total_pages; $i++ ) :
					// Always show first, last, and pages within range.
					if ( $i === 1 || $i === $total_pages || ( $i >= $current_page - $range && $i <= $current_page + $range ) ) :
						$page_url = add_query_arg(
							array(
								'status' => $current_status,
								'paged'  => $i,
							),
							$base_url
						);
						if ( $i === $current_page ) :
							?>
							<span class="polls-pagination__number polls-pagination__number--current" aria-current="page"><?php echo esc_html( $i ); ?></span>
						<?php else : ?>
							<a href="<?php echo esc_url( $page_url ); ?>" class="polls-pagination__number"><?php echo esc_html( $i ); ?></a>
							<?php
						endif;
					elseif ( ( $i === $current_page - $range - 1 ) || ( $i === $current_page + $range + 1 ) ) :
						// Show ellipsis.
						?>
						<span class="polls-pagination__ellipsis">&hellip;</span>
						<?php
					endif;
				endfor;
				?>
			</span>

			<?php
			// Next page link.
			if ( $current_page < $total_pages ) :
				$next_url = add_query_arg(
					array(
						'status' => $current_status,
						'paged'  => $current_page + 1,
					),
					$base_url
				);
				?>
				<a href="<?php echo esc_url( $next_url ); ?>" class="polls-pagination__link polls-pagination__link--next" aria-label="<?php esc_attr_e( 'Next page', 'buddypress-polls' ); ?>">
					<span><?php esc_html_e( 'Next', 'buddypress-polls' ); ?></span>
					<svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
				</a>
			<?php else : ?>
				<span class="polls-pagination__link polls-pagination__link--next polls-pagination__link--disabled" aria-disabled="true">
					<span><?php esc_html_e( 'Next', 'buddypress-polls' ); ?></span>
					<svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
				</span>
			<?php endif; ?>
		</div>
	</nav>
	<?php endif; ?>

	<!-- Panel Overlay -->
	<div class="polls-panel-overlay" id="polls-panel-overlay" aria-hidden="true"></div>

	<!-- Create/Edit Panel (Slide-in) -->
	<aside class="polls-panel" id="polls-panel" role="dialog" aria-modal="true" aria-labelledby="polls-panel-title" aria-hidden="<?php echo ( $is_editing || $is_creating ) ? 'false' : 'true'; ?>" data-edit-blocked="<?php echo $edit_error ? 'true' : 'false'; ?>">
		<div class="polls-panel__header">
			<h2 class="polls-panel__title" id="polls-panel-title">
				<?php echo $is_editing ? esc_html__( 'Edit Poll', 'buddypress-polls' ) : esc_html__( 'Create New Poll', 'buddypress-polls' ); ?>
			</h2>
			<button type="button" class="polls-panel__close" id="polls-panel-close" aria-label="<?php esc_attr_e( 'Close panel', 'buddypress-polls' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
			</button>
		</div>

		<?php if ( $can_create_poll ) : ?>
		<div class="polls-panel__body">
			<form id="polls-form" class="polls-form">
				<?php wp_nonce_field( 'wbpoll_create_poll', 'wbpoll_nonce' ); ?>
				<input type="hidden" name="author_id" id="author_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
				<input type="hidden" name="poll_id" id="poll_id" value="<?php echo esc_attr( $post_id ); ?>">

				<!-- Basic Info Section -->
				<div class="polls-form__section">
					<div class="polls-form__group">
						<label class="polls-form__label" for="polltitle">
							<?php esc_html_e( 'Poll Title', 'buddypress-polls' ); ?>
							<span class="polls-form__required">*</span>
						</label>
						<input type="text" class="polls-form__input" name="title" id="polltitle" value="<?php echo $is_editing ? esc_attr( $post->post_title ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter your poll question...', 'buddypress-polls' ); ?>" required<?php echo $poll_title_limit > 0 ? ' maxlength="' . esc_attr( $poll_title_limit ) . '" data-char-limit="' . esc_attr( $poll_title_limit ) . '"' : ''; ?>>
						<?php if ( $poll_title_limit > 0 ) : ?>
						<div class="polls-form__char-counter" data-for="polltitle">
							<span class="char-count">0</span> / <?php echo esc_html( $poll_title_limit ); ?> <?php esc_html_e( 'characters', 'buddypress-polls' ); ?>
						</div>
						<?php endif; ?>
						<span class="polls-form__error" id="error_title"></span>
					</div>

					<div class="polls-form__group">
						<label class="polls-form__label" for="poll-content">
							<?php esc_html_e( 'Description', 'buddypress-polls' ); ?>
							<span class="polls-form__optional"><?php esc_html_e( '(Optional)', 'buddypress-polls' ); ?></span>
						</label>
						<?php
						$content = $is_editing ? $post->post_content : '';
						if ( $use_simple_textarea ) {
							?>
							<textarea class="polls-form__textarea" name="content" id="poll-content" rows="3" placeholder="<?php esc_attr_e( 'Add more context to your poll...', 'buddypress-polls' ); ?>"<?php echo $poll_description_limit > 0 ? ' maxlength="' . esc_attr( $poll_description_limit ) . '" data-char-limit="' . esc_attr( $poll_description_limit ) . '"' : ''; ?>><?php echo esc_textarea( $content ); ?></textarea>
							<?php
						} else {
							$editor_settings = array(
								'textarea_name' => 'content',
								'editor_height' => 150,
								'media_buttons' => false,
								'teeny'         => true,
								'editor_class'  => 'polls-form__editor',
							);
							if ( $disable_html_editor ) {
								$editor_settings['quicktags'] = false;
							}
							wp_editor( $content, 'poll-content', $editor_settings );
						}
						if ( $poll_description_limit > 0 ) :
						?>
						<div class="polls-form__char-counter" data-for="poll-content" data-limit="<?php echo esc_attr( $poll_description_limit ); ?>">
							<span class="char-count">0</span> / <?php echo esc_html( $poll_description_limit ); ?> <?php esc_html_e( 'characters', 'buddypress-polls' ); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Poll Expiry Settings -->
				<div class="polls-form__section">
					<div class="polls-form__group">
						<label class="polls-form__label"><?php esc_html_e( 'Poll Duration', 'buddypress-polls' ); ?></label>
						<div class="polls-form__expiry-options">
							<label class="polls-form__expiry-option <?php echo '1' === $never_expire ? 'polls-form__expiry-option--selected' : ''; ?>">
								<input type="radio" name="_wbpoll_never_expire" value="1" <?php checked( $never_expire, '1' ); ?>>
								<span class="polls-form__expiry-icon">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
								</span>
								<span class="polls-form__expiry-text">
									<strong><?php esc_html_e( 'Never Expires', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Poll stays open indefinitely', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<label class="polls-form__expiry-option <?php echo '0' === $never_expire ? 'polls-form__expiry-option--selected' : ''; ?>">
								<input type="radio" name="_wbpoll_never_expire" value="0" <?php checked( $never_expire, '0' ); ?>>
								<span class="polls-form__expiry-icon">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
								</span>
								<span class="polls-form__expiry-text">
									<strong><?php esc_html_e( 'Set Duration', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Schedule start and end dates', 'buddypress-polls' ); ?></small>
								</span>
							</label>
						</div>
					</div>

					<!-- Date Fields -->
					<div class="polls-form__row polls-form__dates" id="polls-dates" style="<?php echo '1' === $never_expire ? 'display:none;' : ''; ?>">
						<div class="polls-form__group polls-form__group--half">
							<label class="polls-form__label" for="_wbpoll_start_date"><?php esc_html_e( 'Start Date', 'buddypress-polls' ); ?></label>
							<input type="text" class="polls-form__input wbpollmetadatepicker" name="_wbpoll_start_date" id="_wbpoll_start_date" value="<?php echo ! empty( $start_time ) ? esc_attr( $start_time ) : esc_attr( current_time( 'Y-m-d H:i:s' ) ); ?>">
						</div>
						<div class="polls-form__group polls-form__group--half">
							<label class="polls-form__label" for="_wbpoll_end_date"><?php esc_html_e( 'End Date', 'buddypress-polls' ); ?></label>
							<?php $next_seven_days = wp_date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ); ?>
							<input type="text" class="polls-form__input wbpollmetadatepicker" name="_wbpoll_end_date" id="_wbpoll_end_date" value="<?php echo ! empty( $end_date ) ? esc_attr( $end_date ) : esc_attr( $next_seven_days ); ?>">
						</div>
					</div>
				</div>

				<!-- Poll Type Selection -->
				<div class="polls-form__section">
					<div class="polls-form__group">
						<label class="polls-form__label">
							<?php esc_html_e( 'Poll Type', 'buddypress-polls' ); ?>
							<span class="polls-form__required">*</span>
						</label>
						<div class="polls-form__type-grid" id="poll-type-selector">
							<label class="polls-form__type-card <?php echo 'default' === $poll_type ? 'polls-form__type-card--selected' : ''; ?>">
								<input type="radio" name="poll_type" value="default" <?php checked( $poll_type, 'default' ); ?> required>
								<span class="polls-form__type-icon polls-form__type-icon--text">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M14 17H4v2h10v-2zm6-8H4v2h16V9zM4 15h16v-2H4v2zM4 5v2h16V5H4z"/></svg>
								</span>
								<span class="polls-form__type-info">
									<strong><?php esc_html_e( 'Text Only', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Simple text-based options', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<?php if ( $enable_image_poll ) : ?>
							<label class="polls-form__type-card <?php echo 'image' === $poll_type ? 'polls-form__type-card--selected' : ''; ?>">
								<input type="radio" name="poll_type" value="image" <?php checked( $poll_type, 'image' ); ?>>
								<span class="polls-form__type-icon polls-form__type-icon--image">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
								</span>
								<span class="polls-form__type-info">
									<strong><?php esc_html_e( 'With Images', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Visual options with photos', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<?php endif; ?>
							<?php if ( $enable_video_poll ) : ?>
							<label class="polls-form__type-card <?php echo 'video' === $poll_type ? 'polls-form__type-card--selected' : ''; ?>">
								<input type="radio" name="poll_type" value="video" <?php checked( $poll_type, 'video' ); ?>>
								<span class="polls-form__type-icon polls-form__type-icon--video">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
								</span>
								<span class="polls-form__type-info">
									<strong><?php esc_html_e( 'With Videos', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Embed video content', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<?php endif; ?>
							<?php if ( $enable_audio_poll ) : ?>
							<label class="polls-form__type-card <?php echo 'audio' === $poll_type ? 'polls-form__type-card--selected' : ''; ?>">
								<input type="radio" name="poll_type" value="audio" <?php checked( $poll_type, 'audio' ); ?>>
								<span class="polls-form__type-icon polls-form__type-icon--audio">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
								</span>
								<span class="polls-form__type-info">
									<strong><?php esc_html_e( 'With Audio', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Audio clips or music', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<?php endif; ?>
							<?php if ( $enable_html_poll ) : ?>
							<label class="polls-form__type-card <?php echo 'html' === $poll_type ? 'polls-form__type-card--selected' : ''; ?>">
								<input type="radio" name="poll_type" value="html" <?php checked( $poll_type, 'html' ); ?>>
								<span class="polls-form__type-icon polls-form__type-icon--html">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>
								</span>
								<span class="polls-form__type-info">
									<strong><?php esc_html_e( 'Rich HTML', 'buddypress-polls' ); ?></strong>
									<small><?php esc_html_e( 'Custom formatted content', 'buddypress-polls' ); ?></small>
								</span>
							</label>
							<?php endif; ?>
						</div>
						<!-- Hidden select for backward compatibility -->
						<select class="polls-form__select" name="poll_type_select" id="poll_type" style="display:none;">
							<option value="default" <?php selected( $poll_type, 'default' ); ?>><?php esc_html_e( 'Text Only', 'buddypress-polls' ); ?></option>
							<?php if ( $enable_image_poll ) : ?>
								<option value="image" <?php selected( $poll_type, 'image' ); ?>><?php esc_html_e( 'With Images', 'buddypress-polls' ); ?></option>
							<?php endif; ?>
							<?php if ( $enable_video_poll ) : ?>
								<option value="video" <?php selected( $poll_type, 'video' ); ?>><?php esc_html_e( 'With Videos', 'buddypress-polls' ); ?></option>
							<?php endif; ?>
							<?php if ( $enable_audio_poll ) : ?>
								<option value="audio" <?php selected( $poll_type, 'audio' ); ?>><?php esc_html_e( 'With Audio', 'buddypress-polls' ); ?></option>
							<?php endif; ?>
							<?php if ( $enable_html_poll ) : ?>
								<option value="html" <?php selected( $poll_type, 'html' ); ?>><?php esc_html_e( 'HTML Content', 'buddypress-polls' ); ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>

				<!-- Answer Options Section -->
				<div class="polls-form__section">
					<div class="polls-form__section-header">
						<h3 class="polls-form__section-title"><?php esc_html_e( 'Answer Options', 'buddypress-polls' ); ?></h3>
						<span class="polls-form__section-hint"><?php esc_html_e( 'Drag to reorder', 'buddypress-polls' ); ?></span>
					</div>

					<div class="polls-answers" id="polls-answers" data-answer-limit="<?php echo esc_attr( $poll_answer_limit ); ?>">
						<!-- Text Poll Answers -->
						<div class="polls-answers__list" id="type_text" data-poll-type="default" style="<?php echo 'default' !== $poll_type ? 'display:none;' : ''; ?>">
							<?php if ( $is_editing && 'default' === $poll_type && ! empty( $options ) ) : ?>
								<?php foreach ( $options as $key => $optn ) : ?>
								<div class="polls-answer" data-index="<?php echo esc_attr( $key ); ?>">
									<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</div>
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="<?php echo esc_attr( $optn ); ?>" placeholder="<?php esc_attr_e( 'Enter option...', 'buddypress-polls' ); ?>">
									<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
									<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</button>
								</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="polls-answer" data-index="0">
									<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</div>
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option 1', 'buddypress-polls' ); ?>">
									<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
									<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</button>
								</div>
								<div class="polls-answer" data-index="1">
									<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</div>
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option 2', 'buddypress-polls' ); ?>">
									<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
									<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</button>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( $enable_image_poll ) : ?>
						<!-- Image Poll Answers -->
						<div class="polls-answers__list" id="type_image" data-poll-type="image" style="<?php echo 'image' !== $poll_type ? 'display:none;' : ''; ?>">
							<?php if ( $is_editing && 'image' === $poll_type && ! empty( $options ) ) : ?>
								<?php foreach ( $options as $key => $optn ) : ?>
								<div class="polls-answer polls-answer--media" data-index="<?php echo esc_attr( $key ); ?>">
									<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</div>
									<div class="polls-answer__preview">
										<?php if ( ! empty( $optn['image'] ) ) : ?>
											<img src="<?php echo esc_url( $optn['image'] ); ?>" alt="">
										<?php endif; ?>
									</div>
									<div class="polls-answer__fields">
										<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="<?php echo esc_attr( $optn['ans'] ); ?>" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
										<input type="hidden" value="image" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
										<div class="polls-answer__url-row">
											<input type="url" class="polls-answer__input wbpoll_image_answer_url" name="_wbpoll_full_size_image_answer[]" value="<?php echo esc_attr( $optn['image'] ); ?>" placeholder="<?php esc_attr_e( 'Image URL...', 'buddypress-polls' ); ?>">
											<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="image" aria-label="<?php esc_attr_e( 'Choose image', 'buddypress-polls' ); ?>">
												<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
											</button>
										</div>
									</div>
									<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</button>
								</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="polls-answer polls-answer--media" data-index="0">
									<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</div>
									<div class="polls-answer__preview"></div>
									<div class="polls-answer__fields">
										<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
										<input type="hidden" value="image" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
										<div class="polls-answer__url-row">
											<input type="url" class="polls-answer__input wbpoll_image_answer_url" name="_wbpoll_full_size_image_answer[]" value="" placeholder="<?php esc_attr_e( 'Image URL...', 'buddypress-polls' ); ?>">
											<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="image" aria-label="<?php esc_attr_e( 'Choose image', 'buddypress-polls' ); ?>">
												<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
											</button>
										</div>
									</div>
									<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
									</button>
								</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<?php if ( $enable_video_poll ) : ?>
						<!-- Video Poll Answers -->
						<div class="polls-answers__list" id="type_video" data-poll-type="video" style="<?php echo 'video' !== $poll_type ? 'display:none;' : ''; ?>">
							<div class="polls-answer polls-answer--media" data-index="0">
								<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</div>
								<div class="polls-answer__preview"></div>
								<div class="polls-answer__fields">
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
									<input type="hidden" value="video" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
									<div class="polls-answer__url-row">
										<input type="url" class="polls-answer__input wbpoll_video_answer_url" name="_wbpoll_video_answer_url[]" value="" placeholder="<?php esc_attr_e( 'Video URL (YouTube, Vimeo, etc.)', 'buddypress-polls' ); ?>">
										<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="video" aria-label="<?php esc_attr_e( 'Choose video', 'buddypress-polls' ); ?>">
											<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
										</button>
									</div>
								</div>
								<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</button>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $enable_audio_poll ) : ?>
						<!-- Audio Poll Answers -->
						<div class="polls-answers__list" id="type_audio" data-poll-type="audio" style="<?php echo 'audio' !== $poll_type ? 'display:none;' : ''; ?>">
							<div class="polls-answer polls-answer--media" data-index="0">
								<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</div>
								<div class="polls-answer__preview"></div>
								<div class="polls-answer__fields">
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
									<input type="hidden" value="audio" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
									<div class="polls-answer__url-row">
										<input type="url" class="polls-answer__input wbpoll_audio_answer_url" name="_wbpoll_audio_answer_url[]" value="" placeholder="<?php esc_attr_e( 'Audio URL...', 'buddypress-polls' ); ?>">
										<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="audio" aria-label="<?php esc_attr_e( 'Choose audio', 'buddypress-polls' ); ?>">
											<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd"/></svg>
										</button>
									</div>
								</div>
								<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</button>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $enable_html_poll ) : ?>
						<!-- HTML Poll Answers -->
						<div class="polls-answers__list" id="type_html" data-poll-type="html" style="<?php echo 'html' !== $poll_type ? 'display:none;' : ''; ?>">
							<div class="polls-answer polls-answer--html" data-index="0">
								<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</div>
								<div class="polls-answer__fields polls-answer__fields--full">
									<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
									<textarea class="polls-answer__textarea wbpoll_html_answer_textarea" name="_wbpoll_html_answer[]" placeholder="<?php esc_attr_e( 'HTML content...', 'buddypress-polls' ); ?>"></textarea>
									<input type="hidden" value="html" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
								</div>
								<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</button>
							</div>
						</div>
						<?php endif; ?>
					</div>

					<button type="button" class="polls-btn polls-btn--secondary polls-btn--add-answer" id="polls-add-answer">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v10M3 8h10"/></svg>
						<span><?php esc_html_e( 'Add Option', 'buddypress-polls' ); ?></span>
					</button>
					<span class="polls-form__error" id="error_ans"></span>
				</div>

				<!-- Advanced Options -->
				<details class="polls-form__details">
					<summary class="polls-form__summary">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
						<?php esc_html_e( 'Advanced Settings', 'buddypress-polls' ); ?>
					</summary>
					<div class="polls-form__details-content">
						<div class="polls-form__group" id="polls-show-result-after-expire" style="<?php echo '1' === $never_expire ? 'display:none;' : ''; ?>">
							<label class="polls-form__label"><?php esc_html_e( 'Show Results After Expire', 'buddypress-polls' ); ?></label>
							<div class="polls-form__toggle-group">
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_show_result_before_expire" value="1" <?php checked( $show_result_after_expire, '1' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
								</label>
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_show_result_before_expire" value="0" <?php checked( $show_result_after_expire, '0' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
								</label>
							</div>
						</div>

						<div class="polls-form__group">
							<label class="polls-form__label"><?php esc_html_e( 'Allow Multiple Choices', 'buddypress-polls' ); ?></label>
							<div class="polls-form__toggle-group">
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_multivote" value="1" <?php checked( $multivote, '1' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
								</label>
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_multivote" value="0" <?php checked( $multivote, '0' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
								</label>
							</div>
						</div>

						<?php						
						$wbpolls_user_add_extra_op = isset( $option_value['wbpolls_user_add_extra_op'] ) ? $option_value['wbpolls_user_add_extra_op'] : '';
						if ( 'yes' === $wbpolls_user_add_extra_op ) :
							?>
						<div class="polls-form__group">
							<label class="polls-form__label"><?php esc_html_e( 'Allow Users to Add Options', 'buddypress-polls' ); ?></label>
							<div class="polls-form__toggle-group">
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_add_additional_fields" value="1" <?php checked( $add_additional_fields, '1' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
								</label>
								<label class="polls-form__toggle">
									<input type="radio" name="_wbpoll_add_additional_fields" value="0" <?php checked( $add_additional_fields, '0' ); ?>>
									<span class="polls-form__toggle-label"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
								</label>
							</div>
							<span class="polls-form__hint"><?php esc_html_e( 'Only available for text polls.', 'buddypress-polls' ); ?></span>
						</div>
						<?php endif; ?>
					</div>
				</details>
			</form>
		</div>

		<div class="polls-panel__footer">
			<button type="button" class="polls-btn polls-btn--ghost" id="polls-panel-cancel"><?php esc_html_e( 'Cancel', 'buddypress-polls' ); ?></button>
			<button type="submit" class="polls-btn polls-btn--primary" id="polls-form-submit" form="polls-form">
				<span class="polls-btn__text"><?php echo $is_editing ? esc_html__( 'Update Poll', 'buddypress-polls' ) : esc_html__( 'Create Poll', 'buddypress-polls' ); ?></span>
				<span class="polls-btn__loading" style="display:none;">
					<svg class="polls-spinner" width="16" height="16" viewBox="0 0 16 16"><circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="30" stroke-linecap="round"/></svg>
					<?php esc_html_e( 'Saving...', 'buddypress-polls' ); ?>
				</span>
			</button>
		</div>

		<div class="polls-alert polls-alert--success polls-panel__success" id="polls-success" style="display:none;"></div>
		<?php else : ?>
		<div class="polls-panel__body">
			<div class="polls-alert polls-alert--warning">
				<span class="polls-alert__icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
				</span>
				<span class="polls-alert__text"><?php esc_html_e( 'You do not have permission to create polls.', 'buddypress-polls' ); ?></span>
			</div>
		</div>
		<?php endif; ?>
	</aside>

<?php endif; ?>

</div>

<?php
// Auto-open panel if editing or creating.
if ( $is_editing || $is_creating ) :
	?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var panel = document.getElementById('polls-panel');
	var overlay = document.getElementById('polls-panel-overlay');
	if (panel && overlay) {
		panel.classList.add('is-open');
		panel.setAttribute('aria-hidden', 'false');
		overlay.classList.add('is-visible');
		document.body.classList.add('polls-panel-open');
	}
});
</script>
<?php endif; ?>

<?php
// Restore the original post object.
$post = $temp_post;
?>
