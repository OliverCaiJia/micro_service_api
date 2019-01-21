@include('app.jdt.layouts.header');

<body>
    <div id="container">
        <header id="header" class="col-xs-12 col-sm-12"> <span class="backBtn" onclick="infoController.backBtn()"></span>
            <p class="text-center">信用报告</p>
        </header>
        <!-- /header -->
        <div class="content_top col-xs-12 col-sm-12">
            <section>
                <div class="left"> <img src="" class="status_img" data-value="1" id="grade">
                    <p id="status_text"></p>
                </div>
                <div class="right">
                    <p>编号：SD170628000001</p>
                    <p>2017-06-28</p>
                    <p style="margin-top:.1rem">综合评分：<span id="score">820</span></p>
                    <p>总贷款金额：<span>20万-25万</span></p>
                </div>
            </section>
            <div class="mature-progress">
                <div class="mature-progress-box v0" id="mamture_progress">
                    <dl> <dt style="background:#24c08e">0</dt>
                        <dd><span class="member-ico v0"></span></dd>
                    </dl>
                    <dl> <dt>180</dt>
                        <dd><span class="member-ico v0"></span>低</dd>
                    </dl>
                    <dl> <dt>360</dt>
                        <dd><span class="member-ico v1"></span>较好</dd>
                    </dl>
                    <dl> <dt>540</dt>
                        <dd><span class="member-ico v2"></span>良好</dd>
                    </dl>
                    <dl> <dt>720</dt>
                        <dd><span class="member-ico v3"></span>优秀</dd>
                    </dl>
                    <dl> <dt>900</dt>
                        <dd><span class="member-ico v4"></span>极好</dd>
                    </dl>
                    <div class="progress-box" id="progress_content"> <i class="progress-box-style"></i> </div>
                </div>
            </div>
        </div>
        <div class="content_bottom">
            <section class="section_one">
                <h3>身份解析</h3>
                <p>姓名：王**<span>性别：女</span></p>
                <p>年龄：26</p>
                <p>身份证：2633**********0666</p>
                <p>籍贯：湖北省/随州市</p>
                <p>手机号：1512256****</p>
                <p>手机归属地：江苏省/苏州市/太仓县</p>
                <p>手机运营商：中国移动</p> <img src="/view/img/report/right_icon.png" alt=""> </section>
            <section class="section_two">
                <h3>注册信息</h3>
                <p>注册APP总数：24</p>
                <p>现金贷：10</p>
                <p>信用卡代偿：13</p>
                <p>数据聚合平台：1</p>
            </section>
            <section class="section_three">
                <h3>机构查询历史</h3>
                <p>近15天内贷款申请次数：10</p>
                <p>近1个月内贷款申请次数：15</p>
                <p>近3个月内贷款申请次数：30</p>
                <p>近6个月内贷款申请次数：45</p>
                <p style="margin-top:.18rem">本机构查询次数：2</p>
                <p>现金贷查询次数：10</p>
                <p>其他：23</p>
                <table>
                    <thead>
                        <tr>
                            <th>查询日期</th>
                            <th>机构类型</th>
                            <th>是否是本机构查询</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2017-10-01</td>
                            <td>线上信用卡代还</td>
                            <td>否</td>
                        </tr>
                        <tr>
                            <td>2017-01-01</td>
                            <td>其他</td>
                            <td>是</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section class="section_four">
                <h3>黑名单</h3>
                <p>被标记的黑名单分类：网贷</p>
                <p>身份证和姓名<i style="color: #24c08e">在</i>黑名单<i>(2016-10-01 02:14:00)</i></p>
                <p>手机和姓名<i style="color: #24c08e">在</i>黑名单<i>(2016-10-01 02:14:00)</i></p>
                <p>直接联系人总数：30 <b class="cover_icon">直接联系人：和被查询号码有通话记录</b></p>
                <p>直接联系人在黑名单数量：5</p>
                <p>引起黑名单的直接联系人数量：5 <b class="cover_icon">直接联系人和黑名单用户的通讯记录的号码数量</b></p>
                <p>引起黑名单的直接联系人占比：16.7% <b class="cover_icon">引起黑名单的直接联系人数量/直接联系人总数</b></p>
                <p>间接联系人在黑名单数量：40 <b class="cover_icon">和被查询号码的直接联系人有通话记录</b></p>
                <br>
                <p>黑名单详细信息：</p>
                <p>地址：北京市朝阳区朝阳北路**楼1号楼***</p>
                <p>累计借入本金：0-1000元 </p>
                <p>累计已还金额：0-1000元 </p>
                <p>累计逾期金额：0-1000元 </p>
            </section>
            <section class="section_five">
                <h3>金融信贷信息</h3>
                <p>风险类型：逾期未还款<span>风险等级：中风险</span></p>
                <p>更新时间：2016-10-01</p>
                <p>当前状态：当前不逾期</p>
                <p>违约时间：YYYY-MM</p>
                <p>逾期最大天数：逾期两期</p>
                <p>逾期最大金额：500-1000</p>
                <p>异议状态：用户有异议，信息核查中</p>
            </section>
            <section class="section_six">
                <h3>公检法</h3>
                <p>风险类型：逾期未还款<span>风险等级：中风险</span></p>
                <p>更新时间：2016-10-01</p>
                <p>当前状态：已履行</p>
                <p>风险代码：失信被执行人（老赖）</p>
                <p>异议状态：用户有异议，信息核查中</p>
                <p>案件发布时间：与法院保持一致</p>
            </section>
            <section class="section_seven">
                <h3>公积金信息</h3>
                <p>邮箱：alialiali@163.com</p>
                <p>所在单位：北京智借科技</p>
                <p>单位类型：私企</p>
                <p>家庭地址：北京朝阳区xx路天天向上小区5#-203</p>
            </section>
            <section class="section_eight">
                <h3>借记卡信息</h3>
                <p>卡片数目：2<span>总余额：1000.00元</span></p>
                <p>更新时间：2017-10-12</p>
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
                            <td>2000.00</td>
                        </tr>
                        <tr>
                            <td>贷款收入</td>
                            <td>2000.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>总收入</td>
                            <td>4000.00</td>
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
                            <td>1000.00</td>
                        </tr>
                        <tr>
                            <td>还贷</td>
                            <td>2000.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>总支出</td>
                            <td>3000.00</td>
                        </tr>
                    </tfoot>
                </table>
            </section>
            <section class="section_nine">
                <h3>信用卡信息</h3>
                <p>卡片数目：2</p>
                <p>更新时间：2016-10-01</p>
                <p>总信用额：100,000.00元</p>
                <p>总可用信用额：10,000.00元</p>
                <p>单一银行最高信用额：4,000.00元 </p>
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
                            <td>3次</td>
                        </tr>
                        <tr>
                            <td>逾期月数</td>
                            <td>3个月</td>
                        </tr>
                    </tbody>
                </table>
            </section>
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
