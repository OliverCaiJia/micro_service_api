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

    </div>
    <div class="city_cover_box">
        <!--当前定位城市-->
        <div class="cityLocation">
            <div>当前定位城市</div>
            <div class="address">选择城市</div>
            <div class="reposition">重新定位</div>
        </div>
        <!--主体-->
        <div id="wrapper">
            <div class="iscroll">
                <div>
                    <!--热门城市hotCity-->
                    <div class="hotCity" id="hotCity">
                        <h3>热门城市</h3>
                        <div class="hotCitylist"> </div>
                    </div>
                    <ul class="slider-content"> </ul>
                </div>
            </div>
            <div class="fixedNav"></div>
            <div class="centerfixedNav"></div>
        </div>
        <div class="slider-nav"> <span><a slt="hotCity"></a></span>
            <ol> </ol>
        </div>
    </div>

    <script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script src="/view/js/oneloan/idcardValidate.js"></script>
    <script type="text/javascript" src="//webapi.amap.com/maps?v=1.3"></script>
    <script type="text/javascript" src="//api.map.baidu.com/api?v=1.3"></script>
    <script type="text/javascript" src="//developer.baidu.com/map/jsdemo/demo/convertor.js"></script>
    <script src="/view/js/oneloan/global.js"></script>
    <script>
        $.get(dev_sudaizhijia_host + "/view/oneloan/basic", function(result) {
            $('#container').html(result);
        });

    </script>
</body>

</html>
