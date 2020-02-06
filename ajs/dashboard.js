$(document).ready(function () {

	/** ******************************
    * Data Tables
    ****************************** **/
	$('#lateRent').dataTable({
		"paging": false,		// Disable Pagination
		"searching": false,		// Disable Search
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#lateRent_wrapper').addClass('pt-0 pb-20');
	
	$('#availProp').dataTable({
		"paging": false,		// Disable Pagination
		"searching": false,		// Disable Search
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#availProp_wrapper').addClass('pt-0 pb-20');
	
	$('#rentReceived').dataTable({
		"columnDefs": [{
			"orderable": false, "targets": 7
		}],
		"paging": false,		// Disable Pagination
		"searching": false,		// Disable Search
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#rentReceived_wrapper').addClass('pt-0 pb-20');

});