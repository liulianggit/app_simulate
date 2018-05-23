<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use App\model\users;
use App\jpush\Jpush;
use App\model\UserInfo;
class CommonController extends Controller
{
    function __construct() {
        // parent::__construct();

        // $this->checkToken(); // 检查token方法

    }

    function get_input($str)
    {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        return $str;
    }
    //返回json数据
    public function jsonOut($code,$message,$result=NULL){
        $data['code']=$code;//错误码
        $data['message']=urlencode($message);//错误信息
        $data['data']=$result;//返回的数据
        return urldecode(json_encode($data,true));
        exit;
    }

   public function getSign($private_key, $params)
    {
        ksort($params); # 参数串排序
        $params_data = "";
        foreach($params as $key => $value) {
            $params_data .= $key;
            $params_data .= $value;
        }
        $params_data .= $private_key;
        return md5($params_data); # 生成的sign值
    }
    //https请求
    function curl_request($mothed, $url, $params = array())
    {
        $is_post = false;
        if(strtolower($mothed) == "post"){
            $is_post = true;
        }

        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        $ch = curl_init();
        if(strtolower($mothed) == "put"){
            $is_post = true;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }else{
            curl_setopt($ch, CURLOPT_POST, $is_post);
        }

        if($is_post){
            $fields_string = http_build_query ( $params, '&');
            curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
        } else if(!empty($params)){

            $url = rtrim($url,"/?");
            $uri = http_build_query($params);
            $url = "{$url}?$uri";
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        if($ssl){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }
        curl_setopt($ch, CURLOPT_HEADER, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");

        $content = curl_exec($ch);

        curl_close($ch);

        return $content;
    }
//推送
    function ujpush($content = "",$type='',$receive="",$txt =""){
        $app_key = '70d4acb670a1cd7fb8dfbde1';
        $master_secret = 'bb533d1cf036224b68abbc08';
        $pushObj = new Jpush($app_key,$master_secret);
        //组装需要的参数
        if(empty($receive))$receive = 'all';     //全部
        //调用推送,并处理
        $result = $pushObj->push($receive,$content,$type,$txt);
        //var_dump($result);exit;
        //logDebug($result."\r\n".$content);
        if($result){
            $res_arr = json_decode($result, true);
            if(isset($res_arr['error'])){//如果返回了error则证明失败
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    //推送给某人
    function send_push($user_id,$msg){
        $userinfo=UserInfo::where("uid",$user_id)->first();
        if($userinfo) {
            $receiver = "";
            $device_token = $userinfo->device_token;
            /**/
            //var_dump($device_token);exit;
            if (!empty($device_token)) {
                $receiver['registration_id'] = array($device_token);
                $this->ujpush($msg, '', $receiver);
            }
        }
    }

    

    /**
     * 发送请求给融云
     * @param  [type] $action      [description]
     * @param  [type] $params      [description]
     * @param  string $contentType [description]
     * @param  string $module      [description]
     * @param  string $httpMethod  [description]
     * @return [type]              [description]
     */
    public function send_curl($action, $params,$contentType='urlencoded',$module = 'im',$httpMethod='POST') {
        
        $action = 'http://api.cn.ronghub.com'.$action;
        $httpHeader = $this->createHttpHeader();
        $ch = curl_init();
        if ($httpMethod=='POST' && $contentType=='urlencoded') {
            $httpHeader[] = 'Content-Type:application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_query($params));
        }
        if ($httpMethod=='POST' && $contentType=='json') {
            $httpHeader[] = 'Content-Type:Application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params) );
        }
        curl_setopt($ch, CURLOPT_URL, $action);
        curl_setopt($ch, CURLOPT_POST, $httpMethod=='POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false); //处理http证书问题
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        if (false === $ret) {
            $ret =  curl_errno($ch);
        }
        curl_close($ch);
        return $ret;
    }

    /**
     * 创建http header参数
     * @param array $data
     * @return bool
     */
    private function createHttpHeader() {
        $appSecret="Tcn7h0IfpRj";
        srand((double)microtime()*1000000);
        $nonce = rand();
        $timeStamp = time()*1000;
        $sign = sha1($appSecret.$nonce.$timeStamp);
        return array(
                'RC-App-Key:'.'mgb7ka1nm424g',
                'RC-Nonce:'.$nonce,
                'RC-Timestamp:'.$timeStamp,
                'RC-Signature:'.$sign,
        );
    }

    /**
     * 重写实现 http_build_query 提交实现(同名key)key=val1&key=val2
     * @param array $formData 数据数组
     * @param string $numericPrefix 数字索引时附加的Key前缀
     * @param string $argSeparator 参数分隔符(默认为&)
     * @param string $prefixKey Key 数组参数，实现同名方式调用接口
     * @return string
     */
    private function build_query($formData, $numericPrefix = '', $argSeparator = '&', $prefixKey = '') {
        $str = '';
        foreach ($formData as $key => $val) {
            if (!is_array($val)) {
                $str .= $argSeparator;
                if ($prefixKey === '') {
                    if (is_int($key)) {
                        $str .= $numericPrefix;
                    }
                    $str .= urlencode($key) . '=' . urlencode($val);
                } else {
                    $str .= urlencode($prefixKey) . '=' . urlencode($val);
                }
            } else {
                if ($prefixKey == '') {
                    $prefixKey .= $key;
                }
                if (isset($val[0]) && is_array($val[0])) {
                    $arr = array();
                    $arr[$key] = $val[0];
                    $str .= $argSeparator . http_build_query($arr);
                } else {
                    $str .= $argSeparator . $this->build_query($val, $numericPrefix, $argSeparator, $prefixKey);
                }
                $prefixKey = '';
            }
        }
        return substr($str, strlen($argSeparator));
    }

}
