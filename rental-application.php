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
		
		// Get Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 6";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-1' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);
		
		$propPage = 'true';
		$pageTitle = $rentAppNavLink;

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<hr class="mt-0 mb-0" />
			
			<?php if ($msgBox) { echo $msgBox; } ?>
			
			<h3><?php echo $interestedText; ?></h3>
			
			<?php if ($pc['pageContent'] != '') { ?>
				<div class="intro-text">
					<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
				</div>
			<?php } ?>
			
			<h3><?php echo $dwnldAppTitle; ?></h3>
			<div class="row mb-20">
				<div class="col-md-7">
					<p><?php echo $dwnldAppText; ?></p>
				</div>
				<div class="col-md-5">
					<p class="text-center"><a href="templates/rental-application.pdf" class="btn btn-primary btn-lg btn-icon-alt" target="_blank"><?php echo $dwnldAppBtn; ?> <i class="fa fa-download"></i></a></p>
				</div>
			</div>
		</div>
<?php
		include('includes/footer.php');
	}
?>