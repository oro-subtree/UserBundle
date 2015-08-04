<?php

namespace Oro\Bundle\UserBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;

class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    /** @var Registry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DQLNameFormatter */
    protected $nameFormatter;

    /**
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param DQLNameFormatter $nameFormatter
     */
    public function __construct(
        Registry $registry,
        AclHelper $aclHelper,
        DQLNameFormatter $nameFormatter
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        $fullNameQueryPart = $this->nameFormatter->getFormattedNameDQL(
            'u',
            'Oro\Bundle\UserBundle\Entity\User'
        );

        $primaryEmailsQb = $this->getUserRepository()
            ->getPrimaryEmailsQb($fullNameQueryPart, $args->getExcludedEmails(), $args->getQuery())
            ->setMaxResults($args->getLimit());

        $primaryEmailsResult = $this->aclHelper->apply($primaryEmailsQb)->getResult();
        $emails = $this->emailsFromResult($primaryEmailsResult);

        $limit = $args->getLimit() - count($emails);

        if ($limit > 0) {
            $excludedEmails = array_merge($args->getExcludedEmails(), array_keys($emails));
            $secondaryEmailsQb = $this->getUserRepository()
                ->getSecondaryEmailsQb($fullNameQueryPart, $excludedEmails, $args->getQuery())
                ->setMaxResults($limit);

            $secondaryEmailsResult = $this->aclHelper->apply($secondaryEmailsQb)->getResult();
            $emails = array_merge($emails, $this->emailsFromResult($secondaryEmailsResult));
        }

        return $emails;
    }

    /**
     * {@inheritdoc}
     */
    public function getSection()
    {
        return 'oro.user.entity_plural_label';
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function emailsFromResult(array $result)
    {
        $emails = [];
        foreach ($result as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->registry->getRepository('OroUserBundle:User');
    }
}
