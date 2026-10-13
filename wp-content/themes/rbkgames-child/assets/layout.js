(function () {
	var burger = document.querySelector('.sandwich-link');
	var wrap = document.querySelector('.mobile-nav-wrapper');
	function setMenu(open) {
		if (!burger || !wrap) { return; }
		burger.classList.toggle('active', open);
		wrap.classList.toggle('active', open);
		burger.setAttribute('aria-expanded', open ? 'true' : 'false');
	}
	if (burger && wrap) {
		burger.addEventListener('click', function (e) {
			e.preventDefault();
			setMenu(!wrap.classList.contains('active'));
		});
		wrap.addEventListener('click', function (e) {
			var t = e.target.closest('.dropdown-toggle');
			if (!t) { return; }
			var li = t.closest('.dropdown');
			var open = li.classList.toggle('active');
			t.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	var modal = document.getElementById('search-modal');
	function setSearch(open) {
		if (!modal) { return; }
		modal.hidden = !open;
		modal.setAttribute('aria-hidden', open ? 'false' : 'true');
		document.documentElement.classList.toggle('rbk-modal-open', open);
		if (open) { var i = modal.querySelector('input'); if (i) { i.focus(); } }
	}
	document.addEventListener('click', function (e) {
		if (e.target.closest('[data-rbk-search-open]')) { setMenu(false); setSearch(true); }
		else if (e.target.closest('[data-rbk-search-close]')) { setSearch(false); }
	});
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		setSearch(false);
		setMenu(false);
	});
	window.addEventListener('resize', function () {
		if (window.innerWidth > 768) { setMenu(false); }
	});
})();
