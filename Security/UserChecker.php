<?php

namespace Oro\Bundle\UserBundle\Security;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Exception\PasswordChangedException;
use Oro\Component\DependencyInjection\ServiceLink;

class UserChecker extends BaseUserChecker
{
    /** @var ServiceLink */
    protected $securityContextLink;

    /** @var FlashBagInterface */
    protected $flashBag;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ServiceLink         $securityContextLink
     * @param FlashBagInterface   $flashBag
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ServiceLink $securityContextLink,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->securityContextLink = $securityContextLink;
        $this->flashBag            = $flashBag;
        $this->translator          = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        parent::checkPreAuth($user);

        if ($user instanceof User
            && null !== $this->securityContextLink->getService()->getToken()
            && null !== $user->getPasswordChangedAt()
            && null !== $user->getLastLogin()
            && $user->getPasswordChangedAt() > $user->getLastLogin()
        ) {
            $this->flashBag->add(
                'error',
                $this->translator->trans('oro.user.security.password_changed.message')
            );

            $exception = new PasswordChangedException('Invalid password.');
            $exception->setUser($user);

            throw $exception;
        }

        if ($user instanceof User && $this->isExpiredPassword($user)) {
            $exception = new CredentialsExpiredException('Invalid password.');
            $exception->setUser($user);

            throw $exception;
        }
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function isExpiredPassword(User $user)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if (($user->getPasswordExpiresAt() < $now)) {
            return true;
        }

        return $user->getAuthStatus() && $user->getAuthStatus()->getId() === 'expired';
    }
}
