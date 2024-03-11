<!DOCTYPE html>
<head>
	<title>Xlink Pi</title>
</head>
<body>
	
	<?php
	if (isset($_POST['index'])) header("Location: index.php");
	if (isset($_POST['refresh'])) $_POST = array();
	if (isset($_POST['stop_kai'])) exec('sudo pkill kaiengine');
	else if (isset($_POST['start_kai'])) if (exec('pidof kaiengine') == "") exec('kaiengine > /dev/null 2>/dev/null &');
	
	$pid = exec('pidof kaiengine');
	$running = true;
	if ($pid == "") $running = false;
	
	
	?>
	
	<h1>Xlink Pi</h1>
	<h3>Kai Management</h3>
	<form method="post" action="kai.php">
		<strong>Status:&nbsp;&nbsp;</strong>
		<?php
		if($running) echo 'Running...';
		else echo 'Stopped';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="refresh">Refresh</button>';
		?>
		<br/><br/>
		<button type="submit" name="start_kai"<?php if($running) echo 'disabled=""'; ?>>Start Engine</button>
		<button type="submit" name="stop_kai"<?php if(!$running) echo 'disabled=""'; ?>>Stop Engine</button>
		<a href="http://<?php echo $_SERVER{'HTTP_HOST'};?>:34522" target="_blank"><button type="button"<?php if(!$running) echo ' disabled=""';?> >Launch Web UI</button></a>
		<br/><br/>
		<button type="submit" name="index">Go Back</button>
	</form>
	
</body>
</html>
