<?php
/**
 * WBPoll Single page
 *
 * Modern single poll view with poll type badges.
 *
 * @package WordPress
 * @subpackage buddypress-polls
 * @since 4.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Hook: buddypress_polls_before_main_content.
 */
do_action( 'buddypress_polls_before_main_content' );

$bpolls_settings = get_site_option( 'wbpolls_settings' );

// Get dashboard page URL for frontend edit link.
$dashboard_page_id  = isset( $bpolls_settings['poll_dashboard_page'] ) ? intval( $bpolls_settings['poll_dashboard_page'] ) : 0;
$dashboard_page_url = $dashboard_page_id ? get_permalink( $dashboard_page_id ) : '';
?>

<div id="primary" class="content-area">

	<main id="main" class="site-main buddypress-polls-wrap" role="main">

		<?php do_action( 'before_single_buddypress_polls' ); ?>

		<?php
		while ( have_posts() ) :
			the_post();

			$post_id = intval( get_the_ID() );

			// Get poll meta data.
			$poll_description  = get_post_meta( $post_id, '_wbpoll_content', true );
			$poll_start_date   = get_post_meta( $post_id, '_wbpoll_start_date', true );
			$poll_end_date     = get_post_meta( $post_id, '_wbpoll_end_date', true );
			$poll_never_expire = get_post_meta( $post_id, '_wbpoll_never_expire', true );
			$poll_multivote    = get_post_meta( $post_id, '_wbpoll_multivote', true );

			// Get poll type for badge display.
			$poll_type       = get_post_meta( $post_id, 'poll_type', true );
			$poll_type       = ! empty( $poll_type ) ? $poll_type : 'default';
			$poll_type_label = ucfirst( $poll_type );
			if ( 'default' === $poll_type ) {
				$poll_type_label = __( 'Text', 'buddypress-polls' );
			}

			// Determine poll status.
			$current_datetime = current_datetime()->format( 'Y-m-d H:i:s' );
			$status_label     = __( 'Active', 'buddypress-polls' );
			$status_class     = 'polls-status-badge--active';

			if ( ! empty( $poll_start_date ) && new DateTime( $poll_start_date ) > new DateTime( $current_datetime ) ) {
				$status_label = __( 'Scheduled', 'buddypress-polls' );
				$status_class = 'polls-status-badge--scheduled';
			} elseif ( 'yes' !== $poll_never_expire && ! empty( $poll_end_date ) && new DateTime( $poll_end_date ) < new DateTime( $current_datetime ) ) {
				$status_label = __( 'Ended', 'buddypress-polls' );
				$status_class = 'polls-status-badge--ended';
			}

			// Get vote count.
			global $wpdb;
			$votes_table = $wpdb->prefix . 'wbpoll_votes';
			$vote_count  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$votes_table} WHERE poll_id = %d", $post_id ) );
			$vote_count  = intval( $vote_count );
			?>

			<div class="polls-single">
				<!-- Poll Header -->
				<header class="polls-single__header">
					<div class="polls-single__header-top">
						<?php if ( is_user_logged_in() && ! empty( $dashboard_page_url ) ) : ?>
							<a href="<?php echo esc_url( $dashboard_page_url ); ?>" class="polls-single__back-link">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
								<?php esc_html_e( 'Back to Dashboard', 'buddypress-polls' ); ?>
							</a>
						<?php endif; ?>

						<span class="polls-type-badge polls-type-badge--<?php echo esc_attr( $poll_type ); ?>">
							<?php echo esc_html( $poll_type_label ); ?>
						</span>

						<span class="polls-status-badge <?php echo esc_attr( $status_class ); ?>">
							<span class="polls-status-badge__dot"></span>
							<?php echo esc_html( $status_label ); ?>
						</span>

						<?php if ( current_user_can( 'edit_post', $post_id ) && ! empty( $dashboard_page_url ) ) : ?>
							<?php
							$edit_url = add_query_arg(
								array(
									'poll_id'  => $post_id,
									'_wpnonce' => wp_create_nonce( 'edit_poll_' . $post_id ),
								),
								$dashboard_page_url
							);
							?>
							<a href="<?php echo esc_url( $edit_url ); ?>" class="polls-single__edit-link">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
								<?php esc_html_e( 'Edit', 'buddypress-polls' ); ?>
							</a>
						<?php endif; ?>
					</div>

					<h1 class="polls-single__title"><?php the_title(); ?></h1>

					<?php
					// Only show description if it's meaningful (not just numbers or very short).
					$desc_trimmed = trim( $poll_description );
					if ( ! empty( $desc_trimmed ) && strlen( $desc_trimmed ) > 2 && ! is_numeric( $desc_trimmed ) ) :
					?>
						<p class="polls-single__description"><?php echo esc_html( $poll_description ); ?></p>
					<?php endif; ?>

					<div class="polls-single__meta">
						<div class="polls-single__meta-item">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
							<span>
								<?php
								printf(
									/* translators: %s: vote count */
									esc_html( _n( '%s vote', '%s votes', $vote_count, 'buddypress-polls' ) ),
									esc_html( number_format_i18n( $vote_count ) )
								);
								?>
							</span>
						</div>

						<?php if ( $poll_multivote ) : ?>
						<div class="polls-single__meta-item">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
							<span><?php esc_html_e( 'Multiple choice', 'buddypress-polls' ); ?></span>
						</div>
						<?php endif; ?>
					</div>
				</header>

				<!-- Poll Content -->
				<div class="polls-single__content">
					<?php
					// Only display post content if it's meaningful (not just numbers or very short text).
					$post_content = get_the_content();
					$post_content = wp_strip_all_tags( $post_content );
					$post_content = trim( $post_content );

					// Show content only if it's longer than 2 chars and not purely numeric.
					if ( strlen( $post_content ) > 2 && ! is_numeric( $post_content ) ) {
						the_content();
					}

					echo WBPollHelper::wbpoll_single_display( $post_id, 'content_hook', '', '', 0 ); // phpcs:ignore
					?>
				</div>

				<?php
				// Comments section.
				if ( ( comments_open() || get_comments_number() ) && ( isset( $bpolls_settings['wppolls_show_comment'] ) && 'yes' === $bpolls_settings['wppolls_show_comment'] ) ) :
					?>
					<div class="polls-single__comments">
						<?php comments_template(); ?>
					</div>
					<?php
				endif;
				?>
			</div>

			<?php
		endwhile;
		?>

		<?php do_action( 'after_single_buddypress_polls' ); ?>

	</main>

</div>

<?php if ( is_active_sidebar( 'buddypress-poll-right' ) ) : ?>
	<aside id="primary-sidebar" class="widget-area default" role="complementary">
		<div class="widget-area-inner">
			<?php dynamic_sidebar( 'buddypress-poll-right' ); ?>
		</div>
	</aside>
<?php endif; ?>

<?php
/**
 * Hook: buddypress_polls_after_main_content.
 */
do_action( 'buddypress_polls_after_main_content' );

get_footer();
