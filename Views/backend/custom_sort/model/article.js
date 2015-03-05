//{block name="backend/custom_sort/model/article"}
Ext.define('Shopware.apps.CustomSort.model.Article', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'path', type: 'string' },
        { name: 'position', type: 'int' }
    ],

    proxy:{
        type:'ajax',

        api: {
            read: '{url controller="CustomSort" action="getArticleList"}'
        },

        reader:{
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}