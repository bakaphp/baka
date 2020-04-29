#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="0.5"

function split()
{
    SHA1=`./scripts/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote auth git@github.com:bakaphp/auth.git
remote blameable git@github.com:bakaphp/blameable.git
remote cashier git@github.com:bakaphp/cashier.git
remote database git@github.com:bakaphp/database.git
remote elasticsearch git@github.com:bakaphp/phalcon-elasticsearch.git
remote http git@github.com:bakaphp/http.git
remote mail git@github.com:bakaphp/mail.git
remote phalcon-throttler git@github.com:bakaphp/phalcon-throttler.git
remote router git@github.com:bakaphp/router.git
remote support git@github.com:bakaphp/support.git

split 'src/auth' auth
split 'src/blameable' blameable
split 'src/cashier' cashier
split 'src/database' database
split 'src/elasticsearch' elasticsearch
split 'src/http' http
split 'src/mail' mail
split 'src/support' support
split 'src/phalcon-throttler' phalcon-throttler
split 'src/router' router
