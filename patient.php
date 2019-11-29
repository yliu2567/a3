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
		<div class="row form-inline">
			<input type="text" class="form-control" name="ohip" id="ohip" placeholder="OHIP Number" />
			<a class="btn btn-primary" onclick="srchPatient()" href="javascript:void(0);" role="button">Search</a>
		</div>
		<div class="clearfix" style="margin-bottom: 10px;"></div>
		<div class="row">
			<div class="well" id="result" style="display: none;">
				<div class="row">
					<strong class="col-md-4 text-right">Patient Name: </strong><span id="patient"></span>
				</div>
				<div class="row">
					<strong class="col-md-4 text-right">Doctors' Name: </strong><span id="doctor"></span>
				</div>
			</div>
			<div class="well" id="error" style="display: none;">
				<div class="row">
					<strong class="col-md-4 text-right" id="errMsg"></strong>
				</div>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript">
	function srchPatient()
	{
		$('#result').css('display', 'none');
		$('#error').css('display', 'none');
		if ($('#ohip').val() == '') {
			alert('OHIP Number is null!');
			$('#ohip').focus();
			return false;
		}

		$.ajax({
			url: 'find_patient.php',
			type: 'post',
			dataType: 'json',
			data: {ohip: $('#ohip').val()},
			success: function(res) {
				if (res.code === 1) {
					$('#result').css('display', 'none');
					$('#error').css('display', 'block');
					$('#errMsg').text(res.msg);
				} else {
					$('#error').css('display', 'none');
					$('#result').css('display', 'block');
					$('#patient').text(res.data.fname + ' ' + res.data.lname);
					var doctor = '';
					var showText = '';
					$.each(res.data.doctors, function(i, v){
						showText += ',' + v.fname + ' ' + v.lname;
					});
					$('#doctor').text(showText.substring(1));
				}
			},
			error: function() {
				alert('An unexpected network error occurred !');
			}
		});
	}
</script>
</html>