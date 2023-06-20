<?php

namespace app\common\model;

use think\Model;

/**
 * 用户签到记录相关模型
 * Class UserSignRecord
 * @package app\common\model
 */
class UserSignRecord extends Model
{

    // 表名
    protected $name = 'user_sign_record';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}
