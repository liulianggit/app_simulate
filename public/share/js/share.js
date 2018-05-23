    function checkphone(str) {
    return str.match(/^1[34578]\d{9}$/);
    }
$(function () {
  //获取手机验证码
    $(".send_code").on("click", function () {
        if ($("#utel").val() == "") {
            alert("请输入您的手机号")
            return false;
        } else if (!checkphone($("#utel").val())) {
            alert("您的手机号码格式不正确")
            return false;
        }
        var code = $('#bid').val();
        var btn = $(this);
        btn.addClass("disabled");
        btn.attr("disabled", true);
         // 发送短信
        $.post('/api/ajax/sendphcode',{"phone":$("#utel").val(),"bid":code},function(data){
            var data = JSON.parse(data);
            console.log(data);
            if(data.code == 1){
                countdown();
            }else{
                alert(data.message);
                btn.removeAttr("disabled");
                btn.removeClass("disabled");
            }
        });
    });

     function countdown(){
            document.getElementById("send_code").setAttribute("disabled", true);
            var i = 60;
            var intervalid;
            intervalid = setInterval(function(){
                if(i <= 0){
                    document.getElementById("send_code").value = '获取验证码';
                    clearInterval(intervalid);
                    $('.send_code').removeAttr("disabled");
                    $('.send_code').removeClass("disabled");
                }else{
                    if(i<10){
                        document.getElementById("send_code").value = '正在发送(0'+i+')';
                    }else{
                        document.getElementById("send_code").value = '正在发送('+i+')';
                    }
                    

                     i--;    
                }

      }, 1000);
    }

    $('#register_btn').click(function () {
        if ($("#utel").val().length < 1) {
            alert("手机号不能为空");
            return false;
        } else if (!checkphone($("#utel").val())) {
            alert("您的手机号码格式不正确")
            return false;
        } else if ($("#code").val() == "") {
            alert("验证码不能为空");
            return false;
        }
        else {
            $(this).attr("disabled", true);
       
            $.post('/api/shareband',{"phone":$("#utel").val(),"band":$('#bid').val()},function(data){
                var data = JSON.parse(data);
                if (!data.success) {
                    $('#register_btn').removeAttr("disabled");
                    // console.log(JSON.parse(data));
                    alert(data.message);
                } else {
                    console.log(data);
                    alert(data.message);
                    window.location.reload();
                }
            });
        }
    });
});