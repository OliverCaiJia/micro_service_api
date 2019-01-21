<script src="http://cdn.bootcss.com/jquery/2.1.0/jquery.min.js"></script>
<table>
    <tr>
        <td>手机号码</td>
        <td>
            <input type="text" name="mobile" id="mobile" value="13717607457">
        </td>
    </tr>
    <tr>
        <td>借款金额</td>
        <td>
            <input type="text" name="money" id="money" value="10000">
        </td>
    </tr>
    <tr>
        <td>姓名</td>
        <td>
            <input type="text" name="name" id="name" value="赵强">
        </td>
    </tr>
    <tr>
        <td>性别</td>
        <td>
            <input type="radio" name='sex' value ='1'>男
            <input type="radio" name='sex' value= "0" checked>女
        </td>
    </tr>
    <tr>
        <td>出生日期</td>
        <td>
            <input type="text" name="birthday" id="birthday" value="1991-08-25">
        </td>
    </tr>
    <tr>
        <td>城市</td>
        <td>
            <input type="text" name="city" id="city" value="北京市">
        </td>
    </tr>
    <tr>
        <td>有信用卡</td>
        <td>
            <input type="radio" name='has_creditcard' value ='1' checked>有
            <input type="radio" name='has_creditcard' value= "0">无
        </td>
    </tr>
    <tr>
        <td>领取保险</td>
        <td>
            <input type="radio" name='is_insurance' value ='1'>是
            <input type="radio" name='is_insurance' value = "0">否
        </td>
    </tr>
    <tr>
        <td>身份证号</td>
        <td>
            <input type="text" name='certificate_no' id="idcard" value="371526199108253218">
        </td>
    </tr>
</table>
<button id="btn">提交</button>
<script type="text/javascript">
    $(function () {
        $('#btn').click(function () {
            var mobile = $('#mobile').val();
            var money = $('#money').val();
            var name = $('#name').val();
            var sex  = $("input[name='sex']").val();
            var birthday = $('#birthday').val();
            var city = $('#city').val();
            var has_creditcard = $("input[name='has_creditcard']").val();
            var is_insurance = $("input[name='is_insurance']").val();

            var obj = {
                'page' : 1,
                'user_id':1075,
                'mobile' : mobile,
                'money' : money,
                'name' : name,
                'sex' : sex,
                'birthday' : birthday,
                'city' : city,
                'has_creditcard' : has_creditcard,
                'is_insurance' : 1,
                'certificate_no' :$('#idcard').val()

            }
            console.log(obj);
            var url = 'http://dev.api.sudaizhijia.com/v1/spread/insurance';
            $.post(url, obj, function (data) {
                console.log(data);
            }, 'json');
        });
    });
</script>


