<?php
require 'Db.class.php';
$db_instance = new Db();

$orderBy = '';
$where = '';
$has_no_patient = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$column = intval($_POST['column']);
	$sort = intval($_POST['sort']);
	$date = $_POST['startdate'];
	$has_no_patient = $_POST['hnp'] == 1 ? true : false;
	
	if ($column === 0) {
		$orderBy = ' fname';
	} else {
		$orderBy = ' lname';
	}

	if ($sort === 0) {
		$orderBy .= ' asc';
	} else {
		$orderBy .= ' desc';
	}

	if (!empty($date)) {
		$where = ' where `license_startdate` <= \'' . $date . '\'';
	}
} else {
    $orderBy = ' fname asc';
}
$results = $db_instance->query('SELECT * FROM `doctor` LEFT JOIN `hospital` ON `works_at` = `code` ' . $where . ' ORDER BY ' . $orderBy);
$doctors = [];
foreach ($results as $result) {
	$count = $db_instance->single('SELECT COUNT(*) FROM `treats` WHERE `treated_by` = \'' . $result['licensenumber'] . '\'');
	if ($has_no_patient && $count) {
		continue;
	}
	$result['count'] = $count;
	$doctors[] = $result;
}
// hospital data
$hospitals = $db_instance->query('SELECT * FROM `hospital`');
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
	<style type="text/css">
		table td {
			vertical-align: middle !important;
		}
	</style>
