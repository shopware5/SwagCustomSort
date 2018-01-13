// {namespace name="backend/custom_sort/main"}
// {block name="backend/custom_sort/controller/main"}
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
     * References to specific elements in the module
     * @array
     */
    refs: [
        { ref: 'productView', selector: 'sort-products-view' },
        { ref: 'productList', selector: 'sort-products-list' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.categoryId = null;
        me.subApplication.treeStore = me.subApplication.getStore('Tree');
        me.subApplication.treeStore.load();

        me.subApplication.productStore = me.subApplication.getStore('Product');

        me.subApplication.categorySettings = me.subApplication.getStore('Settings');

        me.control({
            'sort-category-tree': {
                itemclick: me.onCategorySelect
            },
            'sort-products-view': {
                defaultSort: me.onSaveSettings,
                sortChange: me.onSortChange,
                categoryLink: me.onSaveSettings
            },
            'sort-products-list': {
                pageChange: me.onPageChange,
                moveToStart: me.onMoveToStart,
                moveToEnd: me.onMoveToEnd,
                moveToPrevPage: me.onMoveToPrevPage,
                moveToNextPage: me.onMoveToNextPage,
                productMove: me.onProductMove,
                unpin: me.onUnpin,
                remove: me.onRemove
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.subApplication.treeStore,
            productStore: me.subApplication.productStore,
            categorySettings: me.subApplication.categorySettings
        }).show();

        me.callParent(arguments);
    },

    /**
     * Event listener function of the product list panel.
     * Fired when the user uses paging navigation.
     */
    onPageChange: function () {
        var me = this,
            list = me.getProductList();

        list.setLoading(true);
    },

    /**
     * Event listener function of the category tree panel.
     * Fired when the user select category from category tree.
     *
     * @param { object } view - Ext.view.View
     * @param { Ext.data.Model } record The selected record
     */
    onCategorySelect: function (view, record) {
        var me = this,
            grid = me.getProductView(),
            list = me.getProductList();

        // Hide grid buttons on category select
        grid.setDisabled(true);
        list.setDisabled(true);
        list.setLoading(true);

        me.categoryId = record.get('id');

        me.subApplication.categorySettings.getProxy().extraParams = { categoryId: me.categoryId };
        me.subApplication.categorySettings.load({
            callback: function (records, operation, success) {
                if (success) {
                    var record = records[0];
                    var linkedCategoryId = record.get('categoryLink');
                    var baseSort = record.get('baseSort');

                    grid.loadRecord(record);
                    grid.setDisabled(false);
                    grid.categoryTreeCombo.setDisabled(false);

                    me.prepareTreeCombo(linkedCategoryId);

                    if (linkedCategoryId > 0) {
                        grid.sorting.setDisabled(true);
                    } else {
                        grid.categoryTreeCombo.setRawValue();
                        grid.categoryTreeCombo.getPicker().collapseAll();
                        grid.sorting.setDisabled(false);
                        list.setDisabled(false);
                    }

                    me.subApplication.productStore.getProxy().extraParams = {
                        categoryId: me.categoryId,
                        sortBy: baseSort
                    };
                    me.subApplication.productStore.filters.clear();
                    me.subApplication.productStore.currentPage = 1;
                    me.subApplication.productStore.load();

                    grid.sorting.setValue(baseSort);
                }
            }
        });

        me.subApplication.productStore.on('load', function () {
            list.setLoading(false);
        });
    },

    /**
     * Prepare combo tree if selected category is linked to another category.
     *
     * @param { integer } linkedCategoryId
     * @returns { boolean }
     */
    prepareTreeCombo: function (linkedCategoryId) {
        var me = this,
            comboBox = me.getProductView().categoryTreeCombo,
            treePanel = comboBox.getPicker(),
            treeStore = treePanel.getStore();

        // clear tree selection if it is not linked
        if (!linkedCategoryId) {
            treePanel.collapseAll();
            comboBox.setRawValue();
        }

        // helper function for selecting tree node
        var selectNode = function () {
            var node = treeStore.getNodeById(linkedCategoryId);
            if (node) {
                comboBox.setRawValue(node.get('name'));
                treePanel.collapseAll();
                treePanel.selectPath(node.getPath());
            }
        };

        // load whole category tree
        treeStore.on('load', function () {
            treePanel.expandAll();
            treePanel.collapseAll();

            // select tree node on first load
            selectNode();
        });

        // select tree node on change
        selectNode();
        return true;
    },

    /**
     * Event listener function of the sort combobox.
     * Fired when the user change sorting of products in product view panel.
     *
     * @param { Ext.data.Model } record The selected record
     */
    onSortChange: function (record) {
        var me = this,
            list = me.getProductList();

        list.setLoading(true);

        me.onSaveSettings();

        me.subApplication.productStore.getProxy().extraParams = {
            categoryId: me.categoryId,
            sortBy: record
        };
        me.subApplication.productStore.load({
            callback: function () {
                list.setLoading(false);
            }
        });
    },

    /**
     * Event listener function of the product view panel.
     * Fired when the user change default display or linked category
     */
    onSaveSettings: function () {
        var me = this,
            grid = me.getProductView(),
            list = me.getProductList(),
            form = grid.getForm(),
            record = form.getRecord(),
            values = form.getValues();

        if (values.categoryLink > 0) {
            grid.sorting.setDisabled(true);
            list.setDisabled(true);
        } else {
            grid.sorting.setDisabled(false);
            list.setDisabled(false);
        }

        record.set(values);

        record.save({
            success: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/success/title}Success{/s}', '{s name=main/success/message}Successfully applied changes{/s}');
            },
            failure: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/error/title}Error{/s}', '{s name=main/error/message}Changes were not saved{/s}');
            }
        });
    },

    /**
     *
     * @param { Ext.data.Store } productStore
     */
    onMoveToStart: function (productStore) {
        if (!(productStore instanceof Ext.data.Store)) {
            return false;
        }

        var me = this,
            list = me.getProductList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null;

        list.setLoading(true);

        selectedRecords.forEach(function (record, index) {
            if (!(record instanceof Ext.data.Model)) {
                return false;
            }

            oldPosition = productStore.indexOf(record) + ((productStore.currentPage - 1) * productStore.pageSize);
            record.set('position', index);
            record.set('oldPosition', oldPosition);
            record.set('pin', 1);
        });

        me.onSaveProducts(productStore);

        return true;
    },

    /**
     * Event listener function of the product list.
     * Fired when the user click on "move to end" fast move icon.
     *
     * @param { Ext.data.Store } productStore
     */
    onMoveToEnd: function (productStore) {
        if (!(productStore instanceof Ext.data.Store)) {
            return false;
        }

        var me = this,
            list = me.getProductList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            total = productStore.getTotalCount() - 1;

        list.setLoading(true);

        selectedRecords.forEach(function (record, index) {
            if (!(record instanceof Ext.data.Model)) {
                return false;
            }

            oldPosition = productStore.indexOf(record) + ((productStore.currentPage - 1) * productStore.pageSize);
            record.set('position', total - index);
            record.set('oldPosition', oldPosition);
            record.set('pin', 1);
        });

        me.onSaveProducts(productStore);

        return true;
    },

    /**
     * Event listener function of the product list.
     * Fired when the user click on "move to prev page" fast move icon.
     *
     * @param { Ext.data.Store } productStore
     */
    onMoveToPrevPage: function (productStore) {
        if (!(productStore instanceof Ext.data.Store)) {
            return false;
        }

        var me = this,
            list = me.getProductList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null,
            count = selectedRecords.length;

        list.setLoading(true);

        selectedRecords.forEach(function (record) {
            if (!(record instanceof Ext.data.Model)) {
                return false;
            }

            if (productStore.allProductsPageSize && productStore.indexOf(record) < productStore.allProductsPageSize) {
                return true;
            }

            if (productStore.allProductsPageSize) { // all products mode
                oldPosition = productStore.indexOf(record);
                position = oldPosition - productStore.allProductsPageSize;
            } else {
                oldPosition = productStore.indexOf(record) + ((productStore.currentPage - 1) * productStore.pageSize);
                position = ((productStore.currentPage - 1) * productStore.pageSize) - count;
            }

            record.set('oldPosition', oldPosition);
            record.set('position', position);
            record.set('pin', 1);
            count--;
        });

        me.onSaveProducts(productStore);

        return true;
    },

    /**
     * Event listener function of the product list.
     * Fired when the user click on "move to next page" fast move icon.
     *
     * @param { Ext.data.Store } productStore
     */
    onMoveToNextPage: function (productStore) {
        if (!(productStore instanceof Ext.data.Store)) {
            return false;
        }

        var me = this,
            list = me.getProductList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null;

        list.setLoading(true);

        selectedRecords.forEach(function (record, index) {
            if (!(record instanceof Ext.data.Model)) {
                return false;
            }

            if (productStore.allProductsPageSize && productStore.indexOf(record) >= (productStore.totalCount - productStore.allProductsPageSize)) {
                return true;
            }

            if (productStore.allProductsPageSize) { // all products mode
                oldPosition = productStore.indexOf(record);
                position = oldPosition + productStore.allProductsPageSize;
            } else {
                oldPosition = productStore.indexOf(record) + ((productStore.currentPage - 1) * productStore.pageSize);
                position = (productStore.currentPage * productStore.pageSize) + index;
            }

            record.set('oldPosition', oldPosition);
            record.set('position', position);
            record.set('pin', 1);
        });

        me.onSaveProducts(productStore);

        return true;
    },

    /**
     * Event listener function of the product view list.
     * Fired when the user move selected products.
     *
     * @param { Ext.data.Store } productStore
     * @param { Array } productModel Array with selected product in product view list
     * @param { Shopware.apps.Article.model.Article} targetRecord The target record, on which the dragged record dropped
     */
    onProductMove: function (productStore, productModel, targetRecord) {
        var me = this,
            list = me.getProductList(),
            startPosition = (productStore.currentPage - 1) * productStore.pageSize;

        if (!(productStore instanceof Ext.data.Store) ||
            !(targetRecord instanceof Ext.data.Model)) {
            return false;
        }

        list.setLoading(true);
        var count = productModel.length;
        if (count > 0) {
            var positionIndex = productStore.indexOf(targetRecord) + startPosition;

            var forward = [], backward = [], temp = 0;
            Ext.each(productModel, function (record) {
                var oldPosition = productStore.indexOf(record) + startPosition;
                if (oldPosition < positionIndex) {
                    forward.push(record);
                }

                if (oldPosition > positionIndex) {
                    backward.push(record);
                }
            });

            Ext.each(productModel, function (record, index) {
                if (!(record instanceof Ext.data.Model)) {
                    return;
                }

                var oldPosition, position;

                oldPosition = productStore.indexOf(record) + startPosition;
                if (oldPosition < positionIndex) {
                    position = positionIndex - forward.length + index;
                }

                if (oldPosition >= positionIndex) {
                    position = positionIndex + temp;
                    temp++;
                }

                record.set('position', position);
                record.set('oldPosition', oldPosition);
                record.set('pin', 1);
            });
        }

        me.onSaveProducts(productStore);
    },

    /**
     * Event listener function of the product view list.
     * Fired when the user move product in view list or use fast move buttons.
     *
     * @param { Ext.data.Store } productStore
     */
    onSaveProducts: function (productStore) {
        productStore.update();
    },

    /**
     * Event listener function of the product view list.
     * Fired when the user click on unpin icon on product.
     *
     * @param { Ext.data.Store } productStore
     * @param { Ext.data.Model } record The selected record
     */
    onUnpin: function (productStore, record) {
        var me = this,
            list = me.getProductList();

        if (!(productStore instanceof Ext.data.Store) || !(record instanceof Ext.data.Model)) {
            return false;
        }

        list.setLoading(true);

        var store = productStore;
        record.set('pin', 0);
        productStore.remove(record);
        productStore.sync({
            success: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/success/title}Success{/s}', '{s name=main/success/message}Successfully applied changes{/s}');
            },
            failure: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/error/title}Error{/s}', '{s name=main/error/message}Changes were not saved{/s}');
                store.load();
            }
        });

        return true;
    },

    /**
     * Event listener function of the product view list.
     * Fired when the user click on remove icon on product.
     *
     * @param { Ext.data.Store } productStore
     * @param { Ext.data.Model } record The selected record
     */
    onRemove: function (productStore, record) {
        var me = this,
            list = me.getProductList();

        if (!(productStore instanceof Ext.data.Store) || !(record instanceof Ext.data.Model)) {
            return false;
        }

        list.setLoading(true);

        var store = productStore;

        Ext.Ajax.request({
            url: '{url controller="CustomSort" action="removeProduct"}',
            method: 'POST',
            params: {
                productId: record.get('productId'),
                categoryId: me.categoryId
            },
            success: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/success/title}Success{/s}', '{s name=main/success/remove/message}Product successfully removed{/s}');
                store.load();
            },
            failure: function () {
                Shopware.Notification.createGrowlMessage('{s name=main/error/title}Error{/s}', '{s name=main/error/remove/message}Product was not removed{/s}');
            }
        });

        return true;
    }
});
// {/block}
