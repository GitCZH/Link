<?php
/**
 * Created by PhpStorm.
 * User: ZhongHua
 * Date: 2018/3/20
 * Time: 21:50
 */
namespace app\common\dataoper;
use app\common\Factory;

class UserCredit
{

    private static $userCreditObj = null;
    private static function getUserModelObj()
    {
        if (!is_null(self::$userCreditObj)) {
            return self::$userCreditObj;
        }
        self::$userCreditObj = new \app\common\model\UserCredit();
        return self::$userCreditObj;
    }

    /**
     * 根据uid获取用户的详细信息
     * @param $uid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getUserDetailByUid ($uid) {
        $userCreditObj = self::getUserModelObj();
        return $userCreditObj->where(['idnum' => $uid])
            ->find();
    }

    /**
     * 修改用户信誉积分
     */
    public function changeCredit($uid, $credit)
    {
        $userCreditObj = self::getUserModelObj();
        return $userCreditObj->where(['idnum' => $uid])->update(['credit' => $credit]);
    }

    /**
     * 添加数据
     * save()  成功返回1
     */
    public function saveOne(array $datas)
    {
        $userC = Factory::getModelObj('userCredit');
        return $userC->allowField(true)->save($datas);
    }

    /**
     * 根据uid 修改数据
     */
    public function updateByUid($uid, $datas)
    {
        $userC = Factory::getModelObj('userCredit');
        return $userC->allowField(true)->save($datas, ['idnum' => $uid]);
    }
}