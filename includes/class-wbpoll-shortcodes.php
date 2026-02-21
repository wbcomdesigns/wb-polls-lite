<?php
/**
 * Poll List Shortcode
 *
 * Provides the [wbpoll_list] shortcode to display all polls
 * in a grid layout matching the archive page design.
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/includes
 * @since      4.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WBPoll_Shortcodes
 *
 * Registers and handles the [wbpoll_list] shortcode.
 *
 * @since 4.6.0
 */
class WBPoll_Shortcodes {

	/**
	 * Instance of this class.
	 *
	 * @var WBPoll_Shortcodes
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return WBPoll_Shortcodes
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_shortcode( 'wbpoll_list', array( $this, 'render_poll_list' ) );
	}

	/**
	 * Render the [wbpoll_list] shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content (unused).
	 * @return string HTML output.
	 */
	public function render_poll_list( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'ids'         => '',
				'category'    => '',
				'author'      => '',
				'status'      => 'all',
				'count'       => 10,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'columns'     => 3,
				'pagination'  => 'yes',
				'show_status' => 'yes',
				'show_votes'  => 'yes',
				'show_date'   => 'yes',
			),
			$atts,
			'wbpoll_list'
		);

		// Enqueue archive styles.
		wp_enqueue_style( 'wbpolls-archive' );

		// Build query args.
		$query_args = array(
			'post_type'      => 'wbpoll',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['count'] ),
			'order'          => in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : 'DESC',
		);

		// Orderby.
		switch ( $atts['orderby'] ) {
			case 'title':
				$query_args['orderby'] = 'title';
				break;
			case 'votes':
				$query_args['orderby']  = 'meta_value_num';
				$query_args['meta_key'] = '_wbpoll_vote_count'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				break;
			case 'rand':
				$query_args['orderby'] = 'rand';
				break;
			default:
				$query_args['orderby'] = 'date';
				break;
		}

		// Specific IDs.
		if ( ! empty( $atts['ids'] ) ) {
			$ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
			$query_args['post__in'] = $ids;
			if ( 'date' === $atts['orderby'] ) {
				$query_args['orderby'] = 'post__in';
			}
		}

		// Category filter.
		if ( ! empty( $atts['category'] ) ) {
			if ( is_numeric( $atts['category'] ) ) {
				$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'wbpoll_cat',
						'field'    => 'term_id',
						'terms'    => absint( $atts['category'] ),
					),
				);
			} else {
				$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'wbpoll_cat',
						'field'    => 'slug',
						'terms'    => sanitize_title( $atts['category'] ),
					),
				);
			}
		}

		// Author filter.
		if ( ! empty( $atts['author'] ) ) {
			$query_args['author'] = absint( $atts['author'] );
		}

		// Pagination.
		if ( 'yes' === $atts['pagination'] ) {
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$query_args['paged'] = $paged;
		}

		$polls_query = new WP_Query( $query_args );

		// Column class.
		$columns = max( 1, min( 4, intval( $atts['columns'] ) ) );

		ob_start();
		?>
		<div class="polls-archive wbpoll-list-shortcode">

			<?php if ( $polls_query->have_posts() ) : ?>

				<div class="polls-archive__grid" style="grid-template-columns: repeat(<?php echo esc_attr( $columns ); ?>, 1fr);">
					<?php
					while ( $polls_query->have_posts() ) :
						$polls_query->the_post();
						$this->render_poll_card( $atts );
					endwhile;
					?>
				</div>

				<?php
				// Pagination.
				if ( 'yes' === $atts['pagination'] && $polls_query->max_num_pages > 1 ) :
					$pagination = paginate_links(
						array(
							'total'     => $polls_query->max_num_pages,
							'current'   => isset( $paged ) ? $paged : 1,
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
								echo $page_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Paginate links output.
							}
							?>
						</nav>
						<?php
					endif;
				endif;

			else :
				?>
				<div class="polls-widget__empty">
					<p class="polls-widget__empty-text"><?php esc_html_e( 'No polls found.', 'buddypress-polls' ); ?></p>
				</div>
				<?php
			endif;

			wp_reset_postdata();
			?>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single poll card.
	 *
	 * Reuses the archive page card design.
	 *
	 * @param array $atts Shortcode attributes for display options.
	 */
	private function render_poll_card( $atts ) {
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
		$poll_status      = 'active';

		if ( ! empty( $poll_start_date ) && new DateTime( $poll_start_date ) > new DateTime( $current_datetime ) ) {
			$status_label = __( 'Scheduled', 'buddypress-polls' );
			$status_class = 'polls-status-badge--scheduled';
			$poll_status  = 'scheduled';
		} elseif ( 'yes' !== $poll_never_expire && ! empty( $poll_end_date ) && new DateTime( $poll_end_date ) < new DateTime( $current_datetime ) ) {
			$status_label = __( 'Ended', 'buddypress-polls' );
			$status_class = 'polls-status-badge--ended';
			$poll_status  = 'ended';
		}

		// Filter by status attribute.
		if ( 'all' !== $atts['status'] && $atts['status'] !== $poll_status ) {
			return;
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
		$type_label = isset( $type_labels[ $poll_type ] ) ? $type_labels[ $poll_type ] : __( 'Text', 'buddypress-polls' );
		?>

		<article id="post-<?php the_ID(); ?>" class="polls-archive__card">
			<div class="polls-archive__card-header">
				<div class="polls-archive__card-badges">
					<span class="polls-type-badge"><?php echo esc_html( $type_label ); ?></span>
					<?php if ( 'yes' === $atts['show_status'] ) : ?>
						<span class="polls-status-badge <?php echo esc_attr( $status_class ); ?>">
							<span class="polls-status-badge__dot"></span>
							<?php echo esc_html( $status_label ); ?>
						</span>
					<?php endif; ?>
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
					<?php if ( 'yes' === $atts['show_votes'] ) : ?>
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
					<?php endif; ?>
					<?php if ( 'yes' === $atts['show_date'] ) : ?>
						<span class="polls-archive__card-meta-item">
							<svg class="polls-archive__card-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
							<?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'buddypress-polls' ) ); ?>
						</span>
					<?php endif; ?>
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

		<?php
	}
}
