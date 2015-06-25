//{block name="backend/custom_sort/store/settings"}
Ext.define('Shopware.apps.CustomSort.store.Settings', {

    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',

    /**
     * Disable auto loading for this store
     * @boolean
     */
    autoLoad: false,

    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.CustomSort.model.Settings'

});
//{/block}