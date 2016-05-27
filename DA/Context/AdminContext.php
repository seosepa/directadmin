<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context for administrator functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Context_AdminContext extends DA_Context_ResellerContext
{
    /**
     * Creates a new DA_Objects_Users_Admin level account.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return DA_Objects_Users_Admin The newly created DA_Objects_Users_Admin.
     */
    public function createAdmin($username, $password, $email)
    {
        return $this->createAccount($username, $password, $email, [], 'ACCOUNT_ADMIN', 'DA_Objects_Users_Admin');
    }

    /**
     * Creates a new DA_Objects_Users_Reseller level account.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $domain
     * @param string|array $package Either a package name or an array of options for custom.
     * @param string $ip shared, sharedreseller or assign. Defaults to 'shared'.
     * @return DA_Objects_Users_Reseller
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createReseller($username, $password, $email, $domain, $package = [], $ip = 'shared')
    {
        $options = array_merge(
            ['ip' => $ip, 'domain' => $domain, 'serverip' => 'ON', 'dns' => 'OFF'],
            is_array($package) ? $package : ['package' => $package]
        );
        return $this->createAccount($username, $password, $email, $options, 'ACCOUNT_RESELLER', 'DA_Objects_Users_Reseller');
    }

    /**
     * Returns a list of known admins on the server.
     *
     * @return DA_Objects_Users_Admin[]
     */
    public function getAdmins()
    {
        return DA_Objects_Object::toObjectArray($this->invokeGet('SHOW_ADMINS'), 'DA_Objects_Users_Admin', $this);
    }

    /**
     * Returns a full list of all accounts of any type on the server.
     *
     * @return DA_Objects_Users_User[]
     */
    public function getAllAccounts()
    {
        $accounts = array_merge($this->getAllUsers(), $this->getResellers(), $this->getAdmins());
        ksort($accounts);
        return $accounts;
    }

    /**
     * Returns a full list of all users on the server, so no resellers or admins.
     *
     * @return DA_Objects_Users_User[]
     */
    public function getAllUsers()
    {
        return DA_Objects_Object::toObjectArray($this->invokeGet('SHOW_ALL_USERS'), 'DA_Objects_Users_User', $this);
    }

    /**
     * Returns a specific reseller by name, or NULL if there is no reseller by this name.
     *
     * @param string $username
     * @return null|DA_Objects_Users_Reseller
     */
    public function getReseller($username)
    {
        $resellers = $this->getResellers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * Returns the list of known resellers.
     *
     * @return DA_Objects_Users_Reseller[]
     */
    public function getResellers()
    {
        return DA_Objects_Object::toObjectArray($this->invokeGet('SHOW_RESELLERS'), 'DA_Objects_Users_Reseller', $this);
    }

    /**
     * Returns a new DA_Context_AdminContext acting as the specified admin.
     *
     * @param string $username
     * @param bool $validate Whether to check the admin exists and is an admin.
     * @return DA_Context_AdminContext
     */
    public function impersonateAdmin($username, $validate = false)
    {
        return new DA_Context_AdminContext($this->getConnection()->loginAs($username), $validate);
    }

    /**
     * Returns a new DA_Context_ResellerContext acting as the specified reseller.
     *
     * @param string $username
     * @param bool $validate Whether to check the reseller exists and is a reseller.
     * @return DA_Context_ResellerContext
     */
    public function impersonateReseller($username, $validate = false)
    {
        return new DA_Context_ResellerContext($this->getConnection()->loginAs($username), $validate);
    }
}
