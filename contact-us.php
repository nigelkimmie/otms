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
		
		// Get Home Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 5";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-1' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);
		
		$contactPage = 'true';
		$pageTitle = $contactUsNavLink;

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<div class="row header-bar">
				<iframe width="100%" height="340" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://<?php echo $set['contactUsMap']; ?>"></iframe>
			</div>
			
			<?php if ($msgBox) { echo $msgBox; } ?>
			
			<?php if ($pc['pageContent'] != '') { ?>
				<div class="intro-text">
					<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
				</div>
			<?php } ?>
		</div>
<?php
		include('includes/footer.php');
	}
?>