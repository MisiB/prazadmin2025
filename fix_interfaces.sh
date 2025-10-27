#!/bin/bash
find /var/www/projects/laravel/praz/prazcrmadmin/app -type f -name "*.php" -exec sed -i 's/use App\\interfaces\\/use App\\Interfaces\\/g' {} +
find /var/www/projects/laravel/praz/prazcrmadmin/app -type f -name "*.php" -exec sed -i 's/use App\\Interfaces\\services\\/use App\\Interfaces\\Services\\/g' {} +
