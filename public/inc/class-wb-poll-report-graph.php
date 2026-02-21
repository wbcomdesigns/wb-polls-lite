<?php
/**
 * WB Poll Report Widget
 *
 * Modern widget for displaying standalone poll results.
 *
 * @package Buddypress_Polls
 * @subpackage Buddypress_Polls/public/inc
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Poll Report Widget Class.
 *
 * @since 1.0.0
 */
class Wb_Poll_Report extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			'wb_poll_report',
			__( 'WB Poll Report', 'buddypress-polls' ),
			array(
				'description' => __( 'Display standalone poll results with modern styling', 'buddypress-polls' ),
				'classname'   => 'widget_wb_poll_report polls-widget polls-widget--report',
			)
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// AJAX handler for loading poll results.
		add_action( 'wp_ajax_wbpoll_widget_get_results', array( $this, 'ajax_get_poll_results' ) );
		add_action( 'wp_ajax_nopriv_wbpoll_widget_get_results', array( $this, 'ajax_get_poll_results' ) );
	}

	public function enqueue_admin_scripts( $hook ) {
		if ( 'widgets.php' !== $hook && 'customize.php' !== $hook ) {
			return;
		}

		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		wp_add_inline_script(
			'jquery',
			"jQuery(function($){\n" .
			"\tfunction getSinglePollRow(\$pollType){\n" .
			"\t\tvar \$wrap = \$pollType.closest('.polls-widget-form');\n" .
			"\t\tif(!\$wrap.length){\$wrap = \$pollType.closest('.widget-content, .widget');}\n" .
			"\t\treturn \$wrap.find('.polls-widget-form__single-poll-select').first();\n" .
			"\t}\n" .
			"\tfunction sync(\$pollType){\n" .
			"\t\tvar \$row = getSinglePollRow(\$pollType);\n" .
			"\t\tif(!\$row.length){return;}\n" .
			"\t\tif(\$pollType.val()==='single_poll'){\$row.stop(true,true).slideDown(200);}else{\$row.stop(true,true).slideUp(200);}\n" .
			"\t}\n" .
			"\tfunction syncAll(context){\n" .
			"\t\tvar \$ctx = context ? $(context) : $(document);\n" .
			"\t\t\$ctx.find('.polls-widget-form__poll-type').each(function(){ sync($(this)); });\n" .
			"\t}\n" .
			"\t$(document).on('change', '.polls-widget-form__poll-type', function(){ sync($(this)); });\n" .
			"\t$(document).on('widget-added widget-updated', function(e, widget){ syncAll(widget); });\n" .
			"\tsyncAll(document);\n" .
			"});"
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 4.5.0
	 */
	public function enqueue_scripts() {
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
			'wbpollWidgetConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wbpoll_widget_nonce' ),
				'i18n'    => array(
					'loading' => __( 'Loading results...', 'buddypress-polls' ),
					'error'   => __( 'Failed to load results', 'buddypress-polls' ),
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
		// Get widget wrapper variables.
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title        = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Poll Results', 'buddypress-polls' );
		$poll_id      = ! empty( $instance['wb_activity_default'] ) ? absint( $instance['wb_activity_default'] ) : 0;
		$wb_poll_type = ! empty( $instance['wb_poll_type'] ) ? $instance['wb_poll_type'] : 'all_voted_poll';

		?>
		<div class="polls-widget__card">
			<h4 class="polls-widget__title">
				<svg class="polls-widget__title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
				</svg>
				<?php echo esc_html( $title ); ?>
			</h4>

			<?php if ( 'all_voted_poll' === $wb_poll_type ) : ?>
				<?php $this->render_all_voted_polls( $poll_id ); ?>
			<?php else : ?>
				<?php $this->render_single_poll( $poll_id ); ?>
			<?php endif; ?>
		</div>
		<?php

		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render all voted polls with dropdown.
	 *
	 * @since 4.5.0
	 *
	 * @param int $default_poll_id Default poll ID.
	 */
	private function render_all_voted_polls( $default_poll_id ) {
		$polls = $this->get_voted_polls();

		if ( empty( $polls ) ) {
			$this->render_empty_state();
			return;
		}

		$first_poll_id = $default_poll_id ? $default_poll_id : $polls[0]->ID;
		?>
		<div class="polls-widget__select-group">
			<label class="polls-widget__select-label" for="poll_seletect">
				<?php esc_html_e( 'Select a poll to view results:', 'buddypress-polls' ); ?>
			</label>
			<select id="poll_seletect" class="polls-widget__select" data-widget-type="report">
				<?php foreach ( $polls as $poll ) : ?>
					<option value="<?php echo esc_attr( $poll->ID ); ?>" <?php selected( $first_poll_id, $poll->ID ); ?>>
						<?php echo esc_html( $poll->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="all_polll_result">
			<?php $this->render_poll_results( $first_poll_id ); ?>
		</div>
		<?php
	}

	/**
	 * Render single poll results.
	 *
	 * @since 4.5.0
	 *
	 * @param int $poll_id Poll ID.
	 */
	private function render_single_poll( $poll_id ) {
		if ( ! $poll_id ) {
			$this->render_empty_state( __( 'Please select a poll in widget settings.', 'buddypress-polls' ) );
			return;
		}

		$poll = get_post( $poll_id );
		if ( ! $poll || 'wbpoll' !== $poll->post_type ) {
			$this->render_empty_state( __( 'Poll not found.', 'buddypress-polls' ) );
			return;
		}
		?>
		<div class="all_polll_result">
			<?php $this->render_poll_results( $poll_id ); ?>
		</div>
		<?php
	}

	/**
	 * Render poll results with modern progress bars.
	 *
	 * @since 4.5.0
	 *
	 * @param int $poll_id Poll ID.
	 */
	private function render_poll_results( $poll_id ) {
		$poll = get_post( $poll_id );
		if ( ! $poll ) {
			return;
		}

		$poll_answers = get_post_meta( $poll_id, '_wbpoll_answer', true );
		$poll_answers = is_array( $poll_answers ) ? $poll_answers : array();

		if ( empty( $poll_answers ) ) {
			$this->render_empty_state( __( 'No options available for this poll.', 'buddypress-polls' ) );
			return;
		}

		// Get vote counts per option.
		$total_results       = WBPollHelper::get_pollResult( $poll_id );
		$total_votes         = count( $total_results );
		$poll_answers_weight = array();

		foreach ( $total_results as $result ) {
			$user_ans = maybe_unserialize( $result['user_answer'] );

			if ( is_array( $user_ans ) ) {
				foreach ( $user_ans as $u_ans ) {
					$old_val                       = isset( $poll_answers_weight[ $u_ans ] ) ? intval( $poll_answers_weight[ $u_ans ] ) : 0;
					$poll_answers_weight[ $u_ans ] = ( $old_val + 1 );
				}
			} else {
				$user_ans                         = intval( $user_ans );
				$old_val                          = isset( $poll_answers_weight[ $user_ans ] ) ? intval( $poll_answers_weight[ $user_ans ] ) : 0;
				$poll_answers_weight[ $user_ans ] = ( $old_val + 1 );
			}
		}

		// Colors for progress bars.
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
		<div class="polls-widget__results" data-poll-id="<?php echo esc_attr( $poll_id ); ?>">
			<div class="polls-widget__results-header">
				<span class="polls-widget__results-title"><?php echo esc_html( $poll->post_title ); ?></span>
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

			<div class="polls-widget__results-list">
				<?php
				$color_index = 0;
				foreach ( $poll_answers as $index => $answer_title ) :
					$vote_count = isset( $poll_answers_weight[ $index ] ) ? intval( $poll_answers_weight[ $index ] ) : 0;
					$percentage = $total_votes > 0 ? round( ( $vote_count / $total_votes ) * 100, 1 ) : 0;
					$color      = $colors[ $color_index % count( $colors ) ];
					$color_index++;
					?>
					<div class="polls-widget__result-item">
						<div class="polls-widget__result-info">
							<span class="polls-widget__result-label"><?php echo esc_html( $answer_title ); ?></span>
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
	 * Get polls that have received votes.
	 *
	 * @since 4.5.0
	 *
	 * @return array Array of poll post objects.
	 */
	private function get_voted_polls() {
		$args = array(
			'post_type'      => 'wbpoll',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		return get_posts( $args );
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
			$message = __( 'No polls available.', 'buddypress-polls' );
		}
		?>
		<div class="polls-widget__empty">
			<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
			</svg>
			<p class="polls-widget__empty-text"><?php echo esc_html( $message ); ?></p>
			<p class="polls-widget__empty-hint"><?php esc_html_e( 'Create a poll to see results here.', 'buddypress-polls' ); ?></p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for fetching poll results.
	 *
	 * @since 4.5.0
	 */
	public function ajax_get_poll_results() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wbpoll_widget_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-polls' ) ) );
		}

		$poll_id = isset( $_POST['poll_id'] ) ? absint( $_POST['poll_id'] ) : 0;

		if ( ! $poll_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid poll ID.', 'buddypress-polls' ) ) );
		}

		$poll = get_post( $poll_id );
		if ( ! $poll || 'wbpoll' !== $poll->post_type ) {
			wp_send_json_error( array( 'message' => __( 'Poll not found.', 'buddypress-polls' ) ) );
		}

		// Capture the output of render_poll_results.
		ob_start();
		$this->render_poll_results( $poll_id );
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
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
		$defaults = array(
			'title'               => __( 'Poll Results', 'buddypress-polls' ),
			'wb_activity_default' => '',
			'wb_poll_type'        => 'all_voted_poll',
		);

		$instance            = wp_parse_args( (array) $instance, $defaults );
		$title               = wp_strip_all_tags( $instance['title'] );
		$wb_activity_default = absint( $instance['wb_activity_default'] );
		$wb_poll_type        = sanitize_key( $instance['wb_poll_type'] );

		$polls = $this->get_voted_polls();
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
				<label class="polls-widget-form__label" for="<?php echo esc_attr( $this->get_field_id( 'wb_poll_type' ) ); ?>">
					<?php esc_html_e( 'Display Type:', 'buddypress-polls' ); ?>
				</label>
				<select
					class="widefat polls-widget-form__select polls-widget-form__poll-type"
					name="<?php echo esc_attr( $this->get_field_name( 'wb_poll_type' ) ); ?>"
					id="<?php echo esc_attr( $this->get_field_id( 'wb_poll_type' ) ); ?>"
				>
					<option value="all_voted_poll" <?php selected( $wb_poll_type, 'all_voted_poll' ); ?>>
						<?php esc_html_e( 'All Polls (with dropdown)', 'buddypress-polls' ); ?>
					</option>
					<option value="single_poll" <?php selected( $wb_poll_type, 'single_poll' ); ?>>
						<?php esc_html_e( 'Single Poll', 'buddypress-polls' ); ?>
					</option>
				</select>
				<span class="polls-widget-form__hint">
					<?php esc_html_e( 'Choose how polls are displayed in the widget', 'buddypress-polls' ); ?>
				</span>
			</p>

			<p class="polls-widget-form__group polls-widget-form__single-poll-select" style="<?php echo 'single_poll' === $wb_poll_type ? '' : 'display:none;'; ?>">
				<label class="polls-widget-form__label" for="<?php echo esc_attr( $this->get_field_id( 'wb_activity_default' ) ); ?>">
					<?php esc_html_e( 'Select Poll:', 'buddypress-polls' ); ?>
				</label>
				<?php if ( ! empty( $polls ) ) : ?>
					<select
						class="widefat polls-widget-form__select"
						name="<?php echo esc_attr( $this->get_field_name( 'wb_activity_default' ) ); ?>"
						id="<?php echo esc_attr( $this->get_field_id( 'wb_activity_default' ) ); ?>"
					>
						<option value=""><?php esc_html_e( '— Select a poll —', 'buddypress-polls' ); ?></option>
						<?php foreach ( $polls as $poll ) : ?>
							<option value="<?php echo esc_attr( $poll->ID ); ?>" <?php selected( $wb_activity_default, $poll->ID ); ?>>
								<?php echo esc_html( $poll->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<span class="polls-widget-form__hint">
						<?php esc_html_e( 'No polls created yet.', 'buddypress-polls' ); ?>
					</span>
				<?php endif; ?>
			</p>
		</div>
		<?php
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

		$instance['title']               = wp_strip_all_tags( $new_instance['title'] );
		$instance['wb_activity_default'] = absint( $new_instance['wb_activity_default'] );
		$instance['wb_poll_type']        = sanitize_key( $new_instance['wb_poll_type'] );

		return $instance;
	}
}

/**
 * Register the widget.
 *
 * @since 1.0.0
 */
function bpolls_register_report_widget() {
	register_widget( 'Wb_Poll_Report' );
}
add_action( 'widgets_init', 'bpolls_register_report_widget' );
