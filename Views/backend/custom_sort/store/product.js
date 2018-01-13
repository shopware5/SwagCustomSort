// {block name="backend/custom_sort/store/product"}
Ext.define('Shopware.apps.CustomSort.store.Product', {

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
    model: 'Shopware.apps.CustomSort.model.Product',

    /**
     * Page range of the store
     */
    pageSize: 10,

    listeners: {
        write: function (store) {
            store.load();
        }
    }

});
// {/block}
