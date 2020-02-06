<?php
	$requestId = $mysqli->real_escape_string($_GET['requestId']);
	$ipAddress = $_SERVER['REMOTE_ADDR'];
	
	// Check if Service Request belongs to logged in user
	$userCheck = array();
	$usrCk = "SELECT
				servicerequests.userId,
				(SELECT users.primaryTenantId
					FROM users
					WHERE users.userId = servicerequests.userId
				) AS primaryTenant
			FROM
				servicerequests
			WHERE servicerequests.requestId = ".$requestId;
	$userChk = mysqli_query($mysqli, $usrCk) or die('-1' . mysqli_error());
	$uchk = mysqli_fetch_assoc($userChk);
	
	foreach ($uchk as $userIds) {
		$userCheck[] = $mysqli->real_escape_string($userIds);
	}
	
	// Update Service Request
	if (isset($_POST['submit']) && $_POST['submit'] == 'editRequest') {
		// Validation
		if($_POST['requestTitle'] == "") {
            $msgBox = alertBox($reqTitleReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else if($_POST['requestText'] == "") {
            $msgBox = alertBox($reqTextReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else {
			$requestTitle = htmlspecialchars($_POST['requestTitle']);
			$requestPriority = htmlspecialchars($_POST['requestPriority']);
			$requestText = htmlspecialchars($_POST['requestText']);

			$stmt = $mysqli->prepare("UPDATE
										servicerequests
									SET
										requestTitle = ?,
										requestText = ?,
										requestPriority = ?,
										lastUpdated = NOW()
									WHERE
										requestId = ?"
			);
			$stmt->bind_param('ssss',
									$requestTitle,
									$requestText,
									$requestPriority,
									$requestId
			);
			$stmt->execute();
			$stmt->close();

			// Add Recent Activity
			$activityType = '3';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$reqUpdatedAct.' "'.$requestTitle.'"';
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			$msgBox = alertBox($servReqUpdatedMsg1." \"".$requestTitle."\" ".$servReqUpdatedMsg2, "<i class='fa fa-check-square'></i>", "success");
		}
    }
	
	// Update Discussion Comment
	if (isset($_POST['submit']) && $_POST['submit'] == 'editComment') {
		// Validation
		if($_POST['noteText'] == "") {
            $msgBox = alertBox($discCmtReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
        } else {
			$noteText = htmlspecialchars($_POST['noteText']);
			$noteId = htmlspecialchars($_POST['noteId']);
			$requestTitle = htmlspecialchars($_POST['requestTitle']);

			$stmt = $mysqli->prepare("UPDATE
										servicenotes
									SET
										noteText = ?,
										lastUpdated = NOW()
									WHERE
										noteId = ?"
			);
			$stmt->bind_param('ss',
									$noteText,
									$noteId
			);
			$stmt->execute();
			$stmt->close();
			
			// Add Recent Activity
			$activityType = '3';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$discCmtUpdAct.' "'.$requestTitle.'"';
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			$msgBox = alertBox($discCmtUpdMsg." \"".$requestTitle."\" ".$servReqUpdatedMsg2, "<i class='fa fa-check-square'></i>", "success");

			// Clear the form of Values
			$_POST['noteText'] = '';
		}
    }
	
	// Save Comment
	if (isset($_POST['submit']) && $_POST['submit'] == 'addComment') {
        // User Validations
		if($_POST['noteText'] == '') {
			$msgBox = alertBox($discCmtReqMsg, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			// Set some variables
			$noteText = htmlspecialchars($_POST['noteText']);
			$leaseId = htmlspecialchars($_POST['leaseId']);
			$propertyId = htmlspecialchars($_POST['propertyId']);
			$requestTitle = htmlspecialchars($_POST['requestTitle']);
			$assignedEmail = htmlspecialchars($_POST['assignedEmail']);

			$stmt = $mysqli->prepare("
								INSERT INTO
									servicenotes(
										requestId,
										leaseId,
										propertyId,
										adminId,
										userId,
										noteText,
										noteDate,
										ipAddress
									) VALUES (
										?,
										?,
										?,
										0,
										?,
										?,
										NOW(),
										?
									)");
			$stmt->bind_param('ssssss',
				$requestId,
				$leaseId,
				$propertyId,
				$rs_userId,
				$noteText,
				$ipAddress
			);
			$stmt->execute();
			$stmt->close();
			
			$siteName = $set['siteName'];
			$siteEmail = $set['siteEmail'];
			
			$subject = $siteName.' '.$newDiscCmtEmailSubject.' '.$requestTitle;
						
			$message = '<html><body>';
			$message .= '<h3>'.$subject.'</h3>';
			$message .= '<p><strong>'.$newDiscCmtEmail1.'</strong> '.$rs_userFull.'</p>';
			$message .= '<p><strong>'.$newDiscCmtEmail2.'</strong><br>'.nl2br($noteText).'</p>';
			$message .= '<hr>';
			$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
			$message .= '</body></html>';
			
			$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
			$headers .= "Reply-To: ".$siteEmail."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			
			// Get the email list
			$emailTo = serviceManagers($propertyId,$assignedEmail,$siteEmail);
			
			mail($emailTo, $subject, $message, $headers);
			
			// Add Recent Activity
			$activityType = '3';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$newDiscCmtAct.' "'.$requestTitle.'"';
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);

			// Clear the form of Values
			$_POST['noteText'] = '';

			$msgBox = alertBox($newDiscCmtMsg1." ".$requestTitle." ".$newDiscCmtMsg2, "<i class='fa fa-check-square'></i>", "success");
		}
	}
	
	// Get Data
	$qry = "SELECT
				servicerequests.*,
				properties.propertyName,
				servicepriority.priorityTitle,
				servicestatus.statusTitle,
				admins.adminName,
				CONCAT(users.userFirstName,' ',users.userLastName) AS user
			FROM
				servicerequests
				LEFT JOIN properties ON servicerequests.propertyId = properties.propertyId
				LEFT JOIN servicepriority ON servicerequests.requestPriority = servicepriority.priorityId
				LEFT JOIN servicestatus ON servicerequests.requestStatus = servicestatus.statusId
				LEFT JOIN admins ON servicerequests.adminId = admins.adminId
				LEFT JOIN users ON servicerequests.userId = users.userId
			WHERE servicerequests.requestId = ".$requestId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);

	if ($row['needsFollowUp'] == '1') { $needsFollowUp = $yesBtn; } else { $needsFollowUp =  $noBtn; }

	// Get Assigned To
	$stmt = "SELECT
				admins.adminEmail,
				admins.adminName
			FROM
				servicerequests
				LEFT JOIN admins ON servicerequests.assignedTo = admins.adminId
			WHERE servicerequests.requestId = ".$requestId;
	$result = mysqli_query($mysqli, $stmt) or die('-2' . mysqli_error());
	$stmtrow = mysqli_fetch_assoc($result);

	if (is_null($stmtrow['adminName'])) { $assignedAdmin = '<em>'.$unassignedText.'</em>'; } else { $assignedAdmin = clean($stmtrow['adminName']); }
	if (is_null($stmtrow['adminEmail'])) { $assignedEmail = ''; } else { $assignedEmail = clean($stmtrow['adminEmail']); }

	// Get Comment Data
	$sql = "SELECT
				servicenotes.*,
				UNIX_TIMESTAMP(servicenotes.noteDate) AS orderDate,
				admins.adminName,
				admins.adminAvatar,
				CONCAT(users.userFirstName,' ',users.userLastName) AS user,
				users.userAvatar
			FROM
				servicenotes
				LEFT JOIN admins ON servicenotes.adminId = admins.adminId
				LEFT JOIN users ON servicenotes.userId = users.userId
			WHERE servicenotes.requestId = ".$requestId."
			ORDER BY orderDate";
	$results = mysqli_query($mysqli, $sql) or die('-3' . mysqli_error());

	$userPage = 'true';
	$pageTitle = $viewReqPageTitle;
	$addCss = '<link href="css/chosen.css" rel="stylesheet">';
	$chosen = 'true';
	$jsFile = 'viewRequest';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($rs_leaseId != '0' && in_array($rs_userId, $userCheck)) {
				if ($msgBox) { echo $msgBox; }
		?>
			<h3><?php echo $pageTitle; ?></h3>
		
			<div class="row mb-10">
				<div class="col-md-4">
					<ul class="list-group">
						<li class="list-group-item"><strong><?php echo $newReqEmail2; ?></strong> <?php echo clean($row['requestTitle']); ?></li>
						<li class="list-group-item"><strong><?php echo $newPaymentEmail2; ?></strong> <?php echo clean($row['propertyName']); ?></li>
						<li class="list-group-item"><strong><?php echo $reqPriorityText; ?></strong> <?php echo clean($row['priorityTitle']); ?></li>
						<li class="list-group-item"><strong><?php echo $reqStatusText; ?></strong> <?php echo clean($row['statusTitle']); ?></li>
						<li class="list-group-item"><strong><?php echo $reqAssignedToText; ?></strong> <?php echo $assignedAdmin; ?></li>
						<li class="list-group-item"><strong><?php echo $servReqDateText; ?></strong> <?php echo dateFormat($row['requestDate']); ?></li>
						<li class="list-group-item"><strong><?php echo $servReqByText; ?></strong>
							<?php
								if ($row['adminId'] != '0') {
									echo clean($row['adminName']);
								} else {
									echo clean($row['user']);
								}
							?>
						</li>
						<?php if ($row['isClosed'] == '1') { ?>
							<li class="list-group-item"><strong><?php echo $reqComplDateText; ?></strong> <?php echo dateFormat($row['resolutionDate']); ?></li>
						<?php } ?>
					</ul>
					<?php if ($row['isClosed'] == '1') { ?>
						<div class="alertMsg success">
							<div class="msgIcon pull-left">
								<i class="fa fa-check"></i>
							</div>
							<?php echo $reqClosedServReqMsg; ?>
						</div>
					<?php } ?>
				</div>
				<div class="col-md-8">
					<div class="tabs mt-0">
						<ul class="tabsBody">
							<li class="active">
								<h4 class="tabHeader" tabindex="0"><?php echo $servReqTabTitle; ?></h4>
								<section class="tabContent" id="request">
									<div class="well well-sm"><p class="lead mb-0"><?php echo nl2br(htmlspecialchars_decode($row['requestText'])); ?></p></div>

									<?php if ($row['adminCreated'] == '0') { ?>
										<a data-toggle="modal" href="#editRequest" class="btn btn-xs btn-default btn-icon"><i class="fa fa-pencil"></i> <?php echo $editReqBtn; ?></a>
										<div class="modal fade" id="editRequest" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog modal-lg">
												<div class="modal-content">
													<div class="modal-header">
														<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
														<h4 class="modal-title"><?php echo $editReqH4; ?></h4>
													</div>
													<form action="" method="post">
														<div class="modal-body">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="requestTitle"><?php echo $requestTitleField; ?></label>
																		<input type="text" class="form-control" name="requestTitle" id="requestTitle" required="required" value="<?php echo clean($row['requestTitle']); ?>" />
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="requestPriority"><?php echo $priorityField; ?></label>
																		<select class="form-control chosen-select" name="requestPriority" id="requestPriority">
																			<?php
																				$pri = "SELECT * FROM servicepriority";
																				$prires = mysqli_query($mysqli, $pri) or die('-5'.mysqli_error());
																				while ($prirow = mysqli_fetch_assoc($prires)) {
																					echo '<option value="'.$prirow['priorityId'].'">'.$prirow['priorityTitle'].'</option>';
																				}
																			?>
																		</select>
																		<input type="hidden" id="priTitle" value="<?php echo $row['priorityTitle']; ?>" />
																	</div>
																</div>
															</div>

															<div class="form-group">
																<label for="requestText"><?php echo $requestDescField; ?></label>
																<textarea class="form-control" name="requestText" id="requestText" required="required" rows="8"><?php echo clean($row['requestText']); ?></textarea>
																<span class="help-block"><?php echo $requestDescFieldHelp; ?></span>
															</div>
														</div>
														<div class="modal-footer">
															<button type="input" name="submit" value="editRequest" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveBtn; ?></button>
															<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle-o"></i> <?php echo $cancelBtn; ?></button>
														</div>
													</form>
												</div>
											</div>
										</div>
									<?php } ?>
								</section>
							</li>
							<?php if ($row['isClosed'] == '1') { ?>
								<li>
									<h4 class="tabHeader" tabindex="0"><?php echo $servReqResTabTitle; ?></h4>
									<section class="tabContent" id="resolution">
										<div class="well well-sm"><strong><?php echo $resDescText; ?></strong><br /><?php echo nl2br(htmlspecialchars_decode($row['resolutionText'])); ?></div>
										<div class="row mb-10">
											<div class="col-md-6">
												<ul class="list-group mb-10">
													<li class="list-group-item"><strong><?php echo $dateClosedText; ?></strong> <?php echo dateFormat($row['resolutionDate']); ?></li>
												</ul>
											</div>
											<div class="col-md-6">
												<ul class="list-group mb-10">
													<li class="list-group-item"><strong><?php echo $followUpText; ?></strong> <?php echo $needsFollowUp; ?></li>
												</ul>
											</div>
										</div>
									</section>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>

			<hr />

			<h3><?php echo $servReqDiscH3; ?></h3>
			<?php if(mysqli_num_rows($results) > 0) { ?>
				<ul class="commentsBox">
					<?php
						while ($rows = mysqli_fetch_assoc($results)) {
							if ($rows['adminId'] != '0') {
								$cmtType = 'cmtRight';
								$avatarImg = $avatarDir.$rows['adminAvatar'];
								$postedBy = clean($rows['adminName']);
							} else {
								$cmtType = 'cmtLeft';
								$avatarImg = $avatarDir.$rows['userAvatar'];
								$postedBy = clean($rows['user']);
							}
					?>
							<li class="<?php echo $cmtType; ?>">
								<div class="cmtAvatar">
									<img alt="<?php echo $postedByText; ?>" src="<?php echo $avatarImg; ?>" />
								</div>
								<div class="cmtText">
									<p><?php echo nl2br(htmlspecialchars_decode($rows['noteText'])); ?></p>
									<div class="cmtFooter">
										<span class="cmtTextUser"><?php echo $postedByText; ?> <?php echo $postedBy; ?></span>
										<span class="cmtTextDate">on <?php echo dateFormat($rows['noteDate']); ?> <?php echo $atText; ?> <?php echo timeFormat($rows['noteDate']); ?></span>&nbsp;
										<?php if ($rows['userId'] != '0') { ?>
											<small>
												<a data-toggle="modal" href="#editComment<?php echo $rows['noteId']; ?>"><i class="fa fa-pencil text-warning" data-toggle="tooltip" data-placement="left" title="<?php echo $editCommentBtn; ?>"></i></a>&nbsp;
											</small>
										<?php } ?>
									</div>
								</div>

								<?php if ($rows['userId'] != '0') { ?>
									<div class="modal fade" id="editComment<?php echo $rows['noteId']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
										<div class="modal-dialog modal-lg">
											<div class="modal-content">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
													<h4 class="modal-title"><?php echo $editDiscCmtH3; ?></h4>
												</div>
												<form action="" method="post">
													<div class="modal-body">
														<div class="form-group">
															<textarea class="form-control" name="noteText" rows="8"><?php echo clean($rows['noteText']); ?></textarea>
														</div>
													</div>
													<div class="modal-footer">
														<input type="hidden" name="noteId" value="<?php echo $rows['noteId']; ?>" />
														<input type="hidden" name="requestTitle" value="<?php echo clean($row['requestTitle']); ?>" />
														<button type="input" name="submit" value="editComment" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveBtn; ?></button>
														<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle-o"></i> <?php echo $cancelBtn; ?></button>
													</div>
												</form>
											</div>
										</div>
									</div>
								<?php } ?>
							</li>
					<?php } ?>
				</ul>
			<?php } else { ?>
				<div class="alertMsg default mb-20">
					<div class="msgIcon pull-left">
						<i class="fa fa-info-circle"></i>
					</div>
					<?php echo $noDiscCmtFoundMsg; ?>
				</div>
			<?php } ?>

			<hr />

			<h3><?php echo $addDiscCmtH3; ?></h3>
			<form action="" method="post" class="mb-20">
				<div class="form-group">
					<textarea class="form-control" name="noteText" id="noteText" required="required" rows="8"><?php echo isset($_POST['noteText']) ? $_POST['noteText'] : ''; ?></textarea>
				</div>
				<input type="hidden" name="leaseId" value="<?php echo $row['leaseId']; ?>" />
				<input type="hidden" name="propertyId" value="<?php echo $row['propertyId']; ?>" />
				<input type="hidden" name="requestTitle" value="<?php echo clean($row['requestTitle']); ?>" />
				<input type="hidden" name="assignedEmail" value="<?php echo $assignedEmail; ?>" />
				<button type="input" name="submit" value="addComment" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $saveBtn; ?></button>
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