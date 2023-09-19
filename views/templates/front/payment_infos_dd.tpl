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

