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




{if count($auth)>0}
<div class="panel">
	<div class="panel-heading"><i class="icon-gear"></i>{l s='Pagamentos a Capturar - EASYPAY' mod='easypay'}</div>


	<div class="panel-body">
		<div class="row" style="margin-bottom: 30px">
			<div class="col-lg-12">
				<form method="post" action="#">
				<label>{l s='Pesquisar' mod='easypay'}</label>
				<input type="text" name="pesquisar" style="max-width: 200px;" placeholder="Ref. Enc, ID Customer, Data(Y-m-d)">
				<input type="submit" value="pesquisar">
				</form>
			</div>
		</div>
	<div class="text-center">	
		<table class="susc-table RensRC" style="display: inline-block">
			<tr>
    				<th>{l s='nome Pagamento' mod='easypay'}</th>
    				<th>{l s='Id customer' mod='easypay'}</th>
    				<th>{l s='Encomenda' mod='easypay'}</th>
    				<th>{l s='Valor Encomenda' mod='easypay'}</th>
    				<th>{l s='ID Pagamento' mod='easypay'}</th>
    				<th>{l s='Tipo Pagamento' mod='easypay'}</th>
    				<th>{l s='Data' mod='easypay'}</th>
    				<th>{l s='OPÇÕES' mod='easypay'}</th>
			</tr>

			
			    {foreach from=$auth item=sub key=subc}
			    <tr class="mix">
			        {$respuesta = $sub.respuesta|json_decode}
    				<td>{$sub.nombre_de_pago|escape:'html':'UTF-8'}</td>
    				<td>{$sub.id_user|escape:'html':'UTF-8'}</td>
    				<td>{$sub.id_ord|escape:'html':'UTF-8'}</td>
    				<td>{$sub.valor|escape:'html':'UTF-8'}</td>
    				<td>{$sub.id_pagamento|escape:'html':'UTF-8'}</td>

    				<td>{$sub.tipo_pagamento|escape:'html':'UTF-8'}</td>

    				<td>{$sub.first_date|escape:'html':'UTF-8'}</td>
    				
    				
    				{if $sub.ativado == 1}
    					<td><button class="btn btn-success" style="margin-bottom: 5px" onClick="capturar_pagamento('{$sub.id_pagamento}', {$sub.valor}, {$sub.cartt}, 'autorizar');">{l s='Capturar Pagamento' mod='easypay'}</button><br><button class="btn btn-danger" onClick="capturar_pagamento('{$sub.id_pagamento}', {$sub.valor}, {$sub.cartt}, 'cancelar');">{l s='Cancelar Pagamento' mod='easypay'}</button></td>
    				{else}
    					<td><button class="btn btn-danger" onClick="capturar_pagamento('{$sub.id_pagamento}', {$sub.valor}, {$sub.cartt}, 'cancelar');">{l s='Cancelar Pagamento' mod='easypay'}</button></td>
    				{/if}

    			</tr>
				{/foreach}




			
		</table>
	</div>
		
<div class="text-center" style="margin-top: 15px">
		<div class="mixitup-page-list"></div>
		<div class="mixitup-page-stats"></div>
</div>


		
		


	</div>
</div>
{else}
<div class="panel">
	<div class="panel-heading"><i class="icon-gear"></i>{l s='Pagamentos a Aprovar - EASYPAY' mod='easypay'}</div>
	<div class="panel-body">

		<div class="row" style="margin-bottom: 30px">
			<div class="col-lg-12">
				<form method="post" action="#">
				<label>{l s='Pesquisar' mod='easypay'}</label>
				<input type="text" name="pesquisar" style="max-width: 200px;" placeholder="Ref. Enc, ID Customer, Data(Y-m-d)">
				<input type="submit" value="{l s='Pesquisar' mod='easypay'}">
				</form>
			</div>
		</div>
		
		{l s='não se encontrou pagamentos sem aprovar.' mod='easypay'}
	

	</div>
</div>

{/if}

<script>

	function capturar_pagamento(idpagamento, valor, id_cart, tipo){
		$confirm = confirm("Tem certeza de "+tipo+" este pagamento?");

		if($confirm==true){
			$.ajax({
		      url: '{__PS_BASE_URI__}modules/easypay/autorizar.php?test=asddas',
		      {literal}data:{'id_pagamento': idpagamento, 'valor': valor, 'id_cart': id_cart, 'tipo': tipo}{/literal},
		      dataType: 'json',
		      method:'POST',
			}).done(function(data){
			    console.log(data);
		    	if(data.status=='SUCCESS'){
		    		alert(data.msg);
		    		location.reload();
		    	}else{
		    		alert(data.msg);
		    		console.log(data);
		    	}
		    	

		  	});
		}
	}

	function cancelar_pagamento(idpagamento){
		$confirm = confirm("Tem certeza de cancelar este pagamento?");

		if($confirm==true){
			$.ajax({
		      url: '{__PS_BASE_URI__}modules/easypay/cancelar.php',
		      data:{
		          token: new Date().getTime(),
		          payment_id: idpagamento,
		      }, 
		      method:'POST',
		      dataType: 'json',
			}).done(function(data){
		    	if(data.status=='SUCCESS'){
		    		alert(data.msg);
		    		location.reload();
		    	}else{
		    	    console.log(data);
		    		alert(data.msg);
		    	}


		  	});
		}
	}
</script>


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