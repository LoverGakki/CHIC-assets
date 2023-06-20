<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

use app\common\logic\Common;
use app\common\model\User;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class UserRechargeRecord extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'recharge_id,user_id';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/UserRechargeRecord');
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with('user')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function multi($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        if (false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }
        parse_str($this->request->post('params'), $values);
        $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
        if (empty($values)) {
            $this->error(__('You have no permission'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
            foreach ($list as $item) {
                //充值记录状态改为已完成，充值成功（用户改为有效用户）
                if (array_key_exists('status', $values) && $values['status'] == 2) {
                    $userData = (new User())->where('id', $item['user_id'])->find();
                    //增加用户余额
                    Common::changeUserTokenValue($userData, 'token_value', 1, 2, $item['recharge_amount']);
                    //是否修改有效用户
                    $change_valid = 0;
                    if (!$userData['is_valid'] || $userData['is_valid'] == 0) {
                        $userEditPars['is_valid'] = 1;
                        $userEditPars['valid_time'] = time();
                        //团队有效人数+1
                        $userEditPars['team_effective_number'] = $userData['team_effective_number'] + 1;
                        $change_valid = 1;
                        if (!$userData->save($userEditPars)) {
                            throw new Exception('用户：' . $userData['id'] . '：修改用户数据错误');
                        }
                    }
                    //修改团队业绩、团队有效直推
                    if (!$this->teamRelatedDataUpdate($userData, $change_valid)) {
                        throw new Exception('用户：' . $userData['id'] . '：修改团队有效直推错误');
                    }
                }
                $count += $item->allowField(true)->isUpdate(true)->save($values);
            }
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    private function teamRelatedDataUpdate($userData, int $change_valid = 0): bool
    {
        if ($referrerLinkArr = Common::getUserAscReferrerLink($userData)) {
            foreach ($referrerLinkArr as $k => $v) {
                $superiorEditPars = [];
                //团队上级数据
                $superiorUserData = (new User())->where('invite_code', $v)->find();
                if ($superiorUserData) {
                    //修改团队充值额
                    //$superiorEditPars['team_performance'] = $superiorUserData['team_performance'] + $buyNumber;
                    //需要修改有效用户（修改团队有效人数）
                    if ($change_valid == 1) {
                        //判断是否为直推上级，修改有效直推人数
                        if ($superiorUserData['id'] == $userData['superior_user_id']) {
                            $superiorEditPars['valid_direct_drive_number'] = $superiorUserData['valid_direct_drive_number'] + 1;
                        }
                        $superiorEditPars['team_effective_number'] = $superiorUserData['team_effective_number'] + 1;
                    }
                }
                if ($superiorEditPars && !$superiorUserData->save($superiorEditPars)) {
                    return false;
                }
            }
        }
        return true;
    }
}