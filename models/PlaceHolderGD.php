<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 04.12.14
 * Time: 13:23
 */

namespace rico2\yii2images\models;

use yii;

class PlaceHolderGD extends ImageGD implements ImageInterface
{

    private $modelName = 'placeHolder';
    private $itemId = 1;
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

