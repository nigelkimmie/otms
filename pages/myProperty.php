<?php
	// Get Pictures Directory
	$propPicsPath = $set['propPicsPath'];

	$todayDate = date("Y-m-d");
	$currentYear = date('Y');
	$currentMonth = date('F');
	$currentDay = date('d');
	$tenantIsLate = '';

	if ($rs_leaseId != '0') {
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

		// Get Property Data
		$qry = "SELECT
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
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
		$row = mysqli_fetch_assoc($res);

		if ($row['petsAllowed'] == '1') { $pets = $yesBtn; } else { $pets = $noBtn; }
		$profileurl = preg_replace('/ /', '-', clean($row['adminName']));

		$lateRentAmt = $row['propertyRate'] + $row['latePenalty'];

		// Get Property Pictures
		$sql = "SELECT * FROM proppictures WHERE propertyId = ".$row['propertyId'];
		$result = mysqli_query($mysqli, $sql) or die('-2' . mysqli_error());

		// Get Residents
		$resqry = "SELECT * FROM users WHERE isResident = 1 AND propertyId = ".$row['propertyId']." AND leaseId = ".$row['leaseId'];
		$qryres = mysqli_query($mysqli, $resqry) or die('-3' . mysqli_error());

		// Check if the Tenant is late on current month's rent
		$latecheck1 = "SELECT
						users.leaseId,
						leases.leaseStart
					FROM
						users
						LEFT JOIN leases ON users.leaseId = leases.leaseId
					WHERE
						users.leaseId = ".$primaryLeaseId." AND
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
							users.leaseId = ".$primaryLeaseId." AND
							payments.rentMonth = '".$currentMonth."' AND
							payments.rentYear = '".$currentYear."'";
			$lateres = mysqli_query($mysqli, $latecheck2) or die('-9' . mysqli_error());
			if (mysqli_num_rows($lateres) > 0) { $tenantIsLate = 'false'; } else { $tenantIsLate = 'true'; }
		} else {
			$tenantIsLate = 'false';
		}
	}

	// Get Property Files
	$sqlstmt = "SELECT * FROM propfiles WHERE propertyId = ".$row['propertyId'];
	$sqlres = mysqli_query($mysqli, $sqlstmt) or die('-4' . mysqli_error());

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

	$propPage = 'true';
	$pageTitle = $myPropPageTitle;

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($primaryLeaseId != '0') {
				if ($msgBox) { echo $msgBox; }
		?>

			<h3><?php echo $pageTitle; ?></h3>

			<?php
				if ($set['enablePayments'] == '1') {
					if ($tenantIsLate == 'true') {
						if ($currentDay > '5') {
							echo '
									<div class="alertMsg warning mb-20">
										<div class="msgIcon pull-left">
											<i class="fa fa-warning"></i>
										</div>
										'.$dashRentLateText.' <strong>'.formatCurrency($lateRentAmt,$currCode).'</strong> '.$dashRentLateText1.'
									</div>
								';
						} else {
							echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency($row['propertyRate'],$currCode).'</div>';
						}
					} else if ($tenantIsLate == 'false') {
						echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency('0.00',$currCode).'</div>';
					} else {
						echo '<div class="well well-success well-sm">'.$currAmtDueText.' '.formatCurrency($row['propertyRate'],$currCode).'</div>';
					}
				}
			?>

			<div class="row">
				<div class="col-md-9">
					<div class="row mb-10">
						<div class="col-sm-8">
							<?php if ($row['featured'] == '1') { ?>
								<span class="ribbon top-left listed ribbon-primary">
									<small><?php echo $featuredText; ?></small>
								</span>
							<?php } ?>
							<img alt="" src="<?php echo $propPicsPath.clean($row['propertyImage']); ?>" class="img-responsive" />
						</div>
						<div class="col-sm-4">
							<h4 class="mt-0"><?php echo clean($row['propertyName']); ?></h4>
							<hr class="mt-0" />
							<p><?php echo nl2br(clean($row['propertyAddress'])); ?></p>
							<ul class="propLists">
								<li><strong><?php echo $bedroomsText; ?></strong>: <?php echo clean($row['bedrooms']); ?></li>
								<li><strong><?php echo $bathroomsText; ?></strong>: <?php echo clean($row['bathrooms']); ?></li>
								<li><strong><?php echo $sixeText; ?></strong>: <?php echo clean($row['propertySize']); ?></li>
								<li><strong><?php echo $heatingText; ?></strong>: <?php echo clean($row['heating']); ?></li>
								<li><strong><?php echo $yearBuiltText; ?></strong>: <?php echo clean($row['yearBuilt']); ?></li>
								<li><strong><?php echo $petsAllowedHead; ?></strong>: <?php echo $pets; ?></li>
								<li><strong><?php echo $parkingText; ?></strong>: <?php echo clean($row['parking']); ?></li>
								<li><strong><?php echo $depositText; ?></strong>: <?php echo formatCurrency($row['propertyDeposit'],$currCode); ?></li>
								<li><strong><?php echo $lateFeeText; ?></strong>: <?php echo formatCurrency($row['latePenalty'],$currCode); ?></li>
							</ul>
						</div>
					</div>

					<hr />

					<?php if ($row['propertyDesc'] != '') { ?>
						<p class="lead"><?php echo nl2br(htmlspecialchars_decode($row['propertyDesc'])); ?></p>
					<?php } ?>

					<?php if ($row['propertyAmenities'] != '') { ?>
						<p class="mb-20"><strong><?php echo $propAmenText; ?></strong><br /><?php echo nl2br(htmlspecialchars_decode($row['propertyAmenities'])); ?></p>
					<?php } ?>

				</div>
				<div class="col-md-3">
					<?php if ($set['enablePayments'] == '1') { ?>
						<a href="page.php?page=viewPayments" class="btn btn-xs btn-block btn-default btn-icon"><i class="fa fa-usd"></i> <?php echo $viewLeaseHistBtn; ?></a>
						<a href="page.php?page=newPayment" class="btn btn-xs btn-block btn-default btn-icon"><i class="fa fa-credit-card"></i> <?php echo $newPaymentBtn; ?></a>
					<?php } ?>
					<h3><?php echo $myLeaseH3; ?></h3>
					<ul class="propLists">
						<li>
							<strong><?php echo $leaseTermText; ?></strong> <?php echo clean($row['leaseTerm']); ?><br />
							<small><?php echo dateFormat($row['leaseStart']); ?> &mdash; <?php echo dateFormat($row['leaseEnd']); ?></small>
						</li>
						<li>
							<strong><?php echo $managerLiText; ?></strong>
							<a href="profile.php?profile=<?php echo $profileurl; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewMngProfileText; ?>">
								<?php echo clean($row['adminName']); ?>
							</a>
						</li>
					</ul>

					<h3><?php echo $myResidentsH3; ?></h3>
					<?php
						if(mysqli_num_rows($qryres) > 0) {
							echo '<ul class="list-group">';
							while ($resrow = mysqli_fetch_assoc($qryres)) {
								if ($resrow['primaryPhone'] != '') { $residentPhone = decryptIt($resrow['primaryPhone']); } else { $residentPhone = ''; }
					?>
								<li class="list-group-item group-item-xs"><?php echo clean($resrow['userFirstName']).' '.clean($resrow['userLastName']); ?></li>
					<?php
							}
							echo '</ul>';
						} else {
					?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $noResidentsFoundMsg; ?>
						</div>
					<?php } ?>

					<h3><?php echo $propertyFilesH3; ?></h3>
					<?php
						if(mysqli_num_rows($sqlres) > 0) {
							echo '<ul class="list-group">';
							while ($filerow = mysqli_fetch_assoc($sqlres)) {
					?>
								<a href="page.php?page=viewFile&fileId=<?php echo $filerow['fileId']; ?>" class="list-group-item group-item-xs">
									<?php echo clean($filerow['fileName']); ?>
								</a>
					<?php
							}
							echo '</ul>';
						} else {
					?>
						<div class="alertMsg default mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $noUplFilesFoundMsg; ?>
						</div>
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