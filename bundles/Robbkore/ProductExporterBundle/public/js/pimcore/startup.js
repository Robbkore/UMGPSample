pimcore.registerNS("pimcore.plugin.ProductExporterBundle");

pimcore.plugin.ProductExporterBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
    },

    pimcoreReady: function (e) {
        // alert("ProductExporterBundle ready!");
    }
});

var ProductExporterBundlePlugin = new pimcore.plugin.ProductExporterBundle();
