<?php
require 'Db.class.php';
$db_instance = new Db();

// add a new doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$db_instance->bindMore($_POST);
	try
	{
		$db_instance->query('INSERT INTO `doctor` VALUES (:fname, :lname, :licensenumber, :specialty, :license_startdate, :works_at, null)');
		echo json_encode(array('code' => 0, 'msg' => ''));
	} catch (Exception $e) {
		echo json_encode(array('code' => 1, 'msg' => 'Cannot add the doctor , because icense number is already exists.'));
	}
}
?>