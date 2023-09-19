

({l s='Subscrição' mod='easypay'}


{foreach from=$product.features item=feature}
    {if $feature.id_feature==Configuration::get('EASYPAY_EXP_TIME')} - {$feature.value}{/if}
{/foreach}


)

