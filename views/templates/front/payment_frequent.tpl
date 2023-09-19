<div class="row">
  <div class="col-lg-12">
        <form action="{$action}" id="payment-form" method="POST" class="dnonee">
            <label>TOKEN</label>
            <input type="text" name="id_payment" placeholder="" value="{$id_payment}" required><br>
        </form>

        <div class="col-lg-12 text-left"><div class="btn button-primary rensr-btn" onclick="apagar_pagamento('{$id_payment}', '{__PS_BASE_URI__}modules/easypay/delete_payment.php', '{l s='Tem a certeza que pretende esquecer estes dados de pagamento?' mod='easypay'}')"><b>{l s='Esquecer estes dados de pagamento' mod='easypay'}</b></div></div>
  </div>  
</div>
