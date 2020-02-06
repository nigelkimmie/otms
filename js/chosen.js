$(document).ready(function(){
	/** ******************************
	 * Choosen Selects
	 * http://harvesthq.github.io/chosen/options.html
	 ****************************** **/
	$(function () {
		"use strict";
		var configChosen = {
		  '.chosen-select'           : {},
		  '.chosen-select-deselect'  : {allow_single_deselect: true},
		  '.chosen-select-no-single' : {disable_search_threshold: 5},
		  '.chosen-select-no-results': {no_results_text: 'Nothing Found'},
		  '.chosen-select-width'     : {width: "100%"}
		}
		for (var selector in configChosen) {
		  $(selector).chosen(configChosen[selector]);
		}
	});
	
	$(".chosen-select").chosen({disable_search_threshold: 5});
});