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
        if ($options = $this->getOptions()) {
            if ((is_string($options)) && (file_exists($options))) {
                include_once $options;
            }

            Zend_Loader_PluginLoader::setIncludeFileCache($options);
        }
    }
}
