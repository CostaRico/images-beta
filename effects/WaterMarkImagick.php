<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 04.12.14
 * Time: 18:25
 */

namespace rico2\yii2images\effects;

use rico2\yii2images\inters\ImagickEffectInterface;
use rico2\yii2images\ModuleTrait;
use yii;

class WaterMarkImagick extends EffectAbstract implements ImagickEffectInterface
{
    use ModuleTrait;
    public function apply(\Imagick $image)
    {

        if (!file_exists(Yii::getAlias($this->getModule()->waterMark))) {
            throw new \Exception('WaterMark not exists!');
        }
        $watermark = new \Imagick();
        $watermark->readImage(Yii::getAlias($this->getModule()->waterMark));

        $iWidth = $image->getImageWidth();
        $iHeight = $image->getImageHeight();
        $wWidth = $watermark->getImageWidth();
        $wHeight = $watermark->getImageHeight();


        if ($iHeight < $wHeight) {
            // resize the watermark
            $watermark->scaleImage(false, $iHeight * 0.8);
        }

        if ($iWidth < $wWidth) {
            // resize the watermark
            $watermark->scaleImage($iWidth * 0.8, false);
        }
        $wWidth = $watermark->getImageWidth();
        $wHeight = $watermark->getImageHeight();

        $x = ($iWidth - $wWidth) / 2;
        $y = ($iHeight - $wHeight) / 2;

        $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
        return $image;
    }

    public static function getCode()
    {
        return false;
    }
}