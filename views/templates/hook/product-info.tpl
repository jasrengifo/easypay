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

{if $have_products_in_cart==1}
	{if $actual==0}
		{if $have_subs==1}
			<div style="padding: 10px 20px; background-color: rgba(255, 0, 0, .4); border: 1px solid rgba(255, 0, 0, .2); margin: 15px 0px">{l s='Não é possível comprar este produto. Não podem existir produtos de' mod='easypay'} <b>{l s='Subscrição' mod='easypay'}</b> {l s='juntos com produtos' mod='easypay'} <b>{l s='sem Subscrição' mod='easypay'}</b> {l s='no mesmo carrinho' mod='easypay'}.</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>$("button[data-button-action=add-to-cart]").attr("disabled", "true")</script>
			<script>
				$("button[data-button-action=add-to-cart]").on('datachange', function(){
					$("button[data-button-action=add-to-cart]").attr("disabled", "true");
				});
			</script>
		{/if}
	{else}
		{if $have_subs==0}
			<div style="padding: 10px 20px; background-color: rgba(255, 0, 0, .4); border: 1px solid rgba(255, 0, 0, .2); margin: 15px 0px">{l s='Não é possível comprar este produto. Não podem existir produtos de' mod='easypay'} <b>{l s='Subscrição' mod='easypay'}</b> {l s='juntos com produtos' mod='easypay'} <b>{l s='sem Subscrição' mod='easypay'}</b> {l s='no mesmo carrinho' mod='easypay'}.</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>$("button[data-button-action=add-to-cart]").attr("disabled", "true")</script>
		{else}
			<div style="padding: 10px 20px; background-color: rgba(255, 0, 0, .4); border: 1px solid rgba(255, 0, 0, .2); margin: 15px 0px">{l s='Já tens 1 produto de subscrição no carrinho, só podes comprar um por encomenda.' mod='easypay'}</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>$("button[data-button-action=add-to-cart]").attr("disabled", "true")</script>
			<script>
				$("button[data-button-action=add-to-cart]").on('datachange', function(){
					$("button[data-button-action=add-to-cart]").attr("disabled", "true");
				});
			</script>
		{/if}
	{/if}
{/if}