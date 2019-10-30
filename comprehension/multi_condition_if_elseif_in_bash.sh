#!/bin/sh
#multi-condition-if.sh

BACKUP_FROM=/Volumes/DRIVEA/
BACKUP_TO=/Volumes/DRIVEB/

if [[ ! -d "$BACKUP_FROM" || ! -w "$BACKUP_FROM" ]]; then
        # Drive is not present
        MESSAGE="Drive: $BACKUP_FROM is not ready."
        echo $MESSAGE
elif [[ ! -d "$BACKUP_TO" || ! -w "$BACKUP_TO" ]]; then
        # Drive is not present
        MESSAGE="Drive: $BACKUP_TO is not ready."
        echo $MESSAGE
fi

exit
