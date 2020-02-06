<?php
	// If you use an SSL Certificate - HTTPS://
	// Uncomment (remove the double slashes) from lines 5 - 9
	// ************************************************************************
	//if (!isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) != "on") {
	//	$url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	//	header("Location: $url");
	//	exit;
	//}

	// Set the Active State on the Navigation
	$dashNav = $tenNav = $propNav = $servNav = $adminNav = $manageNav = '';
	if (isset($dashPage)) { $dashNav = 'active'; } else { $dashNav = ''; }
	if (isset($tenPage)) { $tenNav = 'active'; } else { $tenNav = ''; }
	if (isset($propPage)) { $propNav = 'active'; } else { $propNav = ''; }
	if (isset($servPage)) { $servNav = 'active'; } else { $servNav = ''; }
	if (isset($adminPage)) { $adminNav = 'active'; } else { $adminNav = ''; }
	if (isset($managePage)) { $manageNav = 'active'; } else { $manageNav = ''; }
	
	// Get the Avatar Directory
	$avatarDir = $set['avatarFolder'];
	
	$auths = getAdminAuth($rs_adminId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title><?php echo $set['siteName'].' &middot; '.$pageTitle; ?></title>

	<link rel="stylesheet" type="text/css" href="../css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../css/font-awesome.css" />
	<?php if (isset($addCss)) { echo $addCss; } ?>
	<link rel="stylesheet" type="text/css" href="../css/custom.css" />
	<link rel="stylesheet" type="text/css" href="../css/styles.css" />

	<!--[if lt IE 9]>
		<script src="../js/html5shiv.min.js"></script>
		<script src="../js/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<div class="container page_block noTopBorder noBotBorder">
		<div class="header-cont">
			<div class="row">
				<div class="col-md-8">
					<ul class="list-inline mt-0 mb-0">
						<li><small><a href="../index.php"><?php echo $homeNavLink; ?></a></small></li>
						<li><small><a href="../available-properties.php"><?php echo $propNavLink; ?></a></small></li>
						<li><small><a href="../about-us.php"><?php echo $aboutUsNavLink; ?></a></small></li>
						<li><small><a href="../contact-us.php"><?php echo $contactUsNavLink; ?></a></small></li>
					</ul>
				</div>
				<div class="col-md-4">
					<ul class="list-inline mt-0 mb-0 pull-right">
						<li><small><a href="index.php?action=myProfile"><?php echo $myProfileNavLink; ?></a></small></li>
						<li><small><a data-toggle="modal" href="#signOut"><?php echo $signOutNavLink; ?></a></small></li>
					</ul>
				</div>
			</div>

			<hr class="mt-10 mb-10" />

			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
							<span class="sr-only"><?php echo $toggleNavText; ?></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="index.php"><img src="../images/logo.png" /></a>
					</div>

					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav navbar-right">
							<li class="<?php echo $dashNav; ?>"><a href="index.php"><?php echo $dashboardNavLink; ?></a></li>
							<?php
								if ((checkArray('MNGTEN', $auths)) || $rs_isAdmin != '') {
							?>
									<li class="dropdown <?php echo $tenNav; ?>">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $tenantsNavLink; ?> <i class="fa fa-angle-down"></i></a>
										<ul class="dropdown-menu" role="menu">
											<li><a href="index.php?action=leasedTenants"><?php echo $leasedTenNavLink; ?></a></li>
											<li><a href="index.php?action=unleasedTenants"><?php echo $unleasedTenNavLink; ?></a></li>
											<li><a href="index.php?action=archivedTenants"><?php echo $archivedTenNavLink; ?></a></li>
											<li><a href="index.php?action=newTenant"><?php echo $newTenNavLink; ?></a></li>
										</ul>
									</li>
							<?php
								}
								if ((checkArray('MNGPROP', $auths)) || $rs_isAdmin != '') {
							?>
									<li class="dropdown <?php echo $propNav; ?>">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $propNavLink; ?> <i class="fa fa-angle-down"></i></a>
										<ul class="dropdown-menu" role="menu">
											<li><a href="index.php?action=leasedProperties"><?php echo $leasedPropNavLink; ?></a></li>
											<li><a href="index.php?action=unleasedProperties"><?php echo $unleasedPropNavLink; ?></a></li>
											<li><a href="index.php?action=newProperty"><?php echo $newPropNavLink; ?></a></li>
											<li><a href="index.php?action=propertyLeases"><?php echo $propLeasesNavLink; ?></a></li>
										</ul>
									</li>
							<?php
								}
								if ((checkArray('SRVREQ', $auths)) || $rs_isAdmin != '') {
							?>
									<li class="dropdown <?php echo $servNav; ?>">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $servReqNavLink; ?> <i class="fa fa-angle-down"></i></a>
										<ul class="dropdown-menu" role="menu">
											<li><a href="index.php?action=activeRequests"><?php echo $openReqNavLink; ?></a></li>
											<li><a href="index.php?action=inactiveRequests"><?php echo $closedReqNavLink; ?></a></li>
											<li><a href="index.php?action=newRequest"><?php echo $newReqNavLink; ?></a></li>
										</ul>
									</li>
							<?php
								}
								if ((checkArray('MNGADMINS', $auths)) || $rs_isAdmin != '') {
							?>
							<li class="dropdown <?php echo $adminNav; ?>">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $adminsNavLink; ?> <i class="fa fa-angle-down"></i></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="index.php?action=adminAccounts"><?php echo $adminAccNavLink; ?></a></li>
									<li><a href="index.php?action=newAdmin"><?php echo $newAdminNavLink; ?></a></li>
									<?php if ((checkArray('APPAUTH', $auths)) || $rs_isAdmin != '') { ?>
										<li><a href="index.php?action=adminAuths"><?php echo $adminAuthNavLink; ?></a></li>
									<?php } ?>
								</ul>
							</li>
							<?php } ?>
							<li class="dropdown <?php echo $manageNav; ?>">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $manageNavLink; ?> <i class="fa fa-angle-down"></i></a>
								<ul class="dropdown-menu" role="menu">
									<?php if ((checkArray('SITEALRTS', $auths)) || $rs_isAdmin != '') { ?>
										<li><a href="index.php?action=siteAlerts"><?php echo $siteAlertsNavLink; ?></a></li>
									<?php } ?>
									<li class="dropdown-submenu">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $repNavLink; ?></a>
										<ul class="dropdown-menu">
											<?php if ((checkArray('TENRPT', $auths)) || (checkArray('ADMINRPT', $auths)) || $rs_isAdmin != '') { ?>
												<li><a href="index.php?action=userReports"><?php echo $usrRepNavLink; ?></a></li>
											<?php } if ((checkArray('PROPRPT', $auths)) || (checkArray('LEASERPT', $auths)) || $rs_isAdmin != '') { ?>
												<li><a href="index.php?action=propertyReports"><?php echo $propRepNavLink; ?></a></li>
											<?php }
													if ($set['enablePayments'] == '1') {
														if ((checkArray('ACCTRPT', $auths)) || $rs_isAdmin != '') {
											?>
														<li><a href="index.php?action=accountingReports"><?php echo $accRepNavLink; ?></a></li>
											<?php
													}
												}
												if ((checkArray('SERVRPT', $auths)) || $rs_isAdmin != '') {
											?>
												<li><a href="index.php?action=serviceReports"><?php echo $servRepNavLink; ?></a></li>
											<?php } ?>
										</ul>
									</li>
									<?php if ((checkArray('FORMS', $auths)) || $rs_isAdmin != '') { ?>
										<li><a href="index.php?action=forms"><?php echo $formsNavLink; ?></a></li>
									<?php } if ((checkArray('SITECNT', $auths)) || $rs_isAdmin != '') { ?>
										<li><a href="index.php?action=siteContent"><?php echo $siteContNavLink; ?></a></li>
									<?php } if ((checkArray('SITESET', $auths)) || $rs_isAdmin != '') { ?>
										<li class="dropdown-submenu">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $settingsNavLink; ?></a>
											<ul class="dropdown-menu">
												<li><a href="index.php?action=siteSettings"><?php echo $siteSetNavLink; ?></a></li>
												<li><a href="index.php?action=socialNetworks"><?php echo $socNetNavLink; ?></a></li>
												<li><a href="index.php?action=uploadSettings"><?php echo $uplSetNavLink; ?></a></li>
												<li><a href="index.php?action=paymentSettings"><?php echo $paySetNavLink; ?></a></li>
												<li><a href="index.php?action=servReqSettings"><?php echo $reqSetNavLink; ?></a></li>
												<li><a href="index.php?action=sliderSettings"><?php echo $slideSetNavLink; ?></a></li>
												<li><a href="index.php?action=importExport"><?php echo $impExptSetNavLink; ?></a></li>
											</ul>
										</li>
									<?php } if ((checkArray('SITELOGS', $auths)) || $rs_isAdmin != '') { ?>
										<li><a href="index.php?action=siteLogs"><?php echo $siteLogsNavLink; ?></a></li>
									<?php } ?>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		</div>
	</div>

<div class="modal fade" id="signOut" tabindex="-1" role="dialog" aria-labelledby="signOutLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<p class="lead"><?php echo $rs_adminName; ?>, <?php echo $signOutConf; ?></p>
			</div>
			<div class="modal-footer">
				<a href="../index.php?action=logout" class="btn btn-success btn-icon-alt"><?php echo $signOutNavLink; ?> <i class="fa fa-sign-out"></i></a>
				<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle"></i> <?php echo $cancelBtn; ?></button>
			</div>
		</div>
	</div>
</div>
