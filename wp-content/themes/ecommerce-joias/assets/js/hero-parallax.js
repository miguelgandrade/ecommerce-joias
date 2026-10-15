(() => {
	const banner = document.querySelector(".nc-hero");
	const camadaDoBanner = document.querySelector(".nc-hero__media");

	if (!banner || !camadaDoBanner) {
		return;
	}

	const condicaoDeMovimento = window.matchMedia(
		"(min-width: 768px) and (prefers-reduced-motion: no-preference)"
	);

	let quadroDoParallax;

	const atualizarParallax = () => {
		quadroDoParallax = undefined;

		if (!condicaoDeMovimento.matches) {
			camadaDoBanner.style.transform = "";
			return;
		}

		const limitesDoBanner = banner.getBoundingClientRect();

		if (
			limitesDoBanner.bottom < 0 ||
			limitesDoBanner.top > window.innerHeight
		) {
			return;
		}

		const progresso =
			(window.innerHeight - limitesDoBanner.top) /
			(window.innerHeight + limitesDoBanner.height);

		// Movimento ampliado temporariamente para facilitar a avaliação visual do parallax.
		const deslocamento = (progresso - 0.5) * 480;

		camadaDoBanner.style.transform = `translate3d(0, ${deslocamento.toFixed(2)}px, 0)`;
	};

	const solicitarAtualizacaoParallax = () => {
		if (!quadroDoParallax) {
			quadroDoParallax = window.requestAnimationFrame(atualizarParallax);
		}
	};

	window.addEventListener("scroll", solicitarAtualizacaoParallax, {
		passive: true,
	});

	window.addEventListener("resize", solicitarAtualizacaoParallax);

	condicaoDeMovimento.addEventListener("change", solicitarAtualizacaoParallax);

	solicitarAtualizacaoParallax();
})();
