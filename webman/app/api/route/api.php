<?php

use Webman\Route;

Route::group('/api', function () {
    Route::any('/test', [\app\back\controller\TestController::class,'test']);//测试使用。不删除
    Route::any('/yin', [\app\api\controller\VideoController::class,'yin']);//测试使用。不删除

});