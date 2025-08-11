#!/bin/bash
cd /var/www/ci4platform || exit 1
git fetch --all
git reset --hard origin/main

# Composer Abhängigkeiten installieren
composer install --no-dev --optimize-autoloader
