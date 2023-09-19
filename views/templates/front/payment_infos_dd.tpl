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
        <form action="{$action|escape:'html':'UTF-8'}" id="payment-form" method="POST">
            <label>{l s='Titular da conta' mod='easypay'}:</label>
            <input type="text" name="account_holder" placeholder="{l s='Nome e Apelido' mod='easypay'}" required><br>
            <label>{l s='IBAN' mod='easypay'}:</label>
            <input type="text" name="iban" placeholder="IBAN" required><br>
            <label>{l s='Telemovel' mod='easypay'}:</label>
            <input type="text" name="telephone" placeholder="{l s='Telemovel' mod='easypay'}" required><br>

            <div style="{if isset($frequente) && $frequente==0}display:none{else}display: inline-block{/if}">
                <label>{l s='Deseja Guardar o seus dados para futuros pagamentos?' mod='easypay'}</label>
                <input type="checkbox" autocomplete="off"  id="cb-guardar-mb" name="guardar-metodo" value="1">

                <label for="vehicle1">{l s='Escreva um nome para identificar o seu método de pagamento' mod='easypay'}</label>
                <input type="text" name="nome-mp" autocomplete="off" placeholder="{l s='Nome' mod='easypay'}">
            </div>
        </form>
  </div>  
</div>