#!/bin/sh
bin/console d:m:m -n && bin/console c:c -e ${APP_ENV} && bin/console c:w -e ${APP_ENV}