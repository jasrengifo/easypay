<?php
/**
 */
 ini_set('precision', 10);
ini_set('serialize_precision', 10);
class easypayVisafowardModuleFrontController extends ModuleFrontController
{
    public function initContent()
	{
		parent::initContent();

        
            

            $this->context->smarty->assign(array(
                'ativar_nome' => Configuration::get('activar_nome'),
            ));

            $this->setTemplate('module:easypay/views/templates/front/visafoward.tpl');
       
	}
}





?>