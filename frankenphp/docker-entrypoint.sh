#!/bin/sh
set -e

echo "Waiting for database to be ready..."

ATTEMPTS_LEFT_TO_REACH_DATABASE=60

while [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -gt 0 ]; do
  if php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; then
    echo "Database is ready!"
    break
  fi

  ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
  echo "Still waiting... $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
  sleep 2
done

if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
  echo "Database not reachable. Exiting."
  exit 1
fi

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
    composer install --prefer-dist --no-progress --no-interaction
  fi

  php bin/console importmap:install
  php bin/console asset-map:compile

  if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction
  fi

  setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
  setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

exec docker-php-entrypoint "$@"
