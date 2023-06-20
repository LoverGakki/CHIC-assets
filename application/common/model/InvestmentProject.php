<?php

namespace app\common\model;

use think\Model;

/**
 * 投资项目相关模型
 * Class InvestmentProject
 * @package app\common\model
 */
class InvestmentProject extends Model
{

    // 表名
    protected $name = 'investment_project';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //获取返利方式
    public function getRebateMethodType()
    {
        //1=日返（每日返还） 2=月返（每月1号返还） 3=定期返（额定日期后返还）
        return [
            1 => '每日返还',
            2 => '每月返还',
            3 => '定期返还',
        ];
    }

    //获取分红方式
    public function getdividendMethodType()
    {
        //分红方式  1=本息同返 2=先息后本 3=本息定期同返
        return [
            1 => '本息同返',
            2 => '先息后本',
            3 => '本息定期同返',
        ];
    }

    public function projectType()
    {
        return $this->belongsTo('ProjectType', 'project_type_id')->setEagerlyType(0);
    }

    /*public function projectLabel()
    {
        return $this->hasMany('AttributeValue', 'attr_name_id')->field('attr_name_id,value,attr_value_id');
    }*/

    public function investmentProjectLabel()
    {
        return $this->belongsToMany('ProjectLabel', 'InvestmentProjectLabel', 'label_id', 'project_id');
    }
}
