<?php



include_once('../../config/config.inc.php');
include_once('../../init.php');
/**
 * Primero revisamos los parametros del GET
 */




// if (!isset($_GET['e']) || !isset($_GET['r']) || !isset($_GET['v']) || !isset($_GET['s']) || !isset($_GET['t_key'])){
//     echo "Error: Not enough params";
//     exit();
// }




$newURL = _PS_BASE_URL_.__PS_BASE_URI__."/index.php?controller=history";

if ($_GET['s'] != 'ok' and $_GET['s'] != 'ok0') {
    If($result !== TRUE){
        $response = sprintf("Error updating order record: %s\n", mysqli_connect_error());
    }
        
    echo '<div style="text-align: center;">O pagamento não foi aceite, verique o seus dados e pode tentar novamente desde "A minha conta->Encomendas->Details"<br><a href="'.$newURL.'"><button>Ir agora</button></a></div>';
}else{
	echo '<div style="text-align: center;">O seu método de pagamento foi aceite, estamos processando a operação, entre tanto pode ver a sua fatura no seguinte url.<br><a href="'.$newURL.'"><button>Ir agora</button></a></div>';
}
  
// /*
// * Aqui guardas la clave de autorizacion en tu db para pedir el pago en el futuro, y que puedas preparar el envio de la orden.
// */  

// $isOrderX = Db::getInstance()->getRow(' SELECT * FROM '._DB_PREFIX_.'orders WHERE id_cart = '.$_GET['t_key'].'');













 //Aqui le muestras el mensaje de success al cliente. 
?>