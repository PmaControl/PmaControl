#!/bin/bash

while [ "$1" ] ; do
f="$1"
shift
echo -ne "\n$f:"
if [ -f "$f" ]; then
echo -n "(file) "
lc=`echo $f | awk '{print(tolower($0));}'`
if [ "$lc" != "$f" ]; then
echo "-> '$lc'"
mv -i "$f" "$lc"
fi
fi
done
