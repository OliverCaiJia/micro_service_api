$(function(){
	dataController.initView();
	dataController.bindEvent();
})
var dataController = {
	addPercent : {
		base:false,
		job:false,
		asset:false,
		credit:false
	},
	initView:function(){
		$(".knob").knob({});		//初始化进度条插件
		dataController.fixedLoansBtn();
		global.checkName($("#base_name"),dataController.checkBasicInfo);
		global.checkIdCard($("#base_card"),dataController.checkBasicInfo);	
		dataController.checkComplete();
		var city = $('#base_city').val() == ''?$.cookie('location'):$('#base_city').val();
		$('#base_city').val(city);
	},
	checkBasicInfo:function(){
		var nameZ = /^[\u4e00-\u9fa5]{2,21}$/;
		var nameVal = $.trim($('#base_name').val());
		var nameCorrect = (nameVal != '' && nameZ.test(nameVal));
		var idcardVal = $('#base_card').val();
		var validator = new IDValidator();
        var res = validator.isValid(idcardVal);
        var idcardCorrect = (idcardVal != '' && res);
        var cityVal = $('#base_city').val();
        if(nameCorrect && idcardCorrect && cityVal != '' && !dataController.addPercent.base){
        	$('#base_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
        	$('#base_info .success_percent').attr('isComplete','1');
			var curPercent = parseInt($('.knob').val());
			$('.knob').val(curPercent + 20).trigger("change");
			$('#percentNum').html(curPercent + 20);
			dataController.addPercent.base = true;
        }else{
        	if(dataController.addPercent.base && (!nameCorrect || !idcardCorrect || cityVal == '')){
	        	var curPercent = parseInt($('.knob').val());
	        	$('#base_info .success_percent').html('完善工作信息，下款成功率+20%');
	        	$('#base_info .success_percent').attr('isComplete','0');
	        	$('.knob').val(curPercent - 20).trigger("change");
				$('#percentNum').html(curPercent - 20);
				dataController.addPercent.base = false;
	        }
        }
	},
	//检查各版块是否含有历史信息
	checkComplete:function(){
		dataController.addPercent = {
			base:$('#base_info .success_percent').attr('isComplete') == '1'?true:false,
			job:$('#job_info .success_percent').attr('isComplete') == '1'?true:false,
			asset:$('#asset_info .success_percent').attr('isComplete') == '1'?true:false,
			credit:$('#credit_info .success_percent').attr('isComplete') == '1'?true:false
		}
	},
	bindEvent:function(){
		var _this = this;
		//收缩板块信息
		$('#base_info .stretch_btn').click(function(){
			if($('#base_info .inp_area').css('display') == 'none'){
				$(this).css('background','url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
				$(this).css('background-size','.28rem .18rem');
			}else{
				$(this).css('background','url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
				$(this).css('background-size','.18rem .28rem');
			}
			$('#base_info .inp_area').stop(false,false).slideToggle();
		})
		$('#job_info .stretch_btn').click(function(){
			if($('#job_info .job_sel_box').css('display') == 'none'){
				$(this).css('background','url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
				$(this).css('background-size','.28rem .18rem');
			}else{
				$(this).css('background','url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
				$(this).css('background-size','.18rem .28rem');
			}
			$('#job_info .job_sel_box').stop(false,false).slideToggle();
		})
		$('#asset_info .stretch_btn').click(function(){
			if($('#asset_info .asset_sel_box').css('display') == 'none'){
				$(this).css('background','url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
				$(this).css('background-size','.28rem .18rem');
			}else{
				$(this).css('background','url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
				$(this).css('background-size','.18rem .28rem');
			}
			$('#asset_info .asset_sel_box').stop(false,false).slideToggle();
		})
		$('#credit_info .stretch_btn').click(function(){
			if($('#credit_info .credit_sel_box').css('display') == 'none'){
				$(this).css('background','url(/view/img/oneloan/full_bottom_icon.png) no-repeat center center');
				$(this).css('background-size','.28rem .18rem');
			}else{
				$(this).css('background','url(/view/img/oneloan/full_right_icon.png) no-repeat center center');
				$(this).css('background-size','.18rem .28rem');
			}
			$('#credit_info .credit_sel_box').stop(false,false).slideToggle();
		})
		
		//点击选择城市信息
		$('#base_city').click(function(){
			global.getCity();
		})
		
		//点击返回按钮
		$('#full_top .go_back').click(function(){
			$('#basic-page').show();
			$('#full-page').hide();
		})
		
		//点击立即贷款按钮
		$('#full_loans .loans_btn').click(function(){
			var basicComplete = $('#base_info .success_percent').attr('isComplete'),
				jobComplete = $('#job_info .success_percent').attr('isComplete'),
				assetComplete = $('#asset_info .success_percent').attr('isComplete'),
				creditComplete = $('#credit_info .success_percent').attr('isComplete');
			var salary_extend = $('#occupation span.onSelect').data('val').toString() == '001'?$('#office_workers .salary_extend .sel_option span.onSelect').data('val').toString():'',
				salary = $('#occupation span.onSelect').data('val').toString() == '001'?$('#office_workers .salary .sel_option span.onSelect').data('val').toString():$('#occupation span.onSelect').data('val').toString() == '002'?$('#servant .salary .sel_option span.onSelect').data('val').toString():$('#private_business_owner .salary .sel_option span.onSelect').data('val').toString(),
				work_hours = $('#occupation span.onSelect').data('val').toString() == '001'?$('#office_workers .work_hours .sel_option span.onSelect').data('val').toString():$('#occupation span.onSelect').data('val').toString() == '002'?$('#servant .work_hours .sel_option span.onSelect').data('val').toString():'',
				accumulation_fund = $('#occupation span.onSelect').data('val').toString() == '001'?$('#office_workers .accumulation_fund .sel_option span.onSelect').data('val').toString():$('#occupation span.onSelect').data('val').toString() == '002'?$('#servant .accumulation_fund .sel_option span.onSelect').data('val').toString():'',
				social_security = $('#occupation span.onSelect').data('val').toString() == '001'?$('#office_workers .social_security .sel_option span.onSelect').data('val').toString():'',
				business_licence = $('#occupation span.onSelect').data('val').toString() == '003'?$('#private_business_owner .business_licence .sel_option span.onSelect').data('val').toString():'';
			var postData = {
				'money' : $.cookie('money'),
				'name' : $('#base_name').val(),
				'certificate_no' : $('#base_card').val(),
				'city' : $('#base_city').val(),
				'occupation' : $('#occupation span.onSelect').data('val').toString(),
				'salary_extend' : salary_extend,
				'salary' : salary,
				'work_hours' : work_hours,
				'accumulation_fund' : accumulation_fund,
				'social_security' : social_security,
				'business_licence' : business_licence,
				'has_insurance' : $('#has_insurance .sel_option span.onSelect').data('val'),
				'house_info' : $('#house_info .sel_option span.onSelect').data('val').toString(),
				'car_info' : $('#car_info .sel_option span.onSelect').data('val').toString(),
				'has_creditcard' : $('#has_creditcard .sel_option span.onSelect').data('val'),
				'is_micro' : $('#has_creditcard .sel_option span.onSelect').data('val')
			}
			console.log(postData);
			if(basicComplete == '0'){
				global.popupCover({
                    content: '基本信息有误,请重新输入!'
                });
                return;
			}else if(jobComplete == '0'){
				global.popupCover({
                    content: '工作信息有误,请重新输入!'
                });
                return;
			}else if(assetComplete == '0'){
				global.popupCover({
                    content: '资产信息有误,请重新输入!'
                });
                return;
			}else if(creditComplete == '0'){
				global.popupCover({
                    content: '信用信息有误,请重新输入!'
                });
                return;
			}else{
				console.log('通过');
			}
		})
		
		
		//点击标签事件
        $('.sel_option').on('click', 'span', function () {
            $(this).addClass('onSelect').siblings('span').removeClass('onSelect');
			
			//当点击工作信息内容 监测输入完整情况
			if($(this).parents('#job_info').length != 0 && $(this).parents('#job').length == 0){
				var selFin = true,
					jobIndex = $('#job span.onSelect').index();
				for(let i=0;i<$('#job_info .sel_section:eq('+jobIndex+') .sel_area').length;i++){
					if($('#job_info .sel_section:eq('+jobIndex+') .sel_area:eq('+i+') .sel_option').find('span.onSelect').length == 0){
						selFin = false;
						break;
					}
				}
				if(selFin && !dataController.addPercent.job){
					$('#job_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
					$('#job_info .success_percent').attr('isComplete','1');
					var curPercent = parseInt($('.knob').val());
					$('.knob').val(curPercent + 40).trigger("change");
					$('#percentNum').html(curPercent + 40);
					dataController.addPercent.job = true;
				}
			}
			
			
			//当点击资产信息内容  监测输入完整情况
			if($(this).parents('#asset_info').length != 0){
				var selFin = true;
				for(let i=0;i<$('#asset_info .sel_area').length;i++){
					if($('#asset_info .sel_area:eq('+i+') .sel_option').find('span.onSelect').length == 0){
						selFin = false;
						break;
					}
				}
				if(selFin && !dataController.addPercent.asset){
					$('#asset_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
					$('#asset_info .success_percent').attr('isComplete','1');
					var curPercent = parseInt($('.knob').val());
					$('.knob').val(curPercent + 25).trigger("change");
					$('#percentNum').html(curPercent + 25);
					dataController.addPercent.asset = true;
				}
			}
			
			
			//当点击信用信息内容   监测输入完整情况
			if($(this).parents('#credit_info').length != 0){
				var selFin = true;
				for(let i=0;i<$('#credit_info .sel_area').length;i++){
					if($('#credit_info .sel_area:eq('+i+') .sel_option').find('span.onSelect').length == 0){
						selFin = false;
						break;
					}
				}
				if(selFin && !dataController.addPercent.credit){
					$('#credit_info .success_percent').html('<span style="color:#fe5c0d;">已完成</span>');
					$('#credit_info .success_percent').attr('isComplete','1');
					var curPercent = parseInt($('.knob').val());
					$('.knob').val(curPercent + 10).trigger("change");
					$('#percentNum').html(curPercent + 10);
					dataController.addPercent.credit = true;
				}
			}
        });
        //切换工作事件
        $('#occupation').on('click', 'span', function () {
            var idx = $(this).index() ;
            $('#job_info .job_cover_box').show().find('span').removeClass('onSelect');
            $('#job_info .success_percent').html('完善工作信息，下款成功率+40%');
            $('#job_info .success_percent').attr('isComplete','0');
            if(dataController.addPercent.job){
            	var curPercent = parseInt($('.knob').val());
				$('.knob').val(curPercent - 40).trigger("change");
				$('#percentNum').html(curPercent - 40);
				dataController.addPercent.job = false;
            }
            $('.sel_section').eq(idx).show().siblings('.sel_section').hide();
        }); 
	},
	//计算高度定位底部提交按钮
	fixedLoansBtn:function(){
		var minHeight = $(window).height() - $('#full_top').height() - $('#full_loans').height()
		$('.main').css('min-height',minHeight);
	}
}
