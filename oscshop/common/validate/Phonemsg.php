<?php
namespace app\common\validate;

use think\Validate;

class Phonemsg extends Validate
{
    protected  $rule = [
        'phoneNumber|手机号' => 'number|require|min:11',
    ];

}