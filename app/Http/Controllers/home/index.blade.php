<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="./js/zepto.js"></script>  
    <script src="./js/touch.js"></script>  
    <title>邀请好友</title>
    <style>
        .img-box{
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
            background-image: url('./img/bg.png');
            background-repeat: no-repeat;
            background-size: 105% 105%;
            background-position-x: center;
        }
        .guize{
            width: 90px;
            height: 60px;
            border: 1px solid black;
            position: absolute;
            right: 0;
            top: 35px;
        }
        .alert-box{
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.25);
            z-index: 3;
            display: none;
        }
        .alert-text-box{
            position: absolute;
            width: 85%;
            height: auto;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            left: 0;
            right: 0;
            margin: auto;
            top: 210px;
            
        }
        .title{
            width: 100%;
            text-align: center;
        }
        .alert-text-box>p{
            color: white;
        }
        .close-box{
            position: absolute;
            width: 40px;
            height: 40px;
            position: absolute;
            right: 0;
            top: 0;
            border: 1px solid white;
            padding: 5px 5px 0 0;
        }
        .close-img{
            width: 22px;
            float: right;
        }
        .shareBtn{
            width: 200px;
            position: absolute;
            left: 0;
            right: 0;
            margin: auto;
            bottom: 15px;
            animation: s2b 1s infinite;
            -moz-animation: s2b 1s infinite;	/* Firefox */
            -webkit-animation: s2b 1s infinite;	/* Safari 和 Chrome */
            -o-animation: s2b 1s infinite;	/* Opera */
        }
        @keyframes s2b {
            50%{
                transform:scale(1.1,1.1);  
            }
            100%{
                transform:scale(1,1);  
            }
        }
        .rightBox{
            position: absolute;
            width: 80px;
            height: 32px;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 14px 0 0 14px;
            text-align: center;
            line-height: 35px;
            font-size: 14px;
            background-color: #ffa941;
            color: white;
        }
        .rightIcon{
            display: inline-block;
            width: 10px;
            height: 10px;
            border-top: 1px solid white;
            border-right: 1px solid white;
            transform: rotateZ(45deg);
            margin-right: 2px;
        }
        .logo{
            position: absolute;
            left: 10px;
            top: 20px;
            width: 135px;
            height: 30px;
        }
    </style>
</head>
<body>
    <div class="img-box">
        <img src="./img/logo.png" class="logo"/>
        <div class="guize" id="guize" tap="">
            <div class="rightBox">
                活动策划<div class="rightIcon"></div>
            </div>
        </div>
        <img src="./img/share.png" alt="" class="shareBtn" id="shareBtn">
    </div>

    <div class="alert-box" id="alert-box">
        <div class="alert-text-box">
            <div class="close-box" id='close-box'>
                <img src="./img/close.png" class="close-img" alt="">
            </div>
            <p class="title">——活动规则——</p>
            <p>1．活动时间：2018年4月16日—2018年5月12日；</p>
            <p>2．活动赠金按照1:6比例，以美元形式发送；</p>
            <p>3．邀请好友必须使用APP内邀请好友功能；</p>
            <p>4．活动赠金将于活动结束后五个工作日内发送到交易账户零钱包内；</p>
            <p>5．熊猫外汇保留随时修订、暂停、终止本活动及
            任何相关规则条款之权利及其最终解释权。</p>
        </div>
    </div>

    <script>
            var sAction = {
                type: "wechat_friend",	//分享类型  wechat_friend、wechat_circle、qq、weibo
                title: '111111',	//标题
                description: '11111',	//描述
                url:'',	//分享页面链接
                imgUrl: '1111',	//图片链接
            };
  
            var sAction_json=JSON.stringify(sAction);

            //点击邀请后调用此函数。
            $('#shareBtn').on('tap',function(){
                if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) { 
                    alert('ios');
                    //判断iPhone|iPad|iPod|iOS
                    TigerwitNative(sAction_json)
                } else if (/(Android)/i.test(navigator.userAgent)) {  
                    alert('android');
                    //判断Android
                    TigerwitNative.jsCall(sAction_json);
                } 
                // else { 
                    //pc
                // };
            })

            $('#guize').on('tap',function(){
                document.getElementById('alert-box').style.display='block';
            })
            $('#close-box').on('tap',function(){
                document.getElementById('alert-box').style.display='none';
            })
            
        

    </script>
</body>
</html>