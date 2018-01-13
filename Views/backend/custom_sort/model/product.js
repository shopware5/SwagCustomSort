// {block name="backend/custom_sort/model/product"}
Ext.define('Shopware.apps.CustomSort.model.Product', {

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
        // {block name="backend/custom_sort/model/product/fields"}{/block}
        { name: 'productId', type: 'int', useNull: true },
        { name: 'positionId', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'position', type: 'int' },
        { name: 'oldPosition', type: 'int', useNull: true },
        { name: 'extension', type: 'string' },
        { name: 'path', type: 'string' },
        {
            name: 'thumbnail',
            type: 'string',
            convert: function (value, record) {
                if (!record.get('path')) {
                    return '{link file="backend/_resources/images/index/no-picture.jpg"}';
                } else {
                    return record.get('path');
                }
            }
        },
        { name: 'pin', type: 'int' }
    ],

    /**
     * defines the field for the unique identifier - id is default.
     *
     * @int
     */
    idProperty: 'productId',

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
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
            read: '{url controller="CustomSort" action="getProductList"}',
            update: '{url controller="CustomSort" action="saveProductList"}',
            create: '{url controller="CustomSort" action="saveProductList"}',
            destroy: '{url controller="CustomSort" action="unpinProduct"}'
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
// {/block}
