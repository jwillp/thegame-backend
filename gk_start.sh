#!/usr/bin/env bash

# This script will run when STARTING the project ""
# Here you might want to cd into your project directory, activate virtualenvs, etc.

# The currently active project is available via $GK_ACTIVE_PROJECT

# AUTO STATUS
clear && git status

# general
alias cdsr="hcd && cd src"
alias cdapp="hcd && cd app"
alias cdco="hcd && cd app/config"

# tests
alias test_all="clear && app/phpunit -c app"
alias test_method="clear && app/phpunit -c app --filter"


# Database operation aliases
alias db_migrate="hcd && app/console doctrine:migrations:migrate"
alias db_status="hcd && app/console doctrine:migrations:status"
alias db_create="hcd && app/console doctrine:database:create"
alias db_validate="hcd && app/console doctrine:schema:validate"
alias db_diff="hcd && app/console doctrine:migrations:diff"

# Entities
alias gen_entity="hcd && app/console generate:doctrine:entity"


# Front end
alias kompile="hcd && gulp"