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
<style>
	.susc-table th{
		border: 1px solid gray;
		padding: 10px 15px;
		text-align: center;
	}
	.susc-table tr:nth-child(odd) {
	    background: #FBFAFF;
	    
	}
	.susc-table td{
		border: 1px solid rgba(0,0,0,0.15);
		padding: 10px 15px;
		text-align: center;
	}
</style>

<script src="{__PS_BASE_URI__}modules/easypay/views/js/mixitup.min.js"></script> 
<script src="{__PS_BASE_URI__}modules/easypay/views/js/mixitup-pagination.js"></script> 

<div class="panel">
	<div class="panel-heading"><i class="icon-gear"></i>Subscrições a Cancelar - EASYPAY</div>
	<div class="panel-body">
		<div class="row" style="margin-bottom: 30px">
			<div class="col-lg-12">
				<form method="post" action="#">
				<label>Pesquisar</label>
				<input type="text" name="pesquisar" style="max-width: 200px;" placeholder="ID encomenda, REF encomenda">
				<input type="submit" value="pesquisar">
				</form>
			</div>
		</div>
		
		<table class="susc-table RensRC">
			<tr>
    				<th>ID. ENCOMENDA</th>
    				<th>DT. INÍCIO</th>
    				<th>DT. FIM</th>
    				<th>FREQ</th>
    				{* <th>Nº. COB. EFETUAR</th> *}
    				<th>Nº. COB. EFETUADAS</th>
    				{* <th>VAL. SUBSCRIÇÃO</th> *}
    				<th>VAL. COBRADO</th>
    				<th>DT. ULT. COBRANÇA</th>
    				<th>ESTADO ATUAL</th>
    				<th>OPÇÕES</th>
			</tr>
			
			    {foreach from=$subs item=sub key=subc}
			    <tr class="mix">
			        {$respuesta = $sub.respuesta|json_decode}
    				<td>{$sub.id_order|escape:'html':'UTF-8'}</td>
    				<td>{$sub.dt_init|escape:'html':'UTF-8'}</td>
    				<td>{$sub.dt_fin|escape:'html':'UTF-8'}</td>
    				<td>{$sub.freq|escape:'html':'UTF-8'}</td>
    				<td>{$sub.n_cob_eftd|escape:'html':'UTF-8'}</td>
    				<td>{Tools::displayPrice($sub.val_cobrado|round:2|escape:'number_float')}</td>
    				<td>{$sub.dt_ult_cob|escape:'html':'UTF-8'}</td>
    				<td>{$sub.estado_act|escape:'html':'UTF-8'}</td>
    				<td>{if $sub.estado_act!="INACTIVE"}<a href="{_PS_BASE_URL_}{__PS_BASE_URI__}modules/easypay/cancelSub.php?id_sub={$respuesta->id}"><button class="btn btn-danger">Cancelar Subscição</button></a>{/if}</td>
    			</tr>
				{/foreach}
			
		</table>

	</div>

	<div class="text-center" style="margin-top: 15px">
		<div class="mixitup-page-list"></div>
		<div class="mixitup-page-stats"></div>
</div>
</div>

{literal}
<script>
  var containerEl = document.querySelector('.RensRC');
  var mixer = mixitup(containerEl, {
      pagination: {
        limit: 10 // impose a limit of 8 targets per page
      }
      
  });
</script>
{/literal}