# CodeIgniter Fork

In order to use this lib with CodeIgniter, i have to get rid of all the fancy pancy stuff :)

* Added autoloader

* Removed namespaces
* Removed tests
* Removed composer

* Added guzzle for codeignier (https://github.com/rohitbh09/codeigniter-guzzle)

--



# DirectAdmin API client

This is a PHP client library to manage DirectAdmin control panel servers. We simply decided to develop this as we needed
automation of our own DirectAdmin servers, and the existing implementations were unsupported and incomplete.

[API documentation for this project is automatically generated on each push](https://omines.github.io/directadmin/api/).

## Installation

Just copy the DA & Guzzle folders to application/libraries


## Basic usage

$adminContext = DirectAdmin::connectAdmin('http://hostname:2222', 'admin', 'pass');
$resellerContext = DirectAdmin::connectReseller('http://hostname:2222', 'reseller', 'pass');
$userContext = DirectAdmin::connectUser('http://hostname:2222', 'user', 'pass');
```

These functions return an
[`AdminContext`](https://omines.github.io/directadmin/api/class-Omines.DirectAdmin.Context.AdminContext.html),
[`ResellerContext`](https://omines.github.io/directadmin/api/class-Omines.DirectAdmin.Context.ResellerContext.html), and
[`UserContext`](https://omines.github.io/directadmin/api/class-Omines.DirectAdmin.Context.UserContext.html)
respectively exposing the functionality available at the given level. All three extend eachother as DirectAdmin uses a
strict is-a model. To act on behalf of a user you can use impersonation calls:

```php
$resellerContext = $adminContext->impersonateReseller($resellerName);
$userContext = $resellerContext->impersonateUser($userName);
```
Both are essentially the same but mapped to the correct return type. Impersonation is also done implicitly
when managing a user's domains:

```php
$domain = $adminContext->getUser('user')->getDomain('example.tld');
```
This returns, if the domain exists, a [`Domain`](https://omines.github.io/directadmin/api/class-Omines.DirectAdmin.Objects.Domain.html)
instance in the context of its owning user, allowing you to manage its email accounts et al transparently.

## Contributions

As the DirectAdmin API keeps expanding pull requests are welcomed, as are requests for specific functionality.
Pull requests should in general include proper unit tests for the implemented or corrected functions.

For more information about unit testing see the `README.md` in the tests folder.

## Legal

this is a fork of:

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.

The project is not in any way affiliated with JBMC Software or its employees.
