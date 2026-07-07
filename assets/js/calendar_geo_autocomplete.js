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

		if (!input || !latInput || !lngInput || !endpoint) {
			return;
		}

		dropdown.className = 'dropdown hidden';
		dropdown.style.left = '0';
		dropdown.style.right = '0';
		dropdown.style.top = '100%';
		dropdown.style.position = 'absolute';
		dropdown.style.zIndex = '100';
		container.appendChild(dropdown);

		input.setAttribute('autocomplete', 'off');
		input.setAttribute('placeholder', placeholder);

		function clearResults() {
			dropdown.innerHTML = '';
			dropdown.classList.add('hidden');
		}

		function applySelection(item) {
			input.value = item.formatted || '';
			latInput.value = item.lat || '';
			lngInput.value = item.lon || '';
			clearResults();
		}

		function renderResults(items) {
			clearResults();

			if (!items.length) {
				return;
			}

			var ul = document.createElement('ul');
			ul.className = 'dropdown-contents';

			items.forEach(function (item) {
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

		input.addEventListener('blur', function () {
			window.setTimeout(clearResults, 150);
		});
	});
});
