<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 22.06.14
 * Time: 16:58
 */

namespace rico2\yii2images\behaviors;


use rico2\yii2images\models\Image;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use rico2\yii2images\models;
use yii\helpers\BaseFileHelper;
use \rico2\yii2images\ModuleTrait;



class ImageBehave extends Behavior
{

    use ModuleTrait;
    public $createAliasMethod = false;

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

        /** @var $image rico2\yii2images\models\ImageAbstract */
        $image->itemId = $this->owner->id;
        $image->filePath = $pictureSubDir . '/' . $pictureFileName;
        $image->modelName = $this->getModule()->getShortClass($this->owner);


        $image->urlAlias = $this->getAlias($image);

        $image->save();
        if (count($image->getErrors()) > 0) {
            $ar = array_shift($image->getErrors());
            unlink($newAbsolutePath);
            throw new \Exception(array_shift($ar));
        }


        $img = $this->owner->getImage();
        //If main image not exists
        if(
            is_object($img) && get_class($img)=='rico2\yii2images\models\PlaceHolder'
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
        $cachePath = $this->getModule()->getCachePath();
        $subdir = $this->getModule()->getModelSubDir($this->owner);

        $dirToRemove = $cachePath . '/' . $subdir;

        if (preg_match('/' . preg_quote($cachePath, '/') . '/', $dirToRemove)) {
            BaseFileHelper::removeDirectory($dirToRemove);
            //exec('rm -rf ' . $dirToRemove);
            return true;
        } else {
            return false;
        }
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
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $imageRecords = $imageQuery->all();
        if(!$imageRecords){
            return [$this->getModule()->getPlaceHolder()];
        }
        return $imageRecords;
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
        $images = $this->owner->getImages();
        if (count($images) < 1) {
            return true;
        } else {
            foreach ($images as $image) {
                $this->owner->removeImage($image);
            }
        }
    }


    /**
     *
     * removes concrete model's image
     * @param Image $img
     * @throws \Exception
     */
    public function removeImage($img)
    {
        $img->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $img->filePath;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $img->delete();
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



    /** Make string part of image's url
     * @return string
     * @throws \Exception
     */
    private function getAliasString()
    {
        if ($this->createAliasMethod) {
            $string = $this->owner->{$this->createAliasMethod}();
            if (!is_string($string)) {
                throw new \Exception("Image's url must be string!");
            } else {
                return $string;
            }

        } else {
            return substr(md5(microtime()), 0, 10);
        }
    }


    /**
     *
     * Обновить алиасы для картинок
     * Зачистить кэш
     */
    private function getAlias()
    {
        $aliasWords = $this->getAliasString();
        $imagesCount = count($this->owner->getImages());

        return $aliasWords . '-' . intval($imagesCount + 1);
    }


}


