/**
 * Origin frontend interactions.
 *
 * Copyright (C) 2026 Morton Li.
 */

(() => {
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
	const shareButtons = document.querySelectorAll('[data-origin-share]');

	if (menuButton && primaryMenu) {
		menuButton.addEventListener('click', () => {
			const isOpen = document.body.classList.toggle('menu-open');

			menuButton.setAttribute('aria-expanded', String(isOpen));
		});

		primaryMenu.addEventListener('click', (event) => {
			if (!(event.target instanceof HTMLAnchorElement)) {
				return;
			}

			document.body.classList.remove('menu-open');
			menuButton.setAttribute('aria-expanded', 'false');
		});
	}

	const setSearchState = (isOpen) => {
		if (!searchModal) {
			return;
		}

		if (isOpen) {
			setAuthState(false);
		}

		searchModal.hidden = !isOpen;
		document.body.classList.toggle('search-open', isOpen);

		searchOpenButtons.forEach((button) => {
			button.setAttribute('aria-expanded', String(isOpen));
		});

		if (isOpen && searchField instanceof HTMLInputElement) {
			window.setTimeout(() => searchField.focus(), 0);
		}
	};

	const setAuthPanel = (panelName) => {
		const selectedPanel = panelName === 'register' ? 'register' : 'login';

		authTabs.forEach((tab) => {
			const isSelected = tab.getAttribute('data-origin-auth-tab') === selectedPanel;

			tab.classList.toggle('is-active', isSelected);
			tab.setAttribute('aria-selected', String(isSelected));
			tab.setAttribute('tabindex', isSelected ? '0' : '-1');
		});

		authPanels.forEach((panel) => {
			const isSelected = panel.getAttribute('data-origin-auth-panel') === selectedPanel;

			panel.hidden = !isSelected;
		});
	};

	const setAuthState = (isOpen, panelName = 'login') => {
		if (!authModal) {
			return;
		}

		if (isOpen) {
			setSearchState(false);
			setAuthPanel(panelName);
		}

		authModal.hidden = !isOpen;
		document.body.classList.toggle('auth-open', isOpen);

		if (isOpen) {
			const currentPanel = authModal.querySelector('.auth-panel:not([hidden])');
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
		setAuthPanel(authModal.getAttribute('data-origin-auth-current-panel') || 'login');

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
