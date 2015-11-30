//{namespace name="backend/custom_sort/main"}
//{block name="backend/custom_sort/view/article/view"}
Ext.define('Shopware.apps.CustomSort.view.article.View', {

    /**
     * Define that the article view is an extension of the Ext.form.Panel
     * @string
     */
    extend: 'Ext.form.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.sort-articles-view',

    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'fit',

    disabled: true,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.tbar = me.createActionToolbar();
        me.items = [
            {
                xtype: 'sort-articles-list',
                store: me.articleStore
            }
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the grid action toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    createActionToolbar: function () {
        var me = this;

        //Create checkbox for displaying custom sort by default
        me.defaultSort = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: '{s name=view/default_sort}Show this sort order by default{/s}',
            cls: 'swag-custom-sort-bold-checkbox',
            name: 'defaultSort',
            inputValue: 1,
            uncheckedValue: 0,
            listeners: {
                change: function (oldValue, newValue) {
                    if (me.store.data.items[0].data.defaultSort != newValue) {
                        me.fireEvent('defaultSort');
                    }
                }
            }
        });

        //Create combo tree in which you can link current category to another
        me.categoryTreeCombo = Ext.create('Shopware.form.field.ComboTree', {
            valueField: 'id',
            displayField: 'name',
            treeField: 'categoryId',
            selectedRecord: me.record,
            store: Ext.create('Shopware.store.CategoryTree'),
            forceSelection: true,
            fieldLabel: '{s name=view/category_sync}Sync from category{/s}',
            labelClsExtra: 'swag-custom-sort-radiobtn-topmargin',
            labelWidth: 120,
            emptyText: '{s name=view/category_sync/empty_text}Please select a category{/s}',
            allowBlank: true,
            name: 'categoryLink',
            rootVisible: false,
            enableKeyEvents: true,
            listeners: {
                select: function (field, record) {
                    me.fireEvent('categoryLink', record);
                },
                keyup: function () {
                    if (this.getRawValue().length < 1 && this.getValue() != 0) {
                        me.fireEvent('categoryLink');
                    }
                }
            }
        });

        //Create combo with base sorting
        me.sorting = Ext.create('Ext.form.field.ComboBox', {
            editable: false,
            fieldLabel: '{s name=view/sorting}Base sorting{/s}',
            labelWidth: 85,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'id',
            name: 'baseSort',
            labelClsExtra: 'swag-custom-sort-radiobtn-topmargin',
            store: Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data: [
                    { id: 1, name: '{s name=view/sort/release}Release date{/s}' },
                    { id: 2, name: '{s name=view/sort/popularity}Popularity{/s}' },
                    { id: 3, name: '{s name=view/sort/price_lowest}Minimum price{/s}' },
                    { id: 4, name: '{s name=view/sort/price_highest}Maximum price{/s}' },
                    { id: 5, name: '{s name=view/sort/name_asc}Product name A-Z{/s}' },
                    { id: 6, name: '{s name=view/sort/name_desc}Product name Z-A{/s}' },
                    { id: 7, name: '{s name=view/sort/ranking}Rating{/s}' },
                    { id: 9, name: '{s name=view/sort/instock_asc}Stock ascending{/s}' },
                    { id: 10, name: '{s name=view/sort/instock_desc}Stock descending{/s}' }
                ]
            }),
            listeners: {
                select: function (field, records) {
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