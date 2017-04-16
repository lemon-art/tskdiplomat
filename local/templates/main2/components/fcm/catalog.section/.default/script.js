$(document).ready(function(){
	$('.sorting select').on('change', function(){
		$(this).parents('form').trigger('submit');
	})
})