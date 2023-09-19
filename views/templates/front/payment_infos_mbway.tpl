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
