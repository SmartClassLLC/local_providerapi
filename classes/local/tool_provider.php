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

defined('MOODLE_INTERNAL') || die;

use context;
use enrol_lti\output\registration;
use html_writer;
use moodle_exception;
use moodle_url;
use stdClass;
use IMSGlobal\LTI\Profile\Message;
use IMSGlobal\LTI\Profile\ResourceHandler;
use IMSGlobal\LTI\Profile\ServiceDefinition;
use IMSGlobal\LTI\Profile\Item;
use IMSGlobal\LTI\ToolProvider\ToolProvider;

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Extends the IMS Tool provider library for the LTI enrolment.
 *
 * @package    enrol_lti
 * @copyright  2016 John Okely <john@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_provider extends ToolProvider {

    /**
     * @var stdClass $tool The object representing the enrol instance providing this LTI tool
     */
    protected $tool;

    /**
     * Remove $this->baseUrl (wwwroot) from a given url string and return it.
     *
     * @param string $url The url from which to remove the base url
     * @return string|null A string of the relative path to the url, or null if it couldn't be determined.
     */
    protected function strip_base_url($url) {
        if (substr($url, 0, strlen($this->baseUrl)) == $this->baseUrl) {
            return substr($url, strlen($this->baseUrl));
        }
        return null;
    }

    /**
     * Create a new instance of tool_provider to handle all the LTI tool provider interactions.
     *
     * @param int $toolid The id of the tool to be provided.
     */
    public function __construct($toolid) {
        global $CFG, $SITE;

        $token = helper::generate_proxy_token($toolid);

        $tool = helper::get_lti_tool($toolid);
        $this->tool = $tool;

        $dataconnector = new data_connector();
        parent::__construct($dataconnector);

        // Override debugMode and set to the configured value.
        $this->debugMode = $CFG->debugdeveloper;

        $this->baseUrl = $CFG->wwwroot;
        $toolpath = helper::get_launch_url($toolid);
        $toolpath = $this->strip_base_url($toolpath);

        $vendorid = $SITE->shortname;
        $vendorname = $SITE->fullname;
        $vendordescription = trim(html_to_text($SITE->summary));
        $this->vendor = new Item($vendorid, $vendorname, $vendordescription, $CFG->wwwroot);

        $name = helper::get_name($tool);
        $description = helper::get_description($tool);
        $icon = helper::get_icon($tool)->out();
        $icon = $this->strip_base_url($icon);

        $this->product = new Item(
                $token,
                $name,
                $description,
                helper::get_proxy_url($tool),
                '1.0'
        );

        $requiredmessages = [
                new Message(
                        'basic-lti-launch-request',
                        $toolpath,
                        [
                                'Context.id',
                                'CourseSection.title',
                                'CourseSection.label',
                                'CourseSection.sourcedId',
                                'CourseSection.longDescription',
                                'CourseSection.timeFrame.begin',
                                'ResourceLink.id',
                                'ResourceLink.title',
                                'ResourceLink.description',
                                'User.id',
                                'User.username',
                                'Person.name.full',
                                'Person.name.given',
                                'Person.name.family',
                                'Person.email.primary',
                                'Person.sourcedId',
                                'Person.name.middle',
                                'Person.address.street1',
                                'Person.address.locality',
                                'Person.address.country',
                                'Person.address.timezone',
                                'Person.phone.primary',
                                'Person.phone.mobile',
                                'Person.webaddress',
                                'Membership.role',
                                'Result.sourcedId',
                                'Result.autocreate'
                        ]
                )
        ];
        $optionalmessages = [
        ];

        $this->resourceHandlers[] = new ResourceHandler(
                new Item(
                        $token,
                        helper::get_name($tool),
                        $description
                ),
                $icon,
                $requiredmessages,
                $optionalmessages
        );

        $this->requiredServices[] = new ServiceDefinition(['application/vnd.ims.lti.v2.toolproxy+json'], ['POST']);
        $this->requiredServices[] = new ServiceDefinition(['application/vnd.ims.lis.v2.membershipcontainer+json'], ['GET']);
    }

    /**
     * Override onError for custom error handling.
     *
     * @return void
     */
    protected function onError() {
        global $OUTPUT;

        $message = $this->message;
        if ($this->debugMode && !empty($this->reason)) {
            $message = $this->reason;
        }

        // Display the error message from the provider's side if the consumer has not specified a URL to pass the error to.
        if (empty($this->returnUrl)) {
            $this->errorOutput =
                    $OUTPUT->notification(get_string('failedrequest', 'local_providerapi', ['reason' => $message]), 'error');
        }
    }

    /**
     * Override onLaunch with tool logic.
     *
     * @return void
     */
    protected function onLaunch() {
        global $DB, $SESSION, $CFG;

        // Check for valid consumer.
        if (empty($this->consumer) || $this->dataConnector->loadToolConsumer($this->consumer) === false) {
            $this->ok = false;
            $this->message = get_string('invalidtoolconsumer', 'local_providerapi');
            return;
        }

        $url = helper::get_launch_url($this->tool->id);
        // If a tool proxy has been stored for the current consumer trying to access a tool,
        // check that the tool is being launched from the correct url.
        $correctlaunchurl = false;
        if (!empty($this->consumer->toolProxy)) {
            $proxy = json_decode($this->consumer->toolProxy);
            $handlers = $proxy->tool_profile->resource_handler;
            foreach ($handlers as $handler) {
                foreach ($handler->message as $message) {
                    $handlerurl = new moodle_url($message->path);
                    $fullpath = $handlerurl->out(false);
                    if ($message->message_type == "basic-lti-launch-request" && $fullpath == $url) {
                        $correctlaunchurl = true;
                        break 2;
                    }
                }
            }
        } else if ($this->tool->secret == $this->consumer->secret) {
            // Test if the LTI1 secret for this tool is being used. Then we know the correct tool is being launched.
            $correctlaunchurl = true;
        }
        if (!$correctlaunchurl) {
            $this->ok = false;
            $this->message = get_string('invalidrequest', 'local_providerapi');
            return;
        }

        // Before we do anything check that the context is valid.
        $tool = $this->tool;
        $context = context::instance_by_id($tool->contextid);

        // Hack user staff.
        if ($dbuser = $DB->get_record('user', ['id' => $this->user->ltiUserId, 'deleted' => 0])) {
            $user = $dbuser;
        } else {
            $this->ok = false;
            $this->message = get_string('invaliduser', 'local_providerapi');
            return;
        }

        // Update user image.
        if (isset($this->user) && isset($this->user->image) && !empty($this->user->image)) {
            $image = $this->user->image;
        } else {
            // Use custom_user_image parameter as a fallback.
            $image = $this->resourceLink->getSetting('custom_user_image');
        }

        // Check if there is an image to process.
        if ($image) {
            helper::update_user_profile_image($user->id, $image);
        }

        // Check if we need to force the page layout to embedded.
        $isforceembed = $this->resourceLink->getSetting('custom_force_embed') == 1;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = $context->instanceid;
            $urltogo = new moodle_url('/course/view.php', ['id' => $courseid]);
        } else {
            print_error('invalidcontext');
            exit();
        }

        // Force page layout to embedded if necessary.
        if ($isforceembed) {
            $SESSION->forcepagelayout = 'embedded';
        } else {
            // May still be set from previous session, so unset it.
            unset($SESSION->forcepagelayout);
        }

        // Enrol check.
        $result = is_enrolled($context, $user->id);

        if (!$result) {
            $this->ok = false;
            $this->message = get_string('usernotenrolled', 'local_providerapi');
            return;
        }

        // Login user.
        $sourceid = $this->user->ltiResultSourcedId;
        $serviceurl = $this->resourceLink->getSetting('lis_outcome_service_url');

        // Check if we have recorded this user before.
        if ($userlog = $DB->get_record('local_api_users', ['toolid' => $tool->id, 'userid' => $user->id])) {
            if ($userlog->sourceid != $sourceid) {
                $userlog->sourceid = $sourceid;
            }
            if ($userlog->serviceurl != $serviceurl) {
                $userlog->serviceurl = $serviceurl;
            }
            $userlog->lastaccess = time();
            $DB->update_record('local_api_users', $userlog);
        } else {
            // Add the user details so we can use it later when syncing grades and members.
            $userlog = new stdClass();
            $userlog->userid = $user->id;
            $userlog->toolid = $tool->id;
            $userlog->serviceurl = $serviceurl;
            $userlog->sourceid = $sourceid;
            $userlog->consumerkey = $this->consumer->getKey();
            $userlog->consumersecret = $tool->secret;
            $userlog->lastgrade = 0;
            $userlog->lastaccess = time();
            $userlog->timecreated = time();
            $userlog->membershipsurl = $this->resourceLink->getSetting('ext_ims_lis_memberships_url');
            $userlog->membershipsid = $this->resourceLink->getSetting('ext_ims_lis_memberships_id');

            $DB->insert_record('local_api_users', $userlog);
        }

        // Finalise the user log in.
        complete_user_login($user);

        // Everything's good. Set appropriate OK flag and message values.
        $this->ok = true;
        $this->message = get_string('success');

        if (empty($CFG->allowframembedding)) {
            // Provide an alternative link.
            $stropentool = get_string('opentool', 'local_providerapi');
            echo html_writer::tag('p', get_string('frameembeddingnotenabled', 'local_providerapi'));
            echo html_writer::link($urltogo, $stropentool, ['target' => '_blank']);
        } else {
            // All done, redirect the user to where they want to go.
            redirect($urltogo);
        }
    }

    /**
     * Override onRegister with registration code.
     */
    protected function onRegister() {
        global $PAGE;

        if (empty($this->consumer)) {
            $this->ok = false;
            $this->message = get_string('invalidtoolconsumer', 'local_providerapi');
            return;
        }

        if (empty($this->returnUrl)) {
            $this->ok = false;
            $this->message = get_string('returnurlnotset', 'local_providerapi');
            return;
        }

        if ($this->doToolProxyService()) {
            // Map tool consumer and published tool, if necessary.
            $this->map_tool_to_consumer();

            // Indicate successful processing in message.
            $this->message = get_string('successfulregistration', 'local_providerapi');

            // Prepare response.
            $returnurl = new moodle_url($this->returnUrl);
            $returnurl->param('lti_msg', get_string("successfulregistration", 'local_providerapi'));
            $returnurl->param('status', 'success');
            $guid = $this->consumer->getKey();
            $returnurl->param('tool_proxy_guid', $guid);

            $returnurlout = $returnurl->out(false);

            $registration = new registration($returnurlout);
            $output = $PAGE->get_renderer('enrol_lti');
            echo $output->render($registration);

        } else {
            // Tell the consumer that the registration failed.
            $this->ok = false;
            $this->message = get_string('couldnotestablishproxy', 'enrol_lti');
        }
    }

    /**
     * Performs mapping of the tool consumer to a published tool.
     *
     * @throws moodle_exception
     */
    public function map_tool_to_consumer() {
        global $DB;

        if (empty($this->consumer)) {
            throw new moodle_exception('invalidtoolconsumer', 'local_providerapi');
        }

        // Map the consumer to the tool.
        $mappingparams = [
                'toolid' => $this->tool->id,
                'consumerid' => $this->consumer->getRecordId()
        ];
        $mappingexists = $DB->record_exists('local_api_tool_consumer_map', $mappingparams);
        if (!$mappingexists) {
            $DB->insert_record('local_api_tool_consumer_map', (object) $mappingparams);
        }
    }
}
