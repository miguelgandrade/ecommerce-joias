(() => {
	const pagina = document.querySelector('.nc-home-page');
	if (!pagina) return;
	const elementos = [...pagina.querySelectorAll('[data-home-reveal]')];
	const movimentoReduzido = window.matchMedia('(prefers-reduced-motion: reduce)');
	const elementosRevelados = new WeakSet();
	let observador;

	const mostrarElemento = elemento => {
		elemento.classList.remove('nc-home-reveal--waiting');
		elementosRevelados.add(elemento);
		observador?.unobserve(elemento);
	};
	const configurarSecoes = () => {
		observador?.disconnect();
		elementos.forEach(elemento => elemento.classList.remove('nc-home-reveal--waiting'));
		if (movimentoReduzido.matches || !('IntersectionObserver' in window)) return;
		observador = new IntersectionObserver(entradas => {
			entradas.forEach(entrada => {
				if (entrada.isIntersecting) mostrarElemento(entrada.target);
			});
		}, { threshold: 0.08 });
		elementos.forEach(elemento => {
			if (elementosRevelados.has(elemento)) return;
			// Preserva o conteúdo já visível ao carregar ou restaurar a página.
			if (elemento.getBoundingClientRect().top < window.innerHeight) {
				mostrarElemento(elemento);
				return;
			}
			observador.observe(elemento);
			elemento.classList.add('nc-home-reveal--waiting');
		});
	};
	pagina.addEventListener('focusin', evento => {
		const elemento = evento.target.closest('[data-home-reveal]');
		if (elemento) mostrarElemento(elemento);
	});
	movimentoReduzido.addEventListener('change', configurarSecoes);
	window.addEventListener('pageshow', () => {
		elementos.forEach(elemento => {
			if (elemento.getBoundingClientRect().top < window.innerHeight) mostrarElemento(elemento);
		});
	});
	configurarSecoes();
})();
