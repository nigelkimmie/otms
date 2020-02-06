$(document).ready(function() {

	/** ******************************
    * Data Tables
    ****************************** **/
	$('#templates').dataTable({
		"order": [ 3, 'asc' ],
		"paging": false,		// Disable Pagination
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#templates_wrapper').addClass('pt-0 pb-20');

	$('#forms').dataTable({
		"order": [ 0, 'desc' ],
		"paging": false,		// Disable Pagination
		"searching": false,		// Disable Search
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#forms_wrapper').addClass('pt-0 pb-20');

});