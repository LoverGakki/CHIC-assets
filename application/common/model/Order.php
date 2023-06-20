<?php

namespace app\common\model;

use think\Model;

/**
 * 订单相关模型
 * Class Notice
 * @package app\common\model
 */
class Order extends Model
{

    // 表名
    protected $name = 'order';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function orderReleaseRecord()
    {
        return $this->hasMany('OrderReleaseRecord', 'order_id');
    }

    /*public function investmentProject(){
        return $this->belongsTo('InvestmentProject','project_id')->setEagerlyType(0);
    }*/

    public function user(){
        return $this->belongsTo('User','user_id')->setEagerlyType(0);
    }

}
