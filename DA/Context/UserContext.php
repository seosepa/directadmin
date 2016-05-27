<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context for user functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Context_UserContext extends DA_Context_BaseContext
{
    /** @var DA_Objects_Users_User */
    private $user;

    /**
     * Constructs the object.
     *
     * @param DA_DirectAdmin $connection A prepared connection.
     * @param bool $validate Whether to check if the connection matches the context.
     */
    public function __construct(DA_DirectAdmin $connection, $validate = false)
    {
        parent::__construct($connection);
        if($validate)
        {
            $classMap = [
                DA_DirectAdmin::ACCOUNT_TYPE_ADMIN => 'DA_Context_AdminContext',
                DA_DirectAdmin::ACCOUNT_TYPE_RESELLER => 'DA_Context_ResellerContext',
                DA_DirectAdmin::ACCOUNT_TYPE_USER => 'DA_Context_UserContext',
            ];
            if($classMap[$this->getType()] != get_class($this))
                throw new DA_DirectAdminException('Validation mismatch on context construction');
        }
    }

    /**
     * Returns the type of the account (user/reseller/admin).
     *
     * @return string One of the DA_DirectAdmin::ACCOUNT_TYPE_ constants describing the type of underlying account.
     */
    public function getType()
    {
        return $this->getContextUser()->getType();
    }

    /**
     * Returns the actual user object behind the context.
     *
     * @return DA_Objects_Users_User The user object behind the context.
     */
    public function getContextUser()
    {
        if(!isset($this->user))
            $this->user = DA_Objects_Users_User::fromConfig($this->invokeGet('SHOW_USER_CONFIG'), $this);
        return $this->user;
    }

    /**
     * Returns a domain managed by the current user.
     *
     * @param string $domainName The requested domain name.
     * @return null|Domain The domain if found, or NULL if it does not exist.
     */
    public function getDomain($domainName)
    {
        return $this->getContextUser()->getDomain($domainName);
    }

    /**
     * Returns a full list of the domains managed by the current user.
     *
     * @return DA_Objects_Domain[]
     */
    public function getDomains()
    {
        return $this->getContextUser()->getDomains();
    }

    /**
     * Returns the username of the current context.
     *
     * @return string Username for the current context.
     */
    public function getUsername()
    {
        return $this->getConnection()->getUsername();
    }
}
