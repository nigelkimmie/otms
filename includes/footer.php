<?php
	if ($rs_userId != '') {
		$cr_FirstName = $rs_userFirst;
		$cr_LastName = $rs_userLast;
		$cr_Email = $rs_userEmail;
	} else {
		$cr_FirstName = '';
		$cr_LastName = '';
		$cr_Email = '';
	}
?>
	<div class="container footer_block noBotBorder mt-20">
		<div class="row">
			<div class="col-md-5">
				<h4 class="footer-hLine"><?php echo $getInTouchTitle; ?></h4>
				<ul class="foot_links">
					<li>
						<span class="fa fa-map-marker"></span>
						<?php echo $set['businessAddress']; ?>
					</li>
					<li>
						<span class="fa fa-phone"></span>
						<?php echo $set['businessPhone']; ?>
					</li>
					<li>
						<span class="fa fa-envelope-o"></span>
						<a href="<?php echo $set['siteEmail']; ?>"><?php echo $set['siteEmail']; ?></a>
					</li>
				</ul>
				<div id="map">
					<?php if (!is_null($set['contactUsMap'])) { ?>
						<div class="row">
							<iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://<?php echo $set['contactUsMap'];?>"></iframe>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="col-md-7">
				<h4 class="footer-hLine"><?php echo $questionsCommentsTitle; ?></h4>
				<form action="" method="post" class="contact_form">
					<div class="row mt-10">
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-control" name="crFirstName" data-placeholder="<?php echo $contUsFormFirstName; ?>" required="required" value="<?php echo $cr_FirstName; ?>" type="text" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-control" name="crLastName" data-placeholder="<?php echo $contUsFormLastName; ?>" required="required" value="<?php echo $cr_LastName; ?>" type="text" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-control" name="crEmail" data-placeholder="<?php echo $emailAddyText; ?>" required="required" value="<?php echo $cr_Email; ?>" type="email" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<input class="form-control" name="crPhone" data-placeholder="<?php echo $contUsFormPhone; ?>" value="" type="text" />
							</div>
						</div>
					</div>
					<div class="form-group">
						<textarea class="form-control" name="crMessage" data-placeholder="<?php echo $contUsFormComments; ?>" rows="5" required="required"></textarea>
					</div>
					<div class="row">
						<div class="col-md-3">
							<p><img src="includes/captcha.php" data-toggle="tooltip" data-placement="top" title="<?php echo $captchaCodeText; ?>"></p>
						</div>
						<div class="col-md-9">
							<div class="form-group">
								<input class="form-control" name="crCaptcha" data-placeholder="<?php echo $enterCodeText; ?>" type="text" required="required" />
							</div>
						</div>
					</div>
					<input name="none" type="hidden">
					<button type="input" name="submit" value="contactReq" class="btn btn-inverse btn-sm btn-block btn-icon"><i class="fa fa-check-square-o"></i> <?php echo $contUsFormBtn; ?></button>
				</form>
			</div>
		</div>
		
		<div class="copyright clearfix">
			<div class="pull-left">
				<span><i class="fa fa-copyright"></i> <?php echo $copyrightText; ?> <?php echo date("Y"); ?></span> <?php echo "OTMS - Online Tenancy Management System"; ?>
			</div>
			<div class="pull-right">
				<ul class="list-inline footer-nav">
					<li><a href="index.php"><?php echo $homeNavLink; ?></a></li>
					<li><a href="available-properties.php"><?php echo $propNavLink; ?></a></li>
					<li><a href="about-us.php"><?php echo $aboutUsNavLink; ?></a></li>
					<li><a href="contact-us.php"><?php echo $contactUsNavLink; ?></a></li>
					<?php if (($rs_adminId != '') || ($rs_userId != '')) { ?>
						<li><a data-toggle="modal" href="#signOut"><?php echo $signOutNavLink; ?></a></li>
					<?php
						} else {
							if ($set['allowRegistrations'] == '1') {
					?>
							<li><a href="sign-in.php"><?php echo $signInUpNavLink; ?></a></li>
					<?php } else { ?>
							<li><a href="sign-in.php"><?php echo $signInNavLink; ?></a></li>
					<?php
							}
						}	
					?>
				</ul>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/custom.js"></script>
	<?php if (isset($flixSlider)) { echo '<script type="text/javascript" src="js/jquery.flexslider.js"></script>'; } ?>
	<?php if (isset($jsFile)) { echo '<script type="text/javascript" src="js/includes/'.$jsFile.'.js"></script>'; } ?>
	<?php if ($set['analyticsCode'] != '') { ?>
		<script type="text/javascript"><?php echo htmlspecialchars_decode($set['analyticsCode']); ?></script>
	<?php } ?>

</body>
</html>