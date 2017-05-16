#!/usr/bin/env bash

# This script will run when STOPPING the project ""
# Here you might want to deactivate virtualenvs, clean up temporary files, etc.

# The currently active project is available via $GK_ACTIVE_PROJECT

# general
unalias cdsr
unalias cdapp
unalias cdco

# tests
unalias test_all
unalias test_method


# Database operation aliases
unalias db_migrate
unalias db_status
unalias db_create
unalias db_validate
unalias db_diff

# Entities
unalias gen_entity