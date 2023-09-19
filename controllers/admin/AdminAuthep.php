<?php
include(_PS_MODULE_DIR_.'easypay/easypay.php');


class AdminAuthepController extends ModuleAdminController
{


	public function __construct()
	{
	    $this->bootstrap = true;
	    $this->context = Context::getContext();


	    parent::__construct();
	}


	public function initContent()
	{
	     parent::initContent();

	}

	 

	public function renderList()

	{
	    


	    	if(!Tools::getIsset(Tools::getValue('pesquisar'))){
	    		$sql = "SELECT  a.*, b.*, c.*, a.id_cart cartt, ord.id_order id_ord FROM "._DB_PREFIX_."ep_frequent_transactions a INNER JOIN "._DB_PREFIX_."ep_requests b ON b.id_ep_request = a.id_pagamento INNER JOIN "._DB_PREFIX_."orders c ON c.id_cart = b.id_cart INNER JOIN "._DB_PREFIX_."orders ord ON ord.id_cart = a.id_cart WHERE a.autorizado=0 GROUP BY id_trans ORDER BY id_trans DESC";
	        	$auth = Db::getInstance()->executeS($sql);
	    	}else{
	    		$sql = "SELECT a.*, b.*, c.*, a.id_cart cartt, ord.id_order id_ord FROM "._DB_PREFIX_."ep_frequent_transactions a INNER JOIN "._DB_PREFIX_."ep_requests b ON b.id_ep_request = a.id_pagamento INNER JOIN "._DB_PREFIX_."orders c ON c.id_cart = b.id_cart INNER JOIN "._DB_PREFIX_."orders ord ON ord.id_cart = a.id_cart WHERE a.autorizado=0 AND (a.id_user LIKE '%".Tools::getValue('pesquisar')."%' OR c.reference LIKE '%".Tools::getValue('pesquisar')."%' OR a.created LIKE '%".Tools::getValue('pesquisar')."%') GROUP BY id_trans ORDER BY id_trans DESC ";



	        	$auth = Db::getInstance()->executeS($sql);
	    	}
	        
	
	    // 	header('Content-Type: application/json');
	  		// die(json_encode($auth));
	        
	        $this->context->smarty->assign(
                array(
                    'auth' => $auth
                    )

            );
	        return $this->module->display(_PS_MODULE_DIR_.'easypay', 'views/templates/admin/auth.tpl');
	}

}

?>