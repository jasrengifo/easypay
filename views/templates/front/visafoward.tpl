{extends file='page.tpl'}

{block name='page_header_container'}{/block}

{block name='left_column'}

    <div class="page-content card card-block">
        {if $smarty.get.s!='ok' and $smarty.get.s!='ok0'}
            <div style="text-align: center;">
                <div style="text-align: center;">{l s='O pagamento não foi aceite, verique o seus dados e pode tentar novamente desde "A minha conta->Encomendas->Details"' mod='easypay'}<br><a href="{__PS_BASE_URI__}index.php?controller=history"><button>{l s='Ir agora' mod='easypay'}</button></a></div>
                <br>
                <br>
            </div>
        {else}
            <div style="text-align: center;">{l s='O Seu método de pagamento foi aceite, estamos processando a operação, entretanto pode ver a sua fatura no seguinte url.' mod='easypay'}<br><a href="{__PS_BASE_URI__}index.php?controller=history"><button>{l s='Ir agora' mod='easypay'}</button></a></div>
            <br>
        {/if}
    </div>

{/block}


