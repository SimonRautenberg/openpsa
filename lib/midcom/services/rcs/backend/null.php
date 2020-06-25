<?php
/**
 * @author tarjei huse
 * @package midcom.services.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midcom.services.rcs
 */
class midcom_services_rcs_backend_null implements midcom_services_rcs_backend
{
    public function __construct($object, midcom_services_rcs_config $config)
    {
    }

    public function update($object, $updatemessage = null) : bool
    {
        return true;
    }

    public function get_revision($revision) : array
    {
        return [];
    }

    public function get_history() : ?midcom_services_rcs_history
    {
        return null;
    }

    public function get_diff($oldest_revision, $latest_revision) : array
    {
        return [];
    }

    public function restore_to_revision($revision) : bool
    {
        return false;
    }
}
