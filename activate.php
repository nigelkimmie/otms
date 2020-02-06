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
		
		$activeAccount = '';
		$nowActive = '';
		
		if((isset($_GET['userEmail']) && !empty($_GET['userEmail'])) && (isset($_GET['hash']) && !empty($_GET['hash']))) {
			// Set some variables
			$userEmail = $mysqli->real_escape_string($_GET['userEmail']);
			$hash = $mysqli->real_escape_string($_GET['hash']);

			// Check to see if there is an account that matches the link
			$check1 = $mysqli->query("SELECT
										userEmail,
										hash,
										isActive
									FROM
										users
									WHERE
										userEmail = '".$userEmail."' AND
										hash = '".$hash."' AND
										isActive = 0
			");
			$match = mysqli_num_rows($check1);
			
			// Check if account has all ready been activated
			$check2 = $mysqli->query("SELECT 'X' FROM users WHERE userEmail = '".$userEmail."' AND hash = '".$hash."' AND isActive = 1");
			if ($check2->num_rows) {
				$activeAccount = 'true';
			}

			// Match found, update the User's account to active
			if ($match > 0) {
				$isActive = '1';

				$stmt = $mysqli->prepare("
									UPDATE
										users
									SET
										isActive = ?
									WHERE
										userEmail = ?");
				$stmt->bind_param('ss',
								   $isActive,
								   $userEmail);
				$stmt->execute();
				$nowActive = 'true';
				$stmt->close();
				
				$qry = "SELECT userId, userFirstName, userLastName FROM users WHERE userEmail = '".$userEmail."' AND hash = '".$hash."'";
				$results = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());
				$row = mysqli_fetch_assoc($results);
				$theUserId = $row['userId'];
				$userFirstName = $row['userFirstName'];
				$userLastName = $row['userLastName'];
				
				// Add Recent Activity
				$activityType = '24';
				$rs_aid = '0';
				$activityTitle = $userFirstName.' ' .$userLastName.' '.$activatedUsrAccText;
				updateActivity($rs_aid,$theUserId,$activityType,$activityTitle);
			}
		} else {
			// Add Recent Activity
			$activityType = '21';
			$rs_aid = $rs_uid = '0';
			$activityTitle = $activatePageAccessErr;
			updateActivity($rs_aid,$rs_uid,$activityType,$activityTitle);
		}
		
		$pageTitle = $activatePageTitle;

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<hr class="mt-0" />
			<?php
				// The account has been activated - show a Signin button
				if ($nowActive != '') {
			?>
					<h3><?php echo $accActivatedTitle; ?></h3>

					<p class="lead"><?php echo $accActivated1; ?></p>
					<div class="alertMsg info mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-check"></i>
						</div>
						<?php echo $accActivated2; ?>
					</div>
					<a href="sign-in.php" class="btn btn-success btn-icon mb-20"><i class="fa fa-sign-in"></i> <?php echo $signInBtn; ?></a>
			<?php
				// An account match was found and has all ready been activated
				} else if ($activeAccount != '') {
			?>
					<h3><?php echo $accActiveTitle; ?></h3>
					
					<p class="lead"><?php echo $accActive; ?></p>
					<div class="alertMsg info mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-check"></i>
						</div>
						<?php echo $getStarted; ?>
					</div>
					<a href="sign-in.php" class="btn btn-success btn-icon mb-20"><i class="fa fa-sign-in"></i><?php echo $signInBtn2; ?></a>
			<?php
				// An account match was not found/or the
				// Client tried to directly access this page
				} else {
			?>
					<h3><?php echo $directAccessError; ?></h3>

					<div class="alertMsg warning">
						<div class="msgIcon pull-left">
							<i class="fa fa-warning"></i>
						</div>
						<?php echo $directAccessError2; ?>
					</div>
					<p class="lead"><?php echo $directAccessError3; ?></p>
			<?php } ?>
		</div>

<?php
		include('includes/footer.php');
	}
?>