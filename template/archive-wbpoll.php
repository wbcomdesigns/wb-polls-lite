<?php
/**
 * WBPoll Archive page
 *
 * Modern card-based archive layout for polls.
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

?>
<div id="primary" class="content-area">

	<main id="main" class="site-main buddypress-polls-wrap" role="main">

		<?php do_action( 'before_single_buddypress_polls' ); ?>

		<div class="polls-archive">

			<?php if ( have_posts() ) : ?>

				<header class="polls-archive__header">
					<h1 class="polls-archive__title"><?php esc_html_e( 'Polls', 'buddypress-polls' ); ?></h1>
					<?php the_archive_description( '<p class="polls-archive__description">', '</p>' ); ?>
				</header>

				<div class="polls-archive__grid">
					<?php
					while ( have_posts() ) :
						the_post();

						$post_id = get_the_ID();

						// Get poll meta data.
						$poll_description  = get_post_meta( $post_id, '_wbpoll_content', true );
						$poll_start_date   = get_post_meta( $post_id, '_wbpoll_start_date', true );
						$poll_end_date     = get_post_meta( $post_id, '_wbpoll_end_date', true );
						$poll_never_expire = get_post_meta( $post_id, '_wbpoll_never_expire', true );
						$poll_type         = get_post_meta( $post_id, '_wbpoll_type', true );

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
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$vote_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$votes_table} WHERE poll_id = %d", $post_id ) );
						$vote_count = intval( $vote_count );

						// Poll type label.
						$type_labels = array(
							'text'  => __( 'Text', 'buddypress-polls' ),
							'image' => __( 'Image', 'buddypress-polls' ),
							'video' => __( 'Video', 'buddypress-polls' ),
							'audio' => __( 'Audio', 'buddypress-polls' ),
							'html'  => __( 'HTML', 'buddypress-polls' ),
						);
						$type_label  = isset( $type_labels[ $poll_type ] ) ? $type_labels[ $poll_type ] : __( 'Text', 'buddypress-polls' );
						?>

						<article id="post-<?php the_ID(); ?>" class="polls-archive__card">
							<div class="polls-archive__card-header">
								<div class="polls-archive__card-badges">
									<span class="polls-type-badge"><?php echo esc_html( $type_label ); ?></span>
									<span class="polls-status-badge <?php echo esc_attr( $status_class ); ?>">
										<span class="polls-status-badge__dot"></span>
										<?php echo esc_html( $status_label ); ?>
									</span>
								</div>
								<h2 class="polls-archive__card-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>
							</div>

							<div class="polls-archive__card-body">
								<?php if ( ! empty( $poll_description ) ) : ?>
									<p class="polls-archive__card-description"><?php echo esc_html( wp_trim_words( $poll_description, 20, '...' ) ); ?></p>
								<?php elseif ( has_excerpt() ) : ?>
									<p class="polls-archive__card-description"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?></p>
								<?php endif; ?>

								<div class="polls-archive__card-meta">
									<span class="polls-archive__card-meta-item">
										<svg class="polls-archive__card-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
											<circle cx="9" cy="7" r="4"></circle>
											<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
											<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
										</svg>
										<?php
										printf(
											/* translators: %s: vote count */
											esc_html( _n( '%s vote', '%s votes', $vote_count, 'buddypress-polls' ) ),
											esc_html( number_format_i18n( $vote_count ) )
										);
										?>
									</span>
									<span class="polls-archive__card-meta-item">
										<svg class="polls-archive__card-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<circle cx="12" cy="12" r="10"></circle>
											<polyline points="12 6 12 12 16 14"></polyline>
										</svg>
										<?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'buddypress-polls' ) ); ?>
									</span>
								</div>
							</div>

							<div class="polls-archive__card-footer">
								<a href="<?php the_permalink(); ?>" class="polls-archive__card-link">
									<?php esc_html_e( 'View Poll', 'buddypress-polls' ); ?>
									<svg class="polls-archive__card-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<line x1="5" y1="12" x2="19" y2="12"></line>
										<polyline points="12 5 19 12 12 19"></polyline>
									</svg>
								</a>
							</div>
						</article>

					<?php endwhile; ?>
				</div>

				<?php
				// Pagination.
				$pagination = paginate_links(
					array(
						'type'      => 'array',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					)
				);

				if ( ! empty( $pagination ) ) :
					?>
					<nav class="polls-archive__pagination" aria-label="<?php esc_attr_e( 'Poll pagination', 'buddypress-polls' ); ?>">
						<?php
						foreach ( $pagination as $page_link ) {
							echo $page_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</nav>
					<?php
				endif;

			else :
				?>
				<div class="polls-widget__empty">
					<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M3 3v18h18"></path>
						<path d="M18 9l-5 5-4-4-3 3"></path>
					</svg>
					<p class="polls-widget__empty-text"><?php esc_html_e( 'No polls found.', 'buddypress-polls' ); ?></p>
					<p class="polls-widget__empty-hint"><?php esc_html_e( 'Check back later for new polls.', 'buddypress-polls' ); ?></p>
				</div>
				<?php
			endif;
			?>

		</div>

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
