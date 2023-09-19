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

ini_set('precision', 10);
ini_set('serialize_precision', 10);



$id = Tools::getValue('id_pagamento'); //el id del pago a autorizar, esta llamada pide autorizacion, asumo es lo que quieres, docs no tienen explicacion de que es. 




if(Tools::getValue('tipo')=='autorizar'){

        $headers = [
            "AccountId: ".Configuration::get('EASYPAY_API_ID'),
            "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];


        $body = [

                    "transaction_key" => Tools::getValue('id_cart'),
                    "descriptive" => "Pagamento EasyPay",
                    "capture_date" => date("Y-m-d"),
                    "value" => round((float)Tools::getValue('valor'), 2), 

                ];


        if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
        }else{
           $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
        }

        $url = $URL_EP . Tools::getValue('id_pagamento');

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


        $sql = 'SELECT * FROM '._BD_PREFIX_.'ep_requests WHERE id_cart = '.Tools::getValue('id_cart').' AND method_type = "mbw"';
        $resultado2 = Db::getInstance()->executeS($sql);
        
        
        $sql = "UPDATE "._DB_PREFIX_."ep_frequent_transactions SET autorizado = 1 WHERE id_pagamento='".Tools::getValue('id_pagamento')."' AND id_cart=".Tools::getValue('id_cart');
        Db::getInstance()->execute($sql);

        die(json_encode(array('status'=>'SUCCESS', 'msg'=>'Pagamento autorizado e capturado com sucesso!', 'exta' => Tools::getValue('id_cart'))));


}

if(Tools::getValue('tipo')=='cancelar'){

    $headers = [
            "AccountId: ".Configuration::get('EASYPAY_API_ID'),
            "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];


    if(Configuration::get('EASYPAY_TESTES')==1){
            $URL_EP = "https://api.test.easypay.pt/2.0/void/" . Tools::getValue('id_pagamento');
        }else{
           $URL_EP = "https://api.prod.easypay.pt/2.0/void/" . Tools::getValue('id_pagamento');
        }


    $url = $URL_EP;

    $body = [

                    "transaction_key" => Tools::getValue('id_cart'),
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


    $objOrder = new Order(Order::getOrderByCartId((int)(Tools::getValue('id_cart')))); 
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_PAYMENT_CANCEL'), (int)$objOrder->id);
    $history->add();

    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response_body, true);

    $sql = "UPDATE "._DB_PREFIX_."ep_frequent_transactions SET autorizado = 2 WHERE id_pagamento='".Tools::getValue('id_pagamento')."'";
    Db::getInstance()->execute($sql);


    die(json_encode(array('status'=>'SUCCESS', 'msg'=>json_encode('Pagamento cancelado com sucesso!'))));
}










?>