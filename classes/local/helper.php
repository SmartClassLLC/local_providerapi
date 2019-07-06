<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * short_description
 *
 * long_description
 *
 * @package    local_providerapi
 * @copyright  2019 çağlar MERSİNLİ <ceremy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_providerapi\local;

use context_course;
use local_providerapi\local\course\course;

defined('MOODLE_INTERNAL') || die();

/**
 * Class helper
 *
 * @package local_providerapi\local
 */
class helper {
    /*
     * The value used when we want to enrol new members and unenrol old ones.
     */
    /**
     *
     */
    const MEMBER_SYNC_ENROL_AND_UNENROL = 1;

    /*
     * The value used when we want to enrol new members only.
     */
    /**
     *
     */
    const MEMBER_SYNC_ENROL_NEW = 2;

    /*
     * The value used when we want to unenrol missing users.
     */
    /**
     *
     */
    const MEMBER_SYNC_UNENROL_MISSING = 3;

    /**
     * Code for when an enrolment was successful.
     */
    const ENROLMENT_SUCCESSFUL = true;

    /**
     * Error code for enrolment when max enrolled reached.
     */
    const ENROLMENT_MAX_ENROLLED = 'maxenrolledreached';

    /**
     * Error code for enrolment has not started.
     */
    const ENROLMENT_NOT_STARTED = 'enrolmentnotstarted';

    /**
     * Error code for enrolment when enrolment has finished.
     */
    const ENROLMENT_FINISHED = 'enrolmentfinished';

    /**
     * Error code for when an image file fails to upload.
     */
    const PROFILE_IMAGE_UPDATE_SUCCESSFUL = true;

    /**
     * Error code for when an image file fails to upload.
     */
    const PROFILE_IMAGE_UPDATE_FAILED = 'profileimagefailed';

    /**
     * @param int $courseid
     * @return bool|int|mixed
     * @throws \dml_exception
     */
    public static function create_tool(int $courseid) {
        global $DB;
        // Is valid course?
        if (!$DB->record_exists('course', array('id' => $courseid))) {
            return false;
        }
        if ($toolid = $DB->get_field('local_api_tools', 'id', array('courseid' => $courseid))) {
            return $toolid;
        }
        $tool = new \stdClass();
        $tool->courseid = $courseid;
        $tool->contextid = context_course::instance($courseid, MUST_EXIST)->id;
        $tool->secret = random_string(32);
        $tool->timecreated = time();
        $tool->timemodified = $tool->timecreated;
        return $DB->insert_record('local_api_tools', $tool);

    }

    /**
     * @param int $courseid
     * @return bool
     * @throws \dml_exception
     */
    public static function delete_tool(int $courseid) {
        global $DB;
        return $DB->delete_records('local_api_tools', array('courseid' => $courseid));
    }

