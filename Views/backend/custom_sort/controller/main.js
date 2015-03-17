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
        { ref: 'articleView', selector: 'sort-articles-view' },
        { ref: 'articleList', selector: 'sort-articles-list' }
    ],

    /**
     * Sets up the ui component
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
                moveToStart: me.onMoveToStart,
                moveToEnd: me.onMoveToEnd,
                moveToPrevPage: me.onMoveToPrevPage,
                moveToNextPage: me.onMoveToNextPage,
                articleMove: me.onArticleMove,
                articleSelect: me.onArticleSelect,
                articleDeselect: me.onArticleDeselect
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.subApplication.treeStore,
            articleStore: me.subApplication.articleStore,
            categorySettings: me.subApplication.categorySettings
        }).show();

        me.callParent(arguments);
    },

    onCategorySelect: function(view, record) {
        var me = this,
            grid = me.getArticleView(),
            list = me.getArticleList();

        //Hide grid buttons on category select
        grid.setDisabled(true);
        list.setDisabled(true);

        me.categoryId = record.get('id');

        me.subApplication.categorySettings.getProxy().extraParams = { categoryId: me.categoryId };
        me.subApplication.categorySettings.load({
            callback: function(records, operation, success) {
                if (success) {
                    var record = records[0];
                    var linkedCategoryId = record.get('categoryLink');

                    grid.loadRecord(record);
                    me.prepareTreeCombo(linkedCategoryId);

                    grid.setDisabled(false);
                    grid.categoryTreeCombo.setDisabled(false);
                    if (linkedCategoryId > 0) {
                        grid.defaultSort.setDisabled(true);
                        grid.sorting.setDisabled(true);
                    } else {
                        grid.defaultSort.setDisabled(false);
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
    },

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

        me.onArticleDeselect();
    },

    onSaveSettings: function() {
        var me = this,
            grid = me.getArticleView(),
            list = me.getArticleList(),
            form = grid.getForm(),
            record = form.getRecord(),
            values = form.getValues();

        if (values.categoryLink > 0) {
            grid.defaultSort.setDisabled(true);
            grid.sorting.setDisabled(true);
            list.setDisabled(true);
        } else {
            grid.defaultSort.setDisabled(false);
            grid.sorting.setDisabled(false);
            list.setDisabled(false);
        }

        record.set(values);

        record.save({
            success: function() {
                Shopware.Notification.createGrowlMessage('Success', 'Successfully applied changes');
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage('Error','Some error appear');
            }
        });
    },

    onMoveToStart: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            selectedRecords = me.getArticleList().dataView.getSelectionModel().getSelection(),
            oldPosition = null;

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('position', index);
            record.set('oldPosition', oldPosition);
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    onMoveToEnd: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            selectedRecords = me.getArticleList().dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            total = articleStore.getTotalCount() - 1;

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('position', total - index);
            record.set('oldPosition', oldPosition);
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    onMoveToPrevPage: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            selectedRecords = me.getArticleList().dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null,
            count = selectedRecords.length;

        selectedRecords.forEach(function(record) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('oldPosition', oldPosition);
            position = ((articleStore.currentPage - 1) * articleStore.pageSize) - count;
            record.set('position', position);
            count--;
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    onMoveToNextPage: function(articleStore) {
        if (!articleStore instanceof Ext.data.Store) {
            return false;
        }

        var me = this,
            selectedRecords = me.getArticleList().dataView.getSelectionModel().getSelection(),
            oldPosition = null,
            position = null;

        selectedRecords.forEach(function(record, index) {
            if (!record instanceof Ext.data.Model) {
                return false;
            }

            oldPosition = articleStore.indexOf(record) + ((articleStore.currentPage - 1) * articleStore.pageSize);
            record.set('oldPosition', oldPosition);
            position = (articleStore.currentPage * articleStore.pageSize) + index;
            record.set('position', position);
        });

        me.onSaveArticles(articleStore);

        return true;
    },

    onArticleMove: function(articleStore, articleModel, targetRecord) {
        var me = this,
            startPosition = (articleStore.currentPage - 1) * articleStore.pageSize;

        if (!articleStore instanceof Ext.data.Store
            || !targetRecord instanceof Ext.data.Model) {
            return false;
        }

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
            });

        }

        me.onSaveArticles(articleStore);
    },

    onArticleSelect: function(store, article) {
        var me = this,
            list = me.getArticleList(),
            index, lastPage;

        index = store.indexOf(article) + ((store.currentPage - 1) * store.pageSize);
        if (index > 0) {
            list.moveToStart.setDisabled(false);
        }

        if ((index + 1) < store.totalCount) {
            list.moveToEnd.setDisabled(false);
        }

        if (store.currentPage > 1) {
            list.moveToPrevPage.setDisabled(false);
        }

        lastPage = store.totalCount / store.pageSize;
        if (lastPage > store.currentPage){
            list.moveToNextPage.setDisabled(false);
        }
    },

    onArticleDeselect: function() {
        var me = this,
            list = me.getArticleList();

        list.moveToStart.setDisabled(true);
        list.moveToEnd.setDisabled(true);
        list.moveToPrevPage.setDisabled(true);
        list.moveToNextPage.setDisabled(true);
    },

    onSaveArticles: function(articleStore) {
        articleStore.update();
    }

});
//{/block}