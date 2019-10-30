#!/bin/sh

TEST_STR="Hello World"
echo $TEST_STR

scheme="ssh"
prodhost="prodserversitea1"
echo $prodhost
devhost="devserver1"
echo $devhost

 if [[ "$scheme" =~ "ssh" ]]; then
 	echo "scheme is ssh"
	if [[ "$prodhost" =~ (prodsiteaserver)[0-9]|(prodsitebserver)[0-9] ]]; then
		echo "host is production"
		# tab-color 255 160 160

	else
		echo "host is dev"
		# tab-color 160 255 160
	fi
else
	echo "scheme is not ssh"
	# tab-color 160 160 255
fi