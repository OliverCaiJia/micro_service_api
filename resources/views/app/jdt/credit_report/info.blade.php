@include('app.sudaizhijia.layouts.header');
<body>
<div id="container">
    <header id="header" class="col-xs-12 col-sm-12"><span class="backBtn" onclick="infoController.backBtn()"></span>
        <p class="text-center">信用报告</p>
    </header>
    <!-- /header -->
    <div class="content_top col-xs-12 col-sm-12">
        <section>
            <div class="left"><img src="" alt="" class="status_img" data-value="{{ $info['grade']  or ''}}" id="grade">
            </div>
            <div class="right">
                <p>编号：{{ $info['serial_num'] }}</p>
                <p>{{ $info['update_date'] or \App\Constants\UserReportConstant::REPORT_DEFAULT }}</p>
                <p style="margin-top:.1rem">综合评分：<span id="score">{{ $info['score'] or '' }}</span></p>
                <p>总贷款金额：<span>{{ $info['loan_money'] or '' }}</span></p>
            </div>
        </section>
        <div class="mature-progress">
            <div class="mature-progress-box v0" id="mamture_progress">
                <dl>
                    <dt style="background:#24c08e">0</dt>
                    <dd><span class="member-ico v0"></span></dd>
                </dl>
                <dl>
                    <dt>180</dt>
                    <dd><span class="member-ico v0"></span>极低</dd>
                </dl>
                <dl>
                    <dt>360</dt>
                    <dd><span class="member-ico v1"></span>低</dd>
                </dl>
                <dl>
                    <dt>540</dt>
                    <dd><span class="member-ico v2"></span>良好</dd>
                </dl>
                <dl>
                    <dt>720</dt>
                    <dd><span class="member-ico v3"></span>优秀</dd>
                </dl>
                <dl>
                    <dt>900</dt>
                    <dd><span class="member-ico v4"></span>极好</dd>
                </dl>
                <div class="progress-box" id="progress_content"><i class="progress-box-style"></i></div>
            </div>
        </div>
    </div>
    <div class="content_bottom"> {{--身份解析--}}
        <section class="">
            <h3>身份解析</h3> @if(!empty($info['identity']))
                <p>姓名：{{ empty($info['identity']['name']) ? '未知' : $info['identity']['name'] }}
                    <span>性别：{{ empty($info['identity']['gender']) ? '未知' : $info['identity']['gender'] }}</span></p>
                <p>年龄：{{ empty($info['identity']['age']) ? '未知' : $info['identity']['age'] }}</p>
                <p>身份证：{{ empty($info['identity']['idcard']) ? '未知' : $info['identity']['idcard'] }}</p>
                <p>
                    籍贯：{{ empty($info['identity']['idcard_location']) ? '未知' : $info['identity']['idcard_location'] }}</p>
                <p>手机号：{{ empty($info['identity']['mobile']) ? '未知' : $info['identity']['mobile'] }}</p>
                <p>
                    手机归属地：{{ empty($info['identity']['mobile_location']) ? '未知' : $info['identity']['mobile_location'] }}</p>
                <p>手机运营商：{{ empty($info['identity']['carrier']) ? '未知' : $info['identity']['carrier'] }}</p> @endif
        </section> {{--注册信息--}}
        <section class="section_two">
            <h3>注册信息</h3>
            @if(!empty($info['register']))
                <p>
                    注册APP总数：{{ empty($info['register']['count']) ? '未知' : $info['register']['count']}}
                </p>
                @foreach($info['register']['list'] as $item)
                    <p>{{ empty($item['org_type']) ? '未知' : $item['org_type'] }}
                        : {{ empty($item['loan_cnt_180d']) ? '未知' : $item['loan_cnt_180d'] }}
                    </p>
                @endforeach
            @else <p>{{ '未查询到您在其他平台的注册信息' }}</p>
            @endif
        </section> {{--机构查询历史--}}
        <section class="section_three">
            <h3>机构查询历史</h3>
            @if(isset($info['history_loan_cnt']))
                <p>近15天内贷款查询次数：{{ $info['history_loan_cnt']['loan_cnt_15d'] }}</p>
                <p>近1个月内贷款查询次数：{{ $info['history_loan_cnt']['loan_cnt_30d'] }}</p>
                <p>近3个月内贷款查询次数：{{ $info['history_loan_cnt']['loan_cnt_90d'] }}</p>
                <p>近6个月内贷款查询次数：{{ $info['history_loan_cnt']['loan_cnt_180d'] }}</p>
            @endif
            <p style="height: .2rem"></p>
            @if(!empty($info['history_queried']))
                @foreach($info['history_queried'] as $key=>$val)
                    <p>
                        {{ $key }}查询次数：{{ $val }}</p>
                @endforeach
            @endif

            @if(!empty($info['history']))
                <table>
                    <thead>
                    <tr>
                        <th>查询日期</th>
                        <th>机构类型</th>
                        <th>是否是本机构查询</th>
                    </tr>
                    </thead>
                    <tbody> @foreach($info['history'] as $item)
                        <tr>
                            <td>{{ empty($item['date']) ? '未知' : $item['date'] }}</td>
                            <td>{{ empty($item['org_type']) ? '未知' : $item['org_type'] }}</td>
                            <td>{{ $item['is_self'] ? '是' : '否' }}</td>
                        </tr> @endforeach </tbody>
                </table>@else <p>{{ '目前还没有机构查询您的信用情况' }}</p> @endif </section> {{--黑名单--}}
        <section class="section_four">
            <h3>黑名单</h3> @if(!empty($info['black']))
                <p>被标记的黑名单分类：{{ empty($info['black']['type']) ? '未知' : $info['black']['type'] }}</p>
                <p>身份证和姓名<i style="color: #24c08e">{{ empty($info['black']['is_idcard_name']) ? '不在' : '在' }}</i>
                    黑名单(<i>{{ empty($info['black']['idcard_name']) ? '未知' : $info['black']['idcard_name'] }}</i>)</p>
                <p>手机和姓名<i style="color: #24c08e">{{ empty($info['black']['is_phone_name']) ? '不在' : '在' }}</i>
                    黑名单(<i>{{ empty($info['black']['phone_name']) ? '未知' : $info['black']['phone_name'] }}</i>)</p>
                <p>直接联系人总数：{{ empty($info['black']['contact_total']) ? '未知' : $info['black']['contact_total'] }} <b
                            class="cover_icon">直接联系人：和被查询号码有通话记录</b></p>
                <p>
                    直接联系人在黑名单数量：{{ empty($info['black']['contact_black_count']) ? '未知' : $info['black']['contact_black_count'] }}</p>
                <p>
                    引起黑名单的直接联系人数量：{{ empty($info['black']['introduce_black_count']) ? '未知' : $info['black']['introduce_black_count'] }}
                    <b class="cover_icon">直接联系人和黑名单用户的通讯记录的号码数量</b></p>
                <p>
                    引起黑名单的直接联系人占比：{{ empty($info['black']['introduce_black_ratio']) ? '未知' : $info['black']['introduce_black_ratio'] }}
                    <b class="cover_icon">引起黑名单的直接联系人数量/直接联系人总数</b></p>
                <p>
                    间接联系人在黑名单数量：{{ empty($info['black']['indirect_black_count']) ? '未知' : $info['black']['indirect_black_count'] }}
                    <b class="cover_icon">和被查询号码的直接联系人有通话记录</b></p>
                <br>
                <p>黑名单详细信息：</p>
                <p>地址：{{ empty($info['black']['user_address']) ? '未知' : $info['black']['user_address'] }}</p>
                <p>累计借入本金：{{ empty($info['black']['loan_money']) ? '未知' : $info['black']['loan_money'] }} </p>
                <p>累计已还本额：{{ empty($info['black']['already_money']) ? '未知' : $info['black']['already_money'] }} </p>
                <p>
                    累计逾期金额：{{ empty($info['black']['overdue_money']) ? '未知' : $info['black']['overdue_money'] }} </p> @endif
        </section> {{--金融信贷信息--}}
        <section class="section_five">
            <h3>金融信贷信息</h3> @if(!empty($info['finance'])) @foreach($info['finance'] as $item)
                <p>风险类型：{{ empty($item['type']) ? '未知' : $item['type'] }}
                    <span>风险等级：{{ empty($item['level']) ? '未知' : $item['level'] }}</span></p>
                <p>更新时间：{{ empty($item['refresh_time']) ? '未知' : $item['refresh_time'] }}</p>
                <p>当前状态：{{ empty($item['settlement']) ? '未知' : $item['settlement'] }}</p>
                <p>违约时间：{{ empty($item['event_end_time_desc']) ? '未知' : $item['event_end_time_desc'] }}</p>
                <p>逾期最大天数：{{ empty($item['code']) ? '未知' : $item['code'] }}</p>
                <p>逾期最大金额：{{ empty($item['event_max_amt_code']) ? '未知' : $item['event_max_amt_code'] }}</p>
                <p>异议状态：{{ empty($item['status']) ? '未知' : $item['status'] }}</p>
                <p style="height: 3px"></p> @endforeach  @else <p>{{ '没有查询到您金融信贷记录' }}</p> @endif </section> {{--公检法--}}
        <section class="section_six">
            <h3>公检法</h3> @if(!empty($info['security'])) @foreach($info['security'] as $item)
                <p>风险类型：{{ empty($item['type']) ? '未知' : $item['type'] }}
                    <span>风险等级：{{ empty($item['level']) ? '未知' : $item['level'] }}</span></p>
                <p>更新时间：{{ empty($item['refresh_time']) ? '未知' : $item['refresh_time'] }}</p>
                <p>当前状态：{{ empty($item['settlement']) ? '未知' : $item['settlement'] }}</p>
                <p>违约时间：{{ empty($item['event_end_time_desc']) ? '未知' : $item['event_end_time_desc'] }}</p>
                <p>逾期最大天数：{{ empty($item['code']) ? '未知' : $item['code'] }}</p>
                <p>逾期最大金额：{{ empty($item['event_max_amt_code']) ? '未知' : $item['event_max_amt_code'] }}</p>
                <p>异议状态：{{ empty($item['status']) ? '未知' : $item['status'] }}</p>
                <p style="height: 3px"></p> @endforeach @else <p>{{ '没有查询到您的公检法类信息' }}</p> @endif
        </section> {{--公积金信息--}}
        <section class="section_seven">
            @if(!empty($info['funds']))
                <h3>公积金信息</h3>
                <p>邮箱：{{ empty($item['email']) ? '未知' : $item['email'] }}</p>
                <p>所在单位：{{ empty($item['company']) ? '未知' : $item['company'] }}</p>
                <p>单位类型：{{ empty($item['company_type']) ? '未知' : $item['company_type'] }}元</p>
                <p>家庭地址：{{ empty($item['home_address']) ? '未知' : $item['home_address'] }}元</p> @endif
        </section> {{--借记卡信息--}}
        <section class="section_eight">
            @if(!empty($info['debit_card']))
                <h3>借记卡信息</h3>
                <p>卡片数目：{{ empty($info['debit_card']['card_amount']) ? '未知' : $info['debit_card']['card_amount'] }}
                    <span>总余额：{{ empty($info['debit_card']['total_amount']) ? '未知' : $info['debit_card']['total_amount'] }}
                        元</span></p>
                <p>更新时间：{{ empty($info['debit_card']['update_date']) ? '未知' : $info['debit_card']['update_date'] }}</p>
                <table>
                    <thead>
                    <tr>
                        <th>收入（近一年）</th>
                        <th>金额（元）</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>工资收入</td>
                        <td>{{ empty($info['debit_card']['total_salary_income']) ? '未知' : $info['debit_card']['total_salary_income'] }}</td>
                    </tr>
                    <tr>
                        <td>贷款收入</td>
                        <td>{{ empty($info['debit_card']['total_loan_income']) ? '未知' : $info['debit_card']['total_loan_income'] }}</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td>总收入</td>
                        <td>{{ empty($info['debit_card']['total_income']) ? '未知' : $info['debit_card']['total_income'] }}</td>
                    </tr>
                    </tfoot>
                </table>
                <table>
                    <thead>
                    <tr>
                        <th>支出（近一年）</th>
                        <th>金额（元）</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>消费</td>
                        <td>{{ empty($info['debit_card']['total_consume_outcome']) ? '未知' : $info['debit_card']['total_consume_outcome'] }}</td>
                    </tr>
                    <tr>
                        <td>还贷</td>
                        <td>{{ empty($info['debit_card']['total_loan_outcome']) ? '未知' : $info['debit_card']['total_loan_outcome'] }}</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td>总支出</td>
                        <td>{{ empty($info['debit_card']['total_outcome']) ? '未知' : $info['debit_card']['total_outcome'] }}</td>
                    </tr>
                    </tfoot>
                </table> @endif </section>
        <section class="section_nine">
            @if(!empty($info['credit_card']))
                <h3>信用卡信息</h3>
                <p>
                    卡片数目：{{ empty($info['credit_card']['total_outcome']) ? '未知' : $info['credit_card']['total_outcome'] }}</p>
                <p>
                    更新时间：{{ empty($info['credit_card']['update_date']) ? '未知' : $info['credit_card']['update_date'] }}</p>
                <p>
                    总信用额：{{ empty($info['credit_card']['total_credit_limit']) ? '未知' : $info['credit_card']['total_credit_limit'].'元' }}</p>
                <p>
                    总可用信用额：{{ empty($info['credit_card']['total_credit_available']) ? '未知' : $info['credit_card']['total_credit_available'].'元' }}</p>
                <p>
                    单一银行最高信用额：{{ empty($info['credit_card']['max_credit_limit']) ? '未知' : $info['credit_card']['max_credit_limit'].'元' }} </p>
                <table>
                    <thead>
                    <tr>
                        <th>收入（近一年）</th>
                        <th>数量</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>逾期次数</td>
                        <td>{{ empty($info['credit_card']['overdue_times']) ? '未知' : $info['credit_card']['overdue_times'] }}</td>
                    </tr>
                    <tr>
                        <td>逾期月数</td>
                        <td>{{ empty($info['credit_card']['overdue_months']) ? '未知' : $info['credit_card']['overdue_months'] }}</td>
                    </tr>
                    </tbody>
                </table> @endif </section>
        <section class="section_ten">
            <h4 style="text-align: center">信用报告数据说明</h4>
            <p style="text-indent:2em">闪信报告由本人授权查询，通过分析原始数据生成，结果仅供您参考。</p>
        </section>
    </div>
</div>
<script src="/vendor/jquery.min.js"></script>
<script src="/vendor/bootstrap/js/popper.js"></script>
<script src="/vendor/bootstrap/js/bootstrap.js"></script>
<script src="/view/js/controler/info.js"></script>
</body>