<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Oro\Bundle\UserBundle\Entity\User';

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $om;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EncoderFactoryInterface
     */
    protected $ef;

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->ef = $this->getMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->om));

        $this->userManager = new UserManager(static::USER_CLASS, $this->registry, $this->ef);
    }

    protected function tearDown()
    {
        unset($this->ef, $this->om, $this->registry, $this->userManager);
    }

    public function testGetApi()
    {
        $user = new User();
        $organization = new Organization();
        $userApi = new UserApi();

        $repository = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\Repository\UserApiRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->with('OroUserBundle:UserApi')
            ->will($this->returnValue($repository));

        $repository->expects($this->once())
            ->method('getApi')
            ->with($user, $organization)
            ->will($this->returnValue($userApi));

        $this->assertSame($userApi, $this->userManager->getApi($user, $organization));
    }
}
