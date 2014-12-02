<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 18:36
 */

namespace rico2\yii2images\models;
use yii\helpers\BaseFileHelper;

class ImageImagick  extends ImageAbstract implements ImageInterface {
    public function getSizes()
    {
        $image = new \Imagick($this->getPathToOrigin());
        $sizes = $image->getImageGeometry();

        return $sizes;
    }

    public function createVersion($sizeString = false)
    {
        if(strlen($this->urlAlias)<1){
            throw new \Exception('Image without urlAlias!');
        }
        if($sizeString) {
            $size = $this->getModule()->parseSize($sizeString);
        }else{
            $size = false;
        }
        $pathToSave = $this->getPathToSave($sizeString);
        BaseFileHelper::createDirectory(dirname($pathToSave), 0777, true);

        $image = new \Imagick($this->getPathToOrigin());
        $image->setImageCompressionQuality(100);

        if($size){
            if($size['height'] && $size['width']){
                $image->cropThumbnailImage($size['width'], $size['height']);
            }elseif($size['height']){
                $image->thumbnailImage(0, $size['height']);
            }elseif($size['width']){
                $image->thumbnailImage($size['width'], 0);
            }else{
                throw new \Exception('Something wrong with this->module->parseSize($sizeString)');
            }
        }
        foreach ($this->effects as $effect) {
            $image = $effect->apply($image);
        }
        $image->writeImage($pathToSave);

        return $image;

    }
} 