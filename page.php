<?php
	// Check if install.php is present
	if(is_dir('install')) {
		header("Location: install/install.php");
	} else {
		if(!isset($_SESSION)) session_start();
		
		if (!isset($_SESSION['rs']['userId'])) {
			header ('Location: index.php');
			exit;
		}

		// Access DB Info
		include('config.php');

		// Get Settings Data
		include ('includes/settings.php');
		$set = mysqli_fetch_assoc($setRes);

		// Include Functions
		include('includes/functions.php');

		// Include Sessions & Localizations
		include('includes/sessions.php');
		
		// Get the Avatar Directory
		$avatarDir = $set['avatarFolder'];
		
		// Set Week Day Name
		$theDay = date('l');
		switch ($theDay) {
			case 'Sunday':		$dayName = $sunText;	break;
			case 'Monday':		$dayName = $monText;	break;
			case 'Tuesday':		$dayName = $tueText;	break;
			case 'Wednesday':	$dayName = $wedText;	break;
			case 'Thursday':	$dayName = $thuText;	break;
			case 'Friday':		$dayName = $friText;	break;
			case 'Saturday':	$dayName = $satText;	break;
		}
		
		// Set Month Name
		$theMonth = date('F');
		switch ($theMonth) {
			case 'January':		$monthName = $janText;	break;
			case 'February':	$monthName = $febText;	break;
			case 'March':		$monthName = $marText;	break;
			case 'April':		$monthName = $aprText;	break;
			case 'May':			$monthName = $mayText;	break;
			case 'June':		$monthName = $junText;	break;
			case 'July':		$monthName = $julText;	break;
			case 'August':		$monthName = $augText;	break;
			case 'September':	$monthName = $septText;	break;
			case 'October':		$monthName = $octText;	break;
			case 'November':	$monthName = $novText;	break;
			case 'December':	$monthName = $decText;	break;
		}

		// Link to the Page
		if (isset($_GET['page']) && $_GET['page'] == 'myProfile') {					$page = 'myProfile';
		} else if (isset($_GET['page']) && $_GET['page'] == 'myProperty') {			$page = 'myProperty';
		} else if (isset($_GET['page']) && $_GET['page'] == 'viewFile') {			$page = 'viewFile';
		} else if (isset($_GET['page']) && $_GET['page'] == 'serviceRequests') {	$page = 'serviceRequests';
		} else if (isset($_GET['page']) && $_GET['page'] == 'viewRequest') {		$page = 'viewRequest';
		} else if (isset($_GET['page']) && $_GET['page'] == 'newRequest') {			$page = 'newRequest';
		} else if (isset($_GET['page']) && $_GET['page'] == 'paymentHistory') {		$page = 'paymentHistory';
		} else if (isset($_GET['page']) && $_GET['page'] == 'viewPayments') {		$page = 'viewPayments';
		} else if (isset($_GET['page']) && $_GET['page'] == 'newPayment') {			$page = 'newPayment';
		} else if (isset($_GET['page']) && $_GET['page'] == 'completed') {			$page = 'completed';
		} else if (isset($_GET['page']) && $_GET['page'] == 'myDocuments') {		$page = 'myDocuments';
		} else if (isset($_GET['page']) && $_GET['page'] == 'viewDocument') {		$page = 'viewDocument';
		} else if (isset($_GET['page']) && $_GET['page'] == 'receipt') {			$page = 'receipt';
		} else {																	$page = 'dashboard';}

		if (file_exists('pages/'.$page.'.php')) {
			// Load the Page
			include('pages/'.$page.'.php');
		} else {
			$pageTitle = $pageNotFoundHeader;

			if ($page != 'receipt') {
				include('includes/user_header.php');
			}

			// Else Display an Error
			echo '
					<div class="container page_block noTopBorder">
						<hr class="mt-0 mb-0" />
						<h3>'.$pageNotFoundHeader.'</h3>
						<div class="alertMsg warning">
							<div class="msgIcon pull-left">
								<i class="fa fa-warning"></i>
							</div>
							'.$pageNotFoundQuip.'
						</div>
					</div>
				';
		}

		if ($page != 'receipt') {
			include('includes/user_footer.php');
		}
	}
?>