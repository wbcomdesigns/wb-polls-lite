/**
 * BuddyPress Polls - Dashboard JavaScript
 *
 * Modern class-based dashboard controller for polls management.
 * Handles filter tabs, slide-in panel, poll actions, and form handling.
 *
 * @package BuddyPress_Polls
 * @since 4.5.0
 */

/* global wbpollpublic, wp, Sortable */

// i18n helper - fallback if wp.i18n not available
const __ = (typeof wp !== 'undefined' && wp.i18n && wp.i18n.__) ? wp.i18n.__ : function(text) { return text; };

/**
 * Polls Dashboard Controller
 * Main class for managing the polls dashboard functionality.
 */
class PollsDashboard {
	/**
	 * Initialize the dashboard.
	 */
	constructor() {
		// Cache DOM elements
		this.elements = {
			dashboard: document.querySelector('.polls-dashboard'),
			tabs: document.querySelectorAll('.polls-tab'),
			grid: document.querySelector('.polls-grid'),
			cards: document.querySelectorAll('.poll-card'),
			panel: document.getElementById('polls-panel'),
			panelOverlay: document.getElementById('polls-panel-overlay'),
			createBtn: document.getElementById('polls-create-btn'),
			createBtnEmpty: document.getElementById('polls-create-btn-empty'),
			closeBtn: document.getElementById('polls-panel-close'),
			form: document.getElementById('polls-form'),
			answerList: document.getElementById('type_text'),
			answerLists: document.querySelectorAll('.polls-answers__list'),
			statsPublished: document.querySelector('.polls-stat[data-status="publish"]'),
			statsPending: document.querySelector('.polls-stat[data-status="pending"]'),
			statsDrafts: document.querySelector('.polls-stat[data-status="draft"]'),
			statsTotal: document.querySelector('.polls-stat[data-status="all"]'),
			toastContainer: null,
		};

		// State
		this.state = {
			currentFilter: 'all',
			isPanelOpen: false,
			editingPollId: null,
			sortables: [],
		};

		// Only initialize if dashboard exists
		if (this.elements.dashboard) {
			this.init();
		}
	}

	/**
	 * Initialize all event listeners and features.
	 */
	init() {
		this.createToastContainer();
		this.bindTabEvents();
		this.bindPanelEvents();
		this.bindCardActions();
		this.bindShortcodeCopy();
		this.bindFormEvents();
		this.initSortable();
		this.handleUrlParams();
		this.updateStats();
	}

	/**
	 * Create the toast notification container.
	 */
	createToastContainer() {
		// Check if container already exists
		let container = document.querySelector('.polls-toast-container');
		if (!container) {
			container = document.createElement('div');
			container.className = 'polls-toast-container';
			container.setAttribute('aria-live', 'polite');
			container.setAttribute('aria-atomic', 'true');
			document.body.appendChild(container);
		}
		this.elements.toastContainer = container;
	}

	// ========================================
	// Tab Filtering
	// ========================================

	/**
	 * Bind filter tab click events.
	 * Note: Tabs are now anchor links that navigate to URLs with status/paged params.
	 * The click handler is kept for keyboard accessibility but no longer prevents default.
	 */
	bindTabEvents() {
		this.elements.tabs.forEach(tab => {
			// Tabs are now anchor links - we just need keyboard navigation
			tab.addEventListener('keydown', (e) => this.handleTabKeydown(e));
		});

		// Get current filter from URL for state tracking
		const urlParams = new URLSearchParams(window.location.search);
		this.state.currentFilter = urlParams.get('status') || 'all';
	}

	/**
	 * Handle tab click event.
	 * Note: Tabs are now anchor links, so this is only called for programmatic tab activation.
	 * @param {Event} e - Click event
	 */
	handleTabClick(e) {
		// Allow default navigation for anchor links
		const tab = e.currentTarget;
		const filter = tab.dataset.status || 'all';
		this.state.currentFilter = filter;
	}

	/**
	 * Handle keyboard navigation for tabs.
	 * @param {KeyboardEvent} e - Keyboard event
	 */
	handleTabKeydown(e) {
		const tabs = Array.from(this.elements.tabs);
		const currentIndex = tabs.indexOf(e.currentTarget);
		let nextIndex;

		switch (e.key) {
			case 'ArrowRight':
			case 'ArrowDown':
				e.preventDefault();
				nextIndex = (currentIndex + 1) % tabs.length;
				break;
			case 'ArrowLeft':
			case 'ArrowUp':
				e.preventDefault();
				nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
				break;
			case 'Home':
				e.preventDefault();
				nextIndex = 0;
				break;
			case 'End':
				e.preventDefault();
				nextIndex = tabs.length - 1;
				break;
			case 'Enter':
			case ' ':
				// Let anchor links handle Enter/Space naturally
				return;
			default:
				return;
		}

		// Focus the next tab
		tabs[nextIndex].focus();
	}

	/**
	 * Set active state on tab.
	 * @param {HTMLElement} activeTab - Tab to activate
	 */
	setActiveTab(activeTab) {
		this.elements.tabs.forEach(tab => {
			tab.setAttribute('aria-selected', 'false');
			tab.classList.remove('is-active');
		});

		activeTab.setAttribute('aria-selected', 'true');
		activeTab.classList.add('is-active');
	}

	/**
	 * Filter poll cards by status.
	 * Note: With server-side pagination, this only handles visual updates after
	 * card status changes (e.g., hiding a card that was just unpublished when
	 * viewing the Published tab).
	 * @param {string} filter - Filter value (all, publish, pending, draft)
	 */
	filterCards(filter) {
		// Get current filter from URL if not provided
		if (!filter) {
			const urlParams = new URLSearchParams(window.location.search);
			filter = urlParams.get('status') || 'all';
		}

		this.elements.cards.forEach(card => {
			const status = card.dataset.status;
			const shouldShow = filter === 'all' || status === filter;

			card.style.display = shouldShow ? '' : 'none';
			card.setAttribute('aria-hidden', !shouldShow);
		});

		// Show empty state if no cards match
		this.checkEmptyState(filter);
	}

	/**
	 * Check and show empty state if no cards visible.
	 * @param {string} filter - Current filter
	 */
	checkEmptyState(filter) {
		const visibleCards = Array.from(this.elements.cards).filter(
			card => card.style.display !== 'none'
		);

		let emptyState = this.elements.grid.querySelector('.polls-empty');

		if (visibleCards.length === 0) {
			if (!emptyState) {
				emptyState = this.createEmptyState(filter);
				this.elements.grid.appendChild(emptyState);
			}
			emptyState.style.display = '';
		} else if (emptyState) {
			emptyState.style.display = 'none';
		}
	}

