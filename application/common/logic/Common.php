<?php

namespace app\common\logic;


use think\Model;
use think\Exception;
use app\common\library\Log;
use app\common\model\User;
use app\common\model\AppConfig;
use app\common\model\UserBalanceLog;
use app\common\model\UserLevelConfig;
use app\common\model\UserYuebaoEarningsRecord;

/**
 * 公共logic相关
 * Class Banner
 * @package app\common\logic
 */
class Common extends Model
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
    }

    /**
     * 修改用户代币余额并记录代币增减日志
     * @param object $userData
     * @param string $tokenField 代币字段
     * @param int $operateType 操作类型 1=加，2=减
     * @param int $changeType 变更类型  1=签到赠送，2=用户充值，3=投资扣除，4=返还利息，5=返还本金，6=提现扣除，7=余额宝日收益，8=升级赠送，9=分销返利，10=平台扣除，11=提现退回，12=转入余额宝，13=余额宝转出，14=转账转出，15=转账接收
     * @param float $operateValue 操作金额
     * @param int $extract_order_id 提币申请单id
     * @param array $transferParams 转账数据
     * @return bool
     * @throws Exception
     */
    public static function changeUserTokenValue(object $userData, string $tokenField, int $operateType, int $changeType, float $operateValue, int $extract_order_id = 0, $admin_id = null, array $transferParams = []): bool
    {
        //用户表代币字段对应类型数组1=web3
        $tokenTypeArr = [
            'token_value' => 1,
        ];
        $changeTypeArr = [
            1 => '签到赠送余额',
            2 => '用户充值',
            3 => '投资扣除',
            4 => '返还利息',
            5 => '返还本金',
            6 => '提现扣除',
            7 => '余额宝日收益',
            8 => '升级赠送',
            9 => '分销返利',
            10 => '平台扣除',
            11 => '提现退回',
            12 => '转入余额宝',
            13 => '余额宝转出',
            14 => '转账转出',
            15 => '转账接收',
            /*4 => '入金本金到账',
            5 => '钱包余额入金',
            6 => '余额提取',
            7 => '提币退回',
            8 => '余额入金退回'*/
        ];
        //旧余额
        $oldValue = $userData[$tokenField];
        //新余额
        $newValue = $operateType == 1 ? $oldValue + $operateValue : $oldValue - $operateValue;
        $userEditPars = [
            $tokenField => $newValue < 0 ? 0 : $newValue
        ];
        if ($changeType == 4) {
            //累计静态收益
            $userEditPars['cumulative_earnings_value'] = $userData['cumulative_earnings_value'] + $operateValue;
            $userEditPars['team_cumulative_earnings_value'] = $userData['team_cumulative_earnings_value'] + $operateValue;
        } else if ($changeType == 2) {
            //累计充值记录
            $userEditPars['cumulative_recharge_amount'] = $userData['cumulative_recharge_amount'] + $operateValue;
        } else if ($changeType == 6) {
            //累计提现记录
            $userEditPars['cumulative_withdrawals_amount'] = $userData['cumulative_withdrawals_amount'] + $operateValue;
        } else if ($changeType == 7) {
            //累计余额宝收益
            $userEditPars['cumulative_yuebao_earnings_amount'] = $userData['cumulative_yuebao_earnings_amount'] + $operateValue;
        } else if ($changeType == 11) {
            //累计提现记录（提现退回）
            $userEditPars['cumulative_withdrawals_amount'] = $userData['cumulative_withdrawals_amount'] - $operateValue;
        }

        //代币日志参数
        $logPars = [
            'user_id' => $userData['id'],
            //'token_type' => $tokenTypeArr[$tokenField],
            'operate_type' => $operateType,
            'change_type' => $changeType,
            'memo' => $changeTypeArr[$changeType],
            'money' => $operateValue,
            'before' => $oldValue,
            'after' => $newValue < 0 ? 0 : $newValue,
            'admin_id' => $admin_id,
        ];

        //提币记录提币订单id
        if ($changeType == 6 && $extract_order_id) {
            $logPars['extract_order_id'] = $extract_order_id;
        }
        //转账数据
        if (in_array($changeType, [14, 15])) {
            $logPars['out_mobile'] = $transferParams['out_mobile'];
            $logPars['in_mobile'] = $transferParams['in_mobile'];
        }
        //修改余额
        if (!$userData->save($userEditPars)) {
            throw new Exception('用户：' . $userData['id'] . '：修改代币余额错误');
        }
        //记录代币增减日志
        if (!empty($logPars) && !(new UserBalanceLog())->save($logPars)) {
            throw new Exception('用户：' . $userData['id'] . '：代币余额变动日志记录错误');
        }
        return true;
    }

    /**
     * 修改用户余额宝余额并记录余额增减日志
     * @param $userData
     * @param $operateType
     * @param $changeType
     * @param $operateValue
     * @param null $return_rate
     * @return bool
     * @throws Exception
     */
    public static function changeUserYuebaoTokenValue($userData, $operateType, $changeType, $operateValue, $return_rate = null): bool
    {
        $changeTypeArr = [
            1 => '转入',
            2 => '日收益',
            3 => '转出',
        ];
        //旧余额
        $oldValue = $userData['yuebao_token_value'];
        //新余额
        $newValue = $operateType == 1 ? $oldValue + $operateValue : $oldValue - $operateValue;
        $userEditPars = [
            'yuebao_token_value' => $newValue < 0 ? 0 : $newValue
        ];
        if ($changeType == 2) {
            //累计余额宝收益
            $userEditPars['cumulative_yuebao_earnings_amount'] = $userData['cumulative_yuebao_earnings_amount'] + $operateValue;
        }
        //代币日志参数
        $recordPars = [
            'user_id' => $userData['id'],
            'money' => $operateValue,
            'before' => $oldValue,
            'after' => $newValue < 0 ? 0 : $newValue,
            'return_rate' => $return_rate,
            'operate_type' => $operateType,
            'change_type' => $changeType,
            'memo' => $changeTypeArr[$changeType],
        ];
        //修改余额
        if (!$userData->save($userEditPars)) {
            throw new Exception('用户：' . $userData['id'] . '：修改余额宝余额错误');
        }
        //记录代币增减日志
        if (!empty($recordPars) && !(new UserYuebaoEarningsRecord())->save($recordPars)) {
            throw new Exception('用户：' . $userData['id'] . '：余额宝余额变动日志记录错误');
        }
        return true;
    }

    /**
     * 获取用户从近到远团队链路数组
     * @param $userData
     * @return array
     */
    public static function getUserAscReferrerLink($userData): array
    {
        return $userData['referrer_link'] ? array_reverse(array_filter(explode(',', $userData['referrer_link']))) : [];
    }

    /**
     * 用户升级处理（计算完团队业绩后调用）
     * @param $userData
     * @throws Exception
     */
    public static function userRoleLevelUp($userData): void
    {
        $newRoleLevel = 0;
        $upgradeCredit = 0;
        if ($userData['status'] == 'normal' && $userData['is_audit'] == 1 && $userData['is_valid'] == 1) {
            //获取用户等级配置数据
            $userLevelConfigData = (new UserLevelConfig())->where(['status' => 1])->order('level_number', 'asc')->select();
            foreach ($userLevelConfigData as $k => $v) {
                //判断用户团队业绩是否超过配置值
                if ($userData['team_performance'] >= $v['investments_total']) {
                    $newRoleLevel = $v['level_number'];
                    $upgradeCredit = $v['upgrade_credit'];
                } else {
                    break;
                }
            }
        }
        if ($newRoleLevel != 0 && $newRoleLevel > $userData['role_level']) {
            //赠送余额
            $after = $userData['token_value'] + $upgradeCredit;
            if ($upgradeCredit > 0 && !(new UserBalanceLog())->save([
                    'user_id' => $userData['id'],
                    'operate_type' => 1,
                    'change_type' => 8,
                    'memo' => '升级赠送',
                    'money' => $upgradeCredit,
                    'before' => $userData['token_value'],
                    'after' => $after,
                ])) {
                throw new Exception('用户：' . $userData['id'] . '：代币余额变动日志记录错误');
            }
            if (!$userData->save([
                'role_level' => $newRoleLevel,
                'token_value' => $after
            ])) {
                throw new Exception('修改用户等级错误');
            }
        }
    }

}