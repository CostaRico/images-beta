<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 25.06.14
 * Time: 15:35
 */

namespace rico\yii2images\controllers;

use yii\web\Controller;
use yii;
use rico\yii2images\models\Image;
use \rico\yii2images\ModuleTrait;

class ImagesController extends Controller
{
    use ModuleTrait;
    public $enableCsrfValidation = false;

    public function actionSetMainImage($imageId)
    {
        $imageClass = $this->getModule()->imageClass();
        $image = $imageClass::findOne($imageId);
        $image->setAsMain();
    }

    public function actionRemoveImage($imageId)
    {
        $imageClass = $this->getModule()->imageClass();
        $image = $imageClass::findOne($imageId);
        $image->removeSelf();
    }

    public function actionIndex()
    {
        echo "Hello, man. It's ok, dont worry.";
    }

    public function actionTestTest()
    {
        echo "Hello, man. It's ok, dont worry.";
    }


    public function actionImageByAlias($alias, $extension)
    {
        $parsedAlias = $this->getModule()->urlManager->parseImageUrl($alias);
        $imgClass = $this->getModule()->imageClass();
        $image = $imgClass::find()->where(['urlAlias'=>$parsedAlias['alias'], 'number'=>$parsedAlias['num']])->one();
        if(!$image){
            $image = $this->getModule()->getPlaceHolder();
        }
        $image->clearCache();
        $image->restoreEffects($parsedAlias['effects'])->disableWatermark();
        //p($image->getContent());die;
        header('Content-Type: image/jpg');
        echo $image->getContent($parsedAlias['size']);
    }

    public function actionImageByAliasW($alias, $extension)
    {
        $parsedAlias = $this->getModule()->urlManager->parseImageUrl($alias);
        $imgClass = $this->getModule()->imageClass();
        $image = $imgClass::find()->where(['urlAlias'=>$parsedAlias['alias'], 'number'=>$parsedAlias['num']])->one();

        if(!$image){
            $image = $this->getModule()->getPlaceHolder();
        }
        $image->clearCache();
        $image->restoreEffects($parsedAlias['effects'])->disableWatermark();
        //p($image->getContent());die;
        header('Content-Type: image/jpg');
        echo $image->getContent($parsedAlias['size']);
    }

}