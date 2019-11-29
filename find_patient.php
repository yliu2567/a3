<?php
require 'Db.class.php';
$db_instance = new Db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	try
	{
		// find a patient by ohip
		$ohip = intval($_POST['ohip']);
		$db_instance->bind('OHIP', $ohip);
		$patient = $db_instance->query('SELECT * FROM `patient` WHERE `ohip` = :OHIP');
		if (empty($patient)) {
			echo json_encode(array('code' => 1, 'msg' => 'OHIP number is not exist.'));
			die;
		}
		// find the doctors who are treating this patient
		$db_instance->bind('OHIP', $ohip);
		$doctors = $db_instance->query('SELECT `doctor`.* FROM `treats` LEFT JOIN `doctor` ON `treated_by` = `licensenumber` WHERE `patient_ohip` = :OHIP');
		$patient[0]['doctors'] = $doctors;
		echo json_encode(array('code' => 0, 'msg' => '', 'data' => $patient[0]));
	} catch (Exception $e) {
		echo json_encode(array('code' => 1, 'msg' => 'Cannot find the patient , please try again later.'));
	}
}
?>
