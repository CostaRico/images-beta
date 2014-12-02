<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 30.11.14
 * Time: 18:32
 */

namespace rico2\yii2images\models;

use yii\helpers\BaseFileHelper;


class ImageGD extends ImageAbstract implements ImageInterface
{
    public function getSizes()
    {
        $sizes = false;
        $image = new \abeautifulsite\SimpleImage($this->getPathToOrigin());
        $sizes['width'] = $image->get_width();
        $sizes['height'] = $image->get_height();

        return $sizes;
    }

    public function createVersion($imagePath, $sizeString = false)
    {
        if (strlen($this->urlAlias) < 1) {
            throw new \Exception('Image without urlAlias!');
        }

        $cachePath = $this->getModule()->getCachePath();
        $subDirPath = $this->getSubDur();
        $fileExtension = pathinfo($this->filePath, PATHINFO_EXTENSION);

        if ($sizeString) {
            $sizePart = '_' . $sizeString;
        } else {
            $sizePart = '';
        }

        $pathToSave = $cachePath . '/' . $subDirPath . '/' . $this->urlAlias . $sizePart . '.' . $fileExtension;

        BaseFileHelper::createDirectory(dirname($pathToSave), 0777, true);


        if ($sizeString) {
            $size = $this->getModule()->parseSize($sizeString);
        } else {
            $size = false;
        }


        $image = new \abeautifulsite\SimpleImage($imagePath);


        if ($size) {
            if ($size['height'] && $size['width']) {

                $image->thumbnail($size['width'], $size['height']);
            } elseif ($size['height']) {
                $image->fit_to_height($size['height']);
            } elseif ($size['width']) {
                $image->fit_to_width($size['width']);
            } else {
                throw new \Exception('Something wrong with this->module->parseSize($sizeString)');
            }
        }

        foreach ($this->effects as $effect) {
            $image = $effect->apply($image);
        }


        $image->save($pathToSave, 100);

        return $image;

    }

} 