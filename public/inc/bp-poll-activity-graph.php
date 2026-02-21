<?php
/**
 * BuddyPress Poll Activity Graph Widget Instance and Ajax.
 *
 * @package Buddypress_Polls
 * @subpackage Buddypress_Polls/public/inc
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'widgets_init',
	function() {
		if ( class_exists('BuddyPress') ) {
			register_widget( 'BP_Poll_Activity_Graph_Widget' );
		}
	}
);

/**
 * Register widgets for poll activity graph.
 *
 * @since 1.0.0
 */
function bpolls_activity_graph_ajax() {
	if ( isset( $_POST['action'] ) && 'bpolls_activity_graph_ajax' === $_POST['action'] ) {
		check_ajax_referer( 'bpolls_widget_security', 'ajax_nonce' );

		// Ensure BuddyPress is active before proceeding.
		if ( ! class_exists( 'BuddyPress' ) || ! function_exists( 'bp_activity_get_specific' ) ) {
			wp_send_json_error( array( 'message' => __( 'BuddyPress is required for this feature.', 'buddypress-polls' ) ) );
		}

		$activity_id      = ( isset( $_POST['actid'] ) ) ? sanitize_text_field(wp_unslash( $_POST['actid'] )) : '';
		$args             = array( 'activity_ids' => $activity_id );
		$activity_details = bp_activity_get_specific( $args );

		if ( is_array( $activity_details ) ) {
			$poll_title = wp_trim_words( $activity_details['activities'][0]->content, 10, '...' );
		} else {
			$poll_title = '';
		}

		$activity_meta = bp_activity_get_meta( $activity_id, 'bpolls_meta' );

		$poll_options = isset( $activity_meta['poll_option'] ) ? $activity_meta['poll_option'] : '';

		if ( ! empty( $poll_options ) && is_array( $poll_options ) ) {
			foreach ( $poll_options as $key => $value ) {

				if ( isset( $activity_meta['poll_total_votes'] ) ) {
					$total_votes = $activity_meta['poll_total_votes'];
				} else {
					$total_votes = 0;
				}

				if ( isset( $activity_meta['poll_optn_votes'] ) && array_key_exists( $key, $activity_meta['poll_optn_votes'] ) ) {
					$this_optn_vote = $activity_meta['poll_optn_votes'][ $key ];
				} else {
					$this_optn_vote = 0;
				}

				if ( 0 != $total_votes ) {
					$vote_percent = round( $this_optn_vote / $total_votes * 100, 2 );
				} else {
					$vote_percent = __( '(no votes yet)', 'buddypress-polls' );
				}

				$bpolls_votes_txt = '(&nbsp;' . $this_optn_vote . '&nbsp;' . _x( 'of', 'Poll Activity Graph', 'buddypress-polls' ) . '&nbsp;' . $total_votes . '&nbsp;)';

				$uptd_votes[ $activity_id ][] = array(
					'poll_title' => $poll_title,
					'label'      => $value,
					'y'          => $vote_percent,
					'color'      => bpolls_color(),

				);
			}
		}

		echo wp_json_encode( $uptd_votes );
		die;
	}
}
add_action( 'wp_ajax_bpolls_activity_graph_ajax', 'bpolls_activity_graph_ajax' );
add_action( 'wp_ajax_nopriv_bpolls_activity_graph_ajax', 'bpolls_activity_graph_ajax' );

/**
 * Bpolls_color
 *
 * @since 1.0.0
 */
function bpolls_color() {
	return '#' . random_color_part() . random_color_part() . random_color_part();
}

/**
 * Random_color_part
 *
 * @since 1.0.0
 */
function random_color_part() {
	return str_pad( dechex( wp_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT );
}
