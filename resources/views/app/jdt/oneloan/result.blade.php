<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/oneloan/result.css">

</head>

<body>
<div class="result-page">
    <!--       有匹配结果-->
@if(empty($result['list']))
    <!--无匹配结果-->
        <div class="result-no">
            <span class="back_btn"></span>
            <h3>我的专属贷款推荐</h3>
            <div class="no_icon">
                很抱歉没有和你匹配的贷款产品
                {{--@include('app.sudaizhijia.errors.error_static',['error'=>'很抱歉没有和你匹配的贷款产品']);--}}
            </div>
        </div>
    @else
        <div class="result-yes" style="display:block">
            <span class="back_btn"></span>
            <h3>您的贷款匹配方案如下</h3>
            <section>
                <p>*3天内注意接听电话，以免与最适合你的贷款产品擦肩而过</p>
                <div id="products-list">
                    <dl>
                        @foreach($result['list'] as $key=>$item)
                            <dt><img src="{{ $item['logo'] }}" alt=""></dt>
                            <dd>{{ $item['name'] }}</dd>
                        @endforeach
                    </dl>
                </div>
            </section>
        </div>

    @endif

    <div class="result-center">
        @if(isset($result['content']) && !empty($result['content']))
            <p>{{ $result['content'] }}</p>
        @endif
        <p>保险合作机构：中国平安 泰康人寿 中英人寿 大都会人寿承保</p>
    </div>
    <div class="result-bottom">
        @if(isset($result['list']))
            <h3>根据您的综合评定，为您推荐以下贷款组合，申请3个以上成功率 提高2倍。</h3>
        @else
        <!--无匹配结果-->
            <h3>和您资质相似的人都申请了以下贷款产品</h3>
        @endif

        <div id="productList">
            @if(isset($product['list']) && !empty($product['list']))
                @foreach($product['list'] as $key=>$item)
                    <div class="product_list" data-productId="{{ $item['platform_product_id'] }}">
                        <dl>
                            <dt><img src='{{ $item['product_logo'] }}'></dt>
                            <dd>
                                <h3>{{ $item['platform_product_name'] }}
                                    <span class="bubble">{{ $item['tag_name'] or '' }}</span></h3>
                                <p>{{ $item['product_introduct'] }}</p>
                            </dd>
                            <span>{{ $item['total_today_count'] }}人今日申请</span>
                        </dl>
                        <div class="product_list_btm">
                            <div class="product_list_btm_left">
                                <p>{{ $item['quota'] }}</p>
                                <p>额度</p>
                            </div>
                            <div class="product_list_btm_center">
                                <p>放款速度：{{ $item['loan_speed'] }}</p>
                                <p>利率：{{ $item['interest_rate'] }}</p>
                            </div>
                            <div class="product_list_btm_right"><span class="apply" data-platformId=''
                                                                      data-productId=''>申请</span></div>
                        </div>
                        <i class="vip_icon"></i> <i class="choose_icon"></i>
                    </div>
                @endforeach
            @endif
        </div>
        <h6 class="bottom-p">版权所有 <span>©</span> 北京智借网络科技有限公司</h6>
        <div id="more-products-btn">更多高通过率贷款产品</div>
    </div>
</div>
<script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/oneloan/global.js"></script>
<script src="/view/js/oneloan/result.js"></script>
</body>

</html>
