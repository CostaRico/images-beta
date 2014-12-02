<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 02.12.14
 * Time: 14:22
 */
namespace rico2\yii2images\inters;

interface ImagickEffectInterface {
    public function getCode();
    public function apply(\Imagick $image);
}