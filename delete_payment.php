<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
header('Content-Type: application/json');

if(isset($_POST['payment_id'])){

	Global $cookie;
	$id_customer = $cookie->id_customer;
	$payment_id = $_POST['payment_id'];


	//Obtener el registro del pagamento guardado
	$sql = "SELECT * FROM "._DB_PREFIX_."ep_requests WHERE id_ep_request='".$payment_id."'";
	$registros = Db::getInstance()->executeS($sql);



	if(!isset($registros[0])){
		echo json_encode(array('status' => 'ERROR', 'msg' => 'Metodo de pagamento não existe'));
		die();
	}

	$is_owner = false;
	if($id_customer==$registros[0]['id_user']){
		$is_owner = true;
	}

	if($is_owner){
		$sql = "UPDATE "._DB_PREFIX_."ep_requests SET active = 0 WHERE id_ep_request='".$payment_id."'";
		$hecho = Db::getInstance()->execute($sql);
	}else{
		echo json_encode(array('status' => 'ERROR', 'msg' => 'Você não tem permissões para apagar estes dados de pagamento!'));
		die();
	}


	if($hecho){
		echo json_encode(array('status' => 'SUCCESS', 'msg' => 'Dados de pagamento apagado com sucesso'));
		die();
	}else{
		echo json_encode(array('status'=> 'ERROR', 'msg' => 'ERRO al apagar os dados de pagamento'));
		die();
	}



}



?>