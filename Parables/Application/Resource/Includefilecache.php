<?php
class Parables_Application_Resource_Includefilecache extends 
    Zend_Application_Resource_ResourceAbstract
{
    /**
     * Initialize include file cache
     *
     * @return  void
     */
    public function init()
    {
        $this->setIncludeFileCache();
    }

    /**
     * Set include file cache
     *
     * @return  void
     */
    public function setIncludeFileCache()
    {
        $options = $this->getOptions();
        if (array_key_exists('path', $options)) {
            if (file_exists($options['path'])) {
                include_once $options['path'];
            }

            Zend_Loader_PluginLoader::setIncludeFileCache($options['path']);
        }
    }
}
