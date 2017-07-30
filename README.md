# Minimal Framework Judus

```php
<?php

namespace Maduser\Minimal\Facades;

use App\Demo\ORM\Entities\Role;
use App\Demo\ORM\Entities\User;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Maduser\Minimal\Loaders\IOC;
use Maduser\Minimal\Translation\Translation;

require __DIR__ . "/../vendor/autoload.php";

App::respond(function () {

    // Respond on GET request
    Router::get('/', function () {
        return 'Hello from Minimal!';
    });

    // Register a modules configs and routes
    Modules::register('Downgrade');

    // Register all modules configs and routes within modules path
    Modules::register('Demo/*');

    // Translations
    Router::get('translator', function() {

        $str = '"Guete Morge" => (auto) ' . __('Guete Morge');
        $str.= '<br>"Guete Morge" => (en) ' . __('Guete Morge', 'en');
        $str.= '<br>"Guete Morge" => (de) ' . __('Guete Morge', 'de');
        $str.= '<br>"Guete Morge" => (fr) ' . __('Guete Morge', 'fr');
        $str.= '<br>"Guete Morge" => (it) ' . __('Guete Morge', 'it');
        $str.= '<br>"Guete Morge" => (rm) ' . __('Guete Morge', 'rm');

        return $str;

    });

    // HtmlBuilder & FormBuilder
    Router::get('collective', function() {

        $str = '';

        /** @var HtmlBuilder $html */
        $html = IOC::resolve('HtmlBuilder');
        /** @var FormBuilder $form */
        $form = IOC::resolve('FormBuilder');

        $str .= $html->tag('h1', 'Collective');
        $str .= $html->ul(['blabla', 'qweqwe', 'asdasd']);

        $str .= $form->open();
        $str .= $form->text('username');
        $str .= $form->close();

        return $str;

    });

    // Respond on GET request with uri paramters
    Router::get('hello/(:any)/(:num)', function ($any, $num) {
        return 'Hello ' . $any . ' ' . $num ;
    });

    // Respond on POST request
    Router::post('/', function () {
        return Request::post();
    });

    // Respond with HTTP location
    Router::get('redirection', function () {
        Response::redirect('/');
    });

    // Respond with a view
    Router::get('view', function () {
        return View::render(path('views').'pages/my-view', [
            'title' => 'Hello',
            'content' => lorem()
        ]);
    });

    // Test the database connection
    Router::get('database', function () {
        PDO::connection(Config::item('database'));
        return 'Successfully connected to database';
    });

    // Route group
    Router::group([
        'uriPrefix' => 'route-groups',
        'namespace' => 'App\\Demo\\Base\\Controllers\\',
        'middlewares' => [
            'App\\Demo\\Base\\Middlewares\\CheckPermission',
            'App\\Demo\\Base\\Middlewares\\ReportAccess',
        ]
    ], function () {

        // Responds to GET route-groups/controller-action/with/middlewares'
        Router::get('controller-action/with/middlewares', [
            'middlewares' => ['App\\Demo\\Base\\Middlewares\\Cache' => [10]],
            'controller' => 'YourController',
            'action' => 'timeConsumingAction'
        ]);

        // Do database stuff
        Router::get('users', function () {

            // Database connection for all the routes in this group
            PDO::connection(Config::item('database'));

            // Import namespaces of the models on top of file to make this work

            // Truncate tables
            Role::instance()->truncate();
            User::instance()->truncate();

            // Create 2 new roles
            Role::create([['name' => 'admin'], ['name' => 'member']]);

            // Get all the roles
            $roles = Role::all();

            // Create a user
            $user = User::create(['username' => 'john']);

            // Assign all roles to this user
            $user->roles()->attach($roles);

            // Get the first username 'john' with his roles
            return $user->with('roles')->where(['username', 'john'])->first();
        });

        // ... subgroups ...

    });
});

```