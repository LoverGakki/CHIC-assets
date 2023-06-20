<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use think\Exception;
use app\common\library\Log;

/**
 * 用户银行卡相关
 * Class BankCard
 * @package app\common\logic
 */
class BankCard extends Model
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
        $this->model = \think\Loader::model('UserBankCard');
    }

    /**
     * 用户绑定银行卡
     * @param $userId
     * @param $params
     * @return array
     */
    public function bindBankCard($userId, $params): array
    {
        $return = $this->return;
        //判断银行卡是否已存在
        $userCardData = $this->model->where([
            'user_id' => $userId,
            'bank_name' => $params['bank_name'],
            'card_number' => $params['card_number'],
            'status' => 1
        ])->find();
        Db::startTrans();
        try {
            if ($userCardData) {
                throw new Exception('The bank card is linked', 1);
            }
            $params['user_id'] = $userId;
            $params['status'] = 1;
            if (!$this->model->save($params)) {
                throw new Exception('用户：' . $userId . '：银行卡绑定失败');
            }

            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('bindBankCard：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }


    /**
     * 用户删除绑定银行卡
     * @param $userId
     * @param $userBankCardId
     * @return array
     */
    public function userDelBankCard($userId, $userBankCardId): array
    {
        $return = $this->return;
        $userCardData = $this->model->where([
            'user_bank_card_id' => $userBankCardId,
            'user_id' => $userId
        ])->find();
        if (!$userCardData) {
            $return['msg'] = '银行卡不存在';
            return $return;
        }
        if (!$userCardData->delete()) {
            $return['msg'] = '删除错误';
            return $return;
        }

        $return['code'] = 1;
        $return['msg'] = 'success';
        return $return;
    }

    /**
     * 用户获取银行卡列表
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function userBankCardList($userId, $page, $pageSize): array
    {
        $limit = ($page - 1) * $pageSize;
        $total = $this->model
            ->with('user')
            ->where([
                'user_id' => $userId,
                'user_bank_card.status' => 1
            ])
            ->count();
        $list = $this->model
            ->with('user')
            ->where([
                'user_id' => $userId,
                'user_bank_card.status' => 1
            ])
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['user_bank_card_id'];
                $row[$k]['bank_name'] = $v['bank_name'];
                $row[$k]['card_number'] = $v['card_number'];
                $row[$k]['card_holder_name'] = $v['user']['real_name'];
                $row[$k]['account_bank_site'] = $v['account_bank_site'];
                $row[$k]['status'] = $v['status'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }


}