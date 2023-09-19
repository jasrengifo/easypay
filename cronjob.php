<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

	include_once(dirname(__FILE__).'/../../config/config.inc.php');
	include_once(dirname(__FILE__).'/../../init.php');



	$sql = "SELECT a.id_order, a.id_cart, b.id_ep_request FROM "._DB_PREFIX_."orders a INNER JOIN "._DB_PREFIX_."ep_requests b ON a.id_cart = b.id_cart WHERE a.current_state = ".Configuration::get("EASYPAY_BMWAY_WAIT")." AND a.date_upd < (NOW() - INTERVAL 5 MINUTE);";


	$actualizar = Db::getInstance()->executeS($sql);

	foreach($actualizar as $encomenda){


         $headers = [
            "AccountId: ".Configuration::get('EASYPAY_API_ID'),
            "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];


	    if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/void/" . $encomenda['id_ep_request'];
        }else{
           $URL_EP = "https://api.prod.easypay.pt/2.0/void/" . $encomenda['id_ep_request'];
        }

	    $url = $URL_EP;

	    $body = [

                    "transaction_key" =>$encomenda['id_cart'],
                    "descriptive" => "Cancelar pagamento manualmente",

                ];


	    $curlOpts = [
	        CURLOPT_URL => $url,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_POST => 1,
	        CURLOPT_TIMEOUT => 60,
	        CURLOPT_POSTFIELDS => json_encode($body),
	        CURLOPT_HTTPHEADER => $headers,
	    ];


		$objOrder = new Order($encomenda['id_order']); 
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get('EASYPAY_FAILED'), (int)$objOrder->id);
        $history->add();

	    $curl = curl_init();
	    curl_setopt_array($curl, $curlOpts);
	    $response_body = curl_exec($curl);
	    curl_close($curl);
	    $response = json_decode($response_body, true);

	    $sql = "UPDATE "._DB_PREFIX_."ep_frequent_transactions SET autorizado = 2 WHERE id_pagamento='".$encomenda['id_ep_request']."'";
	    Db::getInstance()->execute($sql);

	}



	echo 'MBWAY ACUTALIZADOS '.date('d-m-Y H:i:s');


?>