<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-Type" content="text/html" charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/user_bill/import_result.css">
    <title>导入结果</title>
</head>

<body>
    <div class="container">
        <h5>共找到{{ $data['banksTotal'] }}家银行{{ $data['newBillCounts'] }}封账单<span>{{ $data['importType'] }}</span></h5>
        <div class="content"> @if($data['newBillCounts'] > 0) @foreach($data['notBeyonds'] as $value)
            <div class="bill-list">
                <dl> <dt><img src="{{ $value['bank_logo'] }}" alt=""></dt>
                    <dd>
                        <h3>{{ $value['bank_short_name'] }}<i>{{ $value['bank_credit_card_num'] }}</i><span>{{ $value['bank_new_bills_count'] }}
                                    封新账单</span></h3>
                        <p>{{ $value['bank_name_on_card'] }}<span class="icon-arrow-down"></span></p>
                    </dd>
                </dl> @if($value['bills'])
                <ul class="bill-list-content"><i class="icon-arrow-up"></i> @foreach($value['bills'] as $item)
                    <li>{{ $item['bank_bill_time'] }}人民币账单¥{{ $item['bill_money'] }} <i></i> </li> @endforeach </ul> @endif </div> @endforeach @endif @if($data['beyondCounts'] > 0)
            <h6>信用卡超出15张限制，以下{{ $data['beyondCounts'] }}张信用卡账单未能导入，您可以在<span>“账单管理”中删除不常用的信用卡。</span></h6> @endif
            <div class="bill-list more"> @if($data['beyonds']) @foreach($data['beyonds'] as $val)
                <dl> <dt><img src="{{ $val['bank_logo'] }}" alt=""></dt>
                    <dd>
                        <h3>{{ $val['bank_short_name'] }}<i>{{ $val['bank_credit_card_num'] }}</i><span>{{ $val['bank_new_bills_count'] }}
                                封新账单</span></h3>
                        <p>{{ $val['bank_name_on_card'] }}</p>
                    </dd>
                </dl> @endforeach @endif </div> @if(!empty($data['simple_bill_num']))
            <p class="bottom-prompt">您有{{ $data['simple_bill_num'] }}份简版账单，建议网银导入</p> @endif </div>
    </div>
    <script src="/vendor/jquery.min.js"></script>
    <script>
        $(function () {
            //箭头-展开
            $('.icon-arrow-down').on('click', function (e) {
                e.stopPropagation();
                $(this).parents('.bill-list').find('.bill-list-content').slideDown(200, function () {
                    var $li = $(this).parents('.bill-list').find('li');
                    $.each($li, function () {
                        var height = $(this).height();
                        $(this).find('i').css({
                            height: height + 'px'
                        })
                    });
                });
                $(this).hide();
            });
            //箭头-收起
            $('.icon-arrow-up').on('click', function (e) {
                e.stopPropagation();
                $(this).parent('.bill-list-content').slideUp(200, function () {
                    $(this).parents('.bill-list').find('.icon-arrow-down').show();
                });
            });
            //APP点击交互
            $('.bill-list').on('click', function (e) {
                try {
                    window.webkit.messageHandlers.launchBillCreditCard.postMessage({});
                }
                catch (e) {
                    console.log(e);
                }
                try {
                    window.mobileCallback.launchBillCreditCard();
                }
                catch (e) {
                    console.log(e);
                }
            })
        });
    </script>
</body>

</html>