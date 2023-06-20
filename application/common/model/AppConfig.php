<?php

namespace app\common\model;

use think\Model;

/**
 * 系统配置相关模型
 * Class AppConfig
 * @package app\common\model
 */
class AppConfig extends Model
{

    // 表名
    protected $name = 'app_config';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 根据唯一标识获取配置值
     * @param $only_tag
     * @return float|mixed|string
     */
    public static function getConfigDataValue($only_tag)
    {
        return self::where('only_tag', $only_tag)->value('value');
    }

}
