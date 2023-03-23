#! /bin/bash

set -ex

# see https://github.com/openssl/openssl/issues/13180


sed -i '/^default = default_sect/a legacy = legacy_sect' /etc/ssl/openssl.cnf
sed -i '/^\[default_sect\]/a activate = 1' /etc/ssl/openssl.cnf

sed -i '1i OPENSSL_CIPHER_STRING_DEFAULT_SECURITY_LEVEL = 2\
OPENSSL_CIPHER_STRING_SECURITY_LEVEL = $ENV::OPENSSL_CIPHER_STRING_DEFAULT_SECURITY_LEVEL' /etc/ssl/openssl.cnf

sed -i 's/^CipherString = DEFAULT:@SECLEVEL=2$/CipherString = DEFAULT:@SECLEVEL=\$ENV::OPENSSL_CIPHER_STRING_SECURITY_LEVEL/g' /etc/ssl/openssl.cnf

echo "
[legacy_sect]
activate = 1
" >>  /etc/ssl/openssl.cnf

