<?php
namespace Admin\Model;

use Think\Model;

/**
 * Class 产品滤芯数据处理
 * @package Admin\Model
 * @author 潘宏钢 <619328391@qq.com>
 */
class FiltersModel extends Model
{   
    // 自动验证
    protected $_validate = array(
        array('filtername','require','滤芯名称不能为空'),
        array('timelife','/^\+?[1-9][0-9]*$/','请输入有效数值',1,'regex'),
        array('flowlife','/^\+?[1-9][0-9]*$/','请输入有效数值',1,'regex'),
    );


    // 自动完成
    protected $_auto = array ( 
        array('addtime','time',3,'function'), // 对addtime字段在新增和编辑的时候写入当前时间戳 

    );


   

    

}