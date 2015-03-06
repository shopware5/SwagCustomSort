//{block name="backend/custom_sort/model/settings"}
Ext.define('Shopware.apps.CustomSort.model.Settings', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'defaultSort', type: 'int' },
        { name: 'categoryLink', type: 'int' }
    ],

    proxy:{
        type:'ajax',

        api: {
            read: '{url controller=CustomSort action=getCategorySettings}',
            update: '{url controller=CustomSort action=saveCategorySettings}'
        },

        reader:{
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}