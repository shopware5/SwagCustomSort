//{block name="backend/custom_sort/view/article/view"}
Ext.define('Shopware.apps.CustomSort.view.article.View', {

    extend: 'Ext.panel.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias : 'widget.sort-articles-view',

    layout: 'fit',

    disabled: 'true',

    dragOverCls: 'drag-over',

    maximize: true,

    initComponent: function() {
        var me = this;

        me.tbar = me.createActionToolbar();
        me.items = [{
            xtype: 'container',
            style: 'background: #fff',
            autoScroll: true,
            items: [
                me.createMediaView()
            ]
        }];
        me.dockedItems = [ me.getPagingBar() ];

        me.callParent(arguments);
    },

    createActionToolbar: function() {
        var me = this;

        return [{
            xtype: 'checkbox',
            cls: 'confirm-check-box',
            boxLabel: 'Show this sort order by default',
            name: 'default',
            inputValue: 1,
            uncheckedValue: 0,
            labelWidth: 180
        }, {
            xtype: 'combobox',
            fieldLabel: 'Sync from category',
            editable: false,
            store: me.treeStore,
            displayField: 'name',
            triggerAction: 'all',
            valueField: 'id',
            typeAhead: false,
            forceSelection: true,
            loadingText: 'Searching...',
            pageSize: 10,
            hideTrigger: false,
            labelWidth: 150
        }, {
            xtype: 'combobox',
            fieldLabel: 'Base sorting',
            store: me.treeStore
        }];
    },

    createGridForm: function() {
        var me = this;

        me.articlesForm = Ext.create('Ext.form.Panel', {
            layout: 'vbox',
            border: 0,
            style: 'background: #fff',
            items: [
                me.createMediaView(),
                me.getPagingBar()
            ]
        });

        return me.articlesForm;
    },

    createMediaView: function() {
        var me = this;

        var multiSelect = true;
        if(Ext.isBoolean(me.selectionMode)) {
            multiSelect = me.selectionMode;
        }

        me.dataView = Ext.create('Ext.view.View', {
            itemSelector: '.thumb-wrap',
            emptyText: '<div class="empty-text"><span>No articles found</span></div>',
            multiSelect: multiSelect,
            store: me.store,
            tpl: me.createMediaViewTemplate()
        });

        //TODO: Set event listeners for the selection model to lock/unlock the move buttons
        me.dataView.getSelectionModel().on({
            'select': {
                fn: me.onSelectMedia,
                scope: me
            },
            'deselect': {
                fn: me.onLockMoveButton,
                scope: me
            }
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