<?php

namespace app\common\logic;

use think\Model;

/**
 * 前端版本控制相关
 * Class Version
 * @package app\common\logic
 */
class Version extends Model
{
    public $model = null;

    public $return = [
        'code' => 0,
        'msg' => '',
        'data' => null
    ];

    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->model = \think\Loader::model('WebVersion');
    }

    /**
     * 获取当前前端版本
     * @param $osName
     * @return array
     */
    public function getNowVersion($osName): array
    {
        $data = $this->model->where([
            'os_name' => $osName,
            'status' => 1
        ])->find();
        $row = [];
        if ($data) {
            //版本号
            $row['versionNum'] = $data['now_version'];
            //更新简述
            $row['updateInfo'] = $data['info'];
            //更新详情描述
            $row['updateDescription'] = $data['description'];
            //强制更新
            $row['forcedUpdate'] = $data['enforce'];
            //热更新
            $row['hotUpdate'] = $data['hot_update'];
            //下载链接
            $row['versionlink'] = $data['download_link'];
            $row['update_time'] = date('Y-m-d',$data['update_time']);
        }
        return $row;
    }

}