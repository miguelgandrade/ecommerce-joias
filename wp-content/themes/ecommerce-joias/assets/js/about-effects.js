(() => {
	const pagina = document.querySelector('.nc-about-page');
	if (!pagina) return;
	const movimentoReduzido = window.matchMedia('(prefers-reduced-motion: reduce)');
	const telaGrande = window.matchMedia('(min-width: 768px)');
	const banner = pagina.querySelector('.nc-about-hero');
	const imagemBanner = pagina.querySelector('.nc-about-hero__media');
	const fotoAnimada = pagina.querySelector('[data-about-pop]');
	const secaoDaFoto = pagina.querySelector('.nc-about-story');
	const profundidadeDeEntrada = Math.max(15, Math.min(65, Number(fotoAnimada?.dataset.popDepth) || 45)) / 100;
	const elementos = [...pagina.querySelectorAll('[data-about-reveal]')].filter(elemento => elemento !== fotoAnimada);
	const elementosRevelados = new WeakSet();
	let observador;
	let quadroAgendado;
	let fotoProntaParaAnimar = true;

	const mostrarFoto = () => {
		if (!fotoAnimada) return;
		fotoAnimada.classList.remove('nc-about-reveal--waiting');
		fotoAnimada.classList.add('nc-about-pop--visible');
		fotoProntaParaAnimar = false;
	};
	const prepararFoto = () => {
		if (!fotoAnimada) return;
		fotoAnimada.classList.remove('nc-about-pop--visible');
		fotoAnimada.classList.add('nc-about-reveal--waiting');
		fotoProntaParaAnimar = true;
	};
	const configurarFoto = () => {
		if (!fotoAnimada) return;
		fotoAnimada.classList.remove('nc-about-reveal--waiting', 'nc-about-pop--visible');
		fotoProntaParaAnimar = true;
		if (!movimentoReduzido.matches) prepararFoto();
	};
	const atualizarFoto = () => {
		if (!fotoAnimada || !secaoDaFoto || movimentoReduzido.matches) return;
		// Mede a seção sem escala para a animação não alterar o próprio ponto de ativação.
		const limites = secaoDaFoto.getBoundingClientRect();
		if (!fotoProntaParaAnimar && (window.scrollY <= 2 || limites.top >= window.innerHeight * 0.95)) {
			prepararFoto();
			return;
		}
		if (fotoProntaParaAnimar && window.scrollY > 2 && limites.top <= window.innerHeight * (1 - profundidadeDeEntrada) && limites.bottom > 0) mostrarFoto();
	};

	const mostrarElemento = elemento => {
		elemento.classList.remove('nc-about-reveal--waiting');
		elementosRevelados.add(elemento);
		observador?.unobserve(elemento);
	};

	const configurarRevelacao = () => {
		observador?.disconnect();
		elementos.forEach(elemento => elemento.classList.remove('nc-about-reveal--waiting'));
		if (movimentoReduzido.matches || !('IntersectionObserver' in window)) return;
		observador = new IntersectionObserver(entradas => {
			entradas.forEach(entrada => {
				if (!entrada.isIntersecting) return;
				mostrarElemento(entrada.target);
			});
		}, { threshold: 0.08 });
		elementos.forEach(elemento => {
			if (elementosRevelados.has(elemento)) return;
			if (pagina.dataset.reveal !== 'true') return;
			// Mantém visível o conteúdo que já está na tela, inclusive após mudar a preferência de movimento.
			if (elemento.getBoundingClientRect().top < window.innerHeight) {
				mostrarElemento(elemento);
				return;
			}
			observador.observe(elemento);
			elemento.classList.add('nc-about-reveal--waiting');
		});
	};

	const atualizar = () => {
		quadroAgendado = undefined;
		atualizarFoto();
		if (!imagemBanner) return;
		if (movimentoReduzido.matches || !telaGrande.matches || pagina.dataset.parallax !== 'true') {
			imagemBanner.style.transform = '';
			return;
		}
		const limites = banner.getBoundingClientRect();
		if (limites.bottom < 0 || limites.top > window.innerHeight) return;
		const progresso = Math.max(0, Math.min(1, (window.innerHeight - limites.top) / (window.innerHeight + limites.height)));
		const intensidade = Math.max(0, Math.min(120, Number(pagina.dataset.parallaxStrength) || 0));
		imagemBanner.style.transform = `translate3d(0, ${((progresso - 0.5) * intensidade).toFixed(2)}px, 0)`;
	};
	const solicitarAtualizacao = () => {
		if (quadroAgendado === undefined) quadroAgendado = window.requestAnimationFrame(atualizar);
	};
	window.addEventListener('scroll', solicitarAtualizacao, { passive: true });
	window.addEventListener('resize', solicitarAtualizacao);
	telaGrande.addEventListener('change', solicitarAtualizacao);
	movimentoReduzido.addEventListener('change', () => {
		configurarRevelacao();
		configurarFoto();
		solicitarAtualizacao();
	});
	// Revela o conteúdo que recebe foco pelo teclado para ele não permanecer oculto.
	pagina.addEventListener('focusin', evento => {
		const elemento = evento.target.closest('[data-about-reveal]');
		if (elemento === fotoAnimada) mostrarFoto();
		else if (elemento) mostrarElemento(elemento);
	});
	window.addEventListener('pageshow', () => {
			elementos.forEach(elemento => {
			if (elemento.getBoundingClientRect().top < window.innerHeight) mostrarElemento(elemento);
		});
		solicitarAtualizacao();
	});
	configurarRevelacao();
	configurarFoto();
	solicitarAtualizacao();
})();
