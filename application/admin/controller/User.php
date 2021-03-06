<?php
/**
 * Created by PhpStorm.
 * User: ZhongHua
 * Date: 2018/3/12
 * Time: 0:18
 */
namespace app\admin\controller;
use app\common\controller\UserCheck;
use app\common\Error;
use app\common\Factory;
use app\common\Functions;
use app\index\model\UserCredit;
use think\Request;

class User extends Base
{
    public function login () {
        return $this->fetch();
    }
    public function checkLogin (Request $request) {
        $params = $request->param();
        $user = Factory::getOperObj('user');
        $code = $user->checkAdminLogin($params['loginname'], md5($params['loginpwd']));
        $errArr = Error::getCodeMsgArr($code);
        if ($code == 2) {
            $errArr['result'] = '用户名不存在';
        }
        if ($code == 3) {
            $errArr['result'] = "密码错误";
        }
        return json($errArr);
    }

    public function exitLogin()
    {
        $user = Factory::getOperObj('user');
        if ($user->exitLogin()) {
            $this->success('成功退出', 'user/login');
        }
    }

    /**
     * 添加后台管理员账户
     */
    public function addAdmin()
    {
        return $this->fetch();
    }

    public function addAdminDo(Request $request)
    {
        $params = $request->param();
        $user = Factory::getOperObj('user');

        if(!$user->checkEmailNew($params['loginemail'])) {
            $err = '邮箱已被使用';
            $code = 1;
            $errArr = Error::getCodeMsgArr($code);
            $errArr['result'] = $err;
            return json($errArr);
        }

        $res = $user->addAdmin($params['loginname'], $params['loginemail'], $params['loginpwd']);
        $code = $res ? 0 : 1;
        $errArr = Error::getCodeMsgArr($code);
        return json($errArr);
    }

    /**
     * 用户统计
     * @return mixed
     */
    public function countUser()
    {
        $user = new \app\common\dataoper\User();
        $data = $user->getChartData();
        $this->assign('totalNum', json_encode($data));
        return $this->fetch();
    }

    /**
     * 用户列表  TODO 依据注册时间筛选用户
     * @param Request $request
     * @return mixed
     */
    public function userList(Request $request)
    {
        $user = new \app\common\dataoper\User();
        $userCredit = new \app\common\dataoper\UserCredit();
        $page = (int)$request->param('page');
        $key = !empty($request->param('key')) ? $request->param('key') : null;
        $where = '';
        if (!is_null($key)) {
            $where = 'loginname like \'%' . $key . '%\'';
        }
        if ($page < 0) {
            $page = 0;
        }
        $limit = 10;
//        获取总页数

        $where .= !empty($where)  ? ' and isvalid=1' : 'isvalid=1';
        $counts = $user->getAllUserCount(['where' => $where]);
        $totalPage = ceil($counts / $limit);
        $res = $user->getPagingUser([
            'where' => $where,
            'order' => 'create_time desc'
        ], $page, $limit);
        if (!$res) {
            $this->assign('userlist', []);
        } else {
            foreach ($res as &$v) {
                $v = $v->getData();
                if (null !== $uc = $userCredit->getUserDetailByUid($v['id'])) {
                    $v['detail'] = $uc->getData();
                } else {
                    $v['detail'] = [];
                }
            }
            $this->assign('userlist', $res);
        }
        $this->assign('totalpage', $totalPage);
        $this->assign('totalNum', $counts);
        if (!is_null($key)) {
            $this->assign('keyWord', $key);
        }
        return $this->fetch();
    }

    /**
     * 删除用户接口
     * @param Request $request
     * @return string
     */
    public function delUser(Request $request)
    {
        $uid = (int)$request->param('uid');
        $user = new \app\common\dataoper\User();
        $res = $user->delUserByUid($uid);
        $res = ($res !== false) && !empty($res) ? 1 : 0;
        $msg = $res ? 'success' : 'failed';
        return json(['errorCode' => $res, 'errorMsg' => $msg]);
    }

    /**
     * 获取用户详细信息接口
     */
    public function getUserDetailed(Request $request)
    {
        $uid = (int)$request->param('uid');
        $user = new \app\common\dataoper\UserCredit();
        $detailed = $user->getUserDetailByUid($uid);
        $errCode = 1;
        $errMsg = '详细信息如下！';
        if (empty($detailed)) {
            $errCode = 0;
            $errMsg = '暂无详细信息';
        }
        return json_encode(
            [
                'errorCode' => $errCode,
                'result' => empty($detailed) ? '' : $detailed,
                'errorMsg' => $errMsg
            ]
        );
    }

    /**
     * 修改用户信誉积分
     */
    public function changeCredit(Request $request)
    {
        $uid = (int)$request->param('uid');
        $credit = (int)$request->param('credit');

        $ucredit = new \app\common\dataoper\UserCredit();
        $res = $ucredit->changeCredit($uid, $credit);
        $errCode = !empty($res) ? 1 : 0;
        $errMsg = !empty($res) ? '更新成功' : '更新失败';
        return json(['errorCode' => $errCode, 'errorMsg' => $errMsg]);
    }
    /**
     * 生成测试账户
     */
    public function generateAccounts()
    {
        $res = \app\common\dataoper\User::generateAccounts();

        if (!empty($res)) {
            $this->success('创建成功', 'user/userlist', '', 1);
        }
        $this->error('创建失败', 'admin/index/index', '', 1);
    }
}