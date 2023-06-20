<?php

namespace app\common\model;

use think\Model;

/**
 * 新闻相关模型
 * Class Banner
 * @package app\common\model
 */
class News extends Model
{

    // 表名
    protected $name = 'news';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 获取新闻类型
     * @return string[]
     */
    public function getNewsType(): array
    {
        return [
            1 => '新闻', 2 => '视频', 3 => '行业'
        ];
    }

}
