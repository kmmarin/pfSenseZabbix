These script will create xml files that can be used to discover items in zabbix
To use the gateway.php script you need to add this to the User parameters in the zabbix agent config in pfsense
UserParameter=get.gatewaystatus,/usr/local/sbin/gateway.php
Then you have to create an item prototype like 
![image](https://github.com/user-attachments/assets/9fe6255f-1fec-4e76-a057-996f8fb38082)
