<?php
	$managePage = 'true';
	$pageTitle = $accReportsPageTitle;
	$addCss = '<link rel="stylesheet" type="text/css" href="../css/datetimepicker.css" />';
	$datePicker = 'true';
	$jsFile = 'accountingReports';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($set['enablePayments'] == '1') {
				if ((checkArray('ACCTRPT', $auths)) || $rs_isAdmin != '') {
					if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $pageTitle; ?></h3>
				
				<input type="hidden" id="weekStart" value="<?php echo $set['weekStart']; ?>" />
				
				<div class="row">
					<div class="col-md-6">
						<form action="index.php?action=accountingRpt" method="post">
							<fieldset class="success">
								<legend class="success"><?php echo $payRcvdLegend; ?></legend>
								<div id="errNote"></div>

								<label><?php echo $selectPayTypesField; ?></label>
									<div id="payTypes" class="form-group">
										<input type="radio" name="payType" id="allTypes" value="all" checked="" />
										<label for="allTypes" class="allTypesOpt"><i class="fa fa-dot-circle-o"></i> <?php echo $allCheckboxOpt; ?></label>

										<input type="radio" name="payType" id="rentTypes" value="rental" />
										<label for="rentTypes" class="rentTypesOpt"> <i class="fa fa-circle-o"></i> <?php echo $rentalPayOpt; ?></label>
										
										<input type="radio" name="payType" id="otherTypes" value="other" />
										<label for="otherTypes" class="otherTypesOpt"> <i class="fa fa-circle-o"></i> <?php echo $otherPayOpt; ?></label>
									</div>
									
									<?php
										// Get Tenant List
										$tenqry = "SELECT userId, CONCAT(userFirstName,' ',userLastName) AS user, isLeased FROM users WHERE isActive = 1 AND isResident = 0";
										$tenres = mysqli_query($mysqli, $tenqry) or die('-1'.mysqli_error());
									?>
									<label for="tenants1"><?php echo $selectTenantsField; ?></label>
									<select id="tenants1" multiple class="form-control selectall" name="userId[]">
										<option value="all" selected><?php echo $allTenantsOpt; ?></option>
										<?php
											while ($ten = mysqli_fetch_assoc($tenres)) {
												if ($ten['isLeased'] == '0') { $noLease = ' *'; } else { $noLease = ''; }
												echo '<option value="'.$ten['userId'].'">'.$ten['user'].$noLease.'</option>';
											}
										?>
									</select>
									<span class="help-block"><?php echo $selectTenantsFieldHelp; ?></span>
									
									<div class="form-group">
										<label for="accFromDate"><?php echo $showRecFromField; ?></label>
										<input type="text" class="form-control" name="fromDate" id="accFromDate" required="required" value="" />
										<span class="help-block"><?php echo $showRecFromFieldHelp; ?></span>
									</div>
									<div class="form-group">
										<label for="accToDate"><?php echo $showRecToField; ?></label>
										<input type="text" class="form-control" name="toDate" id="accToDate" required="required" value="" />
										<span class="help-block"><?php echo $showRecToFieldHelp; ?></span>
									</div>

									<input type="hidden" name="rptType" value="paymentsRep" />
									<button type="input" name="submit" value="runRpt" id="paymentsRep" class="btn btn-success btn-sm btn-icon-alt"><?php echo $runRptBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
							</fieldset>
						</form>
					</div>
					<div class="col-md-6">
						<form action="index.php?action=accountingRpt" method="post">
							<fieldset class="success">
								<legend class="success"><?php echo $refIssLegend; ?></legend>
								<div id="errNote1"></div>
									
									<?php
										// Get Tenant List
										$tenqry1 = "SELECT userId, CONCAT(userFirstName,' ',userLastName) AS user, isLeased FROM users WHERE isActive = 1 AND isResident = 0";
										$tenres1 = mysqli_query($mysqli, $tenqry1) or die('-1'.mysqli_error());
									?>
									<label for="tenants2"><?php echo $selectTenantsField; ?></label>
									<select id="tenants2" multiple class="form-control selectall" name="userId[]">
										<option value="all" selected><?php echo $allTenantsOpt; ?></option>
										<?php
											while ($ten1 = mysqli_fetch_assoc($tenres1)) {
												if ($ten1['isLeased'] == '0') { $noLease = ' *'; } else { $noLease = ''; }
												echo '<option value="'.$ten1['userId'].'">'.$ten1['user'].$noLease.'</option>';
											}
										?>
									</select>
									<span class="help-block"><?php echo $selectTenantsFieldHelp; ?></span>
									
									<div class="form-group">
										<label for="refFromDate"><?php echo $showRecFromField; ?></label>
										<input type="text" class="form-control" name="fromDate" id="refFromDate" required="required" value="" />
										<span class="help-block"><?php echo $showRecFromFieldHelp; ?></span>
									</div>
									<div class="form-group">
										<label for="refToDate"><?php echo $showRecToField; ?></label>
										<input type="text" class="form-control" name="toDate" id="refToDate" required="required" value="" />
										<span class="help-block"><?php echo $showRecToFieldHelp; ?></span>
									</div>

									<input type="hidden" name="rptType" value="refundsRep" />
									<button type="input" name="submit" value="runRpt" id="refundsRep" class="btn btn-success btn-sm btn-icon-alt"><?php echo $runRptBtn; ?> <i class="fa fa-long-arrow-right"></i></button>
							</fieldset>
						</form>
					</div>
				</div>
		
			<?php } else { ?>
				<hr class="mt-0 mb-0" />
				<h3><?php echo $accessErrorHeader; ?></h3>
				<div class="alertMsg warning mb-20">
					<div class="msgIcon pull-left">
						<i class="fa fa-warning"></i>
					</div>
					<?php echo $permissionDenied; ?>
				</div>
			<?php } ?>
		<?php } else { ?>
			<hr class="mt-0 mb-0" />
			<h3><?php echo $accessErrorHeader; ?></h3>
			<div class="alertMsg warning mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-warning"></i>
				</div>
				<?php echo $permissionDenied; ?>
			</div>
		<?php } ?>
	</div>