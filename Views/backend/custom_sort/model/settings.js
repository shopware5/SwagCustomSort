//{block name="backend/custom_sort/model/settings"}
Ext.define('Shopware.apps.CustomSort.model.Settings', {
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
        //{block name="backend/custom_sort/model/settings/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'defaultSort', type: 'int' },
        { name: 'categoryLink', type: 'int' },
        { name: 'baseSort', type: 'int' }
    ],

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
            read: '{url controller=CustomSort action=getCategorySettings}',
            update: '{url controller=CustomSort action=saveCategorySettings}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}