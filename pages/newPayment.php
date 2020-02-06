<?php
	$todayDate = date("Y-m-d");
	$currentYear = date('Y');
	$currentMonth = date('F');
	$currentDay = date('d');
	$tenantIsLate = '';

	// Get Data
	$qry = "SELECT
				properties.propertyName,
				properties.propertyRate,
				properties.latePenalty,
				leases.*
			FROM
				properties
				LEFT JOIN leases ON properties.propertyId = leases.propertyId
			WHERE
				leases.userId = ".$rs_userId." AND
				leases.leaseId = ".$rs_leaseId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);
	
	$lateRentAmt = $row['propertyRate'] + $row['latePenalty'];

	require_once('includes/paypal.php');
	$show_form = 1;
	
	// Check if the Tenant is late on current month's rent
	$latecheck1 = "SELECT
					users.leaseId,
					leases.leaseStart
				FROM
					users
					LEFT JOIN leases ON users.leaseId = leases.leaseId
				WHERE
					users.leaseId = ".$rs_leaseId." AND
					'".$todayDate."' >= leases.leaseStart";
	$lateres1 = mysqli_query($mysqli, $latecheck1) or die('-8' . mysqli_error());

	if (mysqli_num_rows($lateres1) > 0) {
		$latecheck2 = "SELECT
						payments.*,
						users.leaseId,
						users.propertyId
					FROM
						payments
						LEFT JOIN users ON payments.userId = users.userId
					WHERE
						users.leaseId = ".$rs_leaseId." AND
						payments.rentMonth = '".$currentMonth."' AND
						payments.rentYear = '".$currentYear."'";
		$lateres = mysqli_query($mysqli, $latecheck2) or die('-9' . mysqli_error());
		if (mysqli_num_rows($lateres) > 0) { $tenantIsLate = 'false'; } else { $tenantIsLate = 'true'; }
	} else {
		$tenantIsLate = 'false';
	}
	
	if ($tenantIsLate == 'true') {
		if ($currentDay > '5') {
			$totalToPay = $lateRentAmt;
		} else {
			$totalToPay = $row['propertyRate'];
		}
	} else if ($tenantIsLate == 'false') {
		$totalToPay = '';
	} else {
		$totalToPay = '';
	}
	
	// Get Next Record ID from the Payments Table
	$nxt = "SHOW TABLE STATUS LIKE 'payments'";
	$nxtres = mysqli_query($mysqli, $nxt) or die('-1' . mysqli_error());
	$nxtrow = mysqli_fetch_assoc($nxtres);
	$count = $nxtrow['Auto_increment'];
	$itemId = $count;

	$propPage = 'true';
	$pageTitle = $newPaymentPageTitle;
	$jsFile = 'newPayment';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($rs_leaseId != '0' && $set['enablePayments'] == '1') {
				if ($msgBox) { echo $msgBox; }
		?>
			<h3><?php echo $pageTitle; ?></h3>
			<?php if ($set['enablePaypal'] == '1') { ?>
				<p class="lead"><?php echo $set['siteName']; ?> <?php echo $paymentTypes1; ?></p>
			<?php } else { ?>
				<p class="lead"><?php echo $set['siteName']; ?> <?php echo $paymentTypes2; ?></p>
			<?php } ?>
			
			<?php
				if ($tenantIsLate == 'true') {
					if ($currentDay > '5') {
						echo '<div class="well well-warning well-sm">'.$currAmyPastDueText.' '.formatCurrency($lateRentAmt,$currCode).'</div>';
					} else {
						echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency($row['propertyRate'],$currCode).'</div>';
					}
				} else if ($tenantIsLate == 'false') {
					echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency('0.00',$currCode).'</div>';
				} else {
					echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency($row['propertyRate'],$currCode).'</div>';
				}
			?>
			
			<div class="row">
				<div class="col-md-6">
					<div class="list-group">
						<li class="list-group-item"><?php echo $userMonRentAmtText; ?> <?php echo formatCurrency($row['propertyRate'],$currCode); ?></li>
					</div>
				</div>
				<div class="col-md-6">
					<div class="list-group">
						<li class="list-group-item"><?php echo $addFeeText; ?> <?php echo formatCurrency($row['latePenalty'],$currCode); ?></li>
					</div>
				</div>
			</div>

			<?php if ($set['enablePaypal'] == '1') { ?>
				<h3><?php echo $payPayPalH3; ?></h3>
				<p class="lead mb-0"><?php echo $rentAmtEnteredText; ?></p>
				<p><?php echo $payPalQuip1.' '.$set['paymentFee'].' '.$payPalQuip2; ?></p>
				
				<?php
					if(!empty($_POST["process"]) && $_POST["process"] == "yes") {
						$show_form = 0;

						$paypal = new paypalPaymnents;
						$paypal->addField('business', $set['paymentEmail']);
						$paypal->addField('return', $set['installUrl'].'page.php?page=completed&action=success');
						$paypal->addField('cancel_return', $set['installUrl'].'page.php?page=completed&action=cancel');
						$paypal->addField('notify_url', $set['installUrl'].'page.php?page=completed&action=ipn');
						$paypal->addField('item_name_1', $set['paymentItemName']);
						$paypal->addField('item_number_1', $itemId);
						$paypal->addField('quantity_1', '1');
						$paypal->addField('custom', $_SERVER['REMOTE_ADDR']);
						$paypal->addField('upload', 1);
						$paypal->addField('cmd', '_cart');
						$paypal->addField('txn_type', 'cart');
						$paypal->addField('num_cart_items', 1);
						$paypal->addField('currency_code', strip_tags(str_replace("'","",$_POST["currency"])));
						$paypal->submit_post();

						$show_form = 0;
					}
					if ($show_form == 1) {
				?>
						<form id="ppPayment" name="ppPayment" action="" method="post" enctype="multipart/form-data">
							<div class="errorNote"></div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="priceSet"><?php echo $paymentAmtField; ?></label>
										<input type="text" class="form-control" name="priceSet" id="priceSet" value="<?php echo $totalToPay; ?>" />
										<span class="help-block"><?php echo $paymentAmtFieldHelp; ?></span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="pricePlusFee"><?php echo $totPaypalAmtField; ?></label>
										<input type="text" class="form-control" name="pricePlusFee" id="pricePlusFee" readonly="readonly" />
										<span class="help-block"><?php echo $totPaypalAmtFieldHelp1.' '.$set['paymentFee'].' '.$totPaypalAmtFieldHelp2; ?></span>
									</div>
								</div>
							</div>

							<input type="hidden" name="payFee" id="payFee" value="<?php echo $set['paymentFee'];?>" />
							<input type="hidden" name="amount" value="" />
							<input type="hidden" name="process" value="yes" />
							<input type="hidden" name="currency" value="<?php echo $set['currencyCode']; ?>" />
							<input type="hidden" name="paymentItemName" value="<?php echo $set['paymentItemName']; ?>" />
							<input type="hidden" name="paymentAmount" id="paymentAmount" value="" />
							<input type="hidden" name="item_number" value="<?php echo $itemId; ?>" />
							<button type="input" name="submit" value="Pay" class="btn btn-success btnIcon"><i class="fa fa-check-square-o"></i> <?php echo $payWithPaypalBtn; ?></button>
						</form>
				<?php } ?>
				
				<hr />
			<?php } ?>

			<h3><?php echo $otherPayH3; ?></h3>
			<div class="row">
				<div class="col-md-4">
					<div class="list-group mt-10">
						<li class="list-group-item"><?php echo $payableToText; ?> <?php echo $set['siteName']; ?></li>
						<li class="list-group-item">
							<?php echo $mailToText; ?><br />
							<?php echo nl2br($set['businessAddress']); ?><br />
							<?php echo $set['contactPhone']; ?>
						</li>
					</div>
				</div>
				<div class="col-md-8">
					<p class="lead"><?php echo $mailToQuip; ?></p>
					
					<h3><?php echo $paymentQuestionsH3; ?></h3>
					<p><?php echo $paymentQuestionsQuip; ?></p>
				</div>
			</div>		
		
		<?php } else { ?>
			<hr class="mt-0 mb-0" />
			<h3><?php echo $accessErrorHeader; ?></h3>
			<div class="alertMsg warning mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-warning"></i>
				</div>
				<?php echo $permissionDenied; ?>
			</div>
		<?php } ?>
	</div>