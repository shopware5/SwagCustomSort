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

    refs: [
        { ref: 'articleView', selector: 'sort-articles-view' }
    ],

    /**
     * Sets up the ui component
     * @return void
     */
    init: function() {
        var me = this;

        me.subApplication.treeStore =  me.subApplication.getStore('Tree');
        me.subApplication.treeStore.load();

        me.subApplication.articleStore =  me.subApplication.getStore('Article');

        me.control({
            'sort-category-tree': {
                'itemclick': me.onItemClick
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.subApplication.treeStore,
            articleStore: me.subApplication.articleStore
        }).show();

        me.callParent(arguments);
    },

    onItemClick: function(view, record) {
        var me = this,
            grid = me.getArticleView();

        me.subApplication.articleStore.getProxy().extraParams = { categoryId: record.get("id") };
        me.subApplication.articleStore.load();

        grid.setDisabled(false);
    }

});
//{/block}