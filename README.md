# Laravel Ownable Trait

A Laravel package that adds ownership capabilities to your Eloquent models.

## Installation

You can install the package via composer:

```bash
composer require trinavo/ownable
```

### Migrations

After installing the package, you need to publish and run the migrations to create the necessary database tables:

```bash
php artisan vendor:publish --provider="Trinavo\Ownable\OwnableServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### Basic Usage

Add the `Ownable` trait to any Eloquent model that you want to make ownable:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\Ownable\Traits\Ownable;

class Post extends Model
{
    use Ownable;
    
    // Your model code...
}
```

### Adding Owners

```php
// Add the authenticated user as an owner
$post->addOwner(Auth::user());

// Add another user as an owner
$post->addOwner($anotherUser);

// Add a team as an owner (any model can be an owner)
$post->addOwner($team);
```

### Checking Ownership

```php
// Check if a model is owned by a specific user
if ($post->isOwnedBy($user)) {
    // The user owns this post
}
```

### Removing Owners

```php
// Remove a specific owner
$post->removeOwner($user);

// Remove all owners
$post->removeOwners();
```

### Query Scopes

The package includes helpful query scopes:

```php
// Get all posts owned by a specific user
$userPosts = Post::ownedBy($user)->get();

// Get all posts owned by the currently authenticated user
$myPosts = Post::mine()->get();
```

### Getting Owners

```php
// Get all owners of a model
$owners = $post->getOwners();

// Get owners of a specific type
$userOwners = $post->getOwners(User::class);
$userOwners = $post->getOwners([User::class, Admin::class]);

// Get first owner (useful when you expect only one owner)
$mainOwner = $post->getOwner();
```

### Auto-Ownership

By default, the trait automatically assigns the currently authenticated user as an owner when a model is created. You can disable this behavior:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\Ownable\Traits\Ownable;

class Post extends Model
{
    use Ownable;
    
    public static $autoOwn = false;
    
    // Your model code...
}
```

## Features

- Support for multiple owners per model
- Different types of owners (users, teams, etc.)
- Automatic cleanup of ownership records when models are deleted
- Convenient query scopes
- Easy ownership assignment and checking

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support the Developer

If you find this package useful, please consider supporting the developer:

- [Buy Me a Coffee](https://buymeacoffee.com/doonfrs)
- [GitHub Sponsors](https://github.com/sponsors/doonfrs)
