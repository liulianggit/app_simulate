<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\bander;
use App\model\deal;
use App\model\cash;
use App\model\top;
use App\Http\Controllers\common\CommonController;

class TabController extends CommonController
{
	public function getcash()
	{
		//获取所有的代理id
		$banders = bander::select('pid')->groupBy('pid')->get();
		foreach ($banders as $k => $v) {
			$cumulate = 0;	//佣金
			//获取代理下的子id
			$suns = bander::select('bid')->where('pid',$v['pid'])->get();
			foreach ($suns as $kk => $vv) {
				$res = userinfo::where('uid',$vv['bid'])->first();//判断用户详情表中是否存在此用户，不存在则不是真实账户
				if($res && $res->is_true==2){//存在此用户，判断此用户是否为真实账户（此处暂为体验金账户，上线需更改）
					$cumulate += $this->getRecordById($vv['bid']);//计算此用户今日交易手数所返佣金累加给代理
				}
			}
			$top = top::where('uid',$v['pid'])->first();//排行表是否存在此代理
			if($top){//存在
				$top->cumulate = $cumulate;
				$top->add_time = time();
				$top->save();
			}else{//不存在
				$tmp = new top;
				$tmp->uid = $v['pid'];
				$tmp->cumulate = $cumulate;
				$tmp->add_time = time();
				$tmp->save();
			}
			$cash = cash::where('uid',$v['pid'])->first();//佣金表是否存在此用户
			// var_dump($cash);
			if($cash){//存在
				$cash->balance += $cumulate;	//可提现金额
				$cash->cumulate += $cumulate;	//累计佣金
				$cash->up_time = time();	//修改时间
				$cash->save();
			}else{//不存在
				// echo $cumulate.'<br/>';
				$cmp = new cash;
				$cmp->uid = $v['pid'];
				$cmp->balance = $cumulate;
				$cmp->cumulate = $cumulate;
				$cmp->freeze = 0;
				$cmp->up_time = time();
				$cmp->save();
			}

		}
	}

	/**
     * 根据id获取该用户昨日总交易手数
     */
    public function getRecordById($bid)
    {
    	//获取昨天早上5点的时间戳
		$start_time = mktime(5,0,0,date('m'),date('d')-1,date('Y')); 
		//获取今日5点的时间戳
		$end_time = mktime(5,0,0,date('m'),date('d'),date('Y')); 
        $deal = new deal;
        $record = 0;	//累计返佣
        $result = $deal->where('uid',$bid)->whereBetween('add_time',[$start_time,$end_time])->get();//获取此用户今日交易列表

        if(!$result || count($result)==0){
            return $record;
        }else{
        	foreach ($result as $k => $v) {
        		$record += $v['wai']*2 + $v['gold']*3 + $v['cfd']*1 + $v['yin']*10 + $v['dollar']*0.2 + $v['energy']*2;
        	}
        }
        return $record;
    }
}