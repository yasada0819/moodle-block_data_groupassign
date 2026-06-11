# block_data_groupassign

Moodle block plugin to assign group IDs to unassigned entries in a Database activity.

**日本語の説明は[こちら](#日本語)。**

---

## Overview

When entries are bulk-imported into a Moodle Database activity via CSV, the `groupid` field is not populated — even if the course uses Separate Groups mode. This block allows teachers to assign group IDs to those unassigned entries in a few clicks, based on each user's group membership.

### Key features

- Dropdown to select the target Database activity
- Optional grouping selector (fallback when the module has no grouping set)
- **Grouping priority**: module-level grouping → UI selection → all course groups
- Preview screen showing how many entries will be assigned per group
- Detection of users belonging to multiple groups, with a per-user group selector
- Safe execution: only `groupid = 0` entries are touched; already-assigned entries are never overwritten
- Result screen with skip report for users with no group membership

---

## Requirements

| Item | Version |
|------|---------|
| Moodle | 4.0 or later (tested on 4.5 / 5.0) |
| PHP | 7.4 or later |

---

## Installation

1. Download or clone this repository into `<moodleroot>/blocks/data_groupassign/`
2. Log in as an administrator and navigate to **Site administration → Notifications**
3. Follow the on-screen upgrade steps

---

## Usage

### 1. Add the block to a course

In editing mode, add the **Database Group Assign** block to any course page.

### 2. Select a Database activity and (optionally) a grouping

The block displays all Database activities in the course. If the course has groupings, a second dropdown lets you select one as a fallback.

> **Grouping priority**
> 1. Grouping set on the Database module itself (highest priority)
> 2. Grouping selected in the block UI
> 3. No grouping — all course groups are considered

### 3. Click "Assign groups to unassigned entries"

A **preview screen** appears showing:

- Number of entries to be assigned, broken down by group
- Users belonging to multiple groups (within the effective grouping) — you can choose which group to assign for each

### 4. Confirm and execute

Click **Execute assignment**. A result screen shows how many entries were updated and lists any skipped users.

---

## Permissions

| Capability | Default roles |
|---|---|
| `block/data_groupassign:assign` | Manager, Editing teacher |
| `block/data_groupassign:addinstance` | Manager, Editing teacher |

---

## File structure

```
block_data_groupassign/
├── block_data_groupassign.php   # Block definition and UI
├── assign_groups.php            # Preview and execute logic
├── version.php
├── db/
│   └── access.php               # Capability definitions
└── lang/
    ├── en/block_data_groupassign.php
    └── ja/block_data_groupassign.php
```

---

## Notes & limitations

- **1 user = 1 group** is the assumed happy path. Multi-group users are handled via the preview screen selector.
- CSV import itself is handled by the standard `mod/data` importer. This block is a post-import cleanup tool.
- If a user has no group membership at all (within the effective grouping), their entries are skipped and reported.

---

## License

GNU GPL v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html).

---

---

## 日本語

### 概要

MoodleのデータベースモジュールにCSVでエントリを一括インポートしても、分離グループモードのグループ情報（`groupid`）は付与されません。このブロックプラグインを使うと、インポート済みのエントリに対して、各ユーザのグループ所属を元にグループIDをまとめて付与できます。

### 主な機能

- 対象のデータベースモジュールをドロップダウンで選択
- グルーピングの選択（モジュール側に設定がない場合のフォールバック）
- **グルーピング優先順位**：モジュール設定 → UI選択 → コース全グループ
- グループ別付与予定件数のプレビュー画面
- 複数グループ所属ユーザの検出と、ユーザごとのグループ選択UI
- 安全な実行：`groupid = 0` のエントリのみ対象（付与済みエントリは変更しない）
- グループ未所属ユーザのスキップレポート表示

### 動作要件

| 項目 | バージョン |
|------|-----------|
| Moodle | 4.0以上（4.5 / 5.0で動作確認）|
| PHP | 7.4以上 |

### インストール

1. このリポジトリを `<Moodleルート>/blocks/data_groupassign/` に配置
2. 管理者でログインし、**サイト管理 → 通知** を開く
3. 画面の指示に従ってインストール

### 使い方

1. コースの編集モードで **データベースグループ付与** ブロックを追加
2. 対象のデータベースモジュールを選択（コースにグルーピングがある場合は必要に応じて選択）
3. 「グループ未設定のエントリに付与する」をクリック
4. プレビュー画面でグループ別件数・複数グループ所属ユーザを確認
5. 「確定して実行する」をクリック → 結果を確認

### 備考・制限事項

- **1学生＝1グループ**が基本想定です。複数グループ所属ユーザはプレビュー画面で手動選択できます。
- CSVインポート自体は標準の `mod/data` インポーターを使用します。このブロックはインポート後のグループ付与ツールです。
- 対象グルーピング内にグループ所属がないユーザはスキップされ、結果画面で一覧表示されます。
