<?php
require 'Db.class.php';
$db_instance = new Db();

// update the doctor's image with a image URL on the internet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$docimage = $_POST['docimage'];
	$doctor = $_POST['doctor'];
	try
	{
		$db_instance->bind('DOCIMAGE', $docimage);
		$db_instance->bind('DOCTOR', $doctor);
		$db_instance->query('UPDATE `doctor` SET `docimage` = :DOCIMAGE WHERE `licensenumber` = :DOCTOR');
		echo json_encode(array('code' => 0, 'msg' => ''));
	} catch (Exception $e) {
		echo json_encode(array('code' => 1, 'msg' => 'Cannot change the doctor image , try again later.'));
	}
}
?>
