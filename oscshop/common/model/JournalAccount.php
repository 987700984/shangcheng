<?php
namespace osc\common\model;

use think\Model;

class JournalAccount extends Model {

//    只开启自动写入创建时间字段，关闭修改时间字段
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = false;


}