<?php
	// Update Authorizations
	if (isset($_POST['submit']) && $_POST['submit'] == 'updateFlags') {
		if($_POST['theId'] == '') {
			$msgBox = alertBox($selectAdminErr, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			$theId = htmlentities($_POST['theId']);
			$theAdmin = htmlentities($_POST['theAdmin']);

			// Delete all Flags
			$stmt = $mysqli->prepare("DELETE FROM appauth WHERE adminId = ?");
			$stmt->bind_param('s', $theId);
			$stmt->execute();
			$stmt->close();

			// Insert New Flags
			if (isset($_POST['authFlags'])) {
				foreach($_POST['authFlags'] as $v) {
					$stmt = $mysqli->prepare("
										INSERT INTO
											appauth(
												adminId,
												authFlag,
												authDate
											) VALUES (
												?,
												?,
												NOW()
											)");
					$stmt->bind_param('ss',
						$theId,
						$v
					);
					$stmt->execute();
					$stmt->close();
				}
			}

			// Add Recent Activity
			$activityType = '13';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$admAuthsAct1.' '.$theAdmin;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			$msgBox = alertBox($admAuthsUpdMsg1." ".$theAdmin." ".$admAuthsUpdMsg2, "<i class='fa fa-check-square'></i>", "success");
		}
	}

	// Update Superuser Status
	if (isset($_POST['submit']) && $_POST['submit'] == 'updateSuperuser') {
		$isAdmin = htmlspecialchars($_POST['isAdmin']);
		$adminsId = htmlspecialchars($_POST['adminsId']);
		$adminsName = htmlspecialchars($_POST['adminsName']);

		$stmt = $mysqli->prepare("UPDATE
									admins
								SET
									isAdmin = ?
								WHERE
									adminId = ?"
		);
		$stmt->bind_param('ss',
								$isAdmin,
								$adminsId
		);
		$stmt->execute();
		$stmt->close();

		if ($isAdmin == '1') {
			// Add Recent Activity
			$activityType = '13';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$updatedAdminAct1.' '.$adminsName.' '.$updatedAdminAct2;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			$msgBox = alertBox($theAdminText." \"".$adminsName."\" ".$adminSuperUserUpdMsg1, "<i class='fa fa-check-square'></i>", "success");
		} else {
			// Add Recent Activity
			$activityType = '13';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' updated the Admin '.$adminsName.' to Restricted Access';
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			$msgBox = alertBox($theAdminText." \"".$adminsName."\" ".$adminSuperUserUpdMsg2, "<i class='fa fa-check-square'></i>", "success");
		}
    }

	// Get Data
	$qry = "SELECT * FROM admins WHERE isActive = 1";
	$res = mysqli_query($mysqli, $qry) or die('-1'.mysqli_error());

	// Get Available Auths
	$sql = "SELECT * FROM authdesc";
	$result = mysqli_query($mysqli, $sql) or die('-2'.mysqli_error());

	$adminPage = 'true';
	$pageTitle = $adminAuthsPageTitle;
	$jsFile = 'adminAuths';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ((checkArray('APPAUTH', $auths)) || $rs_isAdmin != '') {
				if ($msgBox) { echo $msgBox; }
		?>

			<h3><?php echo $pageTitle; ?></h3>
			<input type="hidden" id="yesOpt" value="<?php echo $yesBtn; ?>" />
			<input type="hidden" id="noOpt" value="<?php echo $noBtn; ?>" />

			<div class="row pb-20">
				<div class="col-md-6">
					<h4><?php echo $selectAdminH4; ?></h4>
					<hr />
					<div class="row">
						<div class="col-md-7">
							<select class="form-control" id="selectAdmin" name="selectAdmin">
								<option value="..."><?php echo $selectAdminOpt; ?></option>
								<?php
									while ($row = mysqli_fetch_assoc($res)) {
										echo '<option value="'.$row['adminId'].'">'.$row['adminName'].'</option>';
									}
								?>
							</select>
						</div>
						<div class="col-md-5">
							<a href="#" class="btn btn-sm btn-info" id="loadAdmin"><?php echo $loadAdminBtn; ?></a>
							<a href="#" class="btn btn-sm btn-warning resetForm"><?php echo $clearBtn; ?></a>
						</div>
					</div>

					<hr />

					<div id="msgText"><span></span></div>

					<div class="adminInfo mt-20 mb-20">
						<li class="list-group-item" id="admin_name"><span></span></li>
						<li class="list-group-item" id="admin_email"><span></span></li>
						<li class="list-group-item" id="admin_role"><span></span></li>
						<li class="list-group-item" id="isAdmin"><span></span></li>
					</div>

					<div class="superuser">
						<form action="" method="post" class="pt-20">
							<div class="form-group">
								<label for="isAdmin"><?php echo $superUserAccField; ?></label>
								<select class="form-control" name="isAdmin" id="isAdmin">
									<option value="0"><?php echo $noBtn; ?></option>
									<option value="1"><?php echo $yesBtn; ?></option>
								</select>
								<input type="hidden" id="isadminStatus" value="" />
							</div>
							<input type="hidden" name="adminsId" id="adminsId" />
							<input type="hidden" name="adminsName" id="adminsName" />
							<button type="input" name="submit" value="updateSuperuser" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveBtn; ?></button>
						</form>

						<div class="alertMsg default">
							<div class="msgIcon pull-left">
								<i class="fa fa-info-circle"></i>
							</div>
							<?php echo $superUserAccQuip; ?>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<h4><?php echo $accessAuthH4; ?></h4>
					<hr />
					<p><?php echo $accessAuthQuip; ?></p>
					<div class="alertMsg default">
						<div class="msgIcon pull-left">
							<i class="fa fa-warning"></i>
						</div>
						<?php echo $accessAuthMsg; ?>
					</div>
					<hr />
					<form action="" method="post" class="mt-20">
						<?php
							while ($rows = mysqli_fetch_assoc($result)) {
								echo '
										<div class="checkbox">
											<label>
												<input type="checkbox" name="authFlags[]" value="'.$rows['authFlag'].'" id="'.$rows['authFlag'].'">
												'.$rows['flagDesc'].'
											</label>
										</div>
									';
							}
						?>
						<hr />
						<input type="hidden" name="theId" id="theId" />
						<input type="hidden" name="theAdmin" id="theAdmin" />
						<button type="input" name="submit" value="updateFlags" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveAuthsBtn; ?></button>
						<a href="#" class="btn btn-warning btn-icon resetForm"><i class="fa fa-times-circle-o"></i> <?php echo $clearBtn; ?></a>
					</form>
				</div>
			</div>

			<input type="hidden" id="errorOne" value="Please select an Administrator to load first." />

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