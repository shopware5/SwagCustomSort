//{namespace name="backend/custom_sort/view/main"}
//{block name="backend/custom_sort/app"}
Ext.define('Shopware.apps.CustomSort', {

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.CustomSort',

    /**
     * Extends from our special controller, which handles the sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Enable bulk loading
     * @boolean
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @string
     */
    loadPath: '{url controller=CustomSort action=load}',

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers: [
        'Main'
    ],

    models: [
        'Settings',
        'Article'
    ],

    stores: [
        'Settings',
        'Tree',
        'Article'
    ],

    views: [
        'main.Window',
        'category.Tree',
        'article.View',
        'article.List'
    ],

    /**
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     */
    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
//{/block}