<!--当前定位城市-->
<!--
<div class="cityLocation">
    <div>当前定位城市</div>
    <div class="address">选择城市</div>
    <div class="reposition">重新定位</div>
</div>
-->
<!--主体-->
<div id="wrapper">
    <div class="iscroll">
        <div>
            <!--热门城市hotCity-->
            <div class="hotCity" id="hotCity">
                <h3>热门城市</h3>
                <div class="hotCitylist"></div>
            </div>
            <ul class="slider-content"></ul>
        </div>
    </div>
    <div class="fixedNav"></div>
    <div class="centerfixedNav"></div>
</div>
<div class="slider-nav"><span><a slt="hotCity"></a></span>
    <ol></ol>
</div>
<script>
    var cityListController = {
        init: function() {
            var that = this;
            //            that.userAddress = "";
            that.getCityList();
            //            that.doAddressView();
            that.doOperationView();
        },
        getCityList: function() {
            /*城市列表数据渲染*/
            $.ajax({
                url: api_sudaizhijia_host + "/v1/location/devices",
                //                url: "http://api.sudaizhijia.com/v1/location/devices",
                type: "GET",
                dataType: "json",
                data: {},
                success: function(json) {
                    if (json.code == 200 && json.error_code == 0) {
                        cityListController.doCityListView(json.data);
                    }
                }
            })
        },
        doOperationView: function() {
            var that = this;
            //重新定位按钮
            //            $('.reposition').on('click', function() { // $(".address").html('定位中...').removeClass('sureCity'); // that.doAddressView(); // });
            //点击城市筛选跳转
            $(document).on('click', '.cityList>li,.hotCitylist>span', function() {
                var val = $(this).text();
                $('.cityVal').val(val);
                $('#base_city').val(val);
                $('#city_cover_box').hide();
                dataController.checkBasicInfo();
            });
            //定位城市点击
            $('.address').on('click', function() {
                var val = $(this).text();
                if ($(this).hasClass('sureCity')) {
                    $('.cityVal').val(val);
                    $('#base_city').val(val);
                    $('#city_cover_box').hide();
                    dataController.checkBasicInfo();
                }
            });
        },
        /*页面定位*/
        doAddressView: function() {
            var that = this;
            $(".address").text('定位中...')
            var map, geolocation;
            //加载地图，调用浏览器定位服务
            map = new AMap.Map('', {
                resizeEnable: true
            });
            map.plugin('AMap.Geolocation', function() {
                geolocation = new AMap.Geolocation({
                    enableHighAccuracy: true,
                    timeout: 10000,
                    buttonPosition: 'RB'
                });
                map.addControl(geolocation);
                geolocation.getCurrentPosition();
                AMap.event.addListener(geolocation, 'complete', onComplete);
                AMap.event.addListener(geolocation, 'error', onError);
            });
            //解析定位结果
            function onComplete(data) {
                longitude = data.position.getLng();
                latitude = data.position.getLat();
                gpsPoint = new BMap.Point(longitude, latitude);
                BMap.Convertor.translate(gpsPoint, 0, translateCallback);
            }
            translateCallback = function(point) {
                baiduPoint = point;
                var geoc = new BMap.Geocoder();
                geoc.getLocation(baiduPoint, getCityByBaiduCoordinate);
            }

            function getCityByBaiduCoordinate(rs) {
                baiduAddress = rs.addressComponents;
                that.userAddress = baiduAddress.city + baiduAddress.district + baiduAddress.street + baiduAddress.streetNumber;
                var userCity = baiduAddress.city;
                $(".address").html(userCity).addClass('sureCity');
                $(".cityVal").val(userCity);
                $('#base_city').val(userCity);
            }
            //解析定位错误信息
            function onError(data) {
                $(".address").html('定位失败');
                that.apiAddressEvent();
            }
        },
        /*接口定位*/
        apiAddressEvent: function() {
            $.ajax({
                url: api_sudaizhijia_host + "/v1/partner/tools/ip/address",
                //                url: "http://api.sudaizhijia.com/v1/partner/tools/ip/address",
                type: "GET",
                dataType: "json",
                success: function(json) {
                    var data = json.data;
                    if (json.code == 200 && json.error_code == 0) {
                        $(".address").html(data.city).addClass('sureCity');
                        $('.cityVal').val(data.city);
                        $('#base_city').val(data.city);
                    }
                }
            })
        },
        /*城市列表数据渲染*/
        doCityListView: function(json) {
            var that = this;
            var data = json;
            var hotCity = "",
                cityList = "";
            $.each(data.hotCity, function(i, b) {
                hotCity += "<span id=" + b.id + " class=" + b.id + ">" + b.name + "</span>";
            })
            $(".hotCitylist").html(hotCity);
            $.each(data.list, function(i, b) {
                cityList += "<li id=" + b.initial + "><h3>" + b.initial + "</h3><ul class='cityList'>";
                $.each(b.citys, function(a, i) {
                    cityList += "<li id=" + i.id + "  class=" + i.id + ">" + i.name + "</li>";
                });
                cityList += "</ul></li>";
            })
            $(".slider-content").html(cityList);
        }
    };
    $(function() {
        cityListController.init();
    })

</script>
