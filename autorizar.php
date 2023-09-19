<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');

ini_set('precision', 10);
ini_set('serialize_precision', 10);



$id = $_POST['id_pagamento']; //el id del pago a autorizar, esta llamada pide autorizacion, asumo es lo que quieres, docs no tienen explicacion de que es. 




if($_POST['tipo']=='autorizar'){
        /*
            $body = [
                "transaction_key" => "",
                "descriptive" => "Aprovação manual",
                "value" => round(floatval($_POST['valor']), 2), //valor
            ];
        */
        $headers = [
            "AccountId: ".Configuration::get('EASYPAY_API_ID'),
            "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];


        /*
        if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/frequent/authorisation/" . $id;
        }else{
            $URL_EP = "https://api.prod.easypay.pt/2.0/frequent/authorisation/" . $id;
        }
                    
                    
        $url = $URL_EP;

        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $response_body = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response_body, true);
        */
        $body = [

                    "transaction_key" => $_POST['id_cart'],
                    "descriptive" => "Pagamento EasyPay",
                    "capture_date" => date("Y-m-d"),
                    "value" => round(floatval($_POST['valor']), 2), 

                ];


        if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
        }else{
           $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
        }

        $url = $URL_EP . $_POST['id_pagamento'];

        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $response_body = curl_exec($curl);
        curl_close($curl);
        $response2 = json_decode($response_body, true);


        $sql = 'SELECT * FROM '._BD_PREFIX_.'ep_requests WHERE id_cart = '.$_POST['id_cart'].' AND method_type = "mbw"';
        $resultado2 = Db::getInstance()->executeS($sql);
        
        // if(count($resultado2)>0){

        //     $objOrder = new Order(Order::getOrderByCartId((int)($_POST['id_cart']))); 
        //     $history = new OrderHistory();
        //     $history->id_order = (int)$objOrder->id;
        //     $history->changeIdOrderState(Configuration::get('EASYPAY_BMWAY_WAIT'), (int)$objOrder->id);
        //     $test = $history->add();
            
        // }
        


        
        $sql = "UPDATE "._DB_PREFIX_."ep_frequent_transactions SET autorizado = 1 WHERE id_pagamento='".$_POST['id_pagamento']."' AND id_cart=".$_POST['id_cart'];
        Db::getInstance()->execute($sql);

        die(json_encode(array('status'=>'SUCCESS', 'msg'=>'Pagamento autorizado e capturado com sucesso!', 'exta' => $_POST['id_cart'])));


}if($_POST['tipo']='cancelar'){

    $headers = [
            "AccountId: ".Configuration::get('EASYPAY_API_ID'),
            "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];


    if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/void/" . $_POST['id_pagamento'];
        }else{
           $URL_EP = "https://api.prod.easypay.pt/2.0/void/" . $_POST['id_pagamento'];
        }


    $url = $URL_EP;

    $body = [

                    "transaction_key" => $_POST['id_cart'],
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


    $objOrder = new Order(Order::getOrderByCartId((int)($_POST['id_cart']))); 
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_PAYMENT_CANCEL'), (int)$objOrder->id);
    $history->add();

    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response_body, true);

    $sql = "UPDATE "._DB_PREFIX_."ep_frequent_transactions SET autorizado = 2 WHERE id_pagamento='".$_POST['id_pagamento']."'";
    Db::getInstance()->execute($sql);


    die(json_encode(array('status'=>'SUCCESS', 'msg'=>json_encode('Pagamento cancelado com sucesso!'))));
}










?>