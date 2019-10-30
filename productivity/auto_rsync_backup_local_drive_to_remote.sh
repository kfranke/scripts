#!/bin/sh
#rsync_backup.sh
# --------------------------------------------------------------------------- #
# Author: Kevin J Franke                                                      #
# Original build date: 2015-01-08                                             #
# Useful script for backing up one drive to another using rsync. Designed to  #
# work with non-permanent drives such as USB sticks and other portable HDD.   #
# Built and tested on Mac OSX 10.10.1 Yosemite but should work on other nix   #
# systems with very little tweaking                                           #
#                                                                             #
# GNU vs BSD data command                                                     #
# GNU date --date="2015-01-01 12:30:59" +%s                                   #
# BSD date -j -f "%Y-%m-%d %T" "2015-01-01 12:30:59" +%s                      #
# File test operators
# -e file exists
# -w is writable
# -d is a directory
# --------------------------------------------------------------------------- #

# -
# psuedo code
# check to see if source and destination drives are present
# check to see if there's a log file and if not create it
# redirect output to the log file
# get the time of last backup from the log file. If not then epoch
# check to see if script is already running and kill if it is
# check to see if time of last backup is less than our backup interval
# check if we have the optional dependencies on the os


# -


# Set BACKUP_FROM and BACKUP_TO to be your mount locations of the drives you  #
# want to backup.                                                             #
# On OSX mounts are in /Volumes/yourDrive                                     #
# On Ubuntu mounts are in /media/user/yourDrive                               #
BACKUP_FROM=/Volumes/DRIVEA/
# Specify each location to backup from.                                       #
# Example BACKUP_FROM=( "/Volumes/DriveA" "/Volumes/DriveB" )                 #
#BACKUP_FROM=( "/Volumes/DRIVEA" )
BACKUP_TO=/Volumes/DRIVEB/
COMPLETED_BACKUP="false"
BACKUP_IS_READY="false"
DRIVES_ARE_READY="false"
EPOCH="1970-01-01 00:00:00"
TZ_OFFSET=$(date -j +%z | egrep -o '(\+|\-)[0-9]{1,2}')
SCRIPT_START_TIME=$(date -v "${TZ_OFFSET}H" +%s)
# set to beginning of time (zero epoch)
FILE_DATETIME=$(date -j -v "${TZ_OFFSET}H" -f "%Y-%m-%d %T" "${EPOCH}" +%s)


# --------------------------------------------------------------------------- #
#                                TO DO                                        #
# 1) SHOULD ADD BACKUP_FROM TO BE ARRAY
# 2) change BACKUP_FROM and BACKUP_TO to be SOURCE and DESTINATION

for BACKUP_DRIVE in "${BACKUP_FROM[@]}"; do
	# Check for FROM drive
	if [[ ! -d "$BACKUP_DRIVE" || ! -w "$BACKUP_DRIVE" ]]; then
	        # Drive is not present
	        FROM_DRIVE_READY="false"
	        MESSAGE="Drive: $BACKUP_DRIVE is not ready."
	        echo $MESSAGE
	else
			FROM_DRIVE_READY="true"
			MESSAGE="Drive: $BACKUP_DRIVE is ready."
			echo $MESSAGE
	fi
done
# Check for TO drive
if [[ ! -d "$BACKUP_TO" || ! -w "$BACKUP_TO" ]]; then
        # Drive is not present
        TO_DRIVE_READY="false"
        MESSAGE="Drive: $BACKUP_TO is not ready."
        echo $MESSAGE
else
		TO_DRIVE_READY="true"
       	MESSAGE="Drive: $BACKUP_TO is ready."
       	echo $MESSAGE
fi

if [ "$FROM_DRIVE_READY" = "true" ]; then
		OLD_BACKUP_LOG=${BACKUP_FROM}$(ls $BACKUP_FROM | egrep -o 'Backup\sfrom\s[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}\.log')
		BACKUP_LOG=${BACKUP_FROM}"Backup from $(date '+%Y-%m-%d %T').log"        
        
		# Check for log file. If not there, create it
        if [ ! -f "$OLD_BACKUP_LOG" ]; then
                PURGE_OLD_BACKUP_LOG="false"
				touch "$BACKUP_LOG"
                BACKUP_IS_READY="true"
                MESSAGE="Backup log did not exist, creating it."
                echo $MESSAGE
        elif [ -f "$OLD_BACKUP_LOG" ]; then
                PURGE_OLD_BACKUP_LOG="true"
				FILE_DATETIME=$(ls $BACKUP_FROM | egrep -o '[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}')
				BACKUP_IS_READY="true"
                MESSAGE="Backup log found: $BACKUP_LOG"
                echo $MESSAGE
        fi
        # Send stderr (2) to stdout (1) and then all stdout to the backup log
        exec 2>&1
        exec > $BACKUP_LOG
        MESSAGE="Start at: $(date '+%Y-%m-%d %T')"
        echo $MESSAGE
else
		#FROM not there
		echo "not there"
fi



if [[ "$FROM_DRIVE_READY" = "true" && "$TO_DRIVE_READY" = "true" ]]; then
		DRIVES_ARE_READY="true"
 		MESSAGE="Drives $BACKUP_FROM and $BACKUP_TO are ready for backup"
        echo $MESSAGE
