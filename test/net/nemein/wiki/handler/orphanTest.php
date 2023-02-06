<?php
/**
 * @package openpsa.test
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace test\net\nemein\wiki\handler;

use openpsa_testcase;
use midcom_db_topic;

/**
 * OpenPSA testcase
 *
 * @package openpsa.test
 */
class orphanTest extends openpsa_testcase
{
    protected static midcom_db_topic $_topic;

    public static function setUpBeforeClass() : void
    {
        $topic_attributes = [
            'component' => 'net.nemein.wiki',
            'name' => \midcom_helper_misc::urlize(__CLASS__) . time()
        ];
        self::$_topic = self::create_class_object(midcom_db_topic::class, $topic_attributes);
    }

    public function testHandler_orphan()
    {
        $data = $this->run_handler(self::$_topic, ['orphans']);
        $this->assertEquals('orphans', $data['handler_id']);
    }
}
