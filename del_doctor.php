<?php
require 'Db.class.php';
$db_instance = new Db();

// delete a doctor from the table
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$licensenumber = $_POST['licensenumber'];
	
	// deleting
	try
	{
		$db_instance->bind('licensenumber', $licensenumber);
		$db_instance->query('DELETE FROM `doctor` WHERE `licensenumber` = :licensenumber');
		echo json_encode(array('code' => 0, 'msg' => ''));
	} catch (Exception $e) {
		// cannot delete the doctor because he's the headdoctor at a hospital
		$hospital_name = $db_instance->single('SELECT `name` FROM `hospital` WHERE `currenthead` = :licensenumber');
		echo json_encode(array('code' => 1, 'msg' => 'Cannot delete the doctor , because he is the currenthead of ' . $hospital_name . ' hospital !'));
	}
}
?>
