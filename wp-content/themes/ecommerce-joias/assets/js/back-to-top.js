// A home não carrega o pacote do tema pai que controla este botão.
(() => {
	const botao = document.querySelector('.orchid-backtotop');
	if (!botao) return;
	const atualizarVisibilidade = () => {
		botao.hidden = window.scrollY <= 600;
	};
	window.addEventListener('scroll', atualizarVisibilidade, { passive: true });
	botao.addEventListener('click', () => {
		window.scrollTo({
			top: 0,
			behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
		});
	});
	atualizarVisibilidade();
})();
