<?php
require_once 'database.php';
require_once 'email.php';

$DB = new DB();
$data = $_POST['data'];
$method = $_POST['method'];

switch ($method) {
    case 'saveEmails':
        $res = $DB->saveEmails($data);
        return $res;
    case 'formwardEmaiils':
        try {
            for ($i = 0; $i < count($data); $i++) {
                $from = $data[$i]['sender'];
                $subject = $data[$i]['subject'];
                $body = $data[$i]['bodyText'];
                $ips = isset($data[$i]['ip']) ? $data[$i]['ip'] : [];
                $urls = isset($data[$i]['url']) ? $data[$i]['url'] : [];
                $pos_1 = array_search('127.0.0.1', $ips);
                $pos_2 = array_search('nforce.com', $urls);
                if ($pos_1 !== false || $pos_2 !== false) {
                    $email = createEmail($from, 'abuse@nforce.com', $subject, $body);
                    sendEmail($email);
                }
            }
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    default:
        # code...
        break;
}

?>