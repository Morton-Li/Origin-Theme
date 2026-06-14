/**
 * Origin frontend interactions.
 *
 * Copyright (C) 2026 Morton Li.
 */

(() => {
	const menuButton = document.querySelector('.gh-burger');
	const primaryMenu = document.querySelector('#primary-menu');

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
