#!/bin/sh

p1="$( ping -c5 192.168.0.1 | grep "packet loss" | sed "s/.*received, //" | sed "s/%.*time /,/" | sed "s/ms//" | sed "s/+.* errors, //g")"
p2="$( ping -c5 1.1.1.1 | grep "packet loss" | sed "s/.*received, //" | sed "s/%.*time /,/" | sed "s/ms//" | sed "s/+.* errors, //g")"
p3="$( ping -c5 8.8.8.8 | grep "packet loss" | sed "s/.*received, //" | sed "s/%.*time /,/" | sed "s/ms//" | sed "s/+.* errors, //g")"

t="$(date +%d-%m-%Y_%H:%M:%S)"

if [ ${#p1} -lt 3 ]
  then
    p1="100,100"
fi
if [ ${#p2} -lt 3 ]
  then
    p2="100,100"
fi
if [ ${#p3} -lt 3 ]
  then
    p3="100,100"
fi

echo "$t,$p1,$p2,$p3" | sed 's/\x0//g' >> /var/www/html/ping_log/ping_log.csv
