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

use local_providerapi\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Test the helper functionality.
 *
 * @package enrol_lti
 * @copyright 2016 Mark Nelson <markn@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_providerapi_helper_testcase extends advanced_testcase {

    /**
     * @var stdClass $user1 A user.
     */
    public $user1;

    /**
     * @var stdClass $user2 A user.
     */
    public $user2;

    /**
     * Test set up.
     *
     * This is executed before running any test in this file.
     */
    public function setUp() {
        $this->resetAfterTest();

        // Set this user as the admin.
        $this->setAdminUser();

        // Get some of the information we need.
        $this->user1 = self::getDataGenerator()->create_user();
        $this->user2 = self::getDataGenerator()->create_user();
    }

    /**
     * Test the update user profile image function.
     */
    public function test_update_user_profile_image() {
        global $DB, $CFG;

        // Set the profile image.
        helper::update_user_profile_image($this->user1->id, $this->getExternalTestFileUrl('/test.jpg'));

        // Get the new user record.
        $this->user1 = $DB->get_record('user', array('id' => $this->user1->id));

        // Set the page details.
        $page = new moodle_page();
        $page->set_url('/user/profile.php');
        $page->set_context(context_system::instance());
        $renderer = $page->get_renderer('core');
        $usercontext = context_user::instance($this->user1->id);

        // Get the user's profile picture and make sure it is correct.
        $userpicture = new user_picture($this->user1);
        $this->assertSame($CFG->wwwroot . '/pluginfile.php/' . $usercontext->id . '/user/icon/boost/f2?rev=' .
                $this->user1->picture,
                $userpicture->get_url($page, $renderer)->out(false));
    }

    /**
     * Test getting the launch url of a tool.
     */
    public function test_get_launch_url() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $id = $tool1->id;
        $launchurl = helper::get_launch_url($id);
        $this->assertEquals('https://www.example.com/moodle/local/providerapi/tool.php?id=' . $id, $launchurl->out());
    }

    /**
     * Test getting the cartridge url of a tool.
     */
    public function test_get_cartridge_url() {
        global $CFG;

        $slasharguments = $CFG->slasharguments;

        $CFG->slasharguments = false;

        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $id = $tool1->id;
        $token = helper::generate_cartridge_token($id);
        $launchurl = helper::get_cartridge_url($tool1);
        $this->assertEquals('https://www.example.com/moodle/local/providerapi/cartridge.php?id=' . $id . '&amp;token=' . $token,
                $launchurl->out());

        $CFG->slasharguments = true;

        $launchurl = helper::get_cartridge_url($tool1);
        $this->assertEquals('https://www.example.com/moodle/local/providerapi/cartridge.php/' . $id . '/' . $token .
                '/cartridge.xml',
                $launchurl->out());

        $CFG->slasharguments = $slasharguments;
    }

    /**
     * Test getting the cartridge url of a tool.
     */
    public function test_get_proxy_url() {
        global $CFG;

        $slasharguments = $CFG->slasharguments;

        $CFG->slasharguments = false;

        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $id = $tool1->id;
        $token = helper::generate_proxy_token($id);
        $launchurl = helper::get_proxy_url($tool1);
        $this->assertEquals('https://www.example.com/moodle/local/providerapi/proxy.php?id=' . $id . '&amp;token=' . $token,
                $launchurl->out());

        $CFG->slasharguments = true;

        $launchurl = helper::get_proxy_url($tool1);
        $this->assertEquals('https://www.example.com/moodle/local/providerapi/proxy.php/' . $id . '/' . $token . '/',
                $launchurl->out());

        $CFG->slasharguments = $slasharguments;
    }

    /**
     * Test getting the name of a tool.
     */
    public function test_get_name() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $name = helper::get_name($tool1);
        $this->assertEquals('Test course 1', $name);
    }

    /**
     * Test getting the description of a tool.
     */
    public function test_get_description() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $description = helper::get_description($tool1);
        $this->assertContains('Test course 1 Lorem ipsum dolor sit amet', $description);
    }

    /**
     * Test getting the icon of a tool.
     */
    public function test_get_icon() {
        global $CFG;
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool = $providergenerator->create_lti_tool();
        $icon = helper::get_icon($tool);
        $icon = $icon->out();
        // Only local icons are supported by the LTI framework.
        $this->assertContains($CFG->wwwroot, $icon);

    }

    /**
     * Test verifying a cartridge token.
     */
    public function test_verify_cartridge_token() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $token = helper::generate_cartridge_token($tool1->id);
        $this->assertTrue(helper::verify_cartridge_token($tool1->id, $token));
        $this->assertFalse(helper::verify_cartridge_token($tool1->id, 'incorrect token!'));
    }

    /**
     * Test verifying a proxy token.
     */
    public function test_verify_proxy_token() {
        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $token = helper::generate_proxy_token($tool1->id);
        $this->assertTrue(helper::verify_proxy_token($tool1->id, $token));
        $this->assertFalse(helper::verify_proxy_token($tool1->id, 'incorrect token!'));
    }

    /**
     * Data provider for the set_xpath test.
     */
    public function set_xpath_provider() {
        return [
                "Correct structure" => [
                        "parameters" => [
                                "/root" => [
                                        "/firstnode" => "Content 1",
                                        "/parentnode" => [
                                                "/childnode" => "Content 2"
                                        ]
                                ]
                        ],
                        "expected" => "test_correct_xpath-expected.xml"
                ],
                "A null value, but no node to remove" => [
                        "parameters" => [
                                "/root" => [
                                        "/nonexistant" => null,
                                        "/firstnode" => "Content 1"
                                ]
                        ],
                        "expected" => "test_missing_node-expected.xml"
                ],
                "A string value, but no node existing to set" => [
                        "parameters" => [
                                "/root" => [
                                        "/nonexistant" => "This will not be set",
                                        "/firstnode" => "Content 1"
                                ]
                        ],
                        "expected" => "test_missing_node-expected.xml"
                ],
                "Array but no children exist" => [
                        "parameters" => [
                                "/root" => [
                                        "/nonexistant" => [
                                                "/alsononexistant" => "This will not be set"
                                        ],
                                        "/firstnode" => "Content 1"
                                ]
                        ],
                        "expected" => "test_missing_node-expected.xml"
                ],
                "Remove nodes" => [
                        "parameters" => [
                                "/root" => [
                                        "/parentnode" => [
                                                "/childnode" => null
                                        ],
                                        "/firstnode" => null
                                ]
                        ],
                        "expected" => "test_nodes_removed-expected.xml"
                ],
                "Get by attribute" => [
                        "parameters" => [
                                "/root" => [
                                        "/ambiguous[@id='1']" => 'Content 1'
                                ]
                        ],
                        "expected" => "test_ambiguous_nodes-expected.xml"
                ]
        ];
    }

    /**
     * Test set_xpath.
     *
     * @dataProvider set_xpath_provider
     * @param array $parameters A hash of parameters represented by a heirarchy of xpath expressions
     * @param string $expected The name of the fixture file containing the expected result.
     */
    public function test_set_xpath($parameters, $expected) {
        $helper = new ReflectionClass('local_providerapi\\local\\helper');
        $function = $helper->getMethod('set_xpath');
        $function->setAccessible(true);

        $document = new \DOMDocument();
        $document->load(realpath(__DIR__ . '/fixtures/input.xml'));
        $xpath = new \DOMXpath($document);
        $function->invokeArgs(null, [$xpath, $parameters]);
        $result = $document->saveXML();
        $expected = file_get_contents(realpath(__DIR__ . '/fixtures/' . $expected));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test set_xpath when an incorrect xpath expression is given.
     */
    public function test_set_xpath_incorrect_xpath() {
        $parameters = [
                "/root" => [
                        "/firstnode" => null,
                        "/parentnode*&#^*#(" => [
                                "/childnode" => null
                        ],
                ]
        ];
        $helper = new ReflectionClass('local_providerapi\\local\\helper');
        $function = $helper->getMethod('set_xpath');
        $function->setAccessible(true);

        $document = new \DOMDocument();
        $document->load(realpath(__DIR__ . '/fixtures/input.xml'));
        $xpath = new \DOMXpath($document);

        $this->expectException('coding_exception');
        $function->invokeArgs(null, [$xpath, $parameters]);
    }

    /**
     * Test create cartridge.
     */
    public function test_create_cartridge() {
        global $CFG;

        $generator = $this->getDataGenerator();
        $providergenerator = $generator->get_plugin_generator('local_providerapi');
        $tool1 = $providergenerator->create_lti_tool();

        $cartridge = helper::create_cartridge($tool1->id);
        $this->assertContains('<blti:title>Test course 1</blti:title>', $cartridge);
        $this->assertContains("<blti:icon>$CFG->wwwroot/theme/image.php/_s/boost/theme/1/favicon</blti:icon>", $cartridge);
        $this->assertContains("<blti:launch_url>$CFG->wwwroot/local/providerapi/tool.php?id=$tool1->id</blti:launch_url>",
                $cartridge);
    }
}
