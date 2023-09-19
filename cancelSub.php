<?

include_once('../../config/config.inc.php');
include_once('../../init.php');
$id = $_GET['id_sub']; //este es el id del pagamento, de acuerdo a docs de easypay, DEBES hacer este request despues de la notificacion generica para confirmar

error_reporting(E_ALL);
ini_set('display_errors', '1');

$body = [
    "status" => "inactive",

];



    $headers = [
        "AccountId: ".Configuration::get('EASYPAY_API_ID'),
        "ApiKey: ".Configuration::get('EASYPAY_API_KEY'),
        'Content-Type: application/json',
    ];

    if(Configuration::get('EASYPAY_TESTES')==1){
        $url = "https://api.test.easypay.pt/2.0/subscription/" . $id;
    }else{
        $url = "https://api.prod.easypay.pt/2.0/subscription/" . $id;
    }
    

$curlOpts = [
    CURLOPT_URL => $url,
	CURLOPT_CUSTOMREQUEST => "PATCH",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
];

$curl = curl_init();
curl_setopt_array($curl, $curlOpts);
$response_body = curl_exec($curl);
curl_close($curl);
$response = json_decode($response_body, true);


$esql = "UPDATE "._DB_PREFIX_."subscrip
    SET estado_act='INACTIVE'
    WHERE id_ep = '".$id."'";
Db::getInstance()->execute($esql);

$eqql = "SELECT * FROM "._DB_PREFIX_."subscrip WHERE id_ep='".$id."'";
$registro = Db::getInstance()->executeS($eqql);


$objOrder = new Order(Order::getOrderByCartId((int)($registro[0]['id_cart']))); 
$history = new OrderHistory();
$history->id_order = (int)$objOrder->id;
$history->changeIdOrderState(Configuration::get('EASYPAY_SUBSCRICAO_CANCEL'), (int)$objOrder->id);
$history->add();

echo  $_GET['id_sub'];
print_r($response);


header("Location: ".__PS_BASE_URI__."index.php?controller=order-detail&id_order=".$history->id_order);
die();




    
?>