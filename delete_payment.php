<?php
/**
 * Easypay
 *
 * Direitos autorais (c) 2023 Trigenius
 * 
 * Todos os direitos reservados.
 * 
 * É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 * 
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 */
include_once('../../config/config.inc.php');
include_once('../../init.php');
header('Content-Type: application/json');

if(Tools::getValue('payment_id')){

	$context = Context::getContext();
	$cookie = $context->cookie;
	$id_customer = $cookie->id_customer;
	$payment_id = Tools::getValue('payment_id');


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