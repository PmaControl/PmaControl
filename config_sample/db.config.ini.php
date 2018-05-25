;[name_of_connection] => will be acceded in framework with $this->db['name_of_connection']->method()
;driver => list of SGBD avaible {mysql, postgresql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas


[pmacontrol]
driver=mysql
hostname=127.0.0.1
user=root
password='password'
database=pmacontrol
ssh_login=root
ssh_password=password
is_sudo=0
tag='production monitoring'

