<?php
	// Require the PayPal Class
	require_once('includes/paypal.php');

	$paypal = new paypalPaymnents;
	$paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';

	$infoBox = $issuccess = $iscancel = '';
	$pageTitle = $accessErrorHeader;

	if ($set['enablePaypal'] == '1') {
		// Get Data
		$qry = "SELECT
					users.propertyId,
					users.leaseId,
					properties.propertyName
				FROM
					users
					LEFT JOIN properties ON users.propertyId = properties.userId
				WHERE
					users.userId = ".$rs_userId." AND
					users.leaseId = ".$rs_leaseId;
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
		$row = mysqli_fetch_assoc($res);

		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'success':
					// Payment was successful
					$msgBox = alertBox($thankYouConf." ".$set['siteName'].".", "<i class='fa fa-check-square'></i>", "success");
					$issuccess = 'true';
					$pageTitle = $cmpltPageTitle1;
				break;
				case 'cancel':
					// Payment was cancelled
					$msgBox = alertBox($cancelledConf1." ".$set['siteName']." ".$cancelledConf2, "<i class='fa fa-warning'></i>", "warning");
					$iscancel = 'true';
					$pageTitle = $cmpltPageTitle2
				break;
				case 'ipn':
					// PayPal is calling page for IPN validation
					if ($paypal->validate_ipn()) {
						if (isset($_GET['amt'])) { $amtPaid = $_GET['amt']; } else { $amtPaid = null; }

						// Send out the email in HTML
						$installUrl = $set['installUrl'];
						$siteName = $set['siteName'];
						$siteEmail = $set['siteEmail'];

						$subject = $newPaymentEmailSubject.' '.clean($row['propertyName']);

						$message = '<html><body>';
						$message .= '<h3>'.$subject.'</h3>';
						$message .= '<hr>';
						$message .= '<p>'.$newPaymentEmail1.' '.$rs_userFull.'</p>';
						$message .= '<p>'.$newPaymentEmail2.' '.clean($row['propertyName']).'</p>';
						$message .= '<p>'.$newPaymentEmail3.' '.$amtPaid.'</p>';
						$message .= '<p>'.$newPaymentEmail4.' '.date('m/d/Y').' at '.date('g:i A').'</p>';
						$message .= '<hr>';
						$message .= '<p>'.$emailTankYouTxt.'<br>'.$siteName.'</p>';
						$message .= '</body></html>';

						$headers = "From: ".$siteName." <".$siteEmail.">\r\n";
						$headers .= "Reply-To: ".$siteEmail."\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

						mail($siteEmail, $subject, $pay_rec_tmpl, $headers);
						break;
					}
				break;
			}
		} else {
			// User tried to access the page directly
			$msgBox = alertBox($directAccessError, "<i class='fa fa-times-circle'></i>", "danger");
			$infoBox = '<p class="mb-20">'.$directAccessError1.'</p>';

			// Add Recent Activity
			$activityType = '21';
			$rs_aid = '0';
			$activityTitle = $rs_userFull.' '.$directAccessActivity;
			updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
		}
	} else {
		// Add Recent Activity
		$activityType = '21';
		$rs_aid = '0';
		$activityTitle = $rs_userFull.' '.$directAccessActivity;
		updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
	}

	$propPage = 'true';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php if ($rs_leaseId != '0' && $set['enablePaypal'] == '1') { ?>
			<h3><?php echo $pageTitle; ?></h3>
			<?php
				if ($msgBox) { echo $msgBox; }
				if ($infoBox) { echo $infoBox; }
			?>

			<?php
				if (!empty($issuccess) && $issuccess == 'true') {
					// Add Recent Activity
					$activityType = '22';
					$rs_aid = '0';
					$activityTitle = $rs_userFull.' '.$compltPayActivity;
					updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
			?>
					<p class="mb-20"><?php echo $thankYouMsg.' '.$set['siteName']; ?>. <?php$thankYouMsg1; ?></p>
			<?php
				}
				if (!empty($iscancel) && $iscancel == 'true') {
					// Add Recent Activity
					$activityType = '22';
					$rs_aid = '0';
					$activityTitle = $compltPayActivity1.' '.$rs_userFull;
					updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
			?>
					<p class="mb-20"><?php echo $cancelledMsg; ?></p>
			<?php
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
		<?php } ?>
	</div>