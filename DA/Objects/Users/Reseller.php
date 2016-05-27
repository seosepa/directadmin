<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DA_Objects_Users_Reseller
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Objects_Users_Reseller extends DA_Objects_Users_User
{
    /**
     * @inheritdoc
     */
    public function __construct($name, DA_Context_UserContext $context, $config = null)
    {
        parent::__construct($name, $context, $config);
    }

    /**
     * @param string $username
     * @return null|User
     */
    public function getUser($username)
    {
        $users = $this->getUsers();
        return isset($users[$username]) ? $users[$username] : null;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return DA_Objects_Object::toObjectArray($this->getContext()->invokeGet('SHOW_USERS', ['reseller' => $this->getUsername()]),
                                     DA_Objects_Users_User::class, $this->getContext());

    }

    /**
     * @return DA_Context_ResellerContext
     */
    public function impersonate()
    {
        /** @var DA_Context_AdminContext $context */
        if(!($context = $this->getContext()) instanceof DA_Context_AdminContext)
            throw new DA_DirectAdminException('You need to be an admin to impersonate a reseller');
        return $context->impersonateReseller($this->getUsername());
    }
}
