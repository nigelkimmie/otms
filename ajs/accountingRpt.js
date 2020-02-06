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
		"order": [[ 0, 'asc' ],[ 3, 'asc' ]],
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
		"order": [[ 0, 'asc' ],[ 3, 'asc' ]],
		"pageLength": 50
	});
	
	$('#rpt2_wrapper').addClass('mt-20 pb-20');
	$('#rpt2').addClass('pb-10');

});