</head>
<body>
	<?php include('header.php'); ?>
	<div class="container">
		<form name="srhForm" method="POST">
			<div class="row pull-left">
				<label class="radio-inline">
		  			<input type="radio" name="column" value="0" onclick="mySubmit()" <?php echo isset($_POST['column']) ? ($_POST['column'] ? '' : 'checked') : 'checked'; ?>> First Name
				</label>
				<label class="radio-inline">
		  			<input type="radio" name="column" value="1" onclick="mySubmit()" <?php echo isset($_POST['column']) ? ($_POST['column'] ? 'checked' : '') : ''; ?>> Last Name
				</label>
				<div class="clearfix" style="margin-bottom: 10px;"></div>
			<!-- </div>
			<div class="clearfix" style="margin-bottom: 10px;"></div>
			<div class="row"> -->
				<label class="radio-inline">
		  			<input type="radio" name="sort" value="0" onclick="mySubmit()" <?php echo isset($_POST['sort']) ? ($_POST['sort'] ? '' : 'checked') : 'checked'; ?>> ASC
				</label>
				<label class="radio-inline">
		  			<input type="radio" name="sort" value="1" onclick="mySubmit()" <?php echo isset($_POST['sort']) ? ($_POST['sort'] ? 'checked' : '') : ''; ?>> DESC
				</label>
				<div class="clearfix" style="margin-bottom: 10px;"></div>
				<label for="hnp">
		  			<input type="checkbox" name="hnp" id="hnp" value="1" onclick="mySubmit()" <?php echo isset($_POST['hnp']) ? ($_POST['hnp'] ? 'checked' : '') : ''; ?>> has no patient
				</label>
			</div>
			<div class="row pull-right form-inline">
				<input type="date" class="form-control" name="startdate" value="<?php echo isset($_POST['startdate']) ? $_POST['startdate'] : ''; ?>" />
				<a class="btn btn-primary" onclick="mySubmit()" href="javascript:void(0);" role="button">Filter</a>
				<a class="btn btn-success" onclick="newDoctor()" role="button" >New Doctor</a>
			</div>
		</form>
		<div class="clearfix" style="margin-bottom: 10px;"></div>
		<!-- data table -->
		<div class="row">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Doctor Image</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Specialty</th>
						<th>License Startdate</th>
						<th>Operation</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($doctors as $doctor) {
							echo '<tr>';
							echo '<td><img src="' . (empty($doctor['docimage']) ? 'image/default.png' : $doctor['docimage']) . '" width="50" alt="DocImage" class="img-thumbnail" onclick="showImage(\'' . $doctor['licensenumber'] . '\', \'' . $doctor['docimage'] . '\')"></td>';
							echo '<td>' . $doctor['fname'] . '</td>';
							echo '<td>' . $doctor['lname'] . '</td>';
							echo '<td>' . $doctor['specialty'] . '</td>';
							echo '<td>' . $doctor['license_startdate'] . '</td>';
							echo '<td><a onclick="view(\'' . $doctor['fname'] . '\', \'' . $doctor['lname'] . '\', \'' . $doctor['specialty'] . '\', \'' . $doctor['licensenumber'] . '\', \'' . $doctor['license_startdate'] . '\', \'' . $doctor['name'] . '\');">View</a> | <a onclick="del(\'' . $doctor['count'] . '\', \'' . $doctor['licensenumber'] . '\')">Delete</a> | <a href="treat.php?doctor=' . $doctor['licensenumber'] . '">Treating</a></td>';
							echo '</tr>';
						}
					?>
				</tbody>
			</table>
		</div>
		<!-- （Modal） -->
		<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="viewModalLabel">Doctor's information</h4>
					</div>
					<div class="modal-body">
						<div class="row col-md-offset-2">
							<strong class="col-md-4 text-right">Name: </strong><span id="name"></span>
						</div>
						<div class="clearfix" style="margin-bottom: 10px;"></div>
						<div class="row col-md-offset-2">
							<strong class="col-md-4 text-right">License Number: </strong><span id="licensenumber"></span>
						</div>
						<div class="clearfix" style="margin-bottom: 10px;"></div>
						<div class="row col-md-offset-2">
							<strong class="col-md-4 text-right">Specialty: </strong><span id="specialty"></span>
						</div>
						<div class="clearfix" style="margin-bottom: 10px;"></div>
						<div class="row col-md-offset-2">
							<strong class="col-md-4 text-right">License Startdate: </strong><span id="license_startdate"></span>
						</div>
						<div class="clearfix" style="margin-bottom: 10px;"></div>
						<div class="row col-md-offset-2">
							<strong class="col-md-4 text-right">Work at: </strong><span id="hospital_name"></span>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal -->
		</div>

		<!-- （Modal） -->
		<div class="modal fade" id="newModal" tabindex="-1" role="dialog" aria-labelledby="newModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<form class="form-horizontal">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="newModalLabel">Add a new Doctor</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="fname" class="col-sm-4 control-label">First Name</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="fname" name="fname" placeholder="First Name">
								</div>
							</div>
							<div class="form-group">
								<label for="lname" class="col-sm-4 control-label">Last Name</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name">
								</div>
							</div>
							<div class="form-group">
								<label for="lisnum" class="col-sm-4 control-label">License Number</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="lisnum" name="lisnum" placeholder="License Number">
								</div>
							</div>
							<div class="form-group">
								<label for="spec" class="col-sm-4 control-label">Specialty</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="spec" name="spec" placeholder="Specialty">
								</div>
							</div>
							<div class="form-group">
								<label for="lisdate" class="col-sm-4 control-label">License Startdate</label>
								<div class="col-sm-6">
									<input type="date" class="form-control" id="lisdate" name="lisdate" placeholder="License Startdate">
								</div>
							</div>
							<div class="form-group">
								<label for="hospital" class="col-sm-4 control-label">Work At</label>
								<div class="col-sm-6">
									<select class="form-control" id="hospital" name="hospital">
										<?php
											foreach ($hospitals as $hospital) {
												echo '<option value="' . $hospital['code'] . '">' . $hospital['name'] . '</option>';
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" onclick="addDoctor()" class="btn btn-primary">Save</button>
						</div>
					</div><!-- /.modal-content -->
				</form>
			</div><!-- /.modal -->
		</div>

		<!-- （Modal） -->
		<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<form class="form-horizontal">
					<input type="hidden" name="doctor" id="doctor">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="imageModalLabel">Add a new Doctor image</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="docimage" class="col-sm-4 control-label">Doctor Image</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="docimage" name="docimage" placeholder="Image URL">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" onclick="addImage()" class="btn btn-primary">Save</button>
						</div>
					</div><!-- /.modal-content -->
				</form>
			</div><!-- /.modal -->
		</div>
	</div>
</body>
<script type="text/javascript">
	function showImage(doctor, docimage)
	{
		$('#docimage').val(docimage);
		$('#doctor').val(doctor);
		$('#imageModal').modal('show');
	}

	function addImage()
	{
		if ($('#docimage').val() == '') {
			alert('Doctor Image is null!');
			$('#docimage').focus();
			return false;
		}
		if ($('#docimage').val().length > 100) {
			alert('Image url is too long!');
			$('#docimage').focus();
			return false;
		}
		$.ajax({
			url: 'add_doctor_image.php',
			type: 'post',
			dataType: 'json',
			data: {doctor: $('#doctor').val(), docimage: $('#docimage').val()},
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

	function mySubmit()
	{
		srhForm.submit();
	}

	function view(fname, lname, specialty, licensenumber, license_startdate, hospital_name)
	{
		$('#name').text(fname + ' ' + lname);
		$('#licensenumber').text(licensenumber);
		$('#specialty').text(specialty);
		$('#license_startdate').text(license_startdate);
		$('#hospital_name').text(hospital_name);

		$('#viewModal').modal('show');
	}

	function del(count, licensenumber)
	{
		if (count > 0)
		{
			var truthBeTold = window.confirm("Doctor is treating patients , Delete ?");
			if (!truthBeTold)
			{
				return false;
			}
		}
		$.ajax({
			url: 'del_doctor.php',
			type: 'post',
			dataType: 'json',
			data: {licensenumber: licensenumber},
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

	function newDoctor()
	{
		$('#newModal').modal('show');
	}

	function addDoctor()
	{
		if ($('#fname').val() == '') {
			alert('First Name is null!');
			$('#fname').focus();
			return false;
		}

		if ($('#lname').val() == '') {
			alert('Last Name is null!');
			$('#lname').focus();
			return false;
		}

		if ($('#lisnum').val() == '') {
			alert('License Number is null!');
			$('#lisnum').focus();
			return false;
		}

		if ($('#lisnum').val().length > 4) {
			alert('License Number length over four!');
			$('#lisnum').focus();
			return false;
		}

		if ($('#spec').val() == '') {
			alert('Specialty is null!');
			$('#spec').focus();
			return false;
		}

		if ($('#lisdate').val() == '') {
			alert('License Startdate is null!');
			$('#lisdate').focus();
			return false;
		}

		$.ajax({
			url: 'add_doctor.php',
			type: 'post',
			dataType: 'json',
			data: {
				fname: $('#fname').val(),
				lname: $('#lname').val(),
				licensenumber: $('#lisnum').val(),
				specialty: $('#spec').val(),
				license_startdate: $('#lisdate').val(),
				works_at: $('#hospital').val()
			},
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