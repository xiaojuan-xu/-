<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 经销商控制器
 * 
 * @author 潘宏钢 <619328391@qq.com>
 */

class VendorsController extends CommonController 
{
	/**
     * 经销商列表（本质就是后台用户）
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function index()
    {	
        // 根据用户昵称进行搜索
        $map = '';
    	if(!empty($_GET['name'])) $map['name'] = array('like',"%{$_GET['name']}%");

        $user = D('vendors');
        $total = $user->where($map)->count();
        $page  = new \Think\Page($total,8);
        $pageButton =$page->show();

        $userlist = $user->where($map)->limit($page->firstRow.','.$page->listRows)->order('id desc')->getAll();

        $this->assign('list',$userlist);
        $this->assign('button',$pageButton);
        $this->display();
    }

    /**
     * 添加经销商方法
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function add()
    {
        if(IS_POST){
// dump($_POST);die;
            $user = D('vendors');
            $info = $user->create();
            // dump($info);die;

            if($info){

                $res = $user->add();
                if ($res) {
                    $this->success('添加经销商成功啦！！！',U('Vendors/index'));
                } else {
                    $this->error('添加经销商失败啦！');
                }
            
            } else {
                // getError是在数据创建验证时调用，提示的是验证失败的错误信息
                $this->error($user->getError());
            }
        }else{
            $this->display(); 
        }
    }

    /**
     * 编辑经销商方法
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function edit($id)
    {
        if(IS_POST){
            $user = D('vendors');
            $userinfo = $user->create();
            if ($userinfo) {
                $res = $user->save();
                if ($res) {
                    $this->success('编辑经销商成功啦！！！',U('Vendors/index'));
                } else {
                    $this->error('编辑经销商失败啦！');
                }                
            }else{
                $this->error($user->getError());
            }

        } else {
            $info[] = D('vendors')->find($id);
            $this->assign('info',$info);
            $this->display();
        }
    }
    
    /**
     * 删除经销商方法
     * 需保证其没有下级，没有设备与之挂钩
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function del($id)
    {
        $userinfo = M('vendors')->where("id=".$id)->select();

        if ($userinfo[0]['leavel'] == 0 ) {
            $this->error('不能删除超级管理员！');
        }else{
            // 查
            $res = D('vendors')->delete($id);
            if($res){
                $this->success('删除成功',U('index'));
            }else{
                $this->error('删除失败');
            }

        }
    }

    /**
     * 机组绑定经销商方法
     * 
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function devices_add()
    {
        if (IS_POST) {

            if ($_POST['cid']) {

                if ($_POST['vid']) {
                   
                    $arr = array(
                        'vid' => I('vid'),
                        'cid' => I('cid'),
                        'addtime' => time(),
                    );
                    
                    // 添加
                    $binding = M('binding');
                    if ($binding->add($arr)) {
                        // 更改机组的绑定状态
                        $devices = M('crew');  
                        $devices->where('id='.$_POST['cid'])->setField('status','1');

                        $this->success('添加成功',U('bindinglist'));
                    }else{
                        $this->error('添加失败啦');
                    }

                }else{
                    $this->error('经销商不存在！请在经销商管理中添加经销商后尝试！正在为您跳转至经销商管理',U('index')); 
                }

            }else{
                $this->error('机组不存在！请在机组管理中设置机组后尝试！正在为您跳转至机组管理',U('Crew/index'));
            }   

        }else{

            if(!empty($_SESSION['adminuser'])){
                // 获取经销商信息
                $case = $_SESSION['adminuser']['leavel'];

                switch ($case) {
                    
                    case '0':
                        $user = D('vendors')->getAll();
                        break;

                    case '1':
                    // 一级经销商只能给二级经销商绑定设备，如需再往下分级，则case '2' leavel=3
                        $user = M('vendors')->where('leavel=2')->select();

                        break;    
                    
                    default:
                        # code...
                        break;
                }

                // 获取机组信息
                $devices = M('crew')->where('status=0')->select();

                $this->assign('user',$user);
                $this->assign('devices',$devices);
                $this->display();
            }else{
                $this->error('登录失效，请重新登陆！',U('Login/login'));
            }
            
        }

    }

    /**
     * 机组绑定经销商列表
     * 
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function bindinglist()
    {
       // 根据用户昵称进行搜索
        $map = '';
        if(!empty($_GET['name'])) $map['name'] = array('like',"%{$_GET['name']}%");

        $binding = M('binding');
        
        $total =$binding->where($map)
                    ->join('xp_vendors ON xp_binding.vid = xp_vendors.id')
                    ->join('xp_crew ON xp_binding.cid = xp_crew.id')
                    ->field('xp_binding.*,xp_vendors.name,xp_vendors.phone,xp_crew.cname')
                    ->count();
        $page  = new \Think\Page($total,8);
        $pageButton =$page->show();

        $bindinglist = $binding->where($map)
                                ->limit($page->firstRow.','.$page->listRows)
                                ->join('xp_vendors ON xp_binding.vid = xp_vendors.id')
                                ->join('xp_crew ON xp_binding.cid = xp_crew.id')
                                ->field('xp_binding.*,xp_vendors.name,xp_vendors.phone,xp_crew.cname')
                                ->select();
        // dump($bindinglist);
        $this->assign('list',$bindinglist);
        $this->assign('button',$pageButton);
        $this->display(); 
    }

    /**
     * 解除绑定方法
     * 
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function bindingdel($id,$cid)
    {
        
        if ($_SESSION['adminuser']['leavel'] == 0) {

            $res = D('binding')->delete($id);
            if($res){
                // 更新设备绑定状态
                $devices = M('crew');  
                $devices->where('id='.$cid)->setField('status','0');
                $this->success('解除成功',U('bindinglist'));
            }else{
                $this->error('解除失败');
            }

        }else{
           $this->error('对不起，您没有权限解除绑定！',U('bindinglist'));
        }
    }

    /**
     * 修改密码方法
     * 
     * @author 潘宏钢 <619328391@qq.com>
     */
    public function password()
    {
        if (IS_POST) {
            $old = md5(I('oldpassword')); 
            $new = md5(I('newpassword')); 
            $re = md5(I('repassword'));
            $id = I('id');

            if ($new == $re) {
                $user = M('Vendors');
                $info = $user->where('id='.$id)->getField('password');
                
                if ($old == $info) {
                    $res = $user->where('id='.$id)->setField('password',$new);
                    if ($res) {
                        $this->success('修改密码成功，请重新登录！',U('Login/logout'));
                    }else{
                        $this->error('修改密码失败！');
                    }

                }else{
                    $this->error('原密码错误，请重新输入！');
                }

            }else{
                $this->error('两次密码不一样，请重新输入！');
            }

        }else{
            $this->display();
        }
    }

}