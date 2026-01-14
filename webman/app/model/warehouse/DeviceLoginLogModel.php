<?php

namespace app\model\warehouse;

//设备在线更新日志
use app\model\fast\UserModel;
use support\Model;

class DeviceLoginLogModel extends Model
{
    protected $connection='data_warehouse';
    protected $table = 'device_login_log';
    public $timestamps = true;
    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'account',
        'time',
        'create_time',
        'update_time',
    ];

    //代理商
    public function userInfo()
    {
        return $this->hasOne(UserModel::class,'id','admin_id');
    }

}