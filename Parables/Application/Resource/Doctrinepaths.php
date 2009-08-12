<?php
// @todo Handle modularity
class Parables_Application_Resource_Doctrinepaths extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * Defined by Zend_Application_Resource_Resource
     * 
     * @return  array
     * @throws Zend_Application_Resource_Exception
     */
    public function init()
    {
        // @bug The fallback autoloader must be enabled for non-namespaced 
        // model loading to work
        $autoloader = Zend_Loader_Autoloader::getInstance();
        if (!$autoloader->isFallbackAutoloader()) {
            $autoloader->setFallbackAutoloader(true);
        }

        $options = $this->getOptions();

        if (!is_array($options)) {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception('Paths must be an 
                array.');
        }

        if (array_key_exists('models_path', $options)) {
            $this->_loadModels($options['models_path']);
        }

        return $options;
    }

    /**
     * Load models
     *
     * @param mixed $paths
     * @return void
     * @throws Zend_Application_Resource_Exception
     */
    protected function _loadModels($paths = null)
    {
        if (is_string($paths)) {
            Doctrine::loadModels($paths);
        } else if (is_array($paths)) {
            foreach($paths as $path) {
                Doctrine::loadModels($path);
            }
        } else {
            require_once 'Zend/Application/Resource/Exception.php';
            throw new Zend_Application_Resource_Exception('Invalid model_path.');
        }
    }
}
