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
		
		// Logout
		if (isset($_GET['action']) && $_GET['action'] == 'logout') {
			$sign_out = '';
			if ($rs_adminId != '') {
				// Add Recent Activity
				$activityType = '12';
				$rs_uid = '0';
				$activityTitle = $rs_adminName.' '.$adminSignout;
				updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);
				$sign_out = 'true';
			} else if ($rs_userId != '') {
				// Add Recent Activity
				$activityType = '12';
				$rs_aid = '0';
				$activityTitle = $rs_userFull.' '.$userSignout;
				updateActivity($rs_aid,$rs_userId,$activityType,$activityTitle);
				$sign_out = 'true';
			}
			if ($sign_out == 'true') {
				session_destroy();
				header ('Location: index.php');
			}
		}
		
		// Get Featured Properties
		$qry = "SELECT * FROM properties WHERE isLeased = 0 AND featured = 1 ORDER BY RAND() LIMIT 9";
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
		
		// Get Home Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 1";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-2' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);
		
		// Get Site Alert Data
		$alert = "SELECT
						*,
						UNIX_TIMESTAMP(alertDate) AS orderDate
					FROM
						sitealerts
					WHERE
						alertStart <= DATE_SUB(CURDATE(),INTERVAL 0 DAY) AND
						alertExpires >= DATE_SUB(CURDATE(),INTERVAL 0 DAY) OR
						isActive = 1 AND
						alertType = 0
					ORDER BY
						orderDate DESC";
		$alertres = mysqli_query($mysqli, $alert) or die('-2' . mysqli_error());
		
		$homePage = 'true';
		$pageTitle = $homeNavLink;

		include('includes/header.php');
		
		$slidercheck = "SELECT * FROM sliderpics";
		$slidecheck = mysqli_query($mysqli, $slidercheck) or die('-3' . mysqli_error());
?>
		<div class="container page_block noTopBorder">
			<?php if ($msgBox) { echo $msgBox; } ?>

			<?php
				if ($set['enableSlider'] == '1') {
					if(mysqli_num_rows($slidecheck) > 0) {
			?>
					<div class="row header-bar">
						<div id="sliderCarousel" class="carousel slide" data-ride="carousel">
				
							<ol class="carousel-indicators">
								<?php
									$csl1 = "SELECT * FROM sliderpics ORDER BY slideId";
									$cres = mysqli_query($mysqli, $csl1) or die('-4' . mysqli_error());

									$count1 = 0;
									while ($r = mysqli_fetch_assoc($cres)) {
										if ($count1 == 0) { $setActive = 'active'; } else { $setActive = ''; }
								?>
										<li data-target="#sliderCarousel" data-slide-to="<?php echo $count1; ?>" class="<?php echo $setActive; ?>"></li>
								<?php
										$count1++;
									}
								?>
							</ol>

							<div class="carousel-inner">
								<?php
									$csl2 = "SELECT * FROM sliderpics ORDER BY slideId";
									$cslres = mysqli_query($mysqli, $csl2) or die('-5' . mysqli_error());

									$count2 = 0;
									while ($r2 = mysqli_fetch_assoc($cslres)) {
										if ($count2 == 0) { $setActive = 'active'; } else { $setActive = ''; }
								?>
										<div class="item <?php echo $setActive; ?>">
											<img src="<?php echo $propPicsPath.$r2['slideUrl']; ?>">
											<div class="header-text hidden-xs">
												<div class="col-md-12 text-center">
													<h2><?php echo clean($r2['slideTitle']); ?></h3>
													<h4><?php echo clean($r2['slideText']); ?></h4>
													<?php
														if ((!isset($_SESSION['rs']['adminId'])) && (!isset($_SESSION['rs']['rs_userId']))) {
															if ($set['allowRegistrations'] == '1') {
																echo '<a class="btn btn-theme btn-sm btn-min-block" href="sign-in.php">Sign In/Up</a>';
															} else {
																echo '<a class="btn btn-theme btn-sm btn-min-block" href="sign-in.php">Sign In</a>';
															}
														}
														if (!is_null($r2['buttonUrl']) && $r2['buttonUrl'] != '') {
													?>
														<a class="btn btn-theme btn-sm btn-min-block" href="<?php echo clean($r2['buttonUrl']); ?>"><?php echo clean($r2['btnText']); ?></a>
													<?php } ?>
												</div>
											</div>
										</div>
								<?php
										$count2++;
									}
								?>
							</div>
						</div>
					</div>
			<?php
					}
				} else {
					echo '<hr class="mt-0 mb-0" />';
				}
			?>

			<div class="intro-text">
				<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
			</div>
			
			<?php
				if(mysqli_num_rows($alertres) > 0) {
					echo '<hr class="pb-10" />';
					while ($rows = mysqli_fetch_assoc($alertres)) {
						// If Start Date is set, use the Start date, else the Date the Alert was created
						if (!is_null($rows['alertStart'])) { $noticeDate = dateFormat($rows['alertStart']); } else { $noticeDate = dateFormat($rows['alertDate']); }
			?>
						<div class="box">
							<span class="box-notify"><?php echo $noticeDate; ?></span>
							<h4><i class="fa fa-bullhorn"></i> &nbsp; <?php echo clean($rows['alertTitle']); ?></h4>
							<p><?php echo nl2br(htmlspecialchars_decode($rows['alertText'])); ?></p>
						</div>
				<?php
					}
					echo '<div class="mb-20"></div>';
				}
			?>
		</div>

		<?php if(mysqli_num_rows($res) > 0) {	?>
			<div class="container page_block mt-20">
				<h3><?php echo $featuredPropText; ?></h3>
				
				<div class="row">
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
												echo '<span class="label label-list">Year: '.clean($rows['yearBuilt']).'</span> ';
											}
											if (!is_null($rows['bedrooms'])) {
												echo '<span class="label label-list">Bedrooms: '.clean($rows['bedrooms']).'</span> ';
											}
											if (!is_null($rows['bathrooms'])) {
												echo '<span class="label label-list">Baths: '.clean($rows['bathrooms']).'</span> ';
											}
											if (!is_null($rows['propertySize'])) {
												echo '<span class="label label-list">'.clean($rows['propertySize']).'</span> ';
											}
											if ($rows['petsAllowed'] == '1') {
												echo '<span class="label label-list">Pets: Yes</span> ';
											} else {
												echo '<span class="label label-list">Pets: No</span> ';
											}
											echo '<span class="label label-list">Rate: '.formatCurrency($rows['propertyRate'],$currCode).'</span> ';
										?>
									</div>
								</div>
							</div>
					<?php
						}
					?>
				</div>
			</div>
		<?php } ?>
<?php
		include('includes/footer.php');
	}
?>