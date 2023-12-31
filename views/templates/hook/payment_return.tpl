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

{if isset($smarty.get.method) && $smarty.get.method=='mb'}
<h1>{l s='Obrigado por sua compra!' mod='easypay'}</h1>
<P>{l s='Para fazer o pagamento deve dirigir-se a um terminal multibanco e usar os seguintes dados' mod='easypay'}:
</P>
<p/>{l s='Entidade' mod='easypay'}: {$smarty.get.entity|escape:'html'}</p>
<p>{l s='Referencia' mod='easypay'}: {$smarty.get.reference|number_format:0:" ":" "|escape:'number_float'}</p>
<p>{l s='Montante' mod='easypay'}: {$smarty.get.monto|escape:'number_float'} €</p>
{/if}

{if isset($smarty.get.method) && $smarty.get.method=='dds'}
<h1>{l s='Obrigado por sua compra' mod='easypay'}!</h1>
<P>{l s='Vai ser descontado da a sua conta a quantidade' mod='easypay'} {$smarty.get.monto|escape:'number_float'} € {l s='mensual por' mod='easypay'} {$smarty.get.qtt|escape:'number_int'} {l s='meses comenzando por hoje' mod='easypay'}.
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