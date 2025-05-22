
# Laravel SimpleJS

A easy to use set of commands for your Laravel project using Vite for asset bundling. 





[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)



## How to Use

First, make sure that your Laravel project uses Vite for front-end asset bundling. If you are using Webpack, this package will not work.


## Installation

This package uses Composer for installation.
```
composer require richardkimdesigns/laravel-simplejs
```
To check if the artisan commands are ther:
```
php artisan list
```
You should see these new commands:
```
make:js
js:promote
js:demote
js:list
```

## Creating JS files
Before, you would have to manually create Javascript files in your resources/js folder. With this, you can run an artisan command to create the JS file. And it will also automatically add it to your vite.config.js file.
```
php artisan make:js <filename>
```
But what if you don't want this Javascript file to be bundled into app.js? Typically this is great for Javascript code that you don't want running on every page (as app.js would). In that case, you would do this:
```
php artisan make:js <filename> --standalone
```

## Promoting JS files
Sometimes your Javascript file deserves a promotion.
There is a command here that makes it easy for you to convert an existing Javascript file into a standalone JS file. All you need to do is...
```
php artisan js:promote <filename>
```
No need to add the .js extension.
When you promote a Javascript file, it will automatically update the vite.config.js and compile the assets. And becausae this Javascript has been promoted, it will be bundled as its own asset.

## Demoting JS files
But sometimes you need to demote a Javascript file. 
There is a command here for that as well.
```
php artisan js:demote <filename>
```
Just like the promote command, no need to add the .js extension - and also, when you demote a Javascript file, it will automatically update the vite.config.js and compile the assets. And because this Javascript has been demoted, it will be bundled into app.js.

## How to use the bundled assets
In your blade file where you want to use the Javascript asset, do this:
```
@vite('resources/js/<filename>.js')
```
## Locate where your JS files are being used
```
php artisan js:list
```
And you will get all your JS assets as well as list of where they are being used. This is very useful when working on your Laravel application.
## 🔗 Find me in the wild
[![website](https://img.shields.io/badge/my_website-000?style=for-the-badge)](https://richardkimdesigns.com/)

[![linkedin](https://img.shields.io/badge/linkedin-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/richardkimdesigns/)

[![instagram](https://img.shields.io/badge/instagram-d62976?style=for-the-badge&logo=instagram&logoColor=white)](https://instagram.com/coversbyrichard)


