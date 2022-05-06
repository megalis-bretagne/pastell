#! /bin/bash
set -e

cat <<EOF

host ${SMTP_SERVER}
port ${SMTP_PORT:-25}
from ${PLATEFORME_MAIL}
auth ${SMTP_AUTH_METHOD:-off}
user ${SMTP_USER:-}
password ${SMTP_PASSWORD:-}

logfile /data/log/msmtp.log

tls on
tls_starttls off
tls_certcheck off

EOF
