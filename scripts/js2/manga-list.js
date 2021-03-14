for (let select of document.querySelectorAll('.manga-sort-select')) {
	select.addEventListener('change', function(evt) {
		const url = new URL(window.location.href)
		url.searchParams.set('s', this.value)
		url.hash = 'listing'
		window.location = url
	})
}