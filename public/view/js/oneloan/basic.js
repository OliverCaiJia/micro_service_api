var basicController = {
    init: function () {
        this.getData();
        this.advertisement.init();
        this.goBack();
        this.checkMoney();
        this.submitBtn();
        this.getAgreement();
    },
    getData: function () {
        var token = $('.token').text(),
            location = $('.location').text(),
            terminalType = $('.terminalType').text(),
            deviceId = $('.deviceId').text()
        $.cookie('token', token, {
            expires: 1,
            path: '/'
        });
        $.cookie('location', location, {
            expires: 1,
            path: '/'
        });
        $.cookie('terminalType', terminalType, {
            expires: 1,
            path: '/'
        });
        $.cookie('deviceId', deviceId, {
            expires: 1,
            path: '/'
        });
    },
    goBack: function () {
        $('.back_btn').on('click', function () {
            try {
                window.sdbind.sdClose();
            } catch (e) {
                console.log("Android返回错误");
            }
            try {
                window.webkit.messageHandlers.sdClose.postMessage({});
            } catch (e) {
                console.log("ios返回错误");

            }
        });
    },
    /*单行广告轮播*/
    advertisement: {
        init: function () {
            this.random(0);
            this.poster();
            this.applyNum();
        },
        poster: function () {
            var _self = this;
            var liHeight = $("#inducement-ul li").height(); //一个li的高度
            setInterval(function () {
                _self.random(1)
                $("#inducement-ul").animate({
                    top: -liHeight
                }, 500, function () {
                    $("#inducement-ul li").eq(0).appendTo($("#inducement-ul"));
                    $("#inducement-ul").css({
                        "top": 0
                    });
                })
            }, 3000)
        },
        random: function (idx) {
            //申请数
            var applyNum = function () {
                var oDate = new Date();
                var timeS = oDate.getHours() * 60 * 60 + oDate.getMinutes() * 60 + oDate.getSeconds();
                var applyNum = timeS * 3 + 2000;
                $("#inducement-ul li").eq(idx).find('.applyNum').text(applyNum);
            }
            applyNum();
            //2.电话号码
            var phone = function () {
                var prefixArray = new Array("130", "131", "132", "133", "135", "137", "138", "170", "187", "189");
                var i = parseInt(10 * Math.random());
                var prefix = prefixArray[i];
                for (var j = 0; j < 8; j++) {
                    prefix = prefix + Math.floor(Math.random() * 10);
                }
                var phone = prefix.substring(0, 3) + "****" + prefix.substring(7, 11);
                $("#inducement-ul li").eq(idx).find(".random_mobile").text(phone);
            }
            phone();
            //3.价钱
            var rnd = function () {
                var min = 1;
                var max = 1000;
                var money = min + Math.floor(Math.random() * (max - min + 1));
                $("#inducement-ul li").eq(idx).find(".random_money").text(money + "00");
            }
            rnd()
        },
        applyNum: function () {
            //申请数
            var oDate = new Date();
            var timeS = oDate.getHours() * 60 * 60 + oDate.getMinutes() * 60 + oDate.getSeconds();
            var applyNum = timeS * 3 + 2000;
            $("#inducement-ul li").eq(0).find('.applyNum').text(applyNum);
            setInterval(function () {
                applyNum += 27;
                $("#inducement-ul li").eq(1).find('.applyNum').text(applyNum);
            }, 9000);
        }
    },
    //金额判断
    checkMoney: function () {
        var _self = this;
        $('#basic-money').on('blur', function () {
            _self.checkHideOption();
            var val = $.trim($(this).val());
            if (val !== '' && val < 100) {
                global.popupCover({
                    content: "最小额度为100"
                });
                $('#basic-money').val('100');
                $('#basic-data').slideDown(200, function () {
                    _self.moreOption = true;
                });

            } else if (val > 100 && val < 10000) {
                $('#basic-data').slideDown(200, function () {
                    _self.moreOption = true;
                });

            } else if (val > 1000000) {
                global.popupCover({
                    content: "最大额度为1000000",
                });
                $('#basic-money').val('1000000');
                $('#basic-data').slideUp(200, function () {
                    _self.moreOption = false;
                });
            } else {
                $('#basic-data').slideUp(200, function () {
                    _self.moreOption = false;
                });
            }
        })
    },
    //隐藏的选项判断
    checkHideOption: function () {
        global.checkName($('#basic-name'));
        global.checkIdCard($('#basic-idcard'));
        //城市
        var location = $.cookie('location');
        if (location !== '') {
            $('#basic-city').val(location);
        }
        $('#basic-city').on('click', function () {
            global.getCity();
            $(this).parent('div').removeClass('errorStyle');
        });
    },
    //协议
    getAgreement: function () {
        $('#agreement_icon').on('click', function () {
            $(this).toggleClass('onSelected');
        });
        $('#agreement_btn').on('click', function () {
            if ($('#agreement-page').html() === '') {
                $.get(api_sudaizhijia_host + "/view/oneloan/agreement", function (result) {
                    $('#agreement-page').html(result);
                });
            }
            $('#agreement-page').show();
        })
    },
    //提交按钮
    submitBtn: function () {
        var _self = this;
        $('#basic-submit').on('click', function () {
            var moneyVal = $.trim($('#basic-money').val());
            if (moneyVal == '') {
                global.popupCover({
                    content: "请输入金额"
                });
            } else {
                $.cookie('money', moneyVal, {
                    expires: 1,
                    path: '/'
                });
                if (moneyVal < 10000) {
                    if (_self.moreOption) {
                        _self.checkSubmit();
                    }
                } else {
                    $.get(api_sudaizhijia_host + "/view/oneloan/full", function (result) {
                        $('#full-page').show().html(result);
                        $('#full-page').siblings('div').hide();
                    });
                }
            }

        })
    },
    //判断信息填写
    checkSubmit: function () {
        var _self = this;
        var validator = new IDValidator();
        var IDcard = validator.isValid($.trim($('#basic-idcard').val()));
        var agreement_icon = $('#agreement_icon').hasClass('onSelected') ? true : false;
        if (!nameZ.test($('#basic-name').val())) {
            global.popupCover({
                content: "请输入正确姓名"
            });

            $('#basic-name').focus().parent('div').addClass('errorStyle');
            return false;
        } else if (!IDcard) {
            global.popupCover({
                content: "请输入正确身份证号"
            });
            $('#basic-idcard').focus().parent('div').addClass('errorStyle');
            return false;
        } else if ($('#basic-city').val() === '') {
            global.popupCover({
                content: "请选择城市信息"
            });
            $('#basic-city').focus().parent('div').addClass('errorStyle');
            return false;
        } else if (!agreement_icon) {
            global.popupCover({
                content: "请勾选协议"
            });
            return false;
        } else {
            _self.postData();
        }
    },
    postData: function () {
        var _self = this;
        var token = $.cookie('token');
        if (token == '') {
            global.sdLogin();
        } else {
            global.addLoading();
            $.ajax({
                //                url: "https://uat.api.sudaizhijia.com/oneloan/v1/basic",
                url: api_sudaizhijia_host + "/oneloan/v1/spread/basic",
                type: "post",
                dataType: "json",
                data: {
                    money: $('#basic-money').val(),
                    name: $('#basic-name').val(),
                    certificate_no: $('#basic-idcard').val(),
                    sex: global.sex,
                    birthday: global.age,
                    city: $('#basic-city').val()
                },
                success: function (result) {
                    $.cookie('back_page', '1', {
                        expires: 1,
                        path: '/'
                    });
                    $('#result-page').show().html(result);
                    $('#result-page').siblings('div').hide();
                    global.removeLoading();
                }
            })
        }

    }
};

$(function () {
    basicController.init();
})
