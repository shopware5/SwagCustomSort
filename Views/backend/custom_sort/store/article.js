//{block name="backend/custom_sort/store/article"}
Ext.define('Shopware.apps.CustomSort.store.Article', {

    extend: 'Ext.data.Store',

    autoLoad: false,

    model : 'Shopware.apps.CustomSort.model.Article',

    pageSize: 10,

    listeners: {
        write: function(store) {
            store.load();
        }
    }

});
//{/block}