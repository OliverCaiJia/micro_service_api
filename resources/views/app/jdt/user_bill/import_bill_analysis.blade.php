<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <script src="/view/js/htmlrem.min.js"></script>
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/user_bill/bill_analysis.css">
    <title>图表图</title>
</head>

<body>
    <div class="btn"><span class="btn1 onSelect">负债分布</span> <span class="btn2">负债预估</span> </div>
    <div class="chartsBox">
        <div class="pieChart-box chart-box">
            <h3>{{ $data['month'] }}月负债分布（<i class="total_debts"></i>元）<span>24小时内更新</span></h3>
            <div id="pieChart"> </div>
            <div class="centerTitle" style="display:none"> <span>点击查看<br>负债比例</span></div>
        </div>
        <div class="areaChart-box chart-box" style="display:none">
            <h3>预估未来3个月负债情况（元）</h3>
            <div id="areaChart"></div>
            <ul class="side_line">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
    </div> <span id="sign" style="display:none">{{ $data['sign'] }}</span>
    <script src="/vendor/jquery.min.js"></script>
    <script src="/view/js/service/sha1.min.js"></script>
    <script src="/view/js/service/address.js"></script>
    <script src="/view/js/service/service.js"></script>
    <script src="/view/js/charts/echarts.common.min.js"></script>
    <script src="/view/js/charts/highcharts.js"></script>
    <script>


    </script>
    <script>
        var chartsController = {
            initView: function() {
                this.getData(1);
                this.getData(2);
                this.eventView();
            },
            getData: function(analysisType) {
                var _this = this;
                service.doAjaxRequest({
                    url: '/v1/users/bill/analysis',
                    type: 'GET',
                    data: {
                        analysisType: analysisType
                    }
                }, function(json) {
                    if (analysisType == 1) {
                        if (json.list != '') {
                            $('.total_debts').text(json.total_debts);
                            _this.pieData = json.list;
                            $('.centerTitle').show();
                            _this.pieChart();
                        } else {
                            $('#pieChart').html('<div class="pie-no-data"><div><div>暂无负债</div></div></div>');
                        }
                    } else if (analysisType == 2) {
                        _this.areaDataTime = json.bill_count_month;
                        _this.areaDataMoney = json.total_debts;
                    }
                });
            },
            eventView: function() {
                var _this = this,
                    title = $('.title').text();
                $('.btn').on('click', 'span', function() {
                    $('.chart-box').hide();
                    $(this).addClass('onSelect').siblings('span').removeClass('onSelect');
                    var idx = $(this).index();
                    if (idx == 0) {
                        $('.pieChart-box').show();
                    } else if (idx == 1) {
                        $('.areaChart-box').show();
                        _this.areaChart()
                    }
                })
            },
            pieChart: function(data) {
                var _this = this;
                var myChart = echarts.init(document.getElementById('pieChart'));
                var option = {
                    tooltip: {
                        show: false,
                    },
                    series: [{
                        name: '占比',
                        type: 'pie',
                        radius: ['46%', '70%'],
                        center: ['50%', '53%'],
                        hoverAnimation: false,
                        label: {
                            normal: {
                                formatter: '{b|{b}}',
                                rich: {
                                    b: {
                                        fontSize: 12,
                                    }
                                }
                            },
                            emphasis: {
                                show: true
                            }
                        },
                        data: _this.pieData
                    }]
                };
                myChart.setOption(option);
                myChart.on('click', function(params) {
                    $('.centerTitle').html('<i style="color:#666">' + params.data.debts + '</i><br><span style="color:' + params.color + '">' + params.percent + '%</span>');
                });
            },
            areaChart: function(data) {
                var _this = this;
                $('#areaChart').highcharts({
                    chart: {
                        zoomType: 'x',
                        marginLeft: 0,
                        marginRight: 0,
                    },
                    credits: {
                        enabled: false
                    },
                    legend: 'none',
                    plotOptions: {
                        series: {
                            states: {
                                hover: {
                                    lineWidth: 2
                                }
                            }
                        }
                    },
                    title: null,
                    xAxis: {
                        categories: _this.areaDataTime,
                        tickPixelInterval: 50,
                        tickLength: 0,
                        lineWidth: 0,
                        tickmarkPlacement: 'on',
                        gridLineColor: 'rgba(76,134,245,0.30)',
                        gridLineDashStyle: 'longdash',
                        gridLineWidth: 1,
                        tickPixelInterval: 100,
                        labels: {
                            step: 1
                        }
                    },
                    yAxis: {
                        visible: false,
                    },
                    tooltip: {
                        //                        enabled: false,
                        backgroundColor: '#4c86f5',
                        valueDecimals: 2, //                        followTouchMove: false,
                        formatter: function() {
                            var value = this.y;
                            if (Number.isInteger(value)) {
                                return value + '.00';
                            } else {
                                return value;
                            }
                        },
                        padding: 5,
                        style: {
                            color: '#fff',
                            fontSize: '11px'
                        }
                    },
                    series: [{
                        type: 'area',
                        data: _this.areaDataMoney,
                        lineWidth: 2,
                        color: '#4c86f5',
                        fillColor: 'rgba(76,134,245,0.10)',
                        zoneAxis: 'x',
                        zones: [{
                            value: _this.areaDataTime.length - 4
                        }, {
                            dashStyle: 'dot'
                        }],
                        marker: {
                            radius: 3,
                            symbol: 'circle',
                            lineWidth: 1,
                            lineColor: '#4c86f5',
                            fillColor: 'white',
                        }
                    }]
                });
                this.areaChartStyle();
            },
            areaChartStyle: function() {
                //                var _this = this;
                //                var width = this.areaDataTime.length * 1.01;
                //                $('#areaChart').css({
                //                    "width": width + "rem"
                //                });
                //                var hchartsBoxW = $('.chartsBox').width(),
                //                    chartW = $('#areaChart').width(),
                //                    initLeft = -(chartW - hchartsBoxW);
                //                var change_x = initLeft;
                //                $('#areaChart').css({
                //                    "left": change_x
                //                });
                var _this = this;
                var hchartsBoxW = $('.chartsBox').width(),
                    chartW = $('#areaChart').width(),
                    initLeft = -(chartW - hchartsBoxW);
                var change_x = initLeft;
                $('#areaChart').css({
                    "left": change_x
                });

                function Touch() {
                    var startX, moveX, distanceX;
                    $('.chartsBox').on('touchstart', function(e) {
                        startX = e.originalEvent.changedTouches[0].pageX;
                    }).on('touchmove', function(e) {
                        _this.changeMoveBool = true;
                        endX = e.originalEvent.changedTouches[0].pageX;
                        distanceX = endX - startX;
                        var moveChange = change_x + distanceX;
                        if (initLeft < moveChange && moveChange < 0) {
                            $('#areaChart').css({
                                "left": moveChange
                            });
                        } else if (moveChange > 0) {
                            $('#areaChart').css({
                                "left": 0
                            });
                        }
                    }).on('touchend', function(e) {
                        if (_this.changeMoveBool) {
                            _this.changeMoveBool = false;
                            change_x = change_x + distanceX;
                        }
                    })
                }
                Touch();
            }
        };
        $(function() {
            chartsController.initView();
        })

    </script>
</body>

</html>
