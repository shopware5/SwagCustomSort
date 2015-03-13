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

        me.categoryTreeCombo = Ext.create('Shopware.form.field.ComboTree', {
            valueField: 'id',
            displayField: 'name',
            treeField: 'categoryId',
            selectedRecord : me.record,
            store: Ext.create('Shopware.store.CategoryTree'),
            forceSelection: true,
            fieldLabel: 'Sync from category',
            labelClsExtra: 'swag-custom-sort-radiobtn-topmargin',
            labelWidth: 120,
            emptyText: 'Please select a category',
            allowBlank: true,
            name: 'categoryLink',
            rootVisible: false,
            enableKeyEvents: true,
            listeners: {
                select: function(field, record) {
                    me.fireEvent('categoryLink', record);
                },
                keydown: function () {
                    if (this.getRawValue().length <= 1) {
                        me.fireEvent('categoryLink');
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
            value: 5,
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
            '->',
            me.categoryTreeCombo,
            me.sorting
        ];
    }

});
//{/block}