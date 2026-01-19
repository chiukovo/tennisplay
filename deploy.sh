#!/bin/bash

# =============================================================================
# LoveTennis 部署一鍵更新腳本 (Auto-Deploy Script)
# 使用方式: sudo ./deploy.sh [選項]
# 選項:
#   --full    完整更新 (含 composer, npm install, build, migrate)
#   --quick   快速更新 (僅 git pull + npm build)
#   --backend 後端更新 (git pull + composer + migrate，不編譯前端)
# =============================================================================

set -e  # 遇到錯誤立即停止

# 顏色定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 專案路徑 (自動抓取目前路徑)
PROJECT_DIR="$(pwd)"
WEB_USER="nginx"  # Ubuntu/Debian 常用 www-data, CentOS 常用 nginx 或 apache

# 輸出函式
info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# 檢查環境
check_env() {
    if [ ! -f "$PROJECT_DIR/artisan" ]; then
        error "找不到 Laravel 專案，請在專案根目錄執行此腳本。"
    fi
    info "工作目錄: $PROJECT_DIR"
}


# 拉取最新程式碼
pull_code() {
    info "拉取最新程式碼 (git pull)..."
    git pull origin main || git pull
    success "程式碼更新完成"
}

# 安裝 Composer 依賴
install_composer() {
    info "安裝 Composer 依賴..."
    COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --no-interaction
    success "Composer 依賴安裝完成"
}

# 安裝 NPM 依賴
install_npm() {
    info "安裝 NPM 依賴..."
    npm install
    success "NPM 依賴安裝完成"
}

# 編譯前端資源 (LoveTennis 專用)
build_frontend() {
    info "編譯前端資源 (Production)..."
    # 使用 production 編譯以優化效能
    npm run production
    success "前端編譯完成"
}

# 執行資料庫遷移
run_migrations() {
    info "執行資料庫遷移與初始化..."
    php artisan migrate --force
    # 初始化即時揪球房間 (使用 updateOrCreate，重複執行也安全)
    php artisan db:seed --class=InstantRoomSeeder --force
    success "資料庫處理完成"
}

# 快取優化
optimize_laravel() {
    info "清除並重新建立快取..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    success "Laravel 優化完成"
}

# 設定權限 (確保 Web Server 有權限寫入)
set_permissions() {
    info "設定路徑權限 (storage, bootstrap/cache)..."
    chown -R $WEB_USER:$WEB_USER storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    success "權限設定完成"
}

# 重啟關鍵服務 (WebSocket & Queues)
restart_services() {
    info "重啟隊列與相關服務 (Supervisor)..."
    
    # 僅使用 supervisorctl 重啟所有服務，這會包含 Laravel Queue 與 Echo Server (若有加入)
    if command -v supervisorctl >/dev/null 2>&1; then
        supervisorctl restart all
        success "Supervisor 服務已全數重啟"
    else
        warning "未發現 supervisorctl 命令，請手動檢查服務狀態"
    fi
}

# --- 模式邏輯 ---

full_update() {
    info "========== 開始完整部署模式 =========="
    pull_code
    run_migrations
    install_composer
    install_npm
    build_frontend
    optimize_laravel
    set_permissions
    restart_services
    success "========== 部署完成，站點持續運作中 =========="
}

quick_update() {
    info "========== 開始快速更新模式 (僅前端) =========="
    pull_code
    build_frontend
    optimize_laravel
    restart_services
    success "========== 快速更新完成 =========="
}

backend_update() {
    info "========== 開始後端更新模式 =========="
    pull_code
    install_composer
    run_migrations
    optimize_laravel
    set_permissions
    restart_services
    success "========== 後端更新完成 =========="
}

# 顯示使用說明
show_help() {
    echo -e "${YELLOW}LoveTennis 專屬部署腳本${NC}"
    echo "使用方式: sudo ./deploy.sh [選項]"
    echo ""
    echo "選項:"
    echo "  --full     完整更新 (代碼+依賴+前端+遷移)"
    echo "  --quick    快速更新 (代碼+前端，無遷移)"
    echo "  --backend  後端更新 (代碼+依賴+遷移，不編譯前端)"
    echo "  --help     顯示此說明"
}

# 主執行邏輯
main() {
    check_env

    case "${1:-}" in
        --full)
            full_update
            ;;
        --quick)
            quick_update
            ;;
        --backend)
            backend_update
            ;;
        --help|-h)
            show_help
            ;;
        *)
            warning "未指定模式，系統將在 3 秒後自動執行 [完整更新]..."
            sleep 3
            full_update
            ;;
    esac
}

main "$@"
