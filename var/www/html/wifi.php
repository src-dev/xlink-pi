<!DOCTYPE html>

<html>
<head>
	<title>Xlink Pi</title>
</head>
<body>

	<h1>Xlink Pi</h1>
	<h3>Wifi Configuration</h3>
	
	<?php
	if (isset($_POST['index'])) header("Location: index.php");
	if (isset($_POST['refresh'])) $_POST = array();
	else {
		$_POST['essid'] = trim($_POST['essid']);
		$_POST['psk'] = trim($_POST['psk']);
	}
	
	if (isset($_POST['scan'])) {
		$output = shell_exec('sudo python3 /var/www/html/parseiwlist.py');
		if(empty($output)) {
			echo '<form method="post" action="wifi.php"><img src="images/warning.png">&nbsp;No networks found!&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="scan">Rescan</button></form>';
		} else {
			$networks = array_filter(preg_split("(\r\n|\r|\n)", $output));
			echo '
			<table cellpadding="5">
				<tr>
					<td colspan="6"><strong>Scan Results</strong></td>
				</tr>
				<tr>
					<td></td>
					<td><strong>SSID</strong></td>					
					<td><strong>Band</strong></td>
					<td><strong>Mac Address</strong></td>
					<td><strong>Encryption</strong></td>
				</tr>';
			foreach ($networks as $network) {
				$network = json_decode($network, false);
				echo '<tr>
					  <td><img src="images/' . $network->signal_bars . 'bars.png"></td>
					  <td>' . $network->essid . '</td>					  
					  <td>' . $network->band . ' GHz</td>
					  <td>' . $network->mac . '</td>
					  <td>' . $network->encryption . '</td>
					  <td>
						  <form method="post" action="wifi.php">
							  <input type="hidden" name="essid" value="' . $network->essid . '">
							  <input type="hidden" name="band" value="' . $network->band . '">
							  <input type="hidden" name="mac" value="' . $network->mac . '">
							  <input type="hidden" name="encryption" value="' . $network->encryption . '">
							  <input type="hidden" name="signal_bars" value="' . $network->signal_bars . '">
							  <button type="submit" name="scan_select">Connect</button>
						  </form>
					  </td></tr>';			
			}
			echo '</table>';
		}
		echo '<br/>
			  <form method="post" action="wifi.php">
			      <button type="submit" name="scan">Rescan</button>
				  <button type="submit" name="refresh">Go Back</button>
			  </form>';
	} else if (isset($_POST['scan_select']) || isset($_POST['scan_connect'])) {
		$connect = false;
		$valid_psk = true;
		if (isset($_POST['scan_connect'])) {
			$connect = true;
			if (strlen($_POST['psk']) < 8 || strlen($_POST['psk']) > 63) $valid_psk = false;
			if ($_POST['encryption'] == "off") $valid_psk = true;
		}
		if ($connect && $valid_psk) {
			$command = 'sudo python3 confwifi.py "ssid=\"' . $_POST['essid'] . '\""';
			if ($_POST['encryption'] == "off") $command .= ' "key_mgmt=NONE"';
			else $command .= ' "psk=\"' . $_POST['psk'] . '\""';
			exec ($command);
			echo '<img src="images/success.png"> Network reconfigured! Wifi may take a moment to connect.<br/><br/>
				  <form method="post" action="wifi.php"><button type="submit" name="refresh">Go Back</button></form>';
		} else {
			echo '
			<table cellpadding="5">
				<tr>
					<td></td>
					<td><strong>SSID</strong></td>					
					<td><strong>Band</strong></td>
					<td><strong>Mac Address</strong></td>
					<td><strong>Encryption</strong></td>
				</tr>
				<tr>
					<td><img src="images/' . $_POST['signal_bars'] . 'bars.png"></td>
					<td>' . $_POST['essid'] . '</td>					  
					<td>' . $_POST['band'] . ' GHz</td>
					<td>' . $_POST['mac'] . '</td>
					<td>' . $_POST['encryption'] . '</td>
				</tr>
			</table>
			<br/>
			<form method="post" action="wifi.php">
				<input type="hidden" name="essid" value="' . $_POST['essid'] . '">
				<input type="hidden" name="band" value="' . $_POST['band'] . '">
				<input type="hidden" name="mac" value="' . $_POST['mac'] . '">
				<input type="hidden" name="encryption" value="' . $_POST['encryption'] . '">
				<input type="hidden" name="signal_bars" value="' . $_POST['signal_bars'] . '">';
			if ($_POST['encryption'] != "off") echo '	
				<label for="psk">Password: </label>
				<input type="password" name="psk" value="' . $_POST['psk'] . '" autofocus="">&nbsp;';
			echo '
				<button type="submit" name="scan_connect">Connect</button>
				<br/><br/>
				<button type="submit" name="refresh">Go Back</button>
			</form>
			<br/>';
			if (!$valid_psk) echo '<img src="images/error.png"/>&nbsp;Invalid password length';
		}
	} else if (isset($_POST['manual']) || isset($_POST['manual_connect'])) {
		$connect = false;
		$valid_ssid = true;
		$valid_psk = true;
		if (isset($_POST['manual_connect'])) {
			$connect = true;
			if (strlen($_POST['essid']) < 2 || strlen($_POST['essid']) > 32) $valid_ssid = false;
			if (strlen($_POST['psk']) < 8 || strlen($_POST['psk']) > 63) $valid_psk = false;
			if (isset($_POST['unsecured'])) $valid_psk = true;
		}
		if ($connect && $valid_ssid && $valid_psk) {
			$command = 'sudo python3 confwifi.py "ssid=\"' . $_POST['essid'] . '\""';
			if (isset($_POST['unsecured'])) $command .= ' key_mgmt=NONE';
			else $command .= ' "psk=\"' . $_POST['psk'] . '\""';
			exec ($command);
			echo '<img src="images/success.png"> Network reconfigured! Wifi may take a moment to connect.<br/><br/>
				  <form method="post" action="wifi.php"><button type="submit" name="refresh">Go Back</button></form>';
		} else {
			echo '
			<form method="post" action="wifi.php">
				<label for="essid">SSID: </label>
				<input type="text" name="essid" value="' . $_POST['essid'] . '"';
			if (!isset($_POST['essid']) || empty($_POST['essid'])) echo ' autofocus=""';
			echo '>
				<label for="psk">Password: </label>
				<input type="password" name="psk" id="psk" value="' . $_POST['psk'] . '" autofocus=""';
			if ($_POST['encryption'] == "off") echo ' disabled=""';
			echo '>
				<br/><br/>
				<input type="checkbox" name="unsecured" id="unsecured"';
			if (isset($_POST['unsecured'])) echo ' checked=""';
			echo '>
				<label for="unsecured">Unsecured</label>
				<br/><br/>
				<button type="submit" name="manual_connect">Connect</button>
				<button type="submit" name="refresh">Go Back</button>
			</form><br/>';
			if (!$valid_ssid) echo '<img src="images/error.png"/>&nbsp;Invalid SSID length&nbsp;&nbsp;&nbsp;&nbsp;';
			if (!$valid_psk) echo '<img src="images/error.png"/>&nbsp;Invalid password length';
			echo'
			<script>
				window.onload = function() {
					document.getElementById("psk").disabled = document.getElementById("unsecured").checked;
				};
				document.getElementById("unsecured").onchange = function() {
					document.getElementById("psk").disabled = this.checked;
				};
			</script>';
		}
	} else {
		echo '
		<form method="post" action="wifi.php">
			<strong>Status:&nbsp;&nbsp;</strong>';
		$mac = exec('iwgetid -a -r');
		if (empty($mac)) echo 'No connection';
		else {
			$network = json_decode(exec('python3 parseiwlist.py ' . $mac), false);
			echo 'Connected...&nbsp;&nbsp;<img src="images/' . $network->signal_bars . 'bars.png">&nbsp;&nbsp;' . $network->essid;
		}
		echo '
			&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="refresh">Refresh</button>
			<br/><br/>
			<button type="submit" name="scan">Scan</button>
			<button type="submit" name="manual">Manual</button>
			<br/><br/>
			<button type="submit" name="index">Go Back</button>
		</form>';
	}
	?>
	
</body>
</html>

