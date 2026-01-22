# App 原生功能最小改動設計（2026-01-22）

## 目標
- 不改動既有 Web 架構與功能，新增可上架的 App 外殼。
- App 支援原生推播與原生地圖嵌入。
- iOS/Android 功能與網頁版一致。

## 現況重點
- Web 為 Laravel 8 + Blade + inline Vue SPA，Web 路由回傳單一 index view。
- API 走相對 /api，對 WebView 指向正式站台最安全。
- 已有 Android 專案與 Capacitor 設定，但目前 server.url 指向內網。

關鍵檔案：
- [routes/web.php](../../routes/web.php)
- [routes/api.php](../../routes/api.php)
- [resources/views/partials/vue-scripts.blade.php](../../resources/views/partials/vue-scripts.blade.php)
- [android/app/src/main/assets/capacitor.config.json](../../android/app/src/main/assets/capacitor.config.json)

## 推薦方案（最小改動）
- App 只作為 WebView 容器，載入 https://lovetennis.tw。
- App-only 原生功能（推播、地圖）由 Capacitor 插件與 JS bridge 處理。
- Web 版維持原樣，不改動既有資料流與路由。

## 原生地圖（A）
- App 內使用原生地圖（iOS Apple Maps / Android Google Maps）。
- 前端以 isNativeApp 判斷，App 才顯示「開啟地圖」或內嵌地圖區塊。
- Web 版保持地址文字與既有 UI，不影響現有頁面。

## 原生推播（Firebase FCM + APNs）
- App 取得推播權限與 FCM Token。
- 新增 API 儲存 Token（僅 App 使用），不影響 Web：
  - 路由新增於 [routes/api.php](../../routes/api.php)
- 新增資料表 device_tokens（user_id、platform、token、last_seen）。
- 通知發送加入既有通知流程（事件/訊息觸發）。

## App-only 邏輯
- 前端新增 isNativeApp 判斷（window.Capacitor）。
- App 才呼叫推播註冊與地圖顯示。
- Web 版不受影響。

## 上架需求摘要
- 隱私權政策（已有頁面）。
- 帳號刪除/停用入口（需確認現有流程）。
- 推播用途說明與權限提示。
- iOS/Android 簽章與商店帳號。

## 待確認
- Firebase 專案與金鑰尚未建立（需建立）。
- iOS 專案需建立（目前未見 ios/）。
- App 內地圖 UI 位置與互動細節。
