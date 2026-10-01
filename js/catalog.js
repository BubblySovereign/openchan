if (active_page == 'catalog') $(function(){
	if (localStorage.catalog !== undefined) {
		var catalog = JSON.parse(localStorage.catalog);
	} else {
		var catalog = {};
		localStorage.catalog = JSON.stringify(catalog);
	}

	// Merge sort
	function mergeSort(arr, compare) {
		if (arr.length <= 1) {
			return arr;
		}

		var middle = Math.floor(arr.length / 2);
		var left = mergeSort(arr.slice(0, middle), compare);
		var right = mergeSort(arr.slice(middle), compare);

		return merge(left, right, compare);
	}

	function merge(left, right, compare) {
		var result = [];
		var i = 0;
		var j = 0;

		while (i < left.length && j < right.length) {
			if (compare(left[i], right[j]) <= 0) {
				result.push(left[i++]);
			} else {
				result.push(right[j++]);
			}
		}

		while (i < left.length) {
			result.push(left[i++]);
		}

		while (j < right.length) {
			result.push(right[j++]);
		}

		return result;
	}

	$("#sort_by").change(function(){
		var value = this.value;

		if (value == "random") {
			$('#Grid').mixItUp('sort', value);
		} else {
			var items = $('#Grid .grid-li').get();

			items = mergeSort(items, function(a, b) {
				var aValue = $(a).attr('data-' + value);
				var bValue = $(b).attr('data-' + value);

				aValue = aValue !== undefined ? aValue.toLowerCase() : '';
				bValue = bValue !== undefined ? bValue.toLowerCase() : '';

				return aValue.localeCompare(bValue);
			});

			$('#Grid').append(items);
		}

		catalog.sort_by = value;
		localStorage.catalog = JSON.stringify(catalog);
	});

	$("#image_size").change(function(){
		var value = this.value, old;
		$(".grid-li").removeClass("grid-size-vsmall");
		$(".grid-li").removeClass("grid-size-small");
		$(".grid-li").removeClass("grid-size-large");
		$(".grid-li").addClass("grid-size-"+value);
		catalog.image_size = value;
		localStorage.catalog = JSON.stringify(catalog);
	});

	$('#Grid').mixItUp({
		animation: {
			enable: false
		}
	});

	if (catalog.sort_by !== undefined) {
		$('#sort_by').val(catalog.sort_by).trigger('change');
	}
	if (catalog.image_size !== undefined) {
		$('#image_size').val(catalog.image_size).trigger('change');
	}

	$('div.thread').on('click', function(e) {
		if ($(this).css('overflow-y') === 'hidden') {
			$(this).css('overflow-y', 'auto');
			$(this).css('width', '100%');
		} else {
			$(this).css('overflow-y', 'hidden');
			$(this).css('width', 'auto');
		}
	});
});
