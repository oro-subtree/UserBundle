<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\UserApi;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'user_api_key';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var \Oro\Bundle\UserBundle\Entity\UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');
        $role = $userManager->getStorageManager()
            ->getRepository('OroUserBundle:Role')
            ->findBy(array('role' => 'IS_AUTHENTICATED_ANONYMOUSLY'));

        $user = $userManager->createUser();

        $organizationManager = $this->container->get('oro_organization.organization_manager');
        $org = $organizationManager->getOrganizationRepo()->getFirst();

        $api = new UserApi();
        $api->setApiKey('user_api_key')
            ->setOrganization($org)
            ->setUser($user);

        $user
            ->setUsername(self::USER_NAME)
            ->setPlainPassword(self::USER_PASSWORD)
            ->setFirstName('Simple')
            ->setLastName('User')
            ->addRole($role[0])
            ->setEmail('simple@example.com')
            ->addOrganization($org)
            ->addApiKey($api)
            ->setSalt('');

        $userManager->updateUser($user);

    }
}
