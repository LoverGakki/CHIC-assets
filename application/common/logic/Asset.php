<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use think\Exception;
use fast\Random;
use app\common\library\Log;
use app\common\library\Auth;
use app\common\model\User;
use app\common\model\AppConfig;
use app\common\model\ExtractOrder;
use app\common\model\UserBankCard;

/**
 * 用户资产相关
 * Class Asset
 * @package app\common\logic
 */
class Asset extends Model
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
        $this->model = \think\Loader::model('UserBalanceLog');
    }

    /**
     * 获取账户明细
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getUserAssetList($userId, $page, $pageSize): array
    {
        $limit = ($page - 1) * $pageSize;
        $total = $this->model
            ->where([
                'user_id' => $userId
            ])
            ->count();
        $list = $this->model
            ->where([
                'user_id' => $userId
            ])
            ->order('create_time', 'desc')
            ->limit($limit, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['id'];
                $row[$k]['money'] = $v['money'];
                //操作类型 1=加 2=减
                $row[$k]['operate_type'] = $v['operate_type'];
                //变更类型 变更类型  1=签到赠送，2=用户充值，3=投资扣除，4=返还利息，5=返还本金，6=提现扣除
                $row[$k]['change_type'] = $v['change_type'];
                $row[$k]['out_mobile'] = $v['out_mobile'];
                $row[$k]['in_mobile'] = $v['in_mobile'];
                $row[$k]['memo'] = $v['memo'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 获取提现手续比例
     * @return int[]
     */
    public function getExtractServiceChargeRatio(): array
    {
        $ratio = AppConfig::getConfigDataValue('withdraw_service_charge_ratio');
        return [
            'ratio' => $ratio ?: 10
        ];
    }

    /**
     * 提取申请
     * @param $userData
     * @param $userBankCardId
     * @param $applyAmount
     * @param $safePwd
     * @return array
     */
    public function extractApply($userData, $userBankCardId, $applyAmount, $safePwd): array
    {
        $return = $this->return;
        //获取提取手续费比例
        $extractRatioData = $this->getExtractServiceChargeRatio();
        $extract_ratio = $extractRatioData['ratio'];
        $extractMinValue = AppConfig::getConfigDataValue('user_withdraw_min_value');
        $extractMinValue = $extractMinValue > 0 ? $extractMinValue : 100;
        Db::startTrans();
        try {
            //校验安全密码是否正确
            if ($userData->safe_password != (new Auth())->getEncryptPassword($safePwd, $userData->safe_salt)) {
                throw new Exception('The security password is incorrect', 1);
            }
            if ($applyAmount < $extractMinValue) {
                throw new Exception('最低提现金额为' . $extractMinValue, 1);
            }
            //获取用户银行卡数据
            $userBankCardData = (new UserBankCard())->where([
                'user_bank_card_id' => $userBankCardId,
                'user_id' => $userData['id'],
            ])->find();
            if (empty($userBankCardData)) {
                throw new Exception('用户银行卡数据不存在', 1);
            }
            $extractOrderModel = new ExtractOrder();
            //操作金额
            $operate_value = $applyAmount;
            //实际到账金额
            $actual_into_value = number_get_value($operate_value - ($operate_value * $extract_ratio / 100));
            if (!$extractOrderModel->save([
                'user_id' => $userData['id'],
                'order_code' => date('YmdHi') . Random::numeric(6),
                'extract_amount' => $operate_value,
                'extract_ratio' => $extract_ratio,
                'actual_into_value' => $actual_into_value,
                'user_bank_card_id' => $userBankCardId,
                'receive_bank' => $userBankCardData['bank_name'],
                'receive_card_number' => $userBankCardData['card_number'],
                'receive_card_hold_name' => $userData['real_name'],
                'receive_bank_site' => $userBankCardData['account_bank_site'],
                'audit_status' => 0,
                'status' => 1,
                'remark' => '提取申请',
            ])) {
                throw new Exception('用户：' . $userData['id'] . '：申请提取错误');
            }
            $extractRecordId = $extractOrderModel->getLastInsID();
            //扣除余额
            Common::changeUserTokenValue($userData, 'token_value', 2, 6, $operate_value, $extractRecordId);

            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('extractApply：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }

    /**
     * 获取提现申请记录
     * @param $userId
     * @param $page
     * @param $pageSize
     * @return array
     * @throws Exception
     */
    public function getExtractApplyList($userId, $page, $pageSize): array
    {
        $extractOrderModel = new ExtractOrder();
        $total = $extractOrderModel
            ->where([
                'user_id' => $userId
            ])
            ->count();
        $list = $extractOrderModel
            ->where([
                'user_id' => $userId
            ])
            ->order('create_time', 'desc')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $row = [];
        if ($list) {
            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $row[$k]['id'] = $v['extract_order_id'];
                $row[$k]['extract_amount'] = $v['extract_amount'];
                $row[$k]['status'] = $v['status'];
                $row[$k]['bank_name'] = $v['receive_bank'];
                $row[$k]['card_holder_name'] = $v['receive_card_hold_name'];
                $row[$k]['card_number'] = $v['receive_card_number'];
                $row[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
                $row[$k]['processing_time'] = $v['processing_time'] ? date('Y-m-d H:i:s', $v['processing_time']) : '';
                $row[$k]['remark'] = $v['remark'];
            }
        }
        return [
            'total' => $total,
            'rows' => $row
        ];
    }

    /**
     * 余额转账
     * @param $userData
     * @param $acceptMobile
     * @param $transferAmount
     * @param $safePwd
     * @return array
     */
    public function balanceTransfer($userData, $acceptMobile, $transferAmount, $safePwd): array
    {
        $return = $this->return;
        Db::startTrans();
        try {
            //校验安全密码是否正确
            if ($userData->safe_password != (new Auth())->getEncryptPassword($safePwd, $userData->safe_salt)) {
                throw new Exception('The security password is incorrect', 1);
            }
            //获取收款人信息
            $acceptUserData = (new User())->where([
                'mobile' => $acceptMobile,
                'status' => 'normal',
            ])->find();
            if (empty($acceptUserData)) {
                throw new Exception('收款用户不存在或已禁用', 1);
            }

            //转出余额
            Common::changeUserTokenValue($userData, 'token_value', 2, 14, $transferAmount, 0, null, [
                'out_mobile' => $userData['mobile'],
                'in_mobile' => $acceptUserData['mobile']
            ]);
            //接收
            Common::changeUserTokenValue($acceptUserData, 'token_value', 1, 15, $transferAmount, 0, null, [
                'out_mobile' => $userData['mobile'],
                'in_mobile' => $acceptUserData['mobile']
            ]);

            $return['code'] = 1;
            $return['msg'] = 'success';
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            if ($e->getCode() == 1) {
                $return['msg'] = $e->getMessage();
            } else {
                (new Log())->error('balanceTransfer：' . $e->getMessage());
                $return['msg'] = 'Operation failed';
            }
        }
        return $return;
    }
}