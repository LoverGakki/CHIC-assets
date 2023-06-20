<?php

namespace app\common\model;

use think\Model;

/**
 * 项目类型相关模型
 * Class ProjectType
 * @package app\common\model
 */
class ProjectType extends Model
{

    // 表名
    protected $name = 'project_type';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $row->save(['weigh' => $row['id']]);
        });
    }
}
