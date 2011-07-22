/* function to parse the input and exchanges , with .
*/
function parse_input(string)
{
    search_string = ',';
    replace_string = '.';

    return string.replace(search_string , replace_string);
}

function calculate_row(id)
{
    price_unit = parse_input($("#price_per_unit_" + id).val());
    units = parse_input($("#units_" + id).val());
    //check if they are numbers
    if(   isNaN(price_unit)
       && isNaN(units))
    {
        sum = 0;
    }
    else
    {
        sum = price_unit * units;
    }
    $('#row_sum_' + id).html(sum.toFixed(2));
}

function calculate_total(table)
{
    var total = 0;
    table.find('tbody tr').each(function()
    {
        if ($(this).find('input[type="checkbox"]').is(':checked'))
        {
            total += $(this).find('.units').val() * $(this).find('.price_per_unit').val()
        }
    });

    table.find('tfoot .totals').text(total.toFixed(2));
}

function process_invoice(button, action)
{
    var id = button.parent().parent().attr('id');
    $.post(INVOICES_URL, {id: id, action: action}, function(data, status)
    {
        var parsed = jQuery.parseJSON(data);
        if (parsed.success === false)
        {
            //TODO: Error reporting
            return;
        }
        var row_data = button.closest('.ui-jqgrid-btable').getRowData(id);
        button.closest('.ui-jqgrid-btable').delRowData(id);

        var new_grid = jQuery('#' + parsed.new_status + '_invoices_grid');
        if (new_grid.length < 1)
        {
	    // Grid is not present yet, reload
	    window.location.reload();
            return;
        }

        if (new_grid.jqGrid('getGridParam', 'datatype') === 'local')
        {
            row_data.action = parsed.action;
            row_data.due = parsed.due;
            jQuery('#' + parsed.new_status + '_invoices_grid').addRowData(row_data.id, row_data, "last");
        }
        else
        {
            new_grid.trigger('reloadGrid');
        }
        $(window).trigger('resize');
        jQuery.midcom_services_uimessage_add(parsed.message);
    });
}

$(document).ready(function()
{
    $('.projects table')
        .delegate('input[type="text"]', 'change', function()
        {
            var task_id = $(this).closest('tr').attr('id').replace('task_', '');
            calculate_row(task_id);
            calculate_total($(this).closest('table'));
        })
        .delegate('input[type="checkbox"]', 'change', function()
        {
            calculate_total($(this).closest('table'));
        })
        .each(function()
        {
            calculate_total($(this));
        });
    $('.org_openpsa_invoices .ui-jqgrid-btable')
        .delegate('button.mark_sent', 'click', function()
        {
            process_invoice($(this), 'mark_sent');
        })
        .delegate('button.mark_sent_per_mail', 'click', function()
        {
            process_invoice($(this), 'send_by_mail');
        })
        .delegate('button.mark_paid', 'click', function()
        {
            process_invoice($(this), 'mark_paid');
        });

});