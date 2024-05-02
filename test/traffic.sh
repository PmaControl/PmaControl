#!/bin/bash
for i in {1..10}
do
  (
    while true
    do
      random_sleep=$(echo "scale=2; 0.5 + $RANDOM % 1000 / 1000" | bc)
      sleep $random_sleep
      mysql -h 10.68.68.73 -u stnduser -pstnduser -P6033 test -e "select sleep(1); insert into a VALUES (NULL);"
    done
  ) &
done
wait
