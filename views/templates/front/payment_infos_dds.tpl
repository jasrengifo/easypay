<div class="row">
  <div class="col-lg-12">
        <form action="{$action}" id="payment-form" method="POST">
            <label>{l s='Titular da conta' mod='easypay'}:</label>
            <input type="text" name="account_holder" placeholder="{l s='Nome e Apelido' mod='easypay'}" required><br>
            <label>{l s='IBAN' mod='easypay'}:</label>
            <input type="text" name="iban" placeholder="IBAN" required><br>
            <label>{l s='Telemovel' mod='easypay'}:</label>
            <input type="text" name="telephone" placeholder="{l s='Telemovel' mod='easypay'}" required><br>
        </form>
  </div>  
</div>