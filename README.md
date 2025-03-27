These script will create json files that can be used to discover items in zabbix
To use the gateway.php script you need to add this to the User parameters in the zabbix agent config in pfsense

UserParameter=get.pfsenseconfig,cat /cf/conf/config.xml

UserParameter=get.gatewaystatus,/usr/local/zabbix/gateway.php

UserParameter=get.interfacetatus,/usr/local/zabbix/interfaces.php


upload the files into pfsense from Diagnostics -> Command Prompt
then run these commands
mkdir /usr/local/zabbix

mv /tmp/interfaces.php /usr/local/zabbix

chmod +x /usr/local/zabbix/interfaces.php

mv /tmp/gateway.php /usr/local/zabbix

chmod +x /usr/local/zabbix/gateway.php

Import the zabbix template and attach it to the hosts





