<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=375, target-densitydpi=device-dpi, user-scalable=0">
    <title>邀请有礼</title>
    <link href="/share/css/style.css" rel="stylesheet">
      <script src="/share/js/jquery-1.11.3.min.js" type="text/javascript"></script>
     
  </head>
  <body> 
<div class="banner">
  <img src="/share/images/banner.jpg" class="center-block">
</div>
<div class="bg_div">
<div class="container s1">
 <div class="note valign"> 
<div class="div1"><div class="tt">可提现佣金</div><p><strong>${{$data['balance']}}</strong></p></div>
<div class="div2"><div class="tt">累积获得佣金</div><p><strong>${{$data['cumulate']}}</strong></p></div>
<div class="div3"><p><a href="/api/xm_record?id={{$uid}}">提现记录</a></p><p><a href="#" class="cash_a">提现</a></p></div>
  </div>
</div>
<div class="text1 clearfix"><a href="/api/xm_chart">佣金排行榜>></a><a href="#" class="right">查看详细规则>></a></div>
<div class="container s2">
  <div class="title">如何赚返佣</div>
 <div class="note"> 
   <img src="/share/images/img1.png" width="206" class="center-block">
  </div>
</div>
<div class="text2"><p>以邀请100个好友每天交易一手黄金返佣$3美元，汇率7计算：</p>
<p>交易一天 日赚<strong>300</strong>美元 （约合<strong>2100</strong>元）</p>
<p>交易一月 日赚<strong>6300</strong>美元 （约合<strong>44100</strong>元）</p>
<p>交易一年 日赚<strong>75600</strong>美元 （约合<strong>529200</strong>元）</p>
</div>
<div class="container s3">
  <div class="title">我的用户</div>
 <div class="note"> 
  <div class="t1">成功邀请好友 <strong>{{$data['num']}}人</strong></div>
  <div class="t2">更新时间：{{time()}}</div>
  <div class="table">
  <table width="100%">
    <tr>
      <th>邀请列表</th>
      <th>交易返佣</th>
    </tr>
    @if(count($data['users'])>0)
    @foreach($data['users'] as $k=>$v)
    
    <tr>
      <td>{{$v}}</td>
      <td>${{$data['records'][$k]}}</td>
    </tr>
    @endforeach
    @endif
  </table></div>
  </div>
</div>
<div class="container s4">
  <div class="title">活动规则</div>
 <div class="note"> 
<table>
  <tr>
    <td width="50">活动说明：</td>
    <td>好友通过您的推广二链接或二维码进行注册即成为您的用户。该用户每进行一笔实盘交易，您即可获得相应的返佣。</td>
  </tr>
</table>
<p class="p1">各品种返佣金额（最低按0.01手结算）</p>
<table class="border">
  <tr>
    <td>外汇（不含美元人民币品种）</td>
    <td>2美元/手</td>
  </tr>
   <tr>
    <td>能源</td>
    <td>2美元/手</td>
  </tr>
   <tr>
    <td>黄金</td>
    <td>3美元/手</td>
  </tr>
   <tr>
    <td>白银</td>
    <td>10美元/手</td>
  </tr>
   <tr>
    <td>CFD（即：所有指数类品种）</td>
    <td>1美元/手</td>
  </tr>
   <tr>
    <td>美元人民币</td>
    <td>0.2美元/手</td>
  </tr>
</table>
<!-- <p>佣金结算：每天凌晨6点结算前一日返佣，非实时结算。</p> -->
<p>佣金提现：佣金提现50美金起提，到账时间为1个工作日。提现账户为默认绑定银行卡账户。</p>
  </div>
</div>
 </div>
<!-- div class="fixed_bottom ">
  <a href="#" class="fx1">分享链接</a><a href="#" class="fx2">分享二维码</a>
</div> -->
<div class="cash_form">
  <div class="mask_bg"></div>
  <div class="cash_con">
    <div class="title">温馨提示</div>
    <p class="tips">提现金额必须大于$50</p>
    <input type="tel" class="text" value="" maxlength="10" placeholder="请输入金额">
    <input type="button" class="btn" value="确定" id="submit">
  </div>
</div>


<script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"16"},"share":{}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];</script>
     <script>
    $(function () {
 
     $(".cash_a").click(function (g) {
      $('.cash_form').show();
      g.preventDefault();
    });     
          $(".mask_bg").click(function () {
      $('.cash_form').hide();
    }); 
         $("#submit").click(function () {
          //金额,不能为负数，保留两位小数
       var reg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/;
        if ($(".text").val().length < 1) {
            alert("金额不能为空");
            return false;
        }else if (!reg.test($(".text").val())) {
            alert("金额格式不正确");
            return false;
        }else if (parseInt($(".text").val())<50) {
            alert("提现金额必须大于$50");
            return false;
        } 
        else {
         $.post('{{url('/api/getmoney')}}?id={{$uid}}',{'money':parseInt($(".text").val())},function(data){
              data = JSON.parse(data);
              if(data.status == 1){
                  alert(data.msg);
              }else{
                  alert(data.msg);
              }
          });
           $('.cash_form').hide();
        }
    });     
          //分享弹窗
         $(".fx1").on("click",function(g){
           $(".shareBox").show();
         g.preventDefault();
         });
          //取消分享
          $(".shareBox .cancel").on("click",function(g){
           $(".shareBox").hide();
         g.preventDefault();
         });
      });
  </script>
             
</body>
</html>