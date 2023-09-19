



<div class="row mb-3" {if isset($frequente) && $frequente==0}style="display:none"{/if}>




  <div class="col-lg-12 text-left">

        <form action="{$action}" id="payment-form-mb" method="POST">
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

