//{namespace name="backend/custom_sort/view/main"}
//{block name="backend/custom_sort/view/main/main"}
Ext.define('Shopware.apps.CustomSort.view.main.Window', {
    
    extend: 'Enlight.app.Window',

    alias: 'widget.sort-main-window',
    
    layout: 'fit',

    title: '{s name=window/title}Custom category sorting{/s}',

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },


    createItems: function() {
        var me = this;

        return [{
            xtype: 'sort-category-tree',
            split: true,
            store: me.treeStore
        }];
    }

});
//{/block}