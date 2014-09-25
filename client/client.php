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
/// 1. Enable web service advance feature (Admin > Advanced features)
/// 2. Enable REST protocol (Admin > Plugins > Web services > Manage protocols)
/// 3. Create a token for a specific user and for the service 'Get badges' (Admin > Plugins > Web services > Manage tokens)
/// 4. Edit client/client.php file
/// 5. Run this script directly from your browser: http://Moodle_URL/local/badgesapi/client/client.php

/// SETUP - NEED TO BE CHANGED
$token = 'f3db42a4089c7b03ab420d001dfe1659';
$domainname = 'http://www.moodle.dev';
/// PARAMETERS
$courseid = 263; // Course ID

/// FUNCTION NAME
$functionname = 'local_badgesapi_get_badges';

$data = file_get_contents("$domainname/webservice/rest/server.php?wstoken=$token&wsfunction=$functionname&moodlewsrestformat=json&courseid=$courseid");

print_r($data);