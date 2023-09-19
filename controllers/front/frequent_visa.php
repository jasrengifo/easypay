<?php

ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayVisaModuleFrontController extends ModuleFrontController
{

    private function create_pago_simple(){
        
        $productos_actuales = Context::getContext()->cart->getProducts();
        $productos_in = 0;

        $prod_ss = 0;
        $prod_ns = 0;
        
        foreach($productos_actuales as $product_act){
            
            if((int)$product_act['id_category_default'] == (int)Configuration::get('EASYPAY_CATEGORY_SUSCP')){
                $prod_ss = $prod_ss + 1;
            }else{
                $prod_ns = $prod_ns + 1;
            }

            $productos_in = $productos_in + 1;
        }
        
        if($prod_ss>0 && $prod_ns>0){
            
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" e "não subscrição" ao mesmo tempo, não será possível usar os pagamentos EasyPay. Efetue a devida correcção por favor.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
        }

        $cart = $this->context->cart;
        $currency = new CurrencyCore($cart->id_currency);


        //SI EL PRODUCTO ES DE SUBCRIPCION
        if($prod_ss>0){
            echo 'trataremos esto como Subscrição';
            die();
        }else{
            echo 'trataremos esto como não subscrição';
            die();
         //SI EL PRODUCTO NO ES DE SUBSCRIPCIÓN   
            $modo_de_pago = 'single';

            if(isset($_POST['guardar-metodo']) && $_POST['guardar-metodo']){
            
            $modo_de_pago = 'frequent';

            $body = [
                "key" => ''.$cart->id.'',
                "method" => "cc",
                "type" => "sale",
                "min_value" => floatval(1), //Precio minimo que puede pagar el cliente
                "max_value" => floatval(500), //Precio maximo que puede pagar el cliente
                "currency" => $currency->iso_code,
                "expiration_time" =>"2020-12-31 12:00", //fecha en la que expira el pago frecuente ********CAMBIAR******
                "customer" => [
                    "name" => $this->context->customer->firstname.' '.$this->context->customer->lastname,
                    "email" => $this->context->customer->email,
                    "key" => ''.$cart->id.'',
                ],
            ];

            if(Configuration::get('EASYPAY_TESTES')==1){
                $URL_EP = "https://api.test.easypay.pt/2.0/frequent";
            }else{
                $URL_EP = "https://api.prod.easypay.pt/2.0/frequent";
            }

        }else{

            $body = [
                "key" => ''.$cart->id.'',
                "method" => "cc",
                "type"  => "sale",
                "value" => round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2),
                "currency"  => $currency->iso_code,
                "capture" => [
                    "transaction_key" => ''.$cart->id.'',
                    "descriptive" => Configuration::get('PS_SHOP_NAME'),
                    
                ],
                "customer" => [
                    "name" => $this->context->customer->firstname.' '.$this->context->customer->lastname,
                    "email" => $this->context->customer->email,
                    "key" => ''.$cart->id.'',
                ],

            ];

            if(Configuration::get('EASYPAY_TESTES')==1){
                $URL_EP = "https://api.test.easypay.pt/2.0/single";
            }else{
                  $URL_EP = "https://api.prod.easypay.pt/2.0/single";
            }

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

        if($response['status']=='error'){
                     print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro:'.$response['message'][0].'</b></div><br><a style="color: black;" href="'.__PS_BASE_URI__.'index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
                }

        $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('".$response['status']."', '".$response['id']."', '".$response['method']['type']."', '".$response['method']['status']."', '', '', '".$response['customer']['id']."', ".$cart->id.", NOW(), NOW(), '".$modo_de_pago."', '".$nome_mp."', ".$this->context->customer->id.")";
        Db::getInstance()->execute($sql);
        }

        return $response;
    }
    

    public function postProcess()
    {
        $cart = $this->context->cart;
        $authorized = false;

        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }
 
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
 
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $multibanco = $this->create_pago_simple();
        if($multibanco['status']!='ok'){

            die('ERRO DE COMUNICAÇÂO COM O API DO EASYPAY TENTE NOVAMENTE EM 5 MINUTOS');
        }
        
        $sql = "INSERT INTO "._DB_PREFIX_."ep_orders (method, id_cart, link, title) VALUES ('".$multibanco['method']['type']."', ".(int)$cart->id.", '".urlencode($multibanco['method']['url'])."', 'Pagar Agora: ')";
        Db::getInstance()->execute($sql);
        
 
 
        $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('EASYPAY_PAGO_NAO_AUT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'Visa / Mastercard - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );
    print_r('easypay');
    
    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), 
        'visa', 
        'Pagamento com cartões Visa / Mastercard - EASYPAY', 
        array(
            '{URL}' => $multibanco['method']['url'],
            '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
        ),
        $this->context->customer->email, 
        NULL, 
        NULL, 
        NULL, 
        NULL,
        NULL,
        _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    );

    Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&method='.$multibanco['method']['type'].'&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH).'&url='.urlencode($multibanco['method']['url']));
    }


}

?>