{extends file='parent:frontend/listing/actions/action-sorting.tpl'}

{block name='frontend_listing_actions_sort_values'}
    {action module=widgets controller=CustomSort action=defaultSort hideFilter=$sCategoryContent.hideFilter}
{/block}