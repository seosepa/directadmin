<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DA_DirectAdmin API main class, encapsulating a specific account connection to a single server.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_DirectAdmin
{
    const ACCOUNT_TYPE_ADMIN            = 'admin';
    const ACCOUNT_TYPE_RESELLER         = 'reseller';
    const ACCOUNT_TYPE_USER             = 'user';

    /** @var string */
    private $authenticatedUser;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $baseUrl;

    /** @var GuzzleHttp\Client */
    private $connection;

    /**
     * Connects to DA_DirectAdmin with an admin account.
     *
     * @param string $url The base URL of the DA_DirectAdmin server.
     * @param string $username The username of the account.
     * @param string $password The password of the account.
     * @param bool $validate Whether to ensure the account exists and is of the correct type.
     * @return DA_Context_AdminContext
     */
    public static function connectAdmin($url, $username, $password, $validate = false)
    {
        return new DA_Context_AdminContext(new self($url, $username, $password), $validate);
    }

    /**
     * Connects to DA_DirectAdmin with a reseller account.
     *
     * @param string $url The base URL of the DA_DirectAdmin server.
     * @param string $username The username of the account.
     * @param string $password The password of the account.
     * @param bool $validate Whether to ensure the account exists and is of the correct type.
     * @return DA_Context_ResellerContext
     */
    public static function connectReseller($url, $username, $password, $validate = false)
    {
        return new DA_Context_ResellerContext(new self($url, $username, $password), $validate);
    }

    /**
     * Connects to DA_DirectAdmin with a user account.
     *
     * @param string $url The base URL of the DA_DirectAdmin server.
     * @param string $username The username of the account.
     * @param string $password The password of the account.
     * @param bool $validate Whether to ensure the account exists and is of the correct type.
     * @return DA_Context_UserContext
     */
    public static function connectUser($url, $username, $password, $validate = false)
    {
        return new DA_Context_UserContext(new self($url, $username, $password), $validate);
    }

    /**
     * Creates a connection wrapper to DA_DirectAdmin as the specified account.
     *
     * @param string $url The base URL of the DA_DirectAdmin server.
     * @param string $username The username of the account.
     * @param string $password The password of the account.
     */
    protected function __construct($url, $username, $password)
    {
        $accounts = explode('|', $username);
        $this->authenticatedUser = current($accounts);
        $this->username = end($accounts);
        $this->password = $password;
        $this->baseUrl = rtrim($url, '/') . '/';
        $this->connection = new GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'auth' => [$username, $password],
            'http_errors' => true,
            'verify' => false
        ]);
    }

    /**
     * Returns the username behind the current connection.
     *
     * @return string Currently logged in user's username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Invokes the DA_DirectAdmin API with specific options.
     *
     * @param string $method HTTP method to use (ie. GET or POST)
     * @param string $command DA_DirectAdmin API command to invoke.
     * @param array $options Guzzle options to use for the call.
     * @return array The unvalidated response.
     * @throws DA_DirectAdminException If anything went wrong on the network level.
     */
    public function invoke($method, $command, $options = [])
    {
        $result = $this->rawRequest($method, '/CMD_API_' . $command, $options);
        if(!empty($result['error']))
            throw new DA_DirectAdminException("$method to $command failed: $result[details] ($result[text])");
        return DA_Utility_Conversion::sanitizeArray($result);
    }

    /**
     * Returns a clone of the connection logged in as a managed user or reseller.
     *
     * @param string $username
     * @return DA_DirectAdmin
     */
    public function loginAs($username)
    {
        // DA_DirectAdmin format is to just pipe the accounts together under the master password
        return new self($this->baseUrl, $this->authenticatedUser . "|{$username}", $this->password);
    }

    /**
     * Sends a raw request to DA_DirectAdmin.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     */
    private function rawRequest($method, $uri, $options)
    {
        try
        {
            $response = $this->connection->request($method, $uri, $options);
            if($response->getHeader('Content-Type')[0] == 'text/html')
                throw new DA_DirectAdminException('DA ' . $uri . ' _DirectAdmin API returned an error: ' . strip_tags($response->getBody()->getContents()));
            $body = $response->getBody()->getContents();
            return DA_Utility_Conversion::responseToArray($body);
        }
        catch(TransferException $exception)
        {
            // Rethrow anything that causes a network issue
            throw new DA_DirectAdminException("Request to $uri using $method failed", 0, $exception);
        }
    }
}
