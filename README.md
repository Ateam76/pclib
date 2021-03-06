# What is PClib?

Lightweight PHP framework for php 5.4+.
For development of small to middle size web applications.

### Features

* Templates
* Db: Database layer (mysql, pgsql or sql-lite)
* Form: Rendering, validation and storing into database
* Grid (datagrid): Pagination, sorting columns, summarization rows, filter
* Auth: Authentication and authorization: users, roles and permissions
* Translator: multilanguage support
* Debugger: improved error messages, variable dumps and debug-bar
* Logger, Tree view and more


### Requirements
- PHP 5.4

### Installation
1. [Download **pclib**](http://pclib.brambor.net/?r=download)
2. Copy directory `pclib` somewhere at your webroot.
3. Some parts of the library need a few database tables. You can
found sql-dump in `install/pclib_*.sql`. Import this sql-dump into your database.
4. Now you are ready to use **pclib**!

or install it using composer:

	composer require lenochware/pclib

### Examples

**Render form**
```php
require 'pclib/pclib.php';
$app = new PCApp('test-app');

$form = new PCForm('tpl/form-template.tpl');
print $form;
```

**Connect to database and show datagrid with data**
```php
require 'pclib/pclib.php';
$app = new PCApp('test-app');
$app->db = new PCDb('mysql://root@localhost/test');

$grid = new PCGrid('tpl/grid-template.tpl');
$grid->setQuery('SELECT * FROM products');
print $grid;
```

For more examples see http://pclib.brambor.net/demo

### Links
* [PClib homepage](http://pclib.brambor.net/)

### License
This library is free software; you can redistribute it and/or
 modify it under the terms of the GNU Lesser General Public
 License as published by the Free Software Foundation; either
 version 2.1 of the License, or (at your option) any later version.
