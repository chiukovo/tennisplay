# iOS (Capacitor)

此資料夾為 iOS 平台輸出目錄。

本機尚未實際執行 `npx cap add ios`（此環境未啟用 Capacitor CLI / Xcode），因此目前僅建立目錄並保留後續初始化指引。

## 後續初始化步驟（在具備 Xcode 的 macOS 環境）
1. 安裝相依套件：`npm install`
2. 初始化 iOS 平台：`npx cap add ios`
3. 同步設定：`npx cap sync ios`
4. 以 Xcode 開啟：`npx cap open ios`

## 目標
- AppId：`com.chiuko.tennisplay`
- 啟動後 WebView 載入：`https://lovetennis.tw`
