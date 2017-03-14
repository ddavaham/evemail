#!/bin/bash
git pull
sleep 2
php artisan clear-compiled
sleep 2
php artisan cache:clear
sleep 2
supervisorctl reread
sleep 2
supervisorctl update
sleep 2
supervisorctl restart evemail-worker:*
sleep 2
