<?php

namespace app\common\model;

use think\Model;

/**
 * 项目标签相关模型
 * Class ProjectLabel
 * @package app\common\model
 */
class ProjectLabel extends Model
{

    // 表名
    protected $name = 'project_label';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
