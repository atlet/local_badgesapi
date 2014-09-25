<?php
// This client for local_wstemplate is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_wstemplate
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @authorr Jerome Mouneyrac
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable XMLRPC protocol (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user and for the service 'My service' (Admin > Plugins > Web services > Manage tokens)
// 5- Run this script directly from your browser: you should see 'Hello, FIRSTNAME'

/// SETUP - NEED TO BE CHANGED
$token = 'f3db42a4089c7b03ab420d001dfe1659';
$domainname = 'http://www.moodle.dev';

/// FUNCTION NAME
$functionname = 'local_badgesapi_get_badges';

/// PARAMETERS
$courseid = 263;

$data = file_get_contents("$domainname/webservice/rest/server.php?wstoken=$token&wsfunction=$functionname&moodlewsrestformat=json&courseid=$courseid");

print_r($data);