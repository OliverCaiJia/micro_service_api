<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/oneloan/index.css">

</head>

<body>
    <div class="container" id="container">
        <div id="basic-page">
            <div class="banner">
                <span class="back_btn"></span>
            </div>
            <div class="main">
                <div id="basic-inducement">
                    <ul id="inducement-ul">
                        <li>
                            <p>今日已为<span class="applyNum"></span>人成功放款</p>
                            <p>恭喜<span class="random_mobile"></span>在<span>鸭梨贷</span>成功借到<i class="random_money"></i>元</p>
                        </li>
                        <li>
                            <p>今日已为<span class="applyNum"></span>人成功放款</p>
                            <p>恭喜<span class="random_mobile"></span>在<span>鸭梨贷</span>成功借到<i class="random_money"></i>元</p>
                        </li>
                    </ul>
                </div>
                <div class="center">

                    <h3>您的贷款金额（元）</h3>
                    <div id="money-box"><input type="number" placeholder="500000" id="basic-money" value="{{ $data['money'] or '' }}"><label for="basic-money"></label></div>
                    <section id="basic-data">
                        <div><input type="text" placeholder="输入你的尊姓大名" id="basic-name" value="{{ $data['name'] or ''}}"></div>
                        <div><input type="text" placeholder="输入你的身份证号" id="basic-idcard" value="{{ $data['certificate_no'] or '' }}"></div>
                        <div><input type="text" placeholder="现居住城市" readonly id="basic-city" class="cityVal" value="{{ $data['city'] or '' }}"></div>
                    </section>
                    <div id="basic-submit">一键选贷款</div>
                    <p class="agreement"><i class="onSelected" id="agreement_icon" data-val='1'></i>同意<a id="agreement_btn">《用户协议》</a></p>
                </div>
                <div class="question">
                    <p style="height:.3rem"></p>
                    <h3>为什么要定制贷款 </h3>
                    <p>通过大数据分析，由风控系统为你量身挑选通过率最高的平台，不仅告别盲目申请浪费的宝贵时间，还有优化个个人信用，提高了日后贷款的成功率 </p>
                    <h3>如何定制贷款</h3>
                    <p>你只需要3分钟填写需要借款的金额和期限，完善身份信息（风控审核需要），系统精准快速定制匹配符合你的贷款，2小时后即可贷款成功</p>
                </div>
            </div>
            <footer>版权所有<span>©</span>北京智借网络科技有限公司</footer>
        </div>
        <div id="full-page"></div>
        <div id="result-page"></div>
    </div>
    <div id="agreement-page"></div>
    <div id="city_cover_box"></div>
    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script src="/view/js/oneloan/idcardValidate.js"></script>
    <!--    <script type="text/javascript" src="//webapi.amap.com/maps?v=1.3"></script>-->
    <!--    <script type="text/javascript" src="//api.map.baidu.com/api?v=1.3"></script>-->
    <!--    <script type="text/javascript" src="//developer.baidu.com/map/jsdemo/demo/convertor.js"></script>-->
    <script src="/view/js/oneloan/global.js"></script>
    <script src="/view/js/oneloan/basic.js"></script>
</body>

</html>
