<?php
	if ($set['enablePayments'] == '1') {
		$payId = $mysqli->real_escape_string($_GET['payId']);
		
		// Check if Payment belongs to logged in user
		$usrCk = "SELECT userId FROM payments WHERE payId = ".$payId;
		$userChk = mysqli_query($mysqli, $usrCk) or die('-1' . mysqli_error());
		$uchk = mysqli_fetch_assoc($userChk);
		$userCheck = $uchk['userId'];

		$qry = "SELECT
					payments.*,
					refunds.*,
					CONCAT(users.userFirstName,' ',users.userLastName) AS user,
					users.primaryPhone,
					users.userAddress,
					users.userEmail,
					properties.propertyName
				FROM
					payments
					LEFT JOIN refunds ON payments.payId = refunds.payId
					LEFT JOIN users ON payments.userId = users.userId
					LEFT JOIN properties ON payments.propertyId = properties.propertyId
				WHERE payments.payId = ".$payId;
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
		$row = mysqli_fetch_assoc($res);

		// Decrypt data
		if ($row['primaryPhone'] != '') { $primaryPhone = decryptIt($row['primaryPhone']); } else { $primaryPhone = '';  }
		if ($row['userAddress'] != '') { $userAddress = decryptIt($row['userAddress']); } else { $userAddress = '';  }

		if ($row['penaltyFee'] != '') { $penaltyFee = formatCurrency($row['penaltyFee'],$currCode); } else { $penaltyFee = ''; }
		$total = $row['amountPaid'] + $row['penaltyFee'];
		$newTotal = $total - $row['refundAmount'];

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
	
		// Add Recent Activity
		$activityType = '2';
		$rs_aid = '0';
		$activityTitle = $rs_userFull.' viewed the Payment Receipt for "'.clean($row['propertyName']).'"';
		updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title><?php echo $set['siteName']; ?> &middot; <?php echo $receiptPageTitle; ?></title>

	<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="css/print.css" />
	<link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
</head>

<body>
	<div class="container">
		<?php
			if ($set['enablePayments'] == '1') {
				if ($rs_leaseId != '0' && $userCheck == $rs_userId) {
		?>
				<div class="row">
					<div class="col-xs-4">
						<img src="images/logo.png" class="mt-20">
					</div>
					<div class="col-xs-8 text-right">
						<h2><?php echo $receiptPageH3; ?></h3>
					</div>
				</div>

				<hr />

				<div class="row">
					<div class="col-xs-4 col-xs-offset-8">
						<p class="mb-20 text-right">
							<?php echo $payIdText. ' '.$payId; ?><br />
							<?php echo $dateRecvdText.' '.shortDateFormat($row['paymentDate']); ?>
							<?php if ($row['isRent'] == '1') { ?>
								<br /><?php echo $rentMonthText.': '.clean($row['rentMonth']).' '.clean($row['rentYear']); ?>
							<?php } ?>
						</p>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4><?php echo $paidToText.' '.$set['siteName']; ?></h3>
							</div>
							<div class="panel-body">
								<p>
									<?php echo nl2br(clean($set['businessAddress'])); ?><br />
									<?php echo clean($set['businessPhone']); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4><?php echo $recFromText.' '.clean($row['user']); ?></h3>
							</div>
							<div class="panel-body">
								<p>
									<?php echo nl2br($userAddress); ?><br />
									<?php echo $primaryPhone; ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<table class="table table-bordered">
					<thead>
						<tr>
							<th><?php echo $propertyHead; ?></th>
							<th><?php echo $descriptionHead; ?></th>
							<th><?php echo $paidByHead; ?></th>
							<th><?php echo $amountHead; ?></th>
							<th><?php echo $lateFeeHead; ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo clean($row['propertyName']); ?></td>
							<td><?php echo clean($row['paymentFor']); ?></td>
							<td><?php echo clean($row['paymentType']); ?></td>
							<td><?php echo formatCurrency($row['amountPaid'],$currCode); ?></td>
							<td><?php echo $penaltyFee; ?></td>
						</tr>
					</tbody>
				</table>

				<div class="row">
					<div class="col-xs-4 col-xs-offset-8">
						<ul class="list-group mb-0">
							<li class="list-group-item">
								<span class="badge"><?php echo formatCurrency($total,$currCode); ?></span>
								<strong><?php echo $totalDueText; ?></strong>
							</li>
							<?php if ($row['hasRefund'] == '1') { ?>
								<li class="list-group-item">
									<span class="badge"><?php echo formatCurrency($row['refundAmount'],$currCode); ?></span>
									<strong><?php echo $amtRefundedText; ?></strong>
								</li>
							<?php } ?>
							<li class="list-group-item">
								<span class="badge"><strong><?php echo formatCurrency($newTotal,$currCode); ?></strong></span>
								<strong><?php echo $totalReceivedText; ?></strong>
							</li>
						</ul>
					</div>
				</div>

				<?php
					if ($row['hasRefund'] == '1') {
						if ($row['refundAmount'] == $total) {
							echo '<p class="mt-20">'.$rcpPayRefText.'</p>';
						} else {
							echo '<p class="mt-20">'.$rcpPayPartRefText.'</p>';
						}
					}
				?>

				<?php
					if(mysqli_num_rows($alertres) > 0) {
						while ($rows = mysqli_fetch_assoc($alertres)) {
							// If Start Date is set, use the Start date, else the Date the Alert was created
							if (!is_null($rows['alertStart'])) { $noticeDate = dateFormat($rows['alertStart']); } else { $noticeDate = dateFormat($rows['alertDate']); }
							echo '
									<div class="well well-sm mt-20">
										<strong>'.clean($rows['alertTitle']).'</strong><br />
										'.nl2br(htmlspecialchars_decode($rows['alertText'])).'
									</div>
								';
						}
					}
				?>
			<?php } else { ?>
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
			<h3><?php echo $accessErrorHeader; ?></h3>
			<div class="alertMsg warning mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-warning"></i>
				</div>
				<?php echo $permissionDenied; ?>
			</div>
		<?php } ?>
	</div>
</body>
</html>