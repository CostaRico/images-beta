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

    public function createVersion($imagePath, $sizeString = false)
    {
        if(strlen($this->urlAlias)<1){
            throw new \Exception('Image without urlAlias!');
        }

        $cachePath = $this->getModule()->getCachePath();
        $subDirPath = $this->getSubDur();
        $fileExtension =  pathinfo($this->filePath, PATHINFO_EXTENSION);

        if($sizeString){
            $sizePart = '_'.$sizeString;
        }else{
            $sizePart = '';
        }

        $pathToSave = $cachePath.'/'.$subDirPath.'/'.$this->urlAlias.$sizePart.'.'.$fileExtension;

        BaseFileHelper::createDirectory(dirname($pathToSave), 0777, true);


        if($sizeString) {
            $size = $this->getModule()->parseSize($sizeString);
        }else{
            $size = false;
        }

        $image = new \Imagick($imagePath);
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

        $image->writeImage($pathToSave);

        return $image;

    }
} 