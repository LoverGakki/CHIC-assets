<?php

namespace app\common\model;

use think\Model;

/**
 * banner相关模型
 * Class Banner
 * @package app\common\model
 */
class Banner extends Model
{

    // 表名
    protected $name = 'banner';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
