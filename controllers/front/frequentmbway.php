<?php
/*
 * Easypay
 * @author Trigenius
 *
 * @copyright Direitos autorais (c) 2023 Trigenius
 * 
 * 
 * Todos os direitos reservados.
 * 
 * @license É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 * 
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 */
ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayFrequentmbwayModuleFrontController extends ModuleFrontController
{



    private function create_pago_simple($order, $cart_number)
    {



        //Validar si todos los articulos son de suscripcion    
        $productos_actuales = Context::getContext()->cart->getProducts();
        $cat_valido = 1;
        $productos_in = 0;

        foreach ($productos_actuales as $product_act) {

            if ((int)$product_act['id_category_default'] == (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                $cat_valido = 0;
            }
            $productos_in = $productos_in + 1;
        }

        if ($cat_valido != 1 && $productos_in != 1) {

            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" e "não subscrição" ao mesmo tempo, não será possível usar os pagamentos EasyPay. Efetue a devida correcção por favor.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
            die();
        }



        if ($cat_valido != 1) {

            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" com quantidades diferentes de 1, não será possível continuar o pagamento.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
            die();
        }

        $cart = $this->context->cart;
        $currency = new CurrencyCore($cart->id_currency);



        $modo_de_pago = 'frequent';



        $body = [

            "transaction_key" => '' . $cart_number->cart->id . '',
            "descriptive" => "Pagamento EasyPay",
            "capture_date" => date("Y-m-d"),
            "value" => round((float)$order, 2),

        ];

        if (Configuration::get('EASYPAY_AUTORIZAR_PAGOS') == 1) {
            if (Configuration::get('EASYPAY_TESTES') == 1) {
                $URL_EP = "https://api.test.easypay.pt/2.0/capture/" . Tools::getValue('id_payment');
            } else {
                $URL_EP = "https://api.prod.easypay.pt/2.0/capture/" . Tools::getValue('id_payment');
            }



            $headers = [
                "AccountId: " . Configuration::get('EASYPAY_API_ID'),
                "ApiKey: " . Configuration::get('EASYPAY_API_KEY'),
                'Content-Type: application/json',
            ];

            $curlOpts = [
                CURLOPT_URL => $URL_EP,
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

            if ($response['status'] == 'error') {
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro:' . $response['message'][0] . '</b></div><br><a style="color: black;" href="' . __PS_BASE_URI__ . 'index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                die();
            }

            if (isset($response['method'])) {
                $tipo_metodo_tipo = $response['method']['type'];
                $tipo_metodo_status = $response['method']['status'];
            } else {
                $tipo_metodo_tipo = '';
                $tipo_metodo_status = '';
            }

            if (isset($response['customer'])) {
                $response_customer_id = $response['customer']['id'];
            } else {
                $response_customer_id = '';
            }

            $sql = "INSERT INTO " . _DB_PREFIX_ . "ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('" . $response['status'] . "', '" . $response['id'] . "', '" . $tipo_metodo_tipo . "', '" . $tipo_metodo_status . "', '', '', '" . $response_customer_id . "', " . $cart_number->cart->id . ", NOW(), NOW(), '" . $modo_de_pago . "', '', " . $this->context->customer->id . ")";
        } else {

            $sql = "INSERT INTO " . _DB_PREFIX_ . "ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('ok', '" . Tools::getValue('id_payment') . "', 'mbw', 'pending', '', '', '" . $this->context->customer->id . "', " . $cart_number->cart->id . ", NOW(), NOW(), '" . $modo_de_pago . "', '" . $nome_mp . "', " . $this->context->customer->id . ")";
            // die($sql);
            Db::getInstance()->execute($sql);


            $new_trans = "INSERT INTO " . _DB_PREFIX_ . "ep_frequent_transactions (id_user, id_pagamento, tipo_pagamento, autorizado, valor, ativado, id_cart) VALUES ('" . $this->context->customer->id . "', '" . Tools::getValue('id_payment') . "', 'frequent_mbw', 0, " . round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2) . ", 1, " . $cart_number->cart->id . ");";



            Db::getInstance()->execute($new_trans);

            return ("auth");
        }


        Db::getInstance()->execute($sql);


        return $response;
    }


    /*
     * Processa os dados enviados pelo formulário de pagamento
     */
    public function postProcess()
    {

        /*
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $cart_number = $this->context->cart;
        $authorized = false;
        /*
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (
            !$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /*
         * Verify if this payment module is authorized
         */

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'easypay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }

        /* @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);

        /*
         * Check if this is a valid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /*
         *Validar pago com mutlibanco
         */


        /*
         * Place the order
         */
        $order = $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('EASYPAY_BMWAY_WAIT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'Mbway - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );




        if ($order == 1) {
            $esql = "SELECT * FROM " . _DB_PREFIX_ . "orders WHERE id_cart=" . $cart->id;
            $seleccion = Db::getInstance()->executeS($esql);


            $multibanco = $this->create_pago_simple($seleccion[0]['total_paid'], $this->context);


            if ($multibanco != "auth" && $multibanco['status'] != 'ok') {

                print_r($multibanco);
                die('ERRO DE COMUNICAÇÂO COM O API DO EASYPAY TENTE NOVAMENTE EM 5 MINUTOS');
            }

            $sql = "INSERT INTO " . _DB_PREFIX_ . "ep_orders (method, id_cart, link, title) VALUES ('', " . (int)$cart->id . ", '', 'Pagar Agora: ')";
            Db::getInstance()->execute($sql);


            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' . (int)$this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key . '&method=' . 'ccf' . '&monto=' . ' ' . (float) $this->context->cart->getOrderTotal(true, Cart::BOTH) . '');
        }
    }
}