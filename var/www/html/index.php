<!DOCTYPE html>

<html>
<head>
	<title>Xlink Pi</title>
</head>
<body>

	<h1>Xlink Pi</h1>
	
	<?php
	if (isset($_POST['wifi'])) header("Location: wifi.php");
	if (isset($_POST['shell'])) header("Location: shell.php");
	if (isset($_POST['kai'])) header("Location: kai.php");
	if (isset($_POST['refresh'])) $_POST = array();
	
	if (isset($_POST['reboot'])) {
		echo '
			<h3>Reboot Pi</h3>
			<form method="post" action="index.php">
				Are you sure?&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="submit" name="yes_confirm">Yes</button>
				<button type="submit" name="no_confirm">No</button>
			</form>';
	} else if (isset($_POST['yes_confirm'])) exec('sudo reboot');
	else {
		echo '<h3>Status</h3>
			  <strong>Wifi:&nbsp;&nbsp;</strong>';
		$mac = exec('iwgetid -a -r');
		if (empty($mac)) echo 'No connection';
		else {
			$network = json_decode(exec('python3 iwlistparse.py ' . $mac), false);
			echo 'Connected...&nbsp;&nbsp;<img src="images/' . $network->signal_bars . 'bars.png">&nbsp;&nbsp;' . $network->essid;
		}
		echo '<br/><br/><strong>Kai Engine:&nbsp;&nbsp;</strong>';
		if (empty(exec('pidof kaiengine'))) echo 'Stopped';	
		else echo 'Running...';
		echo '
		<br/><br/>
		<form method="post" action="index.php">
			<button type="submit" name="wifi">Wifi Configuration</button>
			<button type="submit" name="kai">Kai Management</button>
			<button type="submit" name="shell">Pi Shell</button>
			<button type="submit" name="reboot">Reboot Pi</button>
			<br/></br>
			<button type="submit" name="refresh">Refresh</button>
		</form>';
	}
	?>
	
</body>

</html>

