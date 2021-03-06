<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Encapsulates an email forwarder.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Objects_Email_Forwarder extends DA_Objects_Email_MailObject
{
    /** @var string[] */
    private $recipients;

    /**
     * Construct the object.
     *
     * @param string $prefix The part before the @ in the address.
     * @param Domain $domain The containing domain.
     * @param array|string $recipients Array or string containing the recipients.
     */
    public function __construct($prefix, DA_Objects_Domain $domain, $recipients)
    {
        parent::__construct($prefix, $domain);
        $this->recipients = is_string($recipients) ? array_map('trim', explode(',', $recipients)) : $recipients;
    }

    /**
     * Creates a new forwarder.
     *
     * @param Domain $domain
     * @param string $prefix
     * @param string|string[] $recipients
     * @return DA_Objects_Email_Forwarder
     */
    public static function create(DA_Objects_Domain $domain, $prefix, $recipients)
    {
        $domain->invokePost('EMAIL_FORWARDERS', 'create', [
            'user' => $prefix,
            'email' => is_array($recipients) ? implode(',', $recipients) : $recipients,
        ]);
        return new self($prefix, $domain, $recipients);
    }

    /**
     * Deletes the forwarder.
     */
    public function delete()
    {
        $this->invokeDelete('EMAIL_FORWARDERS', 'select0');
    }

    /**
     * Returns a list of the recipients of this forwarder.
     *
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Returns the list of valid aliases for this account.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return array_map(function($domain) {
            return $this->getPrefix() . '@' . $domain;
        }, $this->getDomain()->getDomainNames());
    }
}
