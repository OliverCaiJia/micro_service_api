var resultController = {
    init: function () {
        this.eventView();
    },
    eventView: function () {
        $('.back_btn').on('click', function () {
            this.goBack();
        });
        $(document).on('click', '.product_list', function () {
            var productId = $(this).data('productId');
            this.goDetail(productId);
        });
        $(document).on('click', '.apply', function () {
            this.goApply({});
        });
        $('#more-products-btn').on('click', function () {
            this.goProductList();
        });
    },
    goBack: function () {
        var backPage = $.cookie('back_page');
        $('#container>div').hide();
        if (backPage == 1) {
            $('#basic-page').show();
        } else if (backPage == 2) {
            $('#full-page').show();
        }
    },
    goDetail: function (productId) {
        try {
            window.sdbind.sdProductDetail(productId);
        } catch (e) {
            console.log("Android跳转详情错误");
        }
        try {
            window.webkit.messageHandlers.sdProductDetail.postMessage({
                productId: productId
            });
        } catch (e) {
            console.log("ios跳转详情错误");

        }
    },
    goApply: function (opt) {
        var data = {
            platformId: opt.platformId,
            productId: opt.productId,
            typeNid: opt.typeNid,
            title: opt.title,
            mobile: opt.mobile
        };
        var androidData = JSON.stringify(data);
        try {
            window.sdbind.sdProductWebView(androidData);
        } catch (e) {
            console.log("Android跳转产品H5错误");
        }
        try {
            window.webkit.messageHandlers.sdProductWebView.postMessage(data);
        } catch (e) {
            console.log("ios跳转产品H5错误");

        }
    },
    goProductList: function () {
        try {
            window.sdbind.sdProductList();
        } catch (e) {
            console.log("Android跳转到产品大全错误");
        }
        try {
            window.webkit.messageHandlers.sdProductList.postMessage({});
        } catch (e) {
            console.log("ios跳转到产品大全错误");
        }
    },

};
$(function () {
    resultController.init();
})
