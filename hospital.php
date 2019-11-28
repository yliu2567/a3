<?php
require 'Db.class.php';
$db_instance = new Db();

// hospital data
$hospitals = $db_instance->query('SELECT * FROM `hospital` LEFT JOIN `doctor` ON `currenthead` = `licensenumber` ORDER BY `name`');
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
		<div class="row">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Hospital</th>
						<th>Beds</th>
						<th>Head Doctor</th>
						<th>Start Date</th>
						<th>Operation</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($hospitals as $hospital) {
							echo '<tr>';
							echo '<td>' . $hospital['name'] . ' (' . $hospital['city'] . ' ' . $hospital['province'] . ')</td>';
							echo '<td>' . $hospital['number_of_beds'] . '</td>';
							echo '<td>' . $hospital['fname'] . ' ' .$hospital['lname'] . '</td>';
							echo '<td>' . $hospital['start_date'] . '</td>';
							echo '<td><a onclick="edit(\'' . $hospital['code'] . '\', \'' . $hospital['name'] . '\');">Edit</a></td>';
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
		</div>
		<!-- （Modal） -->
		<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<form class="form-horizontal">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="editModalLabel">Hospital's information</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="name" class="col-sm-4 control-label">Name</label>
								<div class="col-sm-6">
									<input type="hidden" name="code" id="code">
									<input type="text" class="form-control" id="name" name="name" placeholder="Hospital Name">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" onclick="chgname()">Save</button>
						</div>
					</div><!-- /.modal-content -->
				</form>
			</div><!-- /.modal -->
		</div>
	</div>
</body>
<script type="text/javascript">
	function edit(code, name)
	{
		$('#code').val(code);
		$('#name').val(name);
		$('#editModal').modal('show');
	}
	function chgname()
	{
		if ($('#name').val() == '') {
			alert('Hospital Name is null!');
			$('#name').focus();
			return false;
		}
		$.ajax({
			url: 'chg_hospital.php',
			type: 'post',
			dataType: 'json',
			data: {code: $('#code').val(), name: $('#name').val()},
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