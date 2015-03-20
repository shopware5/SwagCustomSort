//{namespace name="backend/custom_sort/main"}
//{block name="backend/custom_sort/view/article/list"}
Ext.define('Shopware.apps.CustomSort.view.article.List', {

    /**
     * Define that the article list is an extension of the Ext.panel.Panel
     * @string
     */
    extend: 'Ext.panel.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.sort-articles-list',

    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    dragOverCls: 'drag-over',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-sort',

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

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'Article',
                enableDrop: true
            }
        };

        me.items = [ me.createArticleView() ];
        me.dockedItems = [ me.getPagingBar() ];
        me.registerMoveActions();

        me.callParent(arguments);
    },

    /**
     * Creates the article listing based on an Ext.view.View (know as DataView)
     * and binds the "Article"-store to it
     *
     * @return [object] this.dataView - created Ext.view.View
     */
    createArticleView: function() {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            height: '100%',
            disabled: false,
            itemSelector: '.thumb',
            name: 'image-listing',
            padding: '10 10 20',
            emptyText: '<div class="empty-text"><span>{s name=list/no_articles}No articles found{/s}</span></div>',
            multiSelect: true,
            store: me.store,
            tpl: me.createArticleViewTemplate(),
            listeners: {
                itemclick: function(view, record, item, idx, event, opts) {
                    if (event.target.classList.contains('pin')) {
                        me.fireEvent('unpin', me.store, record);
                    }
                    if (event.target.parentElement.className === 'paging') {
                        return false;
                    }
                }
            }
        });

        me.initDragAndDrop();

        return me.dataView;
    },

    /**
     * Creates the template for the article view panel
     *
     * @return [object] generated Ext.XTemplate
     */
    createArticleViewTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            // If the type is image, then show the image
            '<div class="thumb">',
                '<tpl if="values.pin==1">',
                    '<span class="sprite-sticky-notes-pin pin"></span>',
                '</tpl>',
                '<div class="inner-thumb">',
                    '<img src="','{link file=""}','{literal}{thumbnail}{/literal}','" />' ,
                    '<div class="detail">',
                        '<span>{[Ext.util.Format.ellipsis(values.name, 50)]}</span>',
                        '<span class="paging">',
                            '<span class="first{[this.startPage(values, xindex)]}"></span>',
                            '<span class="prev{[this.prevPage()]}"></span>',
                            '<span class="next{[this.nextPage()]}"></span>',
                            '<span class="last{[this.endPage(values, xindex)]}"></span>',
                        '</span>',
                    '</div>',
                '</div>',
            '</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}',
            {
                //Add class if current product is first position in store list
                startPage: function(article, index) {
                    var store = me.store,
                        view = me.dataView,
                        position,
                        record = view.getStore().getAt((index - 1));

                    position = store.indexOf(record) + ((store.currentPage - 1) * store.pageSize);
                    if (position == 0) {
                       return ' disabled';
                    }
                },

                //Add class if current product is on first page in store list
                prevPage: function() {
                    var store = me.store;

                    if (store.currentPage <= 1) {
                        return ' disabled';
                    }
                },

                //Add class if current product is on last page in store list
                nextPage: function() {
                    var store = me.store, lastPage;

                    lastPage = store.totalCount / store.pageSize;
                    if (lastPage <= store.currentPage){
                        return ' disabled';
                    }
                },

                //Add class if current product is on last position in store list
                endPage: function(article, index) {
                    var store = me.store,
                        view = me.dataView,
                        position,
                        record = view.getStore().getAt((index - 1));

                    position = store.indexOf(record) + ((store.currentPage - 1) * store.pageSize);
                    if ((position + 1) >= store.totalCount) {
                        return ' disabled';
                    }
                }
            }
        );
    },

    /**
     * Create trigger catch when fast move button is click
     */
    registerMoveActions: function() {
        var me = this;

        var el = Ext.getBody();
        //Trigger event when "move to start" action is clicked
        el.on('click', function(event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToStart', me.store);
        }, null, {
            delegate: 'span.first'
        });

        //Trigger event when "move to end" action is clicked
        el.on('click', function(event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToEnd', me.store);
        }, null, {
            delegate: 'span.last'
        });

        //Trigger event when "move to prev page" action is clicked
        el.on('click', function(event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToPrevPage', me.store);
        }, null, {
            delegate: 'span.prev'
        });

        //Trigger event when "move to next page" action is clicked
        el.on('click', function(event, target) {
            if (target.classList.contains('disabled')) {
                return false;
            }
            event.stopEvent();
            me.fireEvent('moveToNextPage', me.store);
        }, null, {
            delegate: 'span.next'
        });
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingBar: function() {
        var me = this,
            productSnippet = '{s name=list/pagingCombo/products}products{/s}';

        var pageSize = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 120,
            cls: Ext.baseCSSPrefix + 'page-size',
            queryMode: 'local',
            width: 180,
            editable: false,
            value: me.store.pageSize,
            listeners: {
                scope: me,
                select: me.onPageSizeChange
            },
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: [
                    { value: '10', name: '10 ' + productSnippet },
                    { value: '25', name: '25 ' + productSnippet },
                    { value: '50', name: '50 ' + productSnippet },
                    { value: '75', name: '75 ' + productSnippet }
                ]
            }),
            displayField: 'name',
            valueField: 'value'
        });
        pageSize.setValue(me.store.pageSize + '');

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            dock: 'bottom',
            displayInfo: true,
            store: me.store
        });

        pagingBar.insert(pagingBar.items.length - 2, [
            { xtype: 'tbspacer', width: 6 },
            pageSize
        ]);

        return pagingBar;
    },

    /**
     * Event listener method which fires when the user selects
     * a entry in the "number of products"-combo box.
     *
     * @event select
     * @param [object] combo - Ext.form.field.ComboBox
     * @param [array] records - Array of selected entries
     * @return void
     */
    onPageSizeChange: function (combo, records) {
        var record = records[0],
            me = this;

        me.store.pageSize = record.get('value');
        me.store.loadPage(1);
    },

    /**
     * Creates the drag and drop zone for the Ext.view.View to allow
     */
    initDragAndDrop: function() {
        var me = this;

        me.dataView.on('afterrender', function(v) {
            var selModel = v.getSelectionModel();
            me.dataView.dragZone = new Ext.dd.DragZone(v.getEl(), {
                ddGroup: 'Article',
                getDragData: function(e) {
                    var sourceEl = e.getTarget(v.itemSelector, 10);
                    if (sourceEl) {
                        var selected = selModel.getSelection(),
                            record = v.getRecord(sourceEl);

                        if(!selected.length) {
                            selModel.select(record);
                            selected = selModel.getSelection();
                        }

                        var d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        var result = {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: v.store,
                            draggedRecord: v.getRecord(sourceEl),
                            articleModels: selected
                        };

                        return result;
                    }
                },
                getRepairXY: function() {
                    return this.dragData.repairXY;
                }
            });

            me.dataView.dropZone = new Ext.dd.DropZone(me.dataView.getEl(), {
                ddGroup: 'Article',
                getTargetFromEvent: function(e) {
                    return e.getTarget(me.dataView.itemSelector);
                },

                onNodeEnter : function(target, dd, e, data) {
                    var record = me.dataView.getRecord(target);
                    if (record !== data.draggedRecord) {
                        Ext.fly(target).addCls(me.dragOverCls);
                    }
                },

                onNodeOut : function(target, dd, e, data) {
                    Ext.fly(target).removeCls(me.dragOverCls);
                },

                onNodeDrop : function(target, dd, e, data) {
                    var draggedRecord = me.dataView.getRecord(target);
                    var articleModels = data.articleModels;

                    me.fireEvent('articleMove', me.store, articleModels, draggedRecord)
                }
            });

        });
    }

});
//{/block}