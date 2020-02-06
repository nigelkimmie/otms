<?php
	$ipAddress = $_SERVER['REMOTE_ADDR'];
	$installUrl = $set['installUrl'];

	// Get the Templates Directory
	$templatesPath = $set['templatesPath'];

	// Get the file types allowed from Site Settings
	$filesAllowed = $set['fileTypesAllowed'];
	// Replace the commas with a comma space
	$fileTypesAllowed = preg_replace('/,/', ', ', $filesAllowed);

	// Get the Max Upload Size allowed
    $maxUpload = (int)(ini_get('upload_max_filesize'));

	// Upload Template
	if (isset($_POST['submit']) && $_POST['submit'] == 'uploadTmpl') {
		// User Validations
		if($_POST['templateName'] == '') {
			$msgBox = alertBox($templNameReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else if($_POST['templateDesc'] == '') {
			$msgBox = alertBox($templDescReq, "<i class='fa fa-times-circle'></i>", "danger");
		} else {
			// Get the File Types allowed
			$allowed = preg_replace('/,/', ', ', $filesAllowed); // Replace the commas with a comma space (, )
			$ftypes = array($filesAllowed);
			$ftypes_data = explode( ',', $filesAllowed );

			$templateName = htmlspecialchars($_POST['templateName']);
			$templateDesc = htmlspecialchars($_POST['templateDesc']);

			// Check file type
			$ext = substr(strrchr(basename($_FILES['file']['name']), '.'), 1);
			if (!in_array($ext, $ftypes_data)) {
				$msgBox = alertBox($templWrongTypeMsg, "<i class='fa fa-times-circle'></i>", "danger");
			} else {
				// Rename the file to the Template Name
				$tempName = clean($templateName);

				// Replace any spaces with an underscore
				// And set to all lower-case
				$newName = str_replace(' ', '-', $tempName);
				$fileName = strtolower($newName);

				// Generate a RANDOM Hash
				$randomHash = uniqid(rand());
				// Take the first 8 hash digits and use them as part of the Image Name
				$randHash = substr($randomHash, 0, 8);

				$fullName = $fileName.'-'.$randHash;

				// set the upload path
				$fileUrl = basename($_FILES['file']['name']);

				// Get the files original Ext
				$extension = explode(".", $fileUrl);
				$extension = end($extension);

				// Set the files name to the name set in the form
				// And add the original Ext
				$newFileName = $fullName.'.'.$extension;
				$movePath = '../'.$templatesPath.$newFileName;

				$stmt = $mysqli->prepare("
									INSERT INTO
										sitetemplates(
											adminId,
											templateName,
											templateDesc,
											templateUrl,
											uploadDate,
											ipAddress
										) VALUES (
											?,
											?,
											?,
											?,
											NOW(),
											?
										)");
				$stmt->bind_param('sssss',
					$rs_adminId,
					$templateName,
					$templateDesc,
					$newFileName,
					$ipAddress
				);

				if (move_uploaded_file($_FILES['file']['tmp_name'], $movePath)) {
					$stmt->execute();
					$stmt->close();

					$msgBox = alertBox($templUplMsg, "<i class='fa fa-check-square'></i>", "success");

					// Add Recent Activity
					$activityType = '7';
					$rs_uid = '0';
					$activityTitle = $rs_adminName.' '.$templUplAct.' "'.$templateName.'"';
					updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);
				} else {
					$msgBox = alertBox($templUplErrorMsg, "<i class='fa fa-times-circle'></i>", "danger");

					// Add Recent Activity
					$activityType = '7';
					$rs_uid = '0';
					$activityTitle = $templUplActError.' \"'.$propertyName.'\" '.$templUplActError1;
					updateActivity($rs_adminId,$rs_uid,$activityType,$activityTitle);
				}
			}
		}
	}

	// Get Data
	$qry = "SELECT
				sitetemplates.*,
				admins.adminName
			FROM
				sitetemplates
				LEFT JOIN admins ON sitetemplates.adminId = admins.adminId";
	$res = mysqli_query($mysqli, $qry) or die('-1' . mysqli_error());

	if (isset($_GET['deleted']) && $_GET['deleted'] == 'yes') {
		$msgBox = alertBox($templDeletedMsg, "<i class='fa fa-check-square'></i>", "success");
	}

	$managePage = 'true';
	$pageTitle = $formsPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'forms';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ((checkArray('FORMS', $auths)) || $rs_isAdmin != '') {
				if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $uplTemplH3; ?></h3>
				<p class="text-right mt-10">
					<a data-toggle="modal" href="#uploadTmpl" class="btn btn-info btn-xs btn-icon mt-5 mb-10"><i class="fa fa-upload"></i> <?php echo $uplTemplBtn; ?></a>
				</p>

				<div class="modal fade" id="uploadTmpl" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
								<h4 class="modal-title"><?php echo $uplTemplH4; ?></h4>
							</div>
							<form enctype="multipart/form-data" action="" method="post">
								<div class="modal-body">
									<p>
										<small>
											<?php echo $fileTypesAllText.' '.$fileTypesAllowed; ?><br />
											<?php echo $maxUploadSizeText.' '.$maxUpload; ?> mb.
										</small>
									</p>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="templateName"><?php echo $templNameField; ?></label>
												<input type="text" class="form-control" name="templateName" id="templateName" required="required" value="<?php echo isset($_POST['templateName']) ? $_POST['templateName'] : ''; ?>" />
												<span class="help-block"><?php echo $templNameFieldHelp; ?></span>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="templateDesc"><?php echo $templDescField; ?></label>
												<input type="text" class="form-control" name="templateDesc" id="templateDesc" required="required" value="<?php echo isset($_POST['templateDesc']) ? $_POST['templateDesc'] : ''; ?>" />
												<span class="help-block"><?php echo $templDescFieldHelp; ?></span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="file"><?php echo $selFileField; ?></label>
										<input type="file" id="file" name="file" required="required" />
									</div>
								</div>
								<div class="modal-footer">
									<button type="input" name="submit" value="uploadTmpl" class="btn btn-success btn-icon"><i class="fa fa-upload"></i> <?php echo $uploadBtn; ?></button>
									<button type="button" class="btn btn-default btn-icon" data-dismiss="modal"><i class="fa fa-times-circle-o"></i> <?php echo $cancelBtn; ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<?php if(mysqli_num_rows($res) < 1) { ?>
					<div class="alertMsg default mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-info-circle"></i>
						</div>
						<?php echo $noUplTmplFoundMsg; ?>
					</div>
				<?php } else { ?>
					<table id="templates" class="display" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo $templNameField; ?></th>
								<th><?php echo $templDescField; ?></th>
								<th class="text-center"><?php echo $uploadedByHead; ?></th>
								<th class="text-center"><?php echo $dateUploadedHead; ?></th>
							</tr>
						</thead>

						<tbody>
							<?php while ($row = mysqli_fetch_assoc($res)) { ?>
								<tr>
									<td>
										<a href="index.php?action=viewTemplate&templateId=<?php echo $row['templateId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewTemplText; ?>">
											<?php echo clean($row['templateName']); ?>
										</a>
									</td>
									<td><?php echo clean($row['templateDesc']); ?></td>
									<td class="text-center"><?php echo clean($row['adminName']); ?></td>
									<td class="text-center"><?php echo dateFormat($row['uploadDate']); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } ?>

				<h3><?php echo $premadeFormsH3; ?></h3>
				<p class="lead"><?php echo $set['siteName']; ?> <?php echo $premadeFormsQuip; ?></p>
				<p><?php echo $premadeFormsQuip1; ?></p>
				<table id="forms" class="display" cellspacing="0">
					<thead>
						<tr>
							<th><?php echo $formNameHead; ?></th>
							<th><?php echo $formDescHead; ?></th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>rental-application.pdf" target="_blank"><?php echo $premadeForm1; ?></a></td>
							<td><?php echo $premadeForm2; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>rent-increase.pdf" target="_blank"><?php echo $premadeForm3; ?></a></td>
							<td><?php echo $premadeForm4; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>move-out-reminder.pdf" target="_blank"><?php echo $premadeForm5; ?></a></td>
							<td><?php echo $premadeForm6; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>pet-agreement.pdf" target="_blank"><?php echo $premadeForm7; ?></a></td>
							<td><?php echo $premadeForm8; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>new-tenant-info.pdf" target="_blank"><?php echo $premadeForm9; ?></a></td>
							<td><?php echo $premadeForm10; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>returned-check-notice.pdf" target="_blank"><?php echo $premadeForm11; ?></a></td>
							<td><?php echo $premadeForm12; ?></td>
						</tr>
						<tr>
							<td><a href="<?php echo $installUrl.$templatesPath; ?>vacate-renew-notice.pdf" target="_blank"><?php echo $premadeForm13; ?></a></td>
							<td><?php echo $premadeForm14; ?></td>
						</tr>
					</tbody>
				</table>

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