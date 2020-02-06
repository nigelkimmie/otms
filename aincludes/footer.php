	<div class="container footer_block noBotBorder mt-20">
		<div class="row">
			<div class="col-md-3">
				<h4 class="footer-hLine"><?php echo $availPropNavLink; ?></h4>
				<div class="footerTags">
					<?php
						// Get Available Properties
						$apqry = "SELECT * FROM properties WHERE isLeased = 0";
						$apres = mysqli_query($mysqli, $apqry) or die('Footer Error in Available Properties' . mysqli_error());

						if (mysqli_num_rows($apres) > 0) {
					?>
						<ul>
					<?php
							while ($aprow = mysqli_fetch_assoc($apres)) {
								$propurl = preg_replace('/ /', '-', clean(strip($aprow['propertyName'])));
					?>
							<li><a href="../view-property.php?property=<?php echo $propurl; ?>"><?php echo clean($aprow['propertyName']); ?></a></li>
					<?php } ?>
						</ul>
					<?php } else { ?>
						<ul>
							<li><a href=""><i class="fa fa-check"></i> &nbsp; <?php echo $allPropRentedText; ?></a></li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<div class="col-md-5">
				<h4 class="footer-hLine"><?php echo $recentActTitle; ?></h4>
				<?php
					// Get the Recent Activity
					$ra = "SELECT
								*,
								UNIX_TIMESTAMP(activityDate) AS orderDate
							FROM
								activity
							ORDER BY orderDate DESC LIMIT 5";
					$recent = mysqli_query($mysqli, $ra) or die('-100' . mysqli_error());

					if(mysqli_num_rows($recent) > 0) {
				?>
						<ul class="activityLinks">
							<?php
								while ($recAct = mysqli_fetch_assoc($recent)) {
									$activityIcon = '<span class="fa fa-database"></span>';
									if ($recAct['activityType'] == '1') { $activityIcon = '<span class="fa fa-user"></span>'; }
									if ($recAct['activityType'] == '2') { $activityIcon = '<span class="fa fa-building"></span>'; }
									if ($recAct['activityType'] == '3') { $activityIcon = '<span class="fa fa-wrench"></span>'; }
									if ($recAct['activityType'] == '4') { $activityIcon = '<span class="fa fa-calendar"></span>'; }
									if ($recAct['activityType'] == '5') { $activityIcon = '<span class="fa fa-male"></span>'; }
									if ($recAct['activityType'] == '6') { $activityIcon = '<span class="fa fa-bullhorn"></span>'; }
									if ($recAct['activityType'] == '7') { $activityIcon = '<span class="fa fa-file"></span>'; }
									if ($recAct['activityType'] == '8') { $activityIcon = '<span class="fa fa-cogs"></span>'; }
									if ($recAct['activityType'] == '9') { $activityIcon = '<span class="fa fa-lock"></span>'; }
									if ($recAct['activityType'] == '10') { $activityIcon = '<span class="fa fa-list"></span>'; }
									if ($recAct['activityType'] == '11') { $activityIcon = '<span class="fa fa-sign-in"></span>'; }
									if ($recAct['activityType'] == '12') { $activityIcon = '<span class="fa fa-sign-out"></span>'; }
									if ($recAct['activityType'] == '13') { $activityIcon = '<span class="fa fa-cog"></span>'; }
									if ($recAct['activityType'] == '14') { $activityIcon = '<span class="fa fa-home"></span>'; }
									if ($recAct['activityType'] == '15') { $activityIcon = '<span class="fa fa-edit"></span>'; }
									if ($recAct['activityType'] == '16') { $activityIcon = '<span class="fa fa-user"></span>'; }
									if ($recAct['activityType'] == '17') { $activityIcon = '<span class="fa fa-male"></span>'; }
									if ($recAct['activityType'] == '18') { $activityIcon = '<span class="fa fa-laptop"></span>'; }
									if ($recAct['activityType'] == '19') { $activityIcon = '<span class="fa fa-upload"></span>'; }
									if ($recAct['activityType'] == '20') { $activityIcon = '<span class="fa fa-file-text-o"></span>'; }
									if ($recAct['activityType'] == '21') { $activityIcon = '<span class="fa fa-times-circle"></span>'; }
									if ($recAct['activityType'] == '22') { $activityIcon = '<span class="fa fa-paypal"></span>'; }
									if ($recAct['activityType'] == '23') { $activityIcon = '<span class="fa fa-bar-chart"></span>'; }
									if ($recAct['activityType'] == '24') { $activityIcon = '<span class="fa fa-lock"></span>'; }
							?>
									<li>
										<?php echo $activityIcon; ?> <?php echo ellipsis($recAct['activityTitle'],70); ?>
										<i class="fa fa-info-circle pull-right activityLinksHelp" data-toggle="popover" data-placement="left" data-content="<?php echo dateFormat($recAct['activityDate']); ?>"></i>
									</li>
							<?php } ?>
						</ul>
				<?php } else { ?>
					<div class="alertMsg default">
						<div class="msgIcon pull-left">
							<i class="fa fa-info-circle"></i>
						</div>
						<?php echo $noActMsg; ?>
					</div>
				<?php } ?>
			</div>

			<div class="col-md-4">
				<div class="dateTime">
					<div class="day"><?php echo date('j'); ?></div>
					<div class="monthYear">
						<?php echo $monthName.' '.date('Y'); ?><br />
						<span><?php echo $dayName; ?></span>
					</div>
				</div>
				<hr class="clearfix" />
				<?php if ($rs_adminLoc != '') { $widgetLoc = $rs_adminLoc; } else { $widgetLoc = $set['weatherLoc']; } ?>
				<input id="weatherLoc" type="hidden" value="<?php echo $widgetLoc; ?>" />
				<div id="weather"></div>
			</div>
		</div>

		<div class="copyright clearfix">
			<div class="pull-left">
				<span><i class="fa fa-copyright"></i> <?php echo $copyrightText; ?> <?php echo date("Y"); ?></span> <?php echo "Online Tenancy Management System"; ?>
			</div>
			<div class="pull-right">
				<ul class="list-inline footer-nav">
					<li><a href="../index.php"><?php echo $homeNavLink; ?></a></li>
					<li><a href="../available-properties.php"><?php echo $propNavLink; ?></a></li>
					<li><a href="../about-us.php"><?php echo $aboutUsNavLink; ?></a></li>
					<li><a href="../contact-us.php"><?php echo $contactUsNavLink; ?></a></li>
					<li><a data-toggle="modal" href="#signOut"><?php echo $signOutNavLink; ?></a></li>
				</ul>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/bootstrap.min.js"></script>
	<?php
		if (isset($chosen)) {
			echo '
					<script type="text/javascript" src="../js/chosen.jquery.min.js"></script>
					<script type="text/javascript" src="../js/chosen.js"></script>
				';
		}
	?>
	<script type="text/javascript" src="../js/simpleWeather.min.js"></script>
	<script type="text/javascript" src="../js/simpleWeather.js"></script>
	<script type="text/javascript" src="../js/custom.js"></script>
	<?php if (isset($datePicker)) { echo '<script type="text/javascript" src="../js/datetimepicker.js"></script>'; } ?>
	<?php
		if (isset($dataTables)) {
			echo '
				<script src="../js/dataTables.js"></script>
				<script src="../js/dataTables.tableTools.js"></script>
			';
			include('../js/tableTools.php');
		}
	?>
	<?php if (isset($jsFile)) { echo '<script type="text/javascript" src="js/'.$jsFile.'.js"></script>'; } ?>

</body>
</html>