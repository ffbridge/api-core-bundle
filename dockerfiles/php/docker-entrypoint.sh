#!/bin/bash

groupadd -f -g $GROUP_ID $GROUPNAME
useradd -u $USER_ID -g $GROUPNAME $USERNAME
mkdir --parent $HOMEDIR
chown -R $USERNAME:$GROUPNAME $HOMEDIR

if [ $CHECK_SSH_GITLAB -gt 0 ]; then
    sudo -u $USERNAME ssh -o "StrictHostKeyChecking no" -q git@gitlab.kilix.net 2>&1 > /dev/null
fi
if [[ -n $GITHUB_API_TOKEN ]]; then
    sudo -u $USERNAME /usr/local/bin/composer config -g github-oauth.github.com $GITHUB_API_TOKEN
fi

sudo -u $USERNAME $@
