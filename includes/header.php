<?php
	// If you use an SSL Certificate - HTTPS://
	// Uncomment (remove the double slashes) from lines 5 - 9
	// ************************************************************************
	//if (!isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) != "on") {
	//	$url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	//	header("Location: $url");
	//	exit;
	//}
	
	// Set the Active State on the Navigation
	$homeNav = $propNav = $aboutNav = $contactNav = $userNav = $manageNav = '';
	if (isset($homePage)) { $homeNav = 'active'; } else { $homeNav = ''; }
	if (isset($propPage)) { $propNav = 'active'; } else { $propNav = ''; }
	if (isset($aboutPage)) { $aboutNav = 'active'; } else { $aboutNav = ''; }
	if (isset($contactPage)) { $contactNav = 'active'; } else { $contactNav = ''; }
	if (isset($userPage)) { $userNav = 'active'; } else { $userNav = ''; }
	if (isset($managePage)) { $manageNav = 'active'; } else { $manageNav = ''; }
	
	// Get Pictures Directory
	$propPicsPath = $set['propPicsPath'];
	
	// Get Documents Directory
	$docUploadPath = $set['uploadPath'];
	
	// Get the Avatar Directory
	$avatarDir = $set['avatarFolder'];
	
	// Get Social Network Icons & Links
	if (!empty($set['facebook'])) {
		$facebook = '<a href="'.clean($set['facebook']).'" class="facebook" data-toggle="tooltip" data-placement="bottom" title="'.$facebookText.'"><i class="fa fa-facebook"></i></a>';
	} else { $facebook = ''; }
	if (!empty($set['google'])) {
		$google = '<a href="'.clean($set['google']).'" class="google" data-toggle="tooltip" data-placement="bottom" title="'.$googleText.'"><i class="fa fa-google-plus"></i></a>';
	} else { $google = ''; }
	if (!empty($set['linkedin'])) {
		$linkedin = '<a href="'.clean($set['linkedin']).'" class="linkedin" data-toggle="tooltip" data-placement="bottom" title="'.$linkedinText.'"><i class="fa fa-linkedin"></i></a>';
	} else { $linkedin = ''; }
	if (!empty($set['pinterest'])) {
		$pinterest = '<a href="'.clean($set['pinterest']).'" class="pinterest" data-toggle="tooltip" data-placement="bottom" title="'.$pinterestText.'"><i class="fa fa-pinterest"></i></a>';
	} else { $pinterest = ''; }
	if (!empty($set['twitter'])) {
		$twitter = '<a href="'.clean($set['twitter']).'" class="twitter" data-toggle="tooltip" data-placement="bottom" title="'.$twitterText.'"><i class="fa fa-twitter"></i></a>';
	} else { $twitter = ''; }
	if (!empty($set['youtube'])) {
		$youtube = '<a href="'.clean($set['youtube']).'" class="youtube" data-toggle="tooltip" data-placement="bottom" title="'.$youtubeText.'"><i class="fa fa-youtube"></i></a>';
	} else { $youtube = ''; }
	
	// Contact Request Form
	if (isset($_POST['submit']) && $_POST['submit'] == 'contactReq') {
		// User Validations
		if($_POST['crFirstName'] == '') {
			$msgBox = alertBox($fnameReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['crLastName'] == '') {
			$msgBox = alertBox($lnameReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['crEmail'] == '') {
			$msgBox = alertBox($validEmailReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['crMessage'] == '') {
			$msgBox = alertBox($msgTextReqErr, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['crCaptcha'] == "") {
            $msgBox = alertBox($captchaReq, "<i class='fa fa-times-circle'></i>", "danger");
		// Black Hole Trap to help reduce bot registrations
		} else if($_POST['none'] != '') {
			$msgBox = alertBox($msgSendErr, "<i class='fa fa-times-circle'></i>", "danger");
			$_POST['crFirstName'] = $_POST['crLastName'] = $_POST['crEmail'] = $_POST['crPhone'] = $_POST['crMessage'] = '';
		} else {
			if(strtolower($_POST['crCaptcha']) == $_SESSION['thecode']) {
				$crFirstName = htmlspecialchars($_POST['crFirstName']);
				$crLastName = htmlspecialchars($_POST['crLastName']);
				$crEmail = htmlspecialchars($_POST['crEmail']);
				$crPhone = htmlspecialchars($_POST['crPhone']);
				$crMessage = allowedHTML(htmlspecialchars($_POST['crMessage']));
				
				// Add Recent Activity
				$activityType = '15';
				if ($rs_adminId != '') { $rs_aid = $rs_adminId; } else { $rs_aid = '0'; }
				if ($rs_userId != '') { $rs_uid = $rs_userId; } else { $rs_uid = '0'; }
				$activityTitle = $sendMsgSubject.' '.$crFirstName.' '.$crLastName.' ('.$crEmail.')';
				updateActivity($rs_aid,$rs_uid,$activityType,$activityTitle);

				// Send out the email in HTML
				$installUrl = $set['installUrl'];
				$siteName = $set['siteName'];
				$siteEmail = $set['siteEmail'];

				$subject = $sendMsgEmail1.' '.$siteName;
				$message = '<html><body>';
				$message .= '<h3>'.$subject.'</h3>';
				$message .= '<hr />';
				$message .= '<p>'.$sendMsgEmail2.' '.$crFirstName.' '.$crLastName.'<br />';
				$message .= $sendMsgEmail3.' '.$crEmail.'<br />';
				$message .= $sendMsgEmail4.' '.$crPhone.'</p>';
				$message .= '<p>'.$sendMsgEmail5.'<br />'.nl2br($crMessage).'</p>';
				$message .= '<hr />';
				$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
				$message .= '</body></html>';

				$headers = "From: ".$crFirstName." ".$crLastName." <".$crEmail.">\r\n";
				$headers .= "Reply-To: ".$crEmail."\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

				if (mail($siteEmail, $subject, $message, $headers)) {
					$msgBox = alertBox($sendMsgEmailSent, "<i class='fa fa-check-square'></i>", "success");
					$_POST['crFirstName'] = $_POST['crLastName'] = $_POST['crEmail'] = $_POST['crPhone'] = $_POST['crMessage'] = '';
				}
			} else {
				$msgBox = alertBox($sendMsgEmailErr, "<i class='fa fa-times-circle'></i>", "danger");
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

	<title><?php echo $set['siteName'].' &middot; '.$pageTitle; ?></title>

	<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
	<?php if (isset($addCss)) { echo $addCss; } ?>
	<link rel="stylesheet" type="text/css" href="css/custom.css" />
	<link rel="stylesheet" type="text/css" href="css/styles.css" />

	<!--[if lt IE 9]>
		<script src="js/html5shiv.min.js"></script>
		<script src="js/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<div class="container page_block noTopBorder noBotBorder">
		<div class="header-cont">
			<div class="row">
				<div class="col-md-6">
					<div class="contact-text">
						<?php echo $needHelpText; ?> <i class="fa fa-phone"></i> <?php echo $set['contactPhone']; ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="header-social-links">
						<?php
							echo $facebook;
							echo $google;
							echo $linkedin;
							echo $pinterest;
							echo $twitter;
							echo $youtube;
						?>
					</div>
				</div>
			</div>

			<hr class="mt-10 mb-10" />

			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
							<span class="sr-only"><?php echo $toggleNavText; ?></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="index.php"><img src="images/logo.png" /></a>
					</div>

					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav navbar-right">
							<li class="<?php echo $homeNav; ?>"><a href="index.php"><?php echo $homeNavLink; ?></a></li>
							<li class="<?php echo $propNav; ?> dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $propNavLink; ?> <i class="fa fa-angle-down"></i></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="available-properties.php"><?php echo $availPropNavLink; ?></a></li>
									<li><a href="rental-application.php"><?php echo $rentAppNavLink; ?></a></li>
								</ul>
							</li>
							<li class="<?php echo $aboutNav; ?>"><a href="about-us.php"><?php echo $aboutUsNavLink; ?></a></li>
							<li class="<?php echo $contactNav; ?>"><a href="contact-us.php"><?php echo $contactUsNavLink; ?></a></li>
							<?php if ($rs_userId != '') { ?>
								<li class="<?php echo $userNav; ?> dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $myAccNavLink; ?> <i class="fa fa-angle-down"></i></a>
									<ul class="dropdown-menu" role="menu">
										<li><a href="page.php"><?php echo $dashboardNavLink; ?></a></li>
										<li><a href="page.php?page=myProfile"><?php echo $myProfileNavLink; ?></a></li>
									</ul>
								</li>
							<?php } ?>
							<?php if ($rs_adminId != '') { ?>
								<li class="<?php echo $manageNav; ?>"><a href="admin/index.php?action=dashboard"><?php echo $manageNavLink; ?></a></li>
							<?php } ?>
							<?php if (($rs_adminId != '') || ($rs_userId != '')) { ?>
								<li><a data-toggle="modal" href="#signOut"><?php echo $signOutNavLink; ?></a></li>
							<?php
								} else {
									if ($set['allowRegistrations'] == '1') {
							?>
									<li><a href="sign-in.php"><?php echo $signInUpNavLink; ?></a></li>
							<?php } else { ?>
									<li><a href="sign-in.php"><?php echo $signInNavLink; ?></a></li>
							<?php
									}
								}	
							?>
						</ul>
					</div>
				</div>
			</nav>
		</div>
	</div>
	
	<?php if (($rs_adminId != '') || ($rs_userId != '')) { ?>
	<div class="modal fade" id="signOut" tabindex="-1" role="dialog" aria-labelledby="signOutLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<?php if ($rs_adminId != '') { ?>
						<p class="lead"><?php echo $rs_adminName; ?>, <?php echo $signOutConf; ?></p>
					<?php } else { ?>
						<p class="lead"><?php echo $rs_userFull; ?>, <?php echo $signOutConf; ?></p>
					<?php } ?>
				</div>
				<div class="modal-footer">
					<a href="index.php?action=logout" class="btn btn-success btn-icon-alt"><?php echo $signOutNavLink; ?> <i class="fa fa-sign-out"></i></a>
					<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle"></i> <?php echo $cancelBtn; ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>