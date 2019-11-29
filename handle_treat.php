<?php
require 'Db.class.php';
$db_instance = new Db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	try
	{
		$type = intval($_POST['type']);
		$doctor = $_POST['doctor'];
		$patient = $_POST['patient'];

		if ($type === 1) {
			// select a doctor to treat a patient
			$db_instance->bind('DOCTOR', $doctor);
			$db_instance->bind('PATIENT', $patient);
			$db_instance->query('INSERT INTO `treats` VALUES (:PATIENT, :DOCTOR)');
		} else {
			// stop the doctor treating a patient
			$db_instance->bind('DOCTOR', $doctor);
			$db_instance->bind('PATIENT', $patient);
			$db_instance->query('DELETE FROM `treats` WHERE `patient_ohip` = :PATIENT AND `treated_by` = :DOCTOR');
		}
		echo json_encode(array('code' => 0, 'msg' => ''));
	} catch (Exception $e) {
		echo json_encode(array('code' => 1, 'msg' => 'Cannot complete the operation , please try again later.'));
	}
}
?>