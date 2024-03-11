<!DOCTYPE html>
<head>
	<title>Xlink Pi</title>
</head>
<body>
	
	<?php
	if (isset($_POST['index'])) header("Location: index.php");
	if (isset($_POST['clear'])) $_POST = array();
	else if(isset($_POST['send_cmd'])) {
		$output = shell_exec($_POST['cmd']);
		$_POST['shell'] .= $output;
	}
	?>
	
	<h1>Xlink Pi</h1>
	<h3>Pi Shell</h3>
	<p><img src="images/warning.png"><span style="color:red"> WARNING! This shell runs commands directly on the Pi. Use at your own risk!</span><br/>
	This shell can accept 'sudo' commands.</p>
	<form method="post" action="shell.php">
		<table>
			<tr>
				<td colspan="2"><textarea rows="30" cols="100" name="shell" readonly=""><?php print $_POST['shell']; ?></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><input size="100" type="text" name="cmd" value="" autofocus=""></td>
			</tr>
			<tr>
				<td>
					<button type="submit" name="index">Go Back</button>
				</td>
				<td align="right">
					<button type="submit" align="right" name="send_cmd">Send</button>
					<button type="submit" name="clear">Clear</button>
				</td>
			</tr>
		</table>
	</form>
	
</body>
</html>

