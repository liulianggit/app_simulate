<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\model\trade;
use App\model\lev;
use App\model\bander;
use App\model\deal;
use App\model\notice;
use App\Http\Controllers\common\CommonController;

class TradeController extends CommonController
{

    /**
     * 获取开仓信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_trade_open(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $cmd = $request->input('cmd');
        $cmd = $this->get_input($cmd);
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }else if($cmd == ''){
            return $this->jsonOut('5','请传入cmd');
        }
        $params = $request->except('tp','sl','uid');
        foreach ($params as $key => $value) {
            if($value == ''){
                return $this->jsonOut('2','请传入必要参数');
            }
        }
        if($request->has('tp') && $request->input('tp') != null){
            $tp = $request->input('tp');
        }else{
            $tp = 0;
        }
        if($request->has('sl') && $request->input('sl') != null){
            $sl = $request->input('sl');
        }else{
            $sl = 0;
        }
        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'trade_open';
                $params['user_id']        = $uid;
                $params['cmd']        = $cmd;
                $params['tp']        = $tp;
                $params['sl']        = $sl;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/trade/open',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $this->get_trade_info($uid,$info->mt4_id,$res['data']['ticket']);
                    if(!is_array($data)){
                        return $this->jsonOut('7',$data);
                    }
                    $data1 = $this->get_symbols_info($data['symbol']);
                    if(!is_array($data1)){
                        return $this->jsonOut('7',$data1);
                    }
                    $data = array_merge($data,$data1);
                    $data['net_profit'] = 0;
                    $data['status'] = 1;
                    $data['profit_currency'] = $data['profit_calc_currency'];
                    $data['contract_size'] = (string)$data['contract_size'];
                    //如果是真实账户，判断有没有上级代理
                    if($info->is_true == 2){//此处暂为模拟，需更改
                        $tmp = bander::where('bid',$uid)->first();
                        if($tmp){
                            $tp = lev::where('symbol','like','%'.substr($data['symbol'], 0,6).'%')->first();
                            $dl = deal::where('uid',$uid)->first();
                            //将用户交易手数累加入所属种类中
                            $deal = new deal;
                            $deal->uid = $uid;
                            $deal->real = $info->mt4_id;
                            $deal->pid = $tmp['pid'];
                            $deal->ticket = $data['ticket'];
                            $deal->add_time = time();
                            if($tp['lev'] == 'wai'){
                                $deal->wai = $data['volume'];
                            }else if($tp['lev'] == 'dollar'){
                                $deal->dollar = $data['volume'];
                            }else if($tp['lev'] == 'gold'){
                                $deal->gold = $data['volume'];
                            }else if($tp['lev'] == 'yin'){
                                $deal->yin = $data['volume'];
                            }else if($tp['lev'] == 'energy'){
                                $deal->energy = $data['volume'];
                            }else{
                                $deal->cfd = $data['volume'];
                            }
                            $deal->save();
                            
                        }
                    }
                    //写入交易表
                    $trade=new trade();
                    $trade->uid=$uid;
                    $trade->price_record=$res['data']['open_price'];
                    $trade->ticket=$res['data']['ticket'];
                    $trade->volume=$res['data']['volume'];
                    $trade->cmd=$res['data']['cmd'];
                    $trade->symbol=$res['data']['symbol'];
                    $trade->add_time=time();
                    $trade->save();
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 获取平仓信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_trade_close(Request $request)
    {
        
        $params = $request->except('sign_key','uid');
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }
        foreach ($params as $key => $value) {
            if($value == ''){
                return $this->jsonOut('2','请传入必要参数');
            }
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'trade_close';
                $params['mt4_id'] = $info->mt4_id;
                $params['user_id'] = $uid;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/trade/close',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $tmp = $this->trade_info($uid,$params['ticket'],$info->mt4_id);
                    if(!is_array($tmp)){
                        return $this->jsonOut('7',$tmp);
                    }
                    $data = array_merge($res['data'],$tmp);
                    $trade = new trade;
                    $trade->uid = $uid;
                    $trade->t_type = 'pingcang';
                    $trade->ticket = $data['ticket'];
                    $trade->cmd = $data['cmd'];
                    $trade->symbol = $data['symbol'];
                    $trade->amount = $data['profit'];
                    $trade->add_time = time();
                    $trade->save();
                    //写入交易提醒表
                    $notice = new notice();
                    $notice->uid = $uid;
                    $notice->add_time = time();
                    $notice->type = 1;
                    $notice->title = '平仓成功';
                    $notice->money = $data['profit'];
                    $notice->order_no=$data['ticket'];
                    $notice->trade_type=$data['symbol'];
                    $notice->save();

                    return $this->jsonOut('1','平仓成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 修改订单止盈止损
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trade_update(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $ticket = $request->input('ticket');
        $ticket = $this->get_input($ticket);
        if($request->has('tp') && $request->input('tp') != null){
            $tp = $request->input('tp');
        }else{
            $tp = 0;
        }
        if($request->has('sl') && $request->input('sl') != null){
            $sl = $request->input('sl');
        }else{
            $sl = 0;
        }
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }else if(empty($ticket)){
            return $this->jsonOut('5','请传入订单号');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'trade_update';
                $params['mt4_id'] = $info->mt4_id;
                $params['user_id'] = $uid;
                $params['ticket'] = $ticket;
                $params['tp'] = $tp;
                $params['sl'] = $sl;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/trade/update',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 挂单交易接口
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function pending_trade_add(Request $request)
    {
        $params = $request->except(['sign_key','uid','tp','sl','expiration_time']);
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }
        foreach ($params as $key => $value) {
            if($value == ''){
                return $this->jsonOut('2','请传入必要参数');
            }
        }
        if($request->has('tp') && $request->input('tp') != null){
            $tp = $request->input('tp');
        }else{
            $tp = 0;
        }
        if($request->has('sl') && $request->input('sl') != null){
            $sl = $request->input('sl');
        }else{
            $sl = 0;
        }
        $expiration_time = $request->input('expiration_time');
        if($expiration_time == ''){
            return $this->jsonOut('5','请传入过期时间');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'pending_trade_add';
                $params['mt4_id'] = $info->mt4_id;
                $params['user_id'] = $uid;
                $params['expiration_time'] = $expiration_time;
                $params['tp'] = $tp;
                $params['sl'] = $sl;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                // return $this->jsonOut('2','测试',$params);
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/pending_trade/add',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $this->get_trade_info($uid,$info->mt4_id,$res['data']['ticket']);
                    if(!is_array($data)){
                        return $this->jsonOut('7',$data);
                    }
                    $data1 = $this->get_symbols_info($data['symbol']);
                    if(!is_array($data1)){
                        return $this->jsonOut('7',$data1);
                    }
                    $data = array_merge($data,$data1);
                    $data['net_profit'] = 0;
                    $data['status'] = 1;
                    $data['volume'] = (string)$data['volume'];
                    $data['profit_currency'] = $data['profit_calc_currency'];
                    $data['contract_size'] = (string)$data['contract_size'];
                    //如果是真实账户，判断有没有上级代理
                    if($info->is_true == 2){//此处暂为代理，需更改
                        $tmp = bander::where('bid',$uid)->first();
                        if($tmp){
                            $tp = lev::where('symbol','like','%'.substr($data['symbol'], 0,6).'%')->first();
                            $dl = deal::where('uid',$uid)->first();
                            //将用户交易手数累加入所属种类中
                            $deal = new deal;
                            $deal->uid = $uid;
                            $deal->real = $info->mt4_id;
                            $deal->pid = $tmp['pid'];
                            $deal->ticket = $data['ticket'];
                            $deal->add_time = time();
                            if($tp['lev'] == 'wai'){
                                $deal->wai = $data['volume'];
                            }else if($tp['lev'] == 'dollar'){
                                $deal->dollar = $data['volume'];
                            }else if($tp['lev'] == 'gold'){
                                $deal->gold = $data['volume'];
                            }else if($tp['lev'] == 'yin'){
                                $deal->yin = $data['volume'];
                            }else if($tp['lev'] == 'energy'){
                                $deal->energy = $data['volume'];
                            }else{
                                $deal->cfd = $data['volume'];
                            }
                            $deal->save();
                            
                        }
                    }
                    //写入交易表
                    $trade=new trade();
                    $trade->uid=$uid;
                    $trade->price_record=$res['data']['open_price'];
                    $trade->ticket=$res['data']['ticket'];
                    $trade->volume=$res['data']['volume'];
                    $trade->cmd=$res['data']['cmd'];
                    $trade->symbol=$res['data']['symbol'];
                    $trade->add_time=time();
                    $trade->save();
                    //写入交易提醒表
                    $notice = new notice();
                    $notice->uid = $uid;
                    $notice->add_time = time();
                    $notice->type = 2;
                    if($res['data']['cmd']==2) {
                        $notice->title = '挂单买入通知';
                        $notice->order_direct="挂单买入";
                    }elseif($res['data']['cmd']==3){
                        $notice->title = '挂单卖出通知';
                        $notice->order_direct="挂单卖出";
                    }elseif($res['data']['cmd']==4){
                        $notice->title = '挂单追涨通知';
                        $notice->order_direct="挂单追涨";
                    }else{
                        $notice->title = '挂单追空通知';
                        $notice->order_direct="挂单追空";
                    }
                    $notice->money = $res['data']['open_price'];
                    $notice->trade_type=$res['data']['symbol'];
                    $notice->content="您的挂单交易已成交";
                    $notice->order_num=$res['data']['volume'];
                    $notice->save();
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 修改挂单
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function pending_trade_update(Request $request)
    {
        $params = $request->except(['sign_key','uid','tp','sl','expiration_time']);
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }
        foreach ($params as $key => $value) {
            if($value == ''){
                return $this->jsonOut('2','请传入必要参数');
            }
        }
        if($request->has('tp') && $request->input('tp') != null){
            $tp = $request->input('tp');
        }else{
            $tp = 0;
        }
        if($request->has('sl') && $request->input('sl') != null){
            $sl = $request->input('sl');
        }else{
            $sl = 0;
        }
        $expiration_time = $request->input('expiration_time');
        if($expiration_time == ''){
            return $this->jsonOut('5','请传入过期时间');
        }
        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'pending_trade_update';
                $params['mt4_id'] = $info->mt4_id;
                $params['user_id'] = $uid;
                $params['expiration_time'] = $expiration_time;
                $params['tp'] = $tp;
                $params['sl'] = $sl;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/pending_trade/update',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];

                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 删除挂单
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function pending_trade_delete(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $ticket = $request->input('ticket');
        $ticket = $this->get_input($ticket);
        if(empty($uid)){
            return $this->jsonOut('4','请传入用户id');
        }else if(empty($ticket)){
            return $this->jsonOut('5','请传入订单号');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'pending_trade_delete';
                $params['mt4_id'] = $info->mt4_id;
                $params['user_id'] = $uid;
                $params['ticket'] = $ticket;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/pending_trade/delete',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    //如果是真实账户，判断有没有上级代理
                    if($info->is_true == 2){//此处暂为代理，需更改
                        $tmp = bander::where('bid',$uid)->first();
                        if($tmp){
                            //将用户交易记录删除
                            deal::where('uid',$uid)->where('ticket',$data['ticket'])->delete();
                        }
                    }
                    //删除交易表
                     $trade=new trade();
                     $trade->where('uid',$uid)->where('ticket',$ticket)->delete();
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 获取持仓、平仓订单详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_trade_info($uid,$mt4_id,$ticket)
    {
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'centre_active_trades';
        $params['user_id']        = $uid;
        $params['mt4_id'] = $mt4_id;
        $params['ticket'] = $ticket;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trade/info',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            $data['open_time'] = date('Y-m-d H:i:s',$data['open_time']);
            if($data['close_time']){
                $data['close_time'] = date('Y-m-d H:i:s',$data['close_time']);
            }else{
                $data['close_time'] = '持仓中';
            }
            return $data;
        }else{
            return $res['message'];
        }
    }

    /**
     * 获取单个交易品种详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_symbols_info($symbol)
    {
        
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_info';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/info',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $data;
        }else{
            return $res['message'];
        }
    }

    /**
     * 平仓后获取占用保证金
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trade_info($uid,$ticket,$mt4_id)
    {
        
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'centre_active_trades';
        $params['user_id']        = $uid;
        $params['mt4_id'] = $mt4_id;
        $params['ticket'] = $ticket;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trade/info',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            $data['open_time'] = date('Y-m-d H:i:s',$data['open_time']);
            $data['close_time'] = date('Y-m-d H:i:s',$data['close_time']);
            return $data;
        }else{
            return $res['message'];
        }
    }

}