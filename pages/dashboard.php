<?php
	$currentYear	= date('Y');			// Get the Current Year
	$currentMonth	= date('F');			// Get the Current Month
	$currentDay		= date('d');			// Get the Current Day
	$currentDate 	= date("Y-m-d");		// Get the Full Current Date
	$propPicsPath	= $set['propPicsPath'];	// Get the Property Pictures Directory

	$hasLateRent = '';
	
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

	// Get User's Data
	$qry = "SELECT * FROM users WHERE userId = ".$rs_userId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);

	// Get Lease/Property Data
	if ($row['isLeased'] == '1') {
		$hasLease = 'true';
		$sql = "SELECT
					properties.*,
					leases.*,
					assigned.adminId AS assignedTo,
					admins.adminName
				FROM
					properties
					LEFT JOIN leases ON properties.propertyId = leases.propertyId
					LEFT JOIN assigned ON properties.propertyId = assigned.propertyId
					LEFT JOIN admins ON assigned.adminId = admins.adminId
				WHERE
					leases.userId = ".$primaryTenId." AND
					leases.leaseId = ".$primaryLeaseId;
		$result = mysqli_query($mysqli, $sql) or die('-2' . mysqli_error());
		$rows = mysqli_fetch_assoc($result);

		if ($rows['petsAllowed'] == '1') { $pets = $yesBtn; } else { $pets = $noBtn; }
		$profileurl = preg_replace('/ /', '-', clean($rows['adminName']));

		if ($set['enablePayments'] == '1') {
			$ifLateTotal = $rows['propertyRate'] + $rows['latePenalty'];
			$lateTotal = formatCurrency($ifLateTotal,$currCode);

			// Check if the Tenant is late on current month's rent
			if ($currentDate > $rows['leaseStart'] && $currentDate < $rows['leaseEnd']) {
				$latecheck = "SELECT
								payments.isRent,
								payments.rentMonth,
								payments.rentYear,
								users.propertyId
							FROM
								payments
								LEFT JOIN users ON payments.userId = users.userId
							WHERE
								users.propertyId = ".$rows['propertyId']." AND
								payments.leaseId = ".$primaryLeaseId." AND
								payments.isRent = 1 AND
								payments.rentMonth = '".$currentMonth."' AND
								payments.rentYear = '".$currentYear."'";
				$lateres = mysqli_query($mysqli, $latecheck) or die('-3' . mysqli_error());
				if(mysqli_num_rows($lateres) < 1) {
					$hasLateRent = 'true';
				}
			} else {
				$hasLateRent = '';
			}
			
			// Get latest payment data
			$payment = "SELECT
							payments.*,
							UNIX_TIMESTAMP(payments.paymentDate) AS orderDate,
							users.propertyId
						FROM
							payments
							LEFT JOIN users ON payments.userId = users.userId
						WHERE
							users.userId = ".$primaryTenId." AND
							payments.leaseId = ".$primaryLeaseId."
						ORDER BY orderDate DESC
						LIMIT 5";
			$paymentres = mysqli_query($mysqli, $payment) or die('-4' . mysqli_error());
		}
	} else {
		$hasLease = 'false';
	}

	// Get Site Alert Data
    $alert = "SELECT
					*,
					UNIX_TIMESTAMP(alertDate) AS orderDate
				FROM
					sitealerts
				WHERE
					alertStart <= DATE_SUB(CURDATE(),INTERVAL 0 DAY) AND
					alertExpires >= DATE_SUB(CURDATE(),INTERVAL 0 DAY) OR
					isActive = 1
				ORDER BY
					orderDate DESC";
    $alertres = mysqli_query($mysqli, $alert) or die('-5' . mysqli_error());

	$dashPage = 'true';
	$pageTitle = $dashPageTitle;
	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		<h3><?php echo $pageTitle; ?></h3>

		<?php if ($msgBox) { echo $msgBox; } ?>

		<div class="row">
			<div class="col-md-6">
				<p class="lead mb-0">
					<img alt="User Avatar" src="<?php echo $avatarDir.$row['userAvatar']; ?>" class="avatarImage pull-left mt-5" />
					<?php echo $welcomeText; ?>, <?php echo $rs_userFull; ?>
				</p>
				<p class="mt-0">The <?php echo $set['siteName'].' '.$welcomeQuipText; ?></p>
				
				<?php
					if ($set['enablePayments'] == '1') {
						if ($hasLease == 'true') {
							if ($hasLateRent == 'true') {
								if ($currentDay > '5') {
				?>
									<div class="alertMsg warning mt-20 mb-0">
										<div class="msgIcon pull-left">
											<i class="fa fa-warning"></i>
										</div>
										<?php echo $dashRentLateText; ?> <strong><?php echo $lateTotal; ?></strong> <?php echo $dashRentLateText1; ?>
									</div>
				<?php
								}
							}
						}
					}
				?>
			</div>
			<div class="col-md-6">
				<?php
					if(mysqli_num_rows($alertres) > 0) {
						while ($alrt = mysqli_fetch_assoc($alertres)) {
							// If Start Date is set, use the Start date, else the Date the Alert was created
							if (!is_null($alrt['alertStart'])) { $noticeDate = dateFormat($alrt['alertStart']); } else { $noticeDate = dateFormat($alrt['alertDate']); }
				?>
							<div class="box">
								<span class="box-notify"><?php echo $noticeDate; ?></span>
								<h4><i class="fa fa-bullhorn"></i> &nbsp; <?php echo clean($alrt['alertTitle']); ?></h4>
								<p><?php echo nl2br(htmlspecialchars_decode($alrt['alertText'])); ?></p>
							</div>
					<?php
						}
					}
				?>
			</div>
		</div>

		<hr />

		<h3><?php echo $dashCurrentLeaseText; ?></h3>
		<?php if ($hasLease == 'true') { ?>
			<div class="table-responsive">
				<table class="table table-bordered table-sm mb-0">
					<thead>
						<tr>
							<th><?php echo $propertyHead; ?></th>
							<th class="text-center"><?php echo $monthlyRentHead; ?></th>
							<th class="text-center"><?php echo $feeLateHead; ?></th>
							<th class="text-center"><?php echo $petsAllowedHead; ?></th>
							<th class="text-center"><?php echo $leaseTermHead; ?></th>
							<th class="text-center"><?php echo $managerHead; ?></th>
							<th class="text-center"><?php echo $leaseEndHead; ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<a href="page.php?page=myProperty" data-toggle="tooltip" data-placement="top" title="<?php echo $viewPropertyText; ?>">
									<?php echo clean($rows['propertyName']); ?>
								</a>
							</td>
							<td class="text-center"><?php echo formatCurrency($rows['propertyRate'],$currCode); ?></td>
							<td class="text-center"><?php echo formatCurrency($rows['latePenalty'],$currCode); ?></td>
							<td class="text-center"><?php echo $pets; ?></td>
							<td class="text-center"><?php echo clean($rows['leaseTerm']); ?></td>
							<td class="text-center">
								<a href="profile.php?profile=<?php echo $profileurl; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewMngProfileText; ?>">
									<?php echo clean($rows['adminName']); ?>
								</a>
							</td>
							<td class="text-center"><?php echo dateFormat($rows['leaseTerm']); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php if ($set['enablePayments'] == '0') { ?>
				<div class="clearfix mb-20"></div>
			<?php } ?>
		<?php } else { ?>
			<div class="alertMsg default mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-info-circle"></i>
				</div>
				<?php echo $usrNoLeasedPropMsg; ?>
			</div>
		<?php } ?>

		<?php
			if ($set['enablePayments'] == '1') {
				if ($hasLease == 'true') {
					if(mysqli_num_rows($paymentres) > 0) {
		?>
					<hr />
					<h3><?php echo $mostRecentPymntsText; ?></h3>
					<div class="table-responsive">
						<table class="table table-bordered table-sm">
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
									while ($pay = mysqli_fetch_assoc($paymentres)) {
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
													<a href="page.php?page=receipt&payId=<?php echo $pay['payId']; ?>" target="_blank" data-toggle="tooltip" data-placement="top" title="<?php echo $viewReceiptText; ?>">
														<i class="fa fa-print"></i>
													</a>
												</td>
											<?php } ?>
										</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
		<?php
					} else {
						echo '<div class="clearfix mb-20"></div>';
					}
				}
			}
		?>
	</div>