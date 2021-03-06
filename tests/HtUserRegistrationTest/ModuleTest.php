<?php
namespace HtUserRegistrationTest;

use HtUserRegistration\Module;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\SharedEventManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
        $this->assertInternalType('array', $module->getServiceConfig());
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }
    
    public function testEventListenerIsAttached()
    {
        $module         = new Module();
        $mvcEvent       = $this->getMock('Laminas\EventManager\EventInterface');
        $application    = $this->getMock('Laminas\Mvc\Application', [], [], '', false);
        $eventManager   = new EventManager;
        $eventManager->setSharedManager(new SharedEventManager);
        $serviceManager = new ServiceManager;

        $mvcEvent->expects($this->once())
            ->method('getParam')
            ->with('application')
            ->will($this->returnValue($application));
        $application->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $application->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $eventManager->addIdentifiers(['ZfcUser\Service\User']);
        $event = new Event;
        $event->setName('register.post');

        $userRegistrationService = $this->getMock('HtUserRegistration\Service\UserRegistrationServiceInterface');
        $serviceManager->setService('HtUserRegistration\UserRegistrationService', $userRegistrationService);
        $userRegistrationService->expects($this->once())
            ->method('onUserRegistration')
            ->with($event);

        $module->onBootstrap($mvcEvent);
        $eventManager->trigger($event);
    }  
}
