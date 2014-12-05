<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 18:37
 */

namespace rico\yii2images\models;


interface ImageInterface {
    public function clearCache();
    public function getExtension();
    public function getUrl($size = false);
    public function getPath($size = false);
    public function getContent($size = false);
    public function getPathToOrigin();
    public function getSizes();
    public function getSizesWhen($sizeString);
    public function createVersion($sizeString = false);
} 