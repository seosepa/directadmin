<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Encapsulates a contextual connection to a DA_DirectAdmin server.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class DA_Context_BaseContext
{
    /** @var DA_DirectAdmin */
    private $connection;

    /**
     * Constructs the object.
     *
     * @param DA_DirectAdmin $connection A prepared connection.
     */
    public function __construct(DA_DirectAdmin $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the internal connection wrapper.
     *
     * @return DA_DirectAdmin
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Invokes the DA_DirectAdmin API via HTTP GET.
     *
     * @param string $command DA_DirectAdmin API command to invoke.
     * @param array $query Optional query parameters.
     * @return array The parsed and validated response.
     */
    public function invokeGet($command, $query = [])
    {
        return $this->connection->invoke('GET', $command, ['query' => $query]);
    }

    /**
     * Invokes the DA_DirectAdmin API via HTTP POST.
     *
     * @param string $command DA_DirectAdmin API command to invoke.
     * @param array $postParameters Optional form parameters.
     * @return array The parsed and validated response.
     */
    public function invokePost($command, $postParameters = [])
    {
        return $this->connection->invoke('POST', $command, ['form_params' => $postParameters]);
    }
}
