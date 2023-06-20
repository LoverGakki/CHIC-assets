<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

use app\common\library\Auth;
use app\common\logic\Common;
use app\common\model\UserCoupons;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $whereArr = [];
            if ($upperUserData = $this->model->where('mobile', $this->auth->mobile)->find()) {
                $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
            } elseif ($upperUserData = $this->model->where('mobile', $this->auth->username)->find()) {
                $whereArr['referrer_link'] = ['like', $upperUserData['referrer_link'] . $upperUserData['invite_code'] . '%'];
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->where($whereArr)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        //return parent::edit($ids);

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            if ($params['is_audit'] != $row['is_audit']) {
                $params['audit_time'] = time();
            }
            if ($params['is_valid'] != $row['is_valid'] && $params['is_valid'] == 1 && !$row['valid_time']) {
                $params['valid_time'] = time();
            }
            if ($params['is_activated'] != $row['is_activated'] && $params['is_activated'] == 1 && !$row['activation_time']) {
                $params['activation_time'] = time();
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

    //操作余额
    public function change_token_value()
    {
        $Id = $this->request->request('id');
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($Id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('userData', $row);
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            $result = Common::changeUserTokenValue($row, 'token_value', $params['operate_type'], $params['operate_type'] == 1 ? 2 : 10, $params['change_value'], 0, $this->auth->id);
            //$result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    //发放优惠券
    public function grant_discount_coupon()
    {
        $Id = $this->request->request('id');
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($Id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('userData', $row);
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            if ($row['is_audit'] != 1) {
                throw new Exception('该用户未通过审核');
            }
            if ($row['status'] != 'normal') {
                throw new Exception('该用户已拉黑');
            }
            if ($params['coupons_amount'] <= 0) {
                throw new Exception('优惠金额应大于0');
            }

            if (!(new UserCoupons())->save([
                'user_id' => $row['id'],
                'can_use_project_type_id' => $params['can_use_project_type_id'],
                'name' => $params['name'],
                'coupons_amount' => $params['coupons_amount'],
                'status' => $params['status'],
            ])) {
                throw new Exception('发放错误');
            }

            $result = true;

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    //余额转账
    public function balance_transfer()
    {
        $Id = $this->request->request('id');
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($Id);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('userData', $row);
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            if ($row['can_transfer'] != 1) {
                throw new Exception('该用户未允许转账');
            }
            if ($row['status'] != 'normal') {
                throw new Exception('该用户已拉黑');
            }
            if ($row['mobile'] == $params['accept_mobile']) {
                throw new Exception('请勿给自己转账');
            }
            if ($params['transfer_value'] <= 0) {
                throw new Exception('转账金额应大于0');
            }
            if ($row['token_value'] < $params['transfer_value']) {
                throw new Exception('用户余额不足');
            }

            //获取收款人信息
            $acceptUserData = $this->model->where([
                'mobile' => $params['accept_mobile'],
                'status' => 'normal',
            ])->find();
            if (empty($acceptUserData)) {
                throw new Exception('收款用户不存在或已禁用', 1);
            }
            //转出余额
            Common::changeUserTokenValue($row, 'token_value', 2, 14, $params['transfer_value'], 0, null, [
                'out_mobile' => $row['mobile'],
                'in_mobile' => $acceptUserData['mobile']
            ]);
            //接收
            Common::changeUserTokenValue($acceptUserData, 'token_value', 1, 15, $params['transfer_value'], 0, null, [
                'out_mobile' => $row['mobile'],
                'in_mobile' => $acceptUserData['mobile']
            ]);

            $result = true;

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }
}
