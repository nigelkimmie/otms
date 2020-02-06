<?php
	$docId = $mysqli->real_escape_string($_GET['docId']);
	
	// Check if Document belongs to logged in user
	$usrCk = "SELECT userId FROM userdocs WHERE docId = ".$docId;
	$userChk = mysqli_query($mysqli, $usrCk) or die('-1' . mysqli_error());
	$uchk = mysqli_fetch_assoc($userChk);
	$userCheck = $uchk['userId'];

	// Get the File Uploads Folder from the Site Settings
	$userDocsDir = $set['userDocsPath'];

	$qry = "SELECT
				userdocs.*,
				admins.adminName,
				users.userFolder
			FROM
				userdocs
				LEFT JOIN admins ON userdocs.adminId = admins.adminId
				LEFT JOIN users ON userdocs.userId = users.userId
			WHERE
				userdocs.docId = ".$docId;
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());
	$row = mysqli_fetch_assoc($res);

	$userPage = 'true';
	$pageTitle = $viewDocPageTitle;

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ($userCheck == $rs_userId) {
				if ($msgBox) { echo $msgBox; }
		?>
			<h3><?php echo $pageTitle; ?></h3>

			<div class="row mb-10">
				<div class="col-md-4">
					<ul class="list-group">
						<li class="list-group-item"><strong><?php echo $titleText; ?></strong> <?php echo clean($row['docTitle']); ?></li>
						<li class="list-group-item"><strong><?php echo $uploadedByText; ?></strong> <?php echo clean($row['adminName']); ?></li>
						<li class="list-group-item"><strong><?php echo $uploadedOnText; ?></strong> <?php echo dateFormat($row['uploadDate']); ?></li>
					</ul>
				</div>
				<div class="col-md-8">
					<p><strong><?php echo $docDescText; ?></strong> <?php echo clean($row['docDesc']); ?></p>

					<hr />

					<?php
						//Get Document Extension
						$ext = substr(strrchr($row['docUrl'],'.'), 1);
						$imgExts = array('gif','GIF','jpg','JPG','jpeg','JPEG','png','PNG','tiff','TIFF','tif','TIF','bmp','BMP');

						if (in_array($ext, $imgExts)) {
							echo '
									<p>
										<a href="'.$userDocsDir.$row['userFolder'].'/'.$row['docUrl'].'" target="_blank">
											<img alt="'.clean($row['docTitle']).'" src="'.$userDocsDir.$row['userFolder'].'/'.$row['docUrl'].'" class="img-responsive" />
										</a>
									</p>
								';
						} else {
							echo '
									<div class="alertMsg default mb-20">
										<div class="msgIcon pull-left">
											<i class="fa fa-info-circle"></i>
										</div>
										'.$noPreviewAvailText.' '.clean($row['docTitle']).'
									</div>
									<p>
										<a href="'.$userDocsDir.$row['userFolder'].'/'.$row['docUrl'].'" class="btn btn-success btn-icon" target="_blank">
										<i class="fa fa-download"></i> '.$downloadFileText.' '.$row['docTitle'].'</a>
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