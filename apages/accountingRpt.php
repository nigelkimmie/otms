<?php
	$rptError = $reportType = $runRpt = $fdate = $tdate = '';
	$where = array();
	
	switch ($_POST['rptType']) {
		case "paymentsRep":
			$payType = $mysqli->real_escape_string($_POST['payType']);
			
			if ($payType == 'all') {
				$where[] = 'payments.isRent IN ("1","0")';
				$reportType = $rptTitle1;
			} else if ($payType == 'rental') {
				$where[] = 'payments.isRent IN ("1")';
				$reportType = $rptTitle2;
			} else if ($payType == 'other') {
				$where[] = 'payments.isRent IN ("0")';
				$reportType = $rptTitle3;
			} else {
				$rptError = 'true';
				$reportType = $reportErrorH3;
			}
			
			if (!empty($_POST['userId']) && is_array($_POST['userId']) && !in_array('all',$_POST['userId'])) {
				$uids = array();
				foreach ($_POST['userId'] as $userId) {
				  $uids[] = $mysqli->real_escape_string($userId);
				}
				$userIds = '"'.implode('", "', $uids).'"';
				$where[] = 'payments.userId IN ('.$userIds.')';
			}
			
			if (!empty($_POST['fromDate'])) {
				$fromDate = $mysqli->real_escape_string($_POST['fromDate']);
				$where[] = 'payments.paymentDate >= "'.$fromDate.'"';
				
				$fdate = dateFormat($fromDate);
			}
			if (!empty($_POST['toDate'])) {
				$toDate = $mysqli->real_escape_string($_POST['toDate']);
				$where[] = 'payments.paymentDate <= "'.$toDate.'"';
				
				$tdate = dateFormat($toDate);
			}
			
			
			if (!empty($where)) {
				$whereSql = "WHERE\n" . implode("\nAND ",$where);
			}
			
			// Get Payment Data
			$qry = 'SELECT
						payments.*,
						users.userFirstName,
						users.userLastName,
						properties.propertyName,
						admins.adminName
					FROM
						payments
						LEFT JOIN users on payments.userId = users.userId
						LEFT JOIN properties ON payments.propertyId = properties.propertyId
						LEFT JOIN admins ON payments.adminId = admins.adminId '.$whereSql;
			$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
			
			$runRpt = 'payments';
		break;
		case "refundsRep":
			$reportType = $rptTitle4;

			if (!empty($_POST['userId']) && is_array($_POST['userId']) && !in_array('all',$_POST['userId'])) {
				$uids = array();
				foreach ($_POST['userId'] as $userId) {
				  $uids[] = $mysqli->real_escape_string($userId);
				}
				$userIds = '"'.implode('", "', $uids).'"';
				$where[] = 'refunds.userId IN ('.$userIds.')';
			}
			
			if (!empty($_POST['fromDate'])) {
				$fromDate = $mysqli->real_escape_string($_POST['fromDate']);
				$where[] = 'refunds.refundDate >= "'.$fromDate.'"';
				
				$fdate = dateFormat($fromDate);
			}
			if (!empty($_POST['toDate'])) {
				$toDate = $mysqli->real_escape_string($_POST['toDate']);
				$where[] = 'refunds.refundDate <= "'.$toDate.'"';
				
				$tdate = dateFormat($toDate);
			}
			
			
			if (!empty($where)) {
				$whereSql = "WHERE\n" . implode("\nAND ",$where);
			}
			
			// Get Refund Data
			$qry = 'SELECT
						refunds.*,
						users.userFirstName,
						users.userLastName,
						properties.propertyName,
						admins.adminName
					FROM
						refunds
						LEFT JOIN users on refunds.userId = users.userId
						LEFT JOIN properties ON refunds.propertyId = properties.propertyId
						LEFT JOIN admins ON refunds.adminId = admins.adminId '.$whereSql;
			$res = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
			
			$runRpt = 'refunds';
		break;
		default:
			$rptError = 'true';
			$reportType = $reportErrorH3;
		break;
	}
	
	if ($rptError == '') {
		// Add Recent Activity
		$activityType = '23';
		$rs_uid = '0';
		$activityTitle = $rs_adminName.' '.$adminRptAct1.' '.$reportType.' '.$adminRptAct2;
		updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);
	} else {
		// Add Recent Activity
		$activityType = '23';
		$rs_uid = '0';
		$activityTitle = $adminRptAct3.' '.$reportType.' '.$adminRptAct2;
		updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);
	}
	
	$managePage = 'true';
	$pageTitle = $accReportsPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'accountingRpt';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($rptError == '') {
				if ((checkArray('ACCTRPT', $auths)) || $rs_isAdmin != '') {
					if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $pageTitle; ?></h3>
				<p class="lead mb-0">
					<?php echo $reportType; ?><br />
					<small><?php echo $fromText; ?> <?php echo $fdate; ?> <?php echo $toText; ?> <?php echo $tdate; ?></small>
				</p>

				<?php if ($runRpt == 'payments') { ?>
					<?php if(mysqli_num_rows($res) < 1) { ?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $nothingToRptMsg; ?>
						</div>
					<?php } else { ?>
						<table id="rpt1" class="display" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo $propertyHead; ?></th>
									<th><?php echo $tenantHead; ?></th>
									<th class="text-center"><?php echo $idHead; ?></th>
									<th class="text-center"><?php echo $paymentDateHead; ?></th>
									<th class="text-center"><?php echo $paymentForHead; ?></th>
									<th class="text-center"><?php echo $renatlMonthHead; ?></th>
									<th class="text-center"><?php echo $amountHead; ?></th>
									<th class="text-center"><?php echo $lateFeePaidHead; ?></th>
									<th class="text-center"><?php echo $totalPaidHead; ?></th>
									<th class="text-center"><?php echo $receivedByHead; ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
									while ($row = mysqli_fetch_assoc($res)) {
										if ($row['penaltyFee'] != '') { $penaltyFee = formatCurrency($row['penaltyFee'],$currCode); } else { $penaltyFee = ''; }
										$total = $row['amountPaid'] + $row['penaltyFee'];
										$totalPaid = formatCurrency($total,$currCode);
								?>
										<tr>
											<td><?php echo clean($row['propertyName']); ?></td>
											<td><?php echo clean($row['userFirstName']).' '.clean($row['userLastName']); ?></td>
											<td class="text-center"><?php echo $row['payId']; ?></td>
											<td class="text-center"><?php echo dateFormat($row['paymentDate']); ?></td>
											<td class="text-center"><?php echo clean($row['paymentFor']); ?></td>
											<td class="text-center"><?php echo clean($row['rentMonth']); ?></td>
											<td class="text-center"><?php echo formatCurrency($row['amountPaid'],$currCode); ?></td>
											<td class="text-center"><?php echo $penaltyFee; ?></td>
											<td class="text-center"><?php echo $totalPaid; ?></td>
											<td class="text-center"><?php echo clean($row['adminName']); ?></td>
										</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
				<?php } else if ($runRpt == 'refunds') { ?>
					<?php if(mysqli_num_rows($res) < 1) { ?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $nothingToRptMsg; ?>
						</div>
					<?php } else { ?>
						<table id="rpt2" class="display" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo $propertyHead; ?></th>
									<th><?php echo $tenantHead; ?></th>
									<th class="text-center"><?php echo $origIdHead; ?></th>
									<th class="text-center"><?php echo $refundDateHead; ?></th>
									<th class="text-center"><?php echo $refunfForHead; ?></th>
									<th class="text-center"><?php echo $refundAmtHead; ?></th>
									<th class="text-center"><?php echo $issuedByHead; ?></th>
								</tr>
							</thead>

							<tbody>
								<?php while ($row = mysqli_fetch_assoc($res)) { ?>
									<tr>
										<td><?php echo clean($row['propertyName']); ?></td>
										<td><?php echo clean($row['userFirstName']).' '.clean($row['userLastName']); ?></td>
										<td class="text-center"><?php echo $row['payId']; ?></td>
										<td class="text-center"><?php echo dateFormat($row['refundDate']); ?></td>
										<td class="text-center"><?php echo clean($row['refundFor']); ?></td>
										<td class="text-center"><?php echo formatCurrency($row['refundAmount'],$currCode); ?></td>
										<td class="text-center"><?php echo clean($row['adminName']); ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
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
		<?php
				}
			} else {
		?>
			<hr class="mt-0 mb-0" />
			<h3><?php echo $reportErrorH3; ?></h3>
			<div class="alertMsg info mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-info-circle"></i>
				</div>
				<?php echo $reportErrorQuip; ?>
			</div>
		<?php } ?>
	</div>