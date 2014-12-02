<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 02.12.14
 * Time: 14:19
 */
namespace rico2\yii2images\inters;

interface EffectsFactoryInterface {
    public static function getEffect($graphicsType);
}