<?php
/**
 * Zend_Loader_Autoloader
 */
require_once 'Zend/Loader/Autoloader.php';

class Zend_Application_Resource_DoctrineTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        Zend_Loader_Autoloader::resetInstance();
        $this->autoloader = Zend_Loader_Autoloader::getInstance();
        $this->autoloader->setFallbackAutoloader(true);

        $this->application = new Zend_Application('testing');

        $this->bootstrap = new Zend_Application_Bootstrap_Bootstrap($this->application);

        Zend_Controller_Front::getInstance()->resetInstance();
    }

    public function tearDown()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Reset autoloader instance so it doesn't affect other tests
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testOptionsPassedToResourceAreUsedToSetDoctrineManagerState()
    {
        $options = array(
            'manager' => array(
                'export'        => 'all',
                'model_loading' => 'conservative',
                'portability'   => 'all',
                'validate'      => 'all',
            ),
        );

        $resource = new My_Application_Resource_Doctrine($options);
        $resource->setBootstrap($this->bootstrap);
        $values = $resource->init();
        $manager = Doctrine_Manager::getInstance();

        $this->assertEquals(Doctrine::EXPORT_ALL, $manager->getAttribute('export'));
        $this->assertEquals(Doctrine::MODEL_LOADING_CONSERVATIVE, $manager->getAttribute('model_loading'));
        $this->assertEquals(Doctrine::PORTABILITY_ALL, $manager->getAttribute('portability'));
        $this->assertEquals(Doctrine::VALIDATE_ALL, $manager->getAttribute('validate'));
    }

    public function testOptionsPassedToResourceAreUsedToSetDoctrineConnections()
    {
        $options = array(
            'connections' => array(
                'demo' => array(
                    'dsn' => 'sqlite:///' . realpath(__FILE__) . '/../../_files/test.db',
                ),
            ),
        );

        $resource = new My_Application_Resource_Doctrine($options);
        $resource->setBootstrap($this->bootstrap);
        $values = $resource->init();
        $manager = Doctrine_Manager::getInstance();

        foreach ($manager->getConnections() as $conn) {
            $this->assertTrue($conn instanceof Doctrine_Connection_Common);
        }
    }

    public function testOptionsPassedToResourceAreUsedToSetDoctrinePaths()
    {
        $currentPath = realpath(__FILE__);
        $options = array(
            'paths' => array(
                'data_fixtures_path'    => $currentPath . '/../../doctrine/data/fixtures',
                'migrations_path'       => $currentPath . '/../../doctrine/migrations',
                'sql_path'              => $currentPath . '/../../doctrine/data/sql',
                'yaml_schema_path'      => $currentPath . '/../../doctrine/schema',
            ),
        );

        $resource = new My_Application_Resource_Doctrine($options);
        $resource->setBootstrap($this->bootstrap);
        $values = $resource->init();
        $paths = $values['paths'];

        $this->assertArrayHasKey('data_fixtures_path', $paths);
        $this->assertArrayHasKey('migrations_path', $paths);
        $this->assertArrayHasKey('sql_path', $paths);
        $this->assertArrayHasKey('yaml_schema_path', $paths);
    }

    /**
     * @expectedException Zend_Application_Resource_Exception
     */
    public function testMissingDsnConnectionOptionThrowsException()
    {
        $options = array(
            'connections' => array(
                'demo' => array(),
            ),
        );

        $resource = new My_Application_Resource_Doctrine($options);
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
    }
}
