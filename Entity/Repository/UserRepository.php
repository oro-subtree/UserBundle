<?php

namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

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
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    public function getEmails(array $excludedEmails = [], $query = null, $limit = 100)
    {
        $primaryEmails = $this->getPrimaryEmails($excludedEmails, $query, $limit);
        $secondaryEmails = $this->getSecondaryEmails($excludedEmails, $query, $limit - count($primaryEmails));

        $emailResults = array_merge($primaryEmails, $secondaryEmails);

        $emails = [];
        foreach ($emailResults as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getPrimaryEmails(array $excludedEmails = [], $query = 100, $limit = 100)
    {
        $qb = $this->createQueryBuilder('u');

        $fullName = $this->getFullNameQueryPart();

        $qb
            ->select(sprintf('%s AS name', $fullName))
            ->addSelect('u.email')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullName, ':query'),
                    $qb->expr()->like('u.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('u.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getSecondaryEmails(array $excludedEmails = [], $query = null, $limit = 100)
    {
        $qb = $this->createQueryBuilder('u');

        $fullName = $this->getFullNameQueryPart();

        $qb
            ->select(sprintf('%s AS name', $fullName))
            ->addSelect('e.email')
            ->join('u.emails', 'e')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullName, ':query'),
                    $qb->expr()->like('e.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('e.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string
     */
    protected function getFullNameQueryPart()
    {
        $fields = [
            'namePrefix',
            'nameSuffix',
            'firstName',
            'middleName',
            'lastName',
        ];

        $alias = 'u';
        $queryParts = array_map(function ($part) use ($alias) {
            return sprintf('COALESCE(%s.%s, \' \')', $alias, $part);
        }, $fields);

        return sprintf('CONCAT(%s)', implode(', ', $queryParts));
    }
}
