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
 ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayFrequentvisaModuleFrontController extends ModuleFrontController
{








    private function create_pago_simple($order, $cart_number){
        

      

       //Validar si todos los articulos son de suscripcion    
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

        }





        
        if($prod_ss>0 && $prod_ns>0){
            
                print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" e "não subscrição" ao mesmo tempo, não será possível usar os pagamentos EasyPay. Efetue a devida correcção por favor.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
                
        }
        if($prod_ss>1){
            
            print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro: Existem produtos de "subscrição" com quantidades diferentes de 1, não será possível continuar o pagamento.</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
            
        }







        
        $cart = $this->context->cart;
        $currency = new CurrencyCore($cart->id_currency);

        $headers = [
                        "AccountId: ".Configuration::get('EASYPAY_API_ID'),
                        "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
                        'Content-Type: application/json',
                    ];


        




        if($prod_ss>0){
            
            
            
            //Os produtos são de subscrição
            
            $cart = $this->context->cart;
            $address = new Address((int)$cart->id_address_invoice);
            $currency = new CurrencyCore($cart->id_currency);

            
            //Comprovar si tiene FREQUENCY Y EXP TIME
            $features = $productos_actuales[0]['features'];
            $tiene_la_feature = 0;
            
            foreach($features as $feature){
                
                if((int)$feature['id_feature']==(int)Configuration::get('EASYPAY_FREQUENCY')){
                    
                    $expiration = $feature['id_feature_value'];
                    $tiene_la_feature = 1;
                    
                }
                
                
                
                $tiene_exp_time = 0;
                $exp_time = 0;
                if((int)$feature['id_feature']==(int)Configuration::get('EASYPAY_EXP_TIME')){
                    
                    $exp_time = $feature['id_feature_value'];
                    $tiene_exp_time = 1;
                    
                }
                
            }
            
            if($tiene_la_feature!=1){
                
                    print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve ser definido FREQUENCY no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                        die();
                    
            }
            
            if($tiene_exp_time!=1){
                
                    print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Error: Para comprar este artigo deves contatar ao administrador. (Deve ser definido EXP_TIME no produto)</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                        die();
                    
            }




            //GET FREQUENCY NAME
            $feature_value = new FeatureValue($expiration);
            $expiration_final =$feature_value->value[(int)Configuration::get('PS_LANG_DEFAULT')];


            //GET EXPIRATION NAME
            $feature_value2 = new FeatureValue($exp_time);
            $expiration_final2 = $feature_value2->value[(int)Configuration::get('PS_LANG_DEFAULT')];


            $this->freq = $expiration_final;

                $arraypush = true;

            
                if($expiration_final2=='1 mês'){
                    
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+1 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='2 meses'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+2 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='3 meses'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+3 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='4 meses'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+4 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='5 meses'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+5 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='6 meses'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+6 month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if($expiration_final2=='1 ano'){
                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+1 year' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if(substr($expiration_final2, -1)=='d' or substr($expiration_final2, -1)=='D'){

                    $numero_r = substr($expiration_final2, 0, -1);

                    $fecha = date('Y-m-d H:i');

                    
                    $nuevafecha = strtotime ( '+'.$numero_r.' day' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if(substr($expiration_final2, -1)=='w' or substr($expiration_final2, -1)=='W'){
                    
                    $numero_r = substr($valor, 0, -1);

                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+'.$numero_r.' week' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if(substr($expiration_final2, -1)=='m' or substr($expiration_final2, -1)=='M'){
                    
                    $numero_r = substr($valor, 0, -1);

                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+'.$numero_r.' month' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }
                else if(substr($expiration_final2, -1)=='y' or substr($expiration_final2, -1)=='Y'){
                    
                    $numero_r = substr($valor, 0, -1);

                    $fecha = date('Y-m-d H:i');
                    $nuevafecha = strtotime ( '+'.$numero_r.' year' , strtotime ( $fecha ) ) ;
                    $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
                    
                    $this->exptime = $nuevafecha;
                    $final_expdate = $nuevafecha;
                }else{
                    $final_expdate = $final_expdate = '2035-12-12 15:30';

                }


            
            
            if(Configuration::get('EASYPAY_TESTES')==1){
                $URL_EP = "https://api.test.easypay.pt/2.0/subscription";
            }else{
                $URL_EP = "https://api.prod.easypay.pt/2.0/subscription";
            }



            $body = [

                "frequent_id" => ''.Tools::getValue('id_payment').'',
                "key" => ''.$cart_number->cart->id.'',
                "retries" =>$retries,
                "capture_now" => true,
                "method" => "cc", //solo acepta dd y cc
                "expiration_time"=>$final_expdate,
                "type"  => "sale",
                "value" => round((float)$order, 2), //precio, requerido
                "frequency"=> $expiration_final, //requerido, frequencia con la que se realizara el pago, los valores son "1D" "1W" "2W" "1M" "2M" "3M" "4M" "6M" "1Y", D significa dias, W semanas, M meses
                "currency"  => $currency->iso_code,
                "start_time"    => gmdate('Y-m-d H:i', strtotime("+5 min")),  //documentacion dice que es opcional y required al mismo tiempo, marca cuando empieza a cobrar
                "capture" => [
                    "transaction_key" => ''.$cart->id.'',
                    "descriptive" => Configuration::get('PS_SHOP_NAME'), // esto es requerido aqui
                   //"capture_date" => "2018-12-31",
                ],
                
                "customer" => [
                    "name" => $address->firstname.' '.$address->lastname,
                    "email" => $this->context->customer->email,
                    "key" => ''.$cart->id.'',
                    //"phone_indicative" => "+351",
                    //"fiscal_number" =>"PT123456789",
                ],
    
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
            
            $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated) VALUES ('".$response['status']."', '".$response['id']."', '".$response['method']['type']."', '".$response['method']['status']."', '".$response['method']['entity']."', '".$response['method']['reference']."', '".$response['customer']['id']."', ".$cart_number->cart->id.", NOW(), NOW())";
            
            Db::getInstance()->execute($sql);
            $response['exp_time_trig'] = $final_expdate;

        }else{



            $modo_de_pago = 'frequent';


            if(Configuration::get('EASYPAY_AUTORIZAR_PAGOS')==1){

                $body = [

                    "transaction_key" => ''.$cart_number->cart->id.'', 
                    "descriptive" => "Pagamento EasyPay",
                    "capture_date" => date("Y-m-d"),
                    "value" => round((float)$order, 2), 

                ];




                if(Configuration::get('EASYPAY_TESTES')==1){
                    $URL_EP = "https://api.test.easypay.pt/2.0/capture/".Tools::getValue('id_payment');
                }else{
                   $URL_EP = "https://api.prod.easypay.pt/2.0/capture/".Tools::getValue('id_payment');
                }



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
                     print('<div style="width: 100%; text-align: center; margin-top: 30px;"><div style="width: 90%; max-width: 900px; display: inline-block; padding: 10px 20px; background-color: rgba(247, 37, 22, .1); border: 1px solid rgb(247, 37, 22); border-radius: 5px;"><b>Erro:'.$response['message'][0].'</b></div><br><a style="color: black;" href="/index.php?controller=Order"><div style="padding: 10px 20px; margin-top: 30px; cursor: pointer; display: inline-block; background-color: #e8e8e8; border-radius: 20px;"><b>Corrigir</b></div></div></div>');
                    die();
                }


                if(!isset($response['status']) or !isset($response['method']['status']) or !isset($response['customer']['id'])){
                    $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('', '".$response['id']."', 'cc', '', '', '', '', ".$cart_number->cart->id.", NOW(), NOW(), '".$modo_de_pago."', '', ".$this->context->customer->id.")";
                }else{
                    $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('".$response['status']."', '".$response['id']."', 'cc', '".$response['method']['status']."', '', '', '".$response['customer']['id']."', ".$cart_number->cart->id.", NOW(), NOW(), '".$modo_de_pago."', '".$nome_mp."', ".$this->context->customer->id.")";
                }
                
                // die($sql);
                Db::getInstance()->execute($sql);

                $new_trans = "INSERT INTO "._DB_PREFIX_."ep_frequent_transactions (id_user, id_pagamento, tipo_pagamento, autorizado, valor) VALUES ('".$this->context->customer->id."', '".Tools::getValue('id_payment')."', 'frequent_visa', 2, ".round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2).");";

                Db::getInstance()->execute($new_trans);

            }else{
                if(!isset($nome_mp)){
                    $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('ok', '".Tools::getValue('id_payment')."', 'cc', 'pending', '', '', '".$this->context->customer->id."', ".$cart_number->cart->id.", NOW(), NOW(), '".$modo_de_pago."', '', ".$this->context->customer->id.")";
                }else{
                    $sql = "INSERT INTO "._DB_PREFIX_."ep_requests (status, id_ep_request, method_type, method_status, method_entity, method_reference, customer_easypay, id_cart, first_date, updated, modo_de_pago, nombre_de_pago, id_user) VALUES ('ok', '".Tools::getValue('id_payment')."', 'cc', 'pending', '', '', '".$this->context->customer->id."', ".$cart_number->cart->id.", NOW(), NOW(), '".$modo_de_pago."', '".$nome_mp."', ".$this->context->customer->id.")";
                }
                
                // die($sql);
                Db::getInstance()->execute($sql);

                $new_trans = "INSERT INTO "._DB_PREFIX_."ep_frequent_transactions (id_user, id_pagamento, tipo_pagamento, autorizado, valor, ativado, id_cart) VALUES ('".$this->context->customer->id."', '".Tools::getValue('id_payment')."', 'frequent_visa', 0, ".round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2).", 1, $cart->id);";

                Db::getInstance()->execute($new_trans);

                $response = "no_capturado";




            }

            

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
            Configuration::get('EASYPAY_PAGO_NAO_AUT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'Cartões Visa / Mastercard - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );


    print_r('easypay');
    
    Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'visa', // email template file to be use
        'Pagamento com Visa / Mastercard - EASYPAY', // email subject
        array(
            '{URL}' => '',
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
        
        if($multibanco!="no_capturado" && isset($multibanco['status']) && $multibanco['status']!='ok'){

            print_r($multibanco);
            
            
            die('ERRO DE COMUNICAÇÂO COM O API DO EASYPAY TENTE NOVAMENTE EM 5 MINUTOS');
        }
            
        $sql = "INSERT INTO "._DB_PREFIX_."ep_orders (method, id_cart, link, title) VALUES ('cc', ".(int)$cart->id.", '', 'Pagar Agora: ')";
        Db::getInstance()->execute($sql);


        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&method='.'ccf'.'&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH).'&url=');
        }
    }
    


}