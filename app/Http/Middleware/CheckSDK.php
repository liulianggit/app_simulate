<?php

namespace App\Http\Middleware;

use Closure;
use App\model\aes;

class CheckSDK
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!isset($request->sign_key) || !$this->checkSign($request->sign_key)){
            $data = [];
            $data['code'] = '10086';
            $data['message'] = '请求接口失败！';
            $data['data'] = '';
            return response()->json($data);
        }else{
            return $next($request);
        }
    }

    /**
    * @param string $token_key | 私有key
    *
    * @return string 签名
    */
    function checkSign($encryptedText) 
    {
        $token_key = config('app.token_key');
        $token_iv = config('app.token_iv');
        $token_str = config('app.token_str');
        $aes = new aes;
        $rtoken_str = $aes->aes256cbcDecrypt($encryptedText, $token_iv, $token_key);
        if($token_str === $rtoken_str){
            return true;
        }else{
            return false;
        }
    }
}
