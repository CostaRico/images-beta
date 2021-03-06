<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 22.06.14
 * Time: 16:58
 */

namespace rico\yii2images\behaviors;


use rico\yii2images\models\Image;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use rico\yii2images\models;
use yii\helpers\BaseFileHelper;
use \rico\yii2images\ModuleTrait;



class ImageBehave extends Behavior
{

    use ModuleTrait;
    public $aliasSourceMethod = null;

    /**
     * @var ActiveRecord|null Model class, which will be used for storing image data in db, if not set default class(models/Image) will be used
     */
    public $imageClass = null;

    /**
     *
     * Method copies image file to module store and creates db record.
     */
    public function attachImage($imgSource, $isMain = false)
    {
        if(!preg_match('#^http#', $imgSource)){
            if (!file_exists($imgSource)) {
                throw new \Exception('File not exist! ('.$imgSource.')');
            }
        }

        if (!$this->owner->id) {
            throw new \Exception('Owner must have id when you attach image!');
        }

        $pictureFileName =
            substr(md5(microtime(true) . $imgSource), 4, 6)
            . '.' .
            pathinfo($imgSource, PATHINFO_EXTENSION);

        $pictureSubDir = $this->getModule()->getModelSubDir($this->owner);
        $storePath = $this->getModule()->getStorePath($this->owner);

        $newAbsolutePath = $storePath .
            DIRECTORY_SEPARATOR . $pictureSubDir .
            DIRECTORY_SEPARATOR . $pictureFileName;

        BaseFileHelper::createDirectory($storePath . DIRECTORY_SEPARATOR . $pictureSubDir,
            0775, true);

        copy($imgSource, $newAbsolutePath);

        if (!file_exists($newAbsolutePath)) {
            throw new \Exception('Cant copy file! ' . $imgSource . ' to ' . $newAbsolutePath);
        }

        $imageClass = $this->getModule()->imageClass();
        $image = new $imageClass;

        /** @var $image rico\yii2images\models\ImageAbstract */
        $image->itemId = $this->owner->id;
        $image->filePath = $pictureSubDir . '/' . $pictureFileName;
        $image->modelName = $this->getModule()->getShortClass($this->owner);
        $image->urlAlias = $this->getAliasForImage($image);
        $image->number = $this->getImagesCount()+1;
        $image->save();
        if (count($image->getErrors()) > 0) {
            $ar = print_r($image->getErrors(), true);
            unlink($newAbsolutePath);
            throw new \Exception($ar);
        }


        $img = $this->owner->getImage();
        //If main image not exists
        if(
            is_object($img) && $img->isPlaceHolder()
            or
            $img == null
            or
            $isMain
        ){
            $image->setAsMain();
        }


        return $image;
    }


    /**
     * Clear all images cache (and resized copies)
     * @return bool
     */
    public function clearImagesCache()
    {
        if($this->owner->getImagesCount()>0){
            foreach($this->owner->getImages() as $img){
                $img->clearCache();
            }
        }
        return true;
    }


    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function getImages()
    {
        $finder = $this->getImagesFinder();
        $imageClass = $this->getModule()->imageClass();
        $imageQuery = $imageClass::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'number' => SORT_ASC]);

        $imageRecords = $imageQuery->all();
        if(!$imageRecords){
            if($this->getModule()->placeHolderPath){
                return [$this->getModule()->getPlaceHolder()];
            }else{
                return [];
            }
        }
        return $imageRecords;
    }


    public function getImagesCount()
    {
        $imgs = $this->getImages();
        if(count($imgs)==1){
            $img = $imgs[0];
            $module = $this->getModule();
            if($img->isPlaceHolder()){
                return 0;
            }
        }

        return count($imgs);
    }
    /**
     * returns main model image
     * @return array|null|ActiveRecord
     */
    public function getImage()
    {
        $imageClass = $this->getModule()->imageClass();
        $finder = $this->getImagesFinder(['isMain' => 1]);
        $imageQuery = $imageClass::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $img = $imageQuery->one();
        if(!$img){
            return $this->getModule()->getPlaceHolder();
        }

        return $img;
    }

    /**
     * Remove all model images
     */
    public function removeImages()
    {
        if($this->owner->getImagesCount()==0){
            return true;
        }
        $images = $this->owner->getImages();
        foreach ($images as $image) {
            $this->owner->removeImage($image);
        }
        return true;
    }


    private function getImagesFinder($additionWhere = false)
    {
        $base = [
            'itemId' => $this->owner->id,
            'modelName' => $this->getModule()->getShortClass($this->owner)
        ];

        if ($additionWhere) {
            $base = \yii\helpers\BaseArrayHelper::merge($base, $additionWhere);
        }

        return $base;
    }


    /**
     * String part of image url
     */
    protected function getAliasForImage(){
        if ($this->aliasSourceMethod) {
            $string = $this->owner->{$this->aliasSourceMethod}();
            if($string==''){
                throw new \Exception("Users alias method must not return empty string");
            }
            if (!is_string($string)) {
                throw new \Exception("Image's alias must be string!");
            } else {
                return $string;
            }
        } else {
            return substr(md5(microtime()), 0, 10);
        }
    }


}


