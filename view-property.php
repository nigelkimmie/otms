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

		$propName = strip($_GET['property']);
		$property = preg_replace('/-/', ' ', $propName);

		// Add Recent Activity
		$activityType = '14';
		if ($rs_adminId != '') { $rs_aid = $rs_adminId; } else { $rs_aid = '0'; }
		if ($rs_userId != '') { $rs_uid = $rs_userId; } else { $rs_uid = '0'; }
		$activityTitle = $thePropText.' "'.$property.'" '.$wasViewedText;
		updateActivity($rs_aid,$rs_uid,$activityType,$activityTitle);

		// Get Data
		$sql = "SELECT * FROM properties WHERE propertyName = '".$property."'";
		$result = mysqli_query($mysqli, $sql) or die('-1' . mysqli_error());
		$row = mysqli_fetch_assoc($result);
		$propertyId = $row['propertyId'];

		// Get Property Pictures
		$qry = "SELECT * FROM proppictures WHERE propertyId = ".$propertyId;
		$res = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());

		if ($row['petsAllowed'] == '1') { $pets = $yesBtn; } else { $pets = $noBtn; }

		// Get Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 3";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-3' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);

		$propPage = 'true';
		$pageTitle = $property;
		$addCss = '<link href="css/flexslider.css" rel="stylesheet">';
		$flixSlider = 'true';
		$jsFile = 'view-property';

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<hr class="mt-0 mb-0" />

			<?php if ($msgBox) { echo $msgBox; } ?>

			<section class="viewProp mt-20 mb-20">
				<div class="row">
					<div class="col-lg-8 col-sm-8">
						<div id="slider2" class="flexslider">
							<ul class="slides">
								<li><a href="<?php echo $propPicsPath.clean($row['propertyImage']); ?>" target="_blank"><img src="<?php echo $propPicsPath.clean($row['propertyImage']); ?>" alt="" class="responsive-img" /></a></li>
								<?php while ($pic = mysqli_fetch_assoc($res)) { ?>
									<li><a href="<?php echo $propPicsPath.$pic['picUrl']; ?>" target="_blank"><img src="<?php echo $propPicsPath.$pic['picUrl']; ?>" alt="" class="responsive-img" /></a></li>
								<?php } ?>
							</ul>
						</div>
						<div class="clear"></div>
					</div>
					<div class="col-lg-4 col-sm-4">
						<h3 class="mt-0"><?php echo $property; ?></h3>
						<p class="lead mb-0 mt-0">
							<?php echo clean($row['propertyType']).' '.clean($row['propertyStyle']); ?>
							<?php if ($row['featured'] == '1') { ?>
								<br />
								<span class="label label-primary btn-icon"><i class="fa fa-bookmark"></i> <?php echo $featuredPropText; ?></span>
							<?php } ?>
						</p>
						<div class="propFee clearfix">
							<div class="propPrice"><?php echo formatCurrency($row['propertyRate'],$currCode); ?> <small class="text-muted"><?php echo $slashMonthText; ?></small></div>
						</div>

						<hr />

						<ul class="propLists">
							<li><strong><?php echo $bedroomsText; ?></strong>: <?php echo clean($row['bedrooms']); ?></li>
							<li><strong><?php echo $bathroomsText; ?></strong>: <?php echo clean($row['bathrooms']); ?></li>
							<li><strong><?php echo $sixeText; ?></strong>: <?php echo clean($row['propertySize']); ?></li>
							<li><strong><?php echo $heatingText; ?></strong>: <?php echo clean($row['heating']); ?></li>
							<li><strong><?php echo $yearBuiltText; ?></strong>: <?php echo clean($row['yearBuilt']); ?></li>
							<li><strong><?php echo $petsText; ?></strong>: <?php echo $pets; ?></li>
							<li><strong><?php echo $parkingText; ?></strong>: <?php echo clean($row['parking']); ?></li>
							<li><strong><?php echo $depositText; ?></strong>: <?php echo formatCurrency($row['propertyDeposit'],$currCode); ?></li>
						</ul>

						<a href="templates/rental-application.pdf" class="btn btn-sm btn-block btn-success" target="_blank"><?php echo $readyRentAppBtn; ?> &nbsp; <span class="fa fa-download"></span></a>
					</div>
				</div>
			</section>
		</div>

		<div class="container page_block mt-20">
			<?php if (!is_null($row['googleMap'])) { ?>
				<div class="row">
					<iframe width="100%" height="240" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $row['googleMap']; ?>"></iframe>
				</div>
			<?php } ?>
			<p class="lead mt-20"><?php echo clean($row['propertyDesc']); ?></p>
			<?php if (!is_null($row['propertyListing'])) { ?>
				<hr />
				<p class="mb-20"><?php echo nl2br(htmlspecialchars_decode($row['propertyListing'])); ?></p>
			<?php } ?>
		</div>

		<?php if ($pc['pageContent'] != '') { ?>
			<div class="container page_block mt-20">
				<div class="intro-text">
					<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
				</div>
			</div>
		<?php } ?>
<?php
		include('includes/footer.php');
	}
?>