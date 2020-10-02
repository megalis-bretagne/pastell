#! /bin/bash
set -e

cat <<EOF

host ${SMTP_SERVER}
port ${SMTP_PORT}
from ${PLATEFORME_MAIL}

EOF
