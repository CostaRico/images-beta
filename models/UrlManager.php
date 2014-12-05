<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 19:07
 */
namespace rico\yii2images\models;
use rico\yii2images\models\ImageAbstract;
use rico\yii2images\ModuleTrait;
use yii\helpers\Url;

class UrlManager {

    const EFFECTS_PREFIX = 'ef';

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

    public function getImageIdentifer(ImageAbstract $image, $size = null){
        $alias = $image->urlAlias;
        $num = $image->number;
        $effectsString = '';
        foreach ($image->getEffects() as $effect) {
            if(strlen($effect->getCode())>0){
                $effectsString .= $effect->getCode().'_';
            }
        }
        if(strlen($effectsString)>0){
            $effectsString = self::EFFECTS_PREFIX.$effectsString;
            $effectsString = substr($effectsString, 0, -1);
        }
        
        $parts = [];
        if(!$alias){
            throw new \Exception('Bad alias');
        }else{
            $parts[] = $alias;
        }

        if($num!=1){
            $parts[] = $num;
        }

        //If defined only width without "x"
        if($size){
            if($num && !preg_match('/x/', $size)){
                $size = $size.'x';
            }
            $parts[] = $size;
        }

        if($effectsString){
            $parts[] =$effectsString;
        }
        return join('_', $parts);
    }

    public function getImageUrl(ImageAbstract $image, $size = null){

        $imageIdPart = $this->getImageIdentifer($image, $size = null);
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
        //$effects = null;
        $effectsArray = [];
        if(preg_match('/ef.*$/', $code, $matches)){
            $effects = str_replace(self::EFFECTS_PREFIX, '', $matches[0]);
            //p($effects);
            $effectsArray = explode('_', $effects);

            $code = substr($code, 0, -strlen('_'.self::EFFECTS_PREFIX.$effects));
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
            'effects' => $effectsArray
        ];

        return $result;
    }
} 