<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Cake\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;

/**
 * EventDispatcherTrait test case
 */
class EventDispatcherTraitTest extends TestCase
{
    /**
     * @var \Cake\Event\EventDispatcherTrait
     */
    public $subject;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->subject = $this->getObjectForTrait(EventDispatcherTrait::class);
    }

    /**
     * testIsInitiallyEmpty
     *
     * @return void
     */
    public function testIsInitiallyEmpty()
    {
        $this->assertAttributeEmpty('_eventManager', $this->subject);
    }

    /**
     * testEventManager
     *
     * @group deprecated
     * @return void
     */
    public function testEventManager()
    {
        $this->deprecated(function () {
            $eventManager = new EventManager();
            $this->subject->eventManager($eventManager);

            $this->assertSame($eventManager, $this->subject->eventManager());
        });
    }

    /**
     * testGetEventManager
     *
     * @return void
     */
    public function testGetEventManager()
    {
        $this->assertInstanceOf(EventManager::class, $this->subject->getEventManager());
    }

    /**
     * testSetEventManager
     *
     * @return void
     */
    public function testSetEventManager()
    {
        $eventManager = new EventManager();

        $this->subject->setEventManager($eventManager);

        $this->assertSame($eventManager, $this->subject->getEventManager());
    }

    /**
     * testDispatchEvent
     *
     * @return void
     */
    public function testDispatchEvent()
    {
        $event = $this->subject->dispatchEvent('some.event', ['foo' => 'bar']);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame($this->subject, $event->getSubject());
        $this->assertEquals('some.event', $event->getName());
        $this->assertEquals(['foo' => 'bar'], $event->getData());
    }
}
