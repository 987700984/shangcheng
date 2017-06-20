<?php
namespace osc\common\model;

use think\Model;

class Goods extends Model {
    protected $pk = 'goods_id';


    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'date_added';
    protected $updateTime = 'date_modified';


    public function updateViewed() {
        $this->viewed += 1;
        $this->save();
    }

//与评论表关联 一对多
    public function goodsComment()
    {
        return $this->hasMany('GoodsComment','goods_id');
    }

//与品牌表关联 一对多
    public function brand()
    {
        return $this->belongsTo('Brand','brand_id');
    }

//与商品详情表关联 一对一
    public function goodsDescription()
    {
        return $this->hasOne('GoodsDescription','goods_id');
    }

    //与长度单位表关联 一对多
    public function lengthClass()
    {
        return $this->belongsTo('LengthClass','length_class_id');
    }


    //与商品-属性表关联 一对多
    public function goodsAttribute()
    {
        return $this->hasMany('GoodsAttribute','goods_id');
    }

    //与商品-分类表关联 一对一
    public function goodsToCategory()
    {
        return $this->hasOne('GoodsToCategory','goods_id')->field('category_id');
    }

    //与商品-分类表关联 一对多
    public function goodsImage()
    {
        return $this->hasMany('GoodsImage','goods_id');
    }

    //与属性值表关联 多对多
    public function attributeValue()
    {
        return $this->belongsToMany('AttributeValue','osc_goods_attribute','attribute_value_id','goods_id');
    }

    //    由于长宽高数值的小数点位数太多，都是这种形式：44.00000000；要把小数点后的位数减少到两位
    public function getLengthAttr($value){
        return substr($value,0,strrpos($value,'.')+3);
    }

    public function getWidthAttr($value){
        return substr($value,0,strrpos($value,'.')+3);
    }

    public function getHeightAttr($value){
        return substr($value,0,strrpos($value,'.')+3);
    }




}