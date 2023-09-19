<?php
include(_PS_MODULE_DIR_.'easypay/easypay.php');


class AdminEasyPayController extends ModuleAdminController
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

			if(isset($_POST['pesquisar'])){
				$sql = "SELECT * FROM esp_subscrip INNER JOIN "._DB_PREFIX_."orders a ON a.id_order = esp_subscrip.id_order AND (a.reference LIKE '%".$_POST['pesquisar']."%' OR a.id_order = '".$_POST['pesquisar']."' ) ORDER BY id_susc DESC";
				
			}else{
				$sql = "SELECT * FROM "._DB_PREFIX_."subscrip ORDER BY id_susc DESC";
			}


			// die($sql);

	        $subs = Db::getInstance()->executeS($sql);
	        $this->context->smarty->assign(
                array(
                    'subs' => $subs
                    )

            );
	        return $this->module->display(_PS_MODULE_DIR_.'easypay', 'views/templates/admin/teste.tpl');
	}

}

?>