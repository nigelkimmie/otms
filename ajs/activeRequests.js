$(document).ready(function () {
	$('#requests').dataTable({
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
		"order": [ 6, 'asc' ],
		"pageLength": 25
	});
	
	$('#requests_wrapper').addClass('pb-20');
	$('#requests').addClass('pb-10');
});