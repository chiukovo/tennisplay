# LINE 官方好友檢查功能實作說明

## 功能概述

當用戶透過 LINE 登入後，系統會自動檢查該用戶是否已加入 LoveTennis 官方 LINE 好友。如果未加入，會在適當時機顯示加好友提示彈窗。

## 實作細節

### 1. 資料庫變更

新增 `is_line_friend` 欄位到 `users` 資料表：
- **類型**: `boolean`
- **預設值**: `false`
- **位置**: `line_picture_url` 之後

Migration 檔案：`database/migrations/2026_02_01_221006_add_is_line_friend_to_users_table.php`

### 2. 後端 API 變更

#### AuthController 新增功能

**新增 `checkLineFriendship()` 私有方法**：
- 使用 LINE Messaging API 的 `/friendship/v1/status` endpoint
- 需要 `LINE_MESSAGE_TOKEN`（Messaging API Channel Access Token）
- 回傳 `true` 或 `false`

**登入流程整合**：
- 在 `lineCallback()` 方法中，登入時檢查好友狀態
- 在 `lineNativeLogin()` 方法中，原生登入時檢查好友狀態
- 將 `is_line_friend` 欄位儲存到資料庫
- 將 `is_line_friend` 包含在回傳給前端的用戶資料中

### 3. 前端變更

#### useAuth Composable 修改

**新增參數**：
- 接收 `showLinePromo` ref 作為參數

**登入成功檢查**：
- 在登入成功後（`checkAuth` 方法中），檢查 `currentUser.is_line_friend`
- 若為 `false`，顯示 LINE 好友提示彈窗

**頁面載入檢查**：
- 每次載入頁面時，檢查已登入用戶的好友狀態
- 使用 `localStorage` 記錄上次提示時間，避免過度騷擾用戶
- **防騷擾機制**：僅在距離上次提示超過 7 天時才再次顯示彈窗
- 延遲 2 秒顯示，避免干擾用戶體驗

### 4. 彈窗 UI

已存在的 LINE 好友提示彈窗（`showLinePromo`）：
- **樣式**: `fixed inset-0 z-[500] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md`
- **內容**: QR Code、LINE ID (@344epiuj)、立即加入好友按鈕
- **位置**: `resources/views/index.blade.php` 第 275 行

## 使用流程

1. **用戶登入**
   - 用戶透過 LINE Login 登入
   - 後端調用 `checkLineFriendship()` 檢查好友狀態
   - 將狀態儲存至 `users.is_line_friend` 欄位

2. **首次登入**
   - 如果不是好友，立即顯示加好友彈窗
   - 重整頁面跳轉首頁

3. **後續登入**
   - 每次頁面載入時檢查好友狀態
   - 若非好友且距離上次提示超過 7 天，延遲 2 秒顯示彈窗
   - 用戶可點擊關閉按鈕隱藏彈窗

## 重要注意事項

### LINE API 設定

1. **Channel 類型**：
   - LINE Login Channel: 用於用戶登入
   - LINE Messaging API Channel: 用於好友狀態檢查

2. **環境變數配置**：
   ```env
   LINE_CHANNEL_ID=2008839076              # LINE Login Channel ID
   LINE_CHANNEL_SECRET=...                 # LINE Login Channel Secret
   LINE_MESSAGE_TOKEN=...                  # Messaging API Channel Access Token
   ```

3. **Channel 關聯**：
   - 如果 LINE Login 和 Messaging API 是不同的 Channel，需要在 LINE Developers Console 中設定「Linked OA (Official Account)」
   - 連結方式：LINE Login Channel 設定頁 → Link a bot → 選擇對應的 Messaging API Channel

### API Endpoint 說明

**LINE Friendship Status API**：
```
GET https://api.line.me/friendship/v1/status?userId={lineUserId}
Header: Authorization: Bearer {MESSAGE_TOKEN}

Response:
{
  "friendFlag": true/false
}
```

## 測試建議

1. **未加好友用戶**：
   - 登入後應立即看到彈窗
   - 關閉彈窗後 7 天內不會再次顯示

2. **已加好友用戶**：
   - 登入後不會顯示彈窗
   - `users.is_line_friend` 應為 `true`

3. **好友狀態更新**：
   - 用戶加入好友後再次登入，狀態應更新為 `true`
   - 用戶封鎖官方帳號後再次登入，狀態應更新為 `false`

## Webhook 整合建議（選配）

為了即時更新好友狀態，可以設定 LINE Webhook 監聽以下事件：

1. **Follow Event**：用戶加入好友時
2. **Unfollow Event**：用戶封鎖/刪除好友時

在 `AuthController::handleWebhook()` 中處理這些事件並更新 `users.is_line_friend` 欄位。

## 檔案清單

### 新增/修改的檔案：
- ✅ `database/migrations/2026_02_01_221006_add_is_line_friend_to_users_table.php` (新增)
- ✅ `app/Models/User.php` (修改 $fillable)
- ✅ `app/Http/Controllers/Api/AuthController.php` (新增檢查方法並整合)
- ✅ `resources/views/partials/vue/composables/useAuth.blade.php` (新增彈窗邏輯)
- ✅ `resources/views/partials/vue-scripts.blade.php` (傳遞參數)
- ✅ `resources/views/index.blade.php` (彈窗 UI 已存在)

## 完成狀態

✅ 資料庫 migration 已執行
✅ 後端 API 已實作好友檢查
✅ 前端登入流程已整合
✅ 防騷擾機制已實作（7 天提示一次）
✅ 彈窗 UI 已存在並可重用

---

**實作日期**: 2026-02-01  
**版本**: v1.0
