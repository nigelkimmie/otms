$(document).ready(function () {
	var signinForm = $('.signin-form');
	var signupForm = $('.signup-form');
	var resetpassForm = $('.resetpass-form');
	
	
	$('#signin-form').click(function(e) {
		e.preventDefault();
		$(signupForm).hide();
		$(resetpassForm).hide();
		$(signinForm).fadeIn("slow");
	});
	
	$('#signup-form').click(function(e) {
		e.preventDefault();
		$(signinForm).hide();
		$(resetpassForm).hide();
		$(signupForm).fadeIn("slow");
	});
	
	$('#password-form').click(function(e) {
		e.preventDefault();
		$(signinForm).hide();
		$(signupForm).hide();
		$(resetpassForm).fadeIn("slow");
	});
	
	$("[data-toggle='tooltip']").tooltip();
	
	$('.msgClose').click(function(e){
		e.preventDefault();
		$(this).closest('.alertMsg').fadeOut("slow", function() {
			$(this).addClass('hidden');
		});
	});
	
	// Show Error State on empty Required Fields
	$("form :input[required='required']").blur(function() {
		if (!$(this).val()) {
			$(this).addClass('hasError');
		} else {
			if ($(this).hasClass('hasError')) {
				$(this).removeClass('hasError');
			}
		}
	});
	$("form :input[required='required']").change(function() {
		if ($(this).hasClass('hasError')) {
			$(this).removeClass('hasError');
		}
	});
});