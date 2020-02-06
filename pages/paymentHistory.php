<?php
	$hasData = '';
	
	if ($rs_isResident == '1') {
		$q = "SELECT
				users.primaryTenantId,
				(SELECT leases.userId
					FROM leases
					WHERE leases.userId = users.primaryTenantId
				) AS primaryTenId
			FROM
				users
			WHERE
				users.userId = ".$rs_userId;
		$r = mysqli_query($mysqli, $q) or die('-0' . mysqli_error());
		$qr = mysqli_fetch_assoc($r);
		$primaryTenId = $qr['primaryTenId'];
	} else {
		$primaryTenId = $rs_userId;
	}
	
	// Check for Any Payments
	$check = "SELECT
				payments.*,
				properties.propertyName,
				leases.*
			FROM
				payments
				LEFT JOIN properties ON payments.propertyId = properties.propertyId
				LEFT JOIN leases ON payments.leaseId = leases.leaseId
			WHERE
				payments.userId = ".$primaryTenId;
	$checkres = mysqli_query($mysqli, $check) or die('-2' . mysqli_error());
	$totalDisc = mysqli_num_rows($checkres);

	if ($totalDisc > 0) { $hasData = 'true'; }

	// Get Payment Data
	$qry = "SELECT
				payments.*,
				properties.propertyName,
				leases.*
			FROM
				payments
				LEFT JOIN properties ON payments.propertyId = properties.propertyId
				LEFT JOIN leases ON payments.leaseId = leases.leaseId
			WHERE
				payments.userId = ".$primaryTenId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	
	if (mysqli_num_rows($res) > 0) {
		// Get the Totals
		$totals = "SELECT
					SUM(amountPaid) AS totalPaid,
					SUM(penaltyFee) AS totalFee
				FROM
					payments
				WHERE
					userId = ".$primaryTenId;
		$total = mysqli_query($mysqli, $totals) or die('-3' . mysqli_error());
		$tot = mysqli_fetch_assoc($total);

		$reftotals = "SELECT
						SUM(refundAmount) AS refundAmount
					FROM
						refunds
					WHERE
						userId = ".$primaryTenId;
		$totRef = mysqli_query($mysqli, $reftotals) or die('-4' . mysqli_error());
		$tr = mysqli_fetch_assoc($totRef);

		// Format the Amounts
		$totreceived = $tot['totalPaid'] + $tot['totalFee'] - $tr['refundAmount'];
		$totalReceived = formatCurrency($totreceived,$currCode);
		
		$totalRef = formatCurrency($tr['refundAmount'],$currCode);

		// Get Refund Data
		$sql = "SELECT
					refunds.*,
					payments.paymentFor,
					properties.propertyName,
					leases.*
				FROM
					refunds
					LEFT JOIN payments ON refunds.payId = payments.payId
					LEFT JOIN properties ON refunds.propertyId = properties.propertyId
					LEFT JOIN leases ON refunds.leaseId = leases.leaseId
				WHERE refunds.userId = ".$primaryTenId;
		$results = mysqli_query($mysqli, $sql) or die('-5' . mysqli_error());
	}

	$userPage = 'true';
	$pageTitle = $payHistPageTitle;
	$addCss = '<link href="css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'paymentHistory';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($set['enablePayments'] == '1') {
				if ($msgBox) { echo $msgBox; }
		?>
		
			<h3><?php echo $allPayRecH3; ?></h3>
			<?php if ($hasData != '') { ?>
				<table id="payments" class="display" cellspacing="0">
					<thead>
						<tr>
							<th><?php echo $propertyHead; ?></th>
							<th class="text-center"><?php echo $leaseDatesHead; ?></th>
							<th class="text-center"><?php echo $paidByHead; ?></th>
							<th class="text-center"><?php echo $amountPaidHead; ?></th>
							<th class="text-center"><?php echo $lateFeePaidHead; ?></th>
							<th class="text-center"><?php echo $paymentDateHead; ?></th>
							<th class="text-center"><?php echo $renatlMonthHead; ?></th>
							<th class="text-center"><?php echo $totalPaidHead; ?></th>
							<?php if ($rs_isResident != '1') { ?>
								<th class="text-center"></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
							while ($pay = mysqli_fetch_assoc($res)) {
								// Format the Amounts
								$paymentAmount = formatCurrency($pay['amountPaid'],$currCode);
								$total = $pay['amountPaid'] + $pay['penaltyFee'];
								$totalPaid = formatCurrency($total,$currCode);

								// Check for Refunds
								$refunds = "SELECT
												SUM(refundAmount) AS refundAmount
											FROM
												refunds
											WHERE
												payId = ".$pay['payId'];
								$refTotal = mysqli_query($mysqli, $refunds) or die('-6' . mysqli_error());
								$refTot = mysqli_fetch_assoc($refTotal);
								// Format the Amount
								$newTotal = $pay['amountPaid'] + $pay['penaltyFee'] - $refTot['refundAmount'];
								$newAmt = formatCurrency($newTotal,$currCode);

								if ($pay['hasRefund'] == '1') {
									$totPaid = '<span data-toggle="tooltip" data-placement="left" title="'.$amtReflectsRefText.'">'.$newAmt.' <sup><i class="fa fa-asterisk text-warning"></i></sup></span>';
								} else {
									$totPaid = $totalPaid;
								}

								if ($pay['penaltyFee'] != '') { $penaltyFee = formatCurrency($pay['penaltyFee'],$currCode); } else { $penaltyFee = '<em class="text-muted">'.$noneText.'</em>'; }
								if ($pay['rentMonth'] != '') { $rentMonth = clean($pay['rentMonth']).' '.clean($pay['rentYear']); } else { $rentMonth = '<em class="text-muted">'.$naText.'</em>'; }
						?>
								<tr>
									<td><?php echo clean($pay['propertyName']); ?></td>
									<td class="text-center"><?php echo shortDateFormat($pay['leaseStart']).' &mdash; '.shortDateFormat($pay['leaseEnd']); ?></td>
									<td class="text-center"><?php echo clean($pay['paymentType']); ?></td>
									<td class="text-center"><?php echo $paymentAmount; ?></td>
									<td class="text-center"><?php echo $penaltyFee; ?></td>
									<td class="text-center"><?php echo shortDateFormat($pay['paymentDate']); ?></td>
									<td class="text-center"><?php echo $rentMonth; ?></td>
									<td class="text-center"><?php echo $totPaid; ?></td>
									<?php if ($rs_isResident != '1') { ?>
										<td class="text-center">
											<a href="page.php?page=receipt&payId=<?php echo $pay['payId']; ?>" target="_blank" data-toggle="tooltip" data-placement="left" title="<?php echo $viewReceiptText; ?>">
												<i class="fa fa-print"></i>
											</a>
										</td>
									<?php } ?>
								</tr>
						<?php } ?>
					</tbody>
				</table>
				
				<span class="reportTotal"><strong><?php echo $totalReceivedText; ?></strong> <?php echo $totalReceived; ?></span>
				
				<hr />
				
				<h3><?php echo $allRefIssH3; ?></h3>
				<?php if(mysqli_num_rows($results) < 1) { ?>
					<div class="alertMsg default mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-info-circle"></i>
						</div>
						<?php echo $noRefIssMsg; ?>
					</div>
				<?php } else { ?>
					<table id="refunds" class="display" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo $propertyHead; ?></th>
								<th class="text-center"><?php echo $leaseDatesHead; ?></th>
								<th><?php echo $origPaymentHead; ?></th>
								<th class="text-center"><?php echo $refundDateHead; ?></th>
								<th class="text-center"><?php echo $refunfForHead; ?></th>
								<th class="text-right"><?php echo $refundAmtHead; ?></th>
							</tr>
						</thead>

						<tbody>
							<?php
								while ($col = mysqli_fetch_assoc($results)) {
							?>
									<tr>
										<td><?php echo clean($col['propertyName']); ?></td>
										<td class="text-center"><?php echo shortDateFormat($col['leaseStart']).' &mdash; '.shortDateFormat($col['leaseEnd']); ?></td>
										<td><?php echo clean($col['paymentFor']); ?></td>
										<td class="text-center"><?php echo shortDateFormat($col['refundDate']); ?></td>
										<td class="text-center"><?php echo clean($col['refundFor']); ?></td>
										<td class="text-right"><?php echo formatCurrency($col['refundAmount'],$currCode); ?></td>
									</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<span class="reportTotal"><strong><?php echo $totRefundedText; ?></strong> <?php echo $totalRef; ?></span>
					<div class="clearfix mb-20"></div>
				<?php } ?>
			<?php } else { ?>
				<div class="alertMsg default mb-20">
					<div class="msgIcon pull-left">
						<i class="fa fa-info-circle"></i>
					</div>
					<?php echo $noUserPaymentsMsg; ?>
				</div>
			<?php } ?>
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