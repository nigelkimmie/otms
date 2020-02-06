<?php
	$accSet = 'active';
	$emlSet = $pssSet = $avtSet = '';

	// Get the file types allowed from Site Settings
	$avatarTypes = $set['avatarTypesAllowed'];
	// Replace the commas with a comma space
	$avatarTypesAllowed = preg_replace('/,/', ', ', $avatarTypes);
	
	// Update Account
	if (isset($_POST['submit']) && $_POST['submit'] == 'accInfo') {
		// Validation
		if($_POST['userFirstName'] == "") {
            $msgBox = alertBox($fnameReq, "<i class='fa fa-times-circle'></i>", "danger");
        } else if($_POST['userLastName'] == "") {
            $msgBox = alertBox($lnameReq, "<i class='fa fa-times-circle'></i>", "danger");
        } else if($_POST['primaryPhone'] == "") {
            $msgBox = alertBox($primPhoneReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else if($_POST['userAddress'] == "") {
            $msgBox = alertBox($mailingAddrReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else {
			$userFirstName = htmlspecialchars($_POST['userFirstName']);
			$userLastName = htmlspecialchars($_POST['userLastName']);
			$primaryPhone = encryptIt($_POST['primaryPhone']);
			if (isset($_POST['altPhone'])) {
				$altPhone = encryptIt($_POST['altPhone']);
			} else {
				$altPhone = null;
			}
			$userAddress = encryptIt($_POST['userAddress']);
			if ($_POST['location'] == "") {
				$location = 'Nairobi, KE';
			} else {
				$location = htmlspecialchars($_POST['location']);
			}

			$stmt = $mysqli->prepare("UPDATE
										users
									SET
										userFirstName = ?,
										userLastName = ?,
										primaryPhone = ?,
										altPhone = ?,
										userAddress = ?,
										location = ?
									WHERE
										userId = ?"
			);
			$stmt->bind_param('sssssss',
									$userFirstName,
									$userLastName,
									$primaryPhone,
									$altPhone,
									$userAddress,
									$location,
									$rs_userId
			);
			$stmt->execute();
			$stmt->close();

			// Update the SESSION Data
			$_SESSION['rs']['userFirstName'] = $userFirstName;
			$rs_userFirst = $userFirstName;
			$_SESSION['rs']['userLastName'] = $userLastName;
			$rs_userLast = $userLastName;
			$rs_userFull = $userFirstName.' '.$userLastName;

			$_SESSION['rs']['location'] = $location;
			$rs_adminLoc = $location;

			// Add Recent Activity
			$activityType = '9';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$profileUpdAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			$msgBox = alertBox($profileUpdatedMsg, "<i class='fa fa-check-square'></i>", "success");
		}
    }
	
	// Update Email
	if (isset($_POST['submit']) && $_POST['submit'] == 'accEmail') {
		// Validation
		if($_POST['newEmail'] == "") {
            $msgBox = alertBox($newEmailAddrReq, "<i class='fa fa-times-circle'></i>", "danger");
        } else if($_POST['newEmail'] != $_POST['newEmailr']) {
            $msgBox = alertBox($newEmailsNotMatchMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else {
			$newEmail = htmlspecialchars($_POST['newEmail']);

			$stmt = $mysqli->prepare("UPDATE
										users
									SET
										userEmail = ?
									WHERE
										userId = ?"
			);
			$stmt->bind_param('ss',
									$newEmail,
									$rs_userId
			);
			$stmt->execute();
			$stmt->close();

			// Update the SESSION Data
			$_SESSION['rs']['userEmail'] = $newEmail;
			$rs_userEmail = $newEmail;

			// Add Recent Activity
			$activityType = '9';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$emailAddrUpdatedAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			$msgBox = alertBox($emailAddrUpdatedMsg, "<i class='fa fa-check-square'></i>", "success");
			
			// Clear the Form of values
			$_POST['newEmail'] = $_POST['newEmailr'] = '';
		}

		$emlSet = 'active';
		$accSet = $pssSet = $avtSet = '';
    }
	
	// Change Password
	if (isset($_POST['submit']) && $_POST['submit'] == 'cngePass') {
		$currentPass = encryptIt($_POST['currentpass']);
		// Validation
		if($_POST['currentpass'] == "") {
            $msgBox = alertBox($accPasswordReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else if ($currentPass != $_POST['passwordOld']) {
			$msgBox = alertBox($curAccPassWrongMsg, "<i class='fa fa-warning'></i>", "warning");
		} else if($_POST['password'] == '') {
			$msgBox = alertBox($newPassReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['password_r'] == '') {
			$msgBox = alertBox($retypeNewPassMsg, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['password'] != $_POST['password_r']) {
			$msgBox = alertBox($newPassNoMatchMsg, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			if(isset($_POST['password']) && $_POST['password'] != "") {
				$password = encryptIt($_POST['password']);
			} else {
				$password = $_POST['passwordOld'];
			}

			$stmt = $mysqli->prepare("UPDATE
										users
									SET
										password = ?
									WHERE
										userId = ?"
			);
			$stmt->bind_param('ss',
									$password,
									$rs_userId
			);
			$stmt->execute();
			$stmt->close();

			// Add Recent Activity
			$activityType = '9';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$usrPassChangeAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			$msgBox = alertBox($passChangedConf, "<i class='fa fa-check-square'></i>", "success");
			
			// Clear the Form of values
			$_POST['currentpass'] = $_POST['password'] = $_POST['password_r'] = '';
		}

		$pssSet = 'active';
		$accSet = $emlSet = $avtSet = '';
    }
	
	// Upload Avatar Image
	if (isset($_POST['submit']) && $_POST['submit'] == 'newAvatar') {
		// Get the File Types allowed
		$fileExt = $set['avatarTypesAllowed'];
		$allowed = preg_replace('/,/', ', ', $fileExt); // Replace the commas with a comma space (, )
		$ftypes = array($fileExt);
		$ftypes_data = explode( ',', $fileExt );

		// Check file type
		$ext = substr(strrchr(basename($_FILES['file']['name']), '.'), 1);
		if (!in_array($ext, $ftypes_data)) {
			$msgBox = alertBox($avatarTypeErrMsg, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			$avatarDir = $set['avatarFolder'];

			// Rename the User's Avatar to the User's Name
			$avatarName = clean($rs_userFull);

			// Replace any spaces with an underscore
			// And set to all lower-case
			$newName = str_replace(' ', '-', $avatarName);
			$fileName = strtolower($newName);

			// Generate a RANDOM Hash
			$randomHash = uniqid(rand());
			// Take the first 8 hash digits and use them as part of the Image Name
			$randHash = substr($randomHash, 0, 8);

			$fullName = $fileName.'-'.$randHash;

			// set the upload path
			$avatarUrl = basename($_FILES['file']['name']);

			// Get the files original Ext
			$extension = explode(".", $avatarUrl);
			$extension = end($extension);

			// Set the files name to the name set in the form
			// And add the original Ext
			$newAvatarName = $fullName.'.'.$extension;
			$movePath = $avatarDir.$newAvatarName;

			$stmt = $mysqli->prepare("
								UPDATE
									users
								SET
									userAvatar = ?
								WHERE
									userId = ?");
			$stmt->bind_param('ss',
							   $newAvatarName,
							   $rs_userId);

			if (move_uploaded_file($_FILES['file']['tmp_name'], $movePath)) {
				$stmt->execute();
				$msgBox = alertBox($avatarUplMsg, "<i class='fa fa-check-square'></i>", "success");
				$stmt->close();

				// Add Recent Activity
				$activityType = '9';
				$rs_aid = '0';
				$activityTitle = $rs_userFull.' '.$newAvatarUpldMsg;;
				updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
			} else {
				$msgBox = alertBox($newAvatarUplErrMsg, "<i class='fa fa-times-circle'></i>", "danger");

				// Add Recent Activity
				$activityType = '9';
				$rs_aid = '0';
				$activityTitle = $rs_userFull.' '.$newAvatarUplErrAct;
				updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
			}
		}

		$avtSet = 'active';
		$accSet = $emlSet = $pssSet = '';
	}
	
	// Delete Avatar Image
	if (isset($_POST['submit']) && $_POST['submit'] == 'deleteAvatar') {
		// Get the User's Avatar url
		$sql = "SELECT userAvatar FROM users WHERE userId = ".$rs_userId;
		$result = mysqli_query($mysqli, $sql) or die('-1'.mysqli_error());
		$r = mysqli_fetch_assoc($result);
		$avatarName = $r['userAvatar'];

		$avatarDir = $set['avatarFolder'];
		$filePath = $avatarDir.$avatarName;
		// Delete the User's image from the server
		if (file_exists($filePath)) {
			unlink($filePath);

			// Update the User record
			$avatarImage = 'userDefault.png';
			$stmt = $mysqli->prepare("
								UPDATE
									users
								SET
									userAvatar = ?
								WHERE
									userId = ?");
			$stmt->bind_param('ss',
							   $avatarImage,
							   $rs_userId);
			$stmt->execute();
			$msgBox = alertBox($delAvatarMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();

			// Add Recent Activity
			$activityType = '9';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$usrAvatarDelAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
		} else {
			$msgBox = alertBox($delAvatarErrMsg, "<i class='fa fa-warning'></i>", "warning");

			// Add Recent Activity
			$activityType = '9';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$delAvatarErrAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
		}

		$avtSet = 'active';
		$accSet = $emlSet = $pssSet = '';
	}
	
	// Get Data
	$qry = "SELECT * FROM users WHERE userId = ".$rs_userId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$rows = mysqli_fetch_assoc($res);
	
	// Decrypt data
	if ($rows['primaryPhone'] != '') { $primaryPhone = decryptIt($rows['primaryPhone']); } else { $primaryPhone = '';  }
	if ($rows['altPhone'] != '') { $altPhone = decryptIt($rows['altPhone']); } else { $altPhone = '';  }
	if ($rows['userAddress'] != '') { $userAddress = decryptIt($rows['userAddress']); } else { $userAddress = '';  }

	$userPage = 'true';
	$pageTitle = $myProfileNavLink;
	$jsFile = 'myProfile';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php if ($msgBox) { echo $msgBox; } ?>

		<div class="row">
			<div class="col-md-4">
				<div class="profileBox mt-20 mb-10">
					<div class="cover">
						<div class="profilePic">
							<img src="<?php echo $avatarDir.$rows['userAvatar']; ?>" class="publicPic" />
						</div>
					</div>

					<div class="profileBody border">
						<h1><?php echo clean($rows['userFirstName']).' '.clean($rows['userLastName']); ?></h1>
						<h4 class="mt-10">
							<?php echo clean($rows['userEmail']); ?><br />
							<small><?php echo $memberSinceText; ?> <?php echo dateFormat($rows['createDate']); ?></small>
						</h3>
					</div>
				</div>
				
				<h3><?php echo $persInfoText; ?></h3>
				<p><?php echo $persInfoQuip; ?></p>
			</div>
			<div class="col-md-8">
				<div class="tabs">
					<ul class="tabsBody">
						<li class="<?php echo $accSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $accountTabTitle; ?></h4>
							<section class="tabContent" id="account">
								<h3><?php echo $accountTabH3; ?></h3>
								<form action="" method="post" class="form-horizontal">
									<div class="form-group">
										<label for="userFirstName" class="col-sm-3 control-label"><?php echo $contUsFormFirstName; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="userFirstName" id="userFirstName" required="required" value="<?php echo clean($rows['userFirstName']); ?>" />
										</div>
									</div>
									<div class="form-group">
										<label for="userLastName" class="col-sm-3 control-label"><?php echo $contUsFormLastName; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="userLastName" id="userLastName" required="required" value="<?php echo clean($rows['userLastName']); ?>" />
										</div>
									</div>
									<div class="form-group">
										<label for="primaryPhone" class="col-sm-3 control-label"><?php echo $primaryPhoneField; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="primaryPhone" id="primaryPhone" required="required" value="<?php echo $primaryPhone; ?>" />
										</div>
									</div>
									<div class="form-group">
										<label for="altPhone" class="col-sm-3 control-label"><?php echo $altPhoneField; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="altPhone" id="altPhone" value="<?php echo $altPhone; ?>" />
										</div>
									</div>
									<div class="form-group">
										<label for="userAddress" class="col-sm-3 control-label"><?php echo $mailingAddrField; ?></label>
										<div class="col-sm-9">
											<textarea class="form-control" name="userAddress" id="userAddress" required="required" rows="3"><?php echo $userAddress; ?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label for="location" class="col-sm-3 control-label"><?php echo $locationField; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="location" id="location" value="<?php echo clean($rows['location']); ?>" />
										</div>
									</div>

									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-9">
											<button type="input" name="submit" value="accInfo" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveChangesBtn; ?></button>
										</div>
									</div>
								</form>
							</section>
						</li>
						<li class="<?php echo $emlSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $emailTabTitle; ?></h4>
							<section class="tabContent" id="email">
								<h3><?php echo $accountEmailText; ?></h3>
								<form action="" method="post" class="form-horizontal">
									<div class="form-group">
										<label for="currEmail" class="col-sm-3 control-label"><?php echo $currEmailAddrField; ?></label>
										<div class="col-sm-9">
											<input type="text" class="form-control" disabled="" value="<?php echo $rs_userEmail; ?>" />
										</div>
									</div>
									<div class="form-group">
										<label for="newEmail" class="col-sm-3 control-label"><?php echo $newEmailAddrField; ?></label>
										<div class="col-sm-9">
											<input type="email" class="form-control" name="newEmail" id="newEmail" required="required" value="<?php echo isset($_POST['newEmail']) ? $_POST['newEmail'] : ''; ?>" />
											<span class="help-block"><?php echo $newEmailAddrFieldHelp; ?></span>
										</div>
									</div>
									<div class="form-group">
										<label for="newEmailr" class="col-sm-3 control-label"><?php echo $rptEmailAddrField; ?></label>
										<div class="col-sm-9">
											<input type="email" class="form-control" name="newEmailr" id="newEmailr" required="required" value="<?php echo isset($_POST['newEmailr']) ? $_POST['newEmailr'] : ''; ?>" />
											<span class="help-block"><?php echo $rptEmailAddrFieldHelp; ?></span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-9">
											<button type="input" name="submit" value="accEmail" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveChangesBtn; ?></button>
										</div>
									</div>
								</form>
							</section>
						</li>
						<li class="<?php echo $pssSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $passwordText; ?></h4>
							<section class="tabContent" id="password">
								<h3><?php echo $passwordTabH3; ?></h3>
								<form action="" method="post" class="form-horizontal">
									<div class="form-group">
										<label for="currentpass" class="col-sm-3 control-label"><?php echo $currPasswordField; ?></label>
										<div class="col-sm-9">
											<input type="password" class="form-control" name="currentpass" id="currentpass" required="required" value="" />
											<span class="help-block"><?php echo $currPasswordFieldHelp; ?></span>
										</div>
									</div>
									<div class="form-group">
										<label for="password" class="col-sm-3 control-label"><?php echo $newPasswordField; ?></label>
										<div class="col-sm-9">
											<input type="password" class="form-control" name="password" id="password" required="required" value="" />
											<span class="help-block"><?php echo $newPasswordFieldHelp; ?></span>
										</div>
									</div>
									<div class="form-group">
										<label for="password_r" class="col-sm-3 control-label"><?php echo $rptPasswordField; ?></label>
										<div class="col-sm-9">
											<input type="password" class="form-control" name="password_r" id="password_r" required="required" value="" />
											<span class="help-block"><?php echo $rptPasswordFieldHelp; ?></span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-9">
											<input type="hidden" name="passwordOld" value="<?php echo $rows['password']; ?>" />
											<button type="input" name="submit" value="cngePass" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveChangesBtn; ?></button>
										</div>
									</div>
								</form>
							</section>
						</li>
						<li class="<?php echo $avtSet; ?>">
							<h4 class="tabHeader" tabindex="0"><?php echo $avatarTabTitle; ?></h4>
							<section class="tabContent" id="avatar">
								<h3><?php echo $avatarTabH3; ?></h3>
								<p><?php echo $avatarTabQuip.' '.$avatarTypesAllowed; ?></p>
								<div class="clearfix"></div>
								<hr />
								<?php if ($rows['userAvatar'] == 'userDefault.png') { ?>
									<form enctype="multipart/form-data" action="" method="post">
										<div class="form-group">
											<label for="file"><?php echo $avatarField; ?></label>
											<input type="file" id="file" name="file" required="required" />
										</div>

										<button type="input" name="submit" value="newAvatar" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $uplAvatarBtn; ?></button>
									</form>
								<?php } else { ?>
									<p><?php echo $remAvatarQuip; ?></p>
									<a data-toggle="modal" href="#deleteAvatar" class="btn btn-warning btn-icon" data-dismiss="modal"><i class="fa fa-ban"></i> <?php echo $remAvatarBtn; ?></a>
								<?php } ?>
							</section>
						</li>
					</ul>
				</div>

				<?php if ($rows['userAvatar'] != 'userDefault.png') { ?>
					<div id="deleteAvatar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<form action="" method="post">
									<div class="modal-body">
										<p class="lead"><?php echo $remAvatarConf; ?></p>
									</div>
									<div class="modal-footer">
										<button type="input" name="submit" value="deleteAvatar" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $remAvatarConfBtn; ?></button>
										<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle-o"></i> <?php echo $cancelBtn; ?></button>
									</div>
								</form>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

	</div>