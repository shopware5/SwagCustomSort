//{block name="backend/custom_sort/model/article"}
Ext.define('Shopware.apps.CustomSort.model.Article', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/custom_sort/model/article/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'positionId', type: 'int', useNull: true },
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
        },
        { name: 'pin', type: 'int' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller="CustomSort" action="getArticleList"}',
            update: '{url controller="CustomSort" action="saveArticleList"}',
            create: '{url controller="CustomSort" action="saveArticleList"}',
            destroy: '{url controller="CustomSort" action="unpinArticle"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        },

        /**
         * Configure the data writer
         * @object
         */
        writer: {
            type: 'json',
            root: 'products'
        }
    }
});
//{/block}