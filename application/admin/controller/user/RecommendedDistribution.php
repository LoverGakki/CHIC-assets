<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\logic\Common;
use fast\Tree;

/**
 * 推荐分布
 * Class RecommendedDistribution
 * @package app\admin\controller\user
 */
class RecommendedDistribution extends Backend
{
    //开启Validate验证
    protected $modelValidate = true;
    //开启模型场景验证
    protected $modelSceneValidate = true;
    //关联查询
    protected $relationSearch = true;
    //快捷搜索的字段
    protected $searchFields = 'id,mobile';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/User');
    }

    public function index()
    {
        /*//设置过滤方法
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
            ->with('superior_user')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);*/

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        $mobile = $this->request->request('mobile');
        if (empty($mobile)) {
            return json(['total' => 0, 'rows' => []]);
        }
        $userData = $this->model->where('mobile', $mobile)->find();
        if (empty($userData)) {
            $this->error('该手机号码用户不存在');
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where('superior_user_id', $userData['id'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function recommend_relationship()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->request('mobile');
            if (empty($mobile)) {
                $this->error('请选择查询用户');
            }
            $userData = $this->model->where('mobile', $mobile)->find();
            if (empty($userData)) {
                $this->error('该手机号码用户不存在');
            }
            //获取所有上级邀请码
            $referrerLinkArr = Common::getUserAscReferrerLink($userData);
            //上级数据
            $upperUserData = $this->model->where([
                'invite_code' => ['in', $referrerLinkArr]
            ])->order('id', 'desc')->select();
            if ($upperUserData) {
                $upperUserData = collection($upperUserData)->toArray();
            }
            array_unshift($upperUserData, $userData->toArray());
            $this->success('success', '', [
                'upper_user_data' => $upperUserData,
                'user_data' => $userData
            ]);
        }
        $this->error('请求方式错误');
    }

    public function get_children_tree()
    {
        $mobile = $this->request->request('mobile');
        if (empty($mobile)) {
            $this->error('请选择查询用户');
        }
        $userData = $this->model->where('mobile', $mobile)->find();
        if (empty($userData)) {
            $this->error('该手机号码用户不存在');
        }
        /*$tree = Tree::instance();
        $treeData = $this->model->where([
            'referrer_link' => ['like', $userData['referrer_link'] . $userData['invite_code'] . '%']
        ])->order('id', 'asc')->select();
        if ($treeData) {
            $treeData = collection($treeData)->toArray();
            array_unshift($treeData, $userData);
            $tree->init($treeData, 'superior_user_id');
            export($tree->getTreeList($tree->getTreeArray($userData['id']), 'mobile'));
        }
        die();*/
        $return = [];
        $upperUserData = $this->model->where([
            'superior_user_id' => $userData['id'],
        ])->order('id', 'asc')->select();
        if ($upperUserData) {
            //$return = array_column(collection($upperUserData)->toArray(), 'mobile');
            foreach ($upperUserData as $k => $v) {
                $return[] = [
                    'text' => $v['mobile'],
                    'icon' => 'glyphicon glyphicon-user',
                    'selectedIcon' => 'glyphicon glyphicon-user',
                    'nodes' => [],
                ];
            }
        }
        $this->success('success', '', $return);
    }

}