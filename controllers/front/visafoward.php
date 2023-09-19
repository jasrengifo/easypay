<?php

/**
 * Easypay
 *
 * Direitos autorais (c) 2023 Trigenius
 * 
 * @author Trigenius
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
