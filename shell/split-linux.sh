#!/usr/bin/env bash

set -e
set -x

NOW=$(date +%s)
CURRENT_BRANCH="master"
REPOS=$@

function split()
{
    SHA1=`./shell/splitsh-lite-linux --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

IGNORE="guzzle"

WORKDIR="src"
BASEPATH=$(cd `dirname $0`; cd ../$WORKDIR/; pwd)

if [[ $# -eq 0 ]]; then
    REPOS=$(ls $BASEPATH | grep -v $IGNORE)
fi
for REPO in $REPOS ; do
    remote $REPO https://$TOKEN@github.com/mix-php/$REPO.git
    split "$WORKDIR/$REPO" $REPO
done

WORKDIR="examples"
BASEPATH=$(cd `dirname $0`; cd ../$WORKDIR/; pwd)
if [[ $# -eq 0 ]]; then
    REPOS=$(ls $BASEPATH | grep -v $IGNORE)
fi
for REPO in $REPOS ; do
    remote $REPO https://$TOKEN@github.com/mix-php/$REPO.git
    split "$WORKDIR/$REPO" $REPO
done

TIME=$(echo "$(date +%s) - $NOW" | bc)
printf "Execution time: %f seconds\n" $TIME
