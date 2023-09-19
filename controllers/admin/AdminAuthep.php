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
	        

	        
	        $this->context->smarty->assign(
                array(
                    'auth' => $auth
                    )

            );
	        return $this->module->display(_PS_MODULE_DIR_.'easypay', 'views/templates/admin/auth.tpl');
	}

}

?>