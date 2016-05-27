<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DA_Objects_Users_Admin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Objects_Users_Admin extends DA_Objects_Users_Reseller
{
    /**
     * @inheritdoc
     */
    public function __construct($name, DA_Context_UserContext $context, $config = null)
    {
        parent::__construct($name, $context, $config);
    }

    /**
     * @return DA_Context_AdminContext
     */
    public function impersonate()
    {
        /** @var DA_Context_AdminContext $context */
        if(!($context = $this->getContext()) instanceof DA_Context_AdminContext)
            throw new DA_DirectAdminException('You need to be an admin to impersonate another admin');
        return $context->impersonateAdmin($this->getUsername());
    }
}
