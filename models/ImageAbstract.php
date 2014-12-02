<?php


/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $filePath
 * @property integer $itemId
 * @property integer $isMain
 * @property string $modelName
 * @property string $urlAlias
 * @property integer $number
 */

namespace rico2\yii2images\models;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\BaseFileHelper;
use \rico2\yii2images\ModuleTrait;
use rico2\yii2images\models\UrlManager;



abstract class ImageAbstract extends \yii\db\ActiveRecord implements ImageInterface
{
    use ModuleTrait;

    protected $effects = [];

    public function clearCache(){
        $subDir = $this->getSubDur();

        $dirToRemove = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.$subDir;

        if(preg_match('/'.preg_quote($this->modelName, '/').DIRECTORY_SEPARATOR, $dirToRemove)){
            BaseFileHelper::removeDirectory($dirToRemove);

        }

        return true;
    }

    public function getEffects()
    {
        return $this->effects;
    }

    private function checkEffect($effectClassName)
    {
        if(class_implements($effectClassName, 'rico2\yii2images\inters\ImagickEffectInterface')){
            if($this->getModule()->graphicsLibrary != 'Imagick'){
                throw new \Exception('Effect class must implement Imagick Effect interface');
            }
        }elseif(class_implements($effectClassName, 'rico2\yii2images\inters\GDEffectInterface')){
            if($this->getModule()->graphicsLibrary != 'GD'){
                throw new \Exception('Effect class must implement GD Effect interface');
            }
        }else{
            throw new \Exception('Effect class must implement one of effect interfaces');
        }

        return true;
    }

    public function effect($effectClassName)
    {
        $effect = null;
        if(class_implements($effectClassName, 'rico2\yii2images\inters\EffectsFactoryInterface')){
            $effectFactory = new $effectClassName;
            $effect = $effectClassName->getEffect();
        }elseif($this->checkEffect($effectClassName)){
            $effect = new $effectClassName;
        }else{
            throw new \Exception('Error with effect');
        }
        $this->effects[] = $effect;
    }

    public function setAsMain($isMain = true){
        $this->isMain = 1;
        $this->save();
    }
    
    public function getExtension(){
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getUrl($size = null){
        return $this->getModule()->urlManager->getImageUrl($this, $size);
    }

    public function getPath($size = false){
        $urlSize = ($size) ? '_'.$size : '';
        $base = $this->getModule()->getCachePath();
        $sub = $this->getSubDur();

        $origin = $this->getPathToOrigin();

        $filePath = $base.DIRECTORY_SEPARATOR.
            $sub.DIRECTORY_SEPARATOR.$this->urlAlias.$urlSize.'.'.pathinfo($origin, PATHINFO_EXTENSION);;
        if(!file_exists($filePath)){
            $this->createVersion($origin, $size);

            if(!file_exists($filePath)){
                throw new \Exception('Problem with image creating.');
            }
        }

        return $filePath;
    }

    public function getContent($size = false){
        return file_get_contents($this->getPath($size));
    }

    public function getPathToOrigin(){

        $base = $this->getModule()->getStorePath();

        $filePath = $base.DIRECTORY_SEPARATOR.$this->filePath;

        return $filePath;
    }


    abstract public function getSizes();

    public function getSizesWhen($sizeString){

        $size = $this->getModule()->parseSize($sizeString);
        if(!$size){
            throw new \Exception('Bad size..');
        }



        $sizes = $this->getSizes();

        $imageWidth = $sizes['width'];
        $imageHeight = $sizes['height'];
        $newSizes = [];
        if(!$size['width']){
            $newWidth = $imageWidth*($size['height']/$imageHeight);
            $newSizes['width'] = intval($newWidth);
            $newSizes['heigth'] = $size['height'];
        }elseif(!$size['height']){
            $newHeight = intval($imageHeight*($size['width']/$imageWidth));
            $newSizes['width'] = $size['width'];
            $newSizes['heigth'] = $newHeight;
        }

        return $newSizes;
    }

    abstract public function createVersion($imagePath, $sizeString = false);

    protected function getSubDur(){
        return $this->modelName. 's/' . $this->modelName.$this->itemId;
    }
        
    /** ----========= GENERATED =========-----*/
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filePath', 'itemId', 'modelName', 'urlAlias'], 'required'],
            [['itemId', 'isMain', 'number'], 'integer'],
            [['filePath', 'urlAlias'], 'string', 'max' => 400],
            [['modelName'], 'string', 'max' => 150]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filePath' => 'File Path',
            'itemId' => 'Item ID',
            'isMain' => 'Is Main',
            'modelName' => 'Model Name',
            'urlAlias' => 'Url Alias',
            'number' => 'number',
        ];
    }
}
