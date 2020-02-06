<?php
	$ipAddress = $_SERVER['REMOTE_ADDR'];

	if (isset($_POST['submit']) && $_POST['submit'] == 'importAdmins') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");
			$i = 0;

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($i > 0) {
					$hash = md5(rand(0,1000));
					$stmt = $mysqli->prepare("
										INSERT INTO admins (
											adminId,
											isAdmin,
											adminRole,
											adminEmail,
											password,
											adminName,
											primaryPhone,
											altPhone,
											adminAddress,
											hash,
											createDate,
											isActive
										) VALUES (
											'$data[0]',
											'$data[1]',
											'Imported Account',
											'$data[3]',
											'$data[4]',
											'$data[5]',
											'$data[6]',
											'$data[7]',
											'$data[8]',
											'$hash',
											'$data[9]',
											'$data[10]'
										)
					");
					$stmt->execute();
				}
				$i++;
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$adminImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($adminRecsImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importUsers') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");
			
			// Get Documents Directory
			$docUploadPath = '../'.$set['userDocsPath'];

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				// Generate a RANDOM Hash
				$randomHash = uniqid(rand());
				// Take the first 8 hash digits and use them as part of the User's Upload Folder
				$randHash = substr($randomHash, 0, 8);

				// Replace any spaces with an underscore
				// And set to all lower-case
				$userFolder = str_replace(' ', '_', $data[6].'_'.$data[7]);
				$usersFolder = strtolower($userFolder);
				
				// Set the User Document Directory using the User's Names
				// Replace any spaces with an underscore and set to all lower-case
				$docFolderName = $usersFolder.'-'.$randHash;
				$userDocs = str_replace(' ', '_', $docFolderName);
				$userDocsFolder = strtolower($userDocs);

				// Create the User Document Directory
				if (mkdir($docUploadPath.$userDocsFolder, 0755, true)) {
					$newDir = $docUploadPath.$userDocsFolder;
				}
				
				if ($data[2] != '0') { $isLeased = '1'; } else { $isLeased = '0'; }
				$tempPass = encryptIt('pa55w0rd');
			
				$stmt = $mysqli->prepare("
									INSERT INTO users (
										userId,
										propertyId,
										leaseId,
										userFolder,
										userEmail,
										password,
										userFirstName,
										userLastName,
										userAddress,
										primaryPhone,
										altPhone,
										notes,
										pets,
										createDate,
										hash,
										isActive,
										isArchived,
										archiveDate,
										isLeased
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$userDocsFolder',
										'$data[4]',
										'$tempPass',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										'$data[9]',
										'$data[10]',
										'$data[11]',
										'$data[12]',
										'$data[13]',
										'$data[14]',
										'$data[15]',
										'$data[16]',
										'$data[17]',
										'$isLeased'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$tenantImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($tenantImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importProperties') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($data[26] == '1') { $active = '0'; } else { $active = '1'; }
				$stmt = $mysqli->prepare("
									INSERT INTO properties (
										propertyId,
										adminId,
										propertyName,
										propertyDesc,
										propertyAddress,
										isLeased,
										propertyRate,
										latePenalty,
										propertyDeposit,
										petsAllowed,
										propertyNotes,
										propertyAmenities,
										propertyListing,
										propertyType,
										propertyStyle,
										yearBuilt,
										propertySize,
										parking,
										heating,
										bedrooms,
										bathrooms,
										hoaName,
										hoaAddress,
										hoaPhone,
										hoaFee,
										feeSchedule,
										active,
										lastUpdated
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										'$data[9]',
										'$data[10]',
										'$data[11]',
										'$data[12]',
										'$data[13]',
										'$data[14]',
										'$data[15]',
										'$data[16]',
										'$data[17]',
										'$data[18]',
										'$data[19]',
										'$data[20]',
										'$data[21]',
										'$data[22]',
										'$data[23]',
										'$data[24]',
										'$data[25]',
										'$active',
										NOW()
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$propImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($propImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importLeases') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$stmt = $mysqli->prepare("
									INSERT INTO leases (
										leaseId,
										adminId,
										propertyId,
										leaseTerm,
										leaseStart,
										leaseEnd,
										notes,
										closed,
										userId,
										lastUpdated,
										ipAddress
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										NOW(),
										'$ipAddress'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$leaseImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($leaseImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importPayments') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$stmt = $mysqli->prepare("
									INSERT INTO payments (
										payId,
										adminId,
										userId,
										leaseId,
										hasRefund,
										paymentDate,
										amountPaid,
										penaltyFee,
										paymentFor,
										paymentType,
										isRent,
										rentMonth,
										rentYear,
										notes,
										propertyId,
										lastUpdated,
										ipAddress
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										'$data[9]',
										'$data[10]',
										'$data[11]',
										'$data[12]',
										'$data[13]',
										'$data[14]',
										NOW(),
										'$ipAddress'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$payImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($payImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importRefunds') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$stmt = $mysqli->prepare("
									INSERT INTO refunds (
										refundId,
										payId,
										propertyId,
										leaseId,
										userId,
										refundDate,
										refundAmount,
										refundFor,
										adminId,
										notes,
										lastUpdated,
										ipAddress
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										'$data[9]',
										NOW(),
										'$ipAddress'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$refImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($refImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importRequests') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$requestPriority = '1';
				if ($data[11] == '1') { $requestStatus = '6'; } else { $requestStatus = '4'; }

				$stmt = $mysqli->prepare("
									INSERT INTO servicerequests (
										requestId,
										userId,
										leaseId,
										adminId,
										requestDate,
										requestTitle,
										requestText,
										resolutionText,
										resolutionDate,
										needsFollowUp,
										followUpText,
										isClosed,
										dateCompleted,
										propertyId,
										requestPriority,
										requestStatus,
										lastUpdated,
										ipAddress
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										'$data[8]',
										'$data[9]',
										'$data[10]',
										'$data[11]',
										'$data[12]',
										'$data[13]',
										'$requestPriority',
										'$requestStatus',
										NOW(),
										'$ipAddress'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$servReqImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($servReqImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}
	
	if (isset($_POST['submit']) && $_POST['submit'] == 'importDiscussions') {
		$fname = $_FILES['importfile']['name'];
		$chk_ext = explode(".",$fname);

		if(strtolower($chk_ext[1]) == "csv") {
			$filename = $_FILES['importfile']['tmp_name'];
			$handle = fopen($filename, "r");

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$stmt = $mysqli->prepare("
									INSERT INTO servicenotes (
										noteId,
										requestId,
										userId,
										adminId,
										noteText,
										noteDate,
										leaseId,
										propertyId,
										lastUpdated,
										ipAddress
									) VALUES (
										'$data[0]',
										'$data[1]',
										'$data[2]',
										'$data[3]',
										'$data[4]',
										'$data[5]',
										'$data[6]',
										'$data[7]',
										NOW(),
										'$ipAddress'
									)
				");
				$stmt->execute();
			}
			
			// Add Recent Activity
			$activityType = '8';
			$rs_uid = '0';
			$activityTitle = $rs_adminName.' '.$servReqDiscImpAct;
			updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);

			fclose($handle);
			$msgBox = alertBox($servReqDiscImpMsg, "<i class='fa fa-check-square'></i>", "success");
			$stmt->close();
		} else {
			$msgBox = alertBox($selFileWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
		}
	}

	// Check for Admins
	$adminCk = $mysqli->query("SELECT 'X' FROM admins");
	$totalAdmins = mysqli_num_rows($adminCk);

	// Check for Users
	$userCk = $mysqli->query("SELECT 'X' FROM users");
	$totalUsers = mysqli_num_rows($userCk);

	// Check for Properties
	$propertyCk = $mysqli->query("SELECT 'X' FROM properties");
	$totalProperties = mysqli_num_rows($propertyCk);

	// Check for Leases
	$leaseCk = $mysqli->query("SELECT 'X' FROM leases");
	$totalLeases = mysqli_num_rows($leaseCk);

	// Check for Payments
	$paymentCk = $mysqli->query("SELECT 'X' FROM payments");
	$totalPayments = mysqli_num_rows($paymentCk);

	// Check for Refunds
	$refundsCk = $mysqli->query("SELECT 'X' FROM refunds");
	$totalRefunds = mysqli_num_rows($refundsCk);

	// Check for Service Requests
	$requestCk = $mysqli->query("SELECT 'X' FROM servicerequests");
	$totalRequests = mysqli_num_rows($requestCk);
	
	// Check for Service Request Discussions
	$discCk = $mysqli->query("SELECT 'X' FROM servicenotes");
	$totalDisc = mysqli_num_rows($discCk);

	$managePage = 'true';
	$pageTitle = $importExportPageTitle;

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ((checkArray('SITESET', $auths)) || $rs_isAdmin != '') {
				if ($msgBox) { echo $msgBox; }
		?>

		<h3><?php echo $impExpH3; ?></h3>
		<p><?php echo $impExpQuip; ?></p>
		<p><?php echo $impExpQuip1; ?></p>
		<div class="alertMsg warning">
			<div class="msgIcon pull-left">
				<i class="fa fa-info-circle"></i>
			</div>
			<?php echo $impExpQuipMsg; ?>
		</div>
		<p class="mt-20"><?php echo $impExpQuip2; ?></p>
		<p><?php echo $impExpQuip3; ?></p>

		<hr />

		<div class="row">
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="info">
						<legend class="info"><?php echo $impAdmins; ?></legend>
						<?php if ($totalAdmins == 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importAdmins" class="btn btn-info btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impAdmins1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="info">
						<legend class="info"><?php echo $impTenants; ?></legend>
						<?php if ($totalUsers < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importUsers" class="btn btn-info btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impTenants1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
		</div>

		<hr />

		<div class="row">
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="primary">
						<legend class="primary"><?php echo $impProp; ?></legend>
						<?php if ($totalProperties < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importProperties" class="btn btn-primary btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impProp1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="primary">
						<legend class="primary"><?php echo $impLeases; ?></legend>
						<?php if ($totalLeases < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importLeases" class="btn btn-primary btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impLeases1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
		</div>

		<hr />

		<div class="row">
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="success">
						<legend class="success"><?php echo $impPay; ?></legend>
						<?php if ($totalPayments < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importPayments" class="btn btn-success btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impPay1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="success">
						<legend class="success"><?php echo $impRefunds; ?></legend>
						<?php if ($totalRefunds < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importRefunds" class="btn btn-success btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impRefunds1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
		</div>

		<hr />

		<div class="row">
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="warning">
						<legend class="warning"><?php echo $impServReq; ?></legend>
						<?php if ($totalRequests < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importRequests" class="btn btn-warning btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impServReq1; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
			</div>
			<div class="col-md-6">
				<form action="" method="post" enctype="multipart/form-data">
					<fieldset class="warning">
						<legend class="warning"><?php echo $impServReq2; ?></legend>
						<?php if ($totalDisc < 1) { ?>
							<div class="form-group">
								<label for="file"><?php echo $selFileField; ?></label>
								<input type="file" name="importfile" required="" />
							</div>

							<button type="input" name="submit" value="importDiscussions" class="btn btn-warning btn-sm btn-icon-alt"><?php echo $impDataBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
						<?php } else { ?>
							<div class="alertMsg default">
								<div class="msgIcon pull-left">
									<i class="fa fa-info-circle"></i>
								</div>
								<?php echo $impServReq3; ?>
							</div>
						<?php } ?>
					</fieldset>
				</form>
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