<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=375, target-densitydpi=device-dpi, user-scalable=0">
    <title>佣金排行榜</title>
    <link href="/share/css/style.css" rel="stylesheet">
      <script src="/share/js/jquery-1.11.3.min.js" type="text/javascript"></script>
     
  </head>
  <body class="phb_body"> 
<div class="top_title">累积返佣金额<strong>${{$sum}}</strong>美金</div>

<div class="phb_content"> 
  <div class="table">
  <table width="100%">
<tr>
  <th>佣金</th>
  <th width="70">返佣金额</th>
</tr>
  @foreach($data as $k=>$v)
    <tr>
    <td><span>{{$k+1}}</span>{{$v['pid']}}</td>
    <td>${{$v['cumulate']}}</td>
  </tr>
  @endforeach
</table></div>

  </div><p class="bottom_title">排行榜仅显示昨日返佣最高前十用户</p>
</div>


     <script>
    $(function () {
  
      });
  </script>
             
</body>
</html>