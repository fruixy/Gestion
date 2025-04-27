#!/bin/sh
set -e

echo "⏳ Waiting for DB connection..."

DB_HOST="interchange.proxy.rlwy.net"
DB_PORT="13286"

until nc -z "$DB_HOST" "$DB_PORT"; do
  echo "❌ Database is not up yet... waiting..."
  sleep 5
done

echo "✅ Database is up! Continuing startup..."

if [ "$1" = 'frankenphp' ]  [ "$1" = 'php' ]  [ "$1" = 'bin/console' ]; then
  if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
    composer install --prefer-dist --no-progress --no-interaction
  fi

  php bin/console importmap:install
  php bin/console asset-map:compile

  if grep -q ^DATABASE_URL= .env; then
    echo "⏳ Waiting for database ready for Symfony..."

    ATTEMPTS_LEFT_TO_REACH_DATABASE=60
    until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
      if [ $? -eq 255 ]; then
        ATTEMPTS_LEFT_TO_REACH_DATABASE=0
        break
      fi
      sleep 1
      ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
      echo "Still waiting... $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
    done

    if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
      echo "❌ Database not reachable:"
      echo "$DATABASE_ERROR"
      exit 1
    else
      echo "✅ Symfony database ready."
    fi

    if [ "$( find ./migrations -iname '*.php' -print -quit )" ]; then
      php bin/console doctrine:migrations:migrate --no-interaction
    fi
  fi

  setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
  setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

exec docker-php-entrypoint "$@"