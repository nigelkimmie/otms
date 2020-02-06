<?php
	// Get Property ID
	$sql = "SELECT
				properties.propertyId
			FROM
				properties
				LEFT JOIN leases ON properties.propertyId = leases.propertyId
			WHERE leases.leaseId = ".$rs_leaseId;
	$result = mysqli_query($mysqli, $sql) or die('-1' . mysqli_error());
	$rows = mysqli_fetch_assoc($result);
	$propId = $rows['propertyId'];

	// Get Service Request Data
	$qry = "SELECT
				servicerequests.*,
				properties.propertyName,
				servicepriority.priorityTitle,
				servicestatus.statusTitle,
				admins.adminName AS assignedAdmin
			FROM
				servicerequests
				LEFT JOIN properties ON servicerequests.propertyId = properties.propertyId
				LEFT JOIN servicepriority ON servicerequests.requestPriority = servicepriority.priorityId
				LEFT JOIN servicestatus ON servicerequests.requestStatus = servicestatus.statusId
				LEFT JOIN admins ON servicerequests.assignedTo = admins.adminId
				LEFT JOIN users ON servicerequests.userId = users.userId
			WHERE
				servicerequests.leaseId = ".$rs_leaseId;
	$res = mysqli_query($mysqli, $qry) or die('-2' . mysqli_error());

	$servPage = 'true';
	$pageTitle = $servReqPageTitle;
	$addCss = '<link href="css/dataTables.css" rel="stylesheet">';
	$dataTables = 'true';
	$jsFile = 'serviceRequests';

	include 'includes/user_header.php';
?>
	<div class="container page_block noTopBorder">
		<hr class="mt-0 mb-0" />
		
		<?php
			if ($rs_leaseId != '0') {
				if ($msgBox) { echo $msgBox; }
		?>
			<h3><?php echo $pageTitle; ?></h3>
			
			<div class="row">
				<div class="col-md-8">
					<p class="lead mb-0"><?php echo $servReqQuip1; ?></p>
					<p class="mb-0"><?php echo $servReqQuip2; ?></p>
				</div>
				<div class="col-md-4">
					<p class="text-right mb-0">
						<a href="page.php?page=newRequest" class="btn btn-info btn-icon"><i class="fa fa-wrench"></i> <?php echo $reqNewServBtn; ?></a>
					</p>
				</div>
			</div>
			
			<hr />
			
			<?php if(mysqli_num_rows($res) > 0) { ?>
				<table id="requests" class="display" cellspacing="0">
					<thead>
						<tr>
							<th><?php echo $assignedToHead; ?></th>
							<th><?php echo $requestHead; ?></th>
							<th class="text-center"><?php echo $priorityField; ?></th>
							<th class="text-center"><?php echo $statusHead; ?></th>
							<th class="text-center"><?php echo $dateSubmittedHead; ?></th>
							<th class="text-center"><?php echo $lastUpdatedHead; ?></th>
							<th class="text-center"><?php echo $openClosedHead; ?></th>
						</tr>
					</thead>

					<tbody>
						<?php
							while ($row = mysqli_fetch_assoc($res)) {
								if ($row['assignedTo'] == '0') { $assignedAdmin = '<em>'.$unassignedText.'</em>'; } else { $assignedAdmin = clean($row['assignedAdmin']); }
								if ($row['lastUpdated'] == '0000-00-00 00:00:00') { $lastUpdated = ''; } else { $lastUpdated = dateFormat($row['lastUpdated']); }
								if ($row['isClosed'] == '0') { $status = $openText; } else { $status = $closedText; }
						?>
								<tr>
									<td><?php echo $assignedAdmin; ?></td>
									<td>
										<a href="page.php?page=viewRequest&requestId=<?php echo $row['requestId']; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $viewRequestText; ?>">
											<?php echo clean($row['requestTitle']); ?>
										</a>
									</td>
									<td class="text-center"><?php echo clean($row['priorityTitle']); ?></td>
									<td class="text-center"><?php echo clean($row['statusTitle']); ?></td>
									<td class="text-center"><?php echo dateFormat($row['requestDate']); ?></td>
									<td class="text-center"><?php echo $lastUpdated; ?></td>
									<td class="text-center"><?php echo $status; ?></td>
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