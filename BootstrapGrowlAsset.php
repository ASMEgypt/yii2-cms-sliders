<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace infoweb\sliders;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Bootstrap Growl asset files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BootstrapGrowlAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap.growl/dist';
    public $js = [
        'bootstrap-growl.min.js'
    ];
}
