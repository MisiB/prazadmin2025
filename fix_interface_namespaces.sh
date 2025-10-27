#!/bin/bash
find /var/www/projects/laravel/praz/prazcrmadmin/app/Interfaces -type f -name "*.php" -exec sed -i 's/namespace App\\Interfaces\\services;/namespace App\\Interfaces\\Services;/g' {} +
find /var/www/projects/laravel/praz/prazcrmadmin/app/Interfaces -type f -name "*.php" -exec sed -i 's/namespace App\\interfaces;/namespace App\\Interfaces;/g' {} +
