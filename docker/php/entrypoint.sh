#!/usr/bin/env bash
set -e

# Wait until the database actually accepts connections (the compose healthcheck
# pings MySQL over localhost inside the db container, which can pass before the
# service is reachable over the network from this container).
wait_for_db() {
    echo "Waiting for database ${DB_HOST}:${DB_PORT} ..."
    local attempts=0
    until php -r '
        $h = getenv("DB_HOST") ?: "db";
        $p = (int)(getenv("DB_PORT") ?: 3306);
        $c = @fsockopen($h, $p, $errno, $errstr, 2);
        exit($c ? 0 : 1);
    '; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge 60 ]; then
            echo "Database not reachable after ${attempts} attempts, aborting." >&2
            exit 1
        fi
        sleep 2
    done
    echo "Database is up."
}

# Only the main app container should bootstrap the application. Other services
# (e.g. the scheduler) just need to wait for the DB.
if [ "${RUN_APP_BOOTSTRAP:-false}" = "true" ]; then
    wait_for_db

    # Generate the app key only if it is missing.
    if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        echo "Generating application key ..."
        php artisan key:generate --force
    fi

    echo "Running migrations and seeders ..."
    php artisan migrate --seed --force
else
    wait_for_db
fi

exec "$@"
