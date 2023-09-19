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
                    <li><b>{l s='Entidade' mod='easypay'}:</b> {$entidade|escape:'html'}</li>
                    <li><b>{l s='Referencia' mod='easypay'}:</b> {$referencia|escape:'html'}</li>
                    <li><b>{l s='Montante' mod='easypay'}:</b> {Tools::displayPrice($montante|escape:'html')}</li>
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
                        <td>{$pagamento->document_number|escape:'html'}</td>
                        <td>{$pagamento->date|escape:'html'}</td>
                        <td>{$pagamento->values->paid|escape:'html'} {$pagamentos->currency|escape:'html'}</td>
                    </tr>
                {/foreach}
            </table>*}
            
        </div>
    </div>
</div>
{/if}
