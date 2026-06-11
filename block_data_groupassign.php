<?php
// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

class block_data_groupassign extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_data_groupassign');
    }

    public function applicable_formats() {
        return ['course-view' => true];
    }

    public function has_config() {
        return false;
    }

    public function get_content() {
        global $DB, $COURSE, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);
        if (!has_capability('block/data_groupassign:assign', $context)) {
            $this->content->text = '';
            return $this->content;
        }

        // コース内のデータベースモジュール一覧
        $databases = $DB->get_records('data', ['course' => $COURSE->id], 'name ASC', 'id, name');
        if (empty($databases)) {
            $this->content->text = get_string('nodatabases', 'block_data_groupassign');
            return $this->content;
        }

        // コースのグルーピング一覧
        $groupings = groups_get_all_groupings($COURSE->id);

        $actionurl = new moodle_url('/blocks/data_groupassign/assign_groups.php', ['courseid' => $COURSE->id]);

        $html  = html_writer::start_tag('form', ['method' => 'post', 'action' => $actionurl]);
        $html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

        // DBモジュール選択
        $html .= html_writer::tag('label',
            get_string('selectdatabase', 'block_data_groupassign'),
            ['for' => 'dga_dataid', 'class' => 'd-block mb-1 small font-weight-bold']
        );
        $db_options = [];
        foreach ($databases as $db) {
            $db_options[$db->id] = format_string($db->name);
        }
        $html .= html_writer::select($db_options, 'dataid', '', false, [
            'id'    => 'dga_dataid',
            'class' => 'custom-select custom-select-sm mb-3 w-100',
        ]);

        // グルーピング選択（コースにグルーピングがある場合のみ表示）
        if (!empty($groupings)) {
            $html .= html_writer::tag('label',
                get_string('selectgrouping', 'block_data_groupassign'),
                ['for' => 'dga_groupingid', 'class' => 'd-block mb-1 small font-weight-bold']
            );
            $html .= html_writer::tag('p',
                get_string('selectgrouping_desc', 'block_data_groupassign'),
                ['class' => 'small text-muted mb-1']
            );
            $grouping_options = [];
            foreach ($groupings as $g) {
                $grouping_options[$g->id] = format_string($g->name);
            }
            $html .= html_writer::select($grouping_options, 'groupingid', '', [0 => get_string('nogrouping', 'block_data_groupassign')], [
                'id'    => 'dga_groupingid',
                'class' => 'custom-select custom-select-sm mb-3 w-100',
            ]);
        } else {
            // グルーピングなしの場合は hidden で 0 を送る
            $html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'groupingid', 'value' => 0]);
        }

        // 実行ボタン
        $html .= html_writer::empty_tag('input', [
            'type'  => 'submit',
            'value' => get_string('assignbutton', 'block_data_groupassign'),
            'class' => 'btn btn-primary btn-sm w-100',
        ]);

        $html .= html_writer::end_tag('form');

        $this->content->text = $html;
        return $this->content;
    }
}
