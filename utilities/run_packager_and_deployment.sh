#!/bin/bash

ROOTPDIR="$(dirname $0)/.."

# Run the compiler
$ROOTPDIR/utilities/compiler.php

# And then the packager
$ROOTPDIR/utilities/packager.php

# And the deployment scripts
$ROOTPDIR/utilities/create_repo.php
$ROOTPDIR/exports/sync.sh