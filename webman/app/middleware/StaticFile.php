<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * Class StaticFile
 * @package app\middleware
 */
class StaticFile implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // Access to files beginning with. Is prohibited
//        if (strpos($request->path(), '/.') !== false) {
//            return response('<h1>403 forbidden</h1>', 403);
//        }
        // 处理预检请求(OPTIONS)
        if ($request->method() === 'OPTIONS') {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin' => $request->header('origin', '*'),
                'Access-Control-Allow-Methods' => $request->header('access-control-request-method', '*'),
                'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', '*'),
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => 86400,
            ]);
        }
        /** @var Response $response */
        $response = $handler($request);
        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $request->header('origin', '*'),
            'Access-Control-Allow-Methods' => $request->header('access-control-request-method', '*'),
            'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', '*'),
        ]);
        // Add cross domain HTTP header
        /*$response->withHeaders([
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);*/
        return $response;
    }
}
