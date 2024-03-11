<!DOCTYPE html>
<head>
	<title>Xlink Pi</title>
</head>
<body>

	<h1>Xlink Pi</h1>
	<h3>Wifi Configuration</h3>
	<?php
	if (isset($_POST['index'])) header("Location: index.php");
	if (isset($_POST['refresh'])) $_POST = array();
	if (isset($_POST['go_back'])) $_POST = array();
	if (isset($_POST['search_button'])) {
		$output = shell_exec('sudo python3 /var/www/html/iwlistparse.py');
		if(empty($output)) {
			echo '<form method="post" action="wifi.php"><img src="images/warning.png">&nbsp;No networks found!&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="search_button">Rescan</button></form>';
		} else {
			$networks = array_filter(preg_split("(\r\n|\r|\n)", $output));
			echo '
			<table cellpadding="5">
				<tr>
					<td colspan="4" align="center"><strong>Scan Results</strong></td>
				</tr>
				<tr>
					<td></td>
					<td><strong>SSID</strong></td>
					<td><strong>Encryption</strong></td>
				</tr>';
			foreach ($networks as $network) {
				$network = json_decode($network, false);
				echo $network->bars;
				echo '<tr>';
				echo '<td><img src="images/' . $network->signal_bars . 'bars.png"></td>';
				echo '<td>' . $network->essid . '</td>';
				echo '<td>' . $network->encryption . '</td>';
				echo '<td><form method="post" action="wifi.php"><input type="hidden" name="ssid_field" value="' . $network->essid . '"><button type="submit" name="manual_button">Connect</button></form></td>';
				echo '</tr>';			
			}
			echo '
			</table>';
		}
		echo '<br/><form method="post" action="wifi.php"><button type="submit" name="go_back">Go Back</button></form>';
	} else if (isset($_POST['manual_button']) || isset($_POST['connect_button'])) {
		$connect = false;
		$valid_ssid = true;
		$valid_psk = true;
		if (isset($_POST['connect_button'])) {
			$connect = true;
			if (strlen(trim($_POST['ssid_field'])) < 2 || strlen(trim($_POST['ssid_field'])) > 32) $valid_ssid = false;
			if (strlen(trim($_POST['psk_field'])) < 8 || strlen(trim($_POST['psk_field'])) > 63) $valid_psk = false;
		}
		if ($connect && $valid_ssid && $valid_psk) {
			echo '<img src="images/success.png" alt="Success!"> Network reconfigured! Wifi may take a moment to connect.<br/><br/>';
			exec ('sudo python3 /var/www/html/configurewifi.py ' . $_POST['ssid_field'] . ' ' . $_POST['psk_field']);
			echo '<form method="post" action="wifi.php"><button type="submit" name="go_back">Go Back</button></form>';
		} else {
			echo '<form method="post" action="wifi.php">
					  <label for="ssid_field">SSID: </label>
					  <input type="text" name="ssid_field" value="' . $_POST['ssid_field'] . '"';
					  if (!isset($_POST['ssid_field']) || $_POST['ssid_field'] == "") echo ' autofocus=""';
				echo '>
					  <label for="psk_field">Password: </label>
					  <input type="password" name="psk_field" value="' . $_POST['psk_field'] . '" autofocus="">
					  <br/><br/>
					  <button type="submit" name="connect_button">Connect</button>
					  <button type="submit" name="go_back">Go Back</button>
				  </form>
				  <br/>';
			if (!$valid_ssid) echo '<img src="images/error.png"/>&nbsp;Invalid SSID length&nbsp;&nbsp;&nbsp;&nbsp;';
			if (!$valid_psk) echo '<img src="images/error.png"/>&nbsp;Invalid password length';
		}
	} else {
		echo '<form method="post" action="wifi.php">
				  <strong>Status:&nbsp;&nbsp;</strong>';
				  if (empty(exec('iwgetid -r'))) echo 'No connection';
				  else {
					$network = json_decode(exec('python3 iwlistparse.py'), false);
					echo 'Connected...&nbsp;&nbsp;<img src="images/' . $network->signal_bars . 'bars.png">&nbsp;&nbsp;' . $network->essid;
				  }
				  echo '
				  &nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="refresh">Refresh</button>
				  <br/><br/>
				  <button type="submit" name="search_button">Scan</button>
				  <button type="submit" name="manual_button">Manual</button>
				  <br/><br/>
				  <button type="submit" name="index">Go Back</button>
			  </form>';
	}
	?>
	
</body>
</html>

