<?php
/**
 * XcacheEngineTest file
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Test\TestCase\Cache\Engine;

use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

/**
 * XcacheEngineTest class
 */
class XcacheEngineTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!extension_loaded('xcache')) {
            $this->markTestSkipped('Xcache is not installed or configured properly');
        }
        Cache::enable();
        Cache::setConfig('xcache', ['engine' => 'Xcache', 'prefix' => 'cake_']);
    }

    /**
     * Helper method for testing.
     *
     * @param array $config
     * @return void
     */
    protected function _configCache($config = [])
    {
        $defaults = [
            'className' => 'Xcache',
            'prefix' => 'cake_',
        ];
        Cache::drop('xcache');
        Cache::setConfig('xcache', array_merge($defaults, $config));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        Cache::drop('xcache');
        Cache::drop('xcache_groups');
    }

    /**
     * testConfig method
     *
     * @return void
     */
    public function testConfig()
    {
        $config = Cache::engine('xcache')->getConfig();
        $expecting = [
            'prefix' => 'cake_',
            'duration' => 3600,
            'probability' => 100,
            'groups' => [],
        ];
        $this->assertArrayHasKey('PHP_AUTH_USER', $config);
        $this->assertArrayHasKey('PHP_AUTH_PW', $config);

        unset($config['PHP_AUTH_USER'], $config['PHP_AUTH_PW']);
        $this->assertEquals($config, $expecting);
    }

    /**
     * testReadAndWriteCache method
     *
     * @return void
     */
    public function testReadAndWriteCache()
    {
        $result = Cache::read('test', 'xcache');
        $expecting = '';
        $this->assertEquals($expecting, $result);

        // String
        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('test', $data, 'xcache');
        $this->assertTrue($result);

        $result = Cache::read('test', 'xcache');
        $expecting = $data;
        $this->assertEquals($expecting, $result);

        // Integer
        $data = 100;
        $result = Cache::write('test', 100, 'xcache');
        $this->assertTrue($result);

        $result = Cache::read('test', 'xcache');
        $this->assertSame(100, $result);

        // Object
        $data = (object)['value' => 'an object'];
        $result = Cache::write('test', $data, 'xcache');
        $this->assertTrue($result);

        $result = Cache::read('test', 'xcache');
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals('an object', $result->value);

        Cache::delete('test', 'xcache');
    }

    /**
     * testExpiry method
     *
     * @return void
     */
    public function testExpiry()
    {
        $this->_configCache(['duration' => 1]);
        $result = Cache::read('test', 'xcache');
        $this->assertFalse($result);

        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('other_test', $data, 'xcache');
        $this->assertTrue($result);

        sleep(2);
        $result = Cache::read('other_test', 'xcache');
        $this->assertFalse($result);

        $this->_configCache(['duration' => '+1 second']);

        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('other_test', $data, 'xcache');
        $this->assertTrue($result);

        sleep(2);
        $result = Cache::read('other_test', 'xcache');
        $this->assertFalse($result);
    }

    /**
     * testDeleteCache method
     *
     * @return void
     */
    public function testDeleteCache()
    {
        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('delete_test', $data, 'xcache');
        $this->assertTrue($result);

        $result = Cache::delete('delete_test', 'xcache');
        $this->assertTrue($result);
    }

    /**
     * testClearCache method
     *
     * @return void
     */
    public function testClearCache()
    {
        if ((PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg')) {
            $this->markTestSkipped('Xcache administration functions are not available for the CLI.');
        }
        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('clear_test_1', $data, 'xcache');
        $this->assertTrue($result);

        $result = Cache::write('clear_test_2', $data, 'xcache');
        $this->assertTrue($result);

        $result = Cache::clear(false, 'xcache');
        $this->assertTrue($result);
    }

    /**
     * testDecrement method
     *
     * @return void
     */
    public function testDecrement()
    {
        $result = Cache::write('test_decrement', 5, 'xcache');
        $this->assertTrue($result);

        $result = Cache::decrement('test_decrement', 1, 'xcache');
        $this->assertEquals(4, $result);

        $result = Cache::read('test_decrement', 'xcache');
        $this->assertEquals(4, $result);

        $result = Cache::decrement('test_decrement', 2, 'xcache');
        $this->assertEquals(2, $result);

        $result = Cache::read('test_decrement', 'xcache');
        $this->assertEquals(2, $result);
    }

    /**
     * testIncrement method
     *
     * @return void
     */
    public function testIncrement()
    {
        $result = Cache::write('test_increment', 5, 'xcache');
        $this->assertTrue($result);

        $result = Cache::increment('test_increment', 1, 'xcache');
        $this->assertEquals(6, $result);

        $result = Cache::read('test_increment', 'xcache');
        $this->assertEquals(6, $result);

        $result = Cache::increment('test_increment', 2, 'xcache');
        $this->assertEquals(8, $result);

        $result = Cache::read('test_increment', 'xcache');
        $this->assertEquals(8, $result);
    }

    /**
     * Tests that configuring groups for stored keys return the correct values when read/written
     * Shows that altering the group value is equivalent to deleting all keys under the same
     * group
     *
     * @return void
     */
    public function testGroupsReadWrite()
    {
        Cache::setConfig('xcache_groups', [
            'engine' => 'Xcache',
            'duration' => 0,
            'groups' => ['group_a', 'group_b'],
            'prefix' => 'test_'
        ]);
        $this->assertTrue(Cache::write('test_groups', 'value', 'xcache_groups'));
        $this->assertEquals('value', Cache::read('test_groups', 'xcache_groups'));

        xcache_inc('test_group_a', 1);
        $this->assertFalse(Cache::read('test_groups', 'xcache_groups'));
        $this->assertTrue(Cache::write('test_groups', 'value2', 'xcache_groups'));
        $this->assertEquals('value2', Cache::read('test_groups', 'xcache_groups'));

        xcache_inc('test_group_b', 1);
        $this->assertFalse(Cache::read('test_groups', 'xcache_groups'));
        $this->assertTrue(Cache::write('test_groups', 'value3', 'xcache_groups'));
        $this->assertEquals('value3', Cache::read('test_groups', 'xcache_groups'));
    }

    /**
     * Tests that deleting from a groups-enabled config is possible
     *
     * @return void
     */
    public function testGroupDelete()
    {
        Cache::setConfig('xcache_groups', [
            'engine' => 'Xcache',
            'duration' => 0,
            'groups' => ['group_a', 'group_b'],
            'prefix' => 'test_'
        ]);
        $this->assertTrue(Cache::write('test_groups', 'value', 'xcache_groups'));
        $this->assertEquals('value', Cache::read('test_groups', 'xcache_groups'));
        $this->assertTrue(Cache::delete('test_groups', 'xcache_groups'));

        $this->assertFalse(Cache::read('test_groups', 'xcache_groups'));
    }

    /**
     * Test clearing a cache group
     *
     * @return void
     */
    public function testGroupClear()
    {
        Cache::setConfig('xcache_groups', [
            'engine' => 'Xcache',
            'duration' => 0,
            'groups' => ['group_a', 'group_b'],
            'prefix' => 'test_'
        ]);

        $this->assertTrue(Cache::write('test_groups', 'value', 'xcache_groups'));
        $this->assertTrue(Cache::clearGroup('group_a', 'xcache_groups'));
        $this->assertFalse(Cache::read('test_groups', 'xcache_groups'));

        $this->assertTrue(Cache::write('test_groups', 'value2', 'xcache_groups'));
        $this->assertTrue(Cache::clearGroup('group_b', 'xcache_groups'));
        $this->assertFalse(Cache::read('test_groups', 'xcache_groups'));
    }
}
