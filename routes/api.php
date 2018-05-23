<?php

use Illuminate\Http\Request;
use App\model\aes;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/index',function(Request $request){
	$test = strrev('zhixliangtianxia2018');
});
Route::get('/sign',function(Request $request){
	$str = config('app.token_str');
	$iv = config('app.token_iv');
	$key = config('app.token_key');
	$aes = new aes;
	echo $aes->aes256cbcEncrypt($str, $iv, $key );
});

//分享页面
Route::get('/xm_yaoqing','home\ShareController@index');
//记录
Route::get('/xm_record','home\ShareController@getRecord');
//排行榜
Route::get('/xm_chart','home\ShareController@getChart');
//分享页面
Route::get('/xm_share','home\ShareController@shareAll');
//分享注册
Route::post('/xm_shareband','home\ShareController@bandShare');
//发送邀请验证码
Route::post('/ajax/sendphcode','home\ShareController@sendphcode');
//佣金提现
Route::post('/getmoney','home\ShareController@getMoney');

//竞猜首页面
Route::get('/xm_get_guess','home\GuessController@index');
//竞猜
Route::post('/xm_play_guess','home\GuessController@play_guess');
//分享成功修改竞猜状态
Route::post('/xm_change_stu','home\GuessController@change_stu');

//获取开户银行列表
Route::get('/get_bank','home\CityController@get_bank');

//发送手机验证码
Route::post('/xm_sendphcode','home\RegisterController@sendmobile');
//手机号注册+登录
Route::post('/xm_reg','home\RegisterController@codeLogin');
//用户名密码登录
Route::post('/xm_login','home\LoginController@index');
//用户设置密码
Route::post('/xm_up_pwd','home\LoginController@up_pwd');
//密码找回
Route::post('/xm_lose_pwd','home\LoginController@lose_pwd');
//密码找回验证手机验证码
Route::post('/xm_get_code','home\LoginController@get_code');
//返回头像和昵称
Route::post('/xm_get_pic','home\UserInfoController@index');
//修改头像
Route::post('/xm_up_pic','home\UserInfoController@upload_pic');
//修改昵称
Route::post('/xm_up_nickname','home\UserInfoController@up_nickname');
//修改绑定手机号
Route::post('/xm_up_phcode','home\UserInfoController@up_phcode');
//公共回调接口
Route::post('/xm_comm_back','home\UserInfoController@comm_back');
//调用开户接口
Route::post('/xm_open_user','home\RegisterController@open_user');
//调用老虎h5
Route::post('/xm_tiger_h5','home\RegisterController@tiger_h5');
//调用修改mt4接口
Route::post('/xm_open_pass','home\LoginController@open_pass');
//轮播图
Route::post('/xm_slide','home\AdvertController@slide');
Route::post('/xm_advert','home\AdvertController@advert');
//获取充值提示
Route::post('/xm_get_hint','home\UserInfoController@get_hint');
//获取个人信息（零钱包余额，用户状态等信息）
Route::post('/xm_get_info','home\AssetController@get_info');
//获取个人资产信息
Route::post('/xm_asset_info','home\AssetController@asset_info');
//开仓获取可用保证金
Route::post('/xm_asset_open','home\AssetController@asset_open');
//外汇持仓接口(包含持仓和挂单订单)
Route::post('/xm_all_trades','home\AssetController@all_trades');
//获取持仓、平仓订单详情
Route::post('/xm_trade_info','home\AssetController@trade_info');
//历史交易记录
Route::post('/xm_history_trade','home\AssetController@history_trade');
//充值提现记录
Route::post('/xm_payment_histories','home\PaymentController@payment_histories');
//入金条件限制
Route::post('/xm_deposit_limits','home\PaymentController@deposit_limits');
//上传大额入金凭证
Route::post('/xm_payment_evidence','home\PaymentController@payment_evidence');
//申请入金【手机客户端入金】
Route::post('/xm_payment_deposit_app','home\PaymentController@payment_deposit_app');
//申请入金【web端入金】
Route::post('/xm_payment_deposit_web','home\PaymentController@payment_deposit_web');
//申请入金【电汇入金】
Route::post('/xm_payment_deposit_transfer','home\PaymentController@payment_deposit_transfer');
//获取支持出金银行列表
Route::post('/xm_get_bank_names','home\PaymentController@get_bank_names');
//申请出金
Route::post('/xm_payment_payout','home\PaymentController@payment_payout');
//取消出金
Route::post('/xm_payment_withdraw_cancel','home\PaymentController@payment_withdraw_cancel');
//出入金汇率
Route::post('/xm_payment_rates','home\PaymentController@payment_rates');
//修改，绑定银行卡信息
Route::post('/xm_bind_bank_card','home\PaymentController@bind_bank_card');
//获取银行卡信息
Route::post('/xm_get_bank_card','home\PaymentController@get_bank_card');
//出金条件检查
Route::post('/xm_payment_withdraw_limits','home\PaymentController@payment_withdraw_limits');
///获取汇率，余额，零钱包余额，充值提示等信息
Route::post('/xm_get_all_payment_info','home\PaymentController@get_all_payment_info');

