#!/bin/sh
mkdir -p var/jwt
if [ ! -f var/jwt/private.pem ]; then
    openssl genrsa -out var/jwt/private.pem -aes256 -passout pass:$JWT_PASSPHRASE 4096
    openssl rsa -passin pass:$JWT_PASSPHRASE -pubout -in var/jwt/private.pem -out var/jwt/public.pem
    if [ ! -f var/jwt/private.pem ]; then
        printf 'The certificate was successfully generated!\n'
    else
        printf 'ERROR! The certificate was NOT generated!.\n'
    fi
else
    printf 'ATTENTION! The certificate already exists, delete the pem file if you want to regenerate the certificate.\n'
fi
