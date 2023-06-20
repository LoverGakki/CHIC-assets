<?php

namespace app\common\model;

use think\Model;

/**
 * 用户等级配置相关模型
 * Class UserLevelConfig
 * @package app\common\model
 */
class UserLevelConfig extends Model
{

    // 表名
    protected $name = 'user_level_config';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
