(() => {
	const trilho = document.querySelector('.nc-category-carousel__track');
	if (!trilho) return;
	const carrossel = trilho.closest('.nc-category-carousel');
	const controles = carrossel.querySelector('.nc-category-carousel__controls');
	const botaoAnterior = controles.querySelector('[data-category-previous]');
	const botaoProximo = controles.querySelector('[data-category-next]');
	const botaoDePausa = carrossel.querySelector('[data-category-pause]');
	const cartoesOriginais = [...trilho.querySelectorAll('.nc-category-card')];
	const movimentoReduzido = window.matchMedia('(prefers-reduced-motion: reduce)');
	const velocidade = Math.max(10, Math.min(80, Number(carrossel.dataset.speed) || 28));
	let larguraDoCiclo = 0;
	let distanciaEntreCartoes = 0;
	let posicao = 0;
	let ponteiroSobreCarrossel = false;
	let focoNoCarrossel = false;
	let interacaoPorPonteiro = false;
	let pausado = false;
	let visivel = !('IntersectionObserver' in window);
	let quadroAgendado;
	let tempoAnterior;
	let movimentoManual;
	let temporizadorDeRetomada;

	const normalizarPosicao = valor => larguraDoCiclo + ((valor - larguraDoCiclo) % larguraDoCiclo + larguraDoCiclo) % larguraDoCiclo;
	const podeAnimar = () => larguraDoCiclo > 0 && carrossel.dataset.autoplay === 'true' &&
		!movimentoReduzido.matches && !pausado && !ponteiroSobreCarrossel && !focoNoCarrossel && !interacaoPorPonteiro && visivel && !document.hidden;
	const animarQuadro = tempoAtual => {
		quadroAgendado = undefined;
		if (movimentoManual) {
			movimentoManual.start ??= tempoAtual;
			const progresso = Math.min(1, (tempoAtual - movimentoManual.start) / 320);
			const progressoSuavizado = 1 - Math.pow(1 - progresso, 3);
			posicao = normalizarPosicao(movimentoManual.from + movimentoManual.distance * progressoSuavizado);
			if (progresso === 1) movimentoManual = undefined;
		} else if (podeAnimar() && tempoAnterior !== undefined) {
			posicao = normalizarPosicao(posicao + velocidade * Math.min(tempoAtual - tempoAnterior, 50) / 1000);
		}
		trilho.scrollLeft = posicao;
		tempoAnterior = tempoAtual;
		if (movimentoManual || podeAnimar()) quadroAgendado = window.requestAnimationFrame(animarQuadro);
		else tempoAnterior = undefined;
	};
	const atualizarAnimacao = () => {
		if ((movimentoManual || podeAnimar()) && quadroAgendado === undefined) {
			tempoAnterior = undefined;
			quadroAgendado = window.requestAnimationFrame(animarQuadro);
		} else if (!movimentoManual && !podeAnimar() && quadroAgendado !== undefined) {
			window.cancelAnimationFrame(quadroAgendado);
			quadroAgendado = undefined;
			tempoAnterior = undefined;
		}
	};
	const clonarGrupo = () => {
		const fragmento = document.createDocumentFragment();
		cartoesOriginais.forEach(cartao => {
			const copia = cartao.cloneNode(true);
			copia.dataset.carouselClone = 'true';
			copia.setAttribute('aria-hidden', 'true');
			copia.setAttribute('tabindex', '-1');
			copia.removeAttribute('id');
			copia.querySelectorAll('[id]').forEach(elemento => elemento.removeAttribute('id'));
			fragmento.append(copia);
		});
		return fragmento;
	};
	const montarCarrossel = () => {
		const proporcaoDoCiclo = larguraDoCiclo ? (normalizarPosicao(posicao) - larguraDoCiclo) / larguraDoCiclo : 0;
		trilho.querySelectorAll('[data-carousel-clone]').forEach(copia => copia.remove());
		movimentoManual = undefined;
		larguraDoCiclo = 0;
		controles.hidden = cartoesOriginais.length < 2;
		botaoDePausa.hidden = cartoesOriginais.length < 2 || carrossel.dataset.autoplay !== 'true' || movimentoReduzido.matches;
		if (cartoesOriginais.length < 2) {
			atualizarAnimacao();
			return;
		}
		distanciaEntreCartoes = cartoesOriginais[0].getBoundingClientRect().width + (parseFloat(window.getComputedStyle(trilho).columnGap) || 0);
		larguraDoCiclo = distanciaEntreCartoes * cartoesOriginais.length;
		if (!larguraDoCiclo) return;
		// Cria cópias antes e depois dos cartões originais para preencher a área visível e permitir o ciclo contínuo.
		trilho.prepend(clonarGrupo());
		const quantidadeDeRepeticoes = Math.ceil(trilho.clientWidth / larguraDoCiclo) + 1;
		for (let indice = 0; indice < quantidadeDeRepeticoes; indice++) trilho.append(clonarGrupo());
		posicao = larguraDoCiclo + proporcaoDoCiclo * larguraDoCiclo;
		trilho.scrollLeft = posicao;
		atualizarAnimacao();
	};
	const mover = direcao => {
		if (!larguraDoCiclo) return;
		posicao = normalizarPosicao(trilho.scrollLeft);
		if (movimentoReduzido.matches) {
			posicao = normalizarPosicao(posicao + direcao * distanciaEntreCartoes);
			trilho.scrollLeft = posicao;
		} else {
			movimentoManual = { from: posicao, distance: direcao * distanciaEntreCartoes };
			atualizarAnimacao();
		}
	};
	botaoAnterior.addEventListener('click', () => mover(-1));
	botaoProximo.addEventListener('click', () => mover(1));
	botaoDePausa.addEventListener('click', () => {
		pausado = !pausado;
		botaoDePausa.setAttribute('aria-pressed', String(pausado));
		botaoDePausa.textContent = pausado ? botaoDePausa.dataset.resumeLabel : botaoDePausa.dataset.pauseLabel;
		atualizarAnimacao();
	});
	carrossel.addEventListener('pointerenter', evento => {
		if (evento.pointerType === 'touch') return;
		ponteiroSobreCarrossel = true;
		atualizarAnimacao();
	});
	carrossel.addEventListener('pointerleave', () => { ponteiroSobreCarrossel = false; atualizarAnimacao(); });
	carrossel.addEventListener('focusin', evento => {
		focoNoCarrossel = evento.target.matches(':focus-visible');
		atualizarAnimacao();
	});
	carrossel.addEventListener('focusout', evento => {
		focoNoCarrossel = carrossel.contains(evento.relatedTarget) && evento.relatedTarget.matches(':focus-visible');
		atualizarAnimacao();
	});
	trilho.addEventListener('pointerdown', () => {
		window.clearTimeout(temporizadorDeRetomada);
		interacaoPorPonteiro = true;
		movimentoManual = undefined;
		atualizarAnimacao();
	});
	const programarRetomada = () => {
		if (!interacaoPorPonteiro) return;
		// Espera a rolagem por toque estabilizar antes de retomar o movimento automático.
		temporizadorDeRetomada = window.setTimeout(() => { interacaoPorPonteiro = false; atualizarAnimacao(); }, 1500);
	};
	window.addEventListener('pointerup', programarRetomada);
	window.addEventListener('pointercancel', programarRetomada);
	trilho.addEventListener('scroll', () => {
		if (!larguraDoCiclo || movimentoManual || podeAnimar()) return;
		posicao = normalizarPosicao(trilho.scrollLeft);
		if (Math.abs(posicao - trilho.scrollLeft) > 1) trilho.scrollLeft = posicao;
	}, { passive: true });
	trilho.addEventListener('keydown', evento => {
		if (evento.target !== trilho) return;
		if (evento.key === 'ArrowLeft' || evento.key === 'ArrowRight') {
			evento.preventDefault();
			mover(evento.key === 'ArrowLeft' ? -1 : 1);
		}
	});
	document.addEventListener('visibilitychange', atualizarAnimacao);
	movimentoReduzido.addEventListener('change', montarCarrossel);
	if ('IntersectionObserver' in window) new IntersectionObserver(entradas => {
		visivel = entradas[0].isIntersecting;
		atualizarAnimacao();
	}, { threshold: 0.05 }).observe(carrossel);
	if ('ResizeObserver' in window) new ResizeObserver(montarCarrossel).observe(trilho);
	else window.addEventListener('resize', montarCarrossel);
	montarCarrossel();
})();
