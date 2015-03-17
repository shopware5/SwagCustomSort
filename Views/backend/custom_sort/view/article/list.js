//{block name="backend/custom_sort/view/article/list"}
Ext.define('Shopware.apps.CustomSort.view.article.List', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.sort-articles-list',

    border: 0,

    autoScroll: true,

    dragOverCls: 'drag-over',

    cls: Ext.baseCSSPrefix + 'article-sort',

    initComponent:function () {
        var me = this;

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'Article',
                enableDrop: true
            }
        };
        me.tbar = me.createActionToolbar();
        me.items = [ me.createMediaView() ];
        me.dockedItems = [ me.getPagingBar() ];

        me.callParent(arguments);
    },

    createMediaView: function() {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            height: '100%',
            disabled: false,
            itemSelector: '.thumb',
            name: 'image-listing',
            padding: '10 10 20',
            emptyText: '<div class="empty-text"><span>No articles found</span></div>',
            multiSelect: true,
            store: me.store,
            tpl: me.createMediaViewTemplate()
        });

        me.initDragAndDrop();

        return me.dataView;
    },

    createMediaViewTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            '<tpl if="main===1">',
            '<div class="article-thumb-wrap main" >',
            '</tpl>',
            '<tpl if="main!=1">',
            '<div class="article-thumb-wrap" >',
            '</tpl>',

            // If the type is image, then show the image
            '<div class="thumb">',
            '<div class="inner-thumb"><img src="','{link file=""}','{literal}{thumbnail}{/literal}','" /><span>{[Ext.util.Format.ellipsis(values.name, 50)]}</span></div>',
            '<tpl if="main===1">',
            '<div class="preview"><span>Test</span></div>',
            '</tpl>',
            '<tpl if="hasConfig">',
            '<div class="mapping-config">&nbsp;</div>',
            '</tpl>',
            '</div>',
            '</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    },

    createActionToolbar: function() {
        var me = this;

        me.moveToStart = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to start',
            action: 'moveToStart',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToStart', me.store);
            }
        });

        me.moveToEnd = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to end',
            action: 'moveToEnd',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToEnd', me.store);
            }
        });

        me.moveToPrevPage = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to previous page',
            action: 'moveToPrevPage',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToPrevPage', me.store);
            }
        });

        me.moveToNextPage = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to next page',
            action: 'moveToNextPage',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToNextPage', me.store);
            }
        });

        return [ me.moveToStart, me.moveToEnd, me.moveToPrevPage, me.moveToNextPage ];
    },

    getPagingBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            dock: 'bottom',
            store: me.store,
            displayInfo: true
        });
    },

    initDragAndDrop: function() {
        var me = this;

        me.dataView.on('afterrender', function(v) {
            var selModel = v.getSelectionModel();
            me.dataView.dragZone = new Ext.dd.DragZone(v.getEl(), {
                ddGroup: 'Article',
                getDragData: function(e) {
                    me.fireEvent('articleDeselect');
                    var sourceEl = e.getTarget(v.itemSelector, 10);
                    if (sourceEl) {
                        var selected = selModel.getSelection(),
                            record = v.getRecord(sourceEl);

                        if(!selected.length) {
                            selModel.select(record);
                            selected = selModel.getSelection();
                        }

                        me.fireEvent('articleSelect', me.store, v.getRecord(sourceEl));
                        var d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        var result = {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: v.store,
                            draggedRecord: v.getRecord(sourceEl),
                            articleModels: selected
                        }

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