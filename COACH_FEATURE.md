# 找教練專區功能開發進度

> 最後更新：2026-01-19 10:51

## 功能概述

讓網球教練可以標記自己為「教練」，在專屬頁面展示教練卡，並提供詳細的教學資訊供學員搜尋。

---

## 開發進度

| Phase | 名稱 | 狀態 | 說明 |
|-------|------|------|------|
| 1 | 後端 | ✅ 完成 | Migration、Model、API |
| 2 | 個人設定表單 | ⏳ 待開發 | 教練資料填寫 UI |
| 3 | 卡片與頁面 | ⏳ 待開發 | 教練卡片主題、專區頁面 |
| 4 | 導航整合 | ⏳ 待開發 | 底部導航新增入口 |

---

## Phase 1 已完成項目

### 1. Migration
- **檔案**：`database/migrations/2026_01_19_110000_add_coach_fields_to_players.php`
- **新增欄位**：
  - `is_coach` (boolean) - 是否為教練
  - `coach_hourly_rate` (string) - 時薪或「私訊詢價」
  - `coach_experience` (string) - 教學年資
  - `coach_tags` (json) - 專長標籤陣列
  - `coach_locations` (text) - 教學場地
  - `coach_certification` (string) - 證照
  - `coach_available_times` (text) - 可教學時段

### 2. Player Model 更新
- **檔案**：`app/Models/Player.php`
- **變更**：
  - 新增欄位到 `$fillable`
  - 新增 `$casts`：`is_coach => boolean`、`coach_tags => array`
  - 新增 Scope：`scopeCoachesOnly()`、`scopeWithCoachTag()`

### 3. API 端點
- **檔案**：`app/Http/Controllers/Api/PlayerController.php`
- **新增方法**：`coaches(Request $request)`
- **路由**：`GET /api/coaches?region=&tag=&search=`

### 4. 前端常數
- **檔案**：`resources/views/partials/vue/constants.blade.php`
- **新增**：`COACH_TAGS` 陣列（10 個專長標籤）

---

## Phase 2 待開發項目

### 個人設定表單（手機優先設計）
- [ ] 「我是教練」開關
- [ ] TAG 點選式多選（一鍵選擇）
- [ ] 時薪快捷按鈕 + 自訂輸入
- [ ] 場地、證照、時段等欄位
- [ ] 儲存到後端

---

## Phase 3 待開發項目

### 教練卡片主題
- [ ] 新增 `coach` 主題（金色邊框）
- [ ] 右上角顯示「⭐ 教練」徽章
- [ ] 卡片底部顯示時薪與 TAG

### 教練專區頁面
- [ ] `view === 'coaches'` 視圖
- [ ] 複用 `player-card` 組件
- [ ] 篩選器：地區、專長標籤

---

## Phase 4 待開發項目

### 導航整合
- [ ] 底部導航新增「找教練」Tab
- [ ] 教練同時顯示在球友列表（帶徽章）

---

## 執行 Migration 指令

```bash
php artisan migrate
```

---

## 相關檔案清單

```
database/migrations/2026_01_19_110000_add_coach_fields_to_players.php  [NEW]
app/Models/Player.php                                                   [MODIFIED]
app/Http/Controllers/Api/PlayerController.php                          [MODIFIED]
routes/api.php                                                          [MODIFIED]
resources/views/partials/vue/constants.blade.php                        [MODIFIED]
```
