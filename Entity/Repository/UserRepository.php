<?php

namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class UserRepository extends EntityRepository
{
    /**
     * @param bool|null $enabled
     * @return int
     */
    public function getUsersCount($enabled = null)
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->select('COUNT(user.id) as usersCount');

        if ($enabled !== null) {
            $queryBuilder->andWhere('user.enabled = :enabled')
                ->setParameter('enabled', $enabled);
        }

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param EmailOrigin $origin
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOriginOwner(EmailOrigin $origin)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->innerJoin('u.emailOrigins', 'o')
            ->where('o.id = :originId')
            ->setParameter('originId', $origin->getId())
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param string $fullNameQueryPart
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    public function getEmails(
        AclHelper $aclHelper,
        $fullNameQueryPart,
        array $excludedEmails = [],
        $query = null,
        $limit = 100
    ) {
        $primaryEmails = $this->getPrimaryEmails($aclHelper, $fullNameQueryPart, $excludedEmails, $query, $limit);

        $limit -= count($primaryEmails);
        $excludedEmails = array_merge($excludedEmails, $primaryEmails);
        $secondaryEmails = $this->getSecondaryEmails($aclHelper, $fullNameQueryPart, $excludedEmails, $query, $limit);

        $emailResults = array_merge($primaryEmails, $secondaryEmails);

        $emails = [];
        foreach ($emailResults as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @param AclHelper $aclHelper
     * @param string $fullNameQueryPart
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getPrimaryEmails(
        AclHelper $aclHelper,
        $fullNameQueryPart,
        array $excludedEmails = [],
        $query = 100,
        $limit = 100
    ) {
        $qb = $this->createQueryBuilder('u');

        $qb
            ->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('u.email')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('u.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('u.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param string $fullNameQueryPart
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getSecondaryEmails(
        AclHelper $aclHelper,
        $fullNameQueryPart,
        array $excludedEmails = [],
        $query = null,
        $limit = 100
    ) {
        $qb = $this->createQueryBuilder('u');

        $qb
            ->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('e.email')
            ->join('u.emails', 'e')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('e.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('e.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $aclHelper->apply($qb)->getResult();
    }
}
