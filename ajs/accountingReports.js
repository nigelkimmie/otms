$(document).ready(function() {

	$("[name='payType']").change(function () {
        if ($('#allTypes').is(':checked')) {
            $('.allTypesOpt i.fa').removeClass('fa-circle-o').addClass('fa-dot-circle-o');
        } else {
            $('.allTypesOpt i.fa').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
        }
        if ($('#rentTypes').is(':checked')) {
            $('.rentTypesOpt i.fa').removeClass('fa-circle-o').addClass('fa-dot-circle-o');
        } else {
            $('.rentTypesOpt i.fa').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
        }
        if ($('#otherTypes').is(':checked')) {
            $('.otherTypesOpt i.fa').removeClass('fa-circle-o').addClass('fa-dot-circle-o');
        } else {
            $('.otherTypesOpt i.fa').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
        }
    });

	var weekStart = $('#weekStart').val();
	
	$(".selectall").change(function(e) {
		var ct = e.currentTarget;

		var o = ct.options[0];
		var t = ct.options[0].text;
		var s = ct.options[0].selected;

		if(s && (t == "All Tenants")) {
			for(var i = 1; i < ct.options.length; i++) {
				ct.options[i].selected = false;
			}
		}
	});

	$("#paymentsRep").click(function() {
		if ($('#tenants1 :selected').size() == 0) {
			$('#errNote').html('<div class="alertMsg warning"><div class="msgIcon pull-left"><i class="fa fa-warning"></i></div>Please select at least one Tenant.</div>');
			$('.alertMsg').delay(6000).fadeOut("slow", function() {
				$(this).addClass('hidden');
			});
			return false;
		} else {
			return true;
		}
	});
	
	$("#refundsRep").click(function() {
		if ($('#tenants2 :selected').size() == 0) {
			$('#errNote1').html('<div class="alertMsg warning"><div class="msgIcon pull-left"><i class="fa fa-warning"></i></div>Please select at least one Tenant.</div>');
			$('.alertMsg').delay(6000).fadeOut("slow", function() {
				$(this).addClass('hidden');
			});
			return false;
		} else {
			return true;
		}
	});
	
	$('#accFromDate').datetimepicker({
		format: 'yyyy-mm-dd',
		todayBtn:  0,
		autoclose: 1,
		todayHighlight: 1,
		minView: 2,
		forceParse: 0,
		weekStart: weekStart
	});
	$('#accToDate').datetimepicker({
		format: 'yyyy-mm-dd',
		todayBtn:  0,
		autoclose: 1,
		todayHighlight: 1,
		minView: 2,
		forceParse: 0,
		weekStart: weekStart
	});
	
	$('#refFromDate').datetimepicker({
		format: 'yyyy-mm-dd',
		todayBtn:  0,
		autoclose: 1,
		todayHighlight: 1,
		minView: 2,
		forceParse: 0,
		weekStart: weekStart
	});
	$('#refToDate').datetimepicker({
		format: 'yyyy-mm-dd',
		todayBtn:  0,
		autoclose: 1,
		todayHighlight: 1,
		minView: 2,
		forceParse: 0,
		weekStart: weekStart
	});

});