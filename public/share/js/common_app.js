document.oncontextmenu = function () { return false; };
        document.onkeydown = function () {
            if (window.event && window.event.keyCode == 123) {
                event.keyCode = 0;
                event.returnValue = false;
                return false;
            }
        };
//采用正则表达式获取地址栏参数
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]); return null;
}
function checkphone(str) {
    return str.match(/^1[34578]\d{9}$/);
}
$(function () {
    $("#url").val(window.location.href);
    $("#reurl").val(document.referrer);
    $("#utm_source").val(GetQueryString("utm_source"));
    $("#utm_medium").val(GetQueryString("utm_medium"));
    $("#utm_term").val(GetQueryString("utm_term"));
    $("#utm_content").val(GetQueryString("utm_content"));
    $("#utm_campaign").val(GetQueryString("utm_campaign"));
    // //获取手机验证码
    // $(".send_code").on("click", function () {
    //     if ($("#utel").val() == "") {
    //         alert("请输入您的手机号")
    //         return false;
    //     } else if (!checkphone($("#utel").val())) {
    //         alert("您的手机号码格式不正确")
    //         return false;
    //     }
    //     var btn = $(this);
    //     btn.addClass("disabled");
    //     btn.attr("disabled", true);
    //     var time = 60;
    //     // 发送短信
    //     $.ajax({
    //         type: 'POST',
    //         url: '/ajax/sendmobile-v2.0.aspx',
    //         cache: false,
    //        data: $("#registerForm").serialize(),
    //         dataType: "json",
    //         success: function (data) {
    //             if (data.success) {
                
    //                 var hander = setInterval(function () {
    //                     if (time <= 0) {
    //                         clearInterval(hander);
    //                         btn.val("获取验证码");
    //                         btn.removeAttr("disabled");
    //                         btn.removeClass("disabled");
    //                     }
    //                     else {
    //                         btn.val("" + (time--) + "秒后重发");
    //                     }
    //                 }, 1000);
    //             } else {
    //                 alert(data.message);
    //                 btn.removeAttr("disabled");
    //                 btn.removeClass("disabled");
    //             }
    //         },
    //         error: function (xhr, type) {
    //             alert("服务器错误");
    //             btn.removeAttr("disabled");
    //             btn.removeClass("disabled");
    //         }
    //     });
    // });
    //获取手机验证码
    $(".send_code").on("click", function () {
        if ($("#utel").val() == "") {
            alert("请输入您的手机号")
            return false;
        } else if (!checkphone($("#utel").val())) {
            alert("您的手机号码格式不正确")
            return false;
        }
        var btn = $(this);
        btn.addClass("disabled");
        btn.attr("disabled", true);
        var time = 60;
        var tmp = new Date().getTime();
        var tmp1 = $("#utel").val();
        tmp = tmp1+''+tmp;
        // 发送短信
        $.post('/ajax/sendmobile',{"phcode":tmp},function(data){
            var data = JSON.parse(data);
            console.log(data);
            if(data.status == 0){
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
        if ($("#uname").val().length < 1) {
            alert("姓名不能为空");
            return false;
        } else if ($("#utel").val().length < 1) {
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
            console.log($("#registerForm").serialize());
            $(this).attr("disabled", true);
       
            $.ajax({
                url: "/ajax/register",
                data: $("#registerForm").serialize(),
                dataType: "json",
                cache: false,
            //    async: false,
                type: "POST",
                success: function (data) {
                    if (!data.success) {
                         $('#register_btn').removeAttr("disabled");
                        alert(data.message);
                    } else {
                        console.log(data);
                        alert(data.message);
                        window.location.href = "/ajax/success_2";
                    }
                }
            });
        }
    });
});