#!/bin/bash

echo "Setting up environment for Clear..."
cd app
mkdir -p storage
cd storage
mkdir -p cache logs meta sessions views
cd ..
cd config
touch local.json
cd ../..
echo "Done. Run \`composer install\`."
