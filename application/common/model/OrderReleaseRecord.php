<?php

namespace app\common\model;

use think\Model;

/**
 * 订单释放记录相关模型
 * Class OrderReleaseRecord
 * @package app\common\model
 */
class OrderReleaseRecord extends Model
{

    // 表名
    protected $name = 'order_release_record';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function user(){
        return $this->belongsTo('User','user_id')->setEagerlyType(0);
    }

    public function orderData(){
        return  $this->belongsTo('Order','order_id')->setEagerlyType(0);
    }
}