//获取红包列表
Route::post('/xm_get_bonus','home\BonusController@get_bonus');
//领取红包
Route::post('/xm_bonus_receive','home\BonusController@bonus_receive');
//我的红包列表
Route::post('/xm_user_bonus','home\BonusController@user_bonus');
//兑换红包
Route::post('/xm_bonus_exchange','home\BonusController@bonus_exchange');

//获取开仓信息
Route::post('/xm_get_trade_open','home\TradeController@get_trade_open');
//获取平仓信息
Route::post('/xm_get_trade_close','home\TradeController@get_trade_close');
//修改订单止盈止损
Route::post('/xm_trade_update','home\TradeController@trade_update');
//挂单交易接口
Route::post('/xm_pending_trade_add','home\TradeController@pending_trade_add');
//修改挂单
Route::post('/xm_pending_trade_update','home\TradeController@pending_trade_update');
//删除挂单
Route::post('/xm_pending_trade_delete','home\TradeController@pending_trade_delete');


//获取交易品种分组
Route::post('/xm_get_symbols','home\SymbolController@get_symbols');
//搜索交易品种
Route::post('/xm_search_symbol','home\SymbolController@search_symbol');
//获取热门推荐的交易品种
Route::get('/xm_get_recommend','home\TradeCateController@get_recommend');
//获取用户自定义交易品种
Route::get('/xm_get_user_trade','home\TradeCateController@get_user_trade');
//添加用户自选交易品种(批量)
Route::post('/xm_add_user_trades','home\TradeCateController@add_user_trades');

//添加用户自选交易品种(批量)
Route::post('/xm_add_user_trades_new','home\TradeCateController@add_user_trades_andriod');
//添加用户自选交易品种
Route::post('/xm_add_user_trade','home\TradeCateController@add_user_trade');
//删除用户自选交易品种
Route::get('/xm_del_user_trade','home\TradeCateController@del_user_trade');
//历史报价
Route::post('/xm_get_quote_history','home\SymbolController@get_quote_history');
//获取交易品种汇总
Route::post('/xm_get_all_symbols','home\SymbolController@get_all_symbols');
//获取单个交易品种详情
Route::post('/xm_get_symbols_info','home\SymbolController@get_symbols_info');
//获取所有交易品种详情
Route::post('/xm_all_symbols_info','home\SymbolController@all_symbols_info');
//获取单个交易品种详情【交易品种详情页面】
Route::post('/xm_get_symbol_detail','home\SymbolController@get_symbol_detail');
//获取单个品种开收盘信息
Route::post('/xm_get_symbol_price','home\SymbolController@get_symbol_price');
//获取单个，多个品种收开盘信息
Route::post('/xm_symbol_price_collect','home\SymbolController@symbol_price_collect');
//获取单个价格信息
Route::post('/xm_symbol_now_price','home\SymbolController@symbol_now_price');
//产品交易时间段
Route::post('/xm_symbol_trade_time','home\SymbolController@symbol_trade_time');
//用户交易概况
Route::post('/xm_trading_profile','home\CentreController@trading_profile');
//用户交易走势图
Route::post('/xm_trading_trend','home\CentreController@trading_trend');
//用户主要交易品种
Route::post('/xm_trading_symbols','home\CentreController@trading_symbols');
//零钱包交易明细
Route::post('/xm_wallet_histories','home\WalletController@wallet_histories');
//零钱包可出金金额
Route::post('/xm_valid_balance','home\WalletController@valid_balance');
//零钱包提现
Route::post('/xm_wallet_withdraw','home\WalletController@wallet_withdraw');
//零钱包划入交易账户
Route::post('/xm_wallet_deposit','home\WalletController@wallet_deposit');
//意见反馈
Route::post('/xm_idea','home\AdvertController@idea');
Route::get('/contact_us', function () {
	return view('app/contact_me');
});
Route::get('/protocol', function () {
	return view('app/protocol');
});
Route::get('/statement', function () {
	return view('app/statement');
});
Route::get('/info','home\UserInfoController@info');
//测试专用
Route::get('/test','home\BreedController@index');
//定时任务路由
Route::get('/getcash','home\TabController@getcash');
//获取用户的开户状态
Route::post('/xm_open_state','home\AdvertController@is_true');
//获取系统消息列表
Route::post('/xm_sys','home\SystermController@sys_msg');
//省市二级联动
Route::post('/xm_region','home\RegionController@get_region');
//新web获取导航标题
Route::post('/xm_title','home\RegionController@get_title');
//新web获取导航标题全部
Route::post('/xm_title_all','home\RegionController@get_title_all');
//交易提醒
Route::post('/xm_trade_notice','home\SystermController@trade_notice');
//帮组首页
Route::get('/help', function () {
	return view('help/index');
});
//app首页文章列表页
Route::post('/xm_app_content','home\AdvertController@app_content');

//app首页文章列表页
Route::post('/xm_app_rumen','home\AdvertController@app_rumen');

//app首页文章列表页
Route::post('/xm_app_zhibiao','home\AdvertController@app_zhibiao');
