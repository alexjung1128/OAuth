<?php
class DB
{
    private $serverName = 'localhost';
    private $userName = 'root';
    private $password = '';
    private $dbName = 'phpEmail';
    private $conn;
    public function __construct($config = [])
    {

        $defaultConfig = array_merge([
            "serverName" => $this->serverName,
            "userName" => $this->userName,
            "password" => $this->password,
            "database" => $this->dbName
        ], $config);

        $this->conn = mysqli_connect($defaultConfig['serverName'], $defaultConfig['userName'], $defaultConfig['password'], $defaultConfig[
            "database"
        ]);
        // check connection
        if (!$this->conn) {
            unset($this->conn);
        }
    }

    public function getEmailData() {

    }

    public function saveEmails($emails = []) {
        $conn = $this->conn;
        $sql = '';
        for ($i=0; $i < count($emails); $i++) { 
            $sender = $emails[$i]['sender'];
            $body = $emails[$i]['body'];
            $subject = $emails[$i]['subject'];
            $date = $emails[$i]['date'];
            $mesId = $emails[$i]['id'];
            $attchs = isset($emails[$i]['attachs']) ? $emails[$i]['attachs'] : [];
            $ips = isset($emails[$i]['ip']) ? $emails[$i]['ip'] : [];
            $urls = isset($emails[$i]['url']) ? $emails[$i]['url'] : [];
            $ipStr = implode("PHP-IP", $ips);
            $urlStr = implode("PHP-URL", $urls);
            $attchStr = implode("PHP-attch",$attchs);
            $sql .= "INSERT INTO emails (sender, body, subject, date, mesId, ips, urls, attach) VALUES ('$sender', '$body', '$subject', '$date', '$mesId', '$ipStr', '$urlStr', '$attchStr');";
        }
        if (mysqli_multi_query($conn, $sql)) {
            return true;
        } else {
            echo $conn->error;
        }
        return false;       
    }
}
?>