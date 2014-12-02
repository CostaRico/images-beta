<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 25.06.14
 * Time: 15:35
 */

namespace rico2\yii2images\controllers;

use yii\web\Controller;
use yii;
use rico2\yii2images\models\Image;
use \rico2\yii2images\ModuleTrait;

class ImagesController extends Controller
{
    use ModuleTrait;
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
        //$image->clearCache();
        $image->restoreEffects($parsedAlias['effects']);
        //p($image->getContent());die;
        header('Content-Type: image/jpg');
        echo $image->getContent();
    }

}