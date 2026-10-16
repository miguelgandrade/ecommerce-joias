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
		evento.preventDefault();
		if (menuCategorias.contains(document.activeElement)) acionador.focus();
		menuCategorias.open = false;
	});
})();


// Menu móvel expansível: mantém a mesma navegação e os mesmos links do desktop.
(() => {
	const header = document.querySelector('.nc-header');
	const button = header?.querySelector('.nc-header__menu-toggle');
	const navigation = header?.querySelector('#nc-primary-navigation');
	if (!button || !navigation) return;
	const mobile = window.matchMedia('(max-width: 640px)');
	let open = false;
	const update = (restoreFocus = false) => {
		button.hidden = !mobile.matches;
		navigation.hidden = mobile.matches && !open;
		navigation.toggleAttribute('data-mobile-open', mobile.matches && open);
		button.setAttribute('aria-expanded', String(mobile.matches && open));
		button.querySelector('span').textContent = open && mobile.matches ? 'Fechar' : 'Menu';
		if (!open) navigation.querySelectorAll('details').forEach(item => { item.open = false; });
		if (restoreFocus && mobile.matches) button.focus();
	};
	document.documentElement.classList.add('nc-has-mobile-menu');
	button.addEventListener('click', () => { open = !open; update(); });
	document.addEventListener('keydown', event => {
		if (event.key !== 'Escape' || event.defaultPrevented || !mobile.matches || !open) return;
		event.preventDefault(); open = false; update(true);
	});
	document.addEventListener('click', event => {
		if (!mobile.matches || !open || navigation.contains(event.target) || button.contains(event.target)) return;
		open = false; update();
	});
	navigation.addEventListener('click', event => {
		if (mobile.matches && event.target.closest('a')) { open = false; update(); }
	});
	mobile.addEventListener('change', () => {
		open = false; update();
		if (!mobile.matches && document.activeElement === button) navigation.querySelector('a')?.focus();
	});
	update();
})();
