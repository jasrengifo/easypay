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

$newURL = _PS_BASE_URL_ . __PS_BASE_URI__ . "/index.php?controller=history";

if (Tools::getValue('s') != 'ok' and Tools::getValue('s') != 'ok0') {
    if ($result !== TRUE) {
        $response = sprintf("Error updating order record: %s\n", mysqli_connect_error());
    }

    echo '<div style="text-align: center;">O pagamento não foi aceite, verique o seus dados e pode tentar novamente desde "A minha conta->Encomendas->Details"<br><a href="' . $newURL . '"><button>Ir agora</button></a></div>';
} else {
    echo '<div style="text-align: center;">O seu método de pagamento foi aceite, estamos processando a operação, entre tanto pode ver a sua fatura no seguinte url.<br><a href="' . $newURL . '"><button>Ir agora</button></a></div>';
}
