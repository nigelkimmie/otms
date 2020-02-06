$(document).ready(function() {
	
	 /** ******************************
	  * PayPal Payments
	  ****************************** **/
	// Calculate the Total to pay on Page Load
	var priceInp = $("#priceSet").val();
	var priceFee = $("#payFee").val();
	
	var fee = Math.round(((priceInp / 100) * priceFee)*100)/100;		// Figure the PayPal Fee
	var sum = Number(priceInp) + Number(fee);							// Add the Rent amount and the PayPal Fee
	var sumAmount = sum.toFixed(2);										// Format the amount to currency
	
	$("#pricePlusFee").val(sumAmount);
	$("[name=amount]").val(sumAmount); 
	$("#paymentAmount").val(sumAmount);
	
	// Calculate the Total to pay on field change/update
	$('#priceSet').blur(function() {
		// If an error is displayed, hide it
		$('.errorNote').fadeOut('slow');

		var priceInp = $("#priceSet").val();
		var priceFee = $("#payFee").val();

		var fee = Math.round(((priceInp / 100) * priceFee)*100)/100;	// Figure the PayPal Fee
		var sum = Number(priceInp) + Number(fee);						// Add the Client entered amount and the PayPal Fee
		var sumAmount = sum.toFixed(2);									// Format the amount to currency

		$("#pricePlusFee").val(sumAmount);
		$("[name=amount]").val(sumAmount);
		$("#paymentAmount").val(sumAmount);
	}); 
	
	// Check the form before allowing the submit to PayPal
	$('#ppPayment').submit(function(){
		if ($('#priceSet').val() == "") {
			result = '<div class="alertMsg warning mb-20"><div class="msgIcon pull-left"><i class="fa fa-warning"></i></div>Please enter the Payment Amount you want to pay by PayPal.</div>';
			$('.errorNote').show().html(result);
			return(false);
		}
	});

});