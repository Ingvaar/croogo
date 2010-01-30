<?php
/**
 * Cached Behavior
 *
 * PHP version 5
 *
 * @category Behavior
 * @package  Croogo
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class CachedBehavior extends ModelBehavior {
/**
 * Setup
 *
 * @param object $model
 * @param array  $config
 * @return void
 */
    function setup(&$model, $config = array()) {
        if (is_string($config)) {
            $config = array($config);
        }

        $this->settings[$model->alias] = $config;
    }
/**
 * afterSave callback
 *
 * @param object  $model
 * @param boolean $created
 * @return void
 */
    function afterSave(&$model, $created) {
        $this->_deleteCachedFiles($model);
    }
/**
 * afterDelete callback
 *
 * @param object $model
 * @return void
 */
    function afterDelete(&$model) {
        $this->_deleteCachedFiles($model);
    }
/**
 * Delete cache files matching prefix
 *
 * @param object $model
 * @return void
 */
    function _deleteCachedFiles(&$model) {
        foreach ($this->settings[$model->alias]['prefix'] AS $prefix) {
            $files = glob(TMP.'cache/queries/cake_'.$prefix.'*');
            foreach ($files AS $file) {
                unlink($file);
            }
        }
    }

}
?>