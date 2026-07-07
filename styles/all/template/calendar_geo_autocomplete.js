document.addEventListener('DOMContentLoaded', function () {
	var containers = document.querySelectorAll('[data-calendar-geo-autocomplete]');

	containers.forEach(function (container) {
		var input = document.getElementById(container.getAttribute('data-input-id'));
		var latInput = document.getElementById(container.getAttribute('data-lat-id'));
		var lngInput = document.getElementById(container.getAttribute('data-lng-id'));
		var endpoint = container.getAttribute('data-endpoint');
		var placeholder = container.getAttribute('data-placeholder') || '';
		var dropdown = document.createElement('div');
		var debounceTimer = null;
		var currentItems = [];
		var focusedItemIndex = -1;

		if (!input || !latInput || !lngInput || !endpoint) {
			return;
		}

		dropdown.className = 'calendar-geo-dropdown hidden';
		container.appendChild(dropdown);

		input.setAttribute('autocomplete', 'off');
		input.setAttribute('placeholder', placeholder);

		function clearResults() {
			dropdown.innerHTML = '';
			dropdown.classList.add('hidden');
			focusedItemIndex = -1;
		}

		function applySelection(item) {
			input.value = item.formatted || '';
			latInput.value = item.lat || '';
			lngInput.value = item.lon || '';
			clearResults();
		}

		function setActive(index) {
			var links = dropdown.querySelectorAll('li a');
			if (!links.length) return;

			links.forEach(function (a, i) {
				if (i === index) {
					a.classList.add('autocomplete-active');
					a.scrollIntoView({ block: 'nearest' });
				} else {
					a.classList.remove('autocomplete-active');
				}
			});
		}

		function renderResults(items) {
			clearResults();
			currentItems = items;

			if (!items.length) {
				return;
			}

			var ul = document.createElement('ul');
			ul.className = 'calendar-geo-dropdown-contents';

			items.forEach(function (item, index) {
				var li = document.createElement('li');
				var a = document.createElement('a');
				a.href = '#';
				a.textContent = item.formatted || '';
				a.addEventListener('click', function (e) {
					e.preventDefault();
					applySelection(item);
				});
				li.appendChild(a);
				ul.appendChild(li);
			});

			dropdown.appendChild(ul);
			dropdown.classList.remove('hidden');
		}

		function fetchResults(query) {
			window.fetch(endpoint + '?text=' + encodeURIComponent(query), {
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Request failed');
					}

					return response.json();
				})
				.then(function (payload) {
					renderResults(Array.isArray(payload.features) ? payload.features : []);
				})
				.catch(function () {
					clearResults();
				});
		}

		input.addEventListener('input', function () {
			var query = input.value.trim();
			latInput.value = '';
			lngInput.value = '';

			if (debounceTimer) {
				window.clearTimeout(debounceTimer);
			}

			if (query.length < 2) {
				clearResults();
				return;
			}

			debounceTimer = window.setTimeout(function () {
				fetchResults(query);
			}, 180);
		});

		input.addEventListener('keydown', function (e) {
			var links = dropdown.querySelectorAll('li a');
			if (links.length && !dropdown.classList.contains('hidden')) {
				if (e.keyCode === 40) { // Arrow Down
					e.preventDefault();
					focusedItemIndex = (focusedItemIndex === links.length - 1) ? 0 : focusedItemIndex + 1;
					setActive(focusedItemIndex);
				} else if (e.keyCode === 38) { // Arrow Up
					e.preventDefault();
					focusedItemIndex = (focusedItemIndex <= 0) ? links.length - 1 : focusedItemIndex - 1;
					setActive(focusedItemIndex);
				} else if (e.keyCode === 13) { // Enter
					if (focusedItemIndex > -1) {
						e.preventDefault();
						applySelection(currentItems[focusedItemIndex]);
					}
				}
			}
		});

		document.addEventListener('click', function (e) {
			if (!container.contains(e.target)) {
				clearResults();
			}
		});
	});
});
