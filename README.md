<h1 align="center">vidoomy/notification-bundle</h1>

<p align="center">
An easy yet powerful notification bundle for Symfony 4
<br>
<br>
<a href="https://packagist.org/packages/vidoomy/notification-bundle"><img src="https://poser.pugx.org/vidoomy/notification-bundle/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/vidoomy/notification-bundle"><img src="https://poser.pugx.org/vidoomy/notification-bundle/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/vidoomy/notification-bundle"><img src="https://poser.pugx.org/vidoomy/notification-bundle/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/vidoomy/notification-bundle"><img src="https://poser.pugx.org/vidoomy/notification-bundle/license" alt="License"></a>
</p>

<p align="center">
<a href="https://insight.sensiolabs.com/projects/697abbcc-4b15-418a-a6c9-e662787fed48"><img src="https://insight.sensiolabs.com/projects/697abbcc-4b15-418a-a6c9-e662787fed48/big.png" alt="SensioLabsInsight"></a>
</p>

<p align="center"><img src="http://i.imgur.com/07OcF6c.gif" alt="vidoomy/notificationBundle"></p>

Create and manage notifications in an efficient way.

Symfony support :
  * 3.x
  * 4.x

## NOW SUPPORTS SYMFONY FLEX !

Version 3.0 out now.

## Features

- Easy setup
- Easy to use
- Powerful notification management
- Simple Twig render methods
- Fully customizable
- Multiple notifiables entities
- No bloated dependencies (little requirements)

Notice: Only Doctrine ORM is supported for now.



## Installation & usage

This bundle is available on [packagist](https://packagist.org/packages/vidoomy/notification-bundle).

Add notification-bundle to your project :

```bash
$ composer require vidoomy/notification-bundle
```

**See [documentation](Resources/doc/index.rst) for next steps**

### Basic usage

```php
class MyController extends Controller
{

    ...

    public function sendNotification(Request $request)
    {
      $manager = $this->get('vidoomy.notification');
      $notif = $manager->createNotification('Hello world!');
      $notif->setMessage('This a notification.');
      $notif->setLink('https://symfony.com/');
      // or the one-line method :
      // $manager->createNotification('Notification subject', 'Some random text', 'https://google.fr/');

      // you can add a notification to a list of entities
      // the third parameter `$flush` allows you to directly flush the entities
      $manager->addNotification(array($this->getUser()), $notif, true);

      ...
    }
```

## Translations

For now this bundle is only translated to de, en, es, fa, fr, it, nl, pt_BR.

Help me improve this by submitting your translations.

## Community

You can help make this bundle better by contributing (every pull request will be considered) or submitting an issue.

Enjoy and share if you like it.

## Licence
MIT