    /**
     * Updates the users profile image.
     *
     * @param int $userid the id of the user
     * @param string $url the url of the image
     * @return bool|string true if successful, else a string explaining why it failed
     */
    public static function update_user_profile_image($userid, $url) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->libdir . '/gdlib.php');

        $fs = get_file_storage();

        $context = \context_user::instance($userid, MUST_EXIST);
        $fs->delete_area_files($context->id, 'user', 'newicon');

        $filerecord = array(
                'contextid' => $context->id,
                'component' => 'user',
                'filearea' => 'newicon',
                'itemid' => 0,
                'filepath' => '/'
        );

        $urlparams = array(
                'calctimeout' => false,
                'timeout' => 5,
                'skipcertverify' => true,
                'connecttimeout' => 5
        );

        try {
            $fs->create_file_from_url($filerecord, $url, $urlparams);
        } catch (\file_exception $e) {
            return get_string($e->errorcode, $e->module, $e->a);
        }

        $iconfile = $fs->get_area_files($context->id, 'user', 'newicon', false, 'itemid', false);

        // There should only be one.
        $iconfile = reset($iconfile);

        // Something went wrong while creating temp file - remove the uploaded file.
        if (!$iconfile = $iconfile->copy_content_to_temp()) {
            $fs->delete_area_files($context->id, 'user', 'newicon');
            return self::PROFILE_IMAGE_UPDATE_FAILED;
        }

        // Copy file to temporary location and the send it for processing icon.
        $newpicture = (int) process_new_icon($context, 'user', 'icon', 0, $iconfile);
        // Delete temporary file.
        @unlink($iconfile);
        // Remove uploaded file.
        $fs->delete_area_files($context->id, 'user', 'newicon');
        // Set the user's picture.
        $DB->set_field('user', 'picture', $newpicture, array('id' => $userid));
        return self::PROFILE_IMAGE_UPDATE_SUCCESSFUL;
    }

    /**
     * Returns the LTI tool.
     *
     * @param int $toolid
     * @return \stdClass the tool
     */
    public static function get_lti_tool($toolid) {
        global $DB;
        return $DB->get_record('local_api_tools', array('id' => $toolid), '*', MUST_EXIST);
    }

    /**
     * @param int $courseid
     * @return mixed
     * @throws \dml_exception
     */
    public static function get_toolid_by_courseid(int $courseid) {
        global $DB;
        return $DB->get_field('local_api_tools', 'id', array('courseid' => $courseid));
    }

    /**
     * Create a IMS POX body request for sync grades.
     *
     * @param string $source Sourceid required for the request
     * @param float $grade User final grade
     * @return string
     */
    public static function create_service_body($source, $grade) {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <imsx_POXEnvelopeRequest xmlns="http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
              <imsx_POXHeader>
                <imsx_POXRequestHeaderInfo>
                  <imsx_version>V1.0</imsx_version>
                  <imsx_messageIdentifier>' . (time()) . '</imsx_messageIdentifier>
                </imsx_POXRequestHeaderInfo>
              </imsx_POXHeader>
              <imsx_POXBody>
                <replaceResultRequest>
                  <resultRecord>
                    <sourcedGUID>
                      <sourcedId>' . $source . '</sourcedId>
                    </sourcedGUID>
                    <result>
                      <resultScore>
                        <language>en-us</language>
                        <textString>' . $grade . '</textString>
                      </resultScore>
                    </result>
                  </resultRecord>
                </replaceResultRequest>
              </imsx_POXBody>
            </imsx_POXEnvelopeRequest>';
    }

    /**
     * Returns the url to launch the lti tool.
     *
     * @param int $toolid the id of the shared tool
     * @return \moodle_url the url to launch the tool
     * @since Moodle 3.2
     */
    public static function get_launch_url($toolid) {
        return new \moodle_url('/local/providerapi/tool.php', array('id' => $toolid));
    }

    /**
     * Returns the name of the lti enrolment instance, or the name of the course/module being shared.
     *
     * @param \stdClass $tool The lti tool
     * @return string The name of the tool
     * @since Moodle 3.2
     */
    public static function get_name($tool) {
        $course = course::get_by_courseid($tool->courseid);
        return $course->get_formatted_name();
    }

    /**
     * Returns a description of the course or module that this lti instance points to.
     *
     * @param \stdClass $tool The lti tool
     * @return string A description of the tool
     * @since Moodle 3.2
     */
    public static function get_description($tool) {
        global $DB;
        $description = '';
        $context = \context::instance_by_id($tool->contextid);
        if ($context->contextlevel == CONTEXT_COURSE) {
            $course = $DB->get_record('course', array('id' => $context->instanceid));
            $description = $course->summary;
        } else if ($context->contextlevel == CONTEXT_MODULE) {
            $cmid = $context->instanceid;
            $cm = get_coursemodule_from_id(false, $context->instanceid, 0, false, MUST_EXIST);
            $module = $DB->get_record($cm->modname, array('id' => $cm->instance));
            $description = $module->intro;
        }
        return trim(html_to_text($description));
    }

    /**
     * Returns the icon of the tool.
     *
     * @param \stdClass $tool The lti tool
     * @return \moodle_url A url to the icon of the tool
     * @since Moodle 3.2
     */
    public static function get_icon($tool) {
        global $OUTPUT;
        return $OUTPUT->favicon();
    }

    /**
     * Returns the url to the cartridge representing the tool.
     *
     * If you have slash arguments enabled, this will be a nice url ending in cartridge.xml.
     * If not it will be a php page with some parameters passed.
     *
     * @param \stdClass $tool The lti tool
     * @return string The url to the cartridge representing the tool
     * @since Moodle 3.2
     */
    public static function get_cartridge_url($tool) {
        global $CFG;
        $url = null;

        $id = $tool->id;
        $token = self::generate_cartridge_token($tool->id);
        if ($CFG->slasharguments) {
            $url = new \moodle_url('/local/providerapi/cartridge.php/' . $id . '/' . $token . '/cartridge.xml');
        } else {
            $url = new \moodle_url('/local/providerapi/cartridge.php',
                    array(
                            'id' => $id,
                            'token' => $token
                    )
            );
        }
        return $url;
    }

    /**
     * Returns the url to the tool proxy registration url.
     *
     * If you have slash arguments enabled, this will be a nice url ending in cartridge.xml.
     * If not it will be a php page with some parameters passed.
     *
     * @param \stdClass $tool The lti tool
     * @return string The url to the cartridge representing the tool
     */
    public static function get_proxy_url($tool) {
        global $CFG;
        $url = null;

        $id = $tool->id;
        $token = self::generate_proxy_token($tool->id);
        if ($CFG->slasharguments) {
            $url = new \moodle_url('/local/providerapi/proxy.php/' . $id . '/' . $token . '/');
        } else {
            $url = new \moodle_url('/local/providerapi/proxy.php',
                    array(
                            'id' => $id,
                            'token' => $token
                    )
            );
        }
        return $url;
    }

    /**
     * Returns a unique hash for this site and this enrolment instance.
     *
     * Used to verify that the link to the cartridge has not just been guessed.
     *
     * @param int $toolid The id of the shared tool
     * @return string MD5 hash of combined site ID and enrolment instance ID.
     * @since Moodle 3.2
     */
    public static function generate_cartridge_token($toolid) {
        $siteidentifier = get_site_identifier();
        $checkhash = md5($siteidentifier . '_local_api_cartridge_' . $toolid);
        return $checkhash;
    }

    /**
     * Returns a unique hash for this site and this enrolment instance.
     *
     * Used to verify that the link to the proxy has not just been guessed.
     *
     * @param int $toolid The id of the shared tool
     * @return string MD5 hash of combined site ID and enrolment instance ID.
     * @since Moodle 3.2
     */
    public static function generate_proxy_token($toolid) {
        $siteidentifier = get_site_identifier();
        $checkhash = md5($siteidentifier . '_local_api_proxy_' . $toolid);
        return $checkhash;
    }

    /**
     * Verifies that the given token matches the cartridge token of the given shared tool.
     *
     * @param int $toolid The id of the shared tool
     * @param string $token hash for this site and this enrolment instance
     * @return boolean True if the token matches, false if it does not
     * @since Moodle 3.2
     */
    public static function verify_cartridge_token($toolid, $token) {
        return $token == self::generate_cartridge_token($toolid);
    }

    /**
     * Verifies that the given token matches the proxy token of the given shared tool.
     *
     * @param int $toolid The id of the shared tool
     * @param string $token hash for this site and this enrolment instance
     * @return boolean True if the token matches, false if it does not
     * @since Moodle 3.2
     */
    public static function verify_proxy_token($toolid, $token) {
        return $token == self::generate_proxy_token($toolid);
    }

    /**
     * Returns the parameters of the cartridge as an associative array of partial xpath.
     *
     * @param int $toolid The id of the shared tool
     * @return array Recursive associative array with partial xpath to be concatenated into an xpath expression
     *     before setting the value.
     * @since Moodle 3.2
     */
    protected static function get_cartridge_parameters($toolid) {
        global $PAGE, $SITE;
        $PAGE->set_context(\context_system::instance());

        // Get the tool.
        $tool = self::get_lti_tool($toolid);

        // Work out the name of the tool.
        $title = self::get_name($tool);
        $launchurl = self::get_launch_url($toolid);
        $launchurl = $launchurl->out(false);
        $iconurl = self::get_icon($tool);
        $iconurl = $iconurl->out(false);
        $securelaunchurl = null;
        $secureiconurl = null;
        $vendorurl = new \moodle_url('/');
        $vendorurl = $vendorurl->out(false);
        $description = self::get_description($tool);

        // If we are a https site, we can add the launch url and icon urls as secure equivalents.
        if (\is_https()) {
            $securelaunchurl = $launchurl;
            $secureiconurl = $iconurl;
        }

        return array(
                "/cc:cartridge_basiclti_link" => array(
                        "/blti:title" => $title,
                        "/blti:description" => $description,
                        "/blti:extensions" => array(
                                "/lticm:property[@name='icon_url']" => $iconurl,
                                "/lticm:property[@name='secure_icon_url']" => $secureiconurl
                        ),
                        "/blti:launch_url" => $launchurl,
                        "/blti:secure_launch_url" => $securelaunchurl,
                        "/blti:icon" => $iconurl,
                        "/blti:secure_icon" => $secureiconurl,
                        "/blti:vendor" => array(
                                "/lticp:code" => $SITE->shortname,
                                "/lticp:name" => $SITE->fullname,
                                "/lticp:description" => trim(html_to_text($SITE->summary)),
                                "/lticp:url" => $vendorurl
                        )
                )
        );
    }

    /**
     * Traverses a recursive associative array, setting the properties of the corresponding
     * xpath element.
     *
     * @param \DOMXPath $xpath The xpath with the xml to modify
     * @param array $parameters The array of xpaths to search through
     * @param string $prefix The current xpath prefix (gets longer the deeper into the array you go)
     * @return void
     * @since Moodle 3.2
     */
    protected static function set_xpath($xpath, $parameters, $prefix = '') {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                self::set_xpath($xpath, $value, $prefix . $key);
            } else {
                $result = @$xpath->query($prefix . $key);
                if ($result) {
                    $node = $result->item(0);
                    if ($node) {
                        if (is_null($value)) {
                            $node->parentNode->removeChild($node);
                        } else {
                            $node->nodeValue = s($value);
                        }
                    }
                } else {
                    throw new \coding_exception('Please check your XPATH and try again.');
                }
            }
        }
    }

    /**
     * Create an IMS cartridge for the tool.
     *
     * @param int $toolid The id of the shared tool
     * @return string representing the generated cartridge
     * @since Moodle 3.2
     */
    public static function create_cartridge($toolid) {
        $cartridge = new \DOMDocument();
        $cartridge->load(realpath(__DIR__ . '/../xml/imslticc.xml'));
        $xpath = new \DOMXpath($cartridge);
        $xpath->registerNamespace('cc', 'http://www.imsglobal.org/xsd/imslticc_v1p0');
        $parameters = self::get_cartridge_parameters($toolid);
        self::set_xpath($xpath, $parameters);

        return $cartridge->saveXML();
    }
}
