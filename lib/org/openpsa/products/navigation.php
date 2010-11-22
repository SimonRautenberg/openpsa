<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 24525 2009-12-21 17:28:45Z flack $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * o.o.products NAP interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves()
    {
        $leaves = array ();

        if (!$this->_config->get('display_navigation'))
        {
            return $leaves;
        }

        // Get the configured root group for the navigation
        $leaves = org_openpsa_products_navigation::get_product_group_navigation($this->_config->get('root_group'));

        return $leaves;
    }

    /**
     * Get one level of navigation
     *
     * @access public
     * @static
     * @return Array containing navigation data
     */
    static function get_product_group_navigation($id)
    {
        // Initialize the array
        $leaves = array();

        if (mgd_is_guid($id))
        {
            $group = org_openpsa_products_product_group_dba::get_cached($id);

            // Stop silently
            if (!$group->guid)
            {
                return $leaves;
            }

            $id = $group->id;
        }

        // Initialize the query builder
        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $id);
        $qb->add_order('metadata.score', 'DESC');

        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute();

        // Get the properties of each group
        foreach ($groups as $group)
        {
            $leaves[$group->id] = array
            (
                MIDCOM_NAV_URL => ($group->code) ? "{$group->code}/" : "{$group->guid}/",
                MIDCOM_NAV_NAME => $group->title,
                MIDCOM_NAV_GUID => $group->guid,
                MIDCOM_NAV_OBJECT => $group,
                MIDCOM_NAV_NOENTRY => (bool) $group->metadata->navnoentry,
            );
        }

        return $leaves;
    }

    /**
     * List recursively the groups
     *
     * @access public
     * @static
     * @param mixed $id       ID or GUID of the product group to start from
     * @param mixed $stopper  ID or GUID of the product group that should be the last to parse
     * @return Array     Containing arrays of navigation data for each level
     */
    static function get_product_tree($id, $stopper = 0)
    {
        // Initialize the return data
        $levels = array();

        // Trial and error: try first if the ID is of a product
        $product = new org_openpsa_products_product_dba($id);

        // If the request was for a product, change the request ID
        if (   !empty($product)
            && $product->guid)
        {
            $id = $product->productGroup;
        }

        $group = new org_openpsa_products_product_group_dba($id);

        // Return an empty array if not able to get the product group
        if (   empty($group)
            || !$group->guid)
        {
            return $levels;
        }

        // Get level at a time
        while($group->guid)
        {
            $levels[] = org_openpsa_products_navigation::get_product_group_navigation($group->id);

            // Break to the requested level (probably the root group of the products content topic)
            if (   $group->id === $stopper
                || $group->guid === $stopper)
            {
                break;
            }
            $group = new org_openpsa_products_product_group_dba($group->up);
        }

        // Reverse the array to start from the root and continue upwards
        $levels = array_reverse($levels);

        return $levels;
    }
}
?>