<?php
/**
 * @package openpsa.test
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace test\org\openpsa\directmarketing\handler\message;

use openpsa_testcase;
use openpsa_test_campaign_helper;
use midcom;

/**
 * OpenPSA testcase
 *
 * @package openpsa.test
 */
class createTest extends openpsa_testcase
{
    protected static $_person;

    public static function setUpBeforeClass() : void
    {
        self::$_person = self::create_user(true);
    }

    public function testHandler_create()
    {
        $helper = new openpsa_test_campaign_helper;
        $campaign = $helper->get_campaign();

        midcom::get()->auth->request_sudo('org.openpsa.directmarketing');

        $data = $this->run_handler('org.openpsa.directmarketing', ['message', 'create', $campaign->guid, 'textemail']);
        $this->assertEquals('create_message', $data['handler_id']);

        midcom::get()->auth->drop_sudo();
    }
}
