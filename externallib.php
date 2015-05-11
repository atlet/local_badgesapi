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
 * External Web Service Template
 *
 * @package    localbadgesapi
 * @copyright  2014 Andraž Prinčič s.p. (http://www.princic.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . "/filelib.php");
require_once($CFG->libdir . "/datalib.php");
require_once($CFG->libdir . "/badgeslib.php");

class local_badgesapi_external extends external_api {

    public static function get_badges_report_parameters() {
        return new external_function_parameters(
                array('courseid' => new external_value(PARAM_INT, 'Course id', VALUE_DEFAULT, 0))
        );
    }

    public static function get_badges_report($courseid = 0) {
        global $DB;

        $params = self::validate_parameters(self::get_badges_report_parameters(), array('courseid' => $courseid));
        $context = context_course::instance($courseid);
        self::validate_context($context);

        $badges = $DB->get_records_sql("SELECT 
                                            b.id,
                                            b.name AS badgename,
                                            COUNT(u.username) AS userscount,
                                            f.contextid,
                                            f.component,
                                            f.filearea,
                                            f.itemid,
                                            f.filepath,
                                            f.filename
                                        FROM
                                            {badge_issued} AS d
                                                JOIN
                                            {badge} AS b ON d.badgeid = b.id
                                                JOIN
                                            {user} AS u ON d.userid = u.id
                                                JOIN
                                            {user_enrolments} AS ue ON ue.userid = u.id
                                                JOIN
                                            {enrol} AS en ON en.id = ue.enrolid
                                                JOIN
                                            {badge_criteria} AS t ON b.id = t.badgeid
                                                JOIN
                                            {files} AS f ON f.id = (SELECT 
                                                    MAX(f2.id)
                                                FROM
                                                    {files} AS f2
                                                WHERE
                                                    b.id = f2.itemid
                                                        AND f2.component LIKE 'badges'
                                                        AND f2.filearea LIKE 'badgeimage')
                                        WHERE
                                            t.criteriatype <> 0
                                                AND en.courseid = :couseid
                                                AND d.visible = 1
                                        GROUP BY b.name", array('couseid' => $courseid));

        $tmpBadges = array();

        foreach ($badges as $badge) {
            $tmpBadge = array();

            $imageurl = moodle_url::make_pluginfile_url($badge->contextid, 'badges', 'badgeimage', $badge->id, '/',
                            "f1", false);
            $imageurl->param('refresh', rand(1, 10000));

            $tmpBadge['badgename'] = $badge->badgename;
            $tmpBadge['userscount'] = $badge->userscount;
            $tmpBadge['badgeimageurl'] = $imageurl->out();

            $tmpBadges['badges'][] = $tmpBadge;
        }

        return $tmpBadges;
    }

    public static function get_badges_report_returns() {
        return new external_single_structure(array('badges' => new external_multiple_structure(
                    new external_single_structure(
                    array(
                'badgename' => new external_value(PARAM_TEXT, 'Badge name'),
                'userscount' => new external_value(PARAM_INT, 'Users count'),
                'badgeimageurl' => new external_value(PARAM_TEXT, 'Badge image URL')
        )))));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_badges_parameters() {
        return new external_function_parameters(
                array('courseid' => new external_value(PARAM_INT, 'Course id', VALUE_DEFAULT, 0))
        );
    }

    // http://www.moodle.dev/webservice/rest/server.php?wstoken=f3db42a4089c7b03ab420d001dfe1659&wsfunction=local_badgesapi_get_badges&moodlewsrestformat=json&courseid=263

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_badges($courseid = 0) {
        global $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_badges_parameters(), array('courseid' => $courseid));
        $context = context_course::instance($courseid);
        self::validate_context($context);

        $userprofilefields = $DB->get_records_select('user_info_field',
                'id > 0 AND shortname IN ("Datumrojstva", "VIZ")', array(), 'id', 'id, shortname, name');

        $allUsers = array();

        $users = get_enrolled_users($context);

        $sql = 'SELECT
                bi.uniquehash,
                bi.dateissued,
                bi.dateexpire,
                bi.id as issuedid,
                bi.visible,
                u.email,
                b.*
            FROM
                {badge} b,
                {badge_issued} bi,
                {user} u
            WHERE b.id = bi.badgeid
                AND u.id = bi.userid
                AND bi.userid = ?';

        foreach ($users as $user) {

            $certs = $DB->get_records_sql($sql, array($user->id));

            $tmpUser = array();
            $tmpUser['id'] = $user->id;
            $tmpUser['username'] = $user->username;
            $tmpUser['firstname'] = $user->firstname;
            $tmpUser['lastname'] = $user->lastname;
            $tmpUser['email'] = $user->email;

            $addFields = $DB->get_records_select('user_info_data', 'userid = ' . $user->id, array(), 'fieldid');

            foreach ($userprofilefields as $profilefieldid => $profilefield) {
                $tmpUser[$profilefield->shortname] = strip_tags($DB->get_field('user_info_data', 'data',
                                array('fieldid' => $profilefieldid, 'userid' => $user->id)));
            }

            $tmpUser['badges'] = array();

            foreach ($certs as $cert) {
                $badge = array();

                $badge['id'] = $cert->id;
                $badge['uniquehash'] = $cert->uniquehash;
                $badge['dateissued'] = $cert->dateissued;
                $badge['email'] = $cert->email;
                $badge['name'] = $cert->name;
                $badge['description'] = $cert->description;
                $badge['issuername'] = $cert->issuername;
                $badge['issuerurl'] = $cert->issuerurl;
                $badge['issuercontact'] = $cert->issuercontact;
                $badge['courseid'] = $cert->courseid;

                $tmpUser['badges'][] = $badge;
            }

            // datum rojstva - custom
            // naslov institucije
            // badges
            // naziv seminarja
            // trajanje seminarja
            // datum seminarja
            // 

            $allUsers[] = $tmpUser;
        }

        return $allUsers;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_badges_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                array(
            'id' => new external_value(PARAM_INT, 'User ID'),
            'username' => new external_value(PARAM_TEXT, 'Username'),
            'firstname' => new external_value(PARAM_TEXT, 'First name'),
            'lastname' => new external_value(PARAM_TEXT, 'Last name'),
            'email' => new external_value(PARAM_TEXT, 'E-mail'),
            'Datumrojstva' => new external_value(PARAM_TEXT, 'Datum rojstva - custom field (Datumrojstva)'),
            'VIZ' => new external_value(PARAM_TEXT, 'Zaposlitev (institucija) - custom field (VIZ)'),
            'badges' => new external_multiple_structure(new external_single_structure(
                    array(
                'id' => new external_value(PARAM_TEXT, 'Badge ID'),
                'uniquehash' => new external_value(PARAM_TEXT, 'Unique hash'),
                'dateissued' => new external_value(PARAM_TEXT, 'Date issued'),
                'email' => new external_value(PARAM_TEXT, 'E-mail'),
                'name' => new external_value(PARAM_TEXT, 'Badge name'),
                'description' => new external_value(PARAM_TEXT, 'Badge description'),
                'issuername' => new external_value(PARAM_TEXT, 'Issuer name'),
                'issuerurl' => new external_value(PARAM_TEXT, 'Issuer URL'),
                'issuercontact' => new external_value(PARAM_TEXT, 'Issuer contact'),
                'courseid' => new external_value(PARAM_TEXT, 'Course ID'),
                    )
                    )),
        )));
    }

}
