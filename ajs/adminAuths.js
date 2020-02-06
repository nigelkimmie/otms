/**
 * Translations
 * Only translate lines 59 through 62.
 * ie. admin_name.html("Name: &nbsp; "+obj[0].adminName);
 * Translate only the text "Name" and no other code.
 *
 * If you have any questions at all about this file, please contact me through my Support Center:
 * http://jennperrin.com/support/
 **/

$(document).ready(function () {
	$(".adminInfo, .superuser").hide();	// Hide on page load

	var msgText		= $("#msgText").find('span');
	var errorOne	= $("#errorOne").val();
	
	$('input[type="checkbox"]').click(function() {
		// Show a message if an Admin has not been loaded
		if ($("#theId").val() == '' && $("#theAdmin").val() == '') {
			var msgText	= $("#msgText").find('span');
			msgText.html('<div class="alertMsg warning"><div class="msgIcon pull-left"><i class="fa fa-warning"></i></div>'+errorOne+'</div>');
			msgText.show();
		}
	});

	// Load an Admin
	$("#loadAdmin").click(function(e) {
		e.preventDefault();
		
		$("#loadAdmin").addClass('disabled');
		
		// Set some variables
		var adminsId	= $("#selectAdmin").val();
		var adminsName	= $('#selectAdmin option:selected').text();
		var admin_name	= $("#admin_name").find('span');
		var admin_email	= $("#admin_email").find('span');
		var admin_role	= $("#admin_role").find('span');
		var isAdmin		= $("#isAdmin").find('span');
		
		// Get started!
		if (adminsId !== '...') {
			// Make the ajax call
			post_data = {'adminsId':adminsId};
			$.post('includes/auth_f.php', post_data, function(datares) {
				if (datares.indexOf("adminName") > 0) {
					// Admin found, load the data
					var obj = $.parseJSON(datares);
					
					var yesOpt	= $("#yesOpt").val();
					var noOpt	= $("#noOpt").val();
					
					if (obj[0].isAdmin == '1') {
						var superuser = yesOpt;
					} else {
						var superuser = noOpt;
					}
					
					// Display the Admins Name & Email for reference
					admin_name.html("Name: &nbsp; "+obj[0].adminName);
					admin_email.html("Email: &nbsp; "+obj[0].adminEmail);
					admin_role.html("Role: &nbsp; "+obj[0].adminRole);
					isAdmin.html("Superuser: &nbsp; "+superuser);

					// Populate the hidden Admin's ID & Name Field
					$("#adminsId").val(obj[0].adminId);
					$("#adminsName").val(obj[0].adminName);
					$("#theId").val(obj[0].adminId);
					$("#theAdmin").val(obj[0].adminName);
					$("#isadminStatus").val(superuser);
					
					// Set the Admin Status Select Option
					var admStat	= $("#isadminStatus").val();
					$("select#isAdmin option").each(function() { this.selected = (this.text == admStat); });
					
					// Loop through the Auth Flags for the Admin
					$.each($.parseJSON(datares), function(idx, obj) {
						// Set the appropriate checkboxes as checked
						$('#'+obj.authFlag+'').prop('checked', true);
					});
					
					// Show the data
					$(".adminInfo, .superuser").show();
					msgText.hide();
				}
			});
		} else {
			// Show an error
			msgText.html('<div class="alertMsg warning"><div class="msgIcon pull-left"><i class="fa fa-warning"></i></div>'+errorOne+'</div>');
			msgText.show();
			$(".adminInfo, .superuser").hide();
			$("#loadAdmin").removeClass('disabled');
		}
	});
	
	// Reset the Form Data
	$(".resetForm").click(function(e) {
		e.preventDefault();
		$("#selectAdmin option:first").prop("selected", "selected");
		$('input[type="checkbox"]').removeAttr('checked');
		$(".adminInfo, .superuser").hide();
		$("#loadAdmin").removeClass('disabled');
		$("#theId, #theAdmin").val('');
		msgText.hide();
	});
});