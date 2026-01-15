<?php

use Webman\Route;

Route::group('/api', function () {
    Route::any('/test', [app\api\controller\TestController::class, 'test']);//测试使用。不删除

});