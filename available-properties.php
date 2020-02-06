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

		// Get Available Properties
		$qry = "SELECT * FROM properties WHERE isLeased = 0 ORDER BY featured DESC";
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());

		// Get Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 2";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-2' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);

		$propPage = 'true';
		$pageTitle = $availPropNavLink;

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<hr class="mt-0 mb-0" />

			<?php if ($msgBox) { echo $msgBox; } ?>

			<h3><?php echo $pageTitle; ?></h3>

			<?php if ($pc['pageContent'] != '') { ?>
				<div class="intro-text">
					<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
				</div>
			<?php } ?>

			<?php if (mysqli_num_rows($res) > 0) { ?>
				<div class="row mt-20">
					<?php
						while ($rows = mysqli_fetch_assoc($res)) {
							$propurl = preg_replace('/ /', '-', clean(strip($rows['propertyName'])));
					?>
							<div class="col-sm-6 col-md-4">
								<div class="thumbnail">
									<?php if ($rows['featured'] == '1') { ?>
										<span class="ribbon top-left propLists ribbon-primary">
											<small><?php echo $featuredText; ?></small>
										</span>
									<?php } ?>
									<a href="view-property.php?property=<?php echo $propurl; ?>">
										<img src="<?php echo $propPicsPath.clean($rows['propertyImage']); ?>" alt="<?php echo clean($rows['propertyName']); ?>" class="img-responsive" />
									</a>
									<div class="caption">
										<h3 id="thumbnail-label"><a href="view-property.php?property=<?php echo $propurl; ?>"><?php echo clean($rows['propertyName']); ?></a></h3>
										<p><?php echo ellipsis($rows['propertyDesc'],150); ?></p>
										<?php
											if (!is_null($rows['propertyStyle'])) {
												echo '<span class="label label-list">'.clean($rows['propertyStyle']).'</span> ';
											}
											if (!is_null($rows['yearBuilt'])) {
												echo '<span class="label label-list">'.$yearText.': '.clean($rows['yearBuilt']).'</span> ';
											}
											if (!is_null($rows['bedrooms'])) {
												echo '<span class="label label-list">'.$bedroomsText.': '.clean($rows['bedrooms']).'</span> ';
											}
											if (!is_null($rows['bathrooms'])) {
												echo '<span class="label label-list">'.$bathsText.': '.clean($rows['bathrooms']).'</span> ';
											}
											if (!is_null($rows['propertySize'])) {
												echo '<span class="label label-list">'.clean($rows['propertySize']).'</span> ';
											}
											if ($rows['petsAllowed'] == '1') {
												echo '<span class="label label-list">'.$petsText.': '.$yesBtn.'</span> ';
											} else {
												echo '<span class="label label-list">'.$petsText.': '.$noBtn.'</span> ';
											}
											echo '<span class="label label-list">'.$rateText.': '.formatCurrency($rows['propertyRate'],$currCode).'</span> ';
										?>
									</div>
								</div>
							</div>
					<?php
						}
					?>
				</div>
			<?php } else { ?>
				<div class="alertMsg default mb-20">
					<div class="msgIcon pull-left">
						<i class="fa fa-info-circle"></i>
					</div>
					<?php echo $noPropAvailMsg; ?>
				</div>
			<?php } ?>
		</div>
<?php
		include('includes/footer.php');
	}
?>