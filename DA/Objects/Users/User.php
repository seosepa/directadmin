<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Objects_Users_User extends DA_Objects_Object
{
    const CACHE_CONFIG          = 'config';
    const CACHE_DATABASES       = 'databases';
    const CACHE_USAGE           = 'usage';

    /** @var DA_Objects_Domain[] **/
    private $domains;

    /**
     * Construct the object.
     *
     * @param string $name Username of the account.
     * @param DA_Context_UserContext $context The context managing this object.
     * @param mixed|null $config An optional preloaded configuration.
     */
    public function __construct($name, DA_Context_UserContext $context, $config = null)
    {
        parent::__construct($name, $context);
        if(isset($config))
            $this->setCache(self::CACHE_CONFIG, $config);
    }

    /**
     * Clear the object's internal cache.
     */
    public function clearCache()
    {
        unset($this->domains);
        parent::clearCache();
    }

    /**
     * Creates a new database under this user.
     *
     * @param string $name Database name, without <user>_ prefix.
     * @param string $username Username to access the database with, without <user>_ prefix.
     * @param string|null $password Password, or null if database user already exists.
     * @return DA_Objects_Database Newly created database.
     */
    public function createDatabase($name, $username, $password = null)
    {
        $db = DA_Objects_Database::create($this->getSelfManagedUser(), $name, $username, $password);
        $this->clearCache();
        return $db;

    }

    /**
     * Creates a new domain under this user.
     *
     * @param string $domainName Domain name to create.
     * @param float|null $bandwidthLimit Bandwidth limit in MB, or NULL to share with account.
     * @param float|null $diskLimit Disk limit in MB, or NULL to share with account.
     * @param bool|null $ssl Whether SSL is to be enabled, or NULL to fallback to account default.
     * @param bool|null $php Whether PHP is to be enabled, or NULL to fallback to account default.
     * @param bool|null $cgi Whether CGI is to be enabled, or NULL to fallback to account default.
     * @return DA_Objects_Domain Newly created domain.
     */
    public function createDomain($domainName, $bandwidthLimit = null, $diskLimit = null, $ssl = null, $php = null, $cgi = null)
    {
        $domain = DA_Objects_Domain::create($this->getSelfManagedUser(), $domainName, $bandwidthLimit, $diskLimit, $ssl, $php, $cgi);
        $this->clearCache();
        return $domain;
    }

    /**
     * @return string The username.
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * Returns the bandwidth limit of the user.
     *
     * @return float|null Limit in megabytes, or null for unlimited.
     */
    public function getBandwidthLimit()
    {
        return floatval($this->getConfig('bandwidth')) ?: null;
    }

    /**
     * Returns the current period's bandwidth usage in megabytes.
     *
     * @return float
     */
    public function getBandwidthUsage()
    {
        return floatval($this->getUsage('bandwidth'));
    }

    /**
     * Returns the database quota of the user.
     *
     * @return int|null Limit, or null for unlimited.
     */
    public function getDatabaseLimit()
    {
        return intval($this->getConfig('mysql')) ?: null;
    }

    /**
     * Returns the current number databases in use.
     *
     * @return int
     */
    public function getDatabaseUsage()
    {
        return intval($this->getUsage('mysql'));
    }

    /**
     * Returns the disk quota of the user.
     *
     * @return float|null Limit in megabytes, or null for unlimited.
     */
    public function getDiskLimit()
    {
        return floatval($this->getConfig('quota')) ?: null;
    }

    /**
     * Returns the current disk usage in megabytes.
     *
     * @return float
     */
    public function getDiskUsage()
    {
        return floatval($this->getUsage('quota'));
    }

    /**
     * @return Domain|null The default domain for the user, if any.
     */
    public function getDefaultDomain()
    {
        if(empty($name = $this->getConfig('domain')))
            return null;
        return $this->getDomain($name);
    }

    /**
     * Returns maximum number of domains allowed to this user, or NULL for unlimited.
     *
     * @return int|null
     */
    public function getDomainLimit()
    {
        return intval($this->getConfig('vdomains')) ?: null;
    }

    /**
     * Returns number of domains owned by this user.
     *
     * @return int
     */
    public function getDomainUsage()
    {
        return intval($this->getUsage('vdomains'));
    }

    /**
     * Returns whether the user is currently suspended.
     *
     * @return bool
     */
    public function isSuspended()
    {
        return ($this->getConfig('suspended') === 'ON');
    }

    /**
     * @return DA_Objects_Domain[]
     */
    public function getDatabases()
    {
        return $this->getCache(self::CACHE_DATABASES, function() {
            $databases = [];
            foreach($this->getSelfManagedContext()->invokeGet('DATABASES') as $fullName) {
                list($user, $db) = explode('_', $fullName, 2);
                if($this->getUsername() != $user)
                    throw new DA_DirectAdminException('Username incorrect on database ' . $fullName);
                $databases[$db] = new DA_Objects_Database($db, $this, $this->getSelfManagedContext());
            }
            return $databases;
        });
    }

    /**
     * @param string $domainName
     * @return null|DA_Objects_Domain
     */
    public function getDomain($domainName)
    {
        if(!isset($this->domains))
            $this->getDomains();
        return isset($this->domains[$domainName]) ? $this->domains[$domainName] : null;
    }

    /**
     * @return DA_Objects_Domain[]
     */
    public function getDomains()
    {
        if(!isset($this->domains))
        {
            if(!$this->isSelfManaged())
                $this->domains = $this->impersonate()->getDomains();
            else
                $this->domains = DA_Objects_Object::toRichObjectArray($this->getContext()->invokeGet('ADDITIONAL_DOMAINS'), DA_Objects_Domain::class, $this->getContext());
        }
        return $this->domains;
    }

    /**
     * @return string The user type, as one of the ACCOUNT_TYPE_ constants in the DA_DirectAdmin class.
     */
    public function getType()
    {
        return $this->getConfig('usertype');
    }

    /**
     * @return bool Whether the user can use CGI.
     */
    public function hasCGI()
    {
        return DA_Utility_Conversion::toBool($this->getConfig('cgi'));
    }

    /**
     * @return bool Whether the user can use PHP.
     */
    public function hasPHP()
    {
        return DA_Utility_Conversion::toBool($this->getConfig('php'));
    }

    /**
     * @return bool Whether the user can use SSL.
     */
    public function hasSSL()
    {
        return DA_Utility_Conversion::toBool($this->getConfig('ssl'));
    }

    /**
     * @return DA_Context_UserContext
     */
    public function impersonate()
    {
        /** @var DA_Context_ResellerContext $context */
        if(!($context = $this->getContext()) instanceof DA_Context_ResellerContext)
            throw new DA_DirectAdminException('You need to be at least a reseller to impersonate');
        return $context->impersonateUser($this->getUsername());
    }

    /**
     * Modifies the configuration of the user. For available keys in the array check the documentation on
     * CMD_API_MODIFY_USER in the linked document.
     *
     * @param array $newConfig Associative array of values to be modified.
     * @url http://www.directadmin.com/api.html#modify
     */
    public function modifyConfig(array $newConfig)
    {
        $this->getContext()->invokePost('MODIFY_USER', array_merge(
                $this->loadConfig(),
                DA_Utility_Conversion::processUnlimitedOptions($newConfig),
                ['action' => 'customize', 'user' => $this->getUsername()]
        ));
        $this->clearCache();
    }

    /**
     * @param bool $newValue Whether catch-all email is enabled for this user.
     */
    public function setAllowCatchall($newValue)
    {
        $this->modifyConfig(['catchall' => DA_Utility_Conversion::onOff($newValue)]);
    }

    /**
     * @param float|null $newValue New value, or NULL for unlimited.
     */
    public function setBandwidthLimit($newValue)
    {
        $this->modifyConfig(['bandwidth' => isset($newValue) ? floatval($newValue) : null]);
    }

    /**
     * @param float|null $newValue New value, or NULL for unlimited.
     */
    public function setDiskLimit($newValue)
    {
        $this->modifyConfig(['quota' => isset($newValue) ? floatval($newValue) : null]);
    }

    /**
     * @param int|null $newValue New value, or NULL for unlimited.
     */
    public function setDomainLimit($newValue)
    {
        $this->modifyConfig(['vdomains' => isset($newValue) ? intval($newValue) : null]);
    }

    /**
     * Constructs the correct object from the given user config.
     *
     * @param array $config The raw config from DA_DirectAdmin.
     * @param DA_Context_UserContext $context The context within which the config was retrieved.
     * @return DA_Objects_Users_Admin|DA_Objects_Users_Reseller|DA_Objects_Users_User The correct object.
     * @throws DA_DirectAdminException If the user type could not be determined.
     */
    public static function fromConfig($config, DA_Context_UserContext $context)
    {
        $name = $config['username'];
        switch($config['usertype'])
        {
            case DA_DirectAdmin::ACCOUNT_TYPE_USER:
                return new DA_Objects_Users_User($name, $context, $config);
            case DA_DirectAdmin::ACCOUNT_TYPE_RESELLER:
                return new DA_Objects_Users_Reseller($name, $context, $config);
            case DA_DirectAdmin::ACCOUNT_TYPE_ADMIN:
                return new DA_Objects_Users_Admin($name, $context, $config);
            default:
                throw new DA_DirectAdminException("Unknown user type '$config[usertype]'");
        }
    }

    /**
     * Internal function to safe guard config changes and cache them.
     *
     * @param string $item Config item to retrieve.
     * @return mixed The value of the config item, or NULL.
     */
    private function getConfig($item)
    {
        return $this->getCacheItem(self::CACHE_CONFIG, $item, function() { return $this->loadConfig(); });
    }

    /**
     * Internal function to safe guard usage changes and cache them.
     *
     * @param string $item Usage item to retrieve.
     * @return mixed The value of the stats item, or NULL.
     */
    private function getUsage($item)
    {
        return $this->getCacheItem(self::CACHE_USAGE, $item, function() {
            return $this->getContext()->invokeGet('SHOW_USER_USAGE', ['user' => $this->getUsername()]);
        });
    }

    /**
     * @return DA_Context_UserContext The local user context.
     */
    protected function getSelfManagedContext()
    {
        return $this->isSelfManaged() ? $this->getContext() : $this->impersonate();
    }


    /**
     * @return DA_Objects_Users_User The user acting as himself.
     */
    protected function getSelfManagedUser()
    {
        return $this->isSelfManaged() ? $this : $this->impersonate()->getContextUser();
    }

    /**
     * @return bool Whether the account is managing itself.
     */
    protected function isSelfManaged()
    {
        return ($this->getUsername() === $this->getContext()->getUsername());
    }

    /**
     * Loads the current user configuration from the server.
     *
     * @return array
     */
    private function loadConfig()
    {
        return $this->getContext()->invokeGet('SHOW_USER_CONFIG', ['user' => $this->getUsername()]);
    }
}
