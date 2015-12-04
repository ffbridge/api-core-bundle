#!/bin/bash

if [ $CHECK_SSH_GITLAB -gt 0 ]; then
    ssh -o "StrictHostKeyChecking no" -q git@gitlab.kilix.net 2>&1 > /dev/null
fi

if [[ -n $GITHUB_API_TOKEN ]]; then
    composer config -g github-oauth.github.com $GITHUB_API_TOKEN
fi

exec "$@"
