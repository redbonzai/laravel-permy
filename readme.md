# Laravel Permy
>Short for "permanent friend" - My Mom said it's pretty cool!

A powerful and flexible ACL for all your Laravel applications; supporting version of 4.2 and higher

* Assign single or multiple permissions to users and control the inheritance logic via `AND`, `OR` or `XOR` operators
* Use multiple User Models
* Manage permissions from the UI or DB with localization support
* Set your permissions directly on Routes, Route Groups or Controllers via filters/middleware
* Batch permission checking with extra logical operators
* Artisan commands and Debugging helpers

## TODO
- [ ] Class refactoring and abstraction
- [ ] Add *operator* key support for array permissions to artisan command
- [ ] Additional helper artisan commands

## Contents
>The only section without hilarious comments

1. [Installation](#installation)
2. [Publishing](#publishing)
3. [Database](#database)
4. [Usage](#usage)
5. [Configuration](#configuration)
6. [Localization](#localization)
7. [Artisan Commands](#artisan-commands)
8. [Exceptions](#exceptions)
9. [Feedback](#feedback)

---
## Installation
>The beginning of a beautiful friendship (with cheesy music in the background)

Require via composer
```shell
    composer require michaeltintiuc/laravel-permy
``````
Append the Service Provider to your providers array in `app/config/app.php` (4.2) or `config/app.php` (5.0+)
```php
    'MichaelT\Permy\PermyServiceProvider'
```
Append the Facade to your facades array in `app/config/app.php` (4.2) or `config/app.php` (5.0+)
```php
    'Permy' => 'MichaelT\Permy\PermyFacade'
```
Add the Trait to your User Models or any other Models you need permissions for.
```php
    use MichaelT\Permy\PermyTrait;

    class User extends Model
    {
        use PermyTrait;
    }
```
---
## Publishing
>I can't publish this anymore...

#### Migrations
**Laravel 4.2**
```shell
    php artisan migrate:publish michaeltintiuc/laravel-permy
```
**Laravel 5.0+**
```shell
    php artisan vendor:publish --provider="MichaelT\Permy\PermyServiceProvider" --tag="migrations"
```
---
#### Configs
This is an **optional** step, the description of the configuration file will follow below

**Laravel 4.2**
```shell
    php artisan config:publish michaeltintiuc/laravel-permy
```
**Laravel 5.0+**
```shell
    php artisan vendor:publish --provider="MichaelT\Permy\PermyServiceProvider" --tag="config"
```
---
#### Translations
This is an **optional** step, the configuration of the language file will follow below

**Laravel 4.2**

Create file `app/lang/packages/en/laravel-permy/defaults.php`

You may create this file for each locale used by your app simply by substituting `en` to `fr` (for example)

**Laravel 5.0+**
```shell
    php artisan vendor:publish --provider="MichaelT\Permy\PermyServiceProvider" --tag="translations"
```
---
## Database
>Drop the base, wait! No, that's not what I meant!

After you've successfully published the migration files, you should have 2 files:

`create_permy_table` and `create_permy_user_table`

The `permy` table is responsible for storing all of your future permissions
while the `permy_user` table holds the relation of users to their respective permissions


Permy works in a way that each permission has a name, description and whole lot of columns, each representing a controller that has restricted user access.
Now onto configuring it.

Leave the boilerplate as is
```php
    $table->increments('id');
    $table->string('name');
    $table->string('desc');
```
Create a column for every controller that needs access restriction.
We're specifying controllers which will use the Permy middleware/filters.
These should be fully name-spaced class names and `\ (backslashes)` replaced with `:: double colons` like so:

Original Controller class name:
```php
    Acme\Controllers\UsersController
```
Resulting php and column name:
```
    $table->text('Acme::Controllers::UsersController')->nullable();
```
The column type is `text` because we'll be storing JSON data that will represent access to specific Controller methods. We also set the column to be `nullable` because well... you might forget that you've created several new Controllers and/or methods for your awesome feature, this will allow a graceful fallback of either restricting or allowing access (we'll discuss this in-depth a bit later).

Now that you're all set - update your database
```php
php artisan migrate
```
---
## Usage
>I know, finally... but it's well worth it, I promise!

#### Middleware/Filters
**Laravel 4.2**

Add the filter to the end of your `app/filters.php` file
```php
Route::filter('permy', 'MichaelT\Permy\PermyFilter');
```
**Laravel 5.0+**

Add the middleware to the `$routeMiddleware` array in your `app/Http/Kernel.php` file
```php
'permy' => 'MichaelT\Permy\PermyMiddleware'
```
This is a base filter/middleware and will simply spit our `403 - Forbidden` on restricted routes. If you'd like to display custom text, view or perhaps a redirect you can provide your own class.
All you have to do is perform a check using `Permy::can($route)` in your implementation.
Have a look at the source code of the [filter](https://github.com/michaeltintiuc/laravel-permy/blob/master/src/MichaelT/Permy/PermyFilter.php) or [middleware](https://github.com/michaeltintiuc/laravel-permy/blob/master/src/MichaelT/Permy/PermyMiddleware.php) and the Laravel docs [4.2](https://laravel.com/docs/4.2/routing#route-filters), [5.0+](https://laravel.com/docs/middleware#registering-middleware) on how to implement custom filters.

---
#### Routes & Controllers
**Laravel 4.2**

These **must be** *before* filters

Applied directly to a route
```php
Route::get('/', ['before' => 'permy', 'uses' => 'SomeController@method']);
```
Or to a route group
```php
    Route::group([before' => 'permy'], function () {
        ...
    });
```
Or within a controller
```php
    class SomeController
    {
        public function __construct()
        {
            $this->beforeFilter('permy');                             // checks all methods
            $this->beforeFilter('permy', array('only' => 'index'));   // checks only index method
            $this->beforeFilter('permy', array('except' => 'index')); // checks all but the index method
        }
    }
```
**Laravel 5.0+**

Applied directly to a route
```php
    Route::get('/', 'SomeController@method')->middleware('permy');
```
Or to a route group
```php
    Route::group([middleware' => 'permy'], function () {
        ...
    });
```
Or within a controller
```php
    class SomeController
    {
        public function __construct()
        {
            $this->middleware('permy');                  // checks all methods
            $this->middleware('permy')->only('index');   // checks only index method
            $this->middleware('permy')->except('index'); // checks all but the index method
        }
    }
```
At this point you're done and can test the application.
If you've assigned the filter/middleware to `Acme\SomeController` which has `index` and `someMethod` methods you can insert a new row in the `permy` table with a test JSON for the `Acme::SomeController` column:
```php
    {"index": 1, "someMethod": 0}
```
Note the ID of the new row and insert a new one in the `permy_user` table binding the permission ID to an existing user.
This will now allow the assigned user to issue requests to the `index` method and prevent access to `someMethod`.
If you try the above routes with a different user, all requests will be blocked, in fact any requests to methods which were not explicitly set will also be blocked. This behavior can be overridden through the config file.

---
#### Methods
**can**
```php
boolean can(<array|string|Illuminate\Routing\Route $routes> [, [string $operator = 'and'] [, boolean|callable $extra_check = true]])
```
Allows you to check if the current user *can* access one or multiple routes or controller methods. You can mix route names, controller class names/methods and Route objects when passing an array.

**Basic**
```php
    // check single route or controller method
    Permy::can('users.index');
    Permy::can('UsersController@index');

    // check multiple routes or controller methods
    // returns true if ALL routes/methods are allowed
    Permy::can(['users.index', 'users.show']);
    Permy::can(['UsersController@index', 'UsersController@show']);

    // OR returns true if at least 1 route/method is accessible
    Permy::can(['users.index', 'users.show', 'operator' => 'or']);
    Permy::can(['UsersController@index', 'UsersController@show', 'operator' => 'or']);

    // XOR the permission values of each route/method
    Permy::can(['users.index', 'users.show', 'operator' => 'xor']);
    Permy::can(['UsersController@index', 'UsersController@show', 'operator' => 'xor']);
```
**Advanced**

You can perform additional logic operations on the resulting permissions.
```php
    // Additional check
    $check = SomeClass::checkUser();

    // return true if permissions AND $check are true
    Permy::can('users.index', 'and', $check);

    // At least one should be true
    Permy::can('users.index', 'or', $check);

    // XOR the values of permissions and $check
    Permy::can('users.index', 'xor', $check);

    // Omit the $operator and use the default value
    Permy::can('users.index', $extra_check = $check);

    // Provide a callback function
    // The return value will be type hinted to boolean
    Permy::can('users.index', $extra_check = function () {
        return SomeClass::fetchData();
    });
```
---
**cant**
```php
boolean cant(<array|string|Illuminate\Routing\Route $routes> [, [string $operator = 'and'] [, boolean|callable $extra_check = true]])
```
Same as *can()*, this is a helper function.
```php
    // returns false if access is allowed
    Permy::cant('users.index');
```
---
**getList**
```php
array getList()
```
Runs a check against all routes and controllers that have a *fillable* filter/middleware assigned to them.
Builds a localized array of controller/method names and descriptions.
Creates/updates the translation file.

Useful when fetching permissions data for UI management.

```php
    // Generates language file for default locale
    Permy::getList();

    // Generates language file for 'fr' locale
    App::setLocale('fr');
    Permy::getList();

    // When setting locale explicitly - reset it when done
    // Whichever is fine
    App::setLocale(Config::get('app.fallback_locale'));
    App::setLocale('en');
```
---
**setUser**
```php
PermyHandler setUser(<Illuminate\Database\Eloquent\Model $user>)
```
Provide a specific user instead of the default authenticated one
```php
    $user = User::find(123);

    // Check if user ID 123 has access
    Permy::setUser($user)->can('users.index');

    // Next calls will check the authenticated user NOT the one we've set before
    Permy::can('users.index');
```
---
**getUser**
```php
Illuminate\Database\Eloquent\Model getUser()
```
Helper function for testing/debugging
```php
    $user = User::find(123);

    // returns user ID 123
    Permy::setUser($user)->getUser();

    // returns currently authenticated user
    Permy::getUser();
```
---
**setDebug**
```php
PermyHandler setDebug(<boolean $bool>)
```
Overrides the config value for current call (see Config docs for details)
```php
    // Debugging is on
    Permy::setDebug(true)->can('users.index');

    // Debugging is equal to value set in config
    Permy::can('users.index');
```
---
**setGodmode**
```php
PermyHandler setGodmode(<boolean $bool>)
```
All checks return true. Why not, right? (see Config docs for details)
```php
    // Returns true even if access is disallowed
    Permy::setGodmode(true)->can('users.index');

    // Godmode is equal to value set in config
    Permy::can('users.index');
```
---
**setRolesLogicOperator**
```php
PermyHandler setRolesLogicOperator(<string $operator>)
```
Overrides the config value for current call (see Config docs for details)
```php
    // At least one of the permissions assigned allows access to users.index
    Permy::setRolesLogicOperator('or')->can('users.index');

    // Value from config is used now
    Permy::can('users.index');
```
---
## Configuration
>What there's more?!

**logic_operator**

If multiple permissions are assigned to a user and there are conflicting permissions per route/method, which logical operator to use? Invalid values default to `and`

Default: `and`

Allowed values & behavior:

* `and` - All permissions must be true
* `or` - At least one of the permissions must be true
* `xor` - [Exclusive or](https://en.wikipedia.org/wiki/Exclusive_or)

---
**users_model**

Sets the default User model used in CLI artisan command and PermyModel describing the many-to-many relationship.

Default: `App\User`

---
**godmode**

When set to true, all route permissions return true. Useful for debugging, I guess...

Default: `false`

---
**debug**

When set to true, all exceptions during permission checking will be thrown. Consider it *strict mode*

Default: `false`

---
**filters**

An array of filters based on which Permy builds a list of permissions to manage.
The fillable array represents the filters that are manageable through the UI.
The guarded array represents the filters that are not seen in the UI and are managed manually through the DB or CLI.

Default:
```php
    [
        'fillable' => ['permy'],
        'guarded' => []
    ]
```
---
## Localization
>OMG PLZ STAHP!

After calling the `getList()` method, you now have language files for all of your restricted routes and controllers.
You are encouraged to edit these files in order to provide a better understanding to those who manage the application on the front-end.

File Location:

**Laravel 4.2**

    app/lang/packages/{locale}/laravel-permy/permy.php

**Laravel 5.0+**

    resources/lang/vendor/laravel-permy/{locale}/permy.php

Example file:
```php
    return array (
        'Acme::UsersController' =>
        array (
            'name' => 'A name for the non-tech people',
            'desc' => 'In case if anyone reads these, provide some sort of help for managers.',
            'methods' =>
            array (
                'myAwesomeMethod' =>
                array (
                    'name' => 'Managers may think camelCase is weird.',
                    'desc' => '"rm -rf ~" is not a very helpful description.',
                )
            )
        )
    );
```

If you've published the translation files, as mentioned at the very top, you should have the `defaults.php` file in your app's lang directory.
It's responsible for the default *(duh!)* names and descriptions of controllers and methods.

When the `permy.php` file is created for the first time or updated with new data - these are the values that everybody dislikes to update so much.
You can have translations of this file for each locale.
```php
    [
        // :controller is replaced with the name-spaced controller name
        'controller' => [
            'name' => '* :controller - please update',
            'desc' => '* The developer was way to busy to care describing the :controller class',
        ],
        // :controller is replaced with the name-spaced controller name
        // :method is replaced with the controller method name
        'method' => [
            'name' => '* :controller@:method - please update',
            'desc' => '* The developer was way to busy to care describing the :method method of :controller class',
        ],
    ];
```
---
## Artisan Commands
>HALT AND CATCH FIRE

**can**

```shell
permy:can <user_id> <routes> [-o|--operator [OPERATOR]] [-e|--extra_check [EXTRA_CHECK]] [-m|--model [MODEL]] [-g|--godmode [GODMODE]] [-d|--debug [DEBUG]] [-l|--roles_logic_operator [ROLES_LOGIC_OPERATOR]] [--]
```
Mimics the `Permy` public methods, only cooler cause it's from CLI.
Prints the result back on screen in pretty colors.
```shell
    artisan permy:can 1 users.index
    artisan permy:can 1 'Acme\UsersController@index'
    artisan permy:can 1 'Acme\UsersController@index' -m 'Acme\OtherUser'
    artisan permy:can 1 users.index,users.show
    artisan permy:can 1 'Acme\UsersController@index,Acme\UsersController@show'
    artisan permy:can 1 'Acme\UsersController@index,Acme\UsersController@show' -l or
```

**More commands coming soon**

---
## Exceptions
>RTFM - Achievement Unlocked!

If `debug` or `strict mode` (if you will) is set to true these Exceptions may be thrown.
You are more than welcome to catch them anywhere in your app.

**PermyFileCreateException**

Error creating the `permy.php` language file

**PermyFileUpdateException**

Error updating the `permy.php` language file

**PermyMethodNotSetException**

The method you're trying to check is not explicitly set in the DB. Defaults to false when debug is false

**PermyControllerNotSetException**

The controller you're trying to check does not exist the DB as a column name. Defaults to false when debug is false

**PermyPermissionsNotFoundException**

Failed to get permissions for current user

---
## Feedback
>The back needs proper feeding and you're not doing anything about it!

Collaboration, bug-reports, feature and pull requests are always welcome!