	/**
	 * Create empty state element using safe DOM methods.
	 * @param {string} filter - Current filter
	 * @returns {HTMLElement} Empty state element
	 */
	createEmptyState(filter) {
		const wrapper = document.createElement('div');
		wrapper.className = 'polls-empty';
		wrapper.style.gridColumn = '1 / -1';

		const messages = {
			all: __('No polls yet', 'buddypress-polls'),
			publish: __('No published polls', 'buddypress-polls'),
			pending: __('No pending polls', 'buddypress-polls'),
			draft: __('No draft polls', 'buddypress-polls'),
		};

		// Create SVG icon
		const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		icon.setAttribute('class', 'polls-empty__icon');
		icon.setAttribute('viewBox', '0 0 24 24');
		icon.setAttribute('fill', 'none');
		icon.setAttribute('stroke', 'currentColor');
		icon.setAttribute('stroke-width', '1.5');
		const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		path.setAttribute('d', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2');
		icon.appendChild(path);
		wrapper.appendChild(icon);

		// Create title
		const title = document.createElement('h3');
		title.className = 'polls-empty__title';
		title.textContent = messages[filter] || messages.all;
		wrapper.appendChild(title);

		// Create text
		const text = document.createElement('p');
		text.className = 'polls-empty__text';
		text.textContent = __('Create your first poll to get started.', 'buddypress-polls');
		wrapper.appendChild(text);

		return wrapper;
	}

	/**
	 * Update stats bar counts.
	 * Note: With server-side pagination, we no longer recalculate stats from DOM cards.
	 * The server renders the correct totals. This method is kept for backward compatibility
	 * and only updates after card status changes (delete, publish, unpublish).
	 */
	updateStats() {
		// With server-side pagination, stats are rendered by the server.
		// We only need to update counts when a card is deleted or status changes.
		// The server-rendered totals in the stats bar and tabs are authoritative.
		// This method now only decrements/adjusts counts after local actions.

		// Count visible cards for current page display only (not total counts)
		const visibleCards = Array.from(this.elements.cards).filter(
			card => card.style.display !== 'none'
		);

		// Note: Tab counts are links to server pages, so they maintain correct totals.
		// We don't update them here as they reflect database totals, not page counts.
	}

	// ========================================
	// Slide-in Panel
	// ========================================

	/**
	 * Bind panel open/close events.
	 */
	bindPanelEvents() {
		// Create button (header)
		if (this.elements.createBtn) {
			this.elements.createBtn.addEventListener('click', (e) => {
				e.preventDefault();
				this.openPanel();
			});
		}

		// Create button (empty state)
		if (this.elements.createBtnEmpty) {
			this.elements.createBtnEmpty.addEventListener('click', (e) => {
				e.preventDefault();
				this.openPanel();
			});
		}

		// Close button
		if (this.elements.closeBtn) {
			this.elements.closeBtn.addEventListener('click', (e) => {
				e.preventDefault();
				this.closePanel();
			});
		}

		// Overlay click
		if (this.elements.panelOverlay) {
			this.elements.panelOverlay.addEventListener('click', () => {
				this.closePanel();
			});
		}

		// Escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && this.state.isPanelOpen) {
				this.closePanel();
			}
		});

		// Cancel button
		const cancelBtn = document.getElementById('polls-panel-cancel');
		if (cancelBtn) {
			cancelBtn.addEventListener('click', (e) => {
				e.preventDefault();
				this.closePanel();
			});
		}
	}

	/**
	 * Open the slide-in panel.
	 * @param {string|null} pollId - Poll ID for editing, null for create
	 */
	openPanel(pollId = null) {
		if (!this.elements.panel) return;

		this.state.isPanelOpen = true;
		this.state.editingPollId = pollId;

		// Add classes and update accessibility
		this.elements.panel.classList.add('is-open');
		this.elements.panel.setAttribute('aria-hidden', 'false');
		if (this.elements.panelOverlay) {
			this.elements.panelOverlay.classList.add('is-visible');
		}

		// Prevent body scroll
		document.body.style.overflow = 'hidden';

		// Update panel title
		const title = this.elements.panel.querySelector('.polls-panel__title');
		if (title) {
			title.textContent = pollId ? __('Edit Poll', 'buddypress-polls') : __('Create New Poll', 'buddypress-polls');
		}

		// Update submit button text
		const submitBtn = document.getElementById('polls-form-submit');
		if (submitBtn) {
			const btnText = submitBtn.querySelector('.polls-btn__text');
			if (btnText) {
				btnText.textContent = pollId ? __('Update Poll', 'buddypress-polls') : __('Create Poll', 'buddypress-polls');
			}
		}

		// Focus first input
		setTimeout(() => {
			const firstInput = this.elements.panel.querySelector('input[type="text"], textarea');
			if (firstInput) firstInput.focus();
		}, 350);

		// Load poll data if editing
		if (pollId) {
			this.loadPollData(pollId);
		} else {
			this.resetForm();
		}
	}

	/**
	 * Close the slide-in panel.
	 */
	closePanel() {
		if (!this.elements.panel) return;

		this.state.isPanelOpen = false;
		this.state.editingPollId = null;

		// Remove classes and update accessibility
		this.elements.panel.classList.remove('is-open');
		this.elements.panel.setAttribute('aria-hidden', 'true');
		if (this.elements.panelOverlay) {
			this.elements.panelOverlay.classList.remove('is-visible');
		}

		// Restore body scroll
		document.body.style.overflow = '';

		// Clear URL params
		if (window.history && window.history.pushState) {
			const url = window.location.pathname;
			window.history.pushState({}, '', url);
		}
	}

	// ========================================
	// Card Actions
	// ========================================

	/**
	 * Bind poll card action buttons.
	 */
	bindCardActions() {
		// Use event delegation
		if (this.elements.grid) {
			this.elements.grid.addEventListener('click', (e) => {
				const action = e.target.closest('[data-action]');
				if (!action) return;

				e.preventDefault();
				const actionType = action.dataset.action;
				const pollId = action.dataset.id;
				const card = action.closest('.poll-card');

				this.handleCardAction(actionType, pollId, action, card);
			});
		}
	}

	/**
	 * Bind shortcode copy button events.
	 */
	bindShortcodeCopy() {
		if (!this.elements.grid) return;

		this.elements.grid.addEventListener('click', (e) => {
			const copyBtn = e.target.closest('.poll-card__shortcode-copy');
			if (!copyBtn) return;

			e.preventDefault();
			const pollId = copyBtn.dataset.pollId;

			if (!pollId) return;

			// Construct the shortcode
			const shortcode = '[wbpoll id="' + pollId + '"]';

			// Copy to clipboard with fallback for non-HTTPS contexts
			this.copyToClipboard(shortcode).then(() => {
				// Show success state
				copyBtn.classList.add('copied');

				// Show toast
				this.showToast(__('Shortcode copied!', 'buddypress-polls'), 'success');

				// Reset after 2 seconds
				setTimeout(() => {
					copyBtn.classList.remove('copied');
				}, 2000);
			}).catch(err => {
				console.error('Failed to copy:', err);
				this.showToast(__('Failed to copy shortcode', 'buddypress-polls'), 'error');
			});
		});
	}

	/**
	 * Copy text to clipboard with fallback for non-HTTPS contexts.
	 * @param {string} text - Text to copy
	 * @returns {Promise} Promise that resolves on success
	 */
	copyToClipboard(text) {
		// Use modern Clipboard API if available (requires HTTPS)
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}

		// Fallback for non-HTTPS contexts (e.g., Local development)
		return new Promise((resolve, reject) => {
			const textarea = document.createElement('textarea');
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.left = '-9999px';
			textarea.style.top = '0';
			textarea.setAttribute('readonly', '');
			document.body.appendChild(textarea);

			try {
				textarea.select();
				textarea.setSelectionRange(0, text.length);
				const success = document.execCommand('copy');
				document.body.removeChild(textarea);

				if (success) {
					resolve();
				} else {
					reject(new Error('execCommand copy failed'));
				}
			} catch (err) {
				document.body.removeChild(textarea);
				reject(err);
			}
		});
	}

	/**
	 * Handle poll card action.
	 * @param {string} actionType - Action type (view, edit, pause, delete, etc.)
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	handleCardAction(actionType, pollId, btn, card) {
		switch (actionType) {
			case 'view':
				this.viewPoll(pollId);
				break;
			case 'edit':
				this.editPoll(pollId, btn);
				break;
			case 'pause':
				this.pausePoll(pollId, btn, card);
				break;
			case 'resume':
				this.resumePoll(pollId, btn, card);
				break;
			case 'delete':
				this.deletePoll(pollId, btn, card);
				break;
			case 'publish':
				this.publishPoll(pollId, btn, card);
				break;
			case 'unpublish':
				this.unpublishPoll(pollId, btn, card);
				break;
		}
	}

	/**
	 * View poll (navigate to single poll page).
	 * @param {string} pollId - Poll ID
	 */
	viewPoll(pollId) {
		const viewBtn = document.querySelector('[data-action="view"][data-id="' + pollId + '"]');
		if (viewBtn && viewBtn.dataset.url) {
			window.location.href = viewBtn.dataset.url;
		}
	}

	/**
	 * Edit poll (open panel with poll data).
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 */
	editPoll(pollId, btn) {
		// Show loading state
		this.setButtonLoading(btn, true);

		// Load poll data and open panel
		this.openPanel(pollId);

		// Restore button state after panel opens
		setTimeout(() => {
			this.setButtonLoading(btn, false);
		}, 500);
	}

	/**
	 * Pause a poll.
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	pausePoll(pollId, btn, card) {
		this.apiRequest('/wp-json/wbpoll/v1/listpoll/pause/poll', {
			pollid: pollId,
			_wbpoll_pause_poll: 1,
		}, btn).then(response => {
			if (response.success) {
				this.showToast(__('Poll paused', 'buddypress-polls'), 'success');
				this.updateCardStatus(card, 'paused');
			}
		});
	}

	/**
	 * Resume a paused poll.
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	resumePoll(pollId, btn, card) {
		this.apiRequest('/wp-json/wbpoll/v1/listpoll/pause/poll', {
			pollid: pollId,
			_wbpoll_pause_poll: 0,
		}, btn).then(response => {
			if (response.success) {
				this.showToast(__('Poll resumed', 'buddypress-polls'), 'success');
				this.updateCardStatus(card, 'publish');
			}
		});
	}

	/**
	 * Delete a poll.
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	deletePoll(pollId, btn, card) {
		if (!confirm(__('Are you sure you want to delete this poll?', 'buddypress-polls'))) {
			return;
		}

		this.apiRequest('/wp-json/wbpoll/v1/listpoll/delete/poll', {
			pollid: pollId,
		}, btn).then(response => {
			if (response.success) {
				this.showToast(__('Poll deleted', 'buddypress-polls'), 'success');
				card.remove();
				this.updateStats();
				this.checkEmptyState(this.state.currentFilter);
			}
		});
	}

	/**
	 * Publish a draft poll.
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	publishPoll(pollId, btn, card) {
		this.apiRequest('/wp-json/wbpoll/v1/listpoll/publish/poll', {
			pollid: pollId,
		}, btn).then(response => {
			if (response.success) {
				this.showToast(__('Poll published', 'buddypress-polls'), 'success');
				this.updateCardStatus(card, 'publish');
			}
		});
	}

	/**
	 * Unpublish a poll (set to draft).
	 * @param {string} pollId - Poll ID
	 * @param {HTMLElement} btn - Button element
	 * @param {HTMLElement} card - Card element
	 */
	unpublishPoll(pollId, btn, card) {
		this.apiRequest('/wp-json/wbpoll/v1/listpoll/unpublish/poll', {
			pollid: pollId,
		}, btn).then(response => {
			if (response.success) {
				this.showToast(__('Poll unpublished', 'buddypress-polls'), 'success');
				this.updateCardStatus(card, 'draft');
			}
		});
	}

	/**
	 * Update card status visually using safe DOM methods.
	 * @param {HTMLElement} card - Card element
	 * @param {string} newStatus - New status
	 */
	updateCardStatus(card, newStatus) {
		card.dataset.status = newStatus;

		const statusBadge = card.querySelector('.poll-card__status');
		if (statusBadge) {
			// Remove old status classes
			statusBadge.classList.remove(
				'poll-card__status--active',
				'poll-card__status--paused',
				'poll-card__status--draft',
				'poll-card__status--pending'
			);

			// Add new status class and update text
			const statusMap = {
				publish: { class: 'poll-card__status--active', text: __('Active', 'buddypress-polls') },
				paused: { class: 'poll-card__status--paused', text: __('Paused', 'buddypress-polls') },
				draft: { class: 'poll-card__status--draft', text: __('Draft', 'buddypress-polls') },
				pending: { class: 'poll-card__status--pending', text: __('Pending', 'buddypress-polls') },
			};

			const statusInfo = statusMap[newStatus] || statusMap.draft;
			statusBadge.classList.add(statusInfo.class);

			// Clear and rebuild content safely
			while (statusBadge.firstChild) {
				statusBadge.removeChild(statusBadge.firstChild);
			}

			const dot = document.createElement('span');
			dot.className = 'poll-card__status-dot';
			statusBadge.appendChild(dot);
			statusBadge.appendChild(document.createTextNode(statusInfo.text));
		}

		// Update pause/resume button
		const pauseBtn = card.querySelector('[data-action="pause"], [data-action="resume"]');
		if (pauseBtn) {
			if (newStatus === 'paused') {
				pauseBtn.dataset.action = 'resume';
				pauseBtn.setAttribute('data-tooltip', __('Resume', 'buddypress-polls'));
			} else {
				pauseBtn.dataset.action = 'pause';
				pauseBtn.setAttribute('data-tooltip', __('Pause', 'buddypress-polls'));
			}
		}

		// Re-filter if needed
		this.filterCards(this.state.currentFilter);
		this.updateStats();
	}

	/**
	 * Set button loading state.
	 * @param {HTMLElement} btn - Button element
	 * @param {boolean} loading - Loading state
	 */
	setButtonLoading(btn, loading) {
		if (!btn) return;

		if (loading) {
			btn._originalContent = Array.from(btn.childNodes);
			while (btn.firstChild) {
				btn.removeChild(btn.firstChild);
			}
			const spinner = document.createElement('span');
			spinner.className = 'polls-spinner';
			spinner.style.cssText = 'width:16px;height:16px;border-width:2px;';
			btn.appendChild(spinner);
			btn.style.pointerEvents = 'none';
		} else {
			while (btn.firstChild) {
				btn.removeChild(btn.firstChild);
			}
			if (btn._originalContent) {
				btn._originalContent.forEach(node => {
					btn.appendChild(node.cloneNode(true));
				});
			}
			btn.style.pointerEvents = '';
		}
	}

	// ========================================
	// API Helper
	// ========================================

	/**
	 * Make API request with loading state.
	 * @param {string} endpoint - API endpoint
	 * @param {Object} data - Request data
	 * @param {HTMLElement} btn - Button to show loading state
	 * @returns {Promise} API response promise
	 */
	apiRequest(endpoint, data, btn = null) {
		const siteUrl = typeof wbpollpublic !== 'undefined' ? wbpollpublic.url : '';
		const nonce = typeof wbpollpublic !== 'undefined' ? wbpollpublic.rest_nonce : '';

		// Show loading state
		if (btn) {
			this.setButtonLoading(btn, true);
		}

		return fetch(siteUrl + endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(data),
		})
			.then(response => response.json())
			.then(result => {
				if (!result.success) {
					this.showToast(result.message || __('An error occurred', 'buddypress-polls'), 'error');
				}
				return result;
			})
			.catch(error => {
				console.error('API Error:', error);
				this.showToast(__('Network error. Please try again.', 'buddypress-polls'), 'error');
				return { success: false };
			})
			.finally(() => {
				// Restore button state
				if (btn) {
					this.setButtonLoading(btn, false);
				}
			});
	}

	// ========================================
	// Form Handling
	// ========================================

	/**
	 * Bind form events.
	 */
	bindFormEvents() {
		// Form submit handler
		const form = document.getElementById('polls-form');
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleFormSubmit(form);
			});
		}

		// Poll type radio buttons
		const pollTypeRadios = document.querySelectorAll('input[name="poll_type"]');
		pollTypeRadios.forEach(radio => {
			radio.addEventListener('change', (e) => this.handlePollTypeChange(e.target.value));
		});

		// Poll duration/expiry radio buttons
		const expiryRadios = document.querySelectorAll('input[name="_wbpoll_never_expire"]');
		expiryRadios.forEach(radio => {
			radio.addEventListener('change', (e) => this.handleExpiryChange(e.target.value));
		});

		// Add answer button
		const addAnswerBtn = document.getElementById('polls-add-answer');
		if (addAnswerBtn) {
			addAnswerBtn.addEventListener('click', (e) => {
				e.preventDefault();
				this.addAnswerField();
			});
		}

		// Remove answer buttons (event delegation)
		const answersContainer = document.getElementById('polls-answers');
		if (answersContainer) {
			answersContainer.addEventListener('click', (e) => {
				const removeBtn = e.target.closest('.polls-answer__remove');
				if (!removeBtn) return;

				const answerEl = removeBtn.closest('.polls-answer');
				if (!answerEl) return;

				e.preventDefault();
				this.removeAnswerField(answerEl);
			});
		}

		// Initialize character counters
		this.initCharacterCounters();
	}

	/**
	 * Initialize character counters for inputs with limits.
	 */
	initCharacterCounters() {
		// Title character counter
		const titleInput = document.getElementById('polltitle');
		if (titleInput && titleInput.hasAttribute('data-char-limit')) {
			this.bindCharacterCounter(titleInput);
			this.updateCharacterCount(titleInput);
		}

		// Description character counter (for plain textarea)
		const descInput = document.getElementById('poll-content');
		if (descInput && descInput.tagName === 'TEXTAREA' && descInput.hasAttribute('data-char-limit')) {
			this.bindCharacterCounter(descInput);
			this.updateCharacterCount(descInput);
		}

		// Description character counter (for TinyMCE)
		const descCounter = document.querySelector('.polls-form__char-counter[data-for="poll-content"]');
		if (descCounter && typeof tinyMCE !== 'undefined') {
			const limit = parseInt(descCounter.dataset.limit, 10);
			if (limit > 0) {
				// Wait for TinyMCE to initialize
				setTimeout(() => {
					this.initTinyMCECharCounter(limit);
				}, 500);
			}
		}

		// Answer character limits - use event delegation
		const answersContainer = document.getElementById('polls-answers');
		if (answersContainer) {
			const answerLimit = parseInt(answersContainer.dataset.answerLimit, 10) || 0;
			if (answerLimit > 0) {
				// Apply maxlength to existing answer inputs
				this.applyAnswerLimits(answerLimit);

				// Use event delegation to handle dynamic answer inputs
				answersContainer.addEventListener('input', (e) => {
					if (e.target.classList.contains('wbpoll_answer')) {
						// Enforce maxlength
						if (e.target.value.length > answerLimit) {
							e.target.value = e.target.value.substring(0, answerLimit);
						}
					}
				});
			}
		}
	}

	/**
	 * Apply character limits to all answer inputs.
	 * @param {number} limit - Character limit
	 */
	applyAnswerLimits(limit) {
		const answerInputs = document.querySelectorAll('.wbpoll_answer');
		answerInputs.forEach(input => {
			input.setAttribute('maxlength', limit);
			input.setAttribute('data-char-limit', limit);
		});
	}

	/**
	 * Bind character counter events to an input.
	 * @param {HTMLElement} input - Input element
	 */
	bindCharacterCounter(input) {
		input.addEventListener('input', () => this.updateCharacterCount(input));
		input.addEventListener('paste', () => {
			setTimeout(() => this.updateCharacterCount(input), 0);
		});
	}

	/**
	 * Update character count display for an input.
	 * @param {HTMLElement} input - Input element
	 */
	updateCharacterCount(input) {
		const counterId = input.id;
		const counter = document.querySelector(`.polls-form__char-counter[data-for="${counterId}"] .char-count`);
		if (counter) {
			const count = input.value.length;
			counter.textContent = count;

			// Add warning class if near limit
			const limit = parseInt(input.getAttribute('data-char-limit'), 10);
			const counterContainer = counter.closest('.polls-form__char-counter');
			if (counterContainer) {
				if (count >= limit) {
					counterContainer.classList.add('polls-form__char-counter--limit');
					counterContainer.classList.remove('polls-form__char-counter--warning');
				} else if (count >= limit * 0.9) {
					counterContainer.classList.add('polls-form__char-counter--warning');
					counterContainer.classList.remove('polls-form__char-counter--limit');
				} else {
					counterContainer.classList.remove('polls-form__char-counter--warning', 'polls-form__char-counter--limit');
				}
			}
		}
	}

	/**
	 * Initialize TinyMCE character counter.
	 * @param {number} limit - Character limit
	 */
	initTinyMCECharCounter(limit) {
		const editor = tinyMCE.get('poll-content');
		if (!editor) return;

		const updateCount = () => {
			const content = editor.getContent({ format: 'text' });
			const count = content.length;
			const counter = document.querySelector('.polls-form__char-counter[data-for="poll-content"] .char-count');
			if (counter) {
				counter.textContent = count;

				const counterContainer = counter.closest('.polls-form__char-counter');
				if (counterContainer) {
					if (count >= limit) {
						counterContainer.classList.add('polls-form__char-counter--limit');
						counterContainer.classList.remove('polls-form__char-counter--warning');
					} else if (count >= limit * 0.9) {
						counterContainer.classList.add('polls-form__char-counter--warning');
						counterContainer.classList.remove('polls-form__char-counter--limit');
					} else {
						counterContainer.classList.remove('polls-form__char-counter--warning', 'polls-form__char-counter--limit');
					}
				}
			}

			// Enforce limit by truncating
			if (count > limit) {
				const truncated = content.substring(0, limit);
				editor.setContent(truncated);
				// Move cursor to end
				editor.selection.select(editor.getBody(), true);
				editor.selection.collapse(false);
			}
		};

		// Bind to editor events
		editor.on('keyup change paste', updateCount);
		updateCount(); // Initial count
	}

	/**
	 * Handle poll type change - show/hide appropriate answer sections.
	 * @param {string} pollType - Selected poll type
	 */
	handlePollTypeChange(pollType) {
		console.log('[PollsDashboard] Poll type changed to:', pollType);

		// Update selected state on cards
		const typeCards = document.querySelectorAll('.polls-form__type-card');
		typeCards.forEach(card => {
			const radio = card.querySelector('input[type="radio"]');
			if (radio && radio.value === pollType) {
				card.classList.add('polls-form__type-card--selected');
			} else {
				card.classList.remove('polls-form__type-card--selected');
			}
		});

		// Update hidden select for backward compatibility
		const hiddenSelect = document.getElementById('poll_type');
		if (hiddenSelect) {
			hiddenSelect.value = pollType;
		}

		// Show/hide answer lists based on type
		const typeMap = {
			'default': 'type_text',
			'image': 'type_image',
			'video': 'type_video',
			'audio': 'type_audio',
			'html': 'type_html',
		};

		const answerLists = document.querySelectorAll('.polls-answers__list');
		answerLists.forEach(list => {
			const listType = list.dataset.pollType || list.id.replace('type_', '');
			if (listType === pollType || (pollType === 'default' && list.id === 'type_text')) {
				list.style.display = '';
			} else {
				list.style.display = 'none';
			}
		});

		// Update current answer list reference
		const listId = typeMap[pollType] || 'type_text';
		this.elements.answerList = document.getElementById(listId);

		// Re-initialize sortable for the new list
		this.initSortable();
	}

	/**
	 * Handle expiry option change - show/hide date fields and "Show Results After Expire" option.
	 * @param {string} value - '1' for never expires, '0' for set duration
	 */
	handleExpiryChange(value) {
		const datesContainer = document.getElementById('polls-dates');
		const showResultAfterExpire = document.getElementById('polls-show-result-after-expire');
		const expiryOptions = document.querySelectorAll('.polls-form__expiry-option');

		// Update selected state on options
		expiryOptions.forEach(option => {
			const radio = option.querySelector('input[type="radio"]');
			if (radio && radio.value === value) {
				option.classList.add('polls-form__expiry-option--selected');
			} else {
				option.classList.remove('polls-form__expiry-option--selected');
			}
		});

		// Show/hide date fields
		if (datesContainer) {
			datesContainer.style.display = value === '1' ? 'none' : '';
		}

		// Show/hide "Show Results After Expire" option
		// When "Never Expire" is selected, this option is irrelevant
		if (showResultAfterExpire) {
			showResultAfterExpire.style.display = value === '1' ? 'none' : '';

			// When hiding, reset to "No" since the setting doesn't apply
			if (value === '1') {
				const noRadio = showResultAfterExpire.querySelector('input[name="_wbpoll_show_result_before_expire"][value="0"]');
				if (noRadio) {
					noRadio.checked = true;
				}
			}
		}
	}

	/**
	 * Get currently selected poll type from radio buttons.
	 * @returns {string} - Poll type value
	 */
	getSelectedPollType() {
		const checkedRadio = document.querySelector('input[name="poll_type"]:checked');
		return checkedRadio ? checkedRadio.value : 'default';
	}

	/**
	 * Handle form submission via AJAX.
	 * @param {HTMLFormElement} form - The form element
	 */
	handleFormSubmit(form) {
		const submitBtn = document.getElementById('polls-form-submit');
		const pollId = document.getElementById('poll_id')?.value || '';
		const isEditing = !!pollId;

		// Validate required fields
		const title = document.getElementById('polltitle')?.value?.trim();
		if (!title) {
			this.showToast(__('Please enter a poll title', 'buddypress-polls'), 'error');
			document.getElementById('polltitle')?.focus();
			return;
		}

		// Get poll type from radio buttons
		const pollType = this.getSelectedPollType();

		// Get content (handle TinyMCE if present)
		let content = '';
		if (typeof tinyMCE !== 'undefined') {
			const editor = tinyMCE.get('poll-content');
			if (editor) {
				content = editor.getContent();
			} else {
				content = document.getElementById('poll-content')?.value || '';
			}
		} else {
			content = document.getElementById('poll-content')?.value || '';
		}

		// Get answers based on poll type
		const typeMap = {
			'default': 'type_text',
			'image': 'type_image',
			'video': 'type_video',
			'audio': 'type_audio',
			'html': 'type_html',
		};
		const listId = typeMap[pollType] || 'type_text';
		const answerList = document.getElementById(listId);

		// Collect answer data
		const answers = [];
		const answerExtras = [];
		const imageUrls = [];
		const videoUrls = [];
		const audioUrls = [];
		const htmlContents = [];

		if (answerList) {
			const answerItems = answerList.querySelectorAll('.polls-answer');
			answerItems.forEach(item => {
				const labelInput = item.querySelector('.wbpoll_answer, input[name="_wbpoll_answer[]"]');
				if (labelInput && labelInput.value.trim()) {
					answers.push(labelInput.value.trim());
					answerExtras.push(pollType === 'default' ? 'default' : pollType);

					// Collect media URLs if applicable
					if (pollType === 'image') {
						const imgUrl = item.querySelector('.wbpoll_image_answer_url, input[name="_wbpoll_full_size_image_answer[]"]');
						imageUrls.push(imgUrl ? imgUrl.value : '');
					} else if (pollType === 'video') {
						const vidUrl = item.querySelector('.wbpoll_video_answer_url, input[name="_wbpoll_video_answer_url[]"]');
						videoUrls.push(vidUrl ? vidUrl.value : '');
					} else if (pollType === 'audio') {
						const audUrl = item.querySelector('.wbpoll_audio_answer_url, input[name="_wbpoll_audio_answer_url[]"]');
						audioUrls.push(audUrl ? audUrl.value : '');
					} else if (pollType === 'html') {
						const htmlContent = item.querySelector('.wbpoll_html_answer_textarea, textarea[name="_wbpoll_html_answer[]"]');
						htmlContents.push(htmlContent ? htmlContent.value : '');
					}
				}
			});
		}

		// Validate at least 2 answers
		if (answers.length < 2) {
			this.showToast(__('Please add at least 2 answer options', 'buddypress-polls'), 'error');
			return;
		}

		// Build request data
		const requestData = {
			title: title,
			content: content,
			poll_type: pollType,
			'_wbpoll_answer': answers,
			'_wbpoll_answer_extra': answerExtras,
		};

		// Add poll_id for updates
		if (pollId) {
			requestData.poll_id = pollId;
		}

		// Add media URLs based on poll type
		if (pollType === 'image' && imageUrls.length > 0) {
			requestData['_wbpoll_full_size_image_answer'] = imageUrls;
		} else if (pollType === 'video' && videoUrls.length > 0) {
			requestData['_wbpoll_video_answer_url'] = videoUrls;
		} else if (pollType === 'audio' && audioUrls.length > 0) {
			requestData['_wbpoll_audio_answer_url'] = audioUrls;
		} else if (pollType === 'html' && htmlContents.length > 0) {
			requestData['_wbpoll_html_answer'] = htmlContents;
		}

		// Get meta fields
		const neverExpire = form.querySelector('input[name="_wbpoll_never_expire"]:checked');
		if (neverExpire) {
			requestData['_wbpoll_never_expire'] = neverExpire.value;
		}

		const startDate = document.getElementById('_wbpoll_start_date');
		if (startDate && startDate.value) {
			requestData['_wbpoll_start_date'] = startDate.value;
		}

		const endDate = document.getElementById('_wbpoll_end_date');
		if (endDate && endDate.value) {
			requestData['_wbpoll_end_date'] = endDate.value;
		}

		const showResult = form.querySelector('input[name="_wbpoll_show_result_before_expire"]:checked');
		if (showResult) {
			requestData['_wbpoll_show_result_before_expire'] = showResult.value;
		}

		const multivote = form.querySelector('input[name="_wbpoll_multivote"]:checked');
		if (multivote) {
			requestData['_wbpoll_multivote'] = multivote.value;
		}

		const addAdditionalFieldsInputs = form.querySelectorAll('input[name="_wbpoll_add_additional_fields"]');
		if (addAdditionalFieldsInputs && addAdditionalFieldsInputs.length) {
			const addAdditionalFieldsChecked = form.querySelector('input[name="_wbpoll_add_additional_fields"]:checked');
			requestData['_wbpoll_add_additional_fields'] = addAdditionalFieldsChecked ? addAdditionalFieldsChecked.value : '0';
		}

		// Show loading state
		if (submitBtn) {
			this.setButtonLoading(submitBtn, true);
		}

		// Make API request
		const siteUrl = typeof wbpollpublic !== 'undefined' ? wbpollpublic.url : '';
		const nonce = typeof wbpollpublic !== 'undefined' ? wbpollpublic.rest_nonce : '';

		fetch(siteUrl + '/wp-json/wbpoll/v1/postpoll', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(requestData),
		})
			.then(response => response.json())
			.then(result => {
				if (submitBtn) {
					this.setButtonLoading(submitBtn, false);
				}

				// Check for error responses
				if (result.code && result.message) {
					// WP_Error response
					this.showToast(result.message, 'error');
					return;
				}

				// Success
				const message = isEditing
					? __('Poll updated successfully!', 'buddypress-polls')
					: __('Poll created successfully!', 'buddypress-polls');
				this.showToast(message, 'success');

				// Close panel and reload page to show new poll
				this.closePanel();
				setTimeout(() => {
					window.location.reload();
				}, 1000);
			})
			.catch(error => {
				console.error('Form submit error:', error);
				if (submitBtn) {
					this.setButtonLoading(submitBtn, false);
				}
				this.showToast(__('An error occurred. Please try again.', 'buddypress-polls'), 'error');
			});
	}

	/**
	 * Add a new answer field based on current poll type.
	 * Creates type-appropriate fields (image, video, audio, html, or default text).
	 */
	addAnswerField() {
		if (!this.elements.answerList) return;

		const answers = this.elements.answerList.querySelectorAll('.polls-answer');
		const index = answers.length + 1;
		const pollType = this.getSelectedPollType();

		let answerHtml = '';

		// Create type-appropriate answer field using existing helper methods
		switch (pollType) {
			case 'image':
				answerHtml = this.createImageAnswerHTML(index, '', '');
				break;
			case 'video':
				answerHtml = this.createVideoAnswerHTML(index, '', '');
				break;
			case 'audio':
				answerHtml = this.createAudioAnswerHTML(index, '', '');
				break;
			case 'html':
				answerHtml = this.createHtmlAnswerHTML(index, '', '');
				break;
			default:
				// Default text answer - use existing createTextAnswerHTML method
				answerHtml = this.createTextAnswerHTML(index, '');
				break;
		}

		// Insert the HTML using same pattern as populateAnswersFromData (line 1497)
		this.elements.answerList.insertAdjacentHTML('beforeend', answerHtml);

		// Focus the new input and apply answer limit if set
		const newAnswer = this.elements.answerList.lastElementChild;
		const input = newAnswer ? newAnswer.querySelector('.wbpoll_answer') : null;
		if (input) {
			// Apply character limit to new answer input
			const answersContainer = document.getElementById('polls-answers');
			const answerLimit = answersContainer ? parseInt(answersContainer.dataset.answerLimit, 10) || 0 : 0;
			if (answerLimit > 0) {
				input.setAttribute('maxlength', answerLimit);
				input.setAttribute('data-char-limit', answerLimit);
			}
			input.focus();
		}

		// Reinitialize sortable
		this.initSortable();
	}

	/**
	 * Remove an answer field.
	 * @param {HTMLElement} answerEl - Answer element to remove
	 */
	removeAnswerField(answerEl) {
		if (!answerEl) return;
		const answerList = answerEl.closest('.polls-answers__list') || this.elements.answerList;
		if (!answerList) return;
		const answers = answerList.querySelectorAll('.polls-answer');

		// Keep at least 2 answers
		if (answers.length <= 2) {
			this.showToast(__('You need at least 2 answer options', 'buddypress-polls'), 'error');
			return;
		}

		answerEl.remove();
	}

	/**
	 * Reset form to initial state.
	 */
	resetForm() {
		if (!this.elements.form) return;

		// Reset native form
		const nativeForm = this.elements.form.querySelector('form');
		if (nativeForm) {
			nativeForm.reset();
		}

		// Clear hidden poll ID
		const pollIdInput = document.getElementById('poll_id');
		if (pollIdInput) {
			pollIdInput.value = '';
		}

		// Reset poll type to default
		const defaultTypeRadio = document.querySelector('input[name="poll_type"][value="default"]');
		if (defaultTypeRadio) {
			defaultTypeRadio.checked = true;
			this.handlePollTypeChange('default');
		}

		// Reset expiry option to "never"
		const neverExpireRadio = document.querySelector('input[name="poll_expiry_option"][value="never"]');
		if (neverExpireRadio) {
			neverExpireRadio.checked = true;
			this.handleExpiryChange('never');
		}

		// Clear error messages
		document.querySelectorAll('.polls-form__error').forEach(el => {
			el.style.display = 'none';
			el.textContent = '';
		});

		// Clear TinyMCE if exists
		if (typeof tinyMCE !== 'undefined') {
			const editor = tinyMCE.get('poll-content');
			if (editor) {
				editor.setContent('');
			}
		}
	}

	/**
	 * Load poll data for editing.
	 * @param {string} pollId - Poll ID
	 */
	loadPollData(pollId) {
		console.log('[PollsDashboard] wbpollpublic object:', typeof wbpollpublic !== 'undefined' ? wbpollpublic : 'NOT DEFINED');

		const siteUrl = typeof wbpollpublic !== 'undefined' ? wbpollpublic.url : '';
		const nonce = typeof wbpollpublic !== 'undefined' ? wbpollpublic.rest_nonce : '';

		console.log('[PollsDashboard] Loading poll data for ID:', pollId);
		console.log('[PollsDashboard] Site URL:', siteUrl);
		console.log('[PollsDashboard] Nonce:', nonce);
		console.log('[PollsDashboard] Full API URL:', siteUrl + '/wp-json/wbpoll/v1/listpoll/id');

		if (!siteUrl) {
			console.error('[PollsDashboard] ERROR: siteUrl is empty! wbpollpublic may not be loaded.');
			this.showToast(__('Configuration error. Please refresh the page.', 'buddypress-polls'), 'error');
			this.closePanel();
			return;
		}

		fetch(siteUrl + '/wp-json/wbpoll/v1/listpoll/id', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify({ pollid: pollId }),
		})
			.then(response => {
				console.log('[PollsDashboard] Response status:', response.status);
				return response.json();
			})
			.then(apiData => {
				console.log('[PollsDashboard] API Response:', apiData);
				// API returns data directly (not wrapped in success/data)
				if (apiData && apiData.id) {
					// Transform API response to match expected form field names
					const pollData = {
						ID: apiData.id,
						post_title: apiData.title || '',
						post_content: apiData.content || '',
						poll_type: this.detectPollType(apiData.options),
						_wbpoll_never_expire: apiData.never_expire || '1',
						_wbpoll_start_date: apiData.start_time || '',
						_wbpoll_end_date: apiData.end_date || '',
						_wbpoll_show_result_before_expire: apiData.show_result_after_expire || '0',
						_wbpoll_multivote: apiData.multivote || '0',
						_wbpoll_add_additional_fields: apiData.add_additional_fields || '0',
						_wbpoll_content: apiData.show_description || '',
						options: apiData.options || {},
					};
					this.populateForm(pollData);
				} else if (apiData.code === '404') {
					this.showToast(__('Poll not found.', 'buddypress-polls'), 'error');
					this.closePanel();
				} else {
					this.showToast(__('Failed to load poll data.', 'buddypress-polls'), 'error');
					this.closePanel();
				}
			})
			.catch(error => {
				console.error('Error loading poll:', error);
				this.showToast(__('Error loading poll data.', 'buddypress-polls'), 'error');
				this.closePanel();
			});
	}

	/**
	 * Detect poll type from options data.
	 * @param {Object} options - Poll options from API
	 * @returns {string} Poll type (default, image, video, audio, html)
	 */
	detectPollType(options) {
		if (!options || typeof options !== 'object') return 'default';

		const firstOption = Object.values(options)[0];
		if (!firstOption) return 'default';

		if (firstOption.image) return 'image';
		if (firstOption.video) return 'video';
		if (firstOption.audio) return 'audio';
		if (firstOption.html) return 'html';

		return 'default';
	}

	/**
	 * Populate form with poll data.
	 * @param {Object} pollData - Poll data object
	 */
	populateForm(pollData) {
		// Set poll ID
		const pollIdInput = document.getElementById('poll_id');
		if (pollIdInput) {
			pollIdInput.value = pollData.ID;
		}

		// Set title
		const titleInput = document.getElementById('polltitle');
		if (titleInput) {
			titleInput.value = pollData.post_title || '';
		}

		// Set content
		const contentInput = document.getElementById('poll-content');
		if (typeof tinyMCE !== 'undefined') {
			const editor = tinyMCE.get('poll-content');
			if (editor) {
				editor.setContent(pollData.post_content || '');
			} else if (contentInput) {
				contentInput.value = pollData.post_content || '';
			}
		} else if (contentInput) {
			contentInput.value = pollData.post_content || '';
		}

		// Set poll type via radio button and trigger change handler
		const pollType = pollData.poll_type || 'default';
		const pollTypeRadio = document.querySelector(`input[name="poll_type"][value="${pollType}"]`);
		if (pollTypeRadio) {
			pollTypeRadio.checked = true;
			this.handlePollTypeChange(pollType);
		}

		// Also set hidden select for backward compatibility
		const pollTypeSelect = document.getElementById('poll_type');
		if (pollTypeSelect) {
			pollTypeSelect.value = pollType;
		}

		// Set meta fields
		this.setRadioValue('_wbpoll_never_expire', pollData._wbpoll_never_expire || '1');
		this.setInputValue('_wbpoll_start_date', pollData._wbpoll_start_date || '');
		this.setInputValue('_wbpoll_end_date', pollData._wbpoll_end_date || '');
		this.setRadioValue('_wbpoll_show_result_before_expire', pollData._wbpoll_show_result_before_expire || '0');
		this.setRadioValue('_wbpoll_multivote', pollData._wbpoll_multivote || '0');
		this.setRadioValue('_wbpoll_add_additional_fields', pollData._wbpoll_add_additional_fields || '0');

		// Set expiry option visual cards
		const neverExpire = pollData._wbpoll_never_expire === '1';
		const expiryValue = neverExpire ? 'never' : 'duration';
		const expiryRadio = document.querySelector(`input[name="poll_expiry_option"][value="${expiryValue}"]`);
		if (expiryRadio) {
			expiryRadio.checked = true;
			this.handleExpiryChange(expiryValue);
		}

		// Populate answers
		this.populateAnswers(pollData.poll_type || 'default', pollData.options || {});
	}

	/**
	 * Populate answer fields from poll data.
	 * @param {string} pollType - Poll type (default, image, video, audio, html)
	 * @param {Object} options - Options object from API {0: {lable, image?, video?, audio?, html?}, ...}
	 */
	populateAnswers(pollType, options) {
		console.log('[PollsDashboard] Populating answers:', pollType, options);

		// Map poll type to answer list ID
		const typeMap = {
			'default': 'type_text',
			'image': 'type_image',
			'video': 'type_video',
			'audio': 'type_audio',
			'html': 'type_html',
		};

		const listId = typeMap[pollType] || 'type_text';
		const answerList = document.getElementById(listId);

		if (!answerList) {
			console.warn('[PollsDashboard] Answer list not found:', listId);
			return;
		}

		// Show the correct poll type list, hide others
		document.querySelectorAll('.polls-answers__list').forEach(list => {
			list.style.display = list.id === listId ? '' : 'none';
		});

		// Clear existing answers
		answerList.innerHTML = '';

		// Get options array
		const optionKeys = Object.keys(options);

		if (optionKeys.length === 0) {
			console.warn('[PollsDashboard] No options to populate');
			return;
		}

		// Create answer elements for each option
		optionKeys.forEach((key, index) => {
			const option = options[key];
			const label = option.label || ''; // API returns 'label'

			let answerHtml = '';

			if (pollType === 'default') {
				answerHtml = this.createTextAnswerHTML(index, label);
			} else if (pollType === 'image') {
				answerHtml = this.createImageAnswerHTML(index, label, option.image || '');
			} else if (pollType === 'video') {
				answerHtml = this.createVideoAnswerHTML(index, label, option.video || '');
			} else if (pollType === 'audio') {
				answerHtml = this.createAudioAnswerHTML(index, label, option.audio || '');
			} else if (pollType === 'html') {
				answerHtml = this.createHtmlAnswerHTML(index, label, option.html || '');
			}

			answerList.insertAdjacentHTML('beforeend', answerHtml);
		});

		// Reinitialize sortable
		this.initSortable();
	}

	/**
	 * Create HTML for text answer field.
	 */
	createTextAnswerHTML(index, label) {
		return `
			<div class="polls-answer" data-index="${index}">
				<div class="polls-answer__handle" aria-label="Drag to reorder">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</div>
				<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="${this.escapeHtml(label)}" placeholder="Enter option...">
				<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
				<button type="button" class="polls-answer__remove" aria-label="Remove option">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>`;
	}

	/**
	 * Create HTML for image answer field.
	 */
	createImageAnswerHTML(index, label, imageUrl) {
		const preview = imageUrl ? `<img src="${this.escapeHtml(imageUrl)}" alt="">` : '';
		return `
			<div class="polls-answer polls-answer--media" data-index="${index}">
				<div class="polls-answer__handle" aria-label="Drag to reorder">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</div>
				<div class="polls-answer__preview">${preview}</div>
				<div class="polls-answer__fields">
					<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="${this.escapeHtml(label)}" placeholder="Option label...">
					<input type="hidden" value="image" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
					<div class="polls-answer__url-row">
						<input type="url" class="polls-answer__input wbpoll_image_answer_url" name="_wbpoll_full_size_image_answer[]" value="${this.escapeHtml(imageUrl)}" placeholder="Image URL...">
						<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="image" aria-label="Choose image">
							<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
						</button>
					</div>
				</div>
				<button type="button" class="polls-answer__remove" aria-label="Remove option">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>`;
	}

	/**
	 * Create HTML for video answer field.
	 */
	createVideoAnswerHTML(index, label, videoUrl) {
		return `
			<div class="polls-answer polls-answer--media" data-index="${index}">
				<div class="polls-answer__handle" aria-label="Drag to reorder">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</div>
				<div class="polls-answer__preview"></div>
				<div class="polls-answer__fields">
					<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="${this.escapeHtml(label)}" placeholder="Option label...">
					<input type="hidden" value="video" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
					<div class="polls-answer__url-row">
						<input type="url" class="polls-answer__input wbpoll_video_answer_url" name="_wbpoll_video_answer_url[]" value="${this.escapeHtml(videoUrl)}" placeholder="Video URL...">
						<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="video" aria-label="Choose video">
							<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
						</button>
					</div>
				</div>
				<button type="button" class="polls-answer__remove" aria-label="Remove option">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>`;
	}

	/**
	 * Create HTML for audio answer field.
	 */
	createAudioAnswerHTML(index, label, audioUrl) {
		return `
			<div class="polls-answer polls-answer--media" data-index="${index}">
				<div class="polls-answer__handle" aria-label="Drag to reorder">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</div>
				<div class="polls-answer__preview"></div>
				<div class="polls-answer__fields">
					<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="${this.escapeHtml(label)}" placeholder="Option label...">
					<input type="hidden" value="audio" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
					<div class="polls-answer__url-row">
						<input type="url" class="polls-answer__input wbpoll_audio_answer_url" name="_wbpoll_audio_answer_url[]" value="${this.escapeHtml(audioUrl)}" placeholder="Audio URL...">
						<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="audio" aria-label="Choose audio">
							<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
						</button>
					</div>
				</div>
				<button type="button" class="polls-answer__remove" aria-label="Remove option">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>`;
	}

	/**
	 * Create HTML for HTML answer field.
	 */
	createHtmlAnswerHTML(index, label, htmlContent) {
		return `
			<div class="polls-answer polls-answer--html" data-index="${index}">
				<div class="polls-answer__handle" aria-label="Drag to reorder">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</div>
				<div class="polls-answer__fields polls-answer__fields--full">
					<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="${this.escapeHtml(label)}" placeholder="Option label...">
					<textarea class="polls-answer__textarea wbpoll_html_answer_textarea" name="_wbpoll_html_answer[]" placeholder="HTML content...">${this.escapeHtml(htmlContent)}</textarea>
					<input type="hidden" value="html" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
				</div>
				<button type="button" class="polls-answer__remove" aria-label="Remove option">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>`;
	}

	/**
	 * Escape HTML for safe insertion.
	 */
	escapeHtml(text) {
		if (!text) return '';
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Set radio button value.
	 * @param {string} name - Input name
	 * @param {string} value - Value to set
	 */
	setRadioValue(name, value) {
		const input = document.querySelector('input[name="' + name + '"][value="' + value + '"]');
		if (input) {
			input.checked = true;
			input.dispatchEvent(new Event('change'));
		}
	}

	/**
	 * Set input value.
	 * @param {string} id - Input ID
	 * @param {string} value - Value to set
	 */
	setInputValue(id, value) {
		const input = document.getElementById(id);
		if (input) {
			input.value = value;
		}
	}

	// ========================================
	// Drag & Drop (SortableJS)
	// ========================================

	/**
	 * Initialize SortableJS for answer reordering.
	 */
	initSortable() {
		// Check if SortableJS is available
		if (typeof Sortable === 'undefined') {
			console.warn('SortableJS not loaded. Drag-drop disabled.');
			return;
		}

		// Destroy existing instances
		if (this.state.sortables) {
			this.state.sortables.forEach(s => s.destroy());
		}
		this.state.sortables = [];

		// Initialize sortable for all answer lists
		this.elements.answerLists.forEach(list => {
			const sortable = new Sortable(list, {
				handle: '.polls-answer__handle',
				animation: 200,
				ghostClass: 'is-dragging',
				chosenClass: 'is-chosen',
				dragClass: 'is-drag',
				onEnd: () => {
					this.updateAnswerIndices();
				},
			});
			this.state.sortables.push(sortable);
		});
	}

	/**
	 * Update answer field indices after reorder.
	 */
	updateAnswerIndices() {
		if (!this.elements.answerList) return;

		const answers = this.elements.answerList.querySelectorAll('.polls-answer');
		answers.forEach((answer, index) => {
			answer.dataset.index = index + 1;
		});
	}

	// ========================================
	// Toast Notifications
	// ========================================

	/**
	 * Show a toast notification using safe DOM methods.
	 * Supports stacking multiple toasts in the container.
	 * @param {string} message - Message to display
	 * @param {string} type - Toast type (success, error, warning, info)
	 * @param {string} title - Optional title for the toast
	 */
	showToast(message, type = 'success', title = '') {
		// Ensure toast container exists
		if (!this.elements.toastContainer) {
			this.createToastContainer();
		}

		// Create toast element
		const toast = document.createElement('div');
		toast.className = 'polls-toast polls-toast--' + type;
		toast.setAttribute('role', 'alert');

		// Create icon using safe DOM methods
		const iconWrapper = document.createElement('span');
		iconWrapper.className = 'polls-toast__icon';
		const icon = this.createToastIcon(type);
		iconWrapper.appendChild(icon);
		toast.appendChild(iconWrapper);

		// Create content wrapper
		const content = document.createElement('div');
		content.className = 'polls-toast__content';

		// Add title if provided
		if (title) {
			const titleEl = document.createElement('h4');
			titleEl.className = 'polls-toast__title';
			titleEl.textContent = title;
			content.appendChild(titleEl);
		}

		// Add message
		const messageEl = document.createElement('p');
		messageEl.className = 'polls-toast__message';
		messageEl.textContent = message;
		content.appendChild(messageEl);
		toast.appendChild(content);

		// Create close button
		const closeBtn = document.createElement('button');
		closeBtn.type = 'button';
		closeBtn.className = 'polls-toast__close';
		closeBtn.setAttribute('aria-label', __('Close notification', 'buddypress-polls'));

		const closeIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		closeIcon.setAttribute('width', '16');
		closeIcon.setAttribute('height', '16');
		closeIcon.setAttribute('viewBox', '0 0 24 24');
		closeIcon.setAttribute('fill', 'none');
		closeIcon.setAttribute('stroke', 'currentColor');
		closeIcon.setAttribute('stroke-width', '2');
		const closePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		closePath.setAttribute('d', 'M18 6L6 18M6 6l12 12');
		closeIcon.appendChild(closePath);
		closeBtn.appendChild(closeIcon);
		toast.appendChild(closeBtn);

		// Add to container
		this.elements.toastContainer.appendChild(toast);

		// Trigger animation
		requestAnimationFrame(() => {
			toast.classList.add('is-visible');
		});

		// Close on click
		closeBtn.addEventListener('click', () => {
			this.hideToast(toast);
		});

		// Auto-hide after 4 seconds
		const autoHideTimer = setTimeout(() => {
			this.hideToast(toast);
		}, 4000);

		// Store timer on element for potential cleanup
		toast._autoHideTimer = autoHideTimer;

		return toast;
	}

	/**
	 * Create SVG icon for toast type using safe DOM methods.
	 * @param {string} type - Toast type
	 * @returns {SVGElement} SVG element
	 */
	createToastIcon(type) {
		const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		svg.setAttribute('width', '20');
		svg.setAttribute('height', '20');
		svg.setAttribute('viewBox', '0 0 24 24');
		svg.setAttribute('fill', 'none');
		svg.setAttribute('stroke', 'currentColor');
		svg.setAttribute('stroke-width', '2');

		if (type === 'success') {
			const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
			path1.setAttribute('d', 'M22 11.08V12a10 10 0 11-5.93-9.14');
			svg.appendChild(path1);
			const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
			path2.setAttribute('d', 'M22 4L12 14.01l-3-3');
			svg.appendChild(path2);
		} else if (type === 'error') {
			const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			circle.setAttribute('cx', '12');
			circle.setAttribute('cy', '12');
			circle.setAttribute('r', '10');
			svg.appendChild(circle);
			const line1 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line1.setAttribute('x1', '15');
			line1.setAttribute('y1', '9');
			line1.setAttribute('x2', '9');
			line1.setAttribute('y2', '15');
			svg.appendChild(line1);
			const line2 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line2.setAttribute('x1', '9');
			line2.setAttribute('y1', '9');
			line2.setAttribute('x2', '15');
			line2.setAttribute('y2', '15');
			svg.appendChild(line2);
		} else if (type === 'warning') {
			const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
			path.setAttribute('d', 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z');
			svg.appendChild(path);
			const line1 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line1.setAttribute('x1', '12');
			line1.setAttribute('y1', '9');
			line1.setAttribute('x2', '12');
			line1.setAttribute('y2', '13');
			svg.appendChild(line1);
			const line2 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line2.setAttribute('x1', '12');
			line2.setAttribute('y1', '17');
			line2.setAttribute('x2', '12.01');
			line2.setAttribute('y2', '17');
			svg.appendChild(line2);
		} else {
			// info (default)
			const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			circle.setAttribute('cx', '12');
			circle.setAttribute('cy', '12');
			circle.setAttribute('r', '10');
			svg.appendChild(circle);
			const line1 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line1.setAttribute('x1', '12');
			line1.setAttribute('y1', '16');
			line1.setAttribute('x2', '12');
			line1.setAttribute('y2', '12');
			svg.appendChild(line1);
			const line2 = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line2.setAttribute('x1', '12');
			line2.setAttribute('y1', '8');
			line2.setAttribute('x2', '12.01');
			line2.setAttribute('y2', '8');
			svg.appendChild(line2);
		}

		return svg;
	}

	/**
	 * Hide a toast notification with animation.
	 * @param {HTMLElement} toast - Toast element
	 */
	hideToast(toast) {
		if (!toast || toast._isHiding) return;

		toast._isHiding = true;

		// Clear auto-hide timer if exists
		if (toast._autoHideTimer) {
			clearTimeout(toast._autoHideTimer);
		}

		// Add hiding class for animation
		toast.classList.add('is-hiding');
		toast.classList.remove('is-visible');

		// Remove after animation
		setTimeout(() => {
			if (toast.parentNode) {
				toast.remove();
			}
		}, 300);
	}

	/**
	 * Clear all toast notifications.
	 */
	clearAllToasts() {
		if (!this.elements.toastContainer) return;

		const toasts = this.elements.toastContainer.querySelectorAll('.polls-toast');
		toasts.forEach(toast => this.hideToast(toast));
	}

	// ========================================
	// URL Params Handling
	// ========================================

	/**
	 * Handle URL parameters (e.g., ?poll_id=123 or ?action=edit&id=123 for editing).
	 */
	handleUrlParams() {
		const urlParams = new URLSearchParams(window.location.search);

		// Support both formats: ?poll_id=123 and ?action=edit&id=123
		const pollId = urlParams.get('poll_id') || urlParams.get('id');
		const action = urlParams.get('action');
		const create = urlParams.get('create');

		// Check if edit is blocked (user not authorized)
		const isEditBlocked = this.elements.panel?.dataset.editBlocked === 'true';

		if (pollId && (!action || action === 'edit') && !isEditBlocked) {
			// Open panel for editing
			this.openPanel(pollId);
		} else if (create === '1') {
			// Open panel for creating new poll
			this.openPanel(null);
		} else {
			// Ensure panel is closed if no relevant URL parameters
			// This fixes the issue where PHP might set aria-hidden="false" but JS should keep it closed
			if (this.elements.panel) {
				this.elements.panel.classList.remove('is-open');
				this.elements.panel.setAttribute('aria-hidden', 'true');
			}
			if (this.elements.panelOverlay) {
				this.elements.panelOverlay.classList.remove('is-visible');
			}
		}
	}
}

// ========================================
// Initialize on DOM Ready
// ========================================

document.addEventListener('DOMContentLoaded', () => {
	window.pollsDashboard = new PollsDashboard();
});

// ========================================
// Legacy jQuery Fallbacks (for backwards compatibility)
// ========================================

(function($) {
	'use strict';

	if (typeof $ === 'undefined') return;

	$(document).ready(function() {
		// Legacy pause poll handler
		$('.pause_poll').on('click', function() {
			if (window.pollsDashboard) return; // Use new system

			var pollid = $(this).data('id');
			var pause_poll = $(this).data('value');

			$.ajax({
				url: wbpollpublic.url + '/wp-json/wbpoll/v1/listpoll/pause/poll',
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify({ pollid: pollid, _wbpoll_pause_poll: pause_poll }),
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', wbpollpublic.rest_nonce);
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					}
				}
			});
		});

		// Legacy delete poll handler
		$('.delete_poll').on('click', function() {
			if (window.pollsDashboard) return;

			if (!confirm(__('Are you sure you want to delete this poll?', 'buddypress-polls'))) {
				return;
			}

			var pollid = $(this).data('id');

			$.ajax({
				url: wbpollpublic.url + '/wp-json/wbpoll/v1/listpoll/delete/poll',
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify({ pollid: pollid }),
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', wbpollpublic.rest_nonce);
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					}
				}
			});
		});

		// Legacy tab navigation
		$('.dashboard-sub-tab').on('click', function(e) {
			if (window.pollsDashboard) return;

			e.preventDefault();
			var datatext = $(this).data('text');

			$('.dashboard-sub-tab').removeClass('selected');
			$('.tab-list').removeClass('active');

			$(this).addClass('selected');

			if (datatext === 'publish') {
				$('#publish-listing').addClass('active');
			} else if (datatext === 'pending') {
				$('#pending-listing').addClass('active');
			} else if (datatext === 'draft') {
				$('#draft-listing').addClass('active');
			}
		});
	});

})(typeof jQuery !== 'undefined' ? jQuery : null);
