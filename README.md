[![Total Downloads](https://poser.pugx.org/sarfraznawaz2005/composer-cost/downloads)](https://packagist.org/packages/sarfraznawaz2005/composer-cost)

# composer-cost

Simple composer plugin that displays size of each folder under `vendor` to help you with which package is taking the most disk space. It will run automatically when you use `composer install` or `composer update` command.

## Screenshot

![Main Window](https://raw.githubusercontent.com/sarfraznawaz2005/composer-cost/master/screenshot.png)

## Install

You can install it globally:

`composer global require sarfraznawaz2005/composer-cost`

or per-project bases:

`composer require sarfraznawaz2005/composer-cost`

That's it. Now on any project when you type `composer update` or `composer install`, it will show info like above screenshot.

FYI, you can skip any plugin when installing/updating by appending `--no-plugins` argument, example:

`composer update --no-plugins`

Thanks