$(document).ready(function() {
	
	/** ******************************
    * Data Tables
    ****************************** **/
	$('#rpt1').dataTable({
		"sDom": 'T<"clear">lfrtip',
		"oTableTools": {
			"sSwfPath": "js/swf/copy_csv_xls_pdf.swf",
			"aButtons": [
				"copy",
				"csv",
				"pdf",
				"print"
			]
		},
		"pageLength": 50
	});
	
	$('#rpt1_wrapper').addClass('mt-20 pb-20');
	$('#rpt1').addClass('pb-10');
	
	$('#rpt2').dataTable({
		"sDom": 'T<"clear">lfrtip',
		"oTableTools": {
			"sSwfPath": "js/swf/copy_csv_xls_pdf.swf",
			"aButtons": [
				"copy",
				"csv",
				"pdf",
				"print"
			]
		},
		"paging": false,		// Disable Pagination
		"searching": false,		// Disable Search
		"bInfo": false			// Disable "Showing x to x of x entries"
	});
	
	$('#rpt2_wrapper').addClass('pt-0 pb-20');

});