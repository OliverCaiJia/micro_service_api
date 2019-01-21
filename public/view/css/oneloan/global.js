var nameZ = /^[\u4e00-\u9fa5]{2,21}$/;
var global = {
    /*姓名*/
    checkName: function ($dom, callback) {
        var _self = this;
        $dom.on('blur', function () {
            var nameVal = $.trim($dom.val());
            if (nameVal !== '' && !nameZ.test(nameVal)) {
                $dom.parent('div').addClass('errorStyle');
                _self.popupCover({
                    content: '姓名格式错误'
                })
            }
            if (callback) {
                callback();
            }
        }).bind('input propertychange', function () {
            $dom.parent('div').removeClass('errorStyle');
        });
    },
    getCardInfo: function (card) {
        var _self = this;
        var UUserCard = $.trim(card);
        var valLen = UUserCard.length;
        if (valLen === 15) {
            if (parseInt(UUserCard.substr(-1, 1)) % 2 == 1) {
                //男
                _self.sex = '1';
            } else {
                //女
                _self.sex = '0';
            }
            var age = '19' + UUserCard.substr(6, 6);
            _self.age = age;
        } else if (valLen === 18) {
            if (parseInt(UUserCard.substr(16, 1)) % 2 == 1) {
                //男
                _self.sex = '1';
            } else {
                //女
                _self.sex = '0';
            }
            var age = UUserCard.substr(6, 8);
            _self.age = age;
        } else {
            _self.age = '';
            _self.sex = '';
        }
    },
    /*身份证*/
    checkIdCard: function ($dom, callback) {
        var _self = this;
        var validator = new IDValidator();
        //判断身份证号码性别男女
        $dom.bind("input propertychange", function () {
            $dom.parent('div').removeClass('errorStyle');
        }).blur(function (e) {
            var idcardVal = $dom.val();
            var res = validator.isValid(idcardVal);
            if (idcardVal !== '') {
                if (!res) {
                    $dom.parent('div').addClass('errorStyle');
                    _self.popupCover({
                        content: '身份证号格式错误'
                    });
                }
            }
            if (callback) {
                callback();
            }
        });
    },
    /*城市选择*/
    getCity: function () {
        if ($('#city_cover_box').html() === '') {
            $.get(api_sudaizhijia_host + "/view/oneloan/citys", function (result) {
                $('#city_cover_box').html(result);
            });
        }
        $('#city_cover_box').show();
    },
    /*弹窗*/
    popupCover: function (opts) {
        $(".hintCover").remove();
        var defaults = {
            content: '',
            showTime: 2000,
            callback: ''
        };
        opts.callback;
        var option = $.extend({}, defaults, opts);
        var posTop = $(window).height() * .5;
        $('body').append('<div class="hintCover"><div class="hintPopup"></div></div>');
        $('.hintPopup').text(option.content);
        $('.hintCover').css({
            "position": "fixed",
            "top": 0,
            "left": 0,
            "width": 100 + "%",
            "height": 100 + "%",
            "background": "rgba(0,0,0,.2)",
            "z-index": "99999",
            "text-align": "center"
        });
        $('.hintPopup').css({
            "margin-top": posTop,
            "max-width": "6rem",
            "display": "inline-block",
            "height": ".76rem",
            "line-height": ".76rem",
            "text-align": "center",
            "background": "rgba(0, 0, 0, 0.8)",
            "color": '#fff',
            "font-size": .34 + "rem",
            "border-radius": .1 + "rem",
            "padding": "0 .35rem",
            "animation": "popupCover .12s ",
            "-webkit-animation": "popupCover .12s "
        });
        setTimeout(function () {
            $(".hintCover").fadeOut(300, option.callback);
            setTimeout(function () {
                $(".hintCover").remove();
            }, 500);
        }, .12 * 1000 + option.showTime);
    },
    /*加载动画*/
    addLoading: function (opts) {
        var defaults = {
            time: 60 * 1000
        };
        var option = $.extend({}, defaults, opts);
        $('body').append('<div class="loadingCover"><div class="loadingImg"></div></div>');
        $('.loadingCover').css({
            "position": "fixed",
            "top": 0,
            "left": 0,
            "width": 100 + "%",
            "height": 100 + "%"
        });
        $('.loadingImg').css({
            'width': '1rem',
            'height': '1rem',
            'position': 'absolute',
            'top': '50%',
            'left': '50%',
            'border-radius': '.1rem',
            'margin-left': '-.5rem',
            'margin-top': '-.5rem',
            'background': 'rgba(0, 0, 0, .8) url("/view/img/oneloan/loading.gif") no-repeat center center',
            'background-size': '.75rem .75rem',
        })
        setTimeout(function () {
            $('.loadingCover').remove();
        }, option.time)
    },
    /*删除加载动画*/
    removeLoading: function () {
        $('.loadingCover').remove();
    },
    /*触发登录*/
    sdLogin: function () {
        try {
            window.sdbind.sdLogin();
        } catch (e) {
            console.log("Android触发登录错误");
        }
        try {
            window.webkit.messageHandlers.sdLogin.postMessage({});
        } catch (e) {
            console.log("ios触发登录错误");

        }
    },
}
