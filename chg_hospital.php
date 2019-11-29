<?php
require 'Db.class.php';
$db_instance = new Db();

// update hospital name by code
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$code = $_POST['code'];
	$name = $_POST['name'];
	try
	{
		$db_instance->bind('CODE', $code);
		$db_instance->bind('NAME', $name);
		$db_instance->query('UPDATE `hospital` SET `name` = :NAME WHERE `code` = :CODE');
		echo json_encode(array('code' => 0, 'msg' => ''));
	} catch (Exception $e) {
		echo json_encode(array('code' => 1, 'msg' => 'Cannot change the hospital name , try again later.'));
	}
}
?>
