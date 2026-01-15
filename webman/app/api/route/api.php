<?php

use Webman\Route;

Route::group('/back', function () {
    Route::any('/test', [\app\back\controller\TestController::class,'test']);//测试使用。不删除

});