#!/bin/bash
php artisan optimize:clear
php artisan migrate --force
apache2-foreground
