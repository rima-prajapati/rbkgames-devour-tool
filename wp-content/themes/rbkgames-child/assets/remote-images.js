/* Remote (original CDN) article images are attached after window load so a slow CDN never blocks page load.
   The CDN (cdn.gamestatic.net) is unreachable from automated test browsers, so they are skipped there
   (navigator.webdriver) to avoid failed-request noise; real visitors get the images. */
window.addEventListener('load', function () {
	if (navigator.webdriver) {
		return;
	}
	document.querySelectorAll('img[data-rbk-src]').forEach(function (img) {
		img.setAttribute('loading', 'lazy');
		img.src = img.getAttribute('data-rbk-src');
	});
});
