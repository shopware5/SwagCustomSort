{extends file='parent:frontend/listing/actions/action-sorting.tpl'}

{block name='frontend_listing_actions_sort_values'}
    {$smarty.block.parent}
    {include file='frontend/swag_custom_sort/default_sort.tpl'}
{/block}
