<?php
	// Check if install.php is present
	if(is_dir('install')) {
		header("Location: install/install.php");
	} else {
		if(!isset($_SESSION)) session_start();

		// Access DB Info
		include('config.php');

		// Get Settings Data
		include ('includes/settings.php');
		$set = mysqli_fetch_assoc($setRes);

		// Include Functions
		include('includes/functions.php');

		// Include Sessions & Localizations
		include('includes/sessions.php');
		
		if ((isset($_SESSION['rs']['adminId'])) && ($_SESSION['rs']['adminId'] != '')) {
			header('Location: index.php');
		} else if ((isset($_SESSION['rs']['userId'])) && ($_SESSION['rs']['userId'] != '')) {
			header('Location: index.php');
		}
		
		$msgDiv = '';
		$installUrl		= $set['installUrl'];
		$siteName		= $set['siteName'];
		$siteEmail		= $set['siteEmail'];
		
		// Account Log In
		if (isset($_POST['submit']) && $_POST['submit'] == 'signIn') {
			if($_POST['emailAddy'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$emailReq."</p>";
			} else if($_POST['password'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$passwordReq."</p>";
			} else {
				$isUser = $isAdmin = '';
				$emailCheck = $_POST['emailAddy'];

				// Check for a User Account
				$usrCheck = $mysqli->query("SELECT 'X' FROM users WHERE userEmail = '".$emailCheck."'");
				if ($usrCheck->num_rows) { $isUser = 'true'; }

				// Check for an Admin Account
				$admCheck = $mysqli->query("SELECT 'X' FROM admins WHERE adminEmail = '".$emailCheck."'");
				if ($admCheck->num_rows) { $isAdmin = 'true'; }

				if ($isUser == 'true') {
					$check1 = $mysqli->query("SELECT isActive, isDisabled, isArchived FROM users WHERE userEmail = '".$emailCheck."'");
					$trow = mysqli_fetch_assoc($check1);

					if ($trow['isArchived'] == '1') {				// If the account is Archived - Do NOT allow the login
						$qry = "SELECT userId, userFirstName, userLastName FROM users WHERE userEmail = '".$emailCheck."'";
						$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
						$row = mysqli_fetch_assoc($results);
						$theUser = $row['userId'];
						$userName = $row['userFirstName'].' '.$row['userLastName'];

						// Add Recent Activity
						$activityType = '11';
						$rs_aid = '0';
						$activityTitle = '"'.$userName.'" '.$archivedAcc;
						updateActivity($rs_aid,$theUser,$activityType,$activityTitle);

						$msgDiv = "<p class=\"text-center text-danger\">".$disabledAccMsg1." ".$userName." ".$archivedAccMsg2."</p>";
					} else if ($trow['isDisabled'] == '1') {				// If the account is Disabled - Do NOT allow the login
						$qry = "SELECT userId, userFirstName, userLastName FROM users WHERE userEmail = '".$emailCheck."'";
						$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
						$row = mysqli_fetch_assoc($results);
						$theUser = $row['userId'];
						$userName = $row['userFirstName'].' '.$row['userLastName'];

						// Add Recent Activity
						$activityType = '11';
						$rs_aid = '0';
						$activityTitle = '"'.$userName.'" '.$disabledAcc;
						updateActivity($rs_aid,$theUser,$activityType,$activityTitle);

						$msgDiv = "<p class=\"text-center text-danger\">".$disabledAccMsg1." ".$userName." ".$disabledAccMsg2."</p>";
					} else if ($trow['isActive'] == '1') {			// If the account is Active - Allow the login
						// User Account
						$userEmail = $_POST['emailAddy'];
						$password = encryptIt($_POST['password']);

						if($stmt = $mysqli -> prepare("
												SELECT
													userId,
													userEmail,
													userFirstName,
													userLastName,
													location,
													leaseId,
													isResident
												FROM
													users
												WHERE
													userEmail = ? AND password = ?
						"))	{
							$stmt -> bind_param("ss",
												$userEmail,
												$password
							);
							$stmt -> execute();
							$stmt -> bind_result(
										$userId,
										$userEmail,
										$userFirstName,
										$userLastName,
										$location,
										$leaseId,
										$isResident
							);
							$stmt -> fetch();
							$stmt -> close();

							if (!empty($userId)) {
								if(!isset($_SESSION))session_start();
									$_SESSION['rs']['userId']			= $userId;
									$_SESSION['rs']['userEmail'] 		= $userEmail;
									$_SESSION['rs']['userFirstName']	= $userFirstName;
									$_SESSION['rs']['userLastName']		= $userLastName;
									$_SESSION['rs']['location']			= $location;
									$_SESSION['rs']['leaseId']			= $leaseId;
									$_SESSION['rs']['isResident']		= $isResident;
								header('Location: page.php');

								// Add Recent Activity
								$activityType = '11';
								$rs_aid = '0';
								$rs_uid = $userId;
								$activityTitle = $userFirstName.' '.$userLastName.' '.$userAccSignIn;
								updateActivity($rs_aid,$rs_uid,$activityType,$activityTitle);
							} else {
								$msgDiv = "<p class=\"text-center text-danger\">".$signInFailed."</p>";
							}
						}

						// Update the Last Login Date for User
						$lastVisited = date("Y-m-d H:i:s");
						$sqlStmt = $mysqli->prepare("
												UPDATE
													users
												SET
													lastVisited = ?
												WHERE
													userId = ?
						");
						$sqlStmt->bind_param('ss',
										   $lastVisited,
										   $userId
						);
						$sqlStmt->execute();
						$sqlStmt->close();

					} else if ($trow['isActive'] == '0') {
						// If the account is not active, show a message
						if ($set['allowRegistrations'] == '1') {
							$msgDiv = "<p class=\"text-center text-danger\">".$inactiveAccMsg."<br /><small><a data-toggle=\"modal\" href=\"#resendEmail\">".$resendActivationTitle."</a></small></p>";
						} else {
							$msgDiv = "<p class=\"text-center text-danger\">".$inactiveAccMsg."</p>";
						}
					} else {
						// No account found
						$msgDiv = "<p class=\"text-center text-danger\">".$noAccMsg."</p>";
					}
				} else if ($isAdmin == 'true') {
					// Admin Account
					$check2 = $mysqli->query("SELECT isActive, isDisabled, lastVisited FROM admins WHERE adminEmail = '".$emailCheck."'");
					$arow = mysqli_fetch_assoc($check2);

					if ($arow['isDisabled'] == '1') {			// If the account is Disabled - Do NOT allow the login
						// If the account is Disabled, show a message
						$msgDiv = "<p class=\"text-center text-danger\">".$disabledAccMsg3."</p>";
					} else if ($arow['isActive'] == '1') {		// If the account is active - allow the login
						$admins_email = $_POST['emailAddy'];
						$password = encryptIt($_POST['password']);

						if($stmt = $mysqli -> prepare("
												SELECT
													adminId,
													adminEmail,
													adminName,
													isAdmin,
													adminRole,
													location
												FROM
													admins
												WHERE
													adminEmail = ? AND password = ?
						"))	{
							$stmt -> bind_param("ss",
												$admins_email,
												$password
							);
							$stmt -> execute();
							$stmt -> bind_result(
										$adminId,
										$adminEmail,
										$adminName,
										$isAdmin,
										$adminRole,
										$location
							);
							$stmt -> fetch();
							$stmt -> close();

							if (!empty($adminId)) {
								if(!isset($_SESSION))session_start();
									$_SESSION['rs']['adminId']		= $adminId;
									$_SESSION['rs']['adminEmail']	= $adminEmail;
									$_SESSION['rs']['adminName']	= $adminName;
									$_SESSION['rs']['isAdmin'] 		= $isAdmin;
									$_SESSION['rs']['adminRole'] 	= $adminRole;
									$_SESSION['rs']['location'] 	= $location;
								header('Location: admin/index.php');

								// Add Recent Activity
								$activityType = '11';
								$sr_uid = '0';
								$activityTitle = $adminName.' '.$admAccSignin;
								$activityIp = $_SERVER['REMOTE_ADDR'];
								updateActivity($adminId,$sr_uid,$activityType,$activityTitle);
							} else {
								$msgDiv = "<p class=\"text-center text-danger\">".$signInFailed."</p>";
							}
						}

						// Update the Last Login Date for Admin
						$lastVisited = date("Y-m-d H:i:s");
						$sqlStmt = $mysqli->prepare("
												UPDATE
													admins
												SET
													lastVisited = ?
												WHERE
													adminId = ?
						");
						$sqlStmt->bind_param('ss',
										   $lastVisited,
										   $adminId
						);
						$sqlStmt->execute();
						$sqlStmt->close();

					} else if ($arow['isActive'] == '0') {
						// If the account is not active, show a message
						$msgDiv = "<p class=\"text-center text-danger\">".$inactiveAccMsg."</p>";
					} else {
						// No account found
						$msgDiv = "<p class=\"text-center text-danger\">".$noAccMsg."</p>";
					}
				} else {
					$msgDiv = "<p class=\"text-center text-danger\">".$signInFailed."</p>";
				}
			}
		}
		
		// Reset Account Password Form
		if (isset($_POST['submit']) && $_POST['submit'] == 'resetPass') {
			// Validation
			if ($_POST['emailAddy'] == "") {
				$msgDiv = "<p class=\"text-center text-danger\">Your Email Address is Required.</p>";
			} else {
				$isUsr = $isAdmin = '';
				$emailCheck = htmlspecialchars($_POST['emailAddy']);

				// Check for a User Account
				$cltCheck = $mysqli->query("SELECT 'X' FROM users WHERE userEmail = '".$emailCheck."'");
				if ($cltCheck->num_rows) { $isUsr = 'true'; }

				// Check for an Admin Account
				$admCheck = $mysqli->query("SELECT 'X' FROM admins WHERE adminEmail = '".$emailCheck."'");
				if ($admCheck->num_rows) { $isAdmin = 'true'; }

				if ($isUsr == 'true') {
					// User Account
					$userEmail = $mysqli->real_escape_string($_POST['emailAddy']);
					$query = "SELECT userEmail FROM users WHERE userEmail = ?";
					$stmt = $mysqli->prepare($query);
					$stmt->bind_param("s",$userEmail);
					$stmt->execute();
					$stmt->bind_result($emailUser);
					$stmt->store_result();
					$numrows = $stmt->num_rows();

					if ($numrows == 1) {
						// Generate a RANDOM Hash for a password
						$randomPassword = uniqid(rand());

						// Take the first 8 digits and use them as the password we intend to email the Employee
						$emailPassword = substr($randomPassword, 0, 8);

						// Encrypt $emailPassword for the database
						$newpassword = encryptIt($emailPassword);

						//update password in db
						$updatesql = "UPDATE users SET password = ? WHERE userEmail = ?";
						$update = $mysqli->prepare($updatesql);
						$update->bind_param("ss",
												$newpassword,
												$userEmail
											);
						$update->execute();
						
						$qry = "SELECT userId, userFirstName, userLastName FROM users WHERE userEmail = '".$userEmail."'";
						$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
						$row = mysqli_fetch_assoc($results);
						$theUser = $row['userId'];
						$userName = $row['userFirstName'].' '.$row['userLastName'];
						
						// Add Recent Activity
						$activityType = '9';
						$rs_aid = '0';
						$activityTitle = $userName.' '.$userReset;
						updateActivity($rs_aid,$theUser,$activityType,$activityTitle);
						
						$subject = $siteName.' '.$passwordResetSubject;
						
						$message = '<html><body>';
						$message .= '<h3>'.$subject.'</h3>';
						$message .= '<p>'.$passResetEmail1.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$emailPassword.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$passResetEmail2.'</p>';
						$message .= '<p>'.$passResetEmail3.' '.$installUrl.'sign-in.php</p>';
						$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
						$message .= '</body></html>';
						
						$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
						$headers .= "Reply-To: ".$siteEmail."\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
						
						mail($userEmail, $subject, $message, $headers);

						$msgDiv = "<p class=\"text-center text-success\">".$passwordResetMsg."</p>";
						$_POST['crMessage'] = '';
						
						$isReset = 'true';
						$stmt->close();
					} else {
						// No account found
						$msgDiv = "<p class=\"text-center text-danger\">".$noAccMsg."</p>";
					}
				} else if ($isAdmin == 'true') {
					// Admin Account
					$adminEmail = $mysqli->real_escape_string($_POST['emailAddy']);
					$query = "SELECT adminEmail FROM admins WHERE adminEmail = ?";
					$stmt = $mysqli->prepare($query);
					$stmt->bind_param("s",$adminEmail);
					$stmt->execute();
					$stmt->bind_result($emailAdmin);
					$stmt->store_result();
					$numrows = $stmt->num_rows();

					if ($numrows == 1) {
						// Generate a RANDOM Hash for a password
						$randomPassword = uniqid(rand());

						// Take the first 8 digits and use them as the password we intend to email the User
						$emailPassword = substr($randomPassword, 0, 8);

						// Encrypt $emailPassword for the database
						$newpassword = encryptIt($emailPassword);

						//update password in db
						$updatesql = "UPDATE admins SET password = ? WHERE adminEmail = ?";
						$update = $mysqli->prepare($updatesql);
						$update->bind_param("ss",
												$newpassword,
												$adminEmail
											);
						$update->execute();
						
						$qry = "SELECT adminId, adminName FROM admins WHERE adminEmail = '".$adminEmail."'";
						$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
						$row = mysqli_fetch_assoc($results);
						$theAdmin = $row['adminId'];
						$adminName = $row['adminName'];
						
						// Add Recent Activity
						$activityType = '9';
						$rs_uid = '0';
						$activityTitle = $adminName.' reset their Admin Account Password';
						updateActivity($theAdmin,$rs_uid,$activityType,$activityTitle);
						
						$subject = $siteName.' Admin Account Password Reset';
						
						$message = '<html><body>';
						$message .= '<h3>'.$subject.'</h3>';
						$message .= '<p>'.$passResetEmail1.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$emailPassword.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$passResetEmail2.'</p>';
						$message .= '<p>'.$passResetEmail3.' '.$installUrl.'sign-in.php</p>';
						$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
						$message .= '</body></html>';
						
						$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
						$headers .= "Reply-To: ".$siteEmail."\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
						
						mail($adminEmail, $subject, $message, $headers);

						$msgDiv = "<p class=\"text-center text-success\">".$passwordResetMsg."</p>";
						$_POST['crMessage'] = '';
						
						$isReset = 'true';
						$stmt->close();
					} else {
						// No account found
						$msgDiv = "<p class=\"text-center text-danger\">".$noAccMsg."</p>";
					}
				} else {
					$msgDiv = "<p class=\"text-center text-danger\">".$noAccMsg."</p>";
				}
			}
		}
		
		// New User
		if (isset($_POST['submit']) && $_POST['submit'] == 'userSignUp') {
			// User Validations
			if($_POST['userEmail'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$validEmailReq."</p>";
			} else if($_POST['password'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$accPasswordReq."</p>";
			} else if($_POST['password'] != $_POST['passwordr']) {
				$msgDiv = "<p class=\"text-center text-danger\">".$passwordsNoMatch."</p>";
			} else if($_POST['userFirstName'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$nameReq."</p>";
			} else if($_POST['userLastName'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$lnameReq."</p>";
			} else if($_POST['captchaanswer'] == "") {
				$msgDiv = "<p class=\"text-center text-danger\">".$captchaReq."</p>";
			// Black Hole Trap to help reduce bot registrations
			} else if($_POST['noAnswer'] != '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$newAccError."</p>";
				$_POST['userEmail'] = $_POST['userFirstName'] = $_POST['userLastName'] = '';
			} else {
				if(strtolower($_POST['captchaanswer']) == $_SESSION['thecode']) {
					// Set some variables
					$dupEmail = '';
					$usrEmail = htmlspecialchars($_POST['userEmail']);
					$userFirstName = htmlspecialchars($_POST['userFirstName']);
					$userLastName = htmlspecialchars($_POST['userLastName']);
					$primaryTenantId = '0';
					
					// Get Documents Directory
					$docUploadPath = $set['userDocsPath'];

					// Check for Duplicate email
					$check = $mysqli->query("SELECT 'X' FROM users WHERE userEmail = '".$usrEmail."'");
					if ($check->num_rows) {
						$dupEmail = 'true';
					}

					// If duplicates are found
					if ($dupEmail != '') {
						$msgDiv = "<p class=\"text-center text-danger\">".$duplicateAccFound."</p>";
						$_POST['userEmail'] = '';
					} else {
						$usrName = $userFirstName.' '.$userLastName;

						// Generate a RANDOM Hash
						$randomHash = uniqid(rand());
						// Take the first 8 hash digits and use them as part of the User's Upload Folder
						$randHash = substr($randomHash, 0, 8);
						
						// Replace any spaces with an underscore
						// And set to all lower-case
						$userFolder = str_replace(' ', '_', $usrName);
						$usersFolder = strtolower($userFolder);

						// Set the User Document Directory using the User's Names
						// Replace any spaces with an underscore and set to all lower-case
						$docFolderName = $usersFolder.'-'.$randHash;
						$userDocs = str_replace(' ', '_', $docFolderName);
						$userDocsFolder = strtolower($userDocs);

						// Create the User Document Directory
						mkdir($docUploadPath.$userDocsFolder, 0755, true);

						// Add the New Account
						$password = encryptIt($_POST['password']);
						$hash = md5(rand(0,1000));
						
						$stmt = $mysqli->prepare("
											INSERT INTO
												users(
													userEmail,
													password,
													userFirstName,
													userLastName,
													userFolder,
													createDate,
													isActive,
													hash,
													primaryTenantId
												) VALUES (
													?,
													?,
													?,
													?,
													?,
													NOW(),
													0,
													?,
													?
												)");
						$stmt->bind_param('sssssss',
							$usrEmail,
							$password,
							$userFirstName,
							$userLastName,
							$userDocsFolder,
							$hash,
							$primaryTenantId
						);
						$stmt->execute();
						$stmt->close();
						
						$qry = "SELECT userId FROM users WHERE userEmail = '".$usrEmail."' AND hash = '".$hash."'";
						$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
						$row = mysqli_fetch_assoc($results);
						$theUser = $row['userId'];
						
						// Add Recent Activity
						$activityType = '24';
						$rs_aid = '0';
						$activityTitle = $userFirstName.' ' .$userLastName.' created a New User Account';
						updateActivity($rs_aid,$theUser,$activityType,$activityTitle);
						
						$newPass = $mysqli->real_escape_string($_POST['password']);
						
						$subject = $siteName.' '.$newUserSubject;
						
						$message = '<html><body>';
						$message .= '<h3>'.$subject.'</h3>';
						$message .= '<p>'.$newUserEmail1.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$newUserEmail2.' '.$newPass.'</p>';
						$message .= '<p>'.$newUserEmail3.$installUrl.'activate.php?userEmail='.$usrEmail.'&hash='.$hash.'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$newUserEmail4.'</p>';
						$message .= '<p>'.$newUserEmail5.' '.$installUrl.'sign-in.php</p>';
						$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
						$message .= '</body></html>';
						
						$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
						$headers .= "Reply-To: ".$siteEmail."\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
						
						mail($usrEmail, $subject, $message, $headers);

						$msgDiv = "<p class=\"text-center text-success\">".$newAccCreatedMsg."</p>";
						// Clear the Form of values
						$_POST['userEmail'] = $_POST['userFirstName'] = $_POST['userLastName'] = '';
					}
				} else {
					$msgDiv = "<p class=\"text-center text-danger\">".$captchaError."</p>";
				}
			}
		}
		
		// Resend Account Activation Email
		if (isset($_POST['submit']) && $_POST['submit'] == 'resendEmail') {
			// User Validations
			if($_POST['accountEmail'] == '') {
				$msgDiv = "<p class=\"text-center text-danger\">".$emailReq2."</p>";
			} else {
				$isAccount = '';
				$accountEmail = $_POST['accountEmail'];
				
				// Check for an account
				$check = $mysqli->query("SELECT 'X' FROM users WHERE userEmail = '".$accountEmail."' AND isActive = 0");
				if ($check->num_rows) {
					$isAccount = 'true';
				}
				
				if ($isAccount == 'true') {
					$qry = "SELECT userId, password, userFirstName, userLastName, hash FROM users WHERE userEmail = '".$accountEmail."'";
					$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
					$row = mysqli_fetch_assoc($results);
					$usrId = $row['userId'];
					$password = decryptIt($row['password']);
					$userFirstName = $row['userFirstName'];
					$userLastName = $row['userLastName'];
					$hashVal = $row['hash'];
					
					// Add Recent Activity
					$activityType = '24';
					$rs_aid = '0';
					$activityTitle = $userFirstName.' ' .$userLastName.' '.$emailResent;
					updateActivity($rs_aid,$usrId,$activityType,$activityTitle);
		
					$subject = $siteName.' New User Account';
						
					$message = '<html><body>';
					$message .= '<h3>'.$subject.'</h3>';
					$message .= '<p>'.$newUserEmail1.'</p>';
					$message .= '<hr>';
					$message .= '<p>'.$newUserEmail2.' '.$password.'</p>';
					$message .= '<p>'.$newUserEmail3.$installUrl.'activate.php?userEmail='.$accountEmail.'&hash='.$hashVal.'</p>';
					$message .= '<hr>';
					$message .= '<p>'.$newUserEmail4.'</p>';
					$message .= '<p>'.$newUserEmail5.' '.$installUrl.'sign-in.php</p>';
					$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
					$message .= '</body></html>';
					
					$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
					$headers .= "Reply-To: ".$siteEmail."\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
					
					mail($accountEmail, $subject, $message, $headers);

					$msgDiv = "<p class=\"text-center text-success\">".$emailResentMsg."</p>";
					// Clear the Form of values
					$_POST['accountEmail'] = '';					
				} else {
					$msgDiv = "<p class=\"text-center text-danger\">".$emailResentError."</p>";
				}
			}
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

		<title><?php echo $set['siteName']; ?> &middot; <?php echo $signInPageTitle; ?></title>

		<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
		<link rel="stylesheet" type="text/css" href="css/custom.css" />
		<link rel="stylesheet" type="text/css" href="css/styles.css" />

		<!--[if lt IE 9]>
			<script src="js/html5shiv.min.js"></script>
			<script src="ja/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="signin-logo">
						<a href="index.php"><img alt="<?php echo $set['siteName']; ?>" src="images/signin_logo.png" /></a>
					</div>

					<div class="signin">
						<p class="text-center">
							<?php if ($set['allowRegistrations'] == '1') { ?>
								<button type="button" id="signin-form" class="btn btn-lg btn-primary btn-icon"><i class="fa fa-lock"></i> <?php echo $accountSignInText; ?></button>
								<button type="button" id="signup-form" class="btn btn-lg btn-warning btn-icon-alt"><?php echo $accountSignUpText; ?> <i class="fa fa-long-arrow-right"></i></button>
							<?php } ?>
						</p>

						<div class="signin-form">
							<?php if ($msgDiv) { echo $msgDiv; } ?>

							<form action="" method="post" class="mt-20">
								<div class="form-group">
									<div class="input-group" data-toggle="tooltip" data-placement="top" title="<?php echo $accountEmailText; ?>">
										<div class="input-group-addon"><i class="fa fa-envelope"></i></div>
										<input type="email" class="form-control" required="required" placeholder="<?php echo $accountEmailText; ?>" name="emailAddy" value="" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group" data-toggle="tooltip" data-placement="top" title="<?php echo $accountPasswordText; ?>">
										<div class="input-group-addon"><i class="fa fa-lock"></i></div>
										<input type="password" class="form-control" required="required" placeholder="<?php echo $accountPasswordText; ?>" name="password" value="" />
									</div>
									<span class="help-block pull-right"><a href="#" id="password-form"><i class="fa fa-lock"></i> <?php echo $resetPasswordText; ?></a></span>
								</div>
								<div class="clearfix"></div>
								<p class="text-center">
									<button type="input" name="submit" value="signIn" class="btn btn-success btn-lg btn-block btn-icon"><i class="fa fa-sign-in"></i> <?php echo $signInText; ?></button>
								</p>
							</form>
						</div>

						<?php if ($set['allowRegistrations'] == '1') { ?>
							<div class="signup-form">
								<form action="" method="post" class="mt-20">
									<div class="form-group" data-toggle="tooltip" data-placement="top" title="<?php echo $emailAddyText; ?>">
										<input type="email" class="form-control" required="required" placeholder="<?php echo $emailAddyText; ?>" name="userEmail" value="" />
										<span class="help-block"><?php echo $emailHelp; ?></span>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group" data-toggle="tooltip" data-placement="top" title="<?php echo $passwordText; ?>">
												<input type="text" class="form-control" required="required" placeholder="<?php echo $passwordText; ?>" name="password" value="" />
												<span class="help-block"><?php echo $newPasswordHelp; ?></span>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group" data-toggle="tooltip" data-placement="top" title="<?php echo $repeatPasswordText; ?>">
												<input type="text" class="form-control" required="required" placeholder="<?php echo $repeatPasswordText; ?>" name="passwordr" value="" />
												<span class="help-block"><?php echo $repeatPasswordHelp; ?></span>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group" data-toggle="tooltip" data-placement="top" title="<?php echo $fisrtNameText; ?>">
												<input type="text" class="form-control" required="required" placeholder="<?php echo $fisrtNameText; ?>" name="userFirstName" value="" />
												<span class="help-block"><?php echo $fisrtNameHelp; ?></span>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group" data-toggle="tooltip" data-placement="top" title="<?php echo $lastNameText; ?>">
												<input type="text" class="form-control" required="required" placeholder="<?php echo $lastNameText; ?>" name="userLastName" value="" />
												<span class="help-block"><?php echo $lastNameHelp; ?></span>
											</div>
										</div>
									</div>
									<div class="row mb-10">
										<div class="col-md-3">
											<img src="includes/captcha.php" data-toggle="tooltip" data-placement="top" title="<?php echo $captchaCodeText; ?>" />
										</div>
										<div class="col-md-9">
											<input type="text" class="form-control" required="required" placeholder="<?php echo $enterCodeText; ?>" maxlength="6" name="captchaanswer" value="" data-toggle="tooltip" data-placement="top" title="<?php echo $enterCodeText; ?>" />
										</div>
									</div>
									<p class="text-center mt-20">
										<input type="hidden" name="noAnswer" />
										<button type="input" name="submit" value="userSignUp" class="btn btn-success btn-lg btn-block btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $createAccountText; ?></button>
									</p>
									<span class="help-block text-center">
										<?php echo $resendActivationText; ?><br />
										<a data-toggle="modal" href="#resendEmail"><i class="fa fa-envelope-o"></i> <?php echo $resendActivationBtn; ?></a>
									</span>
								</form>
							</div>
						<?php } ?>

						<div class="resetpass-form">
							<form action="" method="post" class="mt-20">
								<div class="input-group" data-toggle="tooltip" data-placement="top" title="<?php echo $emailAddyText; ?>">
									<div class="input-group-addon"><i class="fa fa-envelope"></i></div>
									<input type="email" class="form-control" required="required" placeholder="<?php echo $emailAddyText; ?>" name="emailAddy" value="" />
								</div>
								<span class="help-block"><?php echo $emailHelp2; ?></span>
								
								<?php if ($set['allowRegistrations'] == '1') { ?>
									<p class="text-center mt-20">
										<button type="input" name="submit" value="resetPass" class="btn btn-success btn-block btn-lg btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $resetPasswordBtn; ?></button>
									</p>
								<?php } else { ?>
									<div class="row mt-20">
										<div class="col-md-6">
											<button type="input" name="submit" value="resetPass" class="btn btn-success btn-block btn-lg btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $resetPasswordBtn; ?></button>
										</div>
										<div class="col-md-6">
											<button type="button" id="signin-form" class="btn btn-block btn-lg btn-primary btn-icon"><i class="fa fa-lock"></i> <?php echo $accountSignInText; ?></button>
										</div>
									</div>
								<?php } ?>
							</form>
						</div>
					</div>

				</div>
			</div>
		</div>
		
		<?php if ($set['allowRegistrations'] == '1') { ?>
			<div class="modal fade" id="resendEmail" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
							<h4 class="modal-title"><?php echo $resendActivationTitle; ?></h3>
						</div>
						<form action="" method="post">
							<div class="modal-body">
								<p><?php echo $resendActivationText.' '.$resendActivationText2; ?></p>
								<div class="form-group">
									<label for="accountEmail"><?php echo $emailAddyText; ?></label>
									<input type="email" class="form-control" required="required" name="accountEmail" value="" />
									<span class="help-block"><?php echo $emailHelp3; ?></span>
								</div>
							</div>
							<div class="modal-footer">
								<button type="input" name="submit" value="resendEmail" class="btn btn-success btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $resendActivationTitle; ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		<?php } ?>
		
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/includes/sign-in.js"></script>

	</body>
	</html>
<?php } ?>