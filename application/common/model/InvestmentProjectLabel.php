<?php

namespace app\common\model;

use think\Model;

/**
 * 投资项目与标签关联表
 * Class InvestmentProjectLabel
 * @package app\common\model
 */
class InvestmentProjectLabel extends Model
{

    // 表名
    protected $name = 'investment_project_label';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
