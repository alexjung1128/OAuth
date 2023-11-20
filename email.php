<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'utils/index.php';

$forwarder = "abuse@nforce.com";

function googleAuth()
{
    $client = new Google_Client();
    $client->setAuthConfig('./config/certificate.json');
    $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
    $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);

    if (!isset($_SESSION['access_token']) && !isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    } elseif (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        header('Location: ' . filter_var('http://localhost/dashboard.php', FILTER_SANITIZE_URL));
    }

    if (isset($_SESSION['access_token'])) {
        $client->setAccessToken($_SESSION['access_token']);

        header('Location: ' . filter_var('http://localhost/dashboard.php', FILTER_SANITIZE_URL));
    }
}
function getMessages()
{
    $client = new Google_Client();
    if (isset($_SESSION['access_token'])) {
        // Set the client's access token from the session.
        $client->setAccessToken($_SESSION['access_token']);
    } else {
        // Redirect to the login page if there's no access token.
        header('Location: index.php');
    }
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['access_token']);
        header('Location: index.php');
    }

    $service = new Google_Service_Gmail($client);

    // Get the list of messages
    $user = 'me';
    $list = $service->users_messages->listUsersMessages($user, ['maxResults' => 10]);

    $res = [];
    // Print each message subject
    foreach ($list->getMessages() as $mes) {
        $message = $service->users_messages->get($user, $mes->getId());
        $payload = $message->getPayload();
        $headers = $payload->getHeaders();
        $parts = $payload->getParts();
        $body = $payload->getBody();
        $mesId = $mes->getId();
        $subject = '';
        $sender = '';
        $mesBody = '';
        $base64Body = '';
        $date;
        $ips = [];
        $urls = [];
        $attachs = [];
        foreach ($headers as $header) {
            if ($header->getName() == 'Subject') {
                $subject = $header->getValue();
            }
            if ($header->getName() == 'From') {
                $sender = $header->getValue();
            }
            if ($header->getName() == 'Date') {
                $date = new DateTime($header->getValue());
            }
        }

        foreach ($parts as $part) {
            if ($part['mimeType'] == 'text/plain') {
                $temp = $part->getBody();
                $base64Body = $temp->data;
                $mesBody = base64url_decode($temp->data);
                $urls = detectURLS($mesBody);
                $ips = detectIPs($mesBody);
            }
            // attachment is image
            if (!empty($part->getFilename())) {
                if (str_starts_with($part->getMimeType(), "image/")) { // check if attachment is an image
                    // Fetch image attachments only
                    $data = $part->getBody()->data;

                    // Sometimes base64 data is url-safe, so we replace '-' with '+' and '_' with '/'
                    $data = strtr($data, '-_', '+/');

                    // Decode base64 formatted data
                    $decoded = base64_decode($data);

                    // You can then output this however you want, but for simplicity,
                    // I'll just output a img tag with the base64. This method should be implemented according 
                    // to your application needs. You might want to save images to a directory instead.

                    $img = '<img src="data:' . $part->getMimeType() . ';base64,' . $data . '" />';
                    array_push($attachs, $img);
                }
            }
        }
        $mesRes = [
            "id" => $mesId,
            "subject" => $subject,
            "sender" => $sender,
            "date" => $date->format('Y-m-d'),
            "time" => $date->format('H:i:s'),
            "body" => $base64Body,
            "bodyText" => $mesBody,
            "ip" => $ips,
            "url" => $urls,
            "attachs" => $attachs
        ];
        array_push($res, $mesRes);
    }

    return $res;
}
function createEmail($from, $to, $subject, $message)
{
    $boundary = uniqid(rand(), true);
    $email = "From: <{$from}>\r\n";
    $email .= "To: <{$to}>\r\n";
    $email .= "Subject: =?utf-8?B?" . base64_encode($subject) . "?=\r\n";
    $email .= "MIME-Version: 1.0\r\n";
    $email .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $email .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";

    // match 6
    $email .= "\r\n--{$boundary}\r\n";
    $message = preg_replace_callback(
        "/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/",
        function ($matches) {
            global $forwarder;
            if ($matches[0] === $forwarder) {
                return "<a href='mailto:{$forwarder}' target='_blank' style='text-decoration: underline;text-decoration-color: green;'>" . $forwarder . "</a>";
            } else {
                return "<a href='mailto:{$matches[0]}' target='_blank' style='text-decoration: underline;text-decoration-color: red;'>" . $matches[0] . "</a>";
            }
        },
        $message
    );
    $message = "<!DOCTYPE html><head><title>Document</title><style>a { text-decoration: none!important; }</style></head><body>{$message}</body></html>";
    $email .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email .= "{$message}\r\n";

    $base64_email = base64_encode($email);
    $base64_email = str_replace(['+', '/', '='], ['-', '_', ''], $base64_email);
    return $base64_email;
}
function sendEmail($email)
{
    $client = new Google_Client();
    if (isset($_SESSION['access_token'])) {
        // Set the client's access token from the session.
        $client->setAccessToken($_SESSION['access_token']);
    } else {
        // Redirect to the login page if there's no access token.
        return false;
    }
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['access_token']);
        return false;
    }

    $service = new Google_Service_Gmail($client);

    $user = 'me';

    $message = new Google_Service_Gmail_Message();
    $message->setRaw($email);

    $result = $service->users_messages->send($user, $message);
    return $result;

}

?>