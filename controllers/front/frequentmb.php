<?php
/**
 */
 ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayFrequentmbModuleFrontController extends ModuleFrontController
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
        
        $sql = 'SELECT * FROM '._DB_PREFIX_.'ep_requests WHERE id_ep_request="776d5b97-13ea-4fca-9592-0ebb999f4bd8"';
        
        $resultado = Db::getInstance()->executeS($sql);
        
        
        $insertar = "INSERT INTO "._DB_PREFIX_."ep_last_mb (cart, id_pagamento) VALUES (".$this->context->cart->id.", '0')";
        Db::getInstance()->execute($insertar);
        

        return true;
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
            Configuration::get('EASYPAY_MB_WAIT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'MULTIBANCO - EasyPay',
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );



    
    // Mail::Send(
    //     (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
    //     'visa', // email template file to be use
    //     'Pagamento com VISA - EASYPAY', // email subject
    //     array(
    //         '{URL}' => $multibanco['method']['url'],
    //         '{SHOPNAME}' => Configuration::get('PS_SHOP_NAME'),
    //     ),
    //     $this->context->customer->email, // receiver email address 
    //     NULL, //receiver name
    //     NULL, //from email address
    //     NULL,  //from name
    //     NULL,
    //     NULL,
    //     _PS_BASE_URL_.__PS_BASE_URI__.'modules/easypay/mails/'
    // );
        /**
         * Redirect the customer to the order confirmation page
         */

    if($order==1){
        $esql = "SELECT * FROM "._DB_PREFIX_."orders WHERE id_cart=".$cart->id;
        $seleccion = Db::getInstance()->executeS($esql);


        $multibanco = $this->create_pago_simple($seleccion[0]['total_paid'], $this->context);

        

        $qql = "SELECT * FROM "._DB_PREFIX_."ep_requests WHERE id_ep_request='".Tools::getValue('id_payment')."';";
        $seleccion2 = Db::getInstance()->executeS($qql);






        // if($multibanco['status']!='ok'){

        //     print_r($multibanco);
        //     die('ERRO DE COMUNICAÇÂO COM O API DO EASYPAY TENTE NOVAMENTE EM 5 MINUTOS');
        // }
            
        $sql = "INSERT INTO "._DB_PREFIX_."ep_orders (method, id_cart, link, title) VALUES ('mb', ".(int)$cart->id.", '-', 'Pagar Agora: ')";
        Db::getInstance()->execute($sql);


        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&method=mb&entity='.$seleccion2[0]['entidade'].'&reference='.$seleccion2[0]['referencia'].'&monto='.' '.(float) $this->context->cart->getOrderTotal(true, Cart::BOTH));
        }
    }
    


}