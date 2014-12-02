<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 19:07
 */
namespace rico2\yii2images\models;
use rico2\yii2images\models\ImageAbstract;
use rico2\yii2images\ModuleTrait;
use yii\helpers\Url;

class UrlManager {
    use ModuleTrait;
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

    public function getImageUrl(ImageAbstract $image, $size = null){
        $alias = $image->urlAlias;
        $num = $image->number;
        $effectsString = '';
        foreach ($image->getEffects() as $effect) {
            $effectsString .= $effect->getCode().'_';
        }
        $effectsString = substr($effectsString, 0, -1);

        $parts = [];
        if(!$alias){
            throw new \Exception('Bad alias');
        }else{
            $parts[] = $alias;
        }

        if($num!=1){
            $parts[] = $num;
        }

        if($size){
            if($num && !preg_match('/x/', $size)){
                $size = $size.'x';
            }
            $parts[] = $size;
        }

        if($effectsString){
            $parts[] =$effectsString;
        }
        $imageIdPart = join('_', $parts);

        $url = Url::toRoute([
            '/'.$this->getModule()->id.'/images/image-by-alias',
            'alias' => $imageIdPart,
            'extension' => $image->getExtension()
        ]);

        return $url;
    }

    /**
     * Parse all variants like:
     *
     *      'white-cat_1_500x250_efGr-gr2-Sep',
     *      'white-cat.jpg',
     *      'white-cat',
     *      'white-cat_500x.jpg',
     *      'white-cat_500x100.jpg',
     *      'white-cat_x100.jpg',
     *      'white-cat_2_500x400.jpg',
     *      'white-cat_2_efGr.jpg',
     *      'white-cat_2_efGr_12f.jpg',
     *      'white-cat_efGr_12f.jpg',
     *      'white-cat_400x_efGr_12f.jpg'
     */
    public static function parseImageUrl($imagePathOfUrl)
    {
        $code = $imagePathOfUrl;
        //If with extension, remove ext (we will not check ext, every code must be unique)
        if(preg_match('/\./', $imagePathOfUrl)){
            $code = preg_replace('/\..*$/', '', $code);
        }
        $effects = null;
        if(preg_match('/ef.*$/', $code, $matches)){
            $effects = $matches[0];
            $code = substr($code, 0, -strlen('_'.$effects));
        }

        //alias string and size
        $parts = explode('_', $code);
        if(count($parts)==3){
            $aliasString = $parts[0];
            $imageNum = $parts[1];
            $size = $parts[2];
        }elseif(count($parts)==2){
            if(preg_match('/x/', $parts[1])){
                $aliasString = $parts[0];
                $size = $parts[1];
                $imageNum = 1;
            }else{
                $aliasString = $parts[0];
                $size = null;
                $imageNum = $parts[1];
            }

        }elseif(count($parts)==1){
            $aliasString = $parts[0];
            $size = null;
            $imageNum = 1;
        }else{
            throw new \Exception('Bad image code');
        }

        $result = [
            'alias' => $aliasString,
            'num' => $imageNum,
            'size' => $size,
            'effects' => $effects
        ];

        return $result;
    }
} 