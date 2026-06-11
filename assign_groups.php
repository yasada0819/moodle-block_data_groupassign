<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/grouplib.php');

$courseid   = required_param('courseid',   PARAM_INT);
$dataid     = required_param('dataid',     PARAM_INT);
$mode       = optional_param('mode',       'preview', PARAM_ALPHA); // preview | execute
$groupingid = optional_param('groupingid', 0,         PARAM_INT);   // ブロックUIで選択したグルーピング

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
require_sesskey();

$context = context_course::instance($courseid);
require_capability('block/data_groupassign:assign', $context);

$datamodule = $DB->get_record('data', ['id' => $dataid, 'course' => $courseid], '*', MUST_EXIST);

$PAGE->set_url('/blocks/data_groupassign/assign_groups.php', ['courseid' => $courseid, 'dataid' => $dataid]);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('pluginname', 'block_data_groupassign'));
$PAGE->set_heading($course->fullname);

// ----------------------------------------------------------------
// グルーピング優先順位の決定
//   1. DBモジュール自体に groupingid が設定されていればそれを最優先
//   2. なければブロックUIで選んだ groupingid を使用
//   3. どちらも 0 → コース全グループ（絞り込みなし）
// ----------------------------------------------------------------
$effective_groupingid = (!empty($datamodule->groupingid))
    ? (int)$datamodule->groupingid
    : (int)$groupingid;

// グルーピング名を取得（表示用）
$effective_grouping_name = '';
if ($effective_groupingid > 0) {
    $g = $DB->get_record('groupings', ['id' => $effective_groupingid], 'id, name');
    $effective_grouping_name = $g ? format_string($g->name) : "ID:{$effective_groupingid}";
}

// ----------------------------------------------------------------
// 共通：グループ未設定エントリを解析してユーザ情報を分類する
// ----------------------------------------------------------------

/**
 * groupid=0 のエントリを解析し、以下を返す
 *
 * @param int $dataid
 * @param int $courseid
 * @param int $effective_groupingid  使用するグルーピングID（0=絞り込みなし）
 * @return array [
 *   'normal'   => [ userid => groupid, ... ],   // 1グループのみ所属
 *   'multi'    => [ userid => [groupid, ...] ],  // 複数グループ所属
 *   'nogroup'  => [ userid, ... ],               // グループ未所属
 *   'records'  => [ userid => [record_id, ...] ] // エントリIDのマップ
 * ]
 */
function dga_analyse_unassigned($dataid, $courseid, $effective_groupingid = 0) {
    global $DB;

    $unassigned = $DB->get_records('data_records', ['dataid' => $dataid, 'groupid' => 0], '', 'id, userid');

    $result = ['normal' => [], 'multi' => [], 'nogroup' => [], 'records' => []];

    foreach ($unassigned as $record) {
        $uid = $record->userid;
        $result['records'][$uid][] = $record->id;

        if (isset($result['normal'][$uid]) || isset($result['multi'][$uid]) || in_array($uid, $result['nogroup'])) {
            continue;
        }

        // $effective_groupingid を渡すことで対象グルーピング内のグループだけに絞り込む
        $groups   = groups_get_user_groups($courseid, $uid, $effective_groupingid);
        // groups_get_user_groups の戻り値: [ groupingid => [groupid, ...] ]
        // groupingid=0 キーにコース全グループ or 指定グルーピング内グループが入る
        $groupids = isset($groups[0]) ? array_values($groups[0]) : [];

        if (empty($groupids)) {
            $result['nogroup'][] = $uid;
        } elseif (count($groupids) === 1) {
            $result['normal'][$uid] = $groupids[0];
        } else {
            $result['multi'][$uid] = $groupids;
        }
    }

    return $result;
}

// ----------------------------------------------------------------
// EXECUTE モード
// ----------------------------------------------------------------
if ($mode === 'execute') {

    $analysis = dga_analyse_unassigned($dataid, $courseid, $effective_groupingid);
    $chosen   = optional_param_array('chosen_group', [], PARAM_INT);

    $count_updated = 0;
    $count_skipped = 0;
    $skipped_users = [];

    foreach ($analysis['normal'] as $uid => $groupid) {
        foreach ($analysis['records'][$uid] as $rid) {
            $DB->set_field('data_records', 'groupid', $groupid, ['id' => $rid]);
            $count_updated++;
        }
    }

    foreach ($analysis['multi'] as $uid => $groupids) {
        if (!empty($chosen[$uid]) && in_array($chosen[$uid], $groupids)) {
            foreach ($analysis['records'][$uid] as $rid) {
                $DB->set_field('data_records', 'groupid', $chosen[$uid], ['id' => $rid]);
                $count_updated++;
            }
        } else {
            $count_skipped++;
            $skipped_users[] = $uid;
        }
    }

    foreach ($analysis['nogroup'] as $uid) {
        $count_skipped++;
        $skipped_users[] = $uid;
    }

    // ---- 結果表示 ----
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('result_heading', 'block_data_groupassign'));

    $summary  = html_writer::start_div('alert alert-success');
    $summary .= html_writer::tag('p', get_string('result_target', 'block_data_groupassign', format_string($datamodule->name)));
    if ($effective_groupingid > 0) {
        $summary .= html_writer::tag('p', get_string('result_grouping', 'block_data_groupassign', $effective_grouping_name));
    }
    $summary .= html_writer::tag('p', get_string('result_updated', 'block_data_groupassign', $count_updated));
    $summary .= html_writer::tag('p', get_string('result_skipped', 'block_data_groupassign', $count_skipped));
    $summary .= html_writer::end_div();
    echo $summary;

    if (!empty($skipped_users)) {
        echo $OUTPUT->heading(get_string('skipped_users', 'block_data_groupassign'), 4);
        $rows = [];
        foreach (array_unique($skipped_users) as $uid) {
            $user = $DB->get_record('user', ['id' => $uid], 'id, firstname, lastname, username');
            if ($user) {
                $rows[] = [$user->username, fullname($user), get_string('no_group_assigned', 'block_data_groupassign')];
            }
        }
        $table       = new html_table();
        $table->head = ['username', get_string('fullname'), get_string('status')];
        $table->data = $rows;
        echo html_writer::table($table);
    }

    echo html_writer::div(
        html_writer::link(
            new moodle_url('/course/view.php', ['id' => $courseid]),
            get_string('backtocourse', 'block_data_groupassign'),
            ['class' => 'btn btn-secondary mt-3']
        )
    );

    echo $OUTPUT->footer();
    exit;
}

