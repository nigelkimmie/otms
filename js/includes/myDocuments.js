$(document).ready(function () {
	$('#docs').dataTable({
		"order": [ 3, 'desc' ],
		"pageLength": 10
	});
	
	$('#docs_wrapper').addClass('pb-20');
	$('#docs').addClass('pb-10');
});