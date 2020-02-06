<?php
	$rptError = $reportType = $rptResults = '';
	$where = array();
	
	switch ($_POST['rptType']) {
		case "adminRpt1":
			$admAccType = $mysqli->real_escape_string($_POST['admAccType']);
			
			if ($admAccType == 'all') {
				$where[] = 'isActive IN ("1","0")';
				$reportType = $adminRptTitle1;
			} else if ($admAccType == 'active') {
				$where[] = 'isActive IN ("1")';
				$reportType = $adminRptTitle2;
			} else if ($admAccType == 'inactive') {
				$where[] = 'isActive IN ("0")';
				$reportType = $adminRptTitle3;
			} else if ($admAccType == 'disabled') {
				$where[] = 'isDisabled IN ("1")';
				$reportType = $adminRptTitle4;
			} else {
				$rptError = 'true';
				$reportType = $reportErrorH3;
			}
			
			if (!empty($where)) {
				$whereSql = "WHERE\n" . implode("\nOR ",$where);
			}
			
			if ($rptError == '') {
				// Get Data
				$qry = 'SELECT * FROM admins '.$whereSql;
				$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
			}
			
			$rptResults = '1';
		break;
		case "adminRpt2":
			$adminsId = $mysqli->real_escape_string($_POST['adminsId']);
			$reportType = $adminRptTitle5;
						
			if ($rptError == '') {
				// Get Data
				$qry = 'SELECT * FROM admins WHERE adminId = '.$adminsId;
				$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
				$row = mysqli_fetch_assoc($res);
				
				// Decrypt data for display
				if ($row['primaryPhone'] != '') { $primaryPhone = decryptIt($row['primaryPhone']); } else { $primaryPhone = ''; }
										
				if ($row['isActive'] == '1') { $isActive = $activeTabTitle; } else { $isActive= $inactiveText; }
				if ($row['isDisabled'] == '1') { $isDisabled = $disabledText;  } else { $isDisabled = ''; }
				if ($row['isAdmin'] == '1') { $superuser = $yesBtn; } else { $superuser = $noBtn; }
				
				// Get Assigned Property Count
				$ap = "SELECT 'X' FROM assigned WHERE adminId = ".$row['adminId'];
				$apres = mysqli_query($mysqli, $ap) or die('-2' . mysqli_error());
				$apcount = mysqli_num_rows($apres);
				
				// Get Assigned Request Count
				$ar = "SELECT 'X' FROM servicerequests WHERE isClosed = 0 AND assignedTo = ".$row['adminId'];
				$arres = mysqli_query($mysqli, $ar) or die('-2' . mysqli_error());
				$arcount = mysqli_num_rows($arres);
			}
			
			$rptResults = '2';
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
	$pageTitle = $adminRptPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'adminRpt';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($rptError == '') {
				if ((checkArray('ADMINRPT', $auths)) || $rs_isAdmin != '') {
					if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $pageTitle; ?></h3>
				<p class="lead mb-0"><?php echo $reportType; ?></p>

				<?php
					if ($rptResults == '1') {
						if(mysqli_num_rows($res) < 1) {
				?>
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
									<th><?php echo $adminManagerHead; ?></th>
									<th class="text-center"><?php echo $contUsFormPhone; ?></th>
									<th class="text-center"><?php echo $superUserHead; ?></th>
									<th class="text-center"><?php echo $roleHead; ?></th>
									<th class="text-center"><?php echo $statusHead; ?></th>
									<th class="text-center"><?php echo $assignedPropHead; ?></th>
									<th class="text-center"><?php echo $assignedReqHead; ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
									while ($row = mysqli_fetch_assoc($res)) {
										// Decrypt data for display
										if ($row['primaryPhone'] != '') { $primaryPhone = decryptIt($row['primaryPhone']); } else { $primaryPhone = ''; }
										
										if ($row['isActive'] == '1') { $isActive = $activeTabTitle; } else { $isActive = $inactiveText; }
										if ($row['isDisabled'] == '1') { $isDisabled = $disabledText; } else { $isDisabled = ''; }
										if ($row['isAdmin'] == '1') { $superuser = $yesBtn; } else { $superuser = $noBtn; }
										
										// Get Assigned Property Count
										$ap = "SELECT 'X' FROM assigned WHERE adminId = ".$row['adminId'];
										$apres = mysqli_query($mysqli, $ap) or die('-2' . mysqli_error());
										$apcount = mysqli_num_rows($apres);
										
										// Get Assigned Request Count
										$ar = "SELECT 'X' FROM servicerequests WHERE isClosed = 0 AND assignedTo = ".$row['adminId'];
										$arres = mysqli_query($mysqli, $ar) or die('-2' . mysqli_error());
										$arcount = mysqli_num_rows($arres);
								?>
										<tr>
											<td><?php echo clean($row['adminName']); ?></td>
											<td class="text-center"><?php echo $primaryPhone; ?></td>
											<td class="text-center"><?php echo $superuser; ?></td>
											<td class="text-center"><?php echo clean($row['adminRole']); ?></td>
											<td class="text-center"><?php echo $isActive.' '.$isDisabled; ?></td>
											<td class="text-center"><?php echo $apcount; ?></td>
											<td class="text-center"><?php echo $arcount; ?></td>
										</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php
						}
					}
				?>
				
				<?php
					if ($rptResults == '2') {
						if(mysqli_num_rows($res) < 1) {
				?>
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
									<th><?php echo $adminManagerHead; ?></th>
									<th class="text-center"><?php echo $contUsFormPhone; ?></th>
									<th class="text-center"><?php echo $superUserHead; ?></th>
									<th class="text-center"><?php echo $roleHead; ?></th>
									<th class="text-center"><?php echo $statusHead; ?></th>
									<th class="text-center"><?php echo $assignedPropHead; ?></th>
									<th class="text-center"><?php echo $assignedReqHead; ?></th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td><?php echo clean($row['adminName']); ?></td>
									<td class="text-center"><?php echo $primaryPhone; ?></td>
									<td class="text-center"><?php echo $superuser; ?></td>
									<td class="text-center"><?php echo clean($row['adminRole']); ?></td>
									<td class="text-center"><?php echo $isActive.' '.$isDisabled; ?></td>
									<td class="text-center"><?php echo $apcount; ?></td>
									<td class="text-center"><?php echo $arcount; ?></td>
								</tr>
							</tbody>
						</table>
					<?php
						}
					}
				?>
				
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