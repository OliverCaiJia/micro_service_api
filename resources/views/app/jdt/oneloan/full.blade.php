<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/view/css/resets.css">
    <link rel="stylesheet" href="/view/css/oneloan/full.css"/>
</head>
<title></title>
<body>
<div id="full_box">
    <section id="full_top">
        <div class="title">
            <span class="go_back"></span>
            <span>一键选贷款</span>
        </div>
        <div class="schedule">
            <div class="left">
                <p class="text1">仅需1步</p>
                <p class="text2">就可以申请您的专属高通过率产品</p>
            </div>
            <div class="right">
                <input class="knob" data-angleOffset=-110 data-angleArc=220 data-thickness=".1" data-fgColor="#fff11a"
                       value="{{ $progress['count'] or 0 }}" data-width="100%" data-height="100%" data-linecap=round
                       data-bgColor="#a2adb0"
                       readonly>
                <div class="knob_text">
                    <p class="percent">
                        <span id="percentNum">{{ $progress['count'] or 0}}</span>%</p>
                    <p>完善真实信息<br/>提升贷款成功率</p>
                </div>
            </div>
            <div style='clear: both;display: none;'></div>
        </div>
    </section>
    <div class="main">
        <section id="base_info" class="info_mod">
            <div class="info_title">
                <img src="/view/img/oneloan/full_asset_icon.png" alt=""/>
                <span class="info_name">基本信息</span>
                @if(isset($progress['basicSign']) && $progress['basicSign'] == 1)
                    <span class="success_percent" isComplete='1'><span style="color:#fe5c0d;">已完成</span></span>
                @else
                    <span class="success_percent" isComplete='0'>完善基本信息，下款成功率+20%</span>
                @endif
                <span class="stretch_btn"></span>
            </div>
            <div class="inp_area">
                <div>
                    <span class="inp_name">姓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名</span>
                    <input type="text" class="name_inp" id="base_name" placeholder="输入你的尊姓大名"
                           value="{{ $data['name'] or '' }}"/>
                </div>
                <div>
                    <span class="inp_name">身份证号</span>
                    <input type="number" class="idcard_inp" id="base_card" placeholder="输入你的身份证号"
                           value="{{ $data['certificate_no'] or '' }}"/>
                </div>
                <div>
                    <span class="inp_name">城&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;市</span>
                    <input class="city_inp" id="base_city" placeholder="现居住城市" readonly="readonly"
                           value="{{ $data['city'] or '' }}"/>
                </div>
            </div>
        </section>
        <section id="job_info" class="info_mod">
            <div class="info_title">
                <img src="/view/img/oneloan/full_job_icon.png" alt=""/>
                <span class="info_name">工作信息</span>
                @if(isset($progress['workSign']) && $progress['workSign'] == 1)
                    <span class="success_percent" isComplete='1'><span style="color:#fe5c0d;">已完成</span></span>
                @else
                    <span class="success_percent" isComplete='0'>完善工作信息，下款成功率+40%</span>
                @endif
                <span class="stretch_btn"></span>
            </div>
            <div class="job_sel_box">
                <div class="sel_area">
                    <span class="sel_name">你的职业</span>
                    <div class="sel_option" id='occupation'>
                        <span @if(isset($data['occupation']) && $data['occupation'] == '001')class="onSelect" @endif data-val='001'>上班族</span>
                        <span @if(isset($data['occupation']) && $data['occupation'] == '002')class="onSelect" @endif data-val='002'>公务员</span>
                        <span @if(isset($data['occupation']) && $data['occupation'] == '003')class="onSelect" @endif data-val='003'>私营业主</span>
                    </div>
                </div>
                <div class="job_cover_box" @isset($data['occupation']) style="display: block" @endisset>
                    <div class="office_workers sel_section" id="office_workers"
                         @if(isset($data['occupation']) && $data['occupation'] == '001')style="display: block" @endif>
                        <div class="sel_area salary_extend">
                            <span class="sel_name">工资发放</span>
                            <div class="sel_option">
                                <span @if(isset($data['salary_extend']) && $data['salary_extend'] == '001')class="onSelect" @endif data-val='001'>银行转账</span>
                                <span @if(isset($data['salary_extend']) && $data['salary_extend'] == '002')class="onSelect" @endif data-val='002'>现金发放</span>
                            </div>
                        </div>
                        <div class="sel_area salary">
                            <span class="sel_name">月收入</span>
                            <div class="sel_option">
                                <span @if(isset($data['salary']) && $data['salary'] == '101')class="onSelect" @endif data-val='101'>2千以下</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '102')class="onSelect" @endif data-val='102'>2千-3千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '103')class="onSelect" @endif data-val='103'>3千-4千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '104')class="onSelect" @endif data-val='104'>4千-5千</span>
                                <span @if( isset($data['salary']) && $data['salary'] == '105')class="onSelect" @endif data-val='105'>5千-1万</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '106')class="onSelect" @endif data-val='106'>1万以上</span>
                            </div>
                        </div>
                        <div class="sel_area work_hours">
                            <span class="sel_name">工作时间</span>
                            <div class="sel_option">
                                <span @if(isset($data['work_hours']) && $data['work_hours'] == '001')class="onSelect" @endif data-val='001'>6个月内</span>
                                <span @if(isset($data['work_hours']) &&$data['work_hours'] == '002')class="onSelect" @endif data-val='002'>6-12个月</span>
                                <span @if(isset($data['work_hours']) &&$data['work_hours'] == '003')class="onSelect" @endif data-val='003'>1年以上</span>
                            </div>
                        </div>
                        <div class="sel_area accumulation_fund">
                            <span class="sel_name">公积金</span>
                            <div class="sel_option">
                                <span @if(isset($data['accumulation_fund']) && $data['accumulation_fund'] == '001')class="onSelect" @endif data-val='000'>无公积金</span>
                                <span @if(isset($data['accumulation_fund']) && $data['accumulation_fund'] == '002')class="onSelect" @endif data-val='001'>一年以内</span>
                                <span @if(isset($data['accumulation_fund']) && $data['accumulation_fund'] == '003')class="onSelect" @endif data-val='002'>一年以上</span>
                            </div>
                        </div>
                        <div class="sel_area social_security">
                            <span class="sel_name">社保</span>
                            <div class="sel_option">
                                <span @if(isset($data['social_security']) && $data['social_security'] == '0')class="onSelect" @endif data-val='0'>无社保</span>
                                <span @if(isset($data['social_security']) && $data['social_security'] == '1')class="onSelect" @endif data-val='1'>有社保</span>
                            </div>
                        </div>
                    </div>
                    <div class="servant sel_section" id="servant">
                        <div class="sel_area salary">
                            <span class="sel_name">月收入</span>
                            <div class="sel_option">
                                <span @if(isset($data['salary']) && $data['salary'] == '101')class="onSelect" @endif data-val='101'>2千以下</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '102')class="onSelect" @endif data-val='102'>2千-3千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '103')class="onSelect" @endif data-val='103'>3千-4千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '104')class="onSelect" @endif data-val='104'>4千-5千</span>
                                <span @if( isset($data['salary']) && $data['salary'] == '105')class="onSelect" @endif data-val='105'>5千-1万</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '106')class="onSelect" @endif data-val='106'>1万以上</span>
                            </div>
                        </div>
                        <div class="sel_area work_hours">
                            <span class="sel_name">工作时间</span>
                            <div class="sel_option">
                                <span @if(isset($data['work_hours']) && $data['work_hours'] == '001')class="onSelect" @endif data-val='001'>6个月内</span>
                                <span @if(isset($data['work_hours']) && $data['work_hours'] == '002')class="onSelect" @endif data-val='002'>6-12个月</span>
                                <span @if(isset($data['work_hours']) && $data['work_hours'] == '003')class="onSelect" @endif data-val='003'>1年以上</span>
                            </div>
                        </div>
                        <div class="sel_area accumulation_fund">
                            <span class="sel_name">公积金</span>
                            <div class="sel_option">
                                <span @if(isset($data['accumulation_fund']) && $data['accumulation_fund'] == '001')class="onSelect" @endif data-val='000'>无公积金</span>
                                <span @if(isset($data['accumulation_fund']) &&$data['accumulation_fund'] == '002')class="onSelect" @endif data-val='001'>一年以内</span>
                                <span @if(isset($data['accumulation_fund']) &&$data['accumulation_fund'] == '003')class="onSelect" @endif data-val='002'>一年以上</span>
                            </div>
                        </div>
                    </div>
                    <div class="private_business_owner sel_section" id="private_business_owner">
                        <div class="sel_area salary">
                            <span class="sel_name">月收入</span>
                            <div class="sel_option">
                                <span @if(isset($data['salary']) && $data['salary'] == '101')class="onSelect" @endif data-val='101'>2千以下</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '102')class="onSelect" @endif data-val='102'>2千-3千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '103')class="onSelect" @endif data-val='103'>3千-4千</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '104')class="onSelect" @endif data-val='104'>4千-5千</span>
                                <span @if( isset($data['salary']) && $data['salary'] == '105')class="onSelect" @endif data-val='105'>5千-1万</span>
                                <span @if(isset($data['salary']) && $data['salary'] == '106')class="onSelect" @endif data-val='106'>1万以上</span>
                            </div>
                        </div>
                        <div class="sel_area business_licence">
                            <span class="sel_name">营业执照</span>
                            <div class="sel_option">
                                <span @if(isset($data['business_licence']) && $data['business_licence'] == '001')class="onSelect" @endif data-val='001'>1年以内</span>
                                <span @if(isset($data['business_licence']) && $data['business_licence'] == '002')class="onSelect" @endif data-val='002'>1年以上</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="asset_info" class="info_mod">
            <div class="info_title">
                <img src="/view/img/oneloan/full_asset_icon.png" alt=""/>
                <span class="info_name">资产信息</span>
                @if(isset($progress['propertySign']) && $progress['propertySign'] == 1)
                    <span class="success_percent" isComplete='1'><span style="color:#fe5c0d;">已完成</span></span>
                @else
                    <span class="success_percent" isComplete='0'>完善资产信息，下款成功率+25%</span>
                @endif
                <span class="stretch_btn"></span>
            </div>
            <div class="asset_sel_box">
                <div class="sel_area" id="has_insurance">
                    <span class="sel_name">寿险保单</span>
                    <div class="sel_option">
                        <span @if(isset($data['has_insurance']) && $data['has_insurance'] == '0')class="onSelect" @endif data-val='0'>没有</span>
                        <span @if(isset($data['has_insurance']) && $data['has_insurance'] == '1')class="onSelect" @endif data-val='1'>2400以下</span>
                        <span @if(isset($data['has_insurance']) && $data['has_insurance'] == '2')class="onSelect" @endif data-val='2'>2400以上</span>
                    </div>
                </div>
                <div class="sel_area" id="house_info">
                    <span class="sel_name">名下房产</span>
                    <div class="sel_option">
                        <span @if( isset($data['house_info']) && $data['house_info'] == '000')class="onSelect" @endif data-val='000'>无房</span>
                        <span @if(isset($data['house_info']) && $data['house_info'] == '002')class="onSelect" @endif data-val='002'>全款房</span>
                        <span @if(isset($data['house_info']) && $data['house_info'] == '001')class="onSelect" @endif data-val='001'>按揭房</span>
                    </div>
                </div>
                <div class="sel_area" id="car_info">
                    <span class="sel_name">名下汽车</span>
                    <div class="sel_option">
                        <span @if(isset($data['car_info']) && $data['car_info'] == '000')class="onSelect" @endif data-val='000'>无车</span>
                        <span @if(isset($data['car_info']) && $data['car_info'] == '002')class="onSelect" @endif data-val='002'>全款车</span>
                        <span @if(isset($data['car_info']) && $data['car_info'] == '001')class="onSelect" @endif data-val='001'>按揭车</span>
                    </div>
                </div>
            </div>
        </section>
        <section id="credit_info" class="info_mod">
            <div class="info_title">
                <img src="/view/img/oneloan/full_credit_icon.png" alt=""/>
                <span class="info_name">信用信息</span>
                @if(isset($progress['creditSign']) && $progress['creditSign'] == 1)
                    <span class="success_percent" isComplete='1'><span style="color:#fe5c0d;">已完成</span></span>
                @else
                    <span class="success_percent" isComplete='0'>完善信用信息，下款成功率+10%</span>
                @endif
                <span class="stretch_btn"></span>
            </div>
            <div class="credit_sel_box">
                <div class="sel_area" id="has_creditcard">
                    <span class="sel_name">信用卡</span>
                    <div class="sel_option">
                        <span @if(isset($data['has_creditcard']) && $data['has_creditcard'] == '0')class="onSelect" @endif data-val='0'>无</span>
                        <span @if(isset($data['has_creditcard']) && $data['has_creditcard'] == '1')class="onSelect" @endif data-val='1'>有</span>
                    </div>
                </div>
                <div class="sel_area" id="is_micro">
                    <span class="sel_name">微粒贷</span>
                    <div class="sel_option">
                        <span @if(isset($data['is_micro']) && $data['is_micro'] == '0')class="onSelect" @endif data-val='0'>无</span>
                        <span @if(isset($data['is_micro']) && $data['is_micro'] == '1')class="onSelect" @endif data-val='1'>有</span>
                    </div>
                </div>
            </div>
        </section>
        <p class="full_copyright">版权所有&copy;北京智借网络科技有限公司</p>
    </div>
    <section class="" id="full_loans">
        <button class="loans_btn">立即贷款</button>
    </section>
</div>
<div id="city_cover_box"></div>
<script src="/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/view/js/htmlrem.min.js"></script>
<script src="/view/js/oneloan/jquery.knob.js"></script>
<script src="/vendor/jquery/jquery.cookie-1.4.1.min.js"></script>
<script src="/view/js/service/sha1.min.js"></script>
<script src="/view/js/service/service.js"></script>
<script src="/view/js/oneloan/idcardValidate.js"></script>
<script src="/view/js/oneloan/global.js"></script>
<script src="/view/js/oneloan/full.js"></script>
<script type="text/javascript" src="//webapi.amap.com/maps?v=1.3"></script>
<script type="text/javascript" src="//api.map.baidu.com/api?v=1.3"></script>
<script type="text/javascript" src="//developer.baidu.com/map/jsdemo/demo/convertor.js"></script>
</body>
</html>