//{block name="backend/custom_sort/model/Tree"}
Ext.define('Shopware.apps.CustomSort.model.Tree', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

    /**
     * Fields array which contains the model fields
     * @array
     */
    fields: [
		//{block name="backend/custom_sort/model/tree/fields"}{/block}
        'id',
        'name',
        {
            name: 'allowDrag',
            type: 'boolean',
            mapping: 'leaf'
        }
    ]

});
//{/block}

