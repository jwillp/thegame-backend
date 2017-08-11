# ![The Game logo](https://github.com/jwillp/thegame-frontend/blob/dev/src/assets/images/logo.png?raw=true) backend

|Branch | Status |
|-------|--------|
|Dev   | [![Build Status](https://travis-ci.org/jwillp/thegame-backend.svg?branch=dev)](https://travis-ci.org/jwillp/thegame-backend)   |
|Master | [![Build Status](https://travis-ci.org/jwillp/thegame-backend.svg?branch=master)](https://travis-ci.org/jwillp/thegame-backend) |    

TheGame Project

For more info on the project see the frontend repository
Frontend project: https://github.com/jwillp/thegame-frontend

## Build Instructions

```bash
$ composer install

$ mkdir -p var/jwt

# give it a password like tgapi
$ openssl genrsa -out var/jwt/private.pem -aes256 4096

# give it the same password (tgapi)
$ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```
