//{block name="backend/custom_sort/model/article"}
Ext.define('Shopware.apps.CustomSort.model.Article', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'position', type: 'int' },
        { name: 'oldPosition', type: 'int', useNull: true },
        { name: 'extension', type: 'string' },
        { name: 'path', type: 'string' },
        {
            name: 'thumbnail',
            type: 'string',
            convert: function(value, record) {
                if (record.get('path').indexOf('media/image') === -1) {
                    return 'media/image/thumbnail/' + record.get('path') + '_140x140.' + record.get('extension');
                } else {
                    var name =  record.get('path').replace('media/image/', '');
                    name = name.replace('.' + record.get('extension'), '');
                    return 'media/image/thumbnail/' + name + '_140x140.' + record.get('extension');
                }
            }
        }
    ],

    proxy:{
        type:'ajax',

        api: {
            read: '{url controller="CustomSort" action="getArticleList"}',
            update: '{url controller="CustomSort" action="saveArticleList"}',
            create: '{url controller="CustomSort" action="saveArticleList"}'
        },

        reader:{
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}