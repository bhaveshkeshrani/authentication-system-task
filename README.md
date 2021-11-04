----------

# Laravel Developer APPLICATION ASSESSMENT - By Bhavesh Keshrani

## Installation

Please check the official laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/8.x#installation-via-composer)

Alternative installation is possible without local dependencies relying on [Docker](#docker). 

Clone the repository

    git clone https://github.com/bhaveshkeshrani/authentication-system-task.git

Switch to the repo folder

    cd authentication-system-task

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Generate a new JWT Passport authentication secret key

    php artisan passport:install

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000

**TL;DR command list**

    git clone https://github.com/bhaveshkeshrani/authentication-system-task.git
    cd authentication-system-task
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan passport:install
    
**Make sure you set the correct database connection information before running the migrations** [Environment variables](#environment-variables)

    php artisan migrate
    php artisan serve

----------

# Code overview

## Dependencies

- [laravel-sendinblue](https://github.com/agence-webup/laravel-sendinblue) - For Sending Email to users

## Environment variables

- `.env` - Environment variables can be set in this file

***Note*** : You can quickly set the database information and other variables in this file and have the application fully working.

----------

# Testing API

Run the laravel development server

    php artisan serve

The api can now be accessed at

    http://localhost:8000/api

# By bhavesh kesharani