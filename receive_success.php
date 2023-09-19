<?php
/**
 * Easypay
 *
 * @copyright Direitos autorais (c) 2023 Trigenius
 * 
 * @author Trigenius
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

if (Configuration::get('EASYPAY_API_IP') != '' and $_SERVER['REMOTE_ADDR'] == Configuration::get('EASYPAY_API_IP')) {
    die('IP não valida');
}

$respuesta = Tools::file_get_contents('php://input');
$respuesta = json_decode($respuesta, true);


$buscar_registro = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'];
$retornar = Db::getInstance()->executeS($buscar_registro);

$insertar = "SELECT * FROM " . _DB_PREFIX_ . "ep_last_mb WHERE id_pagamento='" . $respuesta['key'] . "'";
$insertar2 = Db::getInstance()->executeS($insertar);
if (isset($insert2[0])) {
    $respuesta['key'] = $insert2[0]['cart'];
    $is_mb = 1;
} else {
    $is_mb = 0;
}

if (isset($respuesta['currency'])) {
    die;
}

//ACEPTAR CAPTURES
if ($respuesta['type'] == 'void') {
    die('pagamento cancelado');
}
if ($respuesta['status'] == 'success' && $respuesta['type'] == 'capture') {



    $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1, autorizado=1 WHERE id_cart=" . $respuesta['key'];
    $precio65 = Db::getInstance()->execute($activar4);



    $revisar = "SELECT * FROM " . _DB_PREFIX_ . "ep_frequent_transactions WHERE id_cart=" . $respuesta['key'];
    $rsp = Db::getInstance()->executeS($revisar);




    if (isset($rsp[0]) && $rsp[0]['autorizado'] == 0) {

        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get('EASYPAY_PAGO_NAO_AUT'), (int)$objOrder->id);
        $history->add();
        echo "Pagamento não aprovado";
    } else {





        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));

        if ($objOrder->current_state != Configuration::get('EASYPAY_APROVED')) {
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $history->changeIdOrderState(Configuration::get('EASYPAY_APROVED'), (int)$objOrder->id);
            $history->add();
            echo "Pagamento capturado";
        } else {
            echo "Esta encomenda já estava como 'Pagamento Aceite'";
        }
    }


    //AVISAR A EASYPAY Q TODO ESTA OK
    $id = $retornar[0]['id_ep_request']; //este es el id del pagamento, de acuerdo a docs de easypay, DEBES hacer este request despues de la notificacion generica para confirmar

    $headers = [
        "AccountId: " . Configuration::get('EASYPAY_API_ID'),
        "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
        'Content-Type: application/json',
    ];

    if (Configuration::get('EASYPAY_TESTES') == 1) {
        $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
    } else {
        $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
    }


    $url = $URL_EP . $id;

    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];

    //Verificar si es multibanco
    $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
    $rspp = Db::getInstance()->executeS($vbm);

    //Si no es multibanco, entonces hacer capture
    if (isset($rspp[0]) && $rspp[0]['method_type'] != "mb") {
        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $response_body = curl_exec($curl);
        curl_close($curl);
        $response2 = json_decode($response_body, true);
    }
} else if ($respuesta['status'] == 'success' && $respuesta['type'] == 'authorisation') {



    /*CHANGE ORDER STATUS*/

    if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 0) {
        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get('EASYPAY_PROCESSING'), (int)$objOrder->id);
        $history->add();

        //ATIVAR PAGAMENTE PARA PODER SER APROVADO
        $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1 WHERE id_cart=" . $respuesta['key'];
        $precio65 = Db::getInstance()->execute($activar4);
    } else {
        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        if ($objOrder->current_state != Configuration::get('EASYPAY_APROVED')) {
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $history->changeIdOrderState(Configuration::get('EASYPAY_APROVED'), (int)$objOrder->id);
            $history->add();
        }
    }




    $sql = 'UPDATE ' . _DB_PREFIX_ . 'ep_orders SET status="ok", messagem="' . $respuesta['messages'][0] . '" WHERE id_cart=' . $respuesta['key'];
    $excel = Db::getInstance()->execute($sql);




    $sql2 = "SELECT " . _DB_PREFIX_ . "ep_orders.*, " . _DB_PREFIX_ . "orders.id_order idorder, " . _DB_PREFIX_ . "orders.reference, " . _DB_PREFIX_ . "orders.id_customer FROM " . _DB_PREFIX_ . "ep_orders INNER JOIN " . _DB_PREFIX_ . "orders ON pss_orders.id_cart = " . _DB_PREFIX_ . "ep_orders.id_cart WHERE " . _DB_PREFIX_ . "ep_orders.id_cart=" . $respuesta['key'] . "";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);




    $metodo_pagamento = $sql2;

    if ($rsp[0]['method'] == 'cc') {
        $metodo_pagamento = "VISA";
    } else if ($rsp[0]['method'] == 'mb') {
        $metodo_pagamento = "Multibanco";
    } else if ($rsp[0]['method'] == 'bb') {
        $metodo_pagamento = "Boleto Bancario";
    } else if ($rsp[0]['method'] == 'dd') {
        $metodo_pagamento = "Debito Direto";
    } else if ($rsp[0]['method'] == 'mbw') {
        $metodo_pagamento = "MBWAY";
    }


    $buscar_id = "SELECT id_ep_request id_ep FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart='" . $respuesta['key'] . "' ORDER BY id_request DESC LIMIT 1";
    $epr = Db::getInstance()->executeS($buscar_id);




    if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 1) {

        $body = [

            "transaction_key" => $respuesta['key'],
            "descriptive" => "Pagamento EasyPay",
            "capture_date" => date("Y-m-d"),
            "value" => round((float)$precio['0']['total_paid'], 2), //asumo que el value es lo que pago el cliente aca, debe de ser entre el min y max

        ];


        if (Configuration::get('EASYPAY_TESTES') == 1) {
            $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
        } else {
            $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
        }


        $url = $URL_EP . $id;

        $headers = [
            "AccountId: " . Configuration::get('EASYPAY_API_ID'),
            "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];

        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ];

        //Verificar si es multibanco
        $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
        $rspp = Db::getInstance()->executeS($vbm);

        //Si no es multibanco, entonces hacer capture
        if (isset($rspp[0]) && $rspp[0]['method_type'] != "mb") {
            $curl = curl_init();
            curl_setopt_array($curl, $curlOpts);
            $response_body = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response_body, true);
            echo "Pagamento capturado automáticamente";
        }
    }
}












