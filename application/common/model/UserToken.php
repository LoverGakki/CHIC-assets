<?php

namespace app\common\model;

use think\Model;

class UserToken extends Model
{

    // 表名
    protected $name = 'user_token';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';

}
