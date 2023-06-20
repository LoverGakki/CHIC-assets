<?php

namespace app\common\model;

use think\Model;

class DividendCommissionConfig extends Model
{

    // 表名
    protected $name = 'dividend_commission_config';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
