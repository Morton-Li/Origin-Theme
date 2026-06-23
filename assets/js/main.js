/**
 * Origin frontend interactions.
 *
 * Copyright (C) 2026 Morton Li.
 */

(() => {
	const root = document.documentElement;
	const menuButton = document.querySelector('.gh-burger');
	const primaryMenu = document.querySelector('#primary-menu');
	const searchModal = document.querySelector('[data-origin-search-modal]');
	const searchOpenButtons = document.querySelectorAll('[data-origin-search-open]');
	const searchCloseButtons = document.querySelectorAll('[data-origin-search-close]');
	const searchField = searchModal?.querySelector('.search-field');
	const authModal = document.querySelector('[data-origin-auth-modal]');
	const authOpenButtons = document.querySelectorAll('[data-origin-auth-open]');
	const authCloseButtons = document.querySelectorAll('[data-origin-auth-close]');
	const authTabs = document.querySelectorAll('[data-origin-auth-tab]');
	const authPanels = document.querySelectorAll('.auth-panel[data-origin-auth-panel]');
	const authStage = document.querySelector('[data-origin-auth-stage]');
	const shareButtons = document.querySelectorAll('[data-origin-share]');
	const backToTopButton = document.querySelector('[data-origin-back-to-top]');
	const transitionTimers = new WeakMap();
	const modalTransitionDuration = 240;
	const authPanelEnterDelay = 90;
	const authPanelTransitionDuration = 180;
	const pageTransitionDuration = 220;
	let backToTopFrame = 0;
	let authPanelResizeObserver = null;

	const finishPageLoad = () => {
		root.classList.remove('origin-page-loading', 'origin-page-exiting');
		root.classList.add('origin-page-ready');
	};

	const beginPageExit = () => {
		root.classList.remove('origin-page-loading', 'origin-page-ready');
		root.classList.add('origin-page-exiting');
	};

	if (document.readyState === 'complete') {
		window.setTimeout(finishPageLoad, 120);
	} else {
		window.addEventListener('load', () => window.setTimeout(finishPageLoad, 120), { once: true });
	}

	window.addEventListener('pageshow', (event) => {
		if (event.persisted) {
			finishPageLoad();
		}
	});

	const setMenuState = (isOpen) => {
		if (!menuButton || !primaryMenu) {
			return;
		}

		document.body.classList.toggle('menu-open', isOpen);
		menuButton.setAttribute('aria-expanded', String(isOpen));
	};

	const openLayer = (layer) => {
		const activeTimer = transitionTimers.get(layer);

		if (activeTimer) {
			window.clearTimeout(activeTimer);
			transitionTimers.delete(layer);
		}

		layer.hidden = false;
		window.requestAnimationFrame(() => layer.classList.add('is-open'));
	};

	const closeLayer = (layer) => {
		if (layer.hidden && !layer.classList.contains('is-open')) {
			return;
		}

		const activeTimer = transitionTimers.get(layer);

		if (activeTimer) {
			window.clearTimeout(activeTimer);
		}

		layer.classList.remove('is-open');

		transitionTimers.set(
			layer,
			window.setTimeout(() => {
				layer.hidden = true;
				transitionTimers.delete(layer);
			}, modalTransitionDuration)
		);
	};

	if (menuButton && primaryMenu) {
		menuButton.addEventListener('click', () => setMenuState(!document.body.classList.contains('menu-open')));

		primaryMenu.addEventListener('click', (event) => {
			if (event.target instanceof Element && event.target.closest('a')) {
				setMenuState(false);
			}
		});
	}

	const shouldTransitionToLink = (link, event) => {
		if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return false;
		}

		if (link.target && link.target.toLowerCase() !== '_self') {
			return false;
		}

		if (link.hasAttribute('download')) {
			return false;
		}

		const href = link.getAttribute('href');

		if (!href || href.startsWith('#')) {
			return false;
		}

		const url = new URL(link.href, window.location.href);

		if (!['http:', 'https:'].includes(url.protocol) || url.origin !== window.location.origin) {
			return false;
		}

		if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) {
			return false;
		}

		return !link.closest('[data-origin-search-open], [data-origin-auth-open], [data-origin-search-close], [data-origin-auth-close]');
	};

	document.addEventListener('click', (event) => {
		if (!(event.target instanceof Element)) {
			return;
		}

		const link = event.target.closest('a');

		if (!(link instanceof HTMLAnchorElement) || !shouldTransitionToLink(link, event)) {
			return;
		}

		event.preventDefault();
		setMenuState(false);
		beginPageExit();
		window.setTimeout(() => window.location.assign(link.href), pageTransitionDuration);
	});

	document.addEventListener('submit', (event) => {
		if (!(event.target instanceof HTMLFormElement)) {
			return;
		}

		if (event.target.target && event.target.target.toLowerCase() !== '_self') {
			return;
		}

		beginPageExit();
	});

	const updateBackToTop = () => {
		if (!(backToTopButton instanceof HTMLButtonElement)) {
			return;
		}

		const scrollableHeight = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
		const progress = Math.min(Math.max(window.scrollY / scrollableHeight, 0), 1);
		const progressDegrees = `${Math.round(progress * 360)}deg`;

		backToTopButton.style.setProperty('--origin-scroll-progress', progressDegrees);
		backToTopButton.classList.toggle('is-visible', window.scrollY > 240);
		backToTopButton.setAttribute('aria-hidden', String(window.scrollY <= 240));
		backToTopButton.tabIndex = window.scrollY > 240 ? 0 : -1;
	};

	const requestBackToTopUpdate = () => {
		if (backToTopFrame) {
			return;
		}

		backToTopFrame = window.requestAnimationFrame(() => {
			backToTopFrame = 0;
			updateBackToTop();
		});
	};

	if (backToTopButton instanceof HTMLButtonElement) {
		backToTopButton.addEventListener('click', () => {
			window.scrollTo({ behavior: 'smooth', top: 0 });
		});

		updateBackToTop();
		window.addEventListener('scroll', requestBackToTopUpdate, { passive: true });
		window.addEventListener('resize', requestBackToTopUpdate);
	}

	const setSearchState = (isOpen) => {
		if (!searchModal) {
			return;
		}

		if (isOpen) {
			setAuthState(false);
			setMenuState(false);
		}

		if (isOpen) {
			openLayer(searchModal);
		} else {
			closeLayer(searchModal);
		}

		document.body.classList.toggle('search-open', isOpen);

		searchOpenButtons.forEach((button) => {
			button.setAttribute('aria-expanded', String(isOpen));
		});

		if (isOpen && searchField instanceof HTMLInputElement) {
			window.setTimeout(() => searchField.focus(), 0);
		}
	};

	const setAuthStageHeight = (panel, shouldAnimate = true) => {
		if (!(authStage instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
			return;
		}

		const height = panel.scrollHeight;

		if (shouldAnimate) {
			authStage.style.height = `${height}px`;
			return;
		}

		authStage.style.transition = 'none';
		authStage.style.height = `${height}px`;
		authStage.offsetHeight;
		authStage.style.transition = '';
	};

	const observeAuthPanelHeight = (panel) => {
		if (!(authStage instanceof HTMLElement) || !(panel instanceof HTMLElement) || !('ResizeObserver' in window)) {
			return;
		}

		if (authPanelResizeObserver) {
			authPanelResizeObserver.disconnect();
		}

		authPanelResizeObserver = new ResizeObserver(() => {
			if (panel.classList.contains('is-active')) {
				setAuthStageHeight(panel);
			}
		});
		authPanelResizeObserver.observe(panel);
	};

	const setAuthPanel = (panelName, shouldAnimate = true, shouldStagger = shouldAnimate) => {
		const selectedPanel = panelName === 'register' ? 'register' : 'login';
		const currentPanel = Array.from(authPanels).find((panel) => panel.classList.contains('is-active'));
		const isSwitchingPanels = shouldStagger && currentPanel instanceof HTMLElement && currentPanel.getAttribute('data-origin-auth-panel') !== selectedPanel;
		let selectedPanelElement = null;

		authTabs.forEach((tab) => {
			const isSelected = tab.getAttribute('data-origin-auth-tab') === selectedPanel;

			tab.classList.toggle('is-active', isSelected);
			tab.setAttribute('aria-selected', String(isSelected));
			tab.setAttribute('tabindex', isSelected ? '0' : '-1');
		});

		authPanels.forEach((panel) => {
			const isSelected = panel.getAttribute('data-origin-auth-panel') === selectedPanel;
			const activeTimer = transitionTimers.get(panel);

			if (activeTimer) {
				window.clearTimeout(activeTimer);
				transitionTimers.delete(panel);
			}

			if (isSelected) {
				selectedPanelElement = panel;
				panel.hidden = false;
				panel.setAttribute('aria-hidden', 'false');

				const showPanel = () => {
					window.requestAnimationFrame(() => {
						panel.classList.add('is-active');
						transitionTimers.delete(panel);
					});
				};

				if (isSwitchingPanels) {
					transitionTimers.set(panel, window.setTimeout(showPanel, authPanelEnterDelay));
				} else {
					showPanel();
				}

				return;
			}

			panel.classList.remove('is-active');
			panel.setAttribute('aria-hidden', 'true');
			transitionTimers.set(
				panel,
				window.setTimeout(() => {
					panel.hidden = true;
					transitionTimers.delete(panel);
				}, authPanelTransitionDuration)
			);
		});

		if (selectedPanelElement instanceof HTMLElement) {
			setAuthStageHeight(selectedPanelElement, shouldAnimate);
			observeAuthPanelHeight(selectedPanelElement);
		}

		return selectedPanelElement;
	};

	const setAuthState = (isOpen, panelName = 'login') => {
		if (!authModal) {
			return;
		}

		const wasAuthOpen = document.body.classList.contains('auth-open');
		let activeAuthPanel = null;

		if (isOpen) {
			setSearchState(false);
			setMenuState(false);
			openLayer(authModal);
		}

		if (isOpen) {
			activeAuthPanel = setAuthPanel(panelName, true, wasAuthOpen);
		} else {
			closeLayer(authModal);
		}

		document.body.classList.toggle('auth-open', isOpen);

		if (isOpen) {
			const currentPanel = activeAuthPanel || authModal.querySelector('.auth-panel.is-active');
			const firstField = currentPanel?.querySelector('input:not([type="hidden"])');

			if (firstField instanceof HTMLInputElement) {
				window.setTimeout(() => firstField.focus(), 0);
			}
		}
	};

	searchOpenButtons.forEach((button) => {
		button.addEventListener('click', () => setSearchState(true));
	});

	searchCloseButtons.forEach((button) => {
		button.addEventListener('click', () => setSearchState(false));
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && document.body.classList.contains('menu-open')) {
			setMenuState(false);
		}

		if (event.key === 'Escape' && document.body.classList.contains('search-open')) {
			setSearchState(false);
		}

		if (event.key === 'Escape' && document.body.classList.contains('auth-open')) {
			setAuthState(false);
		}
	});

	authOpenButtons.forEach((button) => {
		button.addEventListener('click', () => setAuthState(true, button.getAttribute('data-origin-auth-open') || 'login'));
	});

	authCloseButtons.forEach((button) => {
		button.addEventListener('click', () => setAuthState(false));
	});

	authTabs.forEach((tab) => {
		tab.addEventListener('click', () => setAuthPanel(tab.getAttribute('data-origin-auth-tab') || 'login'));
	});

	if (authModal) {
		setAuthPanel(authModal.getAttribute('data-origin-auth-current-panel') || 'login', false);

		if (authModal.getAttribute('data-origin-auth-has-notice') === 'true') {
			setAuthState(true, authModal.getAttribute('data-origin-auth-current-panel') || 'login');
		}
	}

	shareButtons.forEach((button) => {
		button.addEventListener('click', async () => {
			const title = button.getAttribute('data-share-title') || document.title;
			const url = button.getAttribute('data-share-url') || window.location.href;

			try {
				if (navigator.share) {
					await navigator.share({ title, url });
					return;
				}

				if (navigator.clipboard) {
					await navigator.clipboard.writeText(url);
					button.classList.add('is-copied');
					window.setTimeout(() => button.classList.remove('is-copied'), 1500);
				}
			} catch (error) {
				if (error.name !== 'AbortError') {
					throw error;
				}
			}
		});
	});

	const carousel = document.querySelector('[data-origin-featured-carousel]');
	const previousButton = document.querySelector('[data-origin-featured-prev]');
	const nextButton = document.querySelector('[data-origin-featured-next]');

	if (!carousel || !previousButton || !nextButton) {
		return;
	}

	const updateCarouselControls = () => {
		const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth - 1;

		previousButton.disabled = carousel.scrollLeft <= 1;
		nextButton.disabled = carousel.scrollLeft >= maxScrollLeft;
	};

	const scrollCarousel = (direction) => {
		carousel.scrollBy({
			behavior: 'smooth',
			left: direction * carousel.clientWidth,
		});
	};

	previousButton.addEventListener('click', () => scrollCarousel(-1));
	nextButton.addEventListener('click', () => scrollCarousel(1));
	carousel.addEventListener('scroll', updateCarouselControls, { passive: true });
	window.addEventListener('resize', updateCarouselControls);

	updateCarouselControls();
})();
