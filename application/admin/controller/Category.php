<?php
/**
 * 多级分类管理
 * Created by PhpStorm.
 * User: ZhongHua
 * Date: 2018/4/13
 * Time: 15:45
 */
namespace app\admin\controller;
use app\common\Error;
use app\common\Factory;
use app\common\Functions;
use think\Request;

class Category extends Base
{
    /**
     * 新增分类
     */
    public function create(Request $request)
    {
        if (empty($request->param())) {
//            获取已有的分类
            $category = Factory::getOperObj('category');
            $cats = $category->getAllCats();

            Functions::pasteArrToOptions($cats, $option);
            $this->assign('cats', $option);
            return $this->fetch();
        }
        $catBigName = $request->param('bigName');
        $catSmallName = $request->param('smallName', ' ');
        $catFid = (int)$request->param('fid');

        if (empty($catBigName)) {
            return json(['errorCode' => 1, 'errorMsg' => '分类名不能为空']);
        }
        $category = Factory::getOperObj('category');
        $res = $category->create($catBigName, $catSmallName, $catFid);
        $errCode = $res ? 0 : 1;
        $errArr = Error::getCodeMsgArr($errCode);

        return json($errArr);
    }

    /**
     * 修改分类
     */
    public function edit(Request $request)
    {
        $catBigName = $request->param('bigName');
        $catSmallName = $request->param('smallName', ' ');
        $catFid = (int)$request->param('fid', null);
        $id = (int)$request->param('id');

        if (empty($catBigName)) {
            return json(['errorCode' => 1, 'errorMsg' => '分类名不能为空']);
        }
        $category = Factory::getOperObj('category');
        if (!empty($catSmallName)) {
            $data['catnameS'] = $catSmallName;
        }
        if ($catFid !== null) {
            $data['fid'] = $catFid;
        }
        $data['catnameB'] = $catBigName;
        $res = $category->editById($id, $data);

        $errCode = $res ? 0 : 1;
        $errArr = Error::getCodeMsgArr($errCode);

        return json($errArr);
    }

    /**
     * 获取要编辑的分类相关信息
     */
    public function getEditInfo(Request $request)
    {
        $category = Factory::getOperObj('category');

        $id = (int)$request->param('id');
        $cat = $category->getById($id);
        $topStr = '';
        if ($cat->getData()['fid'] != 0) {
            $topCats = $category->getCatsByFid();
            Functions::pasteArrToOptions($topCats, $topStr);
        }
        return json(['options' => $topStr, 'cat' => $cat]);
    }

    /**
     * 删除分类
     */
    public function del(Request $request)
    {
        $id = (int)$request->param('id');

        $category = Factory::getOperObj('category');
        $res = $category->delById($id);
        $errCode = $res ? 0 : 1;
        $errArr = Error::getCodeMsgArr($errCode);

        return json($errArr);
    }

    /**
     * 分类列表
     */
    public function catsList()
    {
        $category = Factory::getOperObj('category');
        $cats = $category->getAllCats();

        $this->assign('cats', $cats);
        return $this->fetch();
    }

    /**
     * 根据fid获取子分类
     */
    public function getChildCats(Request $request)
    {
        $fid = (int)$request->param('fid');

        $category = Factory::getOperObj('category');
        $childs = $category->getCatsByFid($fid);

        $errCode = empty($childs) ? 2 : 0;
        $errArr = Error::getCodeMsgArr($errCode);
        if (!empty($childs)) {
//            拼接输出HTML字符串
        $html = "<tr id='tr_%d' class='tr_%d' data-fid='tr_%d'>
                <td><input type=\"checkbox\"></td><td> </td>
                <td class=\"cat-hover\" data-fid=\"%d\">
                    <span class=\"am-text-primary\">%s
                        <i class=\"am-icon-angle-right tpl-left-nav-more-ico am-fr am-margin-right\"></i>
                    </span>
                </td>
                <td>%s</td>
                <td>
                    <div class=\"am-btn-toolbar\">
                    <div class=\"am-btn-group am-btn-group-xs\">
                    <button data-am-modal=\"{target: '#doc-modal-1', closeViaDimmer: 0}\" type=\"button\" data-id=\"%d\" class=\"edit-cat del-btn am-btn am-btn-xs am-text-primary\">
                        <span class=\"am-icon-pencil-square-o\"></span> 编辑
                    </button>
                    <button type=\"button\" data-id=\"%d\" class=\"del-cat del-btn am-btn am-btn-xs am-text-danger\">
                        <span class=\"am-icon-trash-o\"></span> 封禁
                    </button>
                    </div>
                    </div>
                 </td>
                </tr>";
            $childsStr = '';
            foreach($childs as $child) {
                $catnameS = empty($child['catnameS']) ? ' ' : $child['catnameS'];
                $childsStr .= sprintf($html, $child['id'], $fid, $child['id'], $child['id'], $child['catnameB'], $catnameS, $child['id'], $child['id']);
            }
            $errArr['result'] = $childsStr;
        }
        return json($errArr);
    }
}