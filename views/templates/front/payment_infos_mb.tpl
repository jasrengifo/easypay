{**
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
<div class="row mb-3" {if isset($frequente) && $frequente==0}style="display:none"{/if}>




  <div class="col-lg-12 text-left">

        <form action="{$action|escape:'html':'UTF-8'}" id="payment-form-mb" method="POST">
            <div id="guardar-pagamento-form-mb" class="col-lg-12">

     			<div class="">
            <label>{l s='Deseja guardar o seu metodo de pagamento?' mod='easypay'}</label>
     				<input type="checkbox" autocomplete="off"  id="cb-guardar-mb" name="guardar-metodo" value="1">
				</div>

  				<div>
  					<label for="vehicle1">{l s='Escreva um nome para identificar o seu método de pagamento' mod='easypay'}</label>
           			<input type="text" name="nome-mp" autocomplete="off" placeholder="{l s='Nome' mod='easypay'}">
           		</div>
           	</div>
        </form>
        <div class="col-lg-12 ep-d-none mt-1" id="no-guardar-pagamento-mb-div">
        	<button id="no-guardar-pagamento-mb">{l s='Cancelar' mod='easypay'}</button>
        </div>
  </div>  
</div>