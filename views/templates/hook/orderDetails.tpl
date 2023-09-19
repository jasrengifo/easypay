<style>
   .table-pagamentos{
       width: 80%;
       text-align: center!important;
       border: 1px solid #cccccc;
   } 
   .table-pagamentos tr:nth-child(odd){
       background-color: rgb(240, 240, 240);
   }
   .table-pagamentos th{
       padding: 5px 0px;
   }
</style>
{if $metodo == 'cc'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>{l s='Dados de pagamento' mod='easypay'}</h2>
            {if $status != 'ok'}{l s='Caso não tenha concluído a operação com sucesso' mod='easypay'} <a href="{$url_l}" class="btn btn-primary pointer">{l s='clique aqui!' mod='easypay'}</a>
            {else}
                {l s='O Seu pagamento foi feito com successo.' mod='easypay'}
            {/if}
        </div>
    </div>
</div>
{/if}
{if $metodo == 'mb'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>{l s='Dados de pagamento' mod='easypay'}</h2>
            {if $status != 'ok'}{l s='Se ainda não fez o pagamento, por favor dirija-se a um terminal multibanco e utilize os seguintes dados:' mod='easypay'}
                <ul>
                    <li><b>{l s='Entidade' mod='easypay'}:</b> {$entidade}</li>
                    <li><b>{l s='Referencia' mod='easypay'}:</b> {$referencia}</li>
                    <li><b>{l s='Montante' mod='easypay'}:</b> {Tools::displayPrice($montante)}</li>
                </ul>
            
            {else}
                {l s='O Seu pagamento foi feito com successo.' mod='easypay'}
            {/if}
        </div>
    </div>
</div>
{/if}
{if $metodo == 'bb'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>{l s='Dados de pagamento' mod='easypay'}</h2>
            {if $status != 'ok'}{l s='Caso não tenha concluído a operação com sucesso' mod='easypay'} <a href="{$url_l}" class="btn btn-primary pointer">{l s='clique aqui!' mod='easypay'}</a>
            {else}
                {l s='O Seu pagamento foi feito com successo.' mod='easypay'}
            {/if}
        </div>
    </div>
</div>
{/if}



{if $metodo == 'dd'}
<div class="row">
    <div class="col-lg-12">
        <div class="col-lg-12 box">
            <h2>{l s='Dados de pagamento' mod='easypay'} - <a href="{$linki}modules/easypay/cancelSub.php?id_sub={$pagamentos->id}&url_v=http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}&id_order={$smarty.get.id_order}"><button>{l s='Cancelar Subscrição' mod='easypay'}</button></a></h2>
            {*<table class="table-pagamentos">
                <tr>
                    <th style="text-align: center">Nº FATURA</th>
                    <th style="text-align: center">DATA</th>
                    <th style="text-align: center">TOTAL</th>
                </tr>
                {foreach from=$pagamentos->transactions item=pagamento}
                    <tr>
                        <td>{$pagamento->document_number}</td>
                        <td>{$pagamento->date}</td>
                        <td>{$pagamento->values->paid} {$pagamentos->currency}</td>
                    </tr>
                {/foreach}
            </table>*}
            
        </div>
    </div>
</div>
{/if}
