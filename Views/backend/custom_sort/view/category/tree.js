// {namespace name="backend/custom_sort/view/main"}
// {block name="backend/custom_sort/view/category/tree"}
Ext.define('Shopware.apps.CustomSort.view.category.Tree', {

    /**
     * Parent Element Ext.tree.Panel
     * @string
     */
    extend: 'Ext.tree.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.sort-category-tree',

    collapsible: true,

    /**
     * False to hide the root node.
     * @boolean
     */
    rootVisible: false,

    /**
     * The width of this component in pixels.
     * @integer
     */
    width: 250,

    title: '{s name=view/tree/title}Categories{/s}',

    /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent: function () {
        var me = this;

        me.columns = me.createColumns();

        me.callParent(arguments);
    },

    /**
     * Creates the column model for the TreePanel
     *
     * @return [array] columns - generated columns
     */
    createColumns: function () {
        return [{
            xtype: 'treecolumn',
            sortable: false,
            flex: 1,
            dataIndex: 'text'
        }];
    }
});
// {/block}
