#!/bin/bash

echo "Setting up environment for Clear..."
cd app
mkdir storage
cd storage
mkdir cache logs meta sessions views
cd ..
cd config
touch local.json
cd ../..

# echo "Installing Composer dependencies..."
# composer install
