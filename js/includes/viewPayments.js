$(document).ready(function () {
	$('#payments').dataTable({
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
		"order": [ 4, 'desc' ],
		"pageLength": 25
	});
	
	$('#payments_wrapper').addClass('pb-20');
	$('#payments').addClass('pb-10');
	
	$('#refunds').dataTable({
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
		"order": [ 1, 'desc' ],
		"pageLength": 10
	});
	
	$('#refunds_wrapper').addClass('pb-20');
	$('#refunds').addClass('pb-10');
});