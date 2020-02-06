<?php
	$fileId = $mysqli->real_escape_string($_GET['fileId']);
	
	// Check if Property is leased to logged in user
	$usrCk = "SELECT
				users.userId
			FROM
				propfiles
				LEFT JOIN users ON propfiles.propertyId = users.propertyId
			WHERE
				propfiles.fileId = ".$fileId." AND
				users.userId = ".$rs_userId;
	$userChk = mysqli_query($mysqli, $usrCk) or die('-1' . mysqli_error());
	$uchk = mysqli_fetch_assoc($userChk);
	$userCheck = $uchk['userId'];

	// Get the File Uploads Folder from the Site Settings
	$uploadsDir = $set['uploadPath'];

	$qry = "SELECT
				propfiles.*,
				properties.propertyName,
				admins.adminId,
				admins.adminName
			FROM
				propfiles
				LEFT JOIN properties ON propfiles.propertyId = properties.propertyId
				LEFT JOIN admins ON propfiles.adminId = admins.adminId
			WHERE
				propfiles.fileId = ".$fileId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);

	$propPage = 'true';
	$pageTitle = $viewFilePageTitle;

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($rs_leaseId != '0' && $userCheck == $rs_userId) {
				if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $pageTitle; ?></h3>

				<div class="row mb-10">
					<div class="col-md-4">
						<ul class="list-group">
							<li class="list-group-item"><strong><?php echo $titleText; ?></strong> <?php echo clean($row['fileName']); ?></li>
							<li class="list-group-item"><strong><?php echo $newPaymentEmail2; ?></strong> <?php echo clean($row['propertyName']); ?></li>
							<li class="list-group-item"><strong><?php echo $uploadedByText; ?></strong> <?php echo clean($row['adminName']); ?></li>
							<li class="list-group-item"><strong><?php echo $uploadedOnText; ?></strong> <?php echo dateFormat($row['uploadDate']); ?></li>
						</ul>
					</div>
					<div class="col-md-8">
						<p><strong><?php echo $fileDescText; ?></strong> <?php echo clean($row['fileDesc']); ?></p>

						<hr />

						<?php
							//Get File Extension
							$ext = substr(strrchr($row['fileUrl'],'.'), 1);
							$imgExts = array('gif','GIF','jpg','JPG','jpeg','JPEG','png','PNG','tiff','TIFF','tif','TIF','bmp','BMP');

							if (in_array($ext, $imgExts)) {
								echo '
										<p>
											<a href="'.$uploadsDir.$row['fileUrl'].'" target="_blank">
												<img alt="'.clean($row['fileName']).'" src="'.$uploadsDir.$row['fileUrl'].'" class="img-responsive" />
											</a>
										</p>
									';
							} else {
								echo '
										<div class="alertMsg default mb-20">
											<div class="msgIcon pull-left">
												<i class="fa fa-info-circle"></i>
											</div>
											'.$noPreviewAvailText.' '.clean($row['fileName']).'
										</div>
										<p>
											<a href="'.$uploadsDir.$row['fileUrl'].'" class="btn btn-success btn-icon" target="_blank">
											<i class="fa fa-download"></i> '.$downloadFileText.' '.$row['fileName'].'</a>
										</p>
									';
							}
						?>
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
	</div>