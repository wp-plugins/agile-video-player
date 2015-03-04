jQuery(document).ready(function($){
	// Color picker
	$('.color-picker').wpColorPicker();

	// Reveal AR code
	$('.ar_advance').hide();
	$('#viewarcode').click( function() {
		$('.ar_advance').toggle();
	});

	// Clear AR code
	$('#clear_ar_code').click(function() {
		$('#ar_code').val('');
		$('#ar_name').val('');
		$('#ar_email').val('');
		$('#ar_url').val('');
		$('#ar_hidden').val('');
	});
}); // main jquery container
