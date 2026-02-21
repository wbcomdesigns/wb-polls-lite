<?php
/**
 * BuddyPress Poll Activity Graph Widget
 *
 * Modern widget for displaying activity poll results with progress bars.
 *
 * @package Buddypress_Polls
 * @subpackage Buddypress_Polls/public/inc
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Poll Activity Graph Widget.
 *
 * @since 1.0.0
 */
class BP_Poll_Activity_Graph_Widget extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array(
			'description'                 => __( 'Display activity poll results with modern styling', 'buddypress-polls' ),
			'classname'                   => 'widget_bp_poll_graph_widget polls-widget polls-widget--activity-graph buddypress widget',
			'customize_selective_refresh' => true,
		);
		parent::__construct( false, _x( '(BuddyPress) Poll Graph', 'widget name', 'buddypress-polls' ), $widget_ops );

		if ( ! is_customize_preview() ) {
			global $pagenow;
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			if ( is_admin() && 'index.php' === $pagenow ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}

		// AJAX handler for loading activity poll results.
		add_action( 'wp_ajax_bpolls_widget_get_activity_results', array( $this, 'ajax_get_activity_results' ) );
		add_action( 'wp_ajax_nopriv_bpolls_widget_get_activity_results', array( $this, 'ajax_get_activity_results' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( $hook = '' ) {
		// Enqueue CSS variables.
		wp_enqueue_style(
			'polls-variables-css',
			BPOLLS_PLUGIN_URL . '/public/css/polls-variables.css',
			array(),
			BPOLLS_PLUGIN_VERSION
		);

		// Enqueue widget CSS.
		wp_enqueue_style(
			'polls-widgets-css',
			BPOLLS_PLUGIN_URL . '/public/css/polls-widgets.css',
			array( 'polls-variables-css' ),
			BPOLLS_PLUGIN_VERSION
		);

		// Enqueue widget JS.
		wp_enqueue_script(
			'polls-widgets-js',
			BPOLLS_PLUGIN_URL . '/public/js/polls-widgets.js',
			array( 'jquery' ),
			BPOLLS_PLUGIN_VERSION,
			true
		);

		// Localize script for AJAX.
		wp_localize_script(
			'polls-widgets-js',
			'bpolls_wiget_obj',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'       => wp_create_nonce( 'bpolls_widget_security' ),
				'csv_export_nonce' => wp_create_nonce( 'bp_polls_export_csv_nonce' ),
				'i18n'             => array(
					'loading'  => __( 'Loading...', 'buddypress-polls' ),
					'no_votes' => __( 'No votes yet', 'buddypress-polls' ),
					'error'    => __( 'Failed to load results', 'buddypress-polls' ),
					'votes'    => __( 'Votes', 'buddypress-polls' ),
				),
			)
		);
	}

	/**
	 * Front-end widget display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance data.
	 */
	public function widget( $args, $instance ) {
		global $wpdb, $current_user;

		if ( ! is_user_logged_in() ) {
			return;
		}

		// Get widget wrapper variables.
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';

		// Get activities based on user role.
		if ( ! in_array( 'administrator', (array) $current_user->roles, true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}bp_activity WHERE type = 'activity_poll' AND user_id = %d GROUP BY id HAVING date_recorded = MAX(date_recorded) ORDER BY date_recorded DESC",
					$current_user->ID
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_row(
				"SELECT * FROM {$wpdb->prefix}bp_activity WHERE type = 'activity_poll' GROUP BY id HAVING date_recorded = MAX(date_recorded) ORDER BY date_recorded DESC"
			);
		}

		// Set defaults.
		if ( empty( $instance['activity_default'] ) ) {
			$instance['activity_default'] = ( isset( $results->id ) ) ? $results->id : '';
		}

		if ( empty( $instance['title'] ) ) {
			$instance['title'] = __( 'Poll Graph', 'buddypress-polls' );
		}

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		// Start widget output.
		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$max_activity     = ! empty( $instance['max_activity'] ) ? (int) $instance['max_activity'] : 5;
		$activity_default = ! empty( $instance['activity_default'] ) ? (int) $instance['activity_default'] : '';

		global $activities_template;
		$old_activities_template = $activities_template;

		$act_args = array(
			'action'      => 'activity_poll',
			'type'        => 'activity_poll',
			'per_page'    => $max_activity,
			'show_hidden' => true,
		);

		if ( ! in_array( 'administrator', (array) $current_user->roles, true ) ) {
			$act_args['user_id'] = $current_user->ID;
		}

		?>
		<div class="polls-widget__card">
			<h4 class="polls-widget__title">
				<svg class="polls-widget__title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="18" y1="20" x2="18" y2="10"></line>
					<line x1="12" y1="20" x2="12" y2="4"></line>
					<line x1="6" y1="20" x2="6" y2="14"></line>
				</svg>
				<?php echo esc_html( $title ); ?>
			</h4>

			<?php if ( bp_has_activities( $act_args ) ) : ?>
				<div class="polls-widget__select-group">
					<label class="polls-widget__select-label" for="bpolls-activities-list-<?php echo esc_attr( $this->id ); ?>">
						<?php esc_html_e( 'Select poll to view results:', 'buddypress-polls' ); ?>
					</label>
					<select name="bpolls-show-activity-graph" class="polls-widget__select bpolls-activities-list" id="bpolls-activities-list-<?php echo esc_attr( $this->id ); ?>" data-widget-type="activity-graph">
						<?php
						while ( bp_activities() ) :
							bp_the_activity();
							global $activities_template;
							?>
							<option value="<?php bp_activity_id(); ?>" <?php selected( $activity_default, bp_get_activity_id() ); ?>>
								<?php echo esc_html( wp_strip_all_tags( $activities_template->activity->content ) ); ?>
							</option>
						<?php endwhile; ?>
					</select>
				</div>

				<div class="polls-widget__results-container" data-activity-id="<?php echo esc_attr( $activity_default ); ?>">
					<?php $this->render_activity_poll_results( $activity_default ); ?>
				</div>

				<?php if ( is_admin() ) : ?>
					<div class="polls-widget__actions">
						<a href="<?php echo esc_url( admin_url( '?export_csv=1&buddypress_poll=1&activity_id=' . $activity_default . '&_wpnonce=' . wp_create_nonce( 'bp_polls_export_csv_nonce' ) ) ); ?>"
						   target="_blank"
						   id="export-poll-data"
						   class="polls-widget__btn">
							<svg class="polls-widget__btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
								<polyline points="7 10 12 15 17 10"></polyline>
								<line x1="12" y1="15" x2="12" y2="3"></line>
							</svg>
							<?php esc_html_e( 'Export CSV', 'buddypress-polls' ); ?>
						</a>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="polls-widget__empty">
					<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M3 3v18h18"></path>
						<path d="M18 9l-5 5-4-4-3 3"></path>
					</svg>
					<p class="polls-widget__empty-text"><?php esc_html_e( 'No polls created yet.', 'buddypress-polls' ); ?></p>
					<p class="polls-widget__empty-hint"><?php esc_html_e( 'Create your first poll to see results here.', 'buddypress-polls' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php

		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Restore the global.
		$activities_template = $old_activities_template;
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New instance data.
	 * @param array $old_instance Original instance data.
	 * @return array Updated instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = wp_strip_all_tags( $new_instance['title'] );
		$instance['max_activity']     = absint( $new_instance['max_activity'] );
		$instance['activity_default'] = absint( $new_instance['activity_default'] );

		return $instance;
	}

	/**
	 * Widget admin form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current instance.
	 * @return void
	 */
	public function form( $instance ) {
		global $activities_template;

		// Back up the global.
		$old_activities_template = $activities_template;

		$act_args = array(
			'action' => 'activity_poll',
			'type'   => 'activity_poll',
		);

		$act_default = '';
		if ( bp_has_activities( $act_args ) ) {
			$act_default = $activities_template->activities[0]->id;
		}

		$defaults = array(
			'title'            => __( 'Poll Graph', 'buddypress-polls' ),
			'max_activity'     => 5,
			'activity_default' => $act_default,
		);

		$instance         = wp_parse_args( (array) $instance, $defaults );
		$title            = wp_strip_all_tags( $instance['title'] );
		$max_activity     = absint( $instance['max_activity'] );
		$activity_default = absint( $instance['activity_default'] );
		?>

		<div class="polls-widget-form">
			<p class="polls-widget-form__group">
				<label class="polls-widget-form__label" for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php esc_html_e( 'Title:', 'buddypress-polls' ); ?>
				</label>
				<input
					class="widefat polls-widget-form__input"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					type="text"
					value="<?php echo esc_attr( $title ); ?>"
				/>
			</p>

			<p class="polls-widget-form__group">
				<label class="polls-widget-form__label" for="<?php echo esc_attr( $this->get_field_id( 'max_activity' ) ); ?>">
					<?php esc_html_e( 'Max polls to show:', 'buddypress-polls' ); ?>
				</label>
				<input
					class="small-text polls-widget-form__input"
					id="<?php echo esc_attr( $this->get_field_id( 'max_activity' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'max_activity' ) ); ?>"
					type="number"
					min="1"
					max="20"
					value="<?php echo esc_attr( $max_activity ); ?>"
				/>
				<span class="polls-widget-form__hint"><?php esc_html_e( 'Number of polls in dropdown', 'buddypress-polls' ); ?></span>
			</p>

			<p class="polls-widget-form__group">
				<?php if ( bp_has_activities( $act_args ) ) : ?>
					<label class="polls-widget-form__label" for="<?php echo esc_attr( $this->get_field_id( 'activity_default' ) ); ?>">
						<?php esc_html_e( 'Default poll to display:', 'buddypress-polls' ); ?>
					</label>
					<select
						class="widefat polls-widget-form__select"
						name="<?php echo esc_attr( $this->get_field_name( 'activity_default' ) ); ?>"
						id="<?php echo esc_attr( $this->get_field_id( 'activity_default' ) ); ?>"
					>
						<?php
						while ( bp_activities() ) :
							bp_the_activity();
							global $activities_template;
							?>
							<option value="<?php bp_activity_id(); ?>" <?php selected( $activity_default, bp_get_activity_id() ); ?>>
								<?php echo esc_html( wp_strip_all_tags( $activities_template->activity->content ) ); ?>
							</option>
						<?php endwhile; ?>
					</select>
				<?php else : ?>
					<span class="polls-widget-form__hint">
						<?php esc_html_e( 'No polls are created yet.', 'buddypress-polls' ); ?>
					</span>
				<?php endif; ?>
			</p>
		</div>

		<?php
		// Restore the global.
		$activities_template = $old_activities_template;
	}

	/**
	 * Render activity poll results with modern progress bars.
	 *
	 * @since 4.5.0
	 *
	 * @param int $activity_id Activity ID.
	 */
	public function render_activity_poll_results( $activity_id ) {
		if ( ! $activity_id ) {
			$this->render_empty_state();
			return;
		}

		$activity_meta = bp_activity_get_meta( $activity_id, 'bpolls_meta' );

		if ( empty( $activity_meta ) || ! is_array( $activity_meta ) ) {
			$this->render_empty_state();
			return;
		}

		$poll_options = isset( $activity_meta['poll_option'] ) ? $activity_meta['poll_option'] : array();
		$total_votes  = isset( $activity_meta['poll_total_votes'] ) ? intval( $activity_meta['poll_total_votes'] ) : 0;
		$option_votes = isset( $activity_meta['poll_optn_votes'] ) ? $activity_meta['poll_optn_votes'] : array();

		if ( empty( $poll_options ) || ! is_array( $poll_options ) ) {
			$this->render_empty_state();
			return;
		}

		// Get poll title from activity content.
		$args             = array( 'activity_ids' => $activity_id );
		$activity_details = bp_activity_get_specific( $args );
		$poll_title       = '';

		if ( is_array( $activity_details ) && ! empty( $activity_details['activities'][0]->content ) ) {
			$poll_title = wp_trim_words( wp_strip_all_tags( $activity_details['activities'][0]->content ), 10, '...' );
		}

		// Colors for progress bars (matching Poll Report widget).
		$colors = array(
			'#3b82f6', // blue
			'#10b981', // green
			'#f59e0b', // amber
			'#ef4444', // red
			'#8b5cf6', // purple
			'#ec4899', // pink
			'#06b6d4', // cyan
			'#84cc16', // lime
		);
		?>
		<div class="polls-widget__results" data-activity-id="<?php echo esc_attr( $activity_id ); ?>">
			<?php if ( ! empty( $poll_title ) ) : ?>
				<div class="polls-widget__results-header">
					<span class="polls-widget__results-title"><?php echo esc_html( $poll_title ); ?></span>
					<span class="polls-widget__results-total">
						<?php
						printf(
							/* translators: %s: vote count */
							esc_html( _n( '%s vote', '%s votes', $total_votes, 'buddypress-polls' ) ),
							esc_html( number_format_i18n( $total_votes ) )
						);
						?>
					</span>
				</div>
			<?php endif; ?>

			<div class="polls-widget__results-list">
				<?php
				$color_index = 0;
				foreach ( $poll_options as $opt_key => $opt_value ) :
					$vote_count = isset( $option_votes[ $opt_key ] ) ? intval( $option_votes[ $opt_key ] ) : 0;
					$percentage = $total_votes > 0 ? round( ( $vote_count / $total_votes ) * 100, 1 ) : 0;
					$color      = $colors[ $color_index % count( $colors ) ];
					$color_index++;
					?>
					<div class="polls-widget__result-item">
						<div class="polls-widget__result-info">
							<span class="polls-widget__result-label"><?php echo esc_html( $opt_value ); ?></span>
							<span class="polls-widget__result-stats">
								<span class="polls-widget__result-percent"><?php echo esc_html( $percentage ); ?>%</span>
								<span class="polls-widget__result-count">(<?php echo esc_html( $vote_count ); ?>)</span>
							</span>
						</div>
						<div class="polls-widget__result-bar">
							<div
								class="polls-widget__result-fill"
								style="width: <?php echo esc_attr( $percentage ); ?>%; background-color: <?php echo esc_attr( $color ); ?>;"
							></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render empty state.
	 *
	 * @since 4.5.0
	 *
	 * @param string $message Optional custom message.
	 */
	private function render_empty_state( $message = '' ) {
		if ( empty( $message ) ) {
			$message = __( 'No votes yet', 'buddypress-polls' );
		}
		?>
		<div class="polls-widget__empty">
			<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M3 3v18h18"></path>
				<path d="M18 9l-5 5-4-4-3 3"></path>
			</svg>
			<p class="polls-widget__empty-text"><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for fetching activity poll results.
	 *
	 * @since 4.5.0
	 */
	public function ajax_get_activity_results() {
		// Verify nonce.
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'bpolls_widget_security' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-polls' ) ) );
		}

		$activity_id = isset( $_POST['activity_id'] ) ? absint( $_POST['activity_id'] ) : 0;

		if ( ! $activity_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid activity ID.', 'buddypress-polls' ) ) );
		}

		// Capture the output.
		ob_start();
		$this->render_activity_poll_results( $activity_id );
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
