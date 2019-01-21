var infoController = {
    init: function () {
        this.progress($("#score").text());
        var gradeStatus = $('#grade').data('value');
        $(".status_img").attr("src", '/view/img/report/' + this.status(gradeStatus).imgName);
        $("#status_text").text('(信用' + this.status(gradeStatus).statusText + ')');
        this.coverBtn();
    },
    /*监听APP返回按钮*/
    backBtn: function () {
        try {
            window.webkit.messageHandlers.back.postMessage({});
        } catch (e) {
            console.log(e);
        }
        try {
            window.mobileCallback.back();
        } catch (e) {
            console.log(e);
        }
    },
    /*进度条-Value为传的分值*/
    progress: function (Value) {
        var _self = this;
        var num1 = parseInt(Value / 180),
            num2 = Value % 180;
        var i = 1;
        var t = setInterval(function () {
            if (num1 >= 1) {
                $('.progress-box-style').animate({
                    'width': 180 * i / 900 * 100 + '%'
                }, 300);
                setTimeout(function () {
                    $('.mature-progress-box>dl').eq(i - 1).find('dt').addClass('onStyle').css({
                        "background": "#24c08e"
                    });
                }, 200);
                i++;
                if (i > num1) {
                    clearInterval(t);
                    $('.progress-box-style').animate({
                        'width': Value / 900 * 100 + '%'
                    }, 200, 'linear', function () {
                        textStyle();
                    });
                }
            } else {
                $('.progress-box-style').animate({
                    'width': Value / 900 * 100 + '%'
                }, 200, 'linear', function () {
                    textStyle();
                });
            }
        }, 300);

        function textStyle() {
            var gradeStatus = $('#grade').data('value'),
                idx = _self.status(gradeStatus).highlightIdx;
            $('#mamture_progress>dl').eq(idx).find('dd').css({
                'color': '#24c08e',
                'font-size': .28 + 'rem',
                'line-height': .42 + 'rem'
            }).animate({})
        }
    },
    /*等级判断*/
    status: function (Value) {
        var src = '/view/img/report/',
            imgName, statusText, highlightIdx;
        switch (Value) {
            case 1:
                imgName = 'a+.png';
                statusText = '极好';
                highlightIdx = 5;
                break;
            case 2:
                imgName = 'a-.png';
                statusText = '极好';
                highlightIdx = 5;
                break;
            case 3:
                imgName = 'B.png';
                statusText = '优秀';
                highlightIdx = 4;
                break;
            case 4:
                imgName = 'B-.png';
                statusText = '优秀';
                highlightIdx = 4;
                break;
            case 5:
                imgName = 'C.png';
                statusText = '良好';
                highlightIdx = 3;
                break;
            case 6:
                imgName = 'C-.png';
                statusText = '良好';
                highlightIdx = 3;
                break;
            case 7:
                imgName = 'D.png';
                statusText = '较好';
                highlightIdx = 2;
                break;
            case 8:
                imgName = 'D-.png';
                statusText = '较好';
                highlightIdx = 2;
                break;
            case 9:
                imgName = 'E.png';
                statusText = '低';
                highlightIdx = 1;
                break;
        }
        return {
            imgName: imgName,
            statusText: statusText,
            highlightIdx: highlightIdx
        };
    },
    /*消息提示*/
    promptCover: function (content) {
        $('body').append('<div class="promptCover"><div class="promptPopup"><h3>说明</h3><div class="popupContent"></div><div class="sureBtn">朕知道了</div></div></div>');
        $('.popupContent').html(content);
        $('.sureBtn').click(function () {
            $('.promptCover').remove();
        })
    },
    /*弹窗*/
    coverBtn: function () {
        var _this = this;
        $('.cover_icon').on('click', function () {
            var Text = $(this).text();
            _this.promptCover(Text);
        });
    }
};
$(function () {
    infoController.init();
})
