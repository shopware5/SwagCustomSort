{extends file='parent:frontend/listing/actions/action-sorting.tpl'}

{block name='frontend_listing_actions_sort_values'}
    {if $showCustomSort}
        <option value="8"{if $sSort eq 8} selected="selected"{/if}>{{config name=swagCustomSortName}|trim}</option>
    {/if}
{/block}