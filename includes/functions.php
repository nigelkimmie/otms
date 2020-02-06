<?php
	/*
     * Function to return an array of Authorizations for the logged in Admin
     *
     * @param string $aid		 	The Admin's ID
	 * @return array           		An array of values from the appauth table
     */
	function getAdminAuth($aid) {
		global $mysqli;
		$auths = array();

		$authqry = "SELECT * FROM appauth WHERE adminId = ".$aid;
		$authres = mysqli_query($mysqli, $authqry) or die('Error: getAdminAuth() Function'.mysqli_error());

		while($authrow = mysqli_fetch_assoc($authres)) {
			$authrows = array_map(null, $authrow);
			$auths[] = $authrows;
		}
		return $auths;
	}

	/*
     * Function to check an array for a specific value
     *
     * @param string $val		 	The value to search for
     * @param var $arr			 	The array to search
	 * @return true/false           Boolen
     */
	function checkArray($val, $arr) {
		if (in_array($val, $arr)) {
			return true;
		}
		foreach($arr as $k) {
			if (is_array($k) && checkArray($val, $k)) {
				return true;
			}
		}
		return false;
	}

	/*
     * Functions to format Dates and/or Times from the database
	 * http://php.net/manual/en/function.date.php for a full list of format characters
	 * Uncomment (remove the double slash - //) from the one you want to use
	 * Comment (Add a double slash - //) to the front of the ones you do NOT want to use
     *
     * @param string $v   		The database value (ie. 2014-10-31 20:00:00)
     * @return string           The formatted Date and/or Time
     */
	function dateFormat($v) {
		// $theDate = date("Y-m-d",strtotime($v));				// 2014-10-31
		// $theDate = date("m-d-Y",strtotime($v));				// 10-31-2014
		$theDate = date("F d, Y",strtotime($v));				// October 31, 2014
		return $theDate;
	}
	function dateTimeFormat($v) {
		// $theDateTime = date("Y-m-d g:i a",strtotime($v));	// 2014-10-31 8:00 pm
		// $theDateTime = date("m-d-Y g:i a",strtotime($v));	// 10-31-2014 8:00 pm
		$theDateTime = date("F d, Y g:i a",strtotime($v));		// October 31, 2014 8:00 pm
		return $theDateTime;
	}
	function shortDateFormat($v) {
		$theDateTime = date("m/d/Y",strtotime($v));				// 10/31/2014
		return $theDateTime;
	}
	function shortDateTimeFormat($v) {
		$theDateTime = date("m/d/Y g:i a",strtotime($v));		// 10/31/2014 8:00 pm
		return $theDateTime;
	}
	function timeFormat($v) {
		$theTime = date("g:i a",strtotime($v));					// 8:00 pm
		return $theTime;
	}
	function dbDateFormat($v) {
		$theTime = date("Y-m-d",strtotime($v));					// 2014-10-31
		return $theTime;
	}
	function dbTimeFormat($v) {
		$theTime = date("H:i",strtotime($v));					// 20:00
		return $theTime;
	}

	function shortDate($v) {
		$theDateTime = date("m/d g:i a",strtotime($v));			// 10/31 8:00 pm
		return $theDateTime;
	}

	/*
     * Function to convert a UNIX Timestamp to a Time Ago
     *
     * @param string $datetime	The Unix Timestamp
     */
	function timeago($date) {
		if (empty($date)) {
			return "No date provided";
		}
		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");

		$now = time();
		$unix_date = strtotime($date);

		// check validity of date
		if (empty($unix_date)) {
			return "";
		}

		// is it future date or past date
		if ($now > $unix_date) {
			$difference = $now - $unix_date;
			$tense = "ago";
		} else {
			$difference = $unix_date - $now;
			$tense = "from now";
		}

		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if ($difference != 1) {
			$periods[$j].= "s";
		}

		return "$difference $periods[$j] {$tense}";
	}

	/*
     * Function to Format Currency Amounts for different languages
     *
     * @param string $floatcurr   	The number to format
     * @param string $curr   		The Currency Code
     * @return string      			The Formatted Currency Amount
     */
    function formatCurrency($floatcurr, $curr = ''){
        $currencies['ARS'] = array(2,',','.');          //  Argentine Peso
        $currencies['AMD'] = array(2,'.',',');          //  Armenian Dram
        $currencies['AWG'] = array(2,'.',',');          //  Aruban Guilder
        $currencies['AUD'] = array(2,'.',' ');          //  Australian Dollar
        $currencies['BSD'] = array(2,'.',',');          //  Bahamian Dollar
        $currencies['BHD'] = array(3,'.',',');          //  Bahraini Dinar
        $currencies['BDT'] = array(2,'.',',');          //  Bangladesh, Taka
        $currencies['BZD'] = array(2,'.',',');          //  Belize Dollar
        $currencies['BMD'] = array(2,'.',',');          //  Bermudian Dollar
        $currencies['BOB'] = array(2,'.',',');          //  Bolivia, Boliviano
        $currencies['BAM'] = array(2,'.',',');          //  Bosnia and Herzegovina, Convertible Marks
        $currencies['BWP'] = array(2,'.',',');          //  Botswana, Pula
        $currencies['BRL'] = array(2,',','.');          //  Brazilian Real
        $currencies['BND'] = array(2,'.',',');          //  Brunei Dollar
        $currencies['CAD'] = array(2,'.',',');          //  Canadian Dollar
        $currencies['KYD'] = array(2,'.',',');          //  Cayman Islands Dollar
        $currencies['CLP'] = array(0,'','.');           //  Chilean Peso
        $currencies['CNY'] = array(2,'.',',');          //  China Yuan Renminbi
        $currencies['COP'] = array(2,',','.');          //  Colombian Peso
        $currencies['CRC'] = array(2,',','.');          //  Costa Rican Colon
        $currencies['HRK'] = array(2,',','.');          //  Croatian Kuna
        $currencies['CUC'] = array(2,'.',',');          //  Cuban Convertible Peso
        $currencies['CUP'] = array(2,'.',',');          //  Cuban Peso
        $currencies['CYP'] = array(2,'.',',');          //  Cyprus Pound
        $currencies['CZK'] = array(2,'.',',');          //  Czech Koruna
        $currencies['DKK'] = array(2,',','.');          //  Danish Krone
        $currencies['DOP'] = array(2,'.',',');          //  Dominican Peso
        $currencies['XCD'] = array(2,'.',',');          //  East Caribbean Dollar
        $currencies['EGP'] = array(2,'.',',');          //  Egyptian Pound
        $currencies['SVC'] = array(2,'.',',');          //  El Salvador Colon
        $currencies['ATS'] = array(2,',','.');          //  Euro
        $currencies['BEF'] = array(2,',','.');          //  Euro
        $currencies['DEM'] = array(2,',','.');          //  Euro
        $currencies['EEK'] = array(2,',','.');          //  Euro
        $currencies['ESP'] = array(2,',','.');          //  Euro
        $currencies['EUR'] = array(2,',','.');          //  Euro
        $currencies['FIM'] = array(2,',','.');          //  Euro
        $currencies['FRF'] = array(2,',','.');          //  Euro
        $currencies['GRD'] = array(2,',','.');          //  Euro
        $currencies['IEP'] = array(2,',','.');          //  Euro
        $currencies['ITL'] = array(2,',','.');          //  Euro
        $currencies['LUF'] = array(2,',','.');          //  Euro
        $currencies['NLG'] = array(2,',','.');          //  Euro
        $currencies['PTE'] = array(2,',','.');          //  Euro
        $currencies['GHC'] = array(2,'.',',');          //  Ghana, Cedi
        $currencies['GIP'] = array(2,'.',',');          //  Gibraltar Pound
        $currencies['GTQ'] = array(2,'.',',');          //  Guatemala, Quetzal
        $currencies['HNL'] = array(2,'.',',');          //  Honduras, Lempira
        $currencies['HKD'] = array(2,'.',',');          //  Hong Kong Dollar
        $currencies['HUF'] = array(0,'','.');           //  Hungary, Forint
        $currencies['ISK'] = array(0,'','.');           //  Iceland Krona
        $currencies['INR'] = array(2,'.',',');          //  Indian Rupee
        $currencies['IDR'] = array(2,',','.');          //  Indonesia, Rupiah
        $currencies['IRR'] = array(2,'.',',');          //  Iranian Rial
        $currencies['JMD'] = array(2,'.',',');          //  Jamaican Dollar
        $currencies['JPY'] = array(0,'',',');           //  Japan, Yen
        $currencies['JOD'] = array(3,'.',',');          //  Jordanian Dinar
        $currencies['KES'] = array(2,'.',',');          //  Kenyan Shilling
        $currencies['KWD'] = array(3,'.',',');          //  Kuwaiti Dinar
        $currencies['LVL'] = array(2,'.',',');          //  Latvian Lats
        $currencies['LBP'] = array(0,'',' ');           //  Lebanese Pound
        $currencies['LTL'] = array(2,',',' ');          //  Lithuanian Litas
        $currencies['MKD'] = array(2,'.',',');          //  Macedonia, Denar
        $currencies['MYR'] = array(2,'.',',');          //  Malaysian Ringgit
        $currencies['MTL'] = array(2,'.',',');          //  Maltese Lira
        $currencies['MUR'] = array(0,'',',');           //  Mauritius Rupee
        $currencies['MXN'] = array(2,'.',',');          //  Mexican Peso
        $currencies['MZM'] = array(2,',','.');          //  Mozambique Metical
        $currencies['NPR'] = array(2,'.',',');          //  Nepalese Rupee
        $currencies['ANG'] = array(2,'.',',');          //  Netherlands Antillian Guilder
        $currencies['ILS'] = array(2,'.',',');          //  New Israeli Shekel
        $currencies['TRY'] = array(2,'.',',');          //  New Turkish Lira
        $currencies['NZD'] = array(2,'.',',');          //  New Zealand Dollar
        $currencies['NOK'] = array(2,',','.');          //  Norwegian Krone
        $currencies['PKR'] = array(2,'.',',');          //  Pakistan Rupee
        $currencies['PEN'] = array(2,'.',',');          //  Peru, Nuevo Sol
        $currencies['UYU'] = array(2,',','.');          //  Peso Uruguayo
        $currencies['PHP'] = array(2,'.',',');          //  Philippine Peso
        $currencies['PLN'] = array(2,'.',' ');          //  Poland, Zloty
        $currencies['GBP'] = array(2,'.',',');          //  Pound Sterling
        $currencies['OMR'] = array(3,'.',',');          //  Rial Omani
        $currencies['RON'] = array(2,',','.');          //  Romania, New Leu
        $currencies['ROL'] = array(2,',','.');          //  Romania, Old Leu
        $currencies['RUB'] = array(2,',','.');          //  Russian Ruble
        $currencies['SAR'] = array(2,'.',',');          //  Saudi Riyal
        $currencies['SGD'] = array(2,'.',',');          //  Singapore Dollar
        $currencies['SKK'] = array(2,',',' ');          //  Slovak Koruna
        $currencies['SIT'] = array(2,',','.');          //  Slovenia, Tolar
        $currencies['ZAR'] = array(2,'.',' ');          //  South Africa, Rand
        $currencies['KRW'] = array(0,'',',');           //  South Korea, Won
        $currencies['SZL'] = array(2,'.',', ');         //  Swaziland, Lilangeni
        $currencies['SEK'] = array(2,',','.');          //  Swedish Krona
        $currencies['CHF'] = array(2,'.','\'');         //  Swiss Franc
        $currencies['TZS'] = array(2,'.',',');          //  Tanzanian Shilling
        $currencies['THB'] = array(2,'.',',');          //  Thailand, Baht
        $currencies['TOP'] = array(2,'.',',');          //  Tonga, Paanga
        $currencies['AED'] = array(2,'.',',');          //  UAE Dirham
        $currencies['UAH'] = array(2,',',' ');          //  Ukraine, Hryvnia
        $currencies['USD'] = array(2,'.',',');          //  US Dollar
        $currencies['VUV'] = array(0,'',',');           //  Vanuatu, Vatu
        $currencies['VEF'] = array(2,',','.');          //  Venezuela Bolivares Fuertes
        $currencies['VEB'] = array(2,',','.');          //  Venezuela, Bolivar
        $currencies['VND'] = array(0,'','.');           //  Viet Nam, Dong
        $currencies['ZWD'] = array(2,'.',' ');          //  Zimbabwe Dollar

        return number_format($floatcurr,$currencies[$curr][0],$currencies[$curr][1],$currencies[$curr][2]);
    }

    /*
     * Function to show an Alert type Message Box
     *
     * @param string $msg   	The Alert Message
     * @param string $icon      The Font Awesome Icon
     * @param string $type      The CSS style to apply
     * @return string           The Alert Box
     */
    function alertBox($msg, $icon = "", $type = "") {
        return "
				<div class=\"alertMsg $type\">
					<div class=\"msgIcon pull-left\">$icon</div>
					$msg
					<a class=\"msgClose\" title=\"Close\" href=\"#\"><i class=\"fa fa-times\"></i></a>
				</div>
			";
    }

	/*
     * Function to Strip HTML Tags but allowed
     *
     * @param string $text      The text to be stripped
     * @return string           The allowed text
     */
	function allowedHTML($text = "") {
		if (!is_string($text) || strlen($text) < 1) {
			return "";
		}

		// Strip all html tags except allowed
		// Allowed: h3, h4, p, br, a, strong, em, i, ul, ol, li, blockquote, strikethrough
		$text = strip_tags($text, '<h1><h2><h3><h4><p><br /><a><b><strong><em><i><ul><ol><li><blockquote><strike>');

		// Clean out forward slashes
		$text = str_replace( '\\', '', $text );

		// Strip out all paragraphs with attributes (classes, Id's etc.)
		$text = preg_replace("/<p[^>]*>/", '<p>', $text);

		return $text;
	}

	/*
     * Function to Strip HTML Tags but allowed
     *
     * @param string $text      The text to be stripped
     * @return string           The allowed text
     */
	function allowedTags($text = "") {
		if (!is_string($text) || strlen($text) < 1) {
			return "";
		}

		// Strip all html tags except allowed
		// Allowed: h3, h4, p, br, a, strong, em, i, ul, ol, li, blockquote, strikethrough
		$text = strip_tags($text, '<div><h3><h4><p><br><a><strong><em><i><hr><ul><ol><li><blockquote><strike>');

		// Clean out forward slashes
		$text = str_replace( '\\', '', $text );

		return $text;
	}

    /*
     * Function to ellipse-ify text to a specific length
     *
     * @param string $text      The text to be ellipsified
     * @param int    $max       The maximum number of characters (to the word) that should be allowed
     * @param string $append    The text to append to $text
     * @return string           The shortened text
     */
    function ellipsis($text, $max = '', $append = '&hellip;') {
        if (strlen($text) <= $max) {
			return $text;
		}

        $replacements = array(
            '|<br /><br />|' => ' ',
            '|&nbsp;|' => ' ',
            '|&rsquo;|' => '\'',
            '|&lsquo;|' => '\'',
            '|&ldquo;|' => '"',
            '|&rdquo;|' => '"',
        );

        $patterns = array_keys($replacements);
        $replacements = array_values($replacements);

        // Convert double newlines to spaces.
        $text = preg_replace($patterns, $replacements, $text);

        // Remove any HTML.  We only want text.
        $text = strip_tags($text);

        $out = substr($text, 0, $max);
        if (strpos($text, ' ') === false) {
			return $out.$append;
		}

        return preg_replace('/(\W)&(\W)/', '$1&amp;$2', (preg_replace('/\W+$/', ' ', preg_replace('/\w+$/', '', $out)))).$append;
    }

    /*
     * Function to Encrypt sensitive data for storing in the database
     *
     * @param string	$value		The text to be encrypted
	 * @param 			$encodeKey	The Key to use in the encryption
     * @return						The encrypted text
     */
	function encryptIt($value) {
		// The encodeKey MUST match the decodeKey
		$encodeKey = 'DvHtl3CGp4QLuuOEtBQ2AS';
		$encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($encodeKey), $value, MCRYPT_MODE_CBC, md5(md5($encodeKey))));

		return($encoded);
	}

    /*
     * Function to decrypt sensitive data from the database for displaying
     *
     * @param string	$value		The text to be decrypted
	 * @param 			$decodeKey	The Key to use for decryption
     * @return						The decrypted text
     */
	function decryptIt($value) {
		// The decodeKey MUST match the encodeKey
		$decodeKey = 'DvHtl3CGp4QLuuOEtBQ2AS';
		$decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($decodeKey), base64_decode($value), MCRYPT_MODE_CBC, md5(md5($decodeKey))), "\0");

		return($decoded);
	}

	/*
     * Function to strip slashes for displaying database content
     *
     * @param string	$value		The string to be stripped
     * @return						The stripped text
     */
	function clean($value) {
		$str = str_replace('\\', '', $value);

		return $str;
	}

	/*
     * Function to strip punctuation from a string of characters
     *
     * @param string	$value		The string to be stripped
     * @return						The stripped text
     */
	function strip($value) {
		$str = preg_replace('/[\W]+/', ' ', $value);

		return $str;
	}

	/*
     * Function to insert Recent Activity
     *
     * @param string $aid		 	The Admin's ID
     * @param string $uid	 		The User's ID
     * @param string $type		 	The Activity Type
     * @param string $title			The Activity Title
     */
	function updateActivity($aid,$uid,$type,$title) {
		global $mysqli;
		
		$activityIp = $_SERVER['REMOTE_ADDR'];

		$stmt = $mysqli->prepare("
							INSERT INTO
								activity(
									adminId,
									userId,
									activityType,
									activityTitle,
									activityDate,
									ipAddress
								) VALUES (
									?,
									?,
									?,
									?,
									NOW(),
									?
								)
		");
		$stmt->bind_param('sssss',
							$aid,
							$uid,
							$type,
							$title,
							$activityIp
		);
		$stmt->execute();
		$stmt->close();
	}
	
	/*
     * Function to get a CSV of emails for use in notifications to Tenants
     *
     * @param string $pid		 	The Property ID
     * @param string $siteEmail		The Site's Email
     * @return string	 			The CSV of email address
     */
	function assignedAdmins($pid,$siteEmail) {
		global $mysqli;
		
		$assignedsql = "SELECT
							admins.adminEmail
						FROM
							admins
							LEFT JOIN assigned ON admins.adminId = assigned.adminId
						WHERE assigned.propertyId = ".$pid;
		$assignedresult = mysqli_query($mysqli, $assignedsql) or die('Error, retrieving Assigned Admin email list failed. ' . mysqli_error());

		// Set each admin email into a csv
		$assignedEmails = array();
		while ($asnd = mysqli_fetch_assoc($assignedresult)) {
			$assignedEmails[] = $asnd['adminEmail'];
			array_push($assignedEmails, $siteEmail);
		}
		$assignedList = implode(',',$assignedEmails);
		
		return $assignedList;
	}
	
	/*
     * Function to get a CSV of emails for use in Service Request notifications to Tenants
     *
     * @param string $pid		 	The Property ID
     * @param string $admnEmail		The Assigned Admin's Email
     * @param string $siteEmail		The Site's Email
     * @return string	 			The CSV of email address
     */
	function serviceManagers($pid,$admnEmail,$siteEmail) {
		global $mysqli;
		
		$servicesql = "SELECT
						admins.adminEmail
					FROM
						admins
						LEFT JOIN servicerequests ON admins.adminId = servicerequests.assignedTo
					WHERE servicerequests.propertyId = ".$pid;
		$serviceresult = mysqli_query($mysqli, $servicesql) or die('Error, retrieving Service Request email list failed. ' . mysqli_error());

		// Set each admin email into a csv
		$serviceEmails = array();
		while ($srv = mysqli_fetch_assoc($serviceresult)) {
			$serviceEmails[] = $srv['adminEmail'];
			array_push($serviceEmails, $admnEmail, $siteEmail);
		}
		$serviceList = implode(',',$serviceEmails);
		
		return $serviceList;
	}
	
	/*
     * Function to get a CSV of emails for use in notifications to Tenants
     *
     * @return string	 			The CSV of email address
     */
	function emailUsers() {
		global $mysqli;
		
		$tenantsql = "SELECT userEmail FROM users WHERE isActive = 1";
		$tenantresult = mysqli_query($mysqli, $tenantsql) or die('Error, retrieving User email list failed. ' . mysqli_error());

		// Set each Tenant email into a csv
		$emailTenants = array();
		while ($tenants = mysqli_fetch_assoc($tenantresult)) {
			$emailTenants[] = $tenants['userEmail'];
		}
		$allTenants = implode(',',$emailTenants);
		
		return $allTenants;
	}