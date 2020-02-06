<?php
	// Check if install.php is present
	if(is_dir('install')) {
		header("Location: ../install/install.php");
	} else {
		if(!isset($_SESSION)) session_start();

		if (!isset($_SESSION['rs']['adminId'])) {
			header ('Location: ../index.php');
			exit;
		}

		// Access DB Info
		include('../config.php');

		// Get Settings Data
		include ('../includes/settings.php');
		$set = mysqli_fetch_assoc($setRes);

		// Include Functions
		include('../includes/functions.php');

		// Include Sessions & Localizations
		include('includes/sessions.php');
		
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
		if (isset($_GET['action']) && $_GET['action'] == 'myProfile') {					$page = 'myProfile';
		} else if (isset($_GET['action']) && $_GET['action'] == 'leasedTenants') {		$page = 'leasedTenants';
		} else if (isset($_GET['action']) && $_GET['action'] == 'unleasedTenants') {	$page = 'unleasedTenants';
		} else if (isset($_GET['action']) && $_GET['action'] == 'archivedTenants') {	$page = 'archivedTenants';
		} else if (isset($_GET['action']) && $_GET['action'] == 'newTenant') {			$page = 'newTenant';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewTenant') {			$page = 'viewTenant';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewDocument') {		$page = 'viewDocument';
		} else if (isset($_GET['action']) && $_GET['action'] == 'leasedProperties') {	$page = 'leasedProperties';
		} else if (isset($_GET['action']) && $_GET['action'] == 'unleasedProperties') {	$page = 'unleasedProperties';
		} else if (isset($_GET['action']) && $_GET['action'] == 'newProperty') {		$page = 'newProperty';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewProperty') {		$page = 'viewProperty';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewPayments') {		$page = 'viewPayments';
		} else if (isset($_GET['action']) && $_GET['action'] == 'paymentDetail') {		$page = 'paymentDetail';
		} else if (isset($_GET['action']) && $_GET['action'] == 'receipt') {			$page = 'receipt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'propertyLeases') {		$page = 'propertyLeases';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewLease') {			$page = 'viewLease';
		} else if (isset($_GET['action']) && $_GET['action'] == 'leaseProperty') {		$page = 'leaseProperty';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewUploads') {		$page = 'viewUploads';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewFile') {			$page = 'viewFile';
		} else if (isset($_GET['action']) && $_GET['action'] == 'activeRequests') {		$page = 'activeRequests';
		} else if (isset($_GET['action']) && $_GET['action'] == 'inactiveRequests') {	$page = 'inactiveRequests';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewRequest') {		$page = 'viewRequest';
		} else if (isset($_GET['action']) && $_GET['action'] == 'workOrder') {			$page = 'workOrder';
		} else if (isset($_GET['action']) && $_GET['action'] == 'newRequest') {			$page = 'newRequest';
		} else if (isset($_GET['action']) && $_GET['action'] == 'adminAuths') {			$page = 'adminAuths';
		} else if (isset($_GET['action']) && $_GET['action'] == 'adminAccounts') {		$page = 'adminAccounts';
		} else if (isset($_GET['action']) && $_GET['action'] == 'newAdmin') {			$page = 'newAdmin';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewAdmin') {			$page = 'viewAdmin';
		} else if (isset($_GET['action']) && $_GET['action'] == 'siteAlerts') {			$page = 'siteAlerts';
		} else if (isset($_GET['action']) && $_GET['action'] == 'userReports') {		$page = 'userReports';
		} else if (isset($_GET['action']) && $_GET['action'] == 'propertyReports') {	$page = 'propertyReports';
		} else if (isset($_GET['action']) && $_GET['action'] == 'accountingReports') {	$page = 'accountingReports';
		} else if (isset($_GET['action']) && $_GET['action'] == 'serviceReports') {		$page = 'serviceReports';
		} else if (isset($_GET['action']) && $_GET['action'] == 'tenantRpt') {			$page = 'tenantRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'accountingRpt') {		$page = 'accountingRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'propertyRpt') {		$page = 'propertyRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'leaseRpt') {			$page = 'leaseRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'servReqRpt') {			$page = 'servReqRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'servCostRpt') {		$page = 'servCostRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'adminRpt') {			$page = 'adminRpt';
		} else if (isset($_GET['action']) && $_GET['action'] == 'forms') { 				$page = 'forms';
		} else if (isset($_GET['action']) && $_GET['action'] == 'viewTemplate') { 		$page = 'viewTemplate';
		} else if (isset($_GET['action']) && $_GET['action'] == 'siteContent') { 		$page = 'siteContent';
		} else if (isset($_GET['action']) && $_GET['action'] == 'siteSettings') { 		$page = 'siteSettings';
		} else if (isset($_GET['action']) && $_GET['action'] == 'socialNetworks') {		$page = 'socialNetworks';
		} else if (isset($_GET['action']) && $_GET['action'] == 'uploadSettings') {		$page = 'uploadSettings';
		} else if (isset($_GET['action']) && $_GET['action'] == 'paymentSettings') { 	$page = 'paymentSettings';
		} else if (isset($_GET['action']) && $_GET['action'] == 'servReqSettings') { 	$page = 'servReqSettings';
		} else if (isset($_GET['action']) && $_GET['action'] == 'sliderSettings') { 	$page = 'sliderSettings';
		} else if (isset($_GET['action']) && $_GET['action'] == 'importExport') { 		$page = 'importExport';
		} else if (isset($_GET['action']) && $_GET['action'] == 'siteLogs') { 			$page = 'siteLogs';
		} else {																		$page = 'dashboard';}

		if (file_exists('pages/'.$page.'.php')) {
			// Load the Page
			include('pages/'.$page.'.php');
		} else {
			$pageTitle = $pageNotFoundHeader;

			if (($page != 'receipt') && ($page != 'workOrder')) {
				include('includes/header.php');
			}

			// Else Display an Error
			echo '
					<div class="container page_block noTopBorder">
						<hr class="mt-0 mb-0" />
						<h3>'.$pageNotFoundHeader.'</h3>
						<div class="alertMsg warning mb-20">
							<div class="msgIcon pull-left">
								<i class="fa fa-warning"></i>
							</div>
							'.$pageNotFoundQuip.'
						</div>
					</div>
				';
		}

		if (($page != 'receipt') && ($page != 'workOrder')) {
			include('includes/footer.php');
		}
	}
?>