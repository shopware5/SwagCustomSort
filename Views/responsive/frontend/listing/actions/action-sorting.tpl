{extends file='parent:frontend/listing/actions/action-sorting.tpl'}

{block name='frontend_listing_actions_sort_values'}
    <option value="6"{if $sSort eq 6} selected="selected"{/if}>{{config name=swagCustomSortName}|trim}</option>
{/block}