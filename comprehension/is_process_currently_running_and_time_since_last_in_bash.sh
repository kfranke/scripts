#!/bin/sh
BACKUP_FROM=/Volumes/LOCALDRIVE/
BACKUP_TO=/Volumes/REMOTEDRIVE/
BACKUP_LOG=${BACKUP_FROM}backup_log.log
if [ ! -e "$BACKUP_LOG" ]; then
	touch "$BACKUP_LOG"
fi
#exec 2>&1
#exec > $BACKUP_LOG
#echo $BACKUP_LOG

echo "buttfucker"

PROCESS_ID=$$
PROCESS_NAME=$0

SCRIPT_START_TIME=$(date +%s)
# Change _FROM to _TO
BACKUP_INTERVAL=12
TIME_OF_LAST=$(ls $BACKUP_FROM | egrep -o '[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}')
TIME_OF_LAST=$(date -j -f "%Y-%m-%d %T" "$TIME_OF_LAST" +%s)
HOURS_SINCE_LAST=$(( ( ($SCRIPT_START_TIME - $TIME_OF_LAST) / 60) / 60))
RUNNING_PROCESSES=$(ps -efww | grep -w "$PROCESS_NAME" | grep -v grep | grep -v $$ | awk '{ print $2 }')

if [ ! -z $RUNNING_PROCESSES  ]; then
	echo "Already running: $RUNNING_PROCESSES"
elif [ -z $RUNNING_PROCESSES ]; then
	echo "No processes found, ready to execute"
fi

if (( "$HOURS_SINCE_LAST" < "$BACKUP_INTERVAL" )); then
        echo "Less than 12 hours"
elif (("$HOURS_SINCE_LAST" > "$BACKUP_INTERVAL" )); then
	#exit 1;
	echo "greater than $BACKUP_INTERVAL: ready to go"
fi
MESSAGE="THIS IS MY MESSAGE" >&2
exit
