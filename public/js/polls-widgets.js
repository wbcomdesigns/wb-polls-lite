/**
 * BuddyPress Polls - Widget JavaScript
 *
 * Handles poll widget functionality including AJAX requests,
 * progress bar animations, and dynamic updates.
 *
 * @package BuddyPress_Polls
 * @since 4.5.0
 */

(function($) {
	'use strict';

	/**
	 * Poll Activity Graph Widget Handler
	 * Displays BuddyPress activity poll results with progress bars.
	 */
	class PollActivityGraphWidget {
		constructor(container) {
			this.container = $(container);
			this.select = this.container.find('.bpolls-activities-list');
			this.resultsContainer = this.container.find('.polls-widget__results-container');
			this.config = typeof bpolls_wiget_obj !== 'undefined' ? bpolls_wiget_obj : null;

			if (this.select.length) {
				this.init();
			}
		}

		init() {
			this.bindEvents();
			// Initial results are already rendered by PHP.
			// Animate progress bars on initial load.
			this.animateProgressBars();
		}

		bindEvents() {
			this.select.on('change', (e) => this.handleSelectChange(e));
		}

		handleSelectChange(e) {
			const activityId = $(e.target).val();
			if (activityId) {
				this.loadActivityResults(activityId);
				this.updateExportLink(activityId);
			}
		}

		getTranslation(key, fallback) {
			if (this.config && this.config.i18n && this.config.i18n[key]) {
				return this.config.i18n[key];
			}
			return fallback;
		}

		showLoading() {
			const loadingHtml = `
				<div class="polls-widget__loading">
					<div class="polls-widget__spinner"></div>
					<span class="polls-widget__loading-text">${this.getTranslation('loading', 'Loading...')}</span>
				</div>
			`;
			this.resultsContainer.html(loadingHtml);
		}

		loadActivityResults(activityId) {
			this.showLoading();

			if (!this.config) {
				this.showError(this.getTranslation('error', 'Configuration not found'));
				return;
			}

			$.ajax({
				url: this.config.ajax_url,
				type: 'POST',
				data: {
					action: 'bpolls_widget_get_activity_results',
					activity_id: activityId,
					security: this.config.ajax_nonce
				},
				success: (response) => {
					if (response.success && response.data && response.data.html) {
						this.resultsContainer.html(response.data.html);
						// Animate the progress bars.
						this.animateProgressBars();
					} else {
						const message = response.data && response.data.message
							? response.data.message
							: this.getTranslation('error', 'Failed to load results');
						this.showError(message);
					}
				},
				error: () => {
					this.showError(this.getTranslation('error', 'Request failed'));
				}
			});
		}

		animateProgressBars() {
			// Delay to allow DOM update, then animate bars.
			setTimeout(() => {
				this.resultsContainer.find('.polls-widget__result-fill').each(function() {
					const $fill = $(this);
					const targetWidth = $fill.css('width');
					$fill.css('width', '0');
					setTimeout(() => {
						$fill.css('width', targetWidth);
					}, 50);
				});
			}, 50);
		}

		updateExportLink(activityId) {
			const exportBtn = this.container.find('#export-poll-data');
			if (exportBtn.length && this.config) {
				const baseUrl = exportBtn.attr('href').split('&activity_id=')[0];
				const nonce = this.config.csv_export_nonce || '';
				exportBtn.attr('href', baseUrl + '&activity_id=' + activityId + '&_wpnonce=' + nonce);
			}
		}

		showError(message) {
			const errorHtml = `
				<div class="polls-widget__empty">
					<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p class="polls-widget__empty-text">${message}</p>
				</div>
			`;
			this.resultsContainer.html(errorHtml);
		}
	}

	/**
	 * Poll Report Widget Handler
	 * Displays standalone poll (CPT) results with progress bars.
	 */
	class PollReportWidget {
		constructor(container) {
			this.container = $(container);
			this.select = this.container.find('#poll_seletect');
			this.resultsContainer = this.container.find('.all_polll_result');
			this.config = typeof wbpollWidgetConfig !== 'undefined' ? wbpollWidgetConfig : null;

			this.init();
		}

		init() {
			this.bindEvents();
			// Initial results are already rendered by PHP.
			// Animate progress bars on initial load.
			this.animateProgressBars();
		}

		bindEvents() {
			this.select.on('change', (e) => this.handleSelectChange(e));
		}

		handleSelectChange(e) {
			const pollId = $(e.target).val();
			if (pollId) {
				this.loadPollResults(pollId);
			}
		}

		getTranslation(key, fallback) {
			if (this.config && this.config.i18n && this.config.i18n[key]) {
				return this.config.i18n[key];
			}
			return fallback;
		}

		showLoading() {
			const loadingHtml = `
				<div class="polls-widget__loading">
					<div class="polls-widget__spinner"></div>
					<span class="polls-widget__loading-text">${this.getTranslation('loading', 'Loading results...')}</span>
				</div>
			`;
			this.resultsContainer.html(loadingHtml);
		}

		loadPollResults(pollId) {
			this.showLoading();

			if (!this.config) {
				this.showError(this.getTranslation('error', 'Configuration not found'));
				return;
			}

			$.ajax({
				url: this.config.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wbpoll_widget_get_results',
					poll_id: pollId,
					nonce: this.config.nonce
				},
				success: (response) => {
					if (response.success && response.data && response.data.html) {
						this.resultsContainer.html(response.data.html);
						// Animate the progress bars.
						this.animateProgressBars();
					} else {
						const message = response.data && response.data.message
							? response.data.message
							: this.getTranslation('error', 'Failed to load results');
						this.showError(message);
					}
				},
				error: () => {
					this.showError(this.getTranslation('error', 'Request failed'));
				}
			});
		}

		animateProgressBars() {
			// Delay to allow DOM update, then animate bars.
			setTimeout(() => {
				this.resultsContainer.find('.polls-widget__result-fill').each(function() {
					const $fill = $(this);
					const targetWidth = $fill.css('width');
					$fill.css('width', '0');
					setTimeout(() => {
						$fill.css('width', targetWidth);
					}, 50);
				});
			}, 50);
		}

		showError(message) {
			const errorHtml = `
				<div class="polls-widget__empty">
					<svg class="polls-widget__empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p class="polls-widget__empty-text">${message}</p>
				</div>
			`;
			this.resultsContainer.html(errorHtml);
		}
	}

	/**
	 * Initialize widgets on DOM ready
	 */
	$(document).ready(function() {
		// Initialize Activity Graph Widgets (BuddyPress polls)
		$('.widget_bp_poll_graph_widget, .polls-widget--activity-graph').each(function() {
			new PollActivityGraphWidget(this);
		});

		// Initialize Poll Report Widgets (Standalone polls)
		$('.widget_wb_poll_report, .polls-widget--report').each(function() {
			new PollReportWidget(this);
		});
	});

	// Expose classes for external use
	window.PollActivityGraphWidget = PollActivityGraphWidget;
	window.PollReportWidget = PollReportWidget;

})(jQuery);
