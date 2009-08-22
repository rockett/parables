<?php
class Parables_Plugin_DojoBuildGenerator extends Zend_Controller_Plugin_Abstract
{
    public $buildProfile = '/path/to/public/assets/scripts/util/buildscripts/profiles/custom.profile.js';

    public $layerScript = '/path/to/public/assets/scripts/layers/custom/main.js';

    protected $_build;

    public function dispatchLoopShutdown()
    {
        if (!file_exists($this->layerScript)) {
            $this->generateLayer();
        }

        if (!file_exists($this->buildProfile)) {
            $this->generateBuildProfile();
        }
    }

    public function getBuild()
    {
        if (null === $this->_build) {
            $front = Zend_Controller_Front::getInstance();
            $bootstrap = $front->getParam('bootstrap');
            $view = $bootstrap->getResource('view');

            $this->_build = new Zend_Dojo_BuildLayer(array(
                'view'      => $view,
                'layerName' => 'custom.main',
            ));
        }

        return $this->_build;
    }

    public function generateLayer()
    {
        if (!is_dir(dirname($this->layerScript))) {
            mkdir(dirname($this->layerScript));
        }

        $layer = $this->getBuild()->generateLayerScript();
        file_put_contents($this->layerScript, $layer);
    }

    public function generateBuildProfile()
    {
        $profile = $this->getBuild()->generateBuildProfile();
        file_put_contents($this->buildProfile, $profile);
    }
}
