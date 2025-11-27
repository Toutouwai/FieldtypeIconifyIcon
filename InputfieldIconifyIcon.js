$(function() {

	function initInputfieldIconifyIcon($inputfield) {
		const $container = $inputfield.find('.iii-container');
		const $searchInput = $inputfield.find('.iii-search');
		const $results = $inputfield.find('.iii-results');
		const $selected = $inputfield.find('.iii-selected');
		const $clear = $inputfield.find('.iii-clear');
		const $input = $inputfield.find('.iii-input');
		const prefixes = $searchInput.length ? $searchInput.data('prefixes').replace(/\s*,\s*/g, ',') : null;

		let debounceTimer;

		// On search input keyup or change
		$searchInput.on('keyup paste', function(event) {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(function() {
				const value = $searchInput.val();
				if(value.length > 1) {
					$results.empty().append('<i class="fa fa-fw fa-spin fa-spinner"></i>').css('display', 'flex');
					const query = new URLSearchParams();
					//query.set('limit', 50);
					query.set('limit', 999);
					if(prefixes) query.set('prefixes', prefixes);
					if(value.includes(':')) {
						const pieces = value.split(':');
						// Only allow prefixes from search input if no prefixes are defined for the inputfield
						if(!prefixes) query.set('prefixes', pieces[0].replace(/\s*,\s*/g, ','));
						query.set('query', pieces[1]);
					} else {
						query.set('query', value);
					}
					$.getJSON(`https://api.iconify.design/search?${query.toString()}`)
						.done(function(data) {
							$results.empty();
							if(data.icons.length) {
								$results.append('<div class="iii-choose-notice">' + $results.data('choose') + '</div>');
								for(const item of data.icons) {
									const pieces = item.split(':');
									const collection = pieces[0];
									const name = pieces[1];
									const url = `https://api.iconify.design/${collection}/${name}.svg`;
									const img = `<img src="${url}" alt="${name}" title="${item}" data-name="${item}">`;
									$results.append(img);
								}
							} else {
								$results.html($results.data('no-results'));
							}
						})
						.fail(function(jqXHR, textStatus, errorThrown) {
							$results.empty().css('display', 'flex');
							$results.append(`<div class="iii-error-notice">Error fetching icons</div>`);
						});
				} else {
					$results.empty().hide();
				}
			}, 300); // Wait 300ms after typing stops
		});

		// When a results icon is clicked
		$results.on('click', 'img', function() {
			const $img = $(this);
			const src = $img.attr('src');
			const name = $img.data('name');
			const saveName = 'iconify--' + name.replace(':', '--');
			$input.val(saveName).trigger('change');
			$selected.css('background-image', `url(${src})`).attr('title', name);
			$searchInput.val('');
			$results.empty().hide();
			$container.addClass('has-selection');
		});

		// When the clear button is clicked
		$clear.on('click', function() {
			$input.val('').trigger('change');
			$selected.css('background-image', '');
			$container.removeClass('has-selection');
		});
	}

	// Init on DOM ready
	$('.InputfieldIconifyIcon').each(function() {
		initInputfieldIconifyIcon($(this));
	});

	// Init when inputfield is reloaded
	$(document).on('reloaded', '.InputfieldIconifyIcon', function() {
		initInputfieldIconifyIcon($(this));
	});

});
