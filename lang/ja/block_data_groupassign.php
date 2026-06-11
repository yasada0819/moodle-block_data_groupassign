<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']          = 'データベースグループ付与';
$string['data_groupassign:assign']      = 'データベースエントリにグループを付与する';
$string['data_groupassign:addinstance'] = 'データベースグループ付与ブロックを追加する';

$string['nodatabases']    = 'このコースにはデータベースモジュールがありません。';
$string['selectdatabase'] = '対象のデータベースモジュール：';
$string['assignbutton']   = 'グループ未設定のエントリに付与する';

$string['result_heading'] = 'グループ付与結果';
$string['result_target']  = '対象：{$a}';
$string['result_updated'] = '更新：{$a} 件';
$string['result_skipped'] = 'スキップ（グループ未所属）：{$a} 件';
$string['skipped_users']  = 'スキップされたユーザ（グループ未所属）';
$string['no_group_assigned'] = 'グループなし';
$string['backtocourse']   = 'コースに戻る';
$string['status']         = 'ステータス';

// プレビューページ
$string['preview_heading']       = 'プレビュー：グループ付与';
$string['preview_by_group']      = 'グループ別付与予定件数';
$string['preview_no_normal']     = '自動付与できるエントリはありません。';
$string['preview_entry_count']   = '件数';
$string['preview_entries_unit']  = ' 件';
$string['preview_will_skip']     = 'スキップ予定';
$string['preview_multi_heading'] = '⚠ 複数グループに所属しているユーザ';
$string['preview_multi_desc']    = '以下のユーザは複数のグループに所属しています。各ユーザについて付与するグループを選択してください。選択されなかったユーザはスキップされます。';
$string['preview_choose']        = '-- グループを選択 --';
$string['preview_select_group']  = '付与グループ';
$string['executebutton']         = '確定して実行する';

// グルーピング
$string['selectgrouping']              = 'グルーピング（フォールバック）：';
$string['selectgrouping_desc']         = 'DBモジュール自体にグルーピングが設定されていない場合のみ使用されます。';
$string['nogrouping']                  = '-- グルーピングなし（全グループ対象）--';
$string['grouping_from_module']        = 'モジュール設定より';
$string['grouping_from_ui']            = 'ここで選択';
$string['preview_grouping_notice']     = '使用するグルーピング：{$a}';
$string['preview_no_grouping']         = 'グルーピング未指定。コースの全グループが対象になります。';
$string['result_grouping']             = '使用したグルーピング：{$a}';
