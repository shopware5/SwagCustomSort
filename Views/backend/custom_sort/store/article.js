//{block name="backend/custom_sort/store/article"}
Ext.define('Shopware.apps.CustomSort.store.Article', {

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
    model: 'Shopware.apps.CustomSort.model.Article',

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
//{/block}