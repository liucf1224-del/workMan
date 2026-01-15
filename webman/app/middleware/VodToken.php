<?php

namespace app\middleware;

use Respect\Validation\Validator;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * vod通用校验逻辑
 */
class VodToken implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        try {
            $param = $request->all();
//            dump($param);
            Validator::input($param, [
                'sign' => Validator::notEmpty()->setName('请输入sign'),
                'timestamp' => Validator::notEmpty()->setName('时间戳'),
            ]);
            $param['appid'] = env('APPID','CP9p3q3Ah0N0A364VSCm0MiP4RpaT12Y');
            $filteredParams = array_filter($param, function($key) {
                return $key !== 'sign';
            }, ARRAY_FILTER_USE_KEY);
//            dump("=================");
//            dump($filteredParams);

            // 2. 按键名排序
            ksort($filteredParams);
            // 3. 用&符号连接键值对
//            $queryString = http_build_query($filteredParams, '', '&');
            $queryString = http_build_query($filteredParams, '', '&',PHP_QUERY_RFC3986);
            $queryString .= '&key='.env('Key','vLU0NZBGq80bYQWruCToL0Q8cYcSJOjC');
//            dump($queryString);
            // 4. 生成md5值
            $newString = strtolower(md5($queryString));
//            dump($newString);
            $token = $param['sign'] ?? '';
            if (!$token || $token != $newString) {
                throw new \Exception('请不要非法请求!');
            }
            // 验证时间戳 $param['time'] +60分钟小于 当前时间的话就报错 验签过期
            $future_timestamp = $param['timestamp']+ 3600;
            if (time() > $future_timestamp) {
                throw new \Exception('验签过期!');
            }

        }catch (\Exception $exception){
            return  error($exception->getMessage());
        }
        /** @var Response $response */
        $response = $handler($request);
        return $response;
    }

}