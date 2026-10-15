(() => {
	const cabecalho = document.querySelector('.nc-header[data-hero-overlay="true"]');
	const banner = document.querySelector('.nc-hero, .nc-about-hero');
	if (!cabecalho || !banner) return;
	let quadroAgendado;
	const medirCabecalho = () => {
		document.documentElement.style.setProperty('--nc-overlay-header-height', `${cabecalho.offsetHeight}px`);
	};
	const atualizar = () => {
		quadroAgendado = undefined;
		const barraAdministrativa = document.getElementById('wpadminbar');
		if (barraAdministrativa && window.matchMedia('(max-width: 600px)').matches) {
			cabecalho.style.top = `${Math.max(0, barraAdministrativa.getBoundingClientRect().bottom)}px`;
		} else {
			cabecalho.style.top = '';
		}
		// Restaura o cabeçalho normal quando o banner deixa de preencher seu fundo.
		const permiteSobreposicao = window.matchMedia('(min-width: 641px)').matches;
		cabecalho.classList.toggle('nc-header--over-hero', permiteSobreposicao && banner.getBoundingClientRect().bottom > cabecalho.getBoundingClientRect().bottom + 1);
	};
	const solicitarAtualizacao = () => {
		if (quadroAgendado === undefined) quadroAgendado = window.requestAnimationFrame(atualizar);
	};
	medirCabecalho();
	cabecalho.classList.add('nc-header--hero-enabled');
	document.documentElement.classList.add('nc-has-overlay-header');
	atualizar();
	window.addEventListener('scroll', solicitarAtualizacao, { passive: true });
	window.addEventListener('resize', () => {
		medirCabecalho();
		solicitarAtualizacao();
	});
	window.addEventListener('pageshow', solicitarAtualizacao);
	if ('ResizeObserver' in window) new ResizeObserver(() => {
		medirCabecalho();
		solicitarAtualizacao();
	}).observe(cabecalho);
})();
