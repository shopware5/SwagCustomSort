//{namespace name="backend/custom_sort/view/main"}
//{block name="backend/custom_sort/controller/main"}
Ext.define('Shopware.apps.CustomSort.controller.Main', {
    
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',
    
    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Sets up the ui component
     * @return void
     */
    init: function() {
        var me = this;

        me.subApplication.treeStore =  me.subApplication.getStore('Tree');
        me.subApplication.treeStore.load();

        me.mainWindow = me.getView('main.Window').create({
            treeStore:me.subApplication.treeStore
        }).show();

        me.callParent(arguments);
    }

});
//{/block}