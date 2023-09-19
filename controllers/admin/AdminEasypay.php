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

include(_PS_MODULE_DIR_ . 'easypay/easypay.php');


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

		if (Tools::getValue('pesquisar')) {
			$sql = "SELECT * FROM esp_subscrip INNER JOIN " . _DB_PREFIX_ . "orders a ON a.id_order = esp_subscrip.id_order AND (a.reference LIKE '%" . Tools::getValue('pesquisar') . "%' OR a.id_order = '" . Tools::getValue('pesquisar') . "' ) ORDER BY id_susc DESC";
		} else {
			$sql = "SELECT * FROM " . _DB_PREFIX_ . "subscrip ORDER BY id_susc DESC";
		}


		// die($sql);

		$subs = Db::getInstance()->executeS($sql);
		$this->context->smarty->assign(
			array(
				'subs' => $subs
			)

		);
		return $this->module->display(_PS_MODULE_DIR_ . 'easypay', 'views/templates/admin/teste.tpl');
	}
}