<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=375, target-densitydpi=device-dpi, user-scalable=0">
    <title>提现记录</title>
    <link href="/share/css/style.css" rel="stylesheet">
      <script src="/share/js/jquery-1.11.3.min.js" type="text/javascript"></script>
      
  </head>
  <body class="cash_body"> 


<div class="cash_content"> <div class="cash_title">提现记录</div>
  <div class="table">
  <table width="100%">
    @foreach($data as $v)
    <tr>
    <td width="130"><span></span>{{date('Y年m月d日',$v['cashtime'])}}</td>
    <td>${{$v['money']}}</td>
    @if($v['status'] ==0)
    <td>待审核</td>
    @elseif($v['status']==1)
    <td>已完成</td>
    @elseif($v['status'] ==2)
    <td>审核拒绝</td>
    @endif
    </tr>
    @endforeach
</table></div>


     <script>
    $(function () {
  
      });
  </script>
             
</body>
</html>