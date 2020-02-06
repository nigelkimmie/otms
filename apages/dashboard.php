<?php
	$currentYear	= date('Y');			// Get the Current Year
	$currentMonth	= date('F');			// Get the Current Month
	$currentDay		= date('d');			// Get the Current Day
	$avatarDir		= $set['avatarFolder'];	// Get Avatar Folder from Site Settings

	// Get Admin's Role & Avatar
	$sql = "SELECT
				adminRole,
				adminAvatar
			FROM
				admins
			WHERE
				adminId = ".$rs_adminId;
	$res = mysqli_query($mysqli, $sql) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);

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
    $alertres = mysqli_query($mysqli, $alert) or die('-2' . mysqli_error());

	// Get Leased Tenants Count
	$at = "SELECT 'X' FROM users WHERE isActive = 1 AND isLeased != 0 AND isResident = 0";
	$atres = mysqli_query($mysqli, $at) or die('-3' . mysqli_error());
	$atcount = mysqli_num_rows($atres);
	if ($atcount == 1) { $atcountText = $leasedTenText; } else { $atcountText = $leasedTensText; }

	// Get Available Properties Count
	$ap = "SELECT 'X' FROM properties WHERE isLeased = 0";
	$apres = mysqli_query($mysqli, $ap) or die('-4' . mysqli_error());
	$apcount = mysqli_num_rows($apres);
	if ($apcount == 1) { $apcountText = $availaPropText; } else { $apcountText = $availPropsText; }

	// Get Open Service Requests Count
	$sr = "SELECT 'X' FROM servicerequests WHERE isClosed = 0";
	$srres = mysqli_query($mysqli, $sr) or die('-5' . mysqli_error());
	$srcount = mysqli_num_rows($srres);
	if ($srcount == 1) { $srcountText = $openServReqText; } else { $srcountText = $openServReqsText; }

	if ($set['enablePayments'] == '1') {
		// Get latest payment data
		$payment = "SELECT
						payments.*,
						users.propertyId,
						users.userFirstName,
						users.userLastName,
						properties.propertyName
					FROM
						payments
						LEFT JOIN users ON payments.userId = users.userId
						LEFT JOIN properties ON users.propertyId = properties.propertyId
					WHERE
						payments.isRent = 1 AND
						payments.rentMonth = '".$currentMonth."' AND
						payments.rentYear = '".$currentYear."'";
		$paymentres = mysqli_query($mysqli, $payment) or die('-6' . mysqli_error());

		if(mysqli_num_rows($paymentres) > 0) {
			// Get the Totals
			$totals = "SELECT
						SUM(amountPaid) AS totalPaid,
						SUM(penaltyFee) AS totalFee
					FROM
						payments
					WHERE
						payments.isRent = 1 AND
						payments.rentMonth = '".$currentMonth."' AND
						payments.rentYear = '".$currentYear."'";
			$total = mysqli_query($mysqli, $totals) or die('-7' . mysqli_error());
			$tot = mysqli_fetch_assoc($total);

			// Format the Amounts
			$totreceived = $tot['totalPaid'] + $tot['totalFee'];
			$totalReceived = formatCurrency($totreceived,$currCode);
		} else {
			$totalReceived = '';
		}

		// Get Late Rent data
		// Note: This was a MAJOR Pain in the Arse -- No changes **Unless** you really know what you are doing.
		if($hasPaid = $mysqli->prepare(
			"SELECT
				users.propertyId
			FROM
				users
				LEFT JOIN payments ON users.userId = payments.userId
			WHERE
				payments.rentMonth = ? AND
				payments.rentYear = ?"
		))
		$hasPaid->bind_param('ss', $currentMonth, $currentYear);
		$hasPaid->execute();
		$hasPaid->bind_result($propertyId);
		$hasPaid->store_result();
		$totalrows = $hasPaid->num_rows;

		$propids = array();
		while($hasPaid->fetch()) {
			$propids[] = array(
				'propertyId' => $propertyId
			);
		}
		$hasPaid->close();

		// Get the Property ID list from the array
		foreach($propids as $v) $theIds[] = $v['propertyId'];

		if ($totalrows > 0) {
			$list = "'".implode("','",$theIds)."'";
		} else {
			$list = '0';
		}

		// Get the Property/Tenant info to display based on the array
		$today = date("Y-m-d");
		$latepay = "SELECT
						properties.propertyId,
						properties.propertyName,
						properties.propertyAddress,
						properties.propertyRate,
						properties.latePenalty,
						leases.leaseStart,
						users.userId,
						users.userFirstName,
						users.userLastName
					FROM
						properties
						LEFT JOIN leases ON properties.propertyId = leases.propertyId
						LEFT JOIN users ON properties.propertyId = users.propertyId
					WHERE
						leases.closed = 0 AND
						users.isResident = 0 AND
						properties.isLeased = 1 AND
						'".$today."' >= leases.leaseStart AND
						properties.propertyId NOT IN (".$list.")";
		$latepayres = mysqli_query($mysqli, $latepay) or die('-8' . mysqli_error());

		// Get latest payment data
		$payment = "SELECT DISTINCT
						payments.*,
						users.propertyId,
						users.userFirstName,
						users.userLastName,
						properties.propertyName
					FROM
						payments
						LEFT JOIN users ON payments.userId = users.userId
						LEFT JOIN properties ON users.propertyId = properties.propertyId
					WHERE
						payments.isRent = 1 AND
						payments.rentMonth = '".$currentMonth."' AND
						payments.rentYear = '".$currentYear."'
					GROUP BY users.propertyId";
		$paymentres = mysqli_query($mysqli, $payment) or die('-9' . mysqli_error());

		if(mysqli_num_rows($paymentres) > 0) {
			// Get the Totals
			$totals = "SELECT
						SUM(amountPaid) AS totalPaid,
						SUM(penaltyFee) AS totalFee
					FROM
						payments
					WHERE
						payments.isRent = 1 AND
						payments.rentMonth = '".$currentMonth."' AND
						payments.rentYear = '".$currentYear."'";
			$total = mysqli_query($mysqli, $totals) or die('-10' . mysqli_error());
			$tot = mysqli_fetch_assoc($total);

			// Format the Amounts
			$totreceived = $tot['totalPaid'] + $tot['totalFee'];
			$totalReceived = formatCurrency($totreceived,$currCode);
		} else {
			$totalReceived = '';
		}
	}

	// Get Available Properties
	$avail = "SELECT * FROM properties WHERE isLeased = 0";
	$availres = mysqli_query($mysqli, $avail) or die('-11' . mysqli_error());

	$dashPage = 'true';
	$pageTitle = $dashboardPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'dashboard';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0" />

		<?php if ($msgBox) { echo $msgBox; } ?>

		<div class="row">
			<div class="col-md-6">
				<p class="lead mb-0">
					<img alt="Admin Avatar" src="../<?php echo $avatarDir.$row['adminAvatar']; ?>" class="avatarImage pull-left mt-5" />
					<?php echo $welcomeAdmText.' '.$row['adminRole'].' '.$rs_adminName; ?>
				</p>
				<p class="mt-0"><?php echo $theText.' '.$set['siteName'].' '.$welcomeAdmQuip; ?></p>
			</div>
			<div class="col-md-6">
				<?php
					if(mysqli_num_rows($alertres) > 0) {
						while ($rows = mysqli_fetch_assoc($alertres)) {
							// If Start Date is set, use the Start date, else the Date the Alert was created
							if (!is_null($rows['alertStart'])) { $noticeDate = dateFormat($rows['alertStart']); } else { $noticeDate = dateFormat($rows['alertDate']); }
				?>
							<div class="box">
								<span class="box-notify"><?php echo $noticeDate; ?></span>
								<h4 class="mt-0"><i class="fa fa-bullhorn"></i> &nbsp; <?php echo clean($rows['alertTitle']); ?></h4>
								<p><?php echo nl2br(htmlspecialchars_decode($rows['alertText'])); ?></p>
							</div>
					<?php
						}
					}
				?>
			</div>
		</div>

		<hr />

		<div class="row mb-10">
			<div class="col-sm-4">
				<div class="dashblocks info">
					<div class="dashblocksBody">
						<i class="boxIcon fa fa-group"></i>
						<span><?php echo $atcount; ?></span>
					</div>
					<?php if ((checkArray('MNGTEN', $auths)) || $rs_isAdmin != '') { ?>
						<div class="dashblocksFooter"><a href="index.php?action=leasedTenants"><?php echo $atcountText; ?></a></div>
					<?php } else { ?>
						<div class="dashblocksFooter"><a href=""><?php echo $atcountText; ?></a></div>
					<?php } ?>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="dashblocks success">
					<div class="dashblocksBody">
						<i class="boxIcon fa fa-building"></i>
						<span><?php echo $apcount; ?></span>
					</div>
					<?php if ((checkArray('MNGPROP', $auths)) || $rs_isAdmin != '') { ?>
						<div class="dashblocksFooter"><a href="index.php?action=leasedProperties"><?php echo $apcountText; ?></a></div>
					<?php } else { ?>
						<div class="dashblocksFooter"><a href=""><?php echo $apcountText; ?></a></div>
					<?php } ?>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="dashblocks warning">
					<div class="dashblocksBody">
						<i class="boxIcon fa fa-wrench"></i>
						<span><?php echo $srcount; ?></span>
					</div>
					<?php if ((checkArray('SRVREQ', $auths)) || $rs_isAdmin != '') { ?>
						<div class="dashblocksFooter"><a href="index.php?action=activeRequests"><?php echo $srcountText; ?></a></div>
					<?php } else { ?>
						<div class="dashblocksFooter"><a href=""><?php echo $srcountText; ?></a></div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<?php
		if ($set['enablePayments'] == '1') {
			if ($currentDay > '5') {
	?>
			<div class="container page_block mt-20">
				<h3><?php echo $lateRentH3.' '.$currentMonth; ?></h3>
				<?php if(mysqli_num_rows($latepayres) > 0) { ?>
					<table id="lateRent" class="display" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo $propertyHead; ?></th>
									<th><?php echo $addressHead; ?></th>
									<th><?php echo $tenantHead; ?></th>
									<th class="text-center"><?php echo $rentAmtHead; ?></th>
									<th class="text-center"><?php echo $lateFeeHead; ?></th>
									<th class="text-center"><?php echo $totalDueHead; ?></th>
								</tr>
							</thead>
							<tbody>
					<?php
								while ($late = mysqli_fetch_assoc($latepayres)) {
									// Get the Total Due for each Property
									$total = $late['propertyRate'] + $late['latePenalty'];
									$totalDue = formatCurrency($total,$currCode);
					?>
									<tr>
										<td>
											<a href="index.php?action=viewProperty&propertyId=<?php echo $late['propertyId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewPropertyText; ?>">
												<?php echo clean($late['propertyName']); ?>
											</a>
										</td>
										<td><?php echo clean($late['propertyAddress']); ?></td>
										<td>
											<a href="index.php?action=viewTenant&userId=<?php echo $late['userId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewTenantText; ?>">
												<?php echo clean($late['userFirstName']).' '.clean($late['userLastName']); ?>
											</a>
										</td>
										<td class="text-center"><?php echo formatCurrency($late['propertyRate'],$currCode); ?></td>
										<td class="text-center"><?php echo formatCurrency($late['latePenalty'],$currCode); ?></td>
										<td class="text-danger text-center">
											<strong data-toggle="tooltip" data-placement="left" title="Rent Amount + Late Fee"><?php echo $totalDue; ?></strong>
										</td>
									</tr>
					<?php
								}
					?>
							</tbody>
						</table>
				<?php } else { ?>
					<div class="alertMsg default mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-info-circle"></i>
						</div>
						<?php echo $noLateRentMsg; ?>
					</div>
				<?php } ?>
			</div>
	<?php
			}
		}
	?>

	<?php
		if ($set['enablePayments'] == '1') {
			if (checkArray('ACCTRPT', $auths)) { ?>
				<div class="container page_block mt-20">
				<h3><?php echo $rentRcvdForH3.' '.$currentMonth; ?></h3>
		<?php
			if(mysqli_num_rows($paymentres) > 0) {
		?>
				<table id="rentReceived" class="display" cellspacing="0">
					<thead>
						<tr>
							<th><?php echo $propertyHead; ?></th>
							<th><?php echo $tenantHead; ?></th>
							<th class="text-center"><?php echo $paymentDateHead; ?></th>
							<th class="text-center"><?php echo $renatlMonthHead; ?></th>
							<th class="text-center"><?php echo $amountHead; ?></th>
							<th class="text-center"><?php echo $lateFeePaidHead; ?></th>
							<th class="text-center"><?php echo $totalPaidHead; ?></th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php
							while ($pay = mysqli_fetch_assoc($paymentres)) {
								// Format the Amounts
								$paymentAmount = formatCurrency($pay['amountPaid'],$currCode);
								if ($pay['penaltyFee'] != '') { $penaltyFee = formatCurrency($pay['penaltyFee'],$currCode); } else { $penaltyFee = ''; }
								$total = $pay['amountPaid'] + $pay['penaltyFee'];
								$totalPaid = formatCurrency($total,$currCode);

								// Check for Refunds
								if ($pay['hasRefund'] == '1') { $hasRefund = '<sup><i class="fa fa-asterisk tool-tip text-info" title="'.$amtRefRefText.'"></i></sup>'; } else { $hasRefund = ''; }
						?>
								<tr>
									<td>
										<a href="index.php?action=viewProperty&propertyId=<?php echo $pay['propertyId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewPropertyText; ?>">
											<?php echo clean($pay['propertyName']); ?>
										</a>
									</td>
									<td>
										<a href="index.php?action=viewTenant&userId=<?php echo $pay['userId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewTenantText; ?>">
											<?php echo clean($pay['userFirstName']).' '.clean($pay['userLastName']); ?>
										</a>
									</td>
									<td class="text-center"><?php echo dateFormat($pay['paymentDate']); ?></td>
									<td class="text-center"><?php echo $pay['rentMonth']; ?></td>
									<td class="text-center"><?php echo $paymentAmount.' '.$hasRefund; ?></td>
									<td class="text-center"><?php echo $penaltyFee; ?></td>
									<td class="text-center"><?php echo $totalPaid." ".$hasRefund; ?></td>
									<td>
										<a href="index.php?action=receipt&payId=<?php echo $pay['payId']; ?>" target="_blank" data-toggle="tooltip" data-placement="left" title="<?php echo $receiptText; ?>">
											<i class="fa fa-print text-info"></i>
										</a>
									</td>
								</tr>
						<?php } ?>
					</tbody>
				</table>

				<span class="reportTotal"><strong><?php echo $totalRecvdForText.' '.$currentMonth; ?>:</strong> <?php echo $totalReceived; ?></span>
				<div class="clearfix mb-20"></div>
		<?php
			} else {
		?>
				<div class="alertMsg default mb-20">
					<div class="msgIcon pull-left">
						<i class="fa fa-info-circle"></i>
					</div>
					<?php echo $noPayMadeText; ?>
				</div>
		<?php
				}
				echo '</div>';
			}
		}
		?>
	</div>

	<div class="container page_block mt-20">
		<h3><?php echo $availPropH3; ?></h3>
		<?php if(mysqli_num_rows($availres) > 0) { ?>
			<table id="availProp" class="display" cellspacing="0">
				<thead>
					<tr>
						<th><?php echo $propertyHead; ?></th>
						<th><?php echo $addressHead; ?></th>
						<th class="text-center"><?php echo $rateText; ?></th>
						<th class="text-center"><?php echo $depositText; ?></th>
						<th class="text-center"><?php echo $petsText; ?></th>
						<th class="text-center"><?php echo $bedsText; ?></th>
						<th class="text-center"><?php echo $bathsText; ?></th>
						<th class="text-center"><?php echo $sixeText; ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
						while ($ap = mysqli_fetch_assoc($availres)) {
							if ($ap['petsAllowed'] == '0') { $petsAllowed = $noBtn; } else { $petsAllowed = $yesBtn; }
					?>
							<tr>
								<td>
									<a href="index.php?action=viewProperty&propertyId=<?php echo $ap['propertyId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewPropertyText; ?>">
										<?php echo clean($ap['propertyName']); ?>
									</a>
								</td>
								<td><?php echo clean($ap['propertyAddress']); ?></td>
								<td class="text-center"><?php echo formatCurrency($ap['propertyRate'],$currCode); ?></td>
								<td class="text-center"><?php echo formatCurrency($ap['propertyDeposit'],$currCode); ?></td>
								<td class="text-center"><?php echo $petsAllowed; ?></td>
								<td class="text-center"><?php echo clean($ap['bedrooms']); ?></td>
								<td class="text-center"><?php echo clean($ap['bathrooms']); ?></td>
								<td class="text-center"><?php echo clean($ap['propertySize']); ?></td>
							</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<div class="alertMsg default mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-info-circle"></i>
				</div>
				<?php echo $noPropFoundText; ?>
			</div>
		<?php } ?>
	</div>