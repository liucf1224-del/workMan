<?php

namespace app\api\controller;

use Smalls\VideoTools\VideoManager;
use support\Request;

class VideoController
{
    public function yin(Request $request)
    {
        //https://www.douyin.com/?enter_recommend_method=item_non_existent_recommend_auto&page_url=https%3A%2F%2Fwww.douyin.com%2Fvideo%2F6934532526933380352&vid=7591772957077245224&recommend=1
//        $url = 'https://v.Douyin.com/eeYy4Yo';
//        VideoManager::DouYin()->start($url);
        try {
//            $url = 'https://www.bilibili.com/video/av84665662/';
////        B站：VideoManager::Bili()->start($url);
//            $res = VideoManager::Bili()->start($url);
//            dump($res);
            $data = [];
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
        return success($data);
    }
}