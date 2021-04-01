#!/bin/bash

# Attempt to block new macOS updates and remove the red
# notification badge from the System Preferences Dock icon
# Ref: https://mrmacintosh.com/10-15-5-2020-003-updates-changes-to-softwareupdate-ignore/
# Reset your ignored completely
# sudo softwareupdate --reset-ignored
#
# Note these methods seem to quit working as --ignore has been deprecated
# with Catalina. Possible to cron and hide notice for 90 days?
# To simply remove the update badge from the icon
# edit ~/Library/Preferences/com.apple.dock.plist
# Look through persistent-apps to find bundle-identifier == com.apple.systempreferences
# and set the dock-extra key to NO then reload the Dock

PS3='Which version do you want to ignore? '

versions=("macOS Yosemite"
"macOS El Capitan"
"macOS Sierra"
"macOS High Sierra"
"macOS Mojave"
"macOS Catalina"
"macOS Big Sur")

versions+=("Quit")

select version in "${versions[@]}"
do
	case $version in
		"macOS Yosemite")
		selected=$version
		break
		;;
		"macOS El Capitan")
		selected=$version
		break
		;;
		"macOS Sierra")
		selected=$version
		break
		;;
		"macOS High Sierra")
		selected=$version
		break
		;;
		"macOS Mojave")
		selected=$version
		break
		;;
		"macOS Catalina")
		selected=$version
		break
		;;
		"macOS Big Sur")
		selected=$version
		break
		;;
		"Quit")
		exit
		;;
		*) 
			echo "Invalid option $REPLY"
		;;
	esac
done

if [ -z "$selected" ];
then
	exit
else
	echo "Ignoring selected version \"$selected\""
	# Doesn't work. String problem?
	# Creates weird entries in /Library/Preferences/com.apple.SoftwareUpdate.plist
	# Use sudo /usr/libexec/PlistBuddy /Library/Preferences/com.apple.SoftwareUpdate.plist
	# to edit and remove the bad entries $ Delete :InactiveUpdates:5 where
	# 5 is the array index of the bad entry

	# sudo softwareupdate --ignore \"$selected\"

	# For Sierra and High Sierra 
	# block banner notifications & remove red badge icon
	# sudo softwareupdate --ignore "macOSInstallerNotification_GM"
	# defaults delete com.apple.preferences.softwareupdate LatestMajorOSSeenByUserBundleIdentifier
	# Mojave
	# defaults write com.apple.systempreferences AttentionPrefBundleIDs 0

	# Reload the dock
	# killall Dock
fi
