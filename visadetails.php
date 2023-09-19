<?php
/**
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


include_once('../../config/config.inc.php');
include_once('../../init.php');





if (!Tools::getValue('t_key') || !Tools::getValue('id')) {
    echo "Error: Not enough params";
    exit();
}



if ($api_auth['account_id'] != Tools::getValue('id')) {
    echo "Error: Data mismatch";
    exit();
}




try {

    $isOrderX = Db::getInstance()->getRow(' SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . Tools::getValue('t_key') . '');

    $newURL = _PS_BASE_URL_ . __PS_BASE_URI__ . "index.php?controller=order-detail&id_order=" . $isOrderX['id_order'];
    Tools::redirect($newURL);
} catch (Exception $ex) {
    $xml .= '<ep_status>'      . 'err'                . '</ep_status>' . PHP_EOL;
    $xml .= '<ep_message>'     . $ex->getMessage()    . '</ep_message>' . PHP_EOL;
    $xml .= '<ep_entity>'      . $obj->ep_entity      . '</ep_entity>' . PHP_EOL;
    $xml .= '<ep_reference>'   . $obj->ep_reference   . '</ep_reference>' . PHP_EOL;
    $xml .= '<ep_value>'       . $obj->ep_value       . '</ep_value>' . PHP_EOL;
}

$xml .= '</get_detail>' . PHP_EOL;
echo $xml;
