<?php

namespace App\Http\Controllers\home;
use App\Http\Controllers\common\CommonController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\region;
use App\model\data;
use Illuminate\Http\Concerns\InteractsWithInput;
class RegionController extends CommonController
{
    /**
     * 省市二级联动
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_region(Request $request)
    {

        $region = new region();//示例化轮播图表
        //查询需要的数据
        $region_id=$request->input('region_id');
        if(empty($region_id))
        {
            $data = $region->where('parent_id',1)->get(['region_id', 'region_name'])->toArray();
        }elseif($region_id==2)
        {
            $data = $region->where('parent_id',3)->get(['region_id', 'region_name'])->toArray();
        }elseif($region_id==2622)
        {
            $data = $region->where('parent_id',2623)->get(['region_id', 'region_name'])->toArray();
        }elseif($region_id==2845)
        {
            $data = $region->where('parent_id',2846)->get(['region_id', 'region_name'])->toArray();
        }
        elseif($region_id==3314)
        {
            $data = $region->where('parent_id',3315)->get(['region_id', 'region_name'])->toArray();
        }else
        {
            $data = $region->where('parent_id',$region_id)->get(['region_id', 'region_name'])->toArray();
         }
       if($data){
           return $this->jsonOut('1', '获取成功', $data);
           } else {
            return $this->jsonOut('0', '暂无数据');
        }
    }

    /**
     * 新web的导航标题
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_title(Request $request)
    {
        header("Access-Control-Allow-Origin: *");
        $data = new data();//示例化轮播图表
        //查询需要的数据
        $id=$request->input('id');
        if(empty($id))
        {
            $dat = $data->where('pid',10)->get(['id', 'title'])->toArray();
        }else
        {
            $dat = $data->where('pid',$id)->get(['id', 'title'])->toArray();
        }
        if($dat){
            return $this->jsonOut('1', '获取成功', $dat);
        } else {
            return $this->jsonOut('0', '暂无数据');
        }
    }
    /**
     * 新web的导航标题全部
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_title_all(Request $request)
    {
        header("Access-Control-Allow-Origin: *");
        $data = new data();//示例化轮播图表
        //查询需要的数据
        $dat = $data->where('pid','>',0)->get(['id', 'title','pid'])->toArray();
        if($dat){
            return $this->jsonOut('1', '获取成功', $dat);
        } else {
            return $this->jsonOut('0', '暂无数据');
        }
    }
}
