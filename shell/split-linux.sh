#!/usr/bin/env bash

set -e
set -x

NOW=$(date +%s)
WORKDIR="src"
CURRENT_BRANCH="master"
BASEPATH=$(cd `dirname $0`; cd ../$WORKDIR/; pwd)
REPOS=$@

function split()
{
    SHA1=`./shell/splitsh-lite-linux-amd64 --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

if [[ $# -eq 0 ]]; then
    REPOS=$(ls $BASEPATH)
fi

for REPO in $REPOS ; do
    remote $REPO git@github.com:mix-php/$REPO.git

    split "$WORKDIR/$REPO" $REPO
done

TIME=$(echo "$(date +%s) - $NOW" | bc)
printf "Execution time: %f seconds\n" $TIME
