#!/bin/bash

echo "Database prefix (You can leave this empty): "
read PREFIX
echo "Database username: "
read USERNAME
echo "Database password: "
read PASSWORD

if [ -z "$PREFIX" ]
then
    COREDB="core"
    PLUGINDB="plugins"
    EVEDB="eve"
else
    COREDB="${PREFIX}_core"
    PLUGINDB="${PREFIX}_plugins"
    EVEDB="${PREFIX}_eve"
fi

if [ -z "$USERNAME" ]
then
    echo "Username cannot be empty."
    exit 65
fi

if [ -z "$PASSWORD" ]
then
    echo "Password cannot be empty."
    exit 65
fi

Q1="CREATE DATABASE IF NOT EXISTS ${COREDB};"
Q2="CREATE DATABASE IF NOT EXISTS ${PLUGINDB};"
Q3="CREATE DATABASE IF NOT EXISTS ${EVEDB};"
Q4="CREATE USER '${USERNAME}'@'localhost' IDENTIFIED BY '${PASSWORD}';"
Q5="GRANT ALL ON ${COREDB}.* TO '${USERNAME}'@'localhost';"
Q6="GRANT ALL ON ${PLUGINDB}.* TO '${USERNAME}'@'localhost';"
Q7="GRANT ALL ON ${EVEDB}.* TO '${USERNAME}'@'localhost';"
Q8="FLUSH PRIVILEGES;"
SQL="${Q1}${Q2}${Q3}${Q4}${Q5}${Q6}${Q7}${Q8}"
 
MYSQL=`which mysql`

if [ -z "$MYSQL" ]
then
    echo "MySQL CLI not found."
    exit 65
fi

$MYSQL -uroot -p -e "$SQL"

