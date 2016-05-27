<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Encapsulates a domain-bound object.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class DA_Objects_DomainObject extends DA_Objects_Object
{
    /** @var Domain */
    private $domain;

    /**
     * @param string $name Canonical name for the object.
     * @param Domain $domain Domain to which the object belongs.
     */
    protected function __construct($name, DA_Objects_Domain $domain)
    {
        parent::__construct($name, $domain->getContext());
        $this->domain = $domain;
    }

    /**
     * Invokes a POST command on a domain object.
     *
     * @param string $command Command to invoke.
     * @param string $action Action to execute.
     * @param array $parameters Additional options for the command.
     * @param bool $clearCache Whether to clear the domain cache.
     * @return array Response from the API.
     */
    protected function invokePost($command, $action, $parameters = [], $clearCache = true)
    {
        return $this->domain->invokePost($command, $action, $parameters, $clearCache);
    }

    /**
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domain->getDomainName();
    }

    /**
     * Converts an associative array of descriptors to objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param Domain $domain
     * @return array
     */
    public static function toDomainObjectArray(array $items, $class, DA_Objects_Domain $domain)
    {
        array_walk($items, function(&$value, $name) use ($class, $domain) {
            $value = new $class($name, $domain, $value);
        });
        return $items;
    }
}
