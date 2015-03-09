//{block name="backend/custom_sort/view/article/list"}
Ext.define('Shopware.apps.CustomSort.view.article.List', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.sort-articles-list',

    border: 0,

    autoScroll: true,

    initComponent:function () {
        var me = this;

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
            itemSelector: '.article-thumb-wrap',
            name: 'image-listing',
            padding: '10 10 20',
            emptyText: '<div class="empty-text"><span>No articles found</span></div>',
            multiSelect: true,
            store: me.store,
            tpl: me.createMediaViewTemplate()
        });

        //TODO: Set event listeners for the selection model to lock/unlock the move buttons
        me.dataView.getSelectionModel().on('select', function (dataViewModel, article) {
            me.fireEvent('articleSelect', dataViewModel, article);
        });

        me.dataView.getSelectionModel().on('deselect', function (dataViewModel, article) {
            me.fireEvent('articleDeselect', dataViewModel, article);
        });

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
            '<div class="inner-thumb"><img src="','{link file=""}','{literal}{path}{/literal}','" /></div>',
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
                me.fireEvent('moveToStart');
            }
        });

        me.moveToEnd = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to end',
            action: 'moveToEnd',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToEnd');
            }
        });

        me.moveToPrevPage = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to previous page',
            action: 'moveToPrevPage',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToPrevPage');
            }
        });

        me.moveToNextPage = Ext.create('Ext.button.Button', {
            text: 'Move selected item(s) to next page',
            action: 'moveToNextPage',
            disabled: true,
            handler: function() {
                me.fireEvent('moveToNextPage');
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
    }

});
//{/block}