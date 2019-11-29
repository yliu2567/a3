<?php
require 'Db.class.php';
$db_instance = new Db();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$licensenumber = $_GET['doctor'];

	// treating patients
	$db_instance->bind('TREAT_BY', $licensenumber);
	$patients = $db_instance->query('SELECT `patient`.* FROM `treats` LEFT JOIN `patient` ON `patient_ohip` = `ohip` WHERE `treated_by` = :TREAT_BY');
	
	// other patients
	$db_instance->bind('TREAT_BY', $licensenumber);
	$others = $db_instance->query('SELECT DISTINCT `patient`.`ohip`, `patient`.`fname`, `patient`.`lname` FROM `treats` LEFT JOIN `patient` ON `patient_ohip` = `ohip` WHERE `patient_ohip` NOT IN (SELECT `patient_ohip` FROM `treats` WHERE `treated_by` = :TREAT_BY)');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Management System</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
</head>
<body>
	<?php include('header.php'); ?>
	<div class="container">
		<input type="hidden" name="doctor" id="doctor" value="<?php echo $licensenumber; ?>">
		<div class="form-inline">
			<label for="ohip">Patient:</label>
			<select class="form-control" name="ohip" id="ohip">
				<?php
					foreach ($others as $patient) {
						echo '<option value="' . $patient['ohip'] . '">' . $patient['fname'] . ' ' . $patient['lname'] . '</option>';
					}
				?>
			</select>
			<a class="btn btn-primary" onclick="addPatient()" href="javascript:void(0);" role="button">Add</a>
		</div>
		<div class="clearfix" style="margin-bottom: 10px;"></div>
		<table class="table table-striped">
			<caption>Your patients</caption>
			<thead>
				<tr>
					<th>OHIP</th>
					<th>Name</th>
					<th>Operation</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($patients as $patient) {
						echo '<tr>';
						echo '<td>' . $patient['ohip'] . '</td>';
						echo '<td>' . $patient['fname'] . ' ' .$patient['lname'] . '</td>';
						echo '<td><a onclick="stop(\'' . $patient['ohip'] . '\');">Stop</a></td>';
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</body>
<script type="text/javascript">
	function addPatient()
	{
		$.ajax({
			url: 'handle_treat.php',
			type: 'post',
			dataType: 'json',
			data: {doctor: $('#doctor').val(), patient: $('#ohip').val(), type: 1},
			success: function(res) {
				if (res.code === 1) {
					alert(res.msg);
				} else {
					window.location.reload();
				}
			},
			error: function() {
				alert('An unexpected network error occurred !');
			}
		});
	}

	function stop(ohip)
	{
		$.ajax({
			url: 'handle_treat.php',
			type: 'post',
			dataType: 'json',
			data: {doctor: $('#doctor').val(), patient: ohip, type: 0},
			success: function(res) {
				if (res.code === 1) {
					alert(res.msg);
				} else {
					window.location.reload();
				}
			},
			error: function() {
				alert('An unexpected network error occurred !');
			}
		});
	}
</script>
</html>