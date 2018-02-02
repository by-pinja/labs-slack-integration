#!/bin/bash
set -e

# If we're starting web-server we need to do following:
#   1) Set correct rights on /labs-slack-integration/var -folder
#   2) Clear cache
#   3) Warmup cache
HTTPDUSER=`cat /etc/passwd | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\: -f1`

php /labs-slack-integration/bin/console cache:clear --no-warmup
php /labs-slack-integration/bin/console cache:clear --env prod --no-warmup
php /labs-slack-integration/bin/console cache:warmup
php /labs-slack-integration/bin/console cache:warmup --env prod

exec "$@"
