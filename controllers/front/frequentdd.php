<?php
/**
 */
 ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayFrequentddModuleFrontController extends ModuleFrontController
{








    private function create_pago_simple($order, $cart_number){
        


        //Validar si todos los articulos son de suscripcion    
        $productos_actuales = Context::getContext()->cart->getProducts();
        $cat_valido = 1;
        $productos_in = 0;
        
        foreach($productos_actuales as $product_act){
            if((int)$product_act['id_category_default'] == (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')){
                $cat_valido = 0;
            }
            $productos_in = $productos_in + 1;
        }
        
        if($cat_valido!=1 && $productos_in!=1){
            
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" e "não subscrição" ao mesmo tempo, não será possível usar os pagamentos EasyPay. Efetue a devida correcção por favor.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
                
        }
        if($cat_valido!=1){
            
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" com quantidades diferentes de 1, não será possível continuar o pagamento.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
            
        }
        
        $cart = $this->context->cart;
        $currency = new CurrencyCore($cart->id_currency);



        $modo_de_pago = 'frequent';

 

            $body = [

                "transaction_key" => ''.$cart_number->cart->id.'',
                "descriptive" => "Pagamento EasyPay",
                "capture_date" => date("Y-m-d"),
                "value" => round(floatval($order), 2), 

            ];

        if(Configuration::get('EASYPAY_AUTORIZAR_PAGOS')==1){
            if(Configuration::get('EASYPAY_TESTES')==1){
                $URL_EP = "https://api.test.easypay.pt/2.0/capture/".Tools::getValue('id_payment');
            }else{
               $URL_EP = "https://api.prod.easypay.pt/2.0/capture/".Tools::getValue('id_payment');
            }


            
            


            $headers = [
                "AccountId: ".Configuration::get('EASYPAY_API_ID'),
                "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
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

            if(isset($response['method'])){
                $response_method_estado = $response['method']['status'];
                $response_method_tipo = $response['method']['type'];
            }else{
                $response_method_estado = '';
                $response_method_tipo = '';
            }

            if(isset($response['customer'])){
                $response_customer = $response['customer']['id'];
            }else{
                $response_customer = '';
            }


            if($response['status']=='error'){
                         print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro:'.$response['message'][0].'</b></div><br><a style="color: black;" href="'.__PS_BASE_URI__.'index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                        die();
                    }



            $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('".$response['status']."', '".$response['id']."', '".$response_method_tipo."', '".$response_method_estado."', '', '', '".$response_customer."', ".$cart->id.", NOW(), NOW(), '".$modo_de_pago."', '', ".$this->context->customer->id.")";
            // die($sql);
            Db::getInstance()->execute($sql);
        }else{
             $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('ok', '".Tools::getValue('id_payment')."', 'dd', 'pending', '', '', '".$this->context->customer->id."', ".$cart_number->cart->id.", NOW(), NOW(), '".$modo_de_pago."', '".$nome_mp."', ".$this->context->customer->id.")";
                // die($sql);
            Db::getInstance()->execute($sql);


            $new_trans = "INSERT INTO "._DB_PREFIX_."ep_frequent_transactions (id_user, id_pagamento, tipo_pagamento, autorizado, valor, ativado, id_cart) VALUES ('".$this->context->customer->id."', '".Tools::getValue('id_payment')."', 'frequent_dd', 0, ".round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2).", 1, ".$cart_number->cart->id.");";



            Db::getInstance()->execute($new_trans);

            return("auth");
        }


        return $response;
            }
    

    /**
     * Processa os dados enviados pelo formulário de pagamento
     */
    public function postProcess()
    {
        /**
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $cart_number = $this->context->cart;
        $authorized = false;
        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }
 
        /**
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
 
        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);
 
        /**
         * Check if this is a valid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /**
        *Validar pago com mutlibanco
        */
        
 
        /**
         * Place the order
         */
        $order = $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('EASYPAY_DD_WAIT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'Débito Direto - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );

    
    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'dd', // email template file to be use
        'Pagamento com Débito Direto - EASYPAY', // email subject
        array(
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        $this->context->customer->email, // receiver email address 
        NULL, //receiver name
        NULL, //from email address
        NULL,  //from name
        NULL,
        NULL,
        _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );
        /**
         * Redirect the customer to the order confirmation page
         */

    if($order==1){
        $esql = "SELECT * FROM "._DB_PREFIX_."orders WHERE id_cart=".$cart->id;
        $seleccion = Db::getInstance()->executeS($esql);


        $multibanco = $this->create_pago_simple($seleccion[0]['total_paid'], $this->context);
        
        if($multibanco != "auth" && $multibanco['status']!='ok'){

            print_r($multibanco);
            die('ERRO DE COMUNICAÇÂO COM O API DO EASYPAY TENTE NOVAMENTE EM 5 MINUTOS');
        }

        if(!isset($multibanco['method'])){
            $multibanco_metodo = '';
            $multibanco_metodo_url = '';
        }else{
            $multibanco_metodo = $multibanco['method']['type'];
            $multibanco_metodo_url = $multibanco['method']['url'];
        }
            
        $sql = "INSERT INTO "._DB_PREFIX_."ep_orders (method, id_cart, link, title) VALUES ('".$multibanco_metodo."', ".(int)$cart->id.", '".urlencode($multibanco_metodo_url)."', 'Pagar Agora: ')";
        Db::getInstance()->execute($sql);


        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&method='.'ccf'.'&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH).'');
    }


}
        }
