//{block name="backend/custom_sort/view/article/view"}
Ext.define('Shopware.apps.CustomSort.view.article.View', {

    extend: 'Ext.form.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias : 'widget.sort-articles-view',

    layout: 'fit',

    disabled: true,

    initComponent: function() {
        var me = this;

        me.tbar = me.createActionToolbar();
        me.items = [{
            xtype: 'sort-articles-list',
            store: me.articleStore
        }];

        me.callParent(arguments);
    },

    createActionToolbar: function() {
        var me = this;

        me.defaultSort = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: 'Show this sort order by default',
            cls: 'swag-custom-sort-bold-checkbox',
            name: 'defaultSort',
            inputValue: 1,
            uncheckedValue: 0,
            listeners: {
                change: function(oldValue, newValue) {
                    if (me.store.data.items[0].data.defaultSort != newValue) {
                        me.fireEvent('defaultSort');
                    }
                }
            }
        });

        me.sorting = Ext.create('Ext.form.field.ComboBox', {
            editable: false,
            fieldLabel: 'Base sorting',
            labelWidth: 85,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'id',
            labelClsExtra: 'swag-custom-sort-radiobtn-topmargin',
            store: Ext.create('Ext.data.Store', {
                fields: [ 'id', 'name' ],
                data: [
                    { id: 1, name: 'ListingSortRelease' },
                    { id: 2, name: 'ListingSortPopularity' },
                    { id: 3, name: 'ListingSortPriceLowest' },
                    { id: 4, name: 'ListingSortPriceHighest' },
                    { id: 5, name: 'ListingSortNameAsc' },
                    { id: 6, name: 'ListingSortNameDesc' },
                    { id: 7, name: 'ListingSortRanking' }
                ]
            }),
            listeners: {
                select: function(field, records) {
                    var sort = records[0].get('id');
                    me.fireEvent('sortChange', sort);
                }
            }
        });

        return [
            me.defaultSort,
            '->', {
            xtype: 'combotree',
            fieldLabel: 'Sync from category',
            labelClsExtra: 'swag-custom-sort-radiobtn-topmargin',
            allowBlank: true,
            editable: false,
            store: me.treeStore,
            forceSelection: true,
            name: 'categoryLink',
            labelWidth: 120,
            listeners: {
                select: function(field, record) {
                    me.fireEvent('categoryLink', record);
                }
            }
        }, me.sorting ];
    }

});
//{/block}