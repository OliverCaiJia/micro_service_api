<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-Type" content="text/html" charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/user_bill/import_detail.css">
    <title>账单明细</title>
</head>

<body>
    <div class="container">
        <!--
    <section>
        <div class="click-area"><i class="icon-arrow"></i>
            <p>11月账单<span>2017</span>
                <br><small>2017/10/17-2017/11/16</small> </p><span>22267.02</span></div>
        <ul><div>
            <li>利息
                <br><i>11/16</i><span class="add">+334.67</span></li>
            <li>违约金
                <br><i>11/16</i><span class="add">+334.67</span></li>
            <li>支付宝（快捷支付）
                <br><i>11/16</i><span class="add">+334.67</span></li>
            <li>跨行自助转账还款跨行自助还款
                <br><i>11/16</i><span class="subtract">-2500.00</span></li>
            <li>银联ATM取现
                <br><i>11/16</i><span class="add">+334.67</span></li></div>
            <p class="more-btn">更多账单详情</p>
        </ul>
    </section>
-->
    </div>
    <div id="PullUp">加载中...</div> <span id="creditcardId" style="display:none">{{ $data['creditcardId'] }}</span> <span id="sign" style="display:none">{{ $data['sign'] }}</span>
    <script src="/vendor/jquery.min.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/address.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script>
        var billDetailed = {
            initView: function() {
                this.pageSizeList = 1;
                this.pageSizeDetail = 1;
                this.creditcardId = $('#creditcardId').html();
                this.eventView();
                this.dataRender();
                this.scrollLoad();
            },
            eventView: function() {
                var _this = this;
                $(document).on('click', '.click-area', function() {
                    if ($(this).data('import') == 1) {
                        var billId = $(this).parents('section').attr('id'),
                            $this = $(this),
                            $listBox = $this.next('ul').find('div'),
                            $iconArrow = $this.find('.icon-arrow');

                        function arrowStyle() {
                            $('.icon-arrow').removeClass('icon-arrow-up');
                            $this.find('.icon-arrow').addClass('icon-arrow-up');
                            $('ul').slideUp(200);
                            $this.next('ul').slideDown(200);
                        }
                        if ($iconArrow.hasClass('icon-arrow-up')) {
                            $iconArrow.removeClass('icon-arrow-up');
                            $this.next('ul').slideUp();
                        } else if ($listBox.html() == '') {
                            _this.pageSizeDetail = 1;
                            service.doAjaxRequest({
                                url: '/v1/users/bill/banks/creditcard/detail',
                                type: 'GET',
                                data: {
                                    billId: billId,
                                    pageSize: _this.pageSizeDetail,
                                    pageNum: '20'
                                }
                            }, function(json) {
                                var html_detail = '';
                                $.each(json.list, function(i, b) {
                                    html_detail += '<li><h3>' + b.description + '</h3><p>' + b.trans_date + '</p>';
                                    if (b.amount_money_sign == 1) {
                                        html_detail += '<span class="add">' + b.amount_money + '</span>';
                                    } else {
                                        html_detail += '<span class="subtract">' + b.amount_money + '</span>';
                                    }
                                    html_detail += '</li>';
                                });
                                $listBox.html(html_detail);
                                if (json.pageCount > 1) {
                                    $this.next('ul').append('<p class="more-btn more-btn-sure">更多账单详情</p>');
                                }
                                arrowStyle();
                            })
                        } else {
                            arrowStyle()
                        }
                    }
                });
                $(document).on('click', '.more-btn-sure', function() {
                    var $this = $(this);
                    var billId = $this.parents('section').attr('id');
                    _this.pageSizeDetail++;
                    service.doAjaxRequest({
                        url: '/v1/users/bill/banks/creditcard/detail',
                        type: 'GET',
                        data: {
                            billId: billId,
                            pageSize: _this.pageSizeDetail,
                            pageNum: '20'
                        }
                    }, function(json) {
                        var html_detail = '';
                        $.each(json.list, function(i, b) {
                            html_detail += '<li><h3>' + b.description + '</h3><p>' + b.trans_date + '</p>';
                            if (b.amount_money_sign == 1) {
                                html_detail += '<span class="add">' + b.amount_money + '</span>';
                            } else {
                                html_detail += '<span class="subtract">' + b.amount_money + '</span>';
                            }
                            html_detail += '</li>';
                        });
                        $this.prev('div').append(html_detail);
                        _this.pageCountDetail = json.pageCount;
                        if (_this.pageSizeDetail >= _this.pageCountDetail) {
                            $('.more-btn').html('已加载全部').removeClass('more-btn-sure')
                        }
                    });
                });
            },
            dataRender: function() {
                var _this = this;
                service.doAjaxRequest({
                    url: '/v1/users/bill/banks/creditcard/bills',
                    type: 'GET',
                    data: {
                        creditcardId: _this.creditcardId,
                        pageSize: _this.pageSizeList,
                        pageNum: '10'
                    }
                }, function(json) {
                    var html_list = '';
                    $.each(json.list, function(i, b) {
                        html_list += '<section id=' + b.id + '>' + '<div class="click-area" data-import=' + b.is_import + '>';
                        if (b.is_import == 1) {
                            html_list += '<i class="icon-arrow"></i>';
                        }
                        html_list += '<p>' + b.bill_month + '月<span></span>' + '<br><small>' + b.bill_cycle + '</small> </p>' + '<span>' + b.bill_money + '</span>' + '</div>' + '<ul><div></div></ul>' + '</section>';
                    });
                    $('.container').append(html_list);
                    _this.pageSizeList++;
                    _this.pageCountList = json.pageCount;
                    if (_this.pageCountList > 1) {
                        $('#PullUp').show();
                    }
                });
            },
            scrollLoad: function() {
                var _this = this;
                $(window).scroll(function() {
                    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
                        if (_this.pageSizeList <= _this.pageCountList) {
                            setTimeout(function() {
                                _this.dataRender();
                            }, 300)
                        } else {
                            $('#PullUp').html('已加载全部');
                        }
                    };
                });
            }
        }
        $(function() {
            billDetailed.initView();
        });

    </script>
</body>

</html>
