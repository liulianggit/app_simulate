<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>关于我们</title>
    <style>
    *{
        font-family: PingFangSC-Regular, sans-serif;
    }
    body{
        padding:0 5px;
    }
    p{
        font-size: 15px;
        color: #666666;
        text-indent:30px;
    }
    .en{
        position: fixed;
        text-align: center;
        line-height: 33px;
        width: 33px;
        height: 33px;
        right: 15px;
        bottom: 15px;
        color: rgb(255, 255, 255);
        border-radius: 20%;
        font-size: 13px;
        background-image: linear-gradient(-7deg, #3166ff 0%, #29caff 100%), linear-gradient(#00c89c, #00c89c);
    }
    .right{
        display: block;
        width: 100%;
        text-align: right;
    }
    span{
        margin-bottom: 5px;
    }
    </style>
</head>
<body>
    <div class="en" id="btn" onclick="change()">
        EN
    </div>

    <div id="chs">
        <h3>关于我们:</h3>
        <p>熊猫外汇——初创团队成立于2013年，由国际知名金融公司的专业人士和电子商务领域的专家共同创办。致力于为广大投资者提供国际金融高端产品服务，作为国际金融投资产品和交易商的代理商，熊猫外汇历经两年研发的社交化交易平台极大拉近广大投资者与金融大咖的距离，帮助投资者轻松配置全球资本市场。</p>
        <h3>平台优势:</h3>
        <p>
            1.熊猫外汇成立以来快速成长，累计活跃客户超过1万名，17年第二季度交易量突破300亿美元。
        </p>
        <p>
            2.熊猫外汇与国际著名外汇经纪商合作（作为熊猫外汇的外汇经纪商，TIGERWIT受巴哈马证券交易委员会(SCB)的授权和监管，监管号：SIA-F185。TIGERWIT受英国金融行为监管局（FCA）的授权和监管，监管号：679941。TIGERWIT澳大利亚子公司Tiger Financial Technology Pty Ltd (ACN 614 234 687)，是HLK Group Pty Ltd (ACN 161 284 500)的机构授权代表(CAR No. 001 247 008)，被授权在协议内提供个人和一般性建议以及管理委托账户。）熊猫外汇致力于帮助广大投资者轻松配置全球资本市场，其合作交易平台严格遵守监管方当地的法律法规，且资金隔离，交易资金安全可靠。
        </p>
        <p>
            3.目前平台提供的外汇交易品种高达上百种产品，主要有：外汇、原油、贵金属、CFD。
        </p>
        <p>
            4.在熊猫外汇的APP平台上，每天每时来自全国各地的交易高手实时地分享他们的交易策略。作为投资者，可根据自己的交易情况，风险偏好同步复制高手的交易订单，并帮助自己大幅提高投资收益，作为交易高手也因投资者的复制订单而获得额外的丰厚收益。
        </p>
    </div>
    <div id="en" style="display:none;">
        <p>Panda Forex,whose set-up team is established in 2013，is co-founded by a team of professionals from internationally renowned financial companies and e-commerce specialists, devoted to providing high-end international financial products and services with investors. As an agent of international financial investment products and traders, after two years of research and development of social trading platform, Panda Forex greatly narrowed the gap between investors and financial masters to help investors easily configure global capital markets.</p>
        <p>Since the establishment of Panda FX, it has grown rapidly and has accumulated more than 10,000 active customers. In the second quarter of 2017 , the trading volume has exceeded US$30 billion.Panda FX cooperates with internationally renowned forex broker (as Panda FX's broker, TIGERWIT is authorized and regulated by the Securities Commission of The Bahamas, licence number SIA-F185.TIGERWIT is authorized and regulated by Financial Conduct Authority（FCA）, licence number 679941.Tiger Financial Technology Pty Ltd (ACN 614 234 687), Australian subsidiary of TIGERWIT,is an agency authorized representative (CAR No. 001 247 008) of HLK Group Pty Ltd (ACN 161 284 500),and is authorized to provide personal and general advice and manage trust accounts within the agreement.) Panda FX is committed to  help investors to easily configure the global capital market. Its cooperative trading platform strictly complies with the local laws and regulations, and funds are segregated. The transaction funds are safe and reliable.</p>
        <p>At present, the platform offers hundreds of forex products, including forex, crude oil, precious metals, and CFD.</p>
        <p>On app platform of Panda FX , trading masters from all over the country share their trading strategies all time every day. Investors, according to their own trading situation and risk preferences,can replicate synchronously the master's trading orders and help themselves greatly increase their investment income. As a trading master, he also gains extra lucrative income due to investors' copy orders.</p>
        <span style="display:block;">Contact us：</span>
        <span class="right">Address：(FFC)Financial Center, East Third Ring Road, Chaoyang District</span>
        <span class="right">Tel：4000331949</span>
        <span class="right">Email：info@pandafx.com</span>
        <span class="right">Website：www.pandafx.com</span>
    </div>
    <script>
        var chs=document.getElementById('chs')
        var en=document.getElementById('en')
        var btn=document.getElementById('btn')
        change = () => en.style.display==='none'?changeEn():changeChs();
        changeEn = () => {btn.textContent='CHS';chs.style.display='none';en.style.display='block';}
        changeChs = () => {btn.textContent='EN';chs.style.display='block';en.style.display='none';}
    </script>
</body>
</html>