// ----------------------------------------------------------------
// PREVIEW モード（デフォルト）
// ----------------------------------------------------------------

$analysis      = dga_analyse_unassigned($dataid, $courseid, $effective_groupingid);
$course_groups = groups_get_all_groups($courseid);

// グループ別予定件数を集計
$group_preview = [];
foreach ($analysis['normal'] as $uid => $groupid) {
    $cnt = count($analysis['records'][$uid]);
    $group_preview[$groupid] = ($group_preview[$groupid] ?? 0) + $cnt;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('preview_heading', 'block_data_groupassign'));

// グルーピング情報バナー
if ($effective_groupingid > 0) {
    $source = (!empty($datamodule->groupingid))
        ? get_string('grouping_from_module', 'block_data_groupassign')
        : get_string('grouping_from_ui',     'block_data_groupassign');
    echo html_writer::tag('div',
        get_string('preview_grouping_notice', 'block_data_groupassign', $effective_grouping_name)
        . ' ' . html_writer::tag('span', $source, ['class' => 'badge badge-info']),
        ['class' => 'alert alert-info']
    );
} else {
    echo html_writer::tag('div',
        get_string('preview_no_grouping', 'block_data_groupassign'),
        ['class' => 'alert alert-secondary']
    );
}

$actionurl = new moodle_url('/blocks/data_groupassign/assign_groups.php');

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $actionurl]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',     'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid',    'value' => $courseid]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'dataid',      'value' => $dataid]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'groupingid',  'value' => $groupingid]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode',        'value' => 'execute']);

// ---- グループ別プレビューテーブル ----
echo $OUTPUT->heading(get_string('preview_by_group', 'block_data_groupassign'), 4);

if (empty($group_preview) && empty($analysis['nogroup'])) {
    echo html_writer::tag('p', get_string('preview_no_normal', 'block_data_groupassign'), ['class' => 'text-muted']);
} else {
    $rows = [];
    foreach ($group_preview as $gid => $cnt) {
        $gname  = isset($course_groups[$gid]) ? format_string($course_groups[$gid]->name) : "ID:{$gid}";
        $rows[] = [$gname, $cnt . get_string('preview_entries_unit', 'block_data_groupassign')];
    }
    if (!empty($analysis['nogroup'])) {
        $rows[] = [
            html_writer::tag('span', get_string('no_group_assigned', 'block_data_groupassign'), ['class' => 'text-muted']),
            count($analysis['nogroup']) . get_string('preview_entries_unit', 'block_data_groupassign')
              . ' ' . html_writer::tag('span', get_string('preview_will_skip', 'block_data_groupassign'), ['class' => 'badge badge-secondary'])
        ];
    }
    $table       = new html_table();
    $table->head = [get_string('group'), get_string('preview_entry_count', 'block_data_groupassign')];
    $table->data = $rows;
    echo html_writer::table($table);
}

// ---- 複数グループ所属ユーザ ----
if (!empty($analysis['multi'])) {
    echo $OUTPUT->heading(get_string('preview_multi_heading', 'block_data_groupassign'), 4);
    echo html_writer::tag('p',
        get_string('preview_multi_desc', 'block_data_groupassign'),
        ['class' => 'alert alert-warning']
    );

    $rows = [];
    foreach ($analysis['multi'] as $uid => $groupids) {
        $user = $DB->get_record('user', ['id' => $uid], 'id, firstname, lastname, username');
        if (!$user) continue;

        $options = [];
        foreach ($groupids as $gid) {
            $gname           = isset($course_groups[$gid]) ? format_string($course_groups[$gid]->name) : "ID:{$gid}";
            $options[$gid]   = $gname;
        }
        $select = html_writer::select(
            $options,
            "chosen_group[{$uid}]",
            '',
            ['' => get_string('preview_choose', 'block_data_groupassign')],
            ['class' => 'custom-select custom-select-sm']
        );

        $entry_count = count($analysis['records'][$uid]);
        $rows[] = [
            $user->username,
            fullname($user),
            $entry_count . get_string('preview_entries_unit', 'block_data_groupassign'),
            $select
        ];
    }

    $table       = new html_table();
    $table->head = [
        'username',
        get_string('fullname'),
        get_string('preview_entry_count',  'block_data_groupassign'),
        get_string('preview_select_group', 'block_data_groupassign')
    ];
    $table->data = $rows;
    echo html_writer::table($table);
}

// ---- 実行・キャンセルボタン ----
echo html_writer::start_div('mt-3');
echo html_writer::empty_tag('input', [
    'type'  => 'submit',
    'value' => get_string('executebutton', 'block_data_groupassign'),
    'class' => 'btn btn-primary mr-2',
]);
echo html_writer::link(
    new moodle_url('/course/view.php', ['id' => $courseid]),
    get_string('cancel'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

echo html_writer::end_tag('form');
echo $OUTPUT->footer();
