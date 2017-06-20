<?php
namespace osc\common\model;

use think\Model;

class GoodsComment extends Model {
    protected $pk = 'id';

//    只开启自动写入创建时间字段，关闭修改时间字段
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = false;
    protected $createTime = 'add_time';


//    与goods模型关联
    public function Goods()
    {
        return $this->belongsTo('Goods');
    }




//    与管理员模型关联
    public function Admin(){
        return $this->belongsTo('Admin');
    }


//    获取审核状态
    public function getStatusAttr($value)
    {
        $status = [0=>'未审核',1=>'通过',2=>'未通过'];
        return $status[$value];
    }



//与用户表关联 一对多
    public function Member()
    {
        return $this->belongsTo('Member','id');
    }

}