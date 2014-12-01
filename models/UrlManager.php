<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 19:07
 */
namespace rico2\yii2images\models;
use rico2\yii2images\models\ImageAbstract;

class UrlManager {
    public static function generateUrl(ImageAbstract $image){
        /**
         * Выделить код размер
         * выделить код эффектов
         * разрешить ситуацию с алиасом
         * учесть номер картинки
         * сформировать урл
         * было http://ex.ru/images/Object1202/b91ee13a79-1_500x250.jpg
         * надо http://ex.ru/images/white-cat_1_500x250_efGr-gr2-Sep.jpg
         * варианты урла:
         *  http://ex.ru/images/white-cat.jpg
         *  http://ex.ru/images/white-cat_500x.jpg
         *  http://ex.ru/images/white-cat_500x100.jpg
         *  http://ex.ru/images/white-cat_x100.jpg
         *  http://ex.ru/images/white-cat_2_500x400.jpg
         *  http://ex.ru/images/white-cat_2_efGr.jpg
         *  http://ex.ru/images/white-cat_2_efGr_12f.jpg
         *  http://ex.ru/images/white-cat_efGr_12f.jpg
         *  http://ex.ru/images/white-cat_400x_efGr_12f.jpg
         *
         * эффекты всегда в конце
         * у первой картинки нет номера
         * номер всегда идет вторым
         * номер может быть цифрой только
         * размер всегда содержит в себе одну "x"
         * градиент всегда содержит ef в начале
         */
    }

    public function getImageUrl(ImageAbstract $image){

    }

    public static function parseImageUrl($imagePathOfUrl)
    {
        $code = $imagePathOfUrl;
        p($code);
        //If with extension, remove ext (we will not check ext, every code must be unique)
        if(preg_match('/\./', $imagePathOfUrl)){
            $code = preg_replace('/\.*$/', '', $code);
        }
        p($code);

        $effects = null;
        if(preg_match('/ef.*$/', $code, $mathes)){
            $effects = $mathes[1];
        }




    }
} 