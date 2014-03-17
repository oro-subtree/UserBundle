<?php

namespace Oro\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oro:user:create')
            ->setDescription('Create admin user.')
            ->addOption('user-name', null, InputOption::VALUE_REQUIRED, 'User name')
            ->addOption('user-email', null, InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('user-firstname', null, InputOption::VALUE_REQUIRED, 'User first name')
            ->addOption('user-lastname', null, InputOption::VALUE_REQUIRED, 'User last name')
            ->addOption('user-password', null, InputOption::VALUE_REQUIRED, 'User password')
            ->addOption('user-role', null, InputOption::VALUE_REQUIRED, 'User role')
            ->addOption('user-business-unit', null, InputOption::VALUE_REQUIRED, 'User business unit');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container    = $this->getContainer();
        $options      = $input->getOptions();
        $user         = $container->get('oro_user.manager')->createUser();
        $role         = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => $options['user-role']));
        $businessUnit = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => $options['user-business-unit']));
        $user
            ->setUsername($options['user-name'])
            ->setEmail($options['user-email'])
            ->setFirstName($options['user-firstname'])
            ->setLastName($options['user-lastname'])
            ->setPlainPassword($options['user-password'])
            ->setEnabled(true)
            ->addRole($role)
            ->setOwner($businessUnit)
            ->addBusinessUnit($businessUnit);
        $container->get('oro_user.manager')->updateUser($user);
    }
}
