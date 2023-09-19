{*
 * 2007-2023 Easypay por Trigénius
 *
 * NOTICE OF LICENSE
 *
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 *
 * @author    Trigenius
 * @copyright Direitos autorais (c) 2023 Trigenius
 * @license É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 *}
<div class="row">
  <div class="col-lg-12">
        <form action="{$action|escape:'html':'UTF-8'}" id="payment-form" method="POST" class="dnonee">
            <label>TOKEN</label>
            <input type="text" name="id_payment" placeholder="" value="{$id_payment}" required><br>
        </form>

        <div class="col-lg-12 text-left"><div class="btn button-primary rensr-btn" onclick="apagar_pagamento('{$id_payment}', '{__PS_BASE_URI__}modules/easypay/delete_payment.php', '{l s='Tem a certeza que pretende esquecer estes dados de pagamento?' mod='easypay'}')"><b>{l s='Esquecer estes dados de pagamento' mod='easypay'}</b></div></div>
  </div>  
</div>