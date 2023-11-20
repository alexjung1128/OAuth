<?php
function base64url_decode($data)
{
    return base64_decode(strtr($data, '-_,', '+/='));
}
function detectIPs($text)
{
    preg_match_all('/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/', $text, $matches);
    $ips = $matches[0];
    return $ips;
}
function detectURLS($text)
{
    preg_match_all('@\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))@', $text, $matches);
    $urls = $matches[0];
    return $urls;
}

?>