(function($) {
	'use strict';

	$(function() {
		var iconInput = $('#cat_icon');
		var colorInput = $('#cat_color');
		var previewContainer = $('#cat_icon_preview');
		var previewIcon = previewContainer.find('i');

		var updateIconPreview = function() {
			var value = iconInput.val().trim();
			var colorVal = colorInput.val();

			if (value === '') {
				previewIcon.hide();
			} else {
				// Normalize prefix for the preview if needed
				var displayClass = value.toLowerCase();
				if (displayClass.indexOf('fa-') !== 0) {
					displayClass = 'fa-' + displayClass;
				}
				previewIcon.attr('class', 'icon ' + displayClass + ' fa-fw');
				previewIcon.css({
					'color': colorVal,
					'display': 'inline-block'
				});
			}
		};

		// Update preview instantly while typing or changing color
		iconInput.on('input', updateIconPreview);
		colorInput.on('input change', updateIconPreview);

		// Format the input value (auto-prepend 'fa-') on change (blur)
		iconInput.on('change', function() {
			var value = iconInput.val().trim();
			if (value !== '') {
				value = value.toLowerCase();
				if (value.indexOf('fa-') !== 0) {
					value = 'fa-' + value;
				}
				iconInput.val(value);
			}
			updateIconPreview();
		});
	});
})(jQuery);
