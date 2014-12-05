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

namespace rico\yii2images\models;

use Yii;
use yii\helpers\BaseFileHelper;
use \rico\yii2images\ModuleTrait;



abstract class ImageAbstract extends \yii\db\ActiveRecord implements ImageInterface
{
    use ModuleTrait;

    protected $effects = [];

    protected $effectsFactoryInterfaceClass = null;

    public function init()
    {
        parent::find();
        if($this->getModule()->waterMark){
            $this->effect('waterMark');
        }

        $this->effectsFactoryInterfaceClass = $this->getModule()->effectsFactoryInterfaceClass;
    }

    public function disableWatermark()
    {
        if(isset($this->effects['waterMark'])){
            $this->effects['waterMark']->disable();
        }
    }

    public function clearCache(){
        $subDir = $this->getSubDur();

        $dirToRemove = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.$subDir;

        if(preg_match('#'.preg_quote($this->modelName, '#').'#', $dirToRemove)){
            BaseFileHelper::removeDirectory($dirToRemove);
        }
        return true;
    }

    public function getEffects()
    {
        return $this->effects;
    }

    public function effect($effectCode)
    {
        $effectClassName = $this->getModule()->getEffect($effectCode);//p($effectClassName);die;
        $effect = null;
        if(is_subclass_of($effectClassName, $this->effectsFactoryInterfaceClass)){
            $effectFactory = new $effectClassName;
            $effect = $effectFactory->getEffect();
        }elseif($this->getModule()->checkEffect($effectClassName)){
            $effect = new $effectClassName;
        }else{
            throw new \Exception('Error with effect');
        }
        $this->effects[$effectCode] = $effect;
        return $this;
    }

    public function restoreEffects($effects)
    {
        foreach($effects as $effect){
            //$effectClass = $this->getModule()->getEffect($effect);
            $this->effect($effect);
        }

        return $this;
    }

    public function setAsMain($isMain = true){
        $imageClass = $this->getModule()->imageClass();
        $itemImages = $imageClass::find()->where([
            'itemId'=>$this->itemId,
            'modelName'=>$this->modelName
        ])->all();

        foreach($itemImages as $img){
            $img->isMain = 0;
            $img->save();
        }

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


    public function getPathToSave($sizeString)
    {
        $cachePath = $this->getModule()->getCachePath();
        $subDirPath = $this->getSubDur();
        $fileExtension =  pathinfo($this->filePath, PATHINFO_EXTENSION);
        $pathToSave = $cachePath.'/'.$subDirPath.'/'.$this->getModule()->urlManager->getImageIdentifer($this, $sizeString).'.'.$fileExtension;

        return $pathToSave;
    }

    public function getPath($size = false){
        $filePath = $this->getPathToSave($size);
        if(!file_exists($filePath)){
            $this->createVersion($size);

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

    abstract public function createVersion($sizeString = false);

    protected function getSubDur(){
        return $this->modelName. 's/' . $this->modelName.$this->itemId;
    }


    public function isPlaceHolder()
    {
        return false;
    }
    public function removeSelf()
    {
        $this->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $this->filePath;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $this->delete();
    }

    public function applyEffects($image)
    {
        foreach ($this->effects as $effect) {
            if($effect->isEnabled){
                $image = $effect->apply($image);
            }
        }

        return $image;
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
