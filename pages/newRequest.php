<?php
	// Get Property ID
	$sql = "SELECT
				properties.propertyId,
				properties.propertyName,
				assigned.adminId AS assignedTo,
				admins.adminEmail,
				admins.adminName
			FROM
				properties
				LEFT JOIN leases ON properties.propertyId = leases.propertyId
				LEFT JOIN assigned ON properties.propertyId = assigned.propertyId
				LEFT JOIN admins ON assigned.adminId = admins.adminId
			WHERE leases.leaseId = ".$rs_leaseId;
	$result = mysqli_query($mysqli, $sql) or die('-1' . mysqli_error());
	$rows = mysqli_fetch_assoc($result);
	$propId = $rows['propertyId'];
	$propName = $rows['propertyName'];
	$admnEmail = $rows['adminEmail'];

	// Save New Request
	if (isset($_POST['submit']) && $_POST['submit'] == 'newRequest') {
        // User Validations
		if($_POST['requestTitle'] == '') {
			$msgBox = alertBox($reqTitleReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['requestPriority'] == '...') {
			$msgBox = alertBox($reqPriorityReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['requestText'] == '') {
			$msgBox = alertBox($reqDescReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			// Set some variables
			$requestTitle = htmlspecialchars($_POST['requestTitle']);
			$requestPriority = htmlspecialchars($_POST['requestPriority']);
			$requestText = htmlspecialchars($_POST['requestText']);
			$ipAddress = $_SERVER['REMOTE_ADDR'];

			$stmt = $mysqli->prepare("
								INSERT INTO
									servicerequests(
										leaseId,
										propertyId,
										adminId,
										userId,
										requestTitle,
										requestText,
										requestDate,
										requestPriority,
										ipAddress
									) VALUES (
										?,
										?,
										0,
										?,
										?,
										?,
										NOW(),
										?,
										?
									)");
			$stmt->bind_param('sssssss',
				$rs_leaseId,
				$propId,
				$rs_userId,
				$requestTitle,
				$requestText,
				$requestPriority,
				$ipAddress
			);
			$stmt->execute();
			$stmt->close();
			
			$siteName = $set['siteName'];
			$siteEmail = $set['siteEmail'];
			
			$subject = $siteName.' '.$newReqEmailSubject.' '.$propName;
						
			$message = '<html><body>';
			$message .= '<h3>'.$subject.'</h3>';
			$message .= '<p><strong>'.$newReqEmail1.'</strong> '.$rs_userFull.'<br><strong>'.$newPaymentEmail2.'</strong> '.$propName.'</p>';
			$message .= '<p><strong>'.$newReqEmail2.'</strong> '.$requestTitle.'</p>';
			$message .= '<p><strong>'.$newReqEmail3.'</strong><br>'.nl2br($requestText).'</p>';
			$message .= '<hr>';
			$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
			$message .= '</body></html>';
			
			$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
			$headers .= "Reply-To: ".$siteEmail."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			
			// Get the email list
			$emailTo = serviceManagers($propId,$admnEmail,$siteEmail);
			$emailAdmins = assignedAdmins($propId,$siteEmail);
			
			// Send the Emails
			mail($emailTo, $subject, $message, $headers);
			mail($emailAdmins, $subject, $message, $headers);

			// Add Recent Activity
			$activityType = '3';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$newUsrReqAct;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			// Clear the form of Values
			$_POST['requestTitle'] = $_POST['requestText'] = '';

			$msgBox = alertBox($newReqSavedMsg, "<i class='fa fa-check-square'></i>", "success");
		}
	}

	$servPage = 'true';
	$pageTitle = $newRequestPageTitle;
	$addCss = '<link href="css/chosen.css" rel="stylesheet">';
	$chosen = 'true';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($rs_leaseId != '0') {
				if ($msgBox) { echo $msgBox; }
		?>
			<h3><?php echo $pageTitle; ?></h3>
		
			<form action="" method="post" class="mb-20">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="requestTitle"><?php echo $requestTitleField; ?></label>
							<input type="text" class="form-control" name="requestTitle" id="requestTitle" required="required" value="<?php echo isset($_POST['requestTitle']) ? $_POST['requestTitle'] : ''; ?>" />
							<span class="help-block"><?php echo $requestTitleFieldHelp; ?></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="requestPriority"><?php echo $priorityField; ?></label>
							<select class="form-control chosen-select" name="requestPriority">
								<option value="..."><?php echo $selectOption; ?></option>
								<?php
									$pri = "SELECT * FROM servicepriority";
									$prires = mysqli_query($mysqli, $pri) or die('-2'.mysqli_error());
									while ($prirow = mysqli_fetch_assoc($prires)) {
										echo '<option value="'.$prirow['priorityId'].'">'.$prirow['priorityTitle'].'</option>';
									}
								?>
							</select>
							<span class="help-block"><?php echo $priorityFieldHelp; ?></span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="requestText"><?php echo $requestDescField; ?></label>
					<textarea class="form-control" name="requestText" id="requestText" required="required" rows="8"><?php echo isset($_POST['requestText']) ? $_POST['requestText'] : ''; ?></textarea>
					<span class="help-block"><?php echo $requestDescFieldHelp; ?></span>
				</div>

				<button type="input" name="submit" value="newRequest" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveBtn; ?></button>
			</form>
		
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