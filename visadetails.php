<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
/**
 * Primero revisa parametros del comando GET
 */




if (!Tools::getValue('t_key') || !Tools::getValue('id')) {
    echo "Error: Not enough params";
    exit();
}

/**
 * Tienes que agarrar tu account id que usas para autenticarte, por seguridad revisa que coincida
 * @url https://api.prod.easypay.pt/docs#section/Authentication
 * Revisa bien de donde saca el id aca y todo eso, es el AccountId 
 */

if ($api_auth['account_id'] != Tools::getValue('id')) {
    echo "Error: Data mismatch";
    exit();
}

/**
 * Si todo funciona empieza a escribir la respuesta
 */

// Simple mysqli connection as documented at https://www.php.net/manual/en/mysqli-result.fetch-row.php




try {

    $isOrderX = Db::getInstance()->getRow(' SELECT * FROM '._DB_PREFIX_.'orders WHERE id_cart = '.Tools::getValue('t_key').'');

    $newURL = _PS_BASE_URL_.__PS_BASE_URI__."index.php?controller=order-detail&id_order=".$isOrderX['id_order'];
    Tools::redirect($newURL);

} catch (Exception $ex) {
    $xml.= '<ep_status>'      . 'err'                .'</ep_status>' . PHP_EOL;
    $xml.= '<ep_message>'     . $ex->getMessage()    .'</ep_message>' . PHP_EOL;
    $xml.= '<ep_entity>'      . $obj->ep_entity      .'</ep_entity>' . PHP_EOL;
    $xml.= '<ep_reference>'   . $obj->ep_reference   .'</ep_reference>' . PHP_EOL;
    $xml.= '<ep_value>'       . $obj->ep_value       .'</ep_value>' . PHP_EOL;
}

$xml.= '</get_detail>' . PHP_EOL;
echo $xml;
?>