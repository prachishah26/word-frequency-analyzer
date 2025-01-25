# Laravel Project Setup Guide

## Requirements

Ensure that your system meets the following requirements:

- PHP >= 8.2
- Composer

## Getting Started


Copy the .env.example file to .env:
- cp .env.example .env

Run composer install
- composer install

Generate key:
- php artisan key:generate

run the project locally:
- php artisan serve

Change below variables in php.ini file in your system as per your need
- upload_max_filesize=50M
- post_max_size=60M
- memory_limit=512M

