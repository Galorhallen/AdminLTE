<?php
/* Pi-hole: A black hole for Internet advertisements
*  (c) 2017 Pi-hole, LLC (https://pi-hole.net)
*  Network-wide ad blocking via your own hardware.
*
*  This file is copyright under the latest version of the EUPL.
*  Please see LICENSE file for your rights under this license. */

require_once('auth.php');

$list = $_POST['list'];

// Perform all of the authentication for list editing
// when NOT invoked and authenticated from API
if (empty($api)) {
    list_verify($list);
}

// Only check domains we add to the exact lists.
// Regex are validated by FTL during import
$check_lists = ["white","black","audit"];
if (in_array($list, $check_lists)) {
    check_domain();
}

// Split individual domains into array
$domains = preg_split('/\s+/', trim($_POST['domain']));
$comment = trim($_POST['comment']);

require_once("func.php");
require_once("database.php");
$GRAVITYDB = getGravityDBFilename();
$db = SQLite3_connect($GRAVITYDB, SQLITE3_OPEN_READWRITE);

switch ($list) {
    case "white":
        echo add_to_table($db, "whitelist", $domains, $comment);
        break;

    case "black":
        echo add_to_table($db, "blacklist", $domains, $comment);
        break;

    case "black_regex":
        echo add_to_table($db, "regex_blacklist", $domains, $comment);
        break;

    case "white_regex":
        echo add_to_table($db, "regex_whitelist", $domains, $comment);
        break;

    case "black_wild":
        echo add_to_table($db, "regex_blacklist", $domains, $comment, true);
        break;

    case "white_wild":
        echo add_to_table($db, "regex_whitelist", $domains, $comment, true);
        break;

    case "audit":
        echo add_to_table($db, "domain_audit", $domains, $comment);
        break;

    default:
        die("Invalid list!");
}

// Reload lists in pihole-FTL after having added something
echo shell_exec("sudo pihole restartdns reload");
