$(document).ready(function () {

	/** ******************************
    * Data Tables
    ****************************** **/
	$('#archivedTenants').dataTable({
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
		"columnDefs": [{
			"orderable": false, "targets": 7
		}],
		"order": [[ 6, 'asc' ]],
		"pageLength": 25
	});
	
	$('#archivedTenants_wrapper').addClass('pb-20');
	$('#archivedTenants').addClass('pb-10');

});