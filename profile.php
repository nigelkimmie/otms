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

		$profile = $_GET['profile'];
		$adminsName = preg_replace('/-/', ' ', $profile);

		// Personal Contact Request Form
		if (isset($_POST['submit']) && $_POST['submit'] == 'sendDirect') {
			// User Validations
			if($_POST['contactFirst'] == '') {
				$msgBox = alertBox($fnameReq, "<i class='fa fa-times-circle'></i>", "danger");
			} else if($_POST['contactLast'] == '') {
				$msgBox = alertBox($lnameReq, "<i class='fa fa-times-circle'></i>", "danger");
			} else if($_POST['contactEmail'] == '') {
				$msgBox = alertBox($validEmailReq, "<i class='fa fa-times-circle'></i>", "danger");
			} else if($_POST['contactMessage'] == '') {
				$msgBox = alertBox("Please enter your Message.", "<i class='fa fa-times-circle'></i>", "danger");
			} else if($_POST['contactCaptcha'] == "") {
				$msgBox = alertBox($captchaReq, "<i class='fa fa-times-circle'></i>", "danger");
			// Black Hole Trap to help reduce bot registrations
			} else if($_POST['contactNone'] != '') {
				$msgBox = alertBox($msgSendErr, "<i class='fa fa-times-circle'></i>", "danger");
				$_POST['contactFirst'] = $_POST['contactLast'] = $_POST['contactEmail'] = $_POST['contactPhone'] = $_POST['contactMessage'] = '';
			} else {
				if(strtolower($_POST['contactCaptcha']) == $_SESSION['thecode']) {
					$adminsId = $_POST['adminsId'];

					// Get the Admin's email address
					$sql = "SELECT adminEmail FROM admins WHERE adminId = ".$adminsId;
					$result = mysqli_query($mysqli, $sql) or die('-1'.mysqli_error());
					$r = mysqli_fetch_assoc($result);
					$adminsEmail = $r['adminEmail'];

					$contactFirst = htmlspecialchars($_POST['contactFirst']);
					$contactLast = htmlspecialchars($_POST['contactLast']);
					$contactEmail = htmlspecialchars($_POST['contactEmail']);
					$contactPhone = htmlspecialchars($_POST['contactPhone']);
					$contactMessage = htmlspecialchars($_POST['contactMessage']);

					// Send out the email in HTML
					$installUrl = $set['installUrl'];
					$siteName = $set['siteName'];

					$subject = $directMsgFormSubject.' '.$contactFirst.' '.$contactLast;

					$message = '<html><body>';
					$message .= '<h3>'.$subject.'</h3>';
					$message .= '<hr />';
					$message .= '<p>'.$sendMsgEmail2.': '.$contactFirst.' '.$contactLast.'<br />';
					$message .= $sendMsgEmail3.' '.$contactEmail.'<br />';
					$message .= $sendMsgEmail4.' '.$contactPhone.'</p>';
					$message .= '<p>'.$sendMsgEmail5.'<br />'.nl2br($contactMessage).'</p>';
					$message .= '<hr />';
					$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
					$message .= '</body></html>';

					$headers = "From: ".$contactFirst." ".$contactLast." <".$contactEmail.">\r\n";
					$headers .= "Reply-To: ".$contactEmail."\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

					if (mail($adminsEmail, $subject, $message, $headers)) {
						$msgBox = alertBox($directMsgForm1, "<i class='fa fa-check-square'></i>", "success");
						$_POST['contactFirst'] = $_POST['contactLast'] = $_POST['contactEmail'] = $_POST['contactPhone'] = $_POST['contactMessage'] = '';
					}
				} else {
					$msgBox = alertBox($sendMsgEmailErr, "<i class='fa fa-times-circle'></i>", "danger");
				}
			}
		}

		// Get Data
		$sql = "SELECT * FROM admins WHERE adminName = '".$adminsName."'";
		$result = mysqli_query($mysqli, $sql) or die('-1' . mysqli_error());
		$row = mysqli_fetch_assoc($result);

		// Get Social Network Icons & Links
		if (!empty($row['facebook'])) {
			$pfacebook = '<li data-toggle="tooltip" data-placement="top" title="'.$facebookText.'"><a href="'.clean($row['facebook']).'"><i class="fa fa-facebook"></i></a></li>';
		} else { $pfacebook = ''; }
		if (!empty($row['google'])) {
			$pgoogle = '<li data-toggle="tooltip" data-placement="top" title="'.$googleText.'"><a href="'.clean($row['google']).'"><i class="fa fa-google"></i></a></li>';
		} else { $pgoogle = ''; }
		if (!empty($row['linkedin'])) {
			$plinkedin = '<li data-toggle="tooltip" data-placement="top" title="'.$linkedinText.'"><a href="'.clean($row['linkedin']).'"><i class="fa fa-linkedin"></i></a></li>';
		} else { $plinkedin = ''; }
		if (!empty($row['pinterest'])) {
			$ppinterest = '<li data-toggle="tooltip" data-placement="top" title="'.$pinterestText.'"><a href="'.clean($row['pinterest']).'"><i class="fa fa-pinterest"></i></a></li>';
		} else { $ppinterest = ''; }
		if (!empty($row['twitter'])) {
			$ptwitter = '<li data-toggle="tooltip" data-placement="top" title="'.$twitterText.'"><a href="'.clean($row['twitter']).'"><i class="fa fa-twitter"></i></a></li>';
		} else { $ptwitter = ''; }
		if (!empty($row['youtube'])) {
			$pyoutube = '<li data-toggle="tooltip" data-placement="top" title="'.$youtubeText.'"><a href="'.clean($row['youtube']).'"><i class="fa fa-youtube"></i></a></li>';
		} else { $pyoutube = ''; }

		$aboutPage = 'true';
		$pageTitle = 'View Profile';

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<hr class="mt-0 mb-0" />

			<div class="row mt-20">
				<div class="col-md-4">
					<div class="profileBox">
						<div class="cover">
							<div class="profilePic">
								<img src="<?php echo $avatarDir.'/'.$row['adminPhoto']; ?>" class="publicPic" />
							</div>
						</div>

						<div class="profileBody border">
							<h1><?php echo $adminsName; ?></h1>
							<h4 class="mt-10">
								<?php echo clean($row['adminRole']); ?><br />
								<small><?php echo $memberSinceText; ?> <?php echo dateFormat($row['createDate']); ?></small>
							</h3>
							<ul class="socialLinks">
								<?php
									echo $pfacebook;
									echo $pgoogle;
									echo $plinkedin;
									echo $ppinterest;
									echo $ptwitter;
									echo $pyoutube;
								?>
							</ul>
						</div>
					</div>
				</div>

				<div class="col-md-8">
					<div class="well well-quote">
						<div class="avatar">
							<img src="<?php echo $avatarDir.'/'.$row['adminAvatar']; ?>">
						</div>
						<p class="lead text-center"><i class="fa fa-quote-left icon-quote"></i> <?php echo nl2br(htmlspecialchars_decode($row['personalQuip'])); ?> <i class="fa fa-quote-right icon-quote"></i></p>
						<div class="clearfix"></div>
					</div>

					<h3><?php echo $sendText.' '.$adminsName.' '.$directMsgText; ?></h3>

					<?php if ($msgBox) { echo $msgBox; } ?>

					<form action="" method="post" class="mb-20">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<input type="text" class="form-control" required="" name="contactFirst" data-placeholder="<?php echo $contUsFormFirstName; ?>" value="<?php echo isset($_POST['contactFirst']) ? $_POST['contactFirst'] : ''; ?>" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<input type="text" class="form-control" required="" name="contactLast" data-placeholder="<?php echo $contUsFormLastName; ?>" value="<?php echo isset($_POST['contactLast']) ? $_POST['contactLast'] : ''; ?>" />
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<input type="email" class="form-control" required="" name="contactEmail" data-placeholder="<?php echo $emailAddyText; ?>" value="<?php echo isset($_POST['contactEmail']) ? $_POST['contactEmail'] : ''; ?>" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<input type="text" class="form-control" name="contactPhone" data-placeholder="<?php echo $contUsFormPhone; ?>" value="<?php echo isset($_POST['contactPhone']) ? $_POST['contactPhone'] : ''; ?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<textarea class="form-control" name="contactMessage" required="" data-placeholder="<?php echo $enterMsgText; ?>" rows="3"><?php echo isset($_POST['contactMessage']) ? $_POST['contactMessage'] : ''; ?></textarea>
						</div>
						<div class="row">
							<div class="col-md-3">
								<p><img src="includes/captcha.php" data-toggle="tooltip" data-placement="top" title="<?php echo $captchaCodeText; ?>" /></p>
							</div>
							<div class="col-md-9">
								<div class="form-group">
									<input type="text" class="form-control" required="" name="contactCaptcha" data-placeholder="<?php echo $enterCodeText; ?>" />
								</div>
							</div>
						</div>
						<input type="hidden" name="contactNone" />
						<input type="hidden" name="adminsId" value="<?php echo $row['adminId']; ?>" />
						<button type="input" name="submit" value="sendDirect" class="btn btn-success btn-icon"><i class="fa fa-envelope-o"></i> <?php echo $sendDirectMsgBtn; ?></button>
					</form>

				</div>
			</div>
		</div>
<?php
		include('includes/footer.php');
	}
?>