<?php
/**
 * @package org.openpsa.expenses
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

use midcom\datamanager\schemadb;

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package org.openpsa.expenses
 */
class org_openpsa_expenses_viewer extends midcom_baseclasses_components_request
{
    /**
     * Populates the node toolbar depending on the user's rights.
     */
    public function populate_view_toolbar($prefix = '', $suffix = '')
    {
        $schemadb = schemadb::from_path($this->_config->get('schemadb_hours'));
        $workflow = $this->get_workflow('datamanager');
        foreach ($schemadb->all() as $name => $schema) {
            $create_url = "hours/create/{$prefix}{$name}/{$suffix}";
            $this->_view_toolbar->add_item($workflow->get_button($create_url, [
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($schema->get('description'))),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
            ]));
        }
    }

    public function _on_handle($handler, array $args)
    {
        midcom::get()->auth->require_admin_user();
    }

    /**
     * Apply user filters to hour lists
     *
     * @param midcom_core_query $query The query object to work on
     */
    public function add_list_filter(midcom_core_query $query, $add_time_filter = false)
    {
        $qf = new org_openpsa_core_queryfilter('org_openpsa_expenses_list');
        $person_filter = new org_openpsa_core_filter_multiselect('person');
        $person_filter->set_callback([$this, 'get_person_options']);
        $person_filter->set_label($this->_l10n->get("choose user"));
        $qf->add_filter($person_filter);

        if ($add_time_filter) {
            $date_filter = new org_openpsa_core_filter_timeframe('date');
            $date_filter->set_label($this->_l10n->get("timeframe"));
            $qf->add_filter($date_filter);
        }
        $qf->apply_filters($query);
        $this->_request_data["qf"] = $qf;
    }

    /**
     * List options for the person filter
     */
    public function get_person_options()
    {
        $qb = midcom_db_person::new_query_builder();
        midcom_core_account::add_username_constraint($qb, '<>', '');

        $person_array = [];
        foreach ($qb->execute() as $person) {
            $person_array[$person->id] = $person->get_label();
        }
        return $person_array;
    }
}
