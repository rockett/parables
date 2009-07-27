<?php
/**
 * Zend_Loader_Autoloader
 */
require_once 'Zend/Loader/Autoloader.php';

// @todo Refactor into 3 separate test classes
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
            'attr_export'        => 'export_all',
            'attr_model_loading' => 'model_loading_conservative',
            'attr_portability'   => 'portability_all',
            'attr_validate'      => 'validate_all',
        );

        $resource = new Parables_Application_Resource_Doctrinemanager($options);
        $resource->setBootstrap($this->bootstrap);
        $values = $resource->init();
        $manager = Doctrine_Manager::getInstance();

        $this->assertEquals(Doctrine::EXPORT_ALL, $manager->getAttribute(Doctrine::ATTR_EXPORT));
        $this->assertEquals(Doctrine::MODEL_LOADING_CONSERVATIVE, $manager->getAttribute(Doctrine::ATTR_MODEL_LOADING));
        $this->assertEquals(Doctrine::PORTABILITY_ALL, $manager->getAttribute(Doctrine::ATTR_PORTABILITY));
        $this->assertEquals(Doctrine::VALIDATE_ALL, $manager->getAttribute(Doctrine::ATTR_VALIDATE));
    }

    public function testOptionsPassedToResourceAreUsedToSetDoctrineConnections()
    {
        $options = array(
            'demo' => array(
                'dsn' => 'sqlite:///' . realpath(__FILE__) . '/../../_files/test.db',
            ),
        );

        $resource = new Parables_Application_Resource_Doctrineconnections($options);
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
            'data_fixtures_path'    => $currentPath . '/../../doctrine/data/fixtures',
            'migrations_path'       => $currentPath . '/../../doctrine/migrations',
            'sql_path'              => $currentPath . '/../../doctrine/data/sql',
            'yaml_schema_path'      => $currentPath . '/../../doctrine/schema',
        );

        $resource = new Parables_Application_Resource_Doctrinepaths($options);
        $resource->setBootstrap($this->bootstrap);
        $values = $resource->init();

        $this->assertArrayHasKey('data_fixtures_path', $values);
        $this->assertArrayHasKey('migrations_path', $values);
        $this->assertArrayHasKey('sql_path', $values);
        $this->assertArrayHasKey('yaml_schema_path', $values);
    }

    /**
     * @expectedException Zend_Application_Resource_Exception
     */
    public function testMissingDsnConnectionOptionThrowsException()
    {
        $options = array(
            'demo' => array(),
        );

        $resource = new Parables_Application_Resource_Doctrineconnections($options);
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
    }
}
