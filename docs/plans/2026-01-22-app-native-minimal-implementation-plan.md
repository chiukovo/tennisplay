# App 原生功能最小改動 Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** 以最小改動建立可在模擬器順暢執行的 iOS/Android App，維持 Web 功能一致，新增原生推播與原生地圖。

**Architecture:** App 作為 WebView 容器載入 https://lovetennis.tw，新增 App-only 原生能力（推播/地圖）以 Capacitor 插件與 JS bridge 整合，Web 版不受影響。

**Tech Stack:** Laravel 8, Blade + inline Vue SPA, Capacitor (Android/iOS), Firebase FCM/APNs

---

### Task 1: 基礎 App 設定與 WebView 來源更新

**Files:**
- Modify: android/app/src/main/assets/capacitor.config.json

**Step 1: 更新 server.url 為正式網域**
- 設定為 https://lovetennis.tw

**Step 2: 限制 allowNavigation**
- 僅保留正式網域與必要子網域

**Step 3: 紀錄對照測試**
- 模擬器啟動後應載入正式站台首頁

---

### Task 2: iOS 專案建立（Capacitor）

**Files:**
- Create: ios/ (Capacitor iOS 專案)

**Step 1: 初始化 iOS 平台**
- 產生 ios/ 專案並設定 appId 為 com.chiuko.tennisplay

**Step 2: 驗證 iOS 專案可啟動**
- 在模擬器載入 https://lovetennis.tw

---

### Task 3: 新增 App-only 環境偵測與橋接入口

**Files:**
- Modify: resources/views/partials/vue-scripts.blade.php

**Step 1: 新增 isNativeApp 判斷**
- 以 window.Capacitor 判斷 App 環境

**Step 2: App-only 功能入口 UI**
- 推播與地圖入口僅在 App 顯示

---

### Task 4: 推播 Token 儲存 API（後端）

**Files:**
- Create: database/migrations/xxxx_xx_xx_create_device_tokens_table.php
- Modify: routes/api.php
- Create: app/Models/DeviceToken.php
- Create: app/Http/Controllers/Api/DeviceTokenController.php

**Step 1: 建立 device_tokens 資料表**
- 欄位：user_id, platform, token, last_seen, created_at, updated_at

**Step 2: 新增 API**
- POST /api/device-tokens (auth:sanctum)
- PUT /api/device-tokens/{id} (更新 last_seen 或 token)

**Step 3: 驗證權限與輸入**
- 僅允許本人綁定自己的 token

---

### Task 5: Firebase 推播服務整合（後端）

**Files:**
- Create: app/Services/PushNotificationService.php
- Modify: config/services.php
- Modify: .env.example

**Step 1: 加入 Firebase 服務設定**
- 以環境變數存放金鑰與 projectId

**Step 2: 實作推播發送服務**
- 以 FCM HTTP v1 送出通知

**Step 3: 串接既有通知流程**
- 在事件/訊息通知流程加入推播呼叫

---

### Task 6: App 端推播註冊與上傳

**Files:**
- Modify: resources/views/partials/vue-scripts.blade.php

**Step 1: App 啟動註冊推播**
- 取得 FCM Token

**Step 2: 呼叫 API 上傳 Token**
- POST /api/device-tokens

---

### Task 7: 原生地圖嵌入

**Files:**
- Modify: resources/views/partials/vue-scripts.blade.php
- Modify: resources/views/pages/events.blade.php
- Modify: resources/views/components/modals.blade.php

**Step 1: App-only 開啟地圖按鈕**
- App 內顯示「開啟地圖」

**Step 2: 呼叫原生地圖**
- 以地址或座標開啟原生地圖

---

### Task 8: 模擬器驗證流程

**Files:**
- None

**Step 1: Android 模擬器**
- App 可載入首頁與主要功能

**Step 2: iOS 模擬器**
- App 可載入首頁與主要功能

**Step 3: 推播註冊流程**
- Token 成功上傳到後端

---

## Security Checklist
- 所有推播金鑰以環境變數儲存
- 新 API 皆套用 auth:sanctum
- Token 綁定僅限本人

---

## Notes
- Web 版維持原樣，不修改現有 API 路徑與 SPA 路由。
- 若需離線或本機資產，另行規劃（非本次最小改動範圍）。