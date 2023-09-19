{*
 * EasyPay, a module for Prestashop 1.7
 *
 * Form to be displayed in the payment step
 *}
{if isset($smarty.get.method) && $smarty.get.method=='mb'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Para fazer o pagamento deve dirigir-se a um terminal multibanco e usar os seguintes dados' mod='easypay'}:
</P>
<p/>{l s='Entidade' mod='easypay'}: {$smarty.get.entity}</p>
<p>{l s='Referencia' mod='easypay'}: {$smarty.get.reference|number_format:0:" ":" "}</p>
<p>{l s='Montante' mod='easypay'}: {$smarty.get.monto} €</p>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='dds'}
<h1>{l s='Obrigado por sua compra' mod='easypay'}!</h1>
<P>{l s='Vai ser descontado da a sua conta a quantidade' mod='easypay'} {$smarty.get.monto} € {l s='mensual por' mod='easypay'} {$smarty.get.qtt} {l s='meses comenzando por hoje' mod='easypay'}.
</P>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='cc'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Você será direccionado em breve para a Gateway de pagamento Cartão de Crédito da Easypay.' mod='easypay'}</p>
<a href="{$smarty.get.url}"><button class="success">{l s='Ir agora' mod='easypay'}</button></a>

<script>
    
    
    function redirect_url(){
        window.location.replace("{$smarty.get.url}");
    }
    
   
        setTimeout(redirect_url,15000)
    
</script>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='bb'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Você será redirecionado em breve para efetuar o pagamento no easypay BOLETO' mod='easypay'}</p>
<a href="{$smarty.get.url}"><button class="success">{l s='Ir agora' mod='easypay'}</button></a>

<script>
    
    
    function redirect_url(){
        window.location.replace("{$smarty.get.url}");
    }
    
   
        setTimeout(redirect_url,15000)
    
</script>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='mbw'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Deve fazer o pagamento por MBWAY através do seu telemovel.' mod='easypay'}</P>

{/if}


{if isset($smarty.get.method) && $smarty.get.method=='ccf'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Estamos processando o seu pagamento, este processo pode demorar alguns minutos.' mod='easypay'}</P>

{/if}