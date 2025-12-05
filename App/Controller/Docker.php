<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Mysql;
use App\Library\Available;
use App\Library\Debug;
use Glial\Sgbd\Sgbd;
use App\Controller\Common;
use \phpseclib3\Crypt\RSA;
use \phpseclib3\Net\SSH2;
use \phpseclib3\Crypt\PublicKeyLoader;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Glial\I18n\I18n;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Ssh;

class Docker extends Controller
{
    public static $logger;
    
    public const PMACONTROL_CNF = '.pamcontrol.cnf';

    public function install()
    {
        //require apt install jq skopeo docker kubs ?
        self::$logger->info("TEST msg !!");
    }


    public function uninstall()
    {




    }


    public function getTag($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT * FROM docker_software ORDER by name DESC";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
          

            $cmd = "skopeo inspect docker://".$ob->name." | jq '.RepoTags'";
            Debug::debug($cmd, "cmd");
            $json = trim(shell_exec($cmd));

            $tags = json_decode($json);

            Debug::debug(string: $ob, var: "Elems");

            foreach($tags as $tag)
            {
               
                //echo "$tag \n";
                preg_match('/^(\d+\.\d+\.\d+)$/', $tag, $output_array);

                if (count($output_array) > 0)
                {
                    echo $ob->name." : $tag\n";

                    $sql2 = "SELECT count(1) as cpt FROM docker_image WHERE id_docker_software=".$ob->id." AND tag =  '".$tag."'";
                    Debug::sql($sql2);
                    $res2 = $db->sql_query($sql2);

                    while($ob2 = $db->sql_fetch_object($res2))
                    {
                        if ($ob2->cpt == "0")
                        {
                            $sql = "INSERT INTO docker_image (`id_docker_software`, `tag`) VALUES (".$ob->id.", '".$tag."');";
                            Debug::sql($sql);
                            $db->sql_query($sql);
                        }
                    }
                }
            }
        }
    }


    public function getImage($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name, b.tag FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software WHERE b.sha256 != '' ORDER by a.name DESC, b.tag";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $ret = shell_exec("docker image pull ".$ob->name.":".$ob->tag);
            echo $ret."\n";

            
        }

    }

    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT ds.*, sk.name as ssh_key_name 
                FROM docker_server ds
                INNER JOIN ssh_key sk ON sk.id = ds.id_ssh_key
                ORDER BY ds.id ASC";

        $res = $db->sql_query($sql);

        $data['docker_servers'] = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['docker_servers'][] = $row;
        }

        $this->set('data', $data);
    }


    public function list($param)
    {
        Debug::parseDebug($param);
        $data = array();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT a.display_name, a.name,b.tab  b.tag FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software 
        ORDER by a.name,  
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 1), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED) ASC; ";


        //derniere version
        $sql = "SELECT 
        a.display_name, a.name,b.tag,a.color, a.background,
        CONCAT(
          SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2), 
          '.', 
          MAX(CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED))
        ) AS latest_version, 
        SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2) as main,
        
        GROUP_concat(tag) as all_version

        FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software 
      GROUP BY 
        a.name,
        SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -2)
        ORDER by a.name,  
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 1), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -1) AS UNSIGNED) ASC,
        CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED) ASC;";


        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['image'][] = $arr;
        }

        $sql = "SELECT name, tag, sha256 FROM docker_software a
        INNER JOIN docker_image b ON a.id=b.id_docker_software";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['tag'][strtolower($arr['name'])][$arr['tag']] = $arr['sha256'];
        }

        $this->set('data', $data);

    }


    public function createInstance($param)
    {


        // 
    }


    public function linkTagAndImage($param)
    {
        
        Debug::parseDebug($param);
    
        $db = Sgbd::sql(DB_DEFAULT);

        $ls = "docker image ls";


        $result = shell_exec($ls);

        $lines = explode("\n", $result);

        Debug::debug($lines);

        foreach($lines as $input_line )
        {
            $output_array = array();
            preg_match('/(\S+)\s+(\d+\.\d+\.\d+)\s+([a-z0-9]{12}).*\s+(\d+[KGMB]{2})$/', $input_line, $output_array);

            Debug::debug($output_array);

            if (count($output_array) > 0)
            {

                $sql ="UPDATE docker_image a
                INNER JOIN docker_software b ON a.id_docker_software = b.id SET sha256='".$output_array[3]."', size='".$output_array[4]."' 
                WHERE b.name='".$output_array[1]."' AND a.tag='".$output_array[2]."'";

                Debug::sql($sql);

                $db->sql_query($sql);
            }
        }
    }


    public function add($param)
    {
        // includes / autoload assumed (composer)
        // set_include_path(...) non nécessaire si tu utilises composer/autoload

        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Récupère les valeurs postées
            $data = $_POST['docker_server'] ?? [];

            // Validation basique (tu peux enrichir)
            $hostname   = trim($data['hostname'] ?? '');

            if (empty($data['port']))
            {
                $data['port'] = 22;
            }

            $port = intval($data['port']);
            $name = trim($data['display_name'] ?? '');
            $id_ssh_key = !empty($data['id_ssh_key']) ? intval($data['id_ssh_key']) : null;

            if (empty($hostname)) {
                set_flash("error", I18n::getTranslation(__("Missing IP")), I18n::getTranslation(__("Please provide an IP address")));
                header("Location: " . LINK . "docker/add");
                exit;
            }

            // Si un id_ssh_key est fourni, on récupère la clé privée chiffrée en base
            $ssh_user = null;
            $private_key_pem = null;
            if (!empty($id_ssh_key)) {
                $sql = "SELECT * FROM ssh_key WHERE id = " . intval($id_ssh_key);
                $res = $db->sql_query($sql);
                if ($ob = $db->sql_fetch_object($res)) {
                    // Crypt::decrypt doit exister dans ton projet (comme dans les autres controllers)
                    Crypt::$key = CRYPT_KEY;
                    $private_key_pem = Crypt::decrypt($ob->private_key, CRYPT_KEY);
                    $ssh_user = $ob->user;
                }
            }

            // Tentative de connexion SSH (si on a une clé)
            $ssh_ok = false;
            $ssh_error_msg = "";
            if (!empty($private_key_pem) && !empty($ssh_user)) {
                try {
                    $key = PublicKeyLoader::load($private_key_pem); // phpseclib3
                    $ssh = new SSH2($hostname, $port, 5); // timeout 5s
                    if ($ssh->login($ssh_user, $key)) {
                        $ssh_ok = true;
                    } else {
                        $ssh_error_msg = __("Failed to authenticate with provided SSH key/user");
                    }
                } catch (\Exception $e) {
                    $ssh_error_msg = $e->getMessage();
                }
            } else {
                // Si pas de clé fournie mais tu veux autoriser login/password,
                // tu peux étendre ici pour gérer login/password (non implémenté volontairement)
                $ssh_error_msg = __("No SSH key provided or SSH user missing");
            }

            if (!$ssh_ok) {
                // Ne pas ajouter le serveur si SSH invalide
                $title = I18n::getTranslation(__("Failed to connect to SSH"));
                $msg = I18n::getTranslation(__("Please check your hostname and credentials !")) . " " . $ssh_error_msg;
                set_flash("error", $title, $msg);

                // Préserve les champs soumis pour ré-affichage : convert to query string
                $elems = http_build_query(array('docker_server' => $data));
                header("Location: " . LINK . "docker/add?" . $elems);
                exit;
            }


            

            // Si SSH OK => insertion
            $docker_server = [
                'docker_server' => [
                    'display_name' => $name,
                    'hostname' => $hostname,
                    'port' => $port,
                    'id_ssh_key' => $id_ssh_key
                ]
            ];

            $id = $db->sql_save($docker_server);

            if (!$id) {
                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Fail to add this docker server"));
                $msg = I18n::getTranslation(__("One or more problems occurred when trying to add this server, please verify your informations"));
                set_flash("error", $title, $msg);

                $elems = http_build_query(array('docker_server' => $data));
                header("Location: " . LINK . "docker/add?" . $elems);
                exit;
            }

            $ret = self::getCredentials([$id]);

            if ($ret['success'] === true)
            {
                debug($ret);

                $ret2 = self::pushConfig([$id, 'docker', 'bind_address', '0.0.0.0']);

                if ($ret2 === true)
                {
                    $organization = 'docker'.crc32($id);

                    $mysql_server = 
                    [
                        'fqdn' => $hostname,
                        'ip' => $hostname,
                        'port' => 3306,
                        'display_name' => '@hostname',
                        'login' => $ret['user'],
                        'password' => $ret['pass'],
                        'organization' => $organization,
                        'environment'=> 'Docker'
                    ];


                    $server = Mysql::addMysqlServer($mysql_server);
                    
                    $sql = "UPDATE docker_server SET id_mysql_server=".$server['mysql_server']['id']." WHERE id=".$id;
                    $db->sql_query($sql);
                }
            }




            set_flash("success", I18n::getTranslation(__("Successful")), I18n::getTranslation(__("The docker server has been successfully added!")));
            header("Location: " . LINK . "docker/index");
            exit;
        }

        // GET => préparation des selects pour la vue
        $sql = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res = $db->sql_query($sql);
        $data['geolocalisation_country'] = $db->sql_to_array($res);

        $sql = "SELECT id, name, type, bit, fingerprint, user FROM ssh_key ORDER BY name";
        $res = $db->sql_query($sql);
        $data['ssh_key'] = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->type . ":" . $ob->bit . " bit) " . $ob->fingerprint;
            $data['ssh_key'][] = $tmp;
        }

 
        $this->set('data', $data);

    }

    public function installMariadb($param)
    {

        Debug::parseDebug($parma);
        $id_docker_server = $param[0];

        self::$logger->info("Docker::installMariadb() on host id=$id_docker_server");

        // Ssh::ssh($id_mysql_server);


        $ssh = Ssh::ssh($id_docker_server, 'docker');
        
        // --------------------------------------------------------
        // 1) Detect if server binary exists (not the client!)
        // --------------------------------------------------------
        $server_bin = trim($ssh->exec("command -v mariadbd || command -v mysqld || echo ''"));

        self::$logger->info("MariaDB Exist ? : ".$server_bin."--");
        

        if (empty($server_bin)) {
            self::$logger->info("MariaDB server not installed -> installing... (id=$id_docker_server)");

            // Detect package manager
            $pm = trim($ssh->exec("if command -v apt-get >/dev/null 2>&1; then echo apt; elif command -v dnf >/dev/null 2>&1; then echo dnf; elif command -v yum >/dev/null 2>&1; then echo yum; fi"));

            if ($pm === "apt") {
                $ssh->exec("DEBIAN_FRONTEND=noninteractive apt-get update -y");
                $ssh->exec("DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server");
            }
            elseif ($pm === "dnf" || $pm === "yum") {
                $ssh->exec("$pm install -y mariadb-server");
            }
            else {
                self::$logger->error("Package manager not detected on server id=$id_docker_server");
                return false;
            }
        }
        else {
            self::$logger->info("MariaDB server binary detected: $server_bin");
        }

        // --------------------------------------------------------
        // 2) Detect correct systemd service name
        // --------------------------------------------------------
        $service = trim($ssh->exec("
            (systemctl list-unit-files | grep -Eo '^mariadb' || \
            systemctl list-unit-files | grep -Eo '^mysqld' || \
            echo 'mariadb')
        "));

        $commandes = explode("\n",$service );
        $gg = array_unique($commandes);

        $service = reset($gg);


        self::$logger->info("MariaDB service detected as: $service");

        // --------------------------------------------------------
        // 3) Ensure service is running
        // --------------------------------------------------------
        $service_status = trim($ssh->exec("systemctl is-active $service 2>/dev/null"));
        if ($service_status !== "active") {
            self::$logger->info("MariaDB installed but not running -> starting service...");
            $ssh->exec("sudo systemctl enable $service --now");
            sleep(2);
        }

        // --------------------------------------------------------
        // 4) Ensure pmacontrol user exists
        // --------------------------------------------------------
        $ret = self::getCredentials([$id_docker_server]);

        self::$logger->info("Answer : ". json_encode($ret));

        self::$logger->info("Docker::installMariadb() done for id=$id_docker_server");

        return true;
    }

    public function before($param)
    {
        $monolog       = new Logger("Docker");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);

        $class = explode("\\",__CLASS__);
        $logFileClass = TMP . "log/" . strtolower(end($class)) . ".log";
        $handlerClass = new StreamHandler($logFileClass, Logger::DEBUG); // ou NOTICE/INFO
        $handlerClass->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handlerClass);

        self::$logger = $monolog;
    }


    private static function password($length = 32)
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        // allowed specials (no single quote, no backslash)
        $specials = '!@#$%^&*()-_=+[]{}:;,.<>?';
        $all = $lower . $upper . $digits . $specials;

        // ensure at least one of each class
        $password = [];
        $password[] = $lower[random_int(0, strlen($lower) - 1)];
        $password[] = $upper[random_int(0, strlen($upper) - 1)];
        $password[] = $digits[random_int(0, strlen($digits) - 1)];
        $password[] = $specials[random_int(0, strlen($specials) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        // shuffle
        for ($i = count($password) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $password[$i];
            $password[$i] = $password[$j];
            $password[$j] = $tmp;
        }

        return implode('', $password);
    }



    public static function getCredentials(array $param): array
    {
        Debug::parseDebug($param);

        $ret = [
            'success' => false,
            'user'    => null,
            'pass'    => null,
            'msg'     => '',
        ];

        $id_docker_server = $param[0] ?? '';

        try {
            $ssh = Ssh::ssh($id_docker_server, "docker"); // doit retourner phpseclib3\Net\SSH2
        } catch (\Exception $e) {
            $ret['msg'] = "SSH connect error: " . $e->getMessage();
            if (isset(self::$logger)) self::$logger->error($ret['msg']);
            else error_log($ret['msg']);
            return $ret;
        }

        if (empty($ssh) || !($ssh instanceof SSH2)) {
            $ret['msg'] = "SSH resource invalid for id=$id_docker_server";
            if (isset(self::$logger)) self::$logger->error($ret['msg']);
            else error_log($ret['msg']);
            return $ret;
        }

        // 1) Check if file exists and read it

        $cmd = 'if [ -f /root/'.self::PMACONTROL_CNF.' ]; then cat /root/'.self::PMACONTROL_CNF.'; else echo "__PMACNF_MISSING__"; fi';
        $cat = $ssh->exec($cmd);
        
        Debug::debug($cmd, "CMD");
        
        if ($cat === null) { // phpseclib returns null on some errors
            $ret['msg'] = "SSH exec failed while checking /root/".self::PMACONTROL_CNF;
            if (isset(self::$logger)) self::$logger->error($ret['msg']);
            else error_log($ret['msg']);
            return $ret;
        }

        $cat = trim($cat);
        if ($cat !== "__PMACNF_MISSING__") {
            // parse existing file
            $user = null;
            $pass = null;
            if (preg_match('/^\s*user\s*=\s*(.+?)\s*$/mi', $cat, $m)) {
                $user = trim($m[1]);
            }
            if (preg_match('/^\s*password\s*=\s*\'(.+?)\'\s*$/mi', $cat, $m)) {
                $pass = trim($m[1]);
            }

            if ($user && $pass) {
                $ret['success'] = true;
                $ret['user']    = $user;
                $ret['pass']    = $pass;
                $ret['msg']     = "Credentials loaded from /root/".self::PMACONTROL_CNF;
                if (isset(self::$logger)) self::$logger->info($ret['msg']." id=$id_docker_server");

                Debug::debug($ret,"RET");
                return $ret;
            } else {

                Debug::debug($ret, "RET");
                // file malformed: log and fallthrough to recreate
                $msg = "/root/".self::PMACONTROL_CNF." present but parsing failed - will attempt to recreate";
                if (isset(self::$logger)) self::$logger->warning($msg . " id=$id_docker_server");
                else error_log($msg);
                // continue to creation flow
            }
        }


        $pmUser = 'pmacontrol';
        $pmPass = self::password();

        // 2a) Check that mysql cli and server are available
        $mysql_version = trim($ssh->exec('mysql --version 2>/dev/null || true'));
        if (empty($mysql_version)) {
            // mysql client not present — log and return false (you can choose to install client here)
            $ret['msg'] = "MySQL client not found on remote host (id=$id_docker_server). Install mysql client/server first.";
            if (isset(self::$logger)) self::$logger->error($ret['msg']);
            else error_log($ret['msg']);
            return $ret;
        }

        // 2b) Create user in MySQL as root (idempotent)
        // escape single quotes in password (should not be issue with generated password but be safe)
        $escapedPass = str_replace("'", "'\"'\"'", $pmPass);

        $createSql = "GRANT ALL ON *.* TO `{$pmUser}`@'%' IDENTIFIED BY '{$pmPass}' WITH GRANT OPTION;";

        // Run SQL as root (assumes root can connect without password via socket or root passwordless sudo)
        // Use sudo mysql -uroot -e "...". Protect newlines and quotes by replacing newlines with spaces
        $cmd = 'mysql -uroot -e ' . escapeshellarg($createSql);
        $execCreate = $ssh->exec($cmd . ' 2>&1 || true');

        // basic check: if execCreate contains 'ERROR' treat as failure, but some warnings are okay
        if ($execCreate === null) {
            $ret['msg'] = "Failed to execute MySQL command to create user (null response).";
            if (isset(self::$logger)) self::$logger->error($ret['msg'] . " id=$id_docker_server");
            else error_log($ret['msg']);
            return $ret;
        }

        if (stripos($execCreate, 'ERROR') !== false) {
            $ret['msg'] = "MySQL returned error while creating user: " . trim($execCreate);
            if (isset(self::$logger)) self::$logger->error($ret['msg'] . " id=$id_docker_server");
            else error_log($ret['msg']);
            return $ret;
        }

        // 2c) write /root/.pmacontrol.cnf with strict permissions
        // Use a heredoc on remote to avoid quoting hell. We will ensure the file is owned root and chmod 600.
        $cnfContent = "[client]\nuser={$pmUser}\npassword='{$pmPass}'\n";
        // We'll use base64 to safely write content
        $b64 = base64_encode($cnfContent);
        $writeCmd = "echo {$b64} | base64 -d > /root/".self::PMACONTROL_CNF." && chmod 600 /root/".self::PMACONTROL_CNF." && chown root:root /root/".self::PMACONTROL_CNF." 2>&1 || true";
        $execWrite = $ssh->exec($writeCmd);

        if ($execWrite === null) {
            $ret['msg'] = "Failed to write /root/".self::PMACONTROL_CNF." (null response)";
            if (isset(self::$logger)) self::$logger->error($ret['msg'] . " id=$id_docker_server");
            else error_log($ret['msg']);
            return $ret;
        }

        // verify file written and readable
        $verify = trim($ssh->exec('cat /root/'.self::PMACONTROL_CNF.' 2>/dev/null || echo "__NO_CAT__"'));
        if ($verify === "__NO_CAT__" || stripos($verify, 'user=') === false) {
            $ret['msg'] = "Failed to verify /root/".self::PMACONTROL_CNF." content after write. exec: " . trim($execWrite);
            if (isset(self::$logger)) self::$logger->error($ret['msg'] . " id=$id_docker_server");
            else error_log($ret['msg']);
            return $ret;
        }

        if ($ssh){
            $ssh->disconnect();
        }

        
        // success
        $ret['success'] = true;
        $ret['user']    = $pmUser;
        $ret['pass']    = $pmPass;
        $ret['msg']     = "User created and /root/".self::PMACONTROL_CNF." written";

        if (isset(self::$logger)) {
            self::$logger->info($ret['msg'] . " id=$id_docker_server");
        } else {
            error_log($ret['msg'] . " id=$id_docker_server");
        }

        return $ret;
    }


    public static function pushConfig($param): bool
    {
        Debug::parseDebug($param);

        $id_server = (int) $param[0];
        $type_server = $param[1];
        $variable_name = $param[2];
        $variable_value = $param[3];

        $variable_name = strtolower(str_replace('-', '_', $variable_name));

        $variable_file = strtolower(str_ireplace("_", "-",$variable_name ));
        $file = '/etc/mysql/mariadb.conf.d/99-pmacontrol-'.$variable_file.'.cnf';

        $ssh = ssh::ssh($id_server, $type_server);
        if (!$ssh) {
            self::$logger->error("SSH failed for server id=$id_server type=$type_server");
            return false;
        }

        $query = "SELECT VARIABLE_NAME, GLOBAL_VALUE, READ_ONLY 
            FROM INFORMATION_SCHEMA.SYSTEM_VARIABLES 
            WHERE VARIABLE_NAME = '" . strtoupper($variable_name) . "'";

        $sql = "mysql -uroot -Nse " . escapeshellarg($query);
        $result = trim($ssh->exec($sql));

        if (empty($result)) {
            self::$logger->error("Variable $variable_name not found on server id=$id_server");
            $ssh->disconnect();
            return false;
        }

        list($varname, $current_value, $read_only) = preg_split('/\s+/', $result, 3);

        self::$logger->info("Variable detected: $varname | Current: $current_value | Read-Only: $read_only");

        // 2) If value is already correct → nothing to do
        if ($current_value == $variable_value) {
            self::$logger->info("No change needed for $variable_name on server id=$id_server");
            $ssh->disconnect();
            return true;
        }

        // 3) If NOT READ ONLY → we can SET GLOBAL live
        if (strtoupper($read_only) === "NO") {

            self::$logger->info("Applying SET GLOBAL $variable_name = '$variable_value'");

            $set_cmd = "mysql -uroot -e " . escapeshellarg("SET GLOBAL $variable_name = '$variable_value';");
            $ssh->exec($set_cmd);
            $ssh->disconnect();
            return true;
        }

        // 4) READ_ONLY → must write config file and restart MariaDB
        self::$logger->info("$variable_name is READ_ONLY → applying config file + restart");

        // Write config file
        $content = "[mysqld]\n$variable_name = $variable_value\n";
        $b64 = base64_encode($content);

        $cmd  = "echo $b64 | base64 -d | tee $file >/dev/null";
        $cmd .= " && chmod 644 $file";
        $cmd .= " && chown root:root $file";
        $ssh->exec($cmd);

        // Detect service name
        $service = trim($ssh->exec("
            (systemctl list-unit-files | grep -Eo '^mariadb' || \
            systemctl list-unit-files | grep -Eo '^mysqld' || \
            echo 'mariadb')
        "));

        $commandes = explode("\n",$service );
        $all_commandes = array_unique($commandes);

        $service = reset($all_commandes);

        // Restart MariaDB cleanly
        $ssh->exec("systemctl restart $service");
        if ($ssh){
            $ssh->disconnect();
        }

        self::$logger->info("$variable_name updated via config + restart on server id=$id_server");

        return true;
    }


    public function delete($param)
    {
        Debug::parseDebug($param);

        $id = intval($param[0] ?? 0);
        if ($id <= 0) {
            set_flash("error", __("Error"), __("Invalid ID"));
            header("Location: " . LINK . "docker/index");
            exit;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Vérifier si le docker server existe
        $sql = "SELECT * FROM docker_server WHERE id=".$id;
        $res = $db->sql_query($sql);
        $server = $db->sql_fetch_object($res);

        if (!$server) {
            set_flash("error", __("Error"), __("Docker server not found"));
            header("Location: " . LINK . "docker/index");
            exit;
        }

        // Suppression
        $sql = "DELETE FROM docker_server WHERE id=".$id;
        $db->sql_query($sql);

        set_flash("success", __("Success"), __("Docker server has been deleted"));
        self::$logger->info("Docker server id=$id deleted from configuration");

        header("Location: " . LINK . "docker/index");
        exit;
    }



    public function server($param)
    {
        Debug::parseDebug($param);

        $id_docker_server = (int)$param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT ds.*, sk.name as ssh_key_name, sk.user as ssh_user
                FROM docker_server ds
                LEFT JOIN ssh_key sk ON sk.id = ds.id_ssh_key
                WHERE ds.id = $id_docker_server";

        $res = $db->sql_query($sql);
        $data['server'] = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($data['server'])) {
            set_flash("error", __("Server not found"), __("The requested Docker server does not exist."));
            header("Location: " . LINK . "docker/index");
            exit;
        }

        // Liste des containers associés à ce host (si docker_database_instance déjà en place)
        $sql = "SELECT a.*, b.display_name as software_name, c.tag
                FROM docker_database_instance a
                INNER JOIN docker_image c ON c.id = a.id_docker_image
                INNER JOIN docker_software b ON b.id = c.id_docker_software
                WHERE a.id_docker_server = $id_docker_server
                ORDER BY a.id ASC";

        $res = $db->sql_query($sql);
        $data['instances'] = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['instances'][] = $row;
        }

        $this->set('data', $data);
    }

    public function addContainer($param)
    {
        $id_docker_server = $param[0] ?? '';


        $db = Sgbd::sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $rows = $_POST['docker_container'] ?? null;

            if (!$rows || !is_array($rows)) {
                set_flash("error", "No data", "No container definition provided.");
                header("location: " . LINK . "docker/addContainer");
                exit;
            }

            $finalList = []; // => liste des conteneurs à créer

            // On parcours chaque ligne du formulaire
            $countList   = $rows['count'] ?? [];
            $softList    = $rows['id_software'] ?? [];
            $majorList   = $rows['major'] ?? [];
            $imageList   = $rows['id_image'] ?? [];
            $labelList   = $rows['label'] ?? [];

            $numRows = count($softList);

            for ($i = 0; $i < $numRows; $i++) {

                $count     = (int)$countList[$i];
                $softId    = (int)$softList[$i];
                $major     = trim($majorList[$i]);
                $imageId   = (int)$imageList[$i];
                $labelBase = trim($labelList[$i]);

                // ignore ligne vide
                if (!$softId || !$imageId) {
                    continue;
                }

                // Récupère l'image choisie (pour connaître nom + tag + sha)
                $sql = "SELECT s.name AS family, s.display_name, i.tag
                        FROM docker_image i
                        INNER JOIN docker_software s ON s.id = i.id_docker_software
                        WHERE i.id = ".$imageId;
                $res = $db->sql_query($sql);
                $img = $db->sql_fetch_array($res, MYSQLI_ASSOC);

                if (!$img) {
                    continue;
                }

                // Pour count > 1 on génère des labels auto si non fournis
                for ($x = 1; $x <= $count; $x++) {

                    $label = (empty($labelBase)) ? $img['family'] . "-" . $img['tag'] . "-" . self::getIdContener() : $labelBase . "-" . self::getIdContener();

                    $finalList[] = [
                        'family' => $img['family'], // ex: mariadb, mysql, proxysql
                        'tag'    => $img['tag'],    // ex: 11.4.2
                        'label'  => $label,
                        'image_id' => $imageId,
                    ];
                }
            }

            if (empty($finalList)) {
                set_flash("error", "No container generated", "Please select at least one valid version.");
                header("location: " . LINK . "docker/addContainer");
                exit;
            }

            // ✅ À PARTIR D'ICI finalList contient exactement toutes les instances à créer
            // On redirige vers une page de confirmation, ou on lance directement la création

            $_SESSION['docker_containers_pending'] = $finalList;

            set_flash("success", "Containers prepared", count($finalList) . " container(s) ready.");
            header("location: " . LINK . "docker/server/".$id_docker_server);
            exit;
        }

        // Familles (docker_software) -> id/libelle
        $sql = "SELECT id, display_name FROM docker_software ORDER BY name";
        $res = $db->sql_query($sql);
        $data['software'] = [];
        while ($o = $db->sql_fetch_object($res)) {
            $data['software'][] = ['id' => (int)$o->id, 'libelle' => $o->display_name];
        }

        // Listes majors + tags
        // On reprend ta requête, en ajoutant id_software:
        $sql = "
            SELECT a.id AS id_software, b.id AS id_image, a.display_name, a.name,
                SUBSTRING_INDEX(b.tag, '.', 2) AS major_version, b.tag
            FROM docker_software a
            INNER JOIN docker_image b ON a.id=b.id_docker_software
            ORDER BY a.name,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 1), '.', -1) AS UNSIGNED) ASC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tag, '.', 2), '.', -1) AS UNSIGNED) ASC,
            CAST(SUBSTRING_INDEX(tag, '.', -1) AS UNSIGNED) ASC
        ";
        $res = $db->sql_query($sql);

        $majors = []; // [software_id] => [ ['id'=>major_key, 'libelle'=>major_key], ... ]
        $tags   = []; // [software_id][major_key] => [ ['id'=>id_image,'libelle'=>tag], ... ]
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $sid   = (int)$r['id_software'];
            $major = $r['major_version'];
            $tag   = $r['tag'];
            $imgId = (int)$r['id_image'];

            if (!isset($majors[$sid])) { $majors[$sid] = []; }
            if (!isset($tags[$sid]))   { $tags[$sid]   = []; }
            if (!isset($tags[$sid][$major])) {
                $tags[$sid][$major] = [];
            }

            // ajoute major si pas déjà
            $already = false;
            foreach ($majors[$sid] as $m) {
                if ($m['id'] === $major) { $already = true; break; }
            }
            if (!$already) {
                $majors[$sid][] = ['id' => $major, 'libelle' => $major];
            }

            // ajoute tag
            $tags[$sid][$major][] = ['id' => $imgId, 'libelle' => $tag];
        }

        $data['majors'] = $majors;
        $data['tags']   = $tags;

        $this->set('id_docker_server', $id_docker_server);
        $this->set('data', $data);
    }



    public function create($param)
    {
        Debug::parseDebug($param);

        $id_docker_server = (int) ($param[0] ?? 0);
        $id_image         = (int) ($param[1] ?? 0);

        if (!$id_docker_server || !$id_image) {
            set_flash("error", "Missing parameters", "id_docker_server or id_image invalid.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Récupère image + software (nom + tag)
        $sql = "SELECT s.name AS family, s.display_name, i.tag
                FROM docker_image i
                INNER JOIN docker_software s ON s.id = i.id_docker_software
                WHERE i.id = ".$id_image;
        $res = $db->sql_query($sql);
        $img = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (!$img) {
            set_flash("error", "Image not found", "Please refresh available software.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        // Connexion SSH
        try {
            $ssh = Ssh::ssh($id_docker_server, "docker");
        } catch (\Exception $e) {
            set_flash("error", "SSH error", $e->getMessage());
            header("location: " . LINK . "docker/index");
            exit;
        }

        if (!$ssh) {
            set_flash("error", "SSH failure", "Cannot create container.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        // === Génération dynamique ===

        $password = Docker::password(); // déjà existante
        $family = strtolower($img['family']);
        $tag = $img['tag'];

        // trouver un port libre
        $port = trim($ssh->exec("
            for p in \$(seq 3306 3399); do
              netstat -tuln | grep -q \":\$p \" || { echo \$p; break; }
            done
        "));

        if (!$port) {
            set_flash("error", "No free port", "No available port on remote machine.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        // container name and hostname
        $container_name = $family."_".$tag."_".$port;
        $hostname = $container_name;

        // final docker run
        $cmd = <<<CMD
docker run --detach -h "$hostname" --name "$container_name" -p "$port:$port" \
  --env MARIADB_ROOT_PASSWORD="$password" \
  --env MARIADB_PASSWORD="$password" \
  mariadb:"$tag" \
  --log-bin \
  --server-id="$port" \
  --performance-schema=on \
  --gtid-domain-id="$port" \
  --port="$port"
CMD;

        $out = $ssh->exec($cmd . " 2>&1");

        // Vérification du lancement
        $check = trim($ssh->exec("docker ps --filter name=$container_name --format '{{.Names}}'"));

        if ($check !== $container_name) {
            set_flash("error", "Container creation failed", "Output: " . htmlspecialchars($out));
            header("location: " . LINK . "docker/index");
            exit;
        }

        // Inserer en BDD table docker_container
        $insert = [
            'docker_container' => [
                'id_docker_server' => $id_docker_server,
                'id_docker_image'  => $id_image,
                'container_name'   => $container_name,
                'port'             => $port,
                'password'         => $password,
                'is_running'       => 1,
            ]
        ];
        $db->sql_save($insert);


    }



    public function getImageAvailable($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $id_docker_server = (int)($param[0] ?? 0);


        // Connexion SSH au serveur docker
        try {
            $ssh = Ssh::ssh($id_docker_server, "docker");
        } catch (\Exception $e) {
            set_flash("error", "SSH Error", $e->getMessage());
            header("location: " . LINK . "docker/index");
            exit;
        }

        // Récupère liste des images disponibles sur le serveur
        $result = $ssh->exec("docker image ls --format '{{.Repository}} {{.Tag}} {{.ID}} {{.Size}}'");

        $lines = explode("\n", trim($result));

        Debug::debug($lines, "IMAGES");

        foreach ($lines as $line) {

            if (preg_match('/(\S+)\s+(\S+)\s+([a-z0-9]{12})\s+(\S+)$/', $line, $m)) {

                [$all, $repository, $tag, $sha256, $size] = $m;

                // Update du docker_image (sha256 + size)
                $sql = "UPDATE docker_image a
                        INNER JOIN docker_software b ON a.id_docker_software = b.id
                        SET a.sha256='".$db->sql_real_escape_string($sha256)."',
                            a.size='".$db->sql_real_escape_string($size)."'
                        WHERE b.name='".$db->sql_real_escape_string($repository)."'
                        AND a.tag='".$db->sql_real_escape_string($tag)."'";
                Debug::sql($sql);

                $db->sql_query($sql);

                // Récupère l'id de docker_image
                $sql2 = "SELECT a.id
                        FROM docker_image a
                        INNER JOIN docker_software b ON a.id_docker_software = b.id
                        WHERE b.name='".$db->sql_real_escape_string($repository)."'
                        AND a.tag='".$db->sql_real_escape_string($tag)."'";
                Debug::sql($sql2);

                $res2 = $db->sql_query($sql2);
                $row = $db->sql_fetch_array($res2, MYSQLI_ASSOC);

                if (!$row) {
                    continue;
                }

                $id_docker_image = (int)$row['id'];

                // Insert link docker_image <-> docker_server si pas existant
                $sql3 = "INSERT IGNORE INTO docker_image__docker_server (id_docker_image, id_docker_server)
                        VALUES ($id_docker_image, $id_docker_server)";
                        Debug::sql($sql3);

                $db->sql_query($sql3);
            }
        }


    }


    public function imageAvailable($param)
    {
        Debug::parseDebug($param);
        $id_docker_server = (int)($param[0] ?? 0);

        if ($id_docker_server === 0) {
            set_flash("error", "Missing server", "No docker server selected.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Informations du serveur docker
        $sql = "SELECT * FROM docker_server WHERE id = ".$id_docker_server;
        $res = $db->sql_query($sql);
        $data['server'] = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (!$data['server']) {
            set_flash("error", "Unknown server", "Server does not exist.");
            header("location: " . LINK . "docker/index");
            exit;
        }

        // Liste des images disponibles sur CE serveur
        $sql = "
            SELECT di.id AS id_docker_image,
                   s.display_name AS family,
                   s.name AS repository,
                   di.tag,
                   di.sha256,
                   di.size,
                   s.background,
                   s.color
            FROM docker_image__docker_server link
            INNER JOIN docker_image di ON di.id = link.id_docker_image
            INNER JOIN docker_software s ON s.id = di.id_docker_software
            WHERE link.id_docker_server = ".$id_docker_server."
            ORDER BY s.name, di.tag
        ";

        $res = $db->sql_query($sql);
        $data['images'] = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['images'][] = $row;
        }

        $this->set('data', $data);
    }


    public function createDocker($param)
    {
        Debug::parseDebug($param);

        $id_server_docker = $param[0] ?? '';
        $id_docker_image = $param[1] ?? '';
        $hostname = $param[2] ?? '';
        $port = $param[3] ?? '';

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT b.name,a.tag
        FROM docker_image a
        INNER JOIN docker_software b ON a.id_docker_software = b.id
        WHERE a.id = ".$id_docker_image;

        $res = $db->sql_query($sql);

        $password = Docker::password();
        $label = "container"; // Fix unassigned variable, though method seems incomplete
        $container_name = hash('sha256', $label);
        //$hostname =

        while ($ob = $db->sql_fetch_object($res))
        {
            switch($ob->name)
            {
                case "mysql":
                    
                    break;


                case "percona/percona-server":

                    break;


                case "mariadb":
                    $cmd = "docker run --detach -h '$hostname' --name '$container_name' -p '$port:3306' \
            --env MARIADB_ROOT_PASSWORD='$password' \
            --env MARIADB_PASSWORD='$password' mariadb:'{$ob->tag}' \
            --log-bin \
            --server-id='$port' \
            --performance-schema=on \
            --gtid-domain-id='$port'";
                    break;
            }
        }


        $ret= [
            "success" => true,
            "user" => "root",
            "password" => $password
        ];
    }


    private function getIdContener()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT LPAD(NEXTVAL(contener_id_seq), 4, '0') AS server_code;";
        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res))
        {
            return $ob->server_code;
        }

        return "9999";
    }

}
