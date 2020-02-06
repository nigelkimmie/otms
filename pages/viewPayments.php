<?php
	if ($rs_isResident == '1') {
		$q = "SELECT
				users.primaryTenantId,
				(SELECT leases.userId
					FROM leases
					WHERE leases.userId = users.primaryTenantId
				) AS primaryTenId,
				(SELECT leases.leaseId
					FROM leases
					WHERE leases.userId = users.primaryTenantId
				) AS primaryLeaseId
			FROM
				users
			WHERE
				users.userId = ".$rs_userId;
		$r = mysqli_query($mysqli, $q) or die('-0' . mysqli_error());
		$qr = mysqli_fetch_assoc($r);
		$primaryTenId = $qr['primaryTenId'];
		$primaryLeaseId = $qr['primaryLeaseId'];
	} else {
		$primaryTenId = $rs_userId;
		$primaryLeaseId = $rs_leaseId;
	}

	// Get Payment Data
	$qry = "SELECT
				payments.*,
				users.propertyId
			FROM
				payments
				LEFT JOIN users ON payments.userId = users.userId
			WHERE
				users.userId = ".$primaryTenId." AND
				payments.leaseId = ".$primaryLeaseId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	
	// Get the Totals
	$totals = "SELECT
				SUM(amountPaid) AS totalPaid,
				SUM(penaltyFee) AS totalFee
			FROM
				payments
			WHERE
				leaseId = ".$primaryLeaseId;
	$total = mysqli_query($mysqli, $totals) or die('-2' . mysqli_error());
	$tot = mysqli_fetch_assoc($total);

	$reftotals = "SELECT
					SUM(refundAmount) AS refundAmount
				FROM
					refunds
				WHERE
					leaseId = ".$primaryLeaseId;
	$totRef = mysqli_query($mysqli, $reftotals) or die('-3' . mysqli_error());
	$tr = mysqli_fetch_assoc($totRef);

	// Format the Amounts
	$totreceived = $tot['totalPaid'] + $tot['totalFee'] - $tr['refundAmount'];
	$totalReceived = formatCurrency($totreceived,$currCode);
	
	$totalRef = formatCurrency($tr['refundAmount'],$currCode);

	// Get Refund Data
	$sql = "SELECT
				refunds.*,
				payments.paymentFor
			FROM
				refunds
				LEFT JOIN payments ON refunds.payId = payments.payId
			WHERE refunds.leaseId = ".$primaryLeaseId;
	$results = mysqli_query($mysqli, $sql) or die('-4' . mysqli_error());
	
	// Get Lease Data
	$sql = "SELECT
					properties.*,
					leases.*,
					admins.adminName
				FROM
					properties
					LEFT JOIN leases ON properties.propertyId = leases.propertyId
					LEFT JOIN assigned ON properties.propertyId = assigned.propertyId
					LEFT JOIN admins ON assigned.adminId = admins.adminId
				WHERE
					leases.leaseId = ".$primaryLeaseId;
	$result = mysqli_query($mysqli, $sql) or die('-2' . mysqli_error());
	$rows = mysqli_fetch_assoc($result);
	
	$profileurl = preg_replace('/ /', '-', clean($rows['adminName']));

	$propPage = 'true';
	$pageTitle = $viewPaymentsPageTitle;
	$addCss = '<link href="css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'viewPayments';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($rs_leaseId != '0' && $set['enablePayments'] == '1') {
				if ($msgBox) { echo $msgBox; }
		?>
			<div class="row mb-10">
				<div class="col-md-3">
					<ul class="list-group mt-20">
						<li class="list-group-item group-item-sm">
							<strong><?php echo $newPaymentEmail2; ?></strong> <?php echo clean($rows['propertyName']); ?>
						</li>
						<li class="list-group-item group-item-sm">
							<strong><?php echo $leaseTermText; ?></strong> <?php echo clean($rows['leaseTerm']); ?><br />
							<small><?php echo dateFormat($rows['leaseStart']); ?> &mdash; <?php echo dateFormat($rows['leaseEnd']); ?></small>
						</li>
						<li class="list-group-item group-item-sm">
							<strong><?php echo $managerLiText; ?></strong>
							<a href="profile.php?profile=<?php echo $profileurl; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewMngProfileText; ?>">
								<?php echo clean($rows['adminName']); ?>
							</a>
						</li>
					</ul>
					<a href="page.php?page=newPayment" class="btn btn-xs btn-block btn-default btn-icon"><i class="fa fa-credit-card"></i> <?php echo $newPaymentBtn; ?></a>
				</div>
				<div class="col-md-9">
					<h3><?php echo $leasePayRecH3; ?></h3>
					<?php if(mysqli_num_rows($res) < 1) { ?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $noLeasePayRecMsg; ?>
						</div>
					<?php } else { ?>
						<table id="payments" class="display" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo $paymentForHead; ?></th>
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
											<td>
												<a href="page.php?page=receipt&payId=<?php echo $pay['payId']; ?>" target="_blank" data-toggle="tooltip" data-placement="top" title="<?php echo $viewReceiptText; ?>">
													<?php echo clean($pay['paymentFor']); ?>
												</a>
											</td>
											<td class="text-center"><?php echo clean($pay['paymentType']); ?></td>
											<td class="text-center"><?php echo $paymentAmount; ?></td>
											<td class="text-center"><?php echo $penaltyFee; ?></td>
											<td class="text-center"><?php echo dateFormat($pay['paymentDate']); ?></td>
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
						
						<span class="reportTotal"><strong><?php echo $totRecLeaseText; ?></strong> <?php echo $totalReceived; ?></span>
						
						<hr />
					<?php } ?>
					
					<h3><?php echo $leaseRefIssH3; ?></h3>
					<?php if(mysqli_num_rows($results) < 1) { ?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $noLeaseRefIssMsg; ?>
						</div>
					<?php } else { ?>
						<table id="refunds" class="display" cellspacing="0">
							<thead>
								<tr>
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
											<td><?php echo clean($col['paymentFor']); ?></td>
											<td class="text-center"><?php echo dateFormat($col['refundDate']); ?></td>
											<td class="text-center"><?php echo clean($col['refundFor']); ?></td>
											<td class="text-right"><?php echo formatCurrency($col['refundAmount'],$currCode); ?></td>
										</tr>
								<?php } ?>
							</tbody>
						</table>
						
						<span class="reportTotal"><strong><?php echo $totRefLeaseText; ?></strong> <?php echo $totalRef; ?></span>
						<div class="clearfix mb-20"></div>
					<?php } ?>
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