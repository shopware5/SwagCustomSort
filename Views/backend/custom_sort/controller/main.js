//{namespace name="backend/custom_sort/main"}
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
     * References to specific elements in the module
     * @array
     */
    refs: [
        { ref: 'articleView', selector: 'sort-articles-view' },
        { ref: 'articleList', selector: 'sort-articles-list' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.categoryId = null;
        me.subApplication.treeStore =  me.subApplication.getStore('Tree');
        me.subApplication.treeStore.load();

        me.subApplication.articleStore =  me.subApplication.getStore('Article');

        me.subApplication.categorySettings =  me.subApplication.getStore('Settings');

        me.control({
            'sort-category-tree': {
                itemclick: me.onCategorySelect
            },
            'sort-articles-view': {
                defaultSort: me.onSaveSettings,
                sortChange: me.onSortChange,
                categoryLink: me.onSaveSettings
            },
            'sort-articles-list': {
                pageChange: me.onPageChange,
                moveToStart: me.onMoveToStart,
                moveToEnd: me.onMoveToEnd,
                moveToPrevPage: me.onMoveToPrevPage,
                moveToNextPage: me.onMoveToNextPage,
                articleMove: me.onArticleMove,
                unpin: me.onUnpin
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.subApplication.treeStore,
            articleStore: me.subApplication.articleStore,
            categorySettings: me.subApplication.categorySettings
        }).show();

        me.callParent(arguments);
    },

    /**
     * Event listener function of the article list panel.
     * Fired when the user uses paging navigation.
     */
    onPageChange: function() {
        var me = this,
            list = me.getArticleList();

        list.setLoading(true);
    },

    /**
     * Event listener function of the category tree panel.
     * Fired when the user select category from category tree.
     *
     * @param [object] view - Ext.view.View
     * @param [Ext.data.Model] The selected record
     */
    onCategorySelect: function(view, record) {
        var me = this,
            grid = me.getArticleView(),
            list = me.getArticleList();

        //Hide grid buttons on category select
        grid.setDisabled(true);
        list.setDisabled(true);
        list.setLoading(true);

        me.categoryId = record.get('id');

        me.subApplication.categorySettings.getProxy().extraParams = { categoryId: me.categoryId };
        me.subApplication.categorySettings.load({
            callback: function(records, operation, success) {
                if (success) {
                    var record = records[0];
                    var linkedCategoryId = record.get('categoryLink');

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

                    grid.sorting.setValue(record.get('baseSort'));
                }
            }
        });

        me.subApplication.articleStore.getProxy().extraParams = { categoryId: me.categoryId };
        me.subApplication.articleStore.filters.clear();
        me.subApplication.articleStore.currentPage = 1;
        me.subApplication.articleStore.load();

        me.subApplication.articleStore.on('load', function(){
            list.setLoading(false);
        });
    },

    /**
     * Prepare combo tree if selected category is linked to another category.
     *
     * @param [integer] linkedCategoryId
     * @returns [boolean]
     */
    prepareTreeCombo: function(linkedCategoryId) {
        var me = this,
            comboBox = me.getArticleView().categoryTreeCombo,
            treePanel = comboBox.getPicker(),
            treeStore = treePanel.getStore();

        //clear tree selection if it is not linked
        if (!linkedCategoryId) {
            treePanel.collapseAll();
            comboBox.setRawValue();
        }

        //helper function for selecting tree node
        var selectNode = function() {
            var node = treeStore.getNodeById(linkedCategoryId);
            if (node) {
                comboBox.setRawValue(node.get('name'));
                treePanel.collapseAll();
                treePanel.selectPath(node.getPath());
            }
        };

        //load whole category tree
        treeStore.on('load', function() {
            treePanel.expandAll();
            treePanel.collapseAll();

            //select tree node on first load
            selectNode();
        });

        //select tree node on change
        selectNode();
        return true;
    },

    /**
     * Event listener function of the sort combobox.
     * Fired when the user change sorting of articles in article view panel.
     *
     * @param [Ext.data.Model] The selected record
     */
    onSortChange: function(record) {
        var me = this,
            list = me.getArticleList();

        list.setLoading(true);

        me.subApplication.articleStore.getProxy().extraParams = { categoryId: me.categoryId, sortBy: record }
        me.subApplication.articleStore.load({
            callback: function() {
                list.setLoading(false);
            }
        });
    },

    /**
     * Event listener function of the article view panel.
     * Fired when the user change default display or linked category
     */
    onSaveSettings: function() {
        var me = this,
            grid = me.getArticleView(),
            list = me.getArticleList(),
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
            success: function() {
                Shopware.Notification.createGrowlMessage('{s name=main/success/title}Success{/s}', '{s name=main/success/message}Successfully applied changes{/s}');
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage('{s name=main/error/title}Error{/s}', '{s name=main/error/message}Changes were not saved{/s}');
            }
        });
    },

    /**
     *
     * @param [Ext.data.Store] The article store
     */
    onMoveToStart: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            list = me.getArticleList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null;

        list.setLoading(true);

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('position', index);
            record.set('oldPosition', oldPosition);
            record.set('pin', 1);
        });

        me.onSaveArticles(articleStore);

        return true;
    },
    /**
     * Event listener function of the article list.
     * Fired when the user click on "move to end" fast move icon.
     *
     * @param [Ext.data.Store] The article store
     */
    onMoveToEnd: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            list = me.getArticleList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            total = articleStore.getTotalCount() - 1;

        list.setLoading(true);

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('position', total - index);
            record.set('oldPosition', oldPosition);
            record.set('pin', 1);
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    /**
     * Event listener function of the article list.
     * Fired when the user click on "move to prev page" fast move icon.
     *
     * @param [Ext.data.Store] The article store
     */
    onMoveToPrevPage: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            list = me.getArticleList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null,
            count = selectedRecords.length;

        list.setLoading(true);

        selectedRecords.forEach(function(record) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('oldPosition', oldPosition);
            position = ((articleStore.currentPage - 1) * articleStore.pageSize) - count;
            record.set('position', position);
            record.set('pin', 1);
            count--;
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    /**
     * Event listener function of the article list.
     * Fired when the user click on "move to next page" fast move icon.
     *
     * @param [Ext.data.Store] The article store
     */
    onMoveToNextPage: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            list = me.getArticleList(),
            selectedRecords = list.dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null;

        list.setLoading(true);

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('oldPosition', oldPosition);
            position = (articleStore.currentPage * articleStore.pageSize) + index;
            record.set('position', position);
            record.set('pin', 1);
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    /**
     * Event listener function of the article view list.
     * Fired when the user move selected articles.
     *
     * @param [Ext.data.Store] The article store
     * @param [Array] Array with selected article in article view list
     * @param [Shopware.apps.Article.model.Article] The target record, on which the dragged record dropped
     */
    onArticleMove: function(articleStore, articleModel, targetRecord) {
        var me = this,
            list = me.getArticleList(),
            startPosition = (articleStore.currentPage - 1) * articleStore.pageSize;

        if (!articleStore instanceof Ext.data.Store
            || !targetRecord instanceof Ext.data.Model) {
            return false;
        }

        list.setLoading(true);
        var count = articleModel.length;
        if (count > 0) {

            var positionIndex = articleStore.indexOf(targetRecord) + startPosition;

            var forward = [], backward = [], temp = 0;
            Ext.each(articleModel, function(record) {
                var oldPosition = articleStore.indexOf(record) + startPosition;
                if (oldPosition < positionIndex) {
                    forward.push(record);
                }

                if (oldPosition > positionIndex) {
                    backward.push(record);
                }
            });

            Ext.each(articleModel, function(record, index) {
                if (!record instanceof Ext.data.Model) {
                    return;
                }

                var oldPosition, position;

                oldPosition = articleStore.indexOf(record) + startPosition;
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

        me.onSaveArticles(articleStore);
    },

    /**
     * Event listener function of the article view list.
     * Fired when the user move article in view list or use fast move buttons.
     *
     * @param [Ext.data.Store] The article store
     */
    onSaveArticles: function(articleStore) {
        articleStore.update();
    },

    onUnpin: function(articleStore, record) {
        var me = this,
            list = me.getArticleList();

        if (!articleStore instanceof Ext.data.Store || !record instanceof Ext.data.Model) {
            return false;
        }

        list.setLoading(true);

        var store = articleStore;
        record.set('pin', 0);
        articleStore.remove(record);
        articleStore.sync({
            success: function() {
                Shopware.Notification.createGrowlMessage('{s name=main/success/title}Success{/s}', '{s name=main/success/message}Successfully applied changes{/s}');
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage('{s name=main/error/title}Error{/s}','{s name=main/error/message}Changes were not saved{/s}');
                store.load();
            }
        });

        return true;
    }

});
//{/block}