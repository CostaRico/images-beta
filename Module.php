<?php

namespace rico2\yii2images;


use rico2\yii2images\models\PlaceHolderGD;
use rico2\yii2images\models\PlaceHolderImagick;
use rico2\yii2images\models\UrlManager;
use SebastianBergmann\Exporter\Exception;
use yii;
use yii\helpers\Url;


class Module extends \yii\base\Module
{
    public $imageClasses = [
        'GD' => 'rico2\yii2images\models\ImageGD',
        'Imagick' => 'rico2\yii2images\models\ImageImagick'
    ];

    const MODULE_NAMESPACE = 'rico2\yii2images';

    const IMAGE_BASE_CLASS = 'rico2\yii2images\models\ImageAbstract';
    const IMAGE_INTERFACE_CLASS = 'rico2\yii2images\models\ImageInterface';

    public $placeHolderClass = 'rico2\yii2images\models\PlaceHolder';

    public $imagesStorePath = '@app/web/store';

    public $imagesCachePath = '@app/web/imgCache';

    public $graphicsLibrary = 'GD';

    public $controllerNamespace = 'rico2\yii2images\controllers';

    public $placeHolderPath;

    public $waterMark = false;

    public $urlManager = null;
    public $removeImageUrl = null;
    public $setMainImageUrl = null;

    public $effects = [];

    public $urlPrefix = 'yii2images';
    public $urlRules = [
        '<alias>.<extension>' => 'images/image-by-alias'
    ];


    public function imageClass()
    {
        $imgClassName = null;
        if (!isset($this->imageClasses[$this->graphicsLibrary])) {
            throw new yii\base\Exception('I cant find correct Image class for your graphics library, config array must contain "GD" or "imagick" key.');
        } else {
            $imgClassName = $this->imageClasses[$this->graphicsLibrary];
        }

        if (!is_subclass_of($imgClassName, self::IMAGE_BASE_CLASS)) {
            throw new yii\base\Exception('Image class must be child of Image Abstract class ' . self::IMAGE_BASE_CLASS);
        }

        if (!is_subclass_of($imgClassName, self::IMAGE_INTERFACE_CLASS)) {
            throw new yii\base\Exception('Image class must implements interface ' . self::IMAGE_INTERFACE_CLASS);
        }
        return $imgClassName;
    }


    public function getImage($item, $dirtyAlias)
    {
        //Get params
        $params = $data = $this->parseImageAlias($dirtyAlias);

        $alias = $params['alias'];
        $size = $params['size'];

        $itemId = preg_replace('/[^0-9]+/', '', $item);
        $modelName = preg_replace('/[0-9]+/', '', $item);


        //Lets get image
        $imageClass = $this->imageClass();
        $image = $imageClass::find()
            ->where([
                'modelName' => $modelName,
                'itemId' => $itemId,
                'urlAlias' => $alias
            ])
            ->one();
        if (!$image) {
            return $this->getPlaceHolder();
        }

        return $image;
    }

    public function getStorePath()
    {
        return Yii::getAlias($this->imagesStorePath);
    }


    public function getCachePath()
    {
        return Yii::getAlias($this->imagesCachePath);

    }

    public function getModelSubDir($model)
    {
        $modelName = $this->getShortClass($model);
        $modelDir = $modelName . 's/' . $modelName . $model->id;

        return $modelDir;
    }


    public function getShortClass($obj)
    {
        $className = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $className, $matches)) {
            $className = $matches[1];
        }

        return $className;
    }


    /**
     *
     * Parses size string
     * For instance: 400x400, 400x, x400
     *
     * @param $notParsedSize
     * @return array|null
     */
    public function parseSize($notParsedSize)
    {
        $sizeParts = explode('x', $notParsedSize);
        $part1 = (isset($sizeParts[0]) and $sizeParts[0] != '');
        $part2 = (isset($sizeParts[1]) and $sizeParts[1] != '');
        if ($part1 && $part2) {
            if (intval($sizeParts[0]) > 0
                &&
                intval($sizeParts[1]) > 0
            ) {
                $size = [
                    'width' => intval($sizeParts[0]),
                    'height' => intval($sizeParts[1])
                ];
            } else {
                $size = null;
            }
        } elseif ($part1 && !$part2) {
            $size = [
                'width' => intval($sizeParts[0]),
                'height' => null
            ];
        } elseif (!$part1 && $part2) {
            $size = [
                'width' => null,
                'height' => intval($sizeParts[1])
            ];
        } else {
            throw new \Exception('Something bad with size, sorry!');
        }

        return $size;
    }

    public function parseImageAlias($parameterized)
    {
        $params = explode('_', $parameterized);

        if (count($params) == 1) {
            $alias = $params[0];
            $size = null;
        } elseif (count($params) == 2) {
            $alias = $params[0];
            $size = $this->parseSize($params[1]);
            if (!$size) {
                $alias = null;
            }
        } else {
            $alias = null;
            $size = null;
        }


        return ['alias' => $alias, 'size' => $size];
    }

    private function registerEffects()
    {
        foreach ($this->effects as $effect) {
            $this->checkEffect($effect);
        }
    }

    public function checkEffect($effectClassName)
    {
        if (class_implements($effectClassName, 'rico2\yii2images\inters\ImagickEffectInterface')) {
            if ($this->graphicsLibrary != 'Imagick') {
                throw new \Exception('Effect class must implement Imagick Effect interface');
            }
        } elseif (class_implements($effectClassName, 'rico2\yii2images\inters\GDEffectInterface')) {
            if ($this->graphicsLibrary != 'GD') {
                throw new \Exception('Effect class must implement GD Effect interface');
            }
        } else {
            throw new \Exception('Effect class must implement one of effect interfaces');
        }

        return true;
    }

    public function getEffect($effectId)
    {
        if (!isset($this->effects[$effectId])) {
            throw new Exception('Cant find effect ' . print_r($effectId, true));
        }

        return $this->effects[$effectId];
    }

    public function init()
    {
        parent::init();
        if (!$this->imagesStorePath
            or
            !$this->imagesCachePath
            or
            $this->imagesStorePath == '@app'
            or
            $this->imagesCachePath == '@app'
        )
            throw new \Exception('Setup imagesStorePath and imagesCachePath images module properties!!!');

        $this->urlManager = new UrlManager();
        $this->registerEffects();


        $this->removeImageUrl = Url::toRoute([
            '/' . $this->id . '/images/remove-image'
        ]);
        $this->setMainImageUrl = Url::toRoute([
            '/' . $this->id . '/images/set-main-image'
        ]);

    }

    public function getPlaceHolder()
    {

        if ($this->placeHolderPath) {
            if(!file_exists(Yii::getAlias($this->placeHolderPath))){
                throw new \Exception('PlaceHolder property defined, but placeholder file nor exists!!!');
            }
            $placeHolderClass = self::MODULE_NAMESPACE.'\models\PlaceHolder'.$this->graphicsLibrary;
            return new $placeHolderClass;
        } else {
            return null;
        }
    }
}
