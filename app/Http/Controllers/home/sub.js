$(function () {
	function checkphone(str) {
    	return str.match(/^1[34578]\d{9}$/);
	}
	setInterval(guess_load,1000);
	function guess_load(){
		$.get('/xm_guess_load',{},function(data){
        	// alert(111);
            var data = JSON.parse(data);
            if(data.code == '1'){
                $.each(data.data,function(k,v){
                	$('#showText_img').after("<span class='showText_text_2'>"+v.phone+"在"+v.add_time+"竞猜"+v.guess_no+"</span>");
                });
            }else{
                alert(data.message);
            }
        });
	}
	$('#subBtn').on('click',function(){
		// alert(111);
		var phone = $('#phone').val();
		var guess_no = $('#guess_no').val();
		if(phone =='' || !checkphone(phone)){
			alert('请填写正确格式的手机号！');
			return false;
		}else if(guess_no==''){
			alert('请填写竞猜号码！');
			return false;
		}
		// 发送
        $.post('/xm_play_guess',{'phone':phone,'guess_no':guess_no},function(data){
        	// alert(111);
            var data = JSON.parse(data);
            if(data.code == '1'){
            	$('#guess_no1').text(data.data.guess_no);
            	$('#time1').text(data.data.add_time);
            	$('#guess_no2').text('？');
            	$('#time2').text('');
            	if(data.guess_no2 !=''){
            		$('#guess_no2').text(data.data.guess_no2);
            		$('#time2').text(data.data.add_time2);
            	}
                alert(data.message);
            }else{
                alert(data.message);
            }
        });
	});
});