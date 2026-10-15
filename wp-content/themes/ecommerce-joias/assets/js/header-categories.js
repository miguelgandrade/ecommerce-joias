(() => {
	const menuCategorias = document.querySelector('.nc-header__category-dropdown');
	if (!menuCategorias) return;
	const acionador = menuCategorias.querySelector('summary');

	// O elemento details já permite clique, toque e teclado sem JavaScript.
	menuCategorias.addEventListener('pointerenter', evento => {
		if (evento.pointerType === 'mouse') menuCategorias.open = true;
	});
	menuCategorias.addEventListener('pointerleave', evento => {
		if (evento.pointerType === 'mouse' && !menuCategorias.contains(document.activeElement)) {
			menuCategorias.open = false;
		}
	});
	menuCategorias.addEventListener('focusout', evento => {
		if (!menuCategorias.contains(evento.relatedTarget)) menuCategorias.open = false;
	});
	document.addEventListener('click', evento => {
		if (!menuCategorias.contains(evento.target)) menuCategorias.open = false;
	});
	document.addEventListener('keydown', evento => {
		if (evento.key !== 'Escape' || !menuCategorias.open) return;
		if (menuCategorias.contains(document.activeElement)) acionador.focus();
		menuCategorias.open = false;
	});
})();
