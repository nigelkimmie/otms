<?php
	// Access DB Info
	include('../../config.php');

	// Get the Admin's ID
	$adminsId = $mysqli->real_escape_string($_POST['adminsId']);

	$datasql = "SELECT adminId, adminEmail, adminName, adminRole, isAdmin FROM admins WHERE adminId = ".$adminsId." LIMIT 1";
	$authres = mysqli_query($mysqli, $datasql) or die('-198'.mysqli_error());
	$hasData = mysqli_num_rows($authres);
	
	if ($hasData > 0) {
		$dataqry = "SELECT
						appauth.*,
						authdesc.flagDesc,
						admins.adminEmail,
						admins.adminName,
						admins.isAdmin,
						admins.adminRole
					FROM
						appauth
						LEFT JOIN authdesc ON appauth.authFlag = authdesc.authFlag
						LEFT JOIN admins ON appauth.adminId = admins.adminId
					WHERE appauth.adminId = ".$adminsId." AND isActive = 1";
		$datares = mysqli_query($mysqli, $dataqry) or die('Error: Retrieving Admin Info '.mysqli_error());
		$hasRes = mysqli_num_rows($datares);
		
		if ($hasRes > 0) {
			while($datarow = mysqli_fetch_assoc($datares)) {
				$datarows = array_map(null, $datarow);
				$admindata[] = $datarows;
			}
		
			echo json_encode($admindata);
		} else {
			while($datarow = mysqli_fetch_assoc($authres)) {
				$datarows = array_map(null, $datarow);
				$admindata[] = $datarows;
			}
			
			echo json_encode($admindata);
		}
	}
?>