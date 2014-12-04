<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 05.08.14
 * Time: 18:21
 *
 * TODO: check that placeholder is enable in module class
 * override methods
 */

namespace rico2\yii2images\models;

/**
 * TODO: check path to save and all image method for placeholder
 */

use yii;

class PlaceHolderImagick extends ImageImagick implements ImageInterface
{

    protected  $modelName = 'placeHolder';
    protected $itemId = 1;
    public $urlAlias = 'placeHolder';


    public function __construct()
    {
        $this->filePath = basename(Yii::getAlias($this->getModule()->placeHolderPath)) ;
    }


    public function getPathToOrigin()
    {

        $url = Yii::getAlias($this->getModule()->placeHolderPath);
        if (!$url) {
            throw new \Exception('PlaceHolder image must have path setting!!!');
        }
        return $url;
    }

    public function removeSelf(){
        return false;
    }
    public function isPlaceHolder()
    {
        return true;
    }
}

