jQuery(document).ready(function() {
	top10_callback_set_popup();
});

function top10_callback_set_popup() {
	var top10_callback_popup = jQuery('.top10_popup-with-form').magnificPopup({
		type: 'inline',
		preloader: false
	});

	jQuery(".top10_popup-with-form").on('click', function(){
		jQuery("#top10_slider-time-range").slider({
			range:	true,
			min:	parseInt(jQuery("#top10_feedback").attr("data-time-min")),
			max:	parseInt(jQuery("#top10_feedback").attr("data-time-max")),
			step:	parseInt(jQuery("#top10_feedback").attr("data-time-step")),
			values: [ parseInt(jQuery("#top10_slider-time-range-min").val()), parseInt(jQuery("#top10_slider-time-range-max").val()) ],

			stop: function(e, ui) {
				var hours = Math.floor(ui.value / 60);
				var minutes = ui.value - (hours * 60);

				if(hours.length == 1) hours = '0' + hours;
				if(minutes.length == 1) minutes = '0' + minutes;
				if(minutes == 0) minutes = '00';

				var thisTime = hours+':'+minutes

				jQuery(ui.handle).text(thisTime);

				var values = jQuery("#top10_slider-time-range").slider("values");
				jQuery("#top10_slider-time-range-min").val(values[0]);
				jQuery("#top10_slider-time-range-max").val(values[1]);
			},

			slide: function(e, ui) {
				var hours = Math.floor(ui.value / 60);
				var minutes = ui.value - (hours * 60);

				if(hours.length == 1) hours = '0' + hours;
				if(minutes.length == 1) minutes = '0' + minutes;
				if(minutes == 0) minutes = '00';

				var thisTime = hours+':'+minutes

				jQuery(ui.handle).text(thisTime);

				var values = jQuery("#top10_slider-time-range").slider("values");
				jQuery("#top10_slider-time-range-min").val(values[0]);
				jQuery("#top10_slider-time-range-max").val(values[1]);
			},

			create: function(e, ui) {
				var values = jQuery("#top10_slider-time-range").slider("values");

				for(i=0; i<2; i++) {
					var hours	= Math.floor(values[i] / 60);
					var minutes	= values[i] - (hours * 60);

					if(hours.length == 1)	hours = '0' + hours;
					if(minutes.length == 1)	minutes = '0' + minutes;
					if(minutes == 0)		minutes = '00';

					var thisTime = hours+':'+minutes

					jQuery("#top10_slider-time-range .ui-slider-handle").eq(i).text(thisTime);
				}
			}
		});
	});
}

function top10_callback_success() {
	jQuery("#top10_feedback .mfp-close").click();

	var top10_callback_popup = jQuery('.top10_popup-success').magnificPopup({
		type: 'inline',
		preloader: false
	}).click();
}