else
		DRIVES_ARE_READY="false"
		MESSAGE="Drives $BACKUP_FROM and $BACKUP_TO are not ready for backup"
		echo $MESSAGE
fi

# Make sure process isn't already running. If it is, stop.                    #
# Do this as soon as posible to reduce runtime                                #
PROCESS_ID=$$
PROCESS_NAME=$0
RUNNING_PROCESSES=$(ps -efww | grep -w "$PROCESS_NAME" | grep -v grep | grep -v "$PROCESS_ID" | awk '{ print $2 }')
if [ ! -z "$RUNNING_PROCESSES" ]; then
    	MESSAGE="Process is already running. Quitting."
		echo $MESSAGE
		if [ "$PURGE_OLD_BACKUP_LOG" = "true" ]; then 
        		MESSAGE="Removing old backup log: $OLD_BACKUP_LOG"
				echo $MESSAGE
				rm "$OLD_BACKUP_LOG"
    	fi
    	MESSAGE="Exiting at: $(date '+%Y-%m-%d %T')"
        echo $MESSAGE
		exit 1;
fi
# Check that backup hasn't run recently. No need to run it over and over.     #
# BACKUP_INTERVAL sets the hours to backup every assuming all drives are      #
# present.                                                                    #
BACKUP_INTERVAL=12
TIME_OF_LAST=$(date -j -v "${TZ_OFFSET}H" -f "%Y-%m-%d %T" "$FILE_DATETIME" +%s)
HOURS_SINCE_LAST=$(( ( ($SCRIPT_START_TIME - $TIME_OF_LAST) / 60) / 60))
if (( "$HOURS_SINCE_LAST" < "$BACKUP_INTERVAL" )); then
		MESSAGE="No backup required. Hours since last backup is: $HOURS_SINCE_LAST which is less than: $BACKUP_INTERVAL"
		echo $MESSAGE
        # echo $SCRIPT_START_TIME $TIME_OF_LAST $HOURS_SINCE_LAST
		if [ "$PURGE_OLD_BACKUP_LOG" = "true" ]; then 
                MESSAGE="Removing old backup log: $OLD_BACKUP_LOG"
                echo $MESSAGE
				rm "$OLD_BACKUP_LOG"
        fi
        MESSAGE="Exiting at: $(date '+%Y-%m-%d %T')"
        echo $MESSAGE
		exit 1;
fi

# Optional dependencies                                                       #
# Not required but enable pretty features such as OSX notification center     #
# intigration.                                                                #
# For notification center notifications 'brew install terminal-notifier'      #
DEPENDENCIES=( "/usr/local/bin/terminal-notifier" "/usr/local/bin/pianobar" )
DEPENDENCIES_MET="true"

# Loop through the dependencies array and if any not found set flag
for DEPENDENCY in "${DEPENDENCIES[@]}"; do
        if [ ! -e $DEPENDENCY ]; then
		DEPENDENCIES_MET="false"
		MESSAGE="Skipping optional goodies because $DEPENDENCY not found."
		echo $MESSAGE
		fi
done

if [ $DEPENDENCIES_MET = "true" ]; then
		MESSAGE="Optional dependencies found. Using those bitches!"
		echo $MESSAGE
fi

# Not ready to backup
if [ "$BACKUP_IS_READY" = "false" ]; then
		echo "Aborting backup: $MESSAGE"
		if [ $DEPENDENCIES_MET = "true" ]; then
				terminal-notifier -title "Backup" -subtitle "Aborting backup" -message "$MESSAGE"
		fi
        if [ "$PURGE_OLD_BACKUP_LOG" = "true" ]; then 
                MESSAGE="Removing old backup log: $OLD_BACKUP_LOG"
                echo $MESSAGE
				rm "$OLD_BACKUP_LOG"
        fi
        MESSAGE="Exiting at: $(date '+%Y-%m-%d %T')"
        echo $MESSAGE
		exit 1;
# Ready to backup
elif [ "$BACKUP_IS_READY" = "true" ]; then
		START=$(date +%s)
		BACKUP=$(rsync -avPhn --delete --exclude={"$BACKUP_LOG",".DS_Store",".Spotlight*",".Trashes*",".fseventsd*"} "$BACKUP_FROM" "$BACKUP_TO")
		FINISH=$(date +%s)
		COMPLETED_BACKUP="true"
fi
# Finished backup
if [ "$COMPLETED_BACKUP" = "true" ]; then
		if [ "$PURGE_OLD_BACKUP_LOG" = "true" ]; then
				MESSAGE="Removing old backup log: $OLD_BACKUP_LOG"
                echo $MESSAGE
				rm "$OLD_BACKUP_LOG"
		fi
		MINUTES_USED=$(( ( $FINISH - $START ) / 60 ))
		SECONDS_USED=$(( ( $FINISH - $START ) % 60 ))
		MESSAGE="Backup Complete in: $MINUTES_USED min. $SECONDS_USED sec."
		echo $MESSAGE | tee $BACKUP_LOG
		MESSAGE="Exiting at: $(date '+%Y-%m-%d %T')"
		echo $MESSAGE
		exit;
# Failed backup
elif [ "$COMPLETED_BACKUP" = "false" ]; then
		MESSAGE="Backup failed for unknown reason"
		echo $MESSAGE
		MESSAGE="Exiting at: $(date '+%Y-%m-%d %T')"
		echo $MESSAGE
		exit 1;

else
		echo "caught by else"
fi

exit
