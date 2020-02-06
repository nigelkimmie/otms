<?php
	$activeSet = 'active';
	$otherSet = '';

	// Delete Admin Account
	if (isset($_POST['submit']) && $_POST['submit'] == 'deleteAdmin') {
		$deleteId = htmlspecialchars($_POST['deleteId']);
		$adminName = htmlspecialchars($_POST['adminName']);

		if ($deleteId == '1') {
			// Add Recent Activity
			$activityType = '17';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$delAdmAccAct1;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			$msgBox = alertBox($delAdmAccErr, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			$stmt = $mysqli->prepare("DELETE FROM admins WHERE adminId = ?");
			$stmt->bind_param('s', $deleteId);
			$stmt->execute();
			$stmt->close();

			// Add Recent Activity
			$activityType = '17';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$delAdmAccAct2.' '.$adminName;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			$msgBox = alertBox($admAccDelConf1." ".$adminName." ".$admAccDelConf2, "<i class='fa fa-check-square'></i>", "success");
		}
		
		$otherSet = 'active';
		$activeSet = '';
	}

	// Get Active Admins
	$qry = "SELECT * FROM admins WHERE isActive = 1";
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());

	// Get Inactive Admins
	$sql = "SELECT * FROM admins WHERE isActive = 0";
	$result = mysqli_query($mysqli, $sql) or die('-2' . mysqli_error());

	$adminPage = 'true';
	$pageTitle = $adminAccPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'adminAccounts';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ((checkArray('MNGADMINS', $auths)) || $rs_isAdmin != '') {
				if ($msgBox) { echo $msgBox; }
		?>
				<div class="tabs">
					<ul class="tabsBody">
						<li class="<?php echo $activeSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $activeTabTitle; ?></h4>
							<section class="tabContent" id="active">
								<h3><?php echo $activeAdmAccH3; ?></h3>

								<?php if(mysqli_num_rows($res) < 1) { ?>
									<div class="alertMsg default mb-20">
										<div class="msgIcon pull-left">
											<i class="fa fa-info-circle"></i>
										</div>
										<?php echo $noActAdmAccFoundMsg; ?>
									</div>
								<?php } else { ?>
									<table id="actAdmins" class="display" cellspacing="0">
										<thead>
											<tr>
												<th><?php echo $adminNameFeild; ?></th>
												<th><?php echo $emailTabTitle; ?></th>
												<th class="text-center"><?php echo $primaryPhoneField; ?></th>
												<th class="text-center"><?php echo $titleHead; ?></th>
												<th class="text-center"><?php echo $superUserHead; ?></th>
												<th class="text-center"><?php echo $createDateHead; ?></th>
												<th class="text-center"><?php echo $lastSigninHead; ?></th>
											</tr>
										</thead>

										<tbody>
											<?php
												while ($row = mysqli_fetch_assoc($res)) {
													if (!is_null($row['primaryPhone'])) { $primaryPhone = decryptIt($row['primaryPhone']); } else { $primaryPhone = ''; }
													if ($row['isAdmin'] == '1') { $superuser = $yesBtn; } else { $superuser = $noBtn; }
													if ($row['lastVisited'] == '0000-00-00 00:00:00') { $lastVisited = $noneText; } else { $lastVisited = dateFormat($row['lastVisited']); }
											?>
													<tr>
														<td>
															<a href="index.php?action=viewAdmin&adminId=<?php echo $row['adminId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewAdminText; ?>">
																<?php echo clean($row['adminName']); ?>
															</a>
														</td>
														<td><?php echo clean($row['adminEmail']); ?></td>
														<td class="text-center"><?php echo $primaryPhone; ?></td>
														<td class="text-center"><?php echo clean($row['adminRole']); ?></td>
														<td class="text-center"><?php echo $superuser; ?></td>
														<td class="text-center"><?php echo dateFormat($row['createDate']); ?></td>
														<td class="text-center"><?php echo $lastVisited; ?></td>
													</tr>
											<?php } ?>
										</tbody>
									</table>
								<?php } ?>
							</section>
						</li>
						<li class="<?php echo $otherSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $inactiveTabTitle; ?></h4>
							<section class="tabContent" id="disabled">
								<h3><?php echo $inactAdmTitle; ?></h3>

								<?php if(mysqli_num_rows($result) < 1) { ?>
									<div class="alertMsg default mb-20">
										<div class="msgIcon pull-left">
											<i class="fa fa-info-circle"></i>
										</div>
										<?php echo $noInactAdmFoundMsg; ?>
									</div>
								<?php } else { ?>
									<table id="inactAdmins" class="display" cellspacing="0">
										<thead>
											<tr>
												<th><?php echo $adminNameFeild; ?></th>
												<th><?php echo $emailTabTitle; ?></th>
												<th class="text-center"><?php echo $titleHead; ?></th>
												<th class="text-center"><?php echo $superUserHead; ?></th>
												<th class="text-center"><?php echo $statusHead; ?></th>
												<th class="text-center"><?php echo $createDateHead; ?></th>
												<th class="text-center"><?php echo $lastUpdatedHead; ?></th>
												<th class="text-right"></th>
											</tr>
										</thead>

										<tbody>
											<?php
												while ($rows = mysqli_fetch_assoc($result)) {
													if ($rows['isAdmin'] == '1') { $superuser = $yesBtn; } else { $superuser = $noBtn; }
													if ($rows['lastUpdated'] == '0000-00-00 00:00:00') { $lastUpdated = ''; } else { $lastUpdated = dateFormat($rows['lastUpdated']); }
													if ($rows['isActive'] == '0' && $rows['isDisabled'] == '0') {
														$accStatus = $inactEnbText;
													} else if ($rows['isActive'] == '0' && $rows['isDisabled'] == '1') {
														$accStatus = $inactDisabText;
													} else {
														$accStatus = '';
													}
													if ($rows['adminId'] != '1') {
														$deleteLink = '
																		<a data-toggle="modal" href="#deleteAdmin'.$rows['adminId'].'" class="text-danger">
																			<i class="fa fa-trash" data-toggle="tooltip" data-placement="left" title="'.$delAdmAccText.'"></i>
																		</a>
																	';
													} else {
														$deleteLink = '
																		<a href="" class="text-muted">
																			<i class="fa fa-trash" data-toggle="tooltip" data-placement="left" title="'.$disabledText.'"></i>
																		</a>
																	';
													}
											?>
													<tr>
														<td>
															<a href="index.php?action=viewAdmin&adminId=<?php echo $rows['adminId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewAdminText; ?>">
																<?php echo clean($rows['adminName']); ?>
															</a>
														</td>
														<td><?php echo clean($rows['adminEmail']); ?></td>
														<td class="text-center"><?php echo clean($rows['adminRole']); ?></td>
														<td class="text-center"><?php echo $superuser; ?></td>
														<td class="text-center"><?php echo $accStatus; ?></td>
														<td class="text-center"><?php echo dateFormat($rows['createDate']); ?></td>
														<td class="text-center"><?php echo $lastUpdated; ?></td>
														<td class="text-right">
															<?php echo $deleteLink; ?>

															<div class="modal fade" id="deleteAdmin<?php echo $rows['adminId']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
																<div class="modal-dialog text-left">
																	<div class="modal-content">
																		<form action="" method="post">
																			<div class="modal-body">
																				<p class="lead">
																					<?php echo $delAdmConf.' '.clean($rows['adminName']); ?>?<br />
																					<small><?php echo $delAdmConf1; ?></small>
																				</p>
																			</div>
																			<div class="modal-footer">
																				<input name="deleteId" type="hidden" value="<?php echo $rows['adminId']; ?>" />
																				<input name="adminName" type="hidden" value="<?php echo clean($rows['adminName']); ?>" />
																				<button type="input" name="submit" value="deleteAdmin" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $yesBtn; ?></button>
																				<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle-o"></i> <?php echo $cancelBtn; ?></button>
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</td>
													</tr>
											<?php } ?>
										</tbody>
									</table>
								<?php } ?>
							</section>
						</li>
					</ul>
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