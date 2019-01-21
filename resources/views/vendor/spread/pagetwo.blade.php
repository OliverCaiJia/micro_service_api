<table>
    <tr>
        <td>手机号</td>
        <td>
            <input type="text" name="mobile" id="mobile" value="13853085852">
        </td>
    </tr>
    <tr>
        <td>借款</td>
        <td>
            <input type="text" name="money" id="money" value="100">
        </td>
    </tr>
    <tr>
        <td>寿险保单</td>
        <td>
            <input type="radio" name='has_insurance' value ='1' checked>有
            <input type="radio" name='has_insurance' value= "0">无
        </td>
    </tr>
    <tr>
        <td>房产</td>
        <td>
            <input type="radio" name='house_info' value ='000'>无房
            <input type="radio" name='house_info' value= "001" checked>有房贷
            <input type="radio" name='house_info' value= "002">无房贷
        </td>
    </tr>
    <tr>
        <td>汽车</td>
        <td>
            <input type="radio" name='car_info' value ='000' checked>无车
            <input type="radio" name='car_info' value= "001">有车贷
            <input type="radio" name='car_info' value= "002">无车贷
        </td>
    </tr>
    <tr>
        <td>职业</td>
        <td>
            <input type="radio" name='occupation' value ='001' checked>上班族
            <input type="radio" name='occupation' value= "002">公务员
            <input type="radio" name='occupation' value= "003">私营业主
        </td>
    </tr>
    <tr>
        <td>工资发放</td>
        <td>
            <input type="radio" name='salary_extend' value ='001' checked>银行转账
            <input type="radio" name='salary_extend' value= "002">现金发放
        </td>
    </tr>
    <tr>
        <td>月收入</td>
        <td>
            <input type="radio" name='salary' value ='001' checked>3000以下
            <input type="radio" name='occupation' value= "002">3000-10000
            <input type="radio" name='occupation' value= "003"> >10000
        </td>
    </tr>
    <tr>
        <td>公积金时间</td>
        <td>
            <input type="radio" name='accumulation_fund' value ='000' checked>无公积金
            <input type="radio" name='accumulation_fund' value= "001">1年以内
            <input type="radio" name='accumulation_fund' value= "002">一年以上
        </td>
    </tr>
    <tr>
        <td>工作时间</td>
        <td>
            <input type="radio" name='work_hours' value ='001' checked>6个月内
            <input type="radio" name='work_hours' value= "002">12个月内
            <input type="radio" name='work_hours' value= "003">1年以上
        </td>
    </tr>
    <tr>
        <td>营业执照时间</td>
        <td>
            <input type="radio" name='business_licence' value ='001' checked>1年以内
            <input type="radio" name='business_licence' value= "002">1年以上
        </td>
    </tr>
    <tr>
        <td>有无社会保险</td>
        <td>
            <input type="radio" name='social_security' value='0' checked>无
            <input type="radio" name='social_security' value='1'>有
        </td>
    </tr>
    <tr>
        <td>身份证号</td>
        <td>
            <input type="text" name="money" id="idcard" value="371526199108253218">
        </td>
    </tr>

</table>
<button id="btn">提交</button>
<script src="http://cdn.bootcss.com/jquery/2.1.0/jquery.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('#btn').click(function () {
            var mobile = $('#mobile').val();
            var money = $('#money').val(); // 借款
            var has_insurance = $("input[name='has_insurance']").val(); // 寿险保单
            var house_info = $("input[name='house_info']").val();  //房产
            var car_info = $("input[name='car_info']").val();      //汽车
            var occupation = $("input[name='occupation']").val();  //职业
            var salary_extend = $("input[name='salary_extend']").val();      // 工资发放
            var salary = $("input[name='salary']").val(); // 工资
            var accumulation_fund = $("input[name='accumulation_fund']").val();    // 公积金时间
            var work_hours = $("input[name='work_hours']").val();                  // 工作时间
            var business_licence = $("input[name='business_licence']").val();// 营业执照
            var social_security = $("input[name='social_security']").val();  // 有无社会保险
            var idcard = $('#idcard').val();// 身份证号
            var obj = {
                'user_id' : 1075,
                'page' : 2,
                'mobile' : mobile,
                'money' : money,
                'has_insurance' : has_insurance,
                'house_info' : house_info,
                'car_info' : car_info,
                'occupation' : occupation,
                'salary_extend' : salary_extend,
                'salary' : salary,
                'accumulation_fund' : accumulation_fund,
                'work_hours' : work_hours,
                'business_licence' : business_licence,
                'social_security' : social_security,
                'certificate_no' : idcard
            }
            console.log(obj);
            var url = 'http://dev.api.sudaizhijia.com/v1/spread/insurance';
            $.post(url, obj, function (data) {
                console.log(data);
            }, 'json');
        });
    });
</script>
