<?php

namespace app\common\model;

use think\Model;

/**
 * 用户优惠券相关模型
 * Class UserBalanceLog
 * @package app\common\model
 */
class UserCoupons extends Model
{

    // 表名
    protected $name = 'user_coupons';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function user(){
        return $this->belongsTo('user','user_id')->setEagerlyType(0);
    }
    public function projectType(){
        return $this->belongsTo('projectType','project_type_id');
    }

}
