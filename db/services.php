<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// We defined the web service functions to install.
$functions = array(
    'local_badgesapi_get_badges' => array(
        'classname' => 'local_badgesapi_external',
        'methodname' => 'get_badges',
        'classpath' => 'local/badgesapi/externallib.php',
        'description' => 'Return all issued badges for selected course.',
        'type' => 'read',
    ),
    'local_badgesapi_get_badges_report' => array(
        'classname' => 'local_badgesapi_external',
        'methodname' => 'get_badges_report',
        'classpath' => 'local/badgesapi/externallib.php',
        'description' => 'Return all badges with issued badges for selected course.',
        'type' => 'read',
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Get badges' => array(
        'functions' => array('local_badgesapi_get_badges'),
        'restrictedusers' => 0,
        'enabled' => 1,
    ),
    'Get badges report' => array(
        'functions' => array('local_badgesapi_get_badges_report'),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
