#!/bin/bash

groupadd -f -g $GROUP_ID $GROUPNAME
useradd -u $USER_ID -g $GROUP_ID $USERNAME
mkdir --parent $HOMEDIR
chown -R $USERNAME:$GROUPNAME $HOMEDIR

mkdir -p $HOMEDIR/.ssh/
cp /ssh_config $HOMEDIR/.ssh/config

if [ -n "$SSH_PRIVATE_KEY" ] ;then
    echo "$SSH_PRIVATE_KEY" > $HOMEDIR/.ssh/id_rsa
    chmod 0600 $HOMEDIR/.ssh/id_rsa

    if [ -n "$SSH_PUBLIC_KEY" ] ;then
        echo "$SSH_PUBLIC_KEY" > $HOMEDIR/.ssh/id_rsa.pub
        chmod 0640 $HOMEDIR/.ssh/id_rsa.pub
    fi
fi

if [ -n "$GITHUB_API_TOKEN" ]; then
    sudo -u $USERNAME -H -E /usr/local/bin/composer config -g github-oauth.github.com $GITHUB_API_TOKEN
fi

chown -R $USERNAME:$GROUPNAME $HOMEDIR/.ssh

sudo -u $USERNAME -H -E "$@"
