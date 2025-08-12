#!/bin/bash

# Pfad des Scripts ermitteln
SCRIPT_DIR=$(dirname "$0")

# In den Script-Ordner wechseln
cd "$SCRIPT_DIR" || exit 1

git fetch --all
git reset --hard origin/main

# Composer Abhängigkeiten installieren
composer install --no-dev --optimize-autoloader