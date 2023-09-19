{*
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


