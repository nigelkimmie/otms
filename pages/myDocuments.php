<?php

	// Get Documents
	$qrystmt = "SELECT
					userdocs.*,
					admins.adminName
				FROM
					userdocs
					LEFT JOIN admins ON userdocs.adminId = admins.adminId
				WHERE
					userdocs.userId = ".$rs_userId;
	$results = mysqli_query($mysqli, $qrystmt) or die('-3' . mysqli_error());

	$userPage = 'true';
	$pageTitle = $myDocsNavLink;
	$addCss = '<link href="css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'myDocuments';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php if ($msgBox) { echo $msgBox; } ?>
		<h3><?php echo $pageTitle; ?></h3>
		
		<?php if(mysqli_num_rows($results) < 1) { ?>
			<div class="alertMsg default mb-20">
				<div class="msgIcon pull-left">
					<i class="fa fa-info-circle"></i>
				</div>
				<?php echo $usrNoDocsFoundMsg; ?>
			</div>
		<?php } else { ?>
			<table id="docs" class="display" cellspacing="0">
				<thead>
					<tr>
						<th><?php echo $docNameHead; ?></th>
						<th><?php echo $docDescHead; ?></th>
						<th class="text-center"><?php echo $uploadedByHead; ?></th>
						<th class="text-center"><?php echo $dateUploadedHead; ?></th>
					</tr>
				</thead>

				<tbody>
					<?php while ($rows = mysqli_fetch_assoc($results)) { ?>
						<tr>
							<td>
								<a href="page.php?page=viewDocument&docId=<?php echo $rows['docId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewDocText; ?>">
									<?php echo clean($rows['docTitle']); ?>
								</a>
							</td>
							<td><?php echo clean($rows['docDesc']); ?></td>
							<td class="text-center"><?php echo clean($rows['adminName']); ?></td>
							<td class="text-center"><?php echo dateFormat($rows['uploadDate']); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</div>