//CREANDO FREQUENTE
else if ($respuesta['status'] == 'success' && $respuesta['type'] == 'frequent_create') {
    //Is Generic Success





    //Created payment
    $actualizar = 'UPDATE ' . _DB_PREFIX_ . 'ep_requests SET method_status="acepted" WHERE id_cart=' . $respuesta['key'];
    $executar = Db::getInstance()->execute($actualizar);



    // Make Capture
    $buscar_precio = "SELECT * FROM " . _DB_PREFIX_ . "orders WHERE id_cart=" . $respuesta['key'];
    $precio = Db::getInstance()->executeS($buscar_precio);
    $id = $retornar[0]['id_ep_request']; //este es el id del pago creado






    if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 1) {


        $body = [

            "transaction_key" => $respuesta['key'],
            "descriptive" => "Pagamento EasyPay",
            "capture_date" => date("Y-m-d"),
            "value" => round((float)$precio['0']['total_paid'], 2), //asumo que el value es lo que pago el cliente aca, debe de ser entre el min y max

        ];


        if (Configuration::get('EASYPAY_TESTES') == 1) {
            $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
        } else {
            $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
        }


        $url = $URL_EP . $id;

        $headers = [
            "AccountId: " . Configuration::get('EASYPAY_API_ID'),
            "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];

        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ];

        //Verificar si es multibanco
        $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
        $rspp = Db::getInstance()->executeS($vbm);

        //Si no es multibanco, entonces hacer capture
        if (isset($rspp[0]) && $rspp[0]['method_type'] != "mb") {
            $curl = curl_init();
            curl_setopt_array($curl, $curlOpts);
            $response_body = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response_body, true);
            echo "pagamento capturado automáticamente";
        }



        if ($response['status'] == 'ok') {


            if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 0) {
                $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $history->changeIdOrderState(Configuration::get('EASYPAY_PROCESSING'), (int)$objOrder->id);
                $history->add();

                //ATIVAR PAGAMENTE PARA PODER SER APROVADO
                $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1 WHERE id_cart=" . $respuesta['key'];
                $precio65 = Db::getInstance()->execute($activar4);
            } else {
                $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
                if ($objOrder->current_state != Configuration::get('EASYPAY_APROVED')) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$objOrder->id;
                    $history->changeIdOrderState(Configuration::get('EASYPAY_APROVED'), (int)$objOrder->id);
                    $history->add();
                }
            }
        }

        //ATIVAR PAGAMENTE PARA PODER SER APROVADO
        $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1, autorizado=1 WHERE id_cart=" . $respuesta['key'];
        $precio65 = Db::getInstance()->execute($activar4);
    } else {
        //ATIVAR PAGAMENTE PARA PODER SER APROVADO
        $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1 WHERE id_cart=" . $respuesta['key'];
        $precio65 = Db::getInstance()->execute($activar4);

        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get('EASYPAY_PAGO_NAO_AUT'), (int)$objOrder->id);
        $history->add();
    }
} else if ($respuesta['status'] == 'success' && $respuesta['type'] != 'subscription_capture') {

    /*CHANGE ORDER STATUS*/

    $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1 WHERE id_cart=" . $respuesta['key'];
    $precio65 = Db::getInstance()->execute($activar4);

    if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 0) {
        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get('EASYPAY_PROCESSING'), (int)$objOrder->id);
        $history->add();

        //ATIVAR PAGAMENTE PARA PODER SER APROVADO
        $activar4 = "UPDATE " . _DB_PREFIX_ . "ep_frequent_transactions SET ativado=1 WHERE id_cart=" . $respuesta['key'];
        $precio65 = Db::getInstance()->execute($activar4);
    } else {
        $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
        if ($objOrder->current_state != Configuration::get('EASYPAY_APROVED')) {
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $history->changeIdOrderState(Configuration::get('EASYPAY_APROVED'), (int)$objOrder->id);
            $history->add();
        }
    }






    $sql = 'UPDATE ' . _DB_PREFIX_ . 'ep_orders SET status="ok", messagem="' . $respuesta['messages'][0] . '" WHERE id_cart=' . $respuesta['key'];
    $excel = Db::getInstance()->execute($sql);




    $sql2 = "SELECT " . _DB_PREFIX_ . "ep_orders.*, " . _DB_PREFIX_ . "orders.id_order idorder, " . _DB_PREFIX_ . "orders.reference, " . _DB_PREFIX_ . "orders.id_customer FROM " . _DB_PREFIX_ . "ep_orders INNER JOIN " . _DB_PREFIX_ . "orders ON pss_orders.id_cart = " . _DB_PREFIX_ . "ep_orders.id_cart WHERE " . _DB_PREFIX_ . "ep_orders.id_cart=" . $respuesta['key'] . "";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);




    $metodo_pagamento = $sql2;

    if ($rsp[0]['method'] == 'cc') {
        $metodo_pagamento = "VISA";
    } else if ($rsp[0]['method'] == 'mb') {
        $metodo_pagamento = "Multibanco";
    } else if ($rsp[0]['method'] == 'bb') {
        $metodo_pagamento = "Boleto Bancario";
    } else if ($rsp[0]['method'] == 'dd') {
        $metodo_pagamento = "Debito Direto";
    } else if ($rsp[0]['method'] == 'mbw') {
        $metodo_pagamento = "MBWAY";
    }


    $buscar_id = "SELECT id_ep_request id_ep FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart='" . $respuesta['key'] . "' ORDER BY id_request DESC LIMIT 1";
    $epr = Db::getInstance()->executeS($buscar_id);




    if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS')) {

        $body = [

            "transaction_key" => $respuesta['key'],
            "descriptive" => "Pagamento EasyPay",
            "capture_date" => date("Y-m-d"),
            "value" => round((float)$precio['0']['total_paid'], 2), //asumo que el value es lo que pago el cliente aca, debe de ser entre el min y max

        ];


        if (Configuration::get('EASYPAY_TESTES') == 1) {
            $URL_EP = "https://api.test.easypay.pt/2.0/capture/";
        } else {
            $URL_EP = "https://api.prod.easypay.pt/2.0/capture/";
        }


        $url = $URL_EP . $id;

        $headers = [
            "AccountId: " . Configuration::get('EASYPAY_API_ID'),
            "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];

        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ];

        //Verificar si es multibanco
        $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
        $rspp = Db::getInstance()->executeS($vbm);

        //Si no es multibanco, entonces hacer capture
        if (isset($rspp[0]) && $rspp[0]['method_type'] != "mb") {
            $curl = curl_init();
            curl_setopt_array($curl, $curlOpts);
            $response_body = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response_body, true);
        }
    }










    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'pagamento', // email template file to be use
        'Pagamento com ' . $metodo_pagamento . ' - EASYPAY', // email subject
        array(
            '{id_order}' => $rsp[0]['idorder'],
            '{referencia}' => $rsp[0]['reference'],
            '{pagamento}' =>  $metodo_pagamento,
            '{order_details}' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?controller=order-detail&id_order=' . $rsp[0]['idorder'],
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        $customer->email, // receiver email address 
        NULL, //receiver name
        NULL, //from email address
        NULL,  //from name
        NULL,
        NULL,
        _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/easypay/mails/'
    );


    //Verificar si es multibanco
    $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
    $rspp = Db::getInstance()->executeS($vbm);




    $headers = [
        "AccountId: " . Configuration::get('EASYPAY_API_ID'),
        "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
        'Content-Type: application/json',
    ];

    if (Configuration::get('EASYPAY_TESTES') == 1) {
        $URL_EP = "https://api.test.easypay.pt/2.0/single";
    } else {
        $URL_EP = "https://api.prod.easypay.pt/2.0/single";
    }

    $url = $URL_EP . $respuesta['id'];
    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response2 = json_decode($response_body, true);
} else if ($respuesta['status'] == 'success' && $respuesta['type'] == 'subscription_capture') {



    /*CHANGE ORDER STATUS*/
    $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_SUBSCRICAO_PAID'), (int)$objOrder->id);
    $history->add();

    $sql = 'UPDATE ' . _DB_PREFIX_ . 'ep_orders SET status="ok", messagem="' . $respuesta['messages'][0] . '" WHERE id_cart=' . $respuesta['key'];
    $excel = Db::getInstance()->execute($sql);




    $sql2 = "SELECT " . _DB_PREFIX_ . "ep_orders.*, " . _DB_PREFIX_ . "orders.id_order idorder, " . _DB_PREFIX_ . "orders.reference, " . _DB_PREFIX_ . "orders.id_customer FROM " . _DB_PREFIX_ . "ep_orders INNER JOIN " . _DB_PREFIX_ . "orders ON " . _DB_PREFIX_ . "orders.id_cart = " . _DB_PREFIX_ . "ep_orders.id_cart WHERE " . _DB_PREFIX_ . "ep_orders.id_cart=" . $respuesta['key'] . "";




    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);








    $metodo_pagamento = $sql2;

    if ($rsp[0]['method'] == 'cc') {
        $metodo_pagamento = "VISA - Subscrição";
    } else if ($rsp[0]['method'] == 'mb') {
        $metodo_pagamento = "Multibanco - Subscrição";
    } else if ($rsp[0]['method'] == 'bb') {
        $metodo_pagamento = "Boleto Bancario - Subscrição";
    } else if ($rsp[0]['method'] == 'dd') {
        $metodo_pagamento = "Debito Direto - Subscrição";
    } else if ($rsp[0]['method'] == 'mbw') {
        $metodo_pagamento = "MBWAY - Subscrição";
    }


    /*Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'pagamento_sub', // email template file to be use
            'Pagamento com '.$metodo_pagamento.' - EASYPAY', // email subject
            array(
                '{id_order}' => $rsp[0]['idorder'],
                '{referencia}' => $rsp[0]['reference'],
                '{pagamento}' =>  $metodo_pagamento,
                '{order_details}' => _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-detail&id_order='.$rsp[0]['idorder'],
                '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
            ),
            $customer->email, // receiver email address 
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL,
            NULL,
            _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );*/







    //CAPTURAR EL PAGO Y ENVIAR RESPUESTA
    $headers = [
        "AccountId: " . Configuration::get('EASYPAY_API_ID'),
        "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
        'Content-Type: application/json',
    ];

    if (Configuration::get('EASYPAY_TESTES') == 1) {
        $URL_EP = "https://api.test.easypay.pt/2.0/subscription/";
    } else {
        $URL_EP = "https://api.prod.easypay.pt/2.0/subscription/";
    }

    $url = $URL_EP . $respuesta['id'];
    $curlOpts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $curlOpts);
    $response_body = curl_exec($curl);
    curl_close($curl);
    $response2 = json_decode($response_body, true);



    $lista_de_pagamentos = '<table style="width:100%"><tr><th>Nº Fatura</th><th>VALOR</th><th>DATA</th></tr>';

    $n_contador = count($response2['transactions']);
    $contador = 1;
    $alumbrar = '';
    $valor_final = '';
    $data_final = '';
    $data_final1 = date('Y-m-d H:i');

    foreach ($response2['transactions'] as $transaction) {
        if ($n_contador == $contador) {
            $alumbrar = 'background-color: orange;';
        }
        $lista_de_pagamentos .= '<tr style="' . $alumbrar . '"><td style="text-align: center;">' . $transaction['document_number'] . '</td><td style="text-align: center;">' . round($transaction['values']['paid'], 2) . ' ' . $response2['currency'] . '</td><td style="text-align: center;">' . $data_final1 . '</td></tr>';
        $valor_final = round($transaction['values']['paid'], 2);
        $data_final = $transaction['transfer_date'];
        $contador = $contador + 1;
    }

    $data_final = date('Y-m-d H:i');

    $lista_de_pagamentos .= '</table>';


    //GET PRODUCTS FROM ORDER
    $sql3 = "SELECT * FROM " . _DB_PREFIX_ . "orders inner join " . _DB_PREFIX_ . "order_detail ON " . _DB_PREFIX_ . "orders.id_order = " . _DB_PREFIX_ . "order_detail.id_order WHERE " . _DB_PREFIX_ . "orders.reference='" . $rsp[0]['reference'] . "'";
    $produtos_order = Db::getInstance()->executeS($sql3);

    $lista_de_produtos = '<table style="width:100%"><tr><th>PRODUTOS NESTA SUBSCRIÇÃO</th><th>VALOR</th></tr>';
    foreach ($produtos_order as $mproduto) {
        $lista_de_produtos .= '<tr><td style="text-align: center"><b>' . $mproduto['product_name'] . '<b></td><td style="text-align: center">' . Tools::displayPrice(round($mproduto['total_price_tax_incl'], 2)) . '</td></tr>';
    }

    $lista_de_produtos .= '</table>';

    $get_id_order = "SELECT * FROM " . _DB_PREFIX_ . "orders WHERE reference='" . $rsp[0]['reference'] . "'";



    $orden_actual = Db::getInstance()->executeS($get_id_order);

    $actualizar_table = "UPDATE " . _DB_PREFIX_ . "subscrip SET
    estado_act = 'OK',
    dt_ult_cob = NOW(),
    n_cob_eftd = n_cob_eftd + 1,
    val_cobrado = val_cobrado + " . $valor_final . " 
    WHERE id_order=" . $orden_actual[0]['id_order'] . "";



    $atualizar = Db::getInstance()->execute($actualizar_table);




    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'pagamentosub', // email template file to be use
        "Pagamento com " . $metodo_pagamento . "", // email subject
        array(
            '{id_order}' => $rsp[0]['reference'],
            '{customer_name}' => $customer->firstname . ' ' . $customer->lastname,
            '{lista_de_produtos}' => $lista_de_produtos,
            '{Transactions}' => $response2['transactions'],
            '{currency}' => $response2['currency'],
            '{tabla}' => $lista_de_pagamentos,
            '{precos}' => $valor_final,
            '{data_final}' => $data_final,
            '{transacciones}' => json_encode($response2['transactions']),
            '{respuesta}' => json_encode($response2['currency']),
            '{cobros}' => json_encode($response2['transactions']),
            '{order_details}' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?controller=order-detail&id_order=' . $rsp[0]['idorder'],
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        $customer->email, // receiver email address 
        NULL, //receiver name
        NULL, //from email address
        NULL,  //from name
        NULL,
        NULL,
        _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/easypay/mails/'
    );



    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'pagamentosub_admin', // email template file to be use
        "Pagamento com " . $metodo_pagamento . "", // email subject
        array(
            '{id_order}' => $rsp[0]['reference'],
            '{customer_name}' => $customer->firstname . ' ' . $customer->lastname,
            '{lista_de_produtos}' => $lista_de_produtos,
            '{Transactions}' => $response2['transactions'],
            '{currency}' => $response2['currency'],
            '{tabla}' => $lista_de_pagamentos,
            '{precos}' => $valor_final,
            '{data_final}' => $data_final,
            '{transacciones}' => json_encode($response2['transactions']),
            '{respuesta}' => json_encode($response2['currency']),
            '{cobros}' => json_encode($response2['transactions']),
            '{order_details}' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?controller=order-detail&id_order=' . $rsp[0]['idorder'],
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        Configuration::get('PS_SHOP_NAME'), // receiver email address 
        NULL, //receiver name
        NULL, //from email address
        NULL,  //from name
        NULL,
        NULL,
        _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/easypay/mails/'
    );
} else {

    /*CHANGE ORDER STATUS*/



    $objOrder = new Order(Order::getOrderByCartId((int)($respuesta['key'])));
    $history = new OrderHistory();
    $history->id_order = (int)$objOrder->id;
    $history->changeIdOrderState(Configuration::get('EASYPAY_FAILED'), (int)$objOrder->id);
    $history->add();
    $sql = 'UPDATE ' . _DB_PREFIX_ . 'ep_orders SET status="error", messagem="' . $respuesta['messages'][0] . '" WHERE id_cart=' . $respuesta['key'];
    $exec = Db::getInstance()->execute($sql);

    $id = $respuesta['id']; //este es el id del pagamento, de acuerdo a docs de easypay.


    $sql2 = "SELECT " . _DB_PREFIX_ . "ep_orders.*, " . _DB_PREFIX_ . "orders.id_order idorder, " . _DB_PREFIX_ . "orders.reference, " . _DB_PREFIX_ . "orders.id_customer FROM " . _DB_PREFIX_ . "ep_orders INNER JOIN " . _DB_PREFIX_ . "orders ON pss_orders.id_cart = " . _DB_PREFIX_ . "ep_orders.id_cart WHERE " . _DB_PREFIX_ . "ep_orders.id_cart=" . $respuesta['key'] . "";
    $rsp = Db::getInstance()->executeS($sql2);
    $customer = new Customer((int)$rsp[0]['id_customer']);

    $metodo_pagamento = '';

    if ($rsp[0]['method'] == 'cc') {
        $metodo_pagamento = "VISA";
    } else if ($rsp[0]['method'] == 'mb') {
        $metodo_pagamento = "Multibanco";
    } else if ($rsp[0]['method'] == 'bb') {
        $metodo_pagamento = "Boleto Bancario";
    } else if ($rsp[0]['method'] == 'dd') {
        $metodo_pagamento = "Debito Direto";
    } else if ($rsp[0]['method'] == 'mbw') {
        $metodo_pagamento = "MBWAY";
    }


    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'pagamentoerr', // email template file to be use
        'Pagamento com ' . $metodo_pagamento . ' - EASYPAY', // email subject
        array(
            '{message}' => $respuesta['messages'][0],
            '{id_order}' => $rsp[0]['idorder'],
            '{referencia}' => $rsp[0]['reference'],
            '{pagamento}' =>  $metodo_pagamento,
            '{order_details}' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?controller=order-detail&id_order=' . $rsp[0]['idorder'],
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        $customer->email, // receiver email address 
        NULL, //receiver name
        NULL, //from email address
        NULL,  //from name
        NULL,
        NULL,
        _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/easypay/mails/'
    );


    //Verificar si es multibanco
    $vbm = "SELECT * FROM " . _DB_PREFIX_ . "ep_requests WHERE id_cart = " . $respuesta['key'] . ";";
    $rspp = Db::getInstance()->executeS($vbm);

    //Si no es multibanco, entonces hacer capture
    if (isset($rspp[0]) && $rspp[0]['method_type'] != "mb") {
        $headers = [
            "AccountId: " . Configuration::get('EASYPAY_API_ID'),
            "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
            'Content-Type: application/json',
        ];

        if (Configuration::get('EASYPAY_TESTES') == 1) {
            $URL_EP = "https://api.test.easypay.pt/2.0/single";
        } else {
            $URL_EP = "https://api.prod.easypay.pt/2.0/single";
        }

        $url = $URL_EP . $id;
        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $response_body = curl_exec($curl);
        curl_close($curl);
        $response2 = json_decode($response_body, true);
        print_r($response2);
    }


    echo 'PAGAMENTO COM ERRO';
}
