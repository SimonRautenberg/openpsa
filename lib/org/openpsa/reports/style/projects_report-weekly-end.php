<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$query =& $data['query_data'];

//TODO: See report start about output depending on context
if (   !isset($query['skip_html_headings'])
    || empty($query['skip_html_headings']))
{
?>
        <form method="post" action="&(prefix);csv/&(data['filename']);.csv" onSubmit="return table2csv('org_openpsa_reports_weekly_reporttable');">
            <input type="hidden" id="csvdata" name="org_openpsa_reports_csv" value="" />
            <input class="button" type="submit" value="<?php echo $_MIDCOM->i18n->get_string('download as CSV', 'org.openpsa.core'); ?>" />
        </form>
    </body>
</html>
<?php
}
?>