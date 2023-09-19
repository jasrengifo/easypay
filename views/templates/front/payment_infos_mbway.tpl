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
<div class="row" style="margin-bottom: 15px">
  <div class="col-lg-12" id="caja-entera-rensr">
        <form action="{$action|escape:'html':'UTF-8'}" id="payment-form" method="POST">
            <label>{l s='Telemovel' mod='easypay'}:</label>
            <input type="text" name="phonenumber" placeholder="{l s='Numero do telemovel' mod='easypay'}" id="input-rensr-mbw2" required>

            <div class="mbway-mpagamento" id="nome-iden-p-mbway" {if isset($frequente) && $frequente==0}style="display:none"{/if}>
                <label>{l s='Deseja guardar o seu metodo de pagamento?' mod='easypay'}</label>
                <input class="" type="checkbox" autocomplete="off"  id="cb-guardar-mbway" name="guardar-metodo" value="1">
                <label for="vehicle1">{l s='Escreve um nome para identificar o seu metodo de pagamento' mod='easypay'}</label>
                <input  type="text" name="nome-mp" autocomplete="off" placeholder="{l s='Nome' mod='easypay'}" id="input-rensr-mbw">
            </div>
        </form>

        <!-- <div class="btn button-primary rensr-btn" id="cancelar-mbway-s">Cancelar</div> -->
  </div>  
</div>
