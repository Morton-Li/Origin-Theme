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

		searchModal.hidden = !isOpen;
		document.body.classList.toggle('search-open', isOpen);

		searchOpenButtons.forEach((button) => {
			button.setAttribute('aria-expanded', String(isOpen));
		});

		if (isOpen && searchField instanceof HTMLInputElement) {
			window.setTimeout(() => searchField.focus(), 0);
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
	});

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
