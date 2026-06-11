<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']          = 'Database Group Assign';
$string['data_groupassign:assign']      = 'Assign groups to database entries';
$string['data_groupassign:addinstance'] = 'Add a Database Group Assign block';

$string['nodatabases']    = 'No database modules found in this course.';
$string['selectdatabase'] = 'Target database module:';
$string['assignbutton']   = 'Assign groups to unassigned entries';

$string['result_heading'] = 'Group assignment result';
$string['result_target']  = 'Target: {$a}';
$string['result_updated'] = 'Updated: {$a} entries';
$string['result_skipped'] = 'Skipped (no group): {$a} entries';
$string['skipped_users']  = 'Skipped users (not belonging to any group)';
$string['no_group_assigned'] = 'No group';
$string['backtocourse']   = 'Back to course';
$string['status']         = 'Status';

// Preview page
$string['preview_heading']       = 'Preview: Group Assignment';
$string['preview_by_group']      = 'Entries to be assigned by group';
$string['preview_no_normal']     = 'No entries to assign automatically.';
$string['preview_entry_count']   = 'Entry count';
$string['preview_entries_unit']  = ' entries';
$string['preview_will_skip']     = 'will be skipped';
$string['preview_multi_heading'] = '⚠ Users belonging to multiple groups';
$string['preview_multi_desc']    = 'The following users belong to multiple groups. Please select which group to assign for each user. Users with no selection will be skipped.';
$string['preview_choose']        = '-- Select group --';
$string['preview_select_group']  = 'Assign to group';
$string['executebutton']         = 'Execute assignment';

// Grouping
$string['selectgrouping']              = 'Grouping (fallback):';
$string['selectgrouping_desc']         = 'Used only when the database module has no grouping set.';
$string['nogrouping']                  = '-- No grouping (all groups) --';
$string['grouping_from_module']        = 'Set on module';
$string['grouping_from_ui']            = 'Selected here';
$string['preview_grouping_notice']     = 'Grouping in use: {$a}';
$string['preview_no_grouping']         = 'No grouping specified. All course groups will be used.';
$string['result_grouping']             = 'Grouping used: {$a}';
