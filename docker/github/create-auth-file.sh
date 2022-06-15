#! /bin/bash

if [[ -z "$GITHUB_API_TOKEN" ]]
then
  echo "GITHUB_API_TOKEN not found in env"
  exit 0;
fi

echo "GITHUB_API_TOKEN found in env... Generating /root/.composer/auth.json"

mkdir -p /root/.composer/

cat > /root/.composer/auth.json <<EOF
{
  "http-basic": {},
  "github-oauth": {
    "github.com": "$GITHUB_API_TOKEN"
  }
}
EOF

exit 0;