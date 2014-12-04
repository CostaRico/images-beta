<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 02.12.14
 * Time: 14:24
 */

namespace rico2\yii2images\effects;
use rico2\yii2images\inters\ImagickEffectInterface;


class ImagickGradient extends EffectAbstract implements ImagickEffectInterface{

    public $coverPercent = 40;
    public $fromColor = 'transparent';
    public $toColor = 'black';

    public function apply(\Imagick $image)
    {
        $gradient = new \Imagick();

        $gradient->newPseudoImage($image->getImageWidth(), $image->getImageHeight()*$this->coverPercent/100, "gradient:".$this->fromColor."-".$this->toColor);
        $image->compositeImage($gradient, \Imagick::COMPOSITE_OVER, 0, $image->getImageHeight()-$gradient->getImageHeight()  );

        return $image;
    }

    public static function getCode()
    {
        return 'SG';
    }
}