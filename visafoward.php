<?php

include_once('../../config/config.inc.php');
include_once('../../init.php');

$newURL = _PS_BASE_URL_.__PS_BASE_URI__."/index.php?controller=history";

if (Tools::getValue('s') != 'ok' and Tools::getValue('s') != 'ok0') {
    If($result !== TRUE){
        $response = sprintf("Error updating order record: %s\n", mysqli_connect_error());
    }
        
    echo '<div style="text-align: center;">O pagamento não foi aceite, verique o seus dados e pode tentar novamente desde "A minha conta->Encomendas->Details"<br><a href="'.$newURL.'"><button>Ir agora</button></a></div>';
}else{
	echo '<div style="text-align: center;">O seu método de pagamento foi aceite, estamos processando a operação, entre tanto pode ver a sua fatura no seguinte url.<br><a href="'.$newURL.'"><button>Ir agora</button></a></div>';
}
  
?>