<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for objects exposing a mail address.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class DA_Objects_Email_MailObject extends DA_Objects_DomainObject
{
    /**
     * Delete the object.
     *
     * @param string $command Command to execute.
     * @param string $paramName Parameter name for the delete command.
     */
    protected function invokeDelete($command, $paramName)
    {
        $this->invokePost($command, 'delete', [$paramName => $this->getPrefix()]);
    }

    /**
     * Returns the full email address for this forwarder.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->getPrefix() . '@' . $this->getDomainName();
    }

    /**
     * Returns the domain-agnostic part before the @ in the forwarder.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getName();
    }
}
