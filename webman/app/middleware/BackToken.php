<?php

namespace app\middleware;

use Firebase\JWT\JWT;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

//后续后台登录的jwt的token业务
class BackToken implements MiddlewareInterface
{
    public function process($request, callable $handler): Response
    {
        try {
            $token=$request->header('authorization');
            if(empty($token)){
                return error('请登录token标识不存在', [], 401);
            }else{
                $userdata = JWT::decode(str_replace('Bearer ','',$token),new \Firebase\JWT\Key(env('JWT_KEY'),'HS256'));
                if ($userdata->login_time < time()) {
                    return error('登录已失效，请重新登录!',[],401);
                }
                $data = [
                    'id'=>$userdata->id??0,
                    'code'=>$userdata->code??0,
                    'club_id'=>$userdata->id,
                    'name'=>$userdata->name??null,
                ];
            }
            $request->user_data=$data;
        }catch (\Exception $exception){
            return  error($exception->getMessage());
        }
        /** @var Response $response */
        $response = $handler($request);
        return $response;
    }

}