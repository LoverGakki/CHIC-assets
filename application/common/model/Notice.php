<?php

namespace app\common\model;

use think\Model;

/**
 * 公告相关模型
 * Class Notice
 * @package app\common\model
 */
class Notice extends Model
{

    // 表名
    protected $name = 'notice';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 追加属性
    protected $append = [
        'start_time_text',
        'end_time_text',
    ];

    protected static function init()
    {
        // 如果已经上传该资源，则不再记录
        self::beforeInsert(function (&$row) {
            if (isset($row['start_time_text']) && $row['start_time_text']) {
                $row['start_time'] = strtotime($row['start_time_text']);
            }

            if (isset($row['end_time_text']) && $row['end_time_text']) {
                $row['end_time'] = strtotime($row['end_time_text']);
            }
        });
        self::beforeUpdate(function (&$row) {
            if (isset($row['start_time_text']) && $row['start_time_text']) {
                $row['start_time'] = strtotime($row['start_time_text']);
            }

            if (isset($row['end_time_text']) && $row['end_time_text']) {
                $row['end_time'] = strtotime($row['end_time_text']);
            }
        });
    }

    public function getStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['start_time'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['end_time'] ?? "");
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

}
