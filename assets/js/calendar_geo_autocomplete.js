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

		dropdown.className = 'calendar-geo-results';
		dropdown.hidden = true;
		container.appendChild(dropdown);
		input.setAttribute('autocomplete', 'off');
		input.setAttribute('placeholder', placeholder);

		function clearResults() {
			dropdown.innerHTML = '';
			dropdown.hidden = true;
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

			items.forEach(function (item) {
				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'calendar-geo-result';
				button.textContent = item.formatted || '';
				button.addEventListener('click', function () {
					applySelection(item);
				});
				dropdown.appendChild(button);
			});

			dropdown.hidden = false;
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
