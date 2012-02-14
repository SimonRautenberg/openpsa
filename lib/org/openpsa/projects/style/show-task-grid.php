<?php
$grid_id = $data['view_identifier'] . '_tasks_grid';

$footer_data = array('task' => $data['l10n']->get('totals'));

$rows = array();

$total_hours = array
(
    'reported'    => 0,
    'planned'     => 0,
);

foreach ($data['tasks'] as $task)
{
    $total_hours['reported'] += $task->reportedHours;
    $total_hours['planned'] += $task->plannedHours;

    $task_url = $data['prefix'] . "task/{$task->guid}/";
    $celldata = $data['handler']->get_table_row_data($task, $data);
    $manager_card = org_openpsa_widgets_contact::get($task->manager);

    $row = array();

    $row['id'] = $task->id;
    $row['index_task'] = $task->title;
    $row['task'] = '<a href="' . $task_url . '">' . $task->title . '</a>';
    $row['index_project'] = $celldata['index_parent'];
    $row['project'] = $celldata['parent'];

    $row['index_priority'] = $task->priority;
    $row['priority'] = '';
    if (   isset($data['priority_array'])
        && array_key_exists($task->priority, $data['priority_array']))
    {
        $row['priority'] = '<span title="' . $data['l10n']->get($data['priority_array'][$task->priority]) . '">' . $task->priority . '</span>';
    }

    $row['index_customer'] = $celldata['index_customer'];
    $row['customer'] = $celldata['customer'];

    $row['manager'] = $manager_card->show_inline();
    $row['index_manager'] = preg_replace('/<span.*?class="uid".*?>.*?<\/span>/', '', $row['manager']);
    $row['index_manager'] = strip_tags($row['index_manager']);

    $row['start'] = strftime('%Y-%m-%d', $task->start);
    $row['end'] = strftime('%Y-%m-%d', $task->end);
    $row['index_reported'] = $task->reportedHours;
    $row['reported'] = round($task->reportedHours, 2);
    if ($task->plannedHours > 0)
    {
        $row['reported'] .=  ' / ' . round($task->plannedHours, 2);
    }
    $rows[] = $row;
}

$footer_data['reported'] = round($total_hours['reported'], 2) . " / " . round($total_hours['planned'], 2);

echo '<script type="text/javascript">//<![CDATA[';
echo "\nvar " . $grid_id . '_entries = ' . json_encode($rows);
echo "\n//]]></script>";
?>
<div class="org_openpsa_projects <?php echo $data['view_identifier']; ?> full-width fill-height">

<table id="&(grid_id);"></table>
<div id="p_&(grid_id);"></div>

</div>

<script type="text/javascript">

jQuery("#&(grid_id);").jqGrid({
      datatype: "local",
      data: &(grid_id);_entries,
      colNames: ['id', <?php
                 echo '"index_priority", "' . $data['l10n']->get('priority') . '",';
                 echo '"index_task", "' . $data['l10n']->get('task') . '",';
                 echo '"index_project", "' . $data['l10n']->get('project') . '",';
                 echo '"index_customer", "' . $data['l10n']->get('customer') . '",';
                 echo '"index_manager", "' . $data['l10n']->get('manager') . '",';
                 echo '"' . $data['l10n']->get('start') . '",';
                 echo '"' . $data['l10n']->get('end') . '",';
                 echo '"index_reported", "' . $data['l10n']->get('reported') . '"';
                ?>],
      colModel:[
          {name:'id', index:'id', hidden:true, key:true},
          {name:'index_priority',index:'index_priority', sorttype: "integer", hidden:true},
          {name:'priority', index: 'index_priority', width: 23, align: 'center', fixed: true},
          {name:'index_task', index:'index_task', hidden:true},
          {name:'task', index: 'index_task', width: 100, classes: 'ui-ellipsis'},
          {name:'index_project',index:'index_project', hidden: true},
          {name:'project', index: 'index_project', width: 80, classes: 'ui-ellipsis', hidden: true},
          {name:'index_customer', index: 'index_customer', hidden:true},
          {name:'customer', index: 'index_customer', width: 55, classes: 'ui-ellipsis'},
          {name:'index_manager', index: 'index_manager', hidden: true },
          {name:'manager', index: 'index_manager', width: 70, classes: 'ui-ellipsis'},
          {name:'start', index: 'start', width: 75, formatter: 'date', align: 'center', fixed: true},
          {name:'end', index: 'end', width: 75, formatter: 'date', align: 'center', fixed: true},
          {name:'index_reported', index: 'index_reported', sorttype: 'float', hidden:true},
          {name:'reported', index: 'index_reported', width: 50, align: 'right', summaryType:'sum'}
      ],
      loadonce: true,
      footerrow: true,
      rowNum: <?php echo sizeof($rows); ?>,
      scroll: 1,
      sortname: 'end',
      grouping: true,
      groupingView: {
          groupField: ['project'],
          groupColumnShow: [false],
          groupText : ['<strong>{0}</strong> ({1})'],
          groupOrder: ['asc'],
          groupSummary : [true],
          showSummaryOnHide: true
       }
    });

jQuery("#&(grid_id);").jqGrid('footerData', 'set', <?php echo json_encode($footer_data); ?>);

var &(grid_id);_grouping = "project";

jQuery("#chgrouping_&(grid_id);").change(function()
{
    var selection = $(this).val();
    if (selection)
    {
        if (selection == "clear")
        {
            jQuery("#&(grid_id);").jqGrid('groupingRemove', true);
            &(grid_id);_grouping = '';
        }
        else
        {
            &(grid_id);_grouping = selection;
            jQuery("#&(grid_id);").jqGrid('groupingGroupBy', selection);
        }
	jQuery(window).trigger('resize');
    }
});

</script>
