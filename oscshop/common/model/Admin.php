<?php
namespace osc\common\model;

use think\Model;

class Admin extends Model {
    protected $pk = 'admin_id';


    public function storage_user_action($info)
    {
        storage_user_action($this->admin_id,$this->user_name,config('BACKEND_USER'),$info);
    }
}