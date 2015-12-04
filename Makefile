# set default shell
SHELL := $(shell which bash)
ENV = /usr/bin/env
# default shell options
.SHELLFLAGS = -c

.SILENT: ;               # no need for @
.ONESHELL: ;             # recipes execute in same shell
.NOTPARALLEL: ;          # wait for this target to finish
.EXPORT_ALL_VARIABLES: ; # send all vars to shell
default: all;   # default target

.PHONY: all install vendors composer test

all: install

# Install targets
install: vendors

# Vendors install targets
vendors: composer

composer:
	$(ENV) composer install

# unit and functional tests
test:
	$(ENV) php vendor/bin/phpunit
