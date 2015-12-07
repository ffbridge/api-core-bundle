# set default shell
SHELL := $(shell which bash)
GROUP_ID = $(shell id -g)
USER_ID = $(shell id -u)
GROUPNAME =  dev
USERNAME = dev
HOMEDIR = /home/$(USERNAME)

_GITHUB_API_TOKEN = $(GITHUB_API_TOKEN)
ENV = /usr/bin/env
DKC = docker-compose
DK = docker
# default shell options
.SHELLFLAGS = -c

.SILENT: ;               # no need for @
.ONESHELL: ;             # recipes execute in same shell
.NOTPARALLEL: ;          # wait for this target to finish
.EXPORT_ALL_VARIABLES: ; # send all vars to shell
default: all;   # default target

.PHONY: all install vendors composer test up-dkc

all: install

# Install targets
install: vendors

# Vendors install targets
vendors: composer

composer:
	$(ENV) composer install

# docker compose for unit tests
up-dkc:
	# echo $(ENV) $(DKC) run --rm -e GROUP_ID=$(GROUP_ID) -e USER_ID=$(USER_ID) -e GROUPNAME=$(GROUPNAME) -e USERNAME=$(USERNAME) -e HOMEDIR=$(HOMEDIR) -e GITHUB_API_TOKEN=$(_GITHUB_API_TOKEN) php make install test
	$(ENV) $(DKC) run --rm -e GROUP_ID=$(GROUP_ID) -e USER_ID=$(USER_ID) -e GROUPNAME=$(GROUPNAME) -e USERNAME=$(USERNAME) -e HOMEDIR=$(HOMEDIR) -e GITHUB_API_TOKEN=$(_GITHUB_API_TOKEN) php make install test

# unit and functional tests
test:
	$(ENV) php vendor/bin/phpunit
