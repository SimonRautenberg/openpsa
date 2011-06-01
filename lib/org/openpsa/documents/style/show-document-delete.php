<?php
$view = $data['document_dm'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <div class="area">
        <h2><?php echo midcom::get('i18n')->get_string('confirm delete', 'org.openpsa.core'); ?></h2>
        <p><?php echo midcom::get('i18n')->get_string('use the buttons below or in toolbar', 'org.openpsa.core'); ?></p>
        <form id="org_openpsa_documents_document_deleteform" method="post">
            <input type="hidden" name="org_openpsa_documents_deleteok" value="1" />
            <input type="submit" class="button delete" value="<?php echo $data['l10n_midcom']->get('delete'); ?>" />
            <input type="button" class="button cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" onclick="window.location='<?php echo $prefix . 'document/' . $data['document']->guid . '/'; ?>'" />
        </form>
    </div>
</div>