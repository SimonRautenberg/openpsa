<?php
/**
 * @package openpsa.test
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA testcase
 *
 * @package openpsa.test
 */
class org_openpsa_products_groupTest extends openpsa_testcase
{
    public function testCRUD()
    {
        $code = 'GROUP-TEST-' . __CLASS__ . time();
        $group = new org_openpsa_products_product_group_dba();
        $group->code = $code;
        $group->_use_activitystream = false;
        $group->_use_rcs = false;

        midcom::get()->auth->request_sudo('org.openpsa.products');
        $stat = $group->create();
        $this->assertTrue($stat);
        $this->register_object($group);

        $group->title = 'TEST TITLE';
        $stat = $group->update();
        $this->assertTrue($stat);

        $this->assertEquals($group->title, 'TEST TITLE');

        $stat = $group->delete();
        $this->assertTrue($stat);

        midcom::get()->auth->drop_sudo();
    }

    public function test_get_parent()
    {
        $parentgroup = $this->create_object('org_openpsa_products_product_group_dba');
        $group = $this->create_object('org_openpsa_products_product_group_dba', ['up' => $parentgroup->id]);

        $parent = $group->get_parent();
        $this->assertEquals($parentgroup->guid, $parent->guid);
    }
}
