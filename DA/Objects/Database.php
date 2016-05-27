<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Database
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class DA_Objects_Database extends DA_Objects_Object
{
    /** @var User */
    private $owner;

    /** @var string */
    private $databaseName;
    /**
     * @param array $info
     * @param DA_Context_UserContext $context Context within which the object is valid.
     */
    public function __construct($name, DA_Objects_Users_User $owner, DA_Context_UserContext $context)
    {
        parent::__construct($name, $context);
        $this->owner = $owner;
        $this->databaseName = $this->owner->getUsername() . '_' . $this->getName();
    }

    /**
     * Creates a new database under the specified user.
     *
     * @param User $user Owner of the database.
     * @param string $name Database name, without <user>_ prefix.
     * @param string $username Username to access the database with, without <user>_ prefix.
     * @param string|null $password Password, or null if database user already exists.
     * @return DA_Objects_Database Newly created database.
     */
    public static function create(DA_Objects_Users_User $user, $name, $username, $password)
    {
        $options = [
            'action' => 'create',
            'name' => $name,
        ];
        if(!empty($password)) {
            $options += ['user' => $username, 'passwd' => $password, 'passwd2' => $password];
        } else {
            $options += ['userlist' => $username];
        }
        $user->getContext()->invokePost('DATABASES', $options);
        return new self($name, $user, $user->getContext());
    }

    /**
     * Deletes this database from the user.
     */
    public function delete()
    {
        $this->getContext()->invokePost('DATABASES', [
            'action' => 'delete',
            'select0' => $this->getDatabaseName(),
        ]);
        $this->getContext()->getContextUser()->clearCache();
    }

    /**
     * @return string Name of the database.
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
