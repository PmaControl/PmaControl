#!/bin/bash

# this script used monitor mysql network traffic.echo sql

tcpdump -i eno1 -s 0 -l -w - dst port 3306 | strings | perl -e '
while(<>) { chomp; next if /^[^ ]+[ ]*$/;
    if(/^(SELECT|UPDATE|DELETE|INSERT|SET|COMMIT|ROLLBACK|CREATE|DROP|ALTER|CALL)/i)
    {
        if (defined $q) { print "$q\n"; }
        $q=$_;
    } else {
        $_ =~ s/^[ \t]+//; $q.=" $_";
    }
}'
