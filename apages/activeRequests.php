<?php
	$qry = "SELECT
				servicerequests.*,
				properties.propertyName,
				servicepriority.priorityTitle,
				servicestatus.statusTitle,
				CONCAT(users.userFirstName,' ',users.userLastName) AS user,
				admins.adminName AS assignedAdmin
			FROM
				servicerequests
				LEFT JOIN properties ON servicerequests.propertyId = properties.propertyId
				LEFT JOIN servicepriority ON servicerequests.requestPriority = servicepriority.priorityId
				LEFT JOIN servicestatus ON servicerequests.requestStatus = servicestatus.statusId
				LEFT JOIN admins ON servicerequests.assignedTo = admins.adminId
				LEFT JOIN users ON servicerequests.userId = users.userId
			WHERE
				servicerequests.isClosed = 0";
	$res = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());

	$servPage = 'true';
	$pageTitle = $actReqPageTitle;
	$addCss = '<link href="../css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'activeRequests';

	include 'includes/header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />

		<?php
			if ((checkArray('SRVREQ', $auths)) || $rs_isAdmin != '') {
				if ($msgBox) { echo $msgBox; }
		?>
				<h3><?php echo $pageTitle; ?></h3>

				<?php if(mysqli_num_rows($res) > 0) { ?>
					<table id="requests" class="display" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo $propertyHead; ?></th>
								<th><?php echo $tenantHead; ?></th>
								<th><?php echo $assignedToHead; ?></th>
								<th><?php echo $requestHead; ?></th>
								<th class="text-center"><?php echo $priorityField; ?></th>
								<th class="text-center"><?php echo $statusHead; ?></th>
								<th class="text-center"><?php echo $dateSubmittedHead; ?></th>
								<th class="text-center"><?php echo $lastUpdatedHead; ?></th>
							</tr>
						</thead>

						<tbody>
							<?php
								while ($row = mysqli_fetch_assoc($res)) {
									if ($row['assignedTo'] == '0') { $assignedAdmin = '<em>'.$unassignedText.'</em>'; } else { $assignedAdmin = clean($row['assignedAdmin']); }
									if ($row['lastUpdated'] == '0000-00-00 00:00:00') { $lastUpdated = ''; } else { $lastUpdated = dateFormat($row['lastUpdated']); }
							?>
									<tr>
										<td>
											<a href="index.php?action=viewProperty&propertyId=<?php echo $row['propertyId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewPropertyText; ?>">
												<?php echo clean($row['propertyName']); ?>
											</a>
										</td>
										<td>
											<a href="index.php?action=viewTenant&userId=<?php echo $row['userId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewTenantText; ?>">
												<?php echo clean($row['user']); ?>
											</a>
										</td>
										<td><?php echo $assignedAdmin; ?></td>
										<td>
											<a href="index.php?action=viewRequest&requestId=<?php echo $row['requestId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewRequestText; ?>">
												<?php echo clean($row['requestTitle']); ?>
											</a>
										</td>
										<td class="text-center"><?php echo clean($row['priorityTitle']); ?></td>
										<td class="text-center"><?php echo clean($row['statusTitle']); ?></td>
										<td class="text-center"><?php echo dateFormat($row['requestDate']); ?></td>
										<td class="text-center"><?php echo $lastUpdated; ?></td>
									</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } else { ?>
					<div class="alertMsg default mb-20">
						<div class="msgIcon pull-left">
							<i class="fa fa-info-circle"></i>
						</div>
						<?php echo $noOpenClosedReqFoundMsg; ?>
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