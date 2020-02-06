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

		// Get Manager/Admin Data
		$qry = "SELECT * FROM admins WHERE isDisabled = 0";
		$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());

		// Get Home Page Content
		$pageCont = "SELECT * FROM sitecontent WHERE pageId = 4";
		$pcres = mysqli_query($mysqli, $pageCont) or die('-2' . mysqli_error());
		$pc = mysqli_fetch_assoc($pcres);

		$aboutPage = 'true';
		$pageTitle = $aboutUsNavLink;

		include('includes/header.php');
?>
		<div class="container page_block noTopBorder">
			<?php if ($msgBox) { echo $msgBox; } ?>

			<hr class="mt-0 mb-0" />

			<?php if ($pc['pageContent'] != '') { ?>
				<div class="intro-text">
					<?php echo htmlspecialchars_decode($pc['pageContent']); ?>
				</div>
			<?php } ?>
		</div>

		<div class="container page_block mt-20">
			<div class="row mt-20">
				<?php
					while ($rows = mysqli_fetch_assoc($res)) {
						$profileurl = preg_replace('/ /', '-', clean($rows['adminName']));

						if (!empty($rows['facebook'])) {
							$afacebook = '<li data-toggle="tooltip" data-placement="top" title="'.$facebookText.'"><a href="'.clean($rows['facebook']).'" class="facebook"><i class="fa fa-facebook"></i></a></li>';
						} else { $afacebook = ''; }
						if (!empty($rows['google'])) {
							$agoogle = '<li data-toggle="tooltip" data-placement="top" title="'.$googleText.'"><a href="'.clean($rows['google']).'" class="google"><i class="fa fa-google"></i></a></li>';
						} else { $agoogle = ''; }
						if (!empty($rows['linkedin'])) {
							$alinkedin = '<li data-toggle="tooltip" data-placement="top" title="'.$linkedinText.'"><a href="'.clean($rows['linkedin']).'" class="linkedin"><i class="fa fa-linkedin"></i></a></li>';
						} else { $alinkedin = ''; }
						if (!empty($rows['pinterest'])) {
							$apinterest = '<li data-toggle="tooltip" data-placement="top" title="'.$pinterestText.'"><a href="'.clean($rows['pinterest']).'" class="pinterest"><i class="fa fa-pinterest"></i></a></li>';
						} else { $apinterest = ''; }
						if (!empty($rows['twitter'])) {
							$atwitter = '<li data-toggle="tooltip" data-placement="top" title="'.$twitterText.'"><a href="'.clean($rows['twitter']).'" class="twitter"><i class="fa fa-twitter"></i></a></li>';
						} else { $atwitter = ''; }
						if (!empty($rows['youtube'])) {
							$ayoutube = '<li data-toggle="tooltip" data-placement="top" title="'.$youtubeText.'"><a href="'.clean($rows['youtube']).'" class="youtube"><i class="fa fa-youtube"></i></a></li>';
						} else { $ayoutube = ''; }
				?>
						<div class="col-md-4">
							<div class="profileBox">
								<div class="cover">
									<div class="profilePic">
										<img src="<?php echo $avatarDir.$rows['adminPhoto']; ?>" class="publicPic" />
									</div>
								</div>

								<div class="profileBody">
									<h1>
										<a href="profile.php?profile=<?php echo $profileurl; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $viewPubProfileText; ?>">
											<?php echo clean($rows['adminName']); ?>
										</a>
									</h1>
									<h4 class="mt-10">
										<?php echo clean($rows['adminRole']); ?><br />
										<small><?php echo $memberSinceText; ?> <?php echo dateFormat($rows['createDate']); ?></small>
									</h3>
									<p class="mt-10"><i class="fa fa-quote-left icon-quote"></i> <?php echo clean($rows['personalQuip']); ?> <i class="fa fa-quote-right icon-quote"></i></p>
									<ul class="socialLinks">
										<?php
											echo $afacebook;
											echo $agoogle;
											echo $alinkedin;
											echo $apinterest;
											echo $atwitter;
											echo $ayoutube;
										?>
									</ul>
								</div>
							</div>
						</div>
				<?php } ?>
			</div>
		</div>
<?php
		include('includes/footer.php');
	}
?>