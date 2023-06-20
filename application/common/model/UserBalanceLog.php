<?php

namespace app\common\model;

use think\Model;

/**
 * 用户余额相关模型
 * Class UserBalanceLog
 * @package app\common\model
 */
class UserBalanceLog extends Model
{

    // 表名
    protected $name = 'user_balance_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function user()
    {
        return $this->belongsTo('User', 'user_id')->setEagerlyType(0);
    }

}
