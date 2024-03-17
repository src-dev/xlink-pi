<?php
function moveUp(&$array, $i) {
	$p1 = array_splice($array, $i, 1);
	array_splice($array, $i - 1, 0, $p1);
}
function moveDown(&$array, $i) {
	$p1 = array_splice($array, $i, 1);
	array_splice($array, $i + 1, 0, $p1);
}
?>

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
		if(empty($output)) echo '<form method="post" action="wifi.php"><img src="images/warning.png">&nbsp;No networks found!&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="scan">Rescan</button></form>';
		else {
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
			$network = array();
			$network['ssid'] = '"' . $_POST['essid'] . '"';
			if ($_POST['encryption'] == "off") $network['key_mgmt'] = 'NONE';
			else $network['psk'] = '"' . $_POST['psk'] . '"';
			$command = 'sudo python3 confwifi.py a "' . addslashes(json_encode($network)) . '"';
			exec ($command);
			exec ('sudo wpa_cli -i wlan0 reconfigure');
			echo '<img src="images/success.png"> Network configured! Wifi may take a moment to connect.<br/><br/>
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
			$network = array();
			$network['ssid'] = '"' . $_POST['essid'] . '"';
			if (isset($_POST['unsecured'])) $network['key_mgmt'] = 'NONE';
			else $network['psk'] = '"' . $_POST['psk'] . '"';
			if (isset($_POST['hidden'])) $network['scan_ssid'] = '1';
			$command = 'sudo python3 confwifi.py a "' . addslashes(json_encode($network)) . '"';
			exec ($command);
			exec ('sudo wpa_cli -i wlan0 reconfigure');
			echo '<img src="images/success.png"> Network configured! Wifi may take a moment to connect.<br/><br/>
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
				<input type="checkbox" name="hidden">
				<label for="hidden">Hidden Network</label>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="unsecured" id="unsecured"';
			if (isset($_POST['unsecured'])) echo ' checked=""';
			echo '>
				<label for="unsecured">Open (No Password)</label>
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
	} else if (isset($_POST['remembered']) || isset($_POST['move_up']) || isset($_POST['move_down']) || isset($_POST['forget']) || isset($_POST['save'])) {
		$networks = array();
		$saved = false;
		if (isset($_POST['remembered'])) $networks = array_filter(preg_split("(\r\n|\r|\n)", shell_exec('sudo python3 /var/www/html/confwifi.py p')));
		else if (isset($_POST['move_up'])) {
			$networks = unserialize($_POST['networks']);
			moveUp($networks, intval($_POST['position']));
		} else if (isset($_POST['move_down'])) {
			$networks = unserialize($_POST['networks']);
			moveDown($networks, intval($_POST['position']));
		} else if (isset($_POST['forget'])) {
			$networks = unserialize($_POST['networks']);
			unset($networks[intval($_POST['position'])]);
			$networks = array_values($networks);
		} else if (isset($_POST['save'])) {
			$networks = unserialize($_POST['networks']);
			$command = 'sudo python3 /var/www/html/confwifi.py w';
			foreach ($networks as $network) $command .= ' "' . addslashes($network) . '"';
			exec($command);
			$saved = true;
			exec('sudo wpa_cli -i wlan0 reconfigure');
		}
		if (empty($networks)) echo '<form method="post" action="wifi.php"><img src="images/warning.png">&nbsp;No remembered networks found!<br/>';
		else {
			echo '
			<p><strong>Remembered Networks</strong></p>
			<table cellpadding="5">';
			foreach ($networks as $network) {
				$position = array_search($network, $networks);
				$ssid = substr(json_decode($network, false)->ssid, 1, -1);
				echo '
				<tr>
					<form method="post" action="wifi.php">
						<input type="hidden" name="position" value="' . $position . '">
						<input type="hidden" name="networks" value=' . "'" . serialize($networks) . "'" . '>
						<td>' . $ssid . '</td>		
						<td';
				if ($position == 0) echo ' align="right"';
				echo '>';
				if ($position != 0) echo '<button type="submit" name="move_up">↑</button>';
				if ((count($networks) - 1) != $position) echo '<button type="submit" name="move_down">↓</button>';
				echo '
						</td>
						<td>
							<button type="submit" name="forget">Forget</button>
						</td>
					</form>
				</tr>';			
			}
			echo '
			</table>
			<br/>
			<form method="post" action="wifi.php">
				<input type="hidden" name="networks" value=' . "'" . serialize($networks) . "'" . '>
				<button type="submit" name="save">Save</button>';
			if ($saved) echo '&nbsp;&nbsp;<img src="images/success.png"> Networks saved!';
			echo '
			</form>';
		}
		echo '
			<br/>
			<form method="post" action="wifi.php">
				<button type="submit" name="refresh">Go Back</button>
			</form>';
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
			<button type="submit" name="remembered">Remembered Networks</button>
			<br/><br/>
			<button type="submit" name="index">Go Back</button>
		</form>';
	}
	?>
	
</body>
</html>

