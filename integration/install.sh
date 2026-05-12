#!/bin/bash

# ============================================================
#  Staffing2Earn — Script d'intégration complet
#  Lance depuis la RACINE de ton projet Laravel :
#  bash install.sh
# ============================================================

set -e
ROOT="$(pwd)"
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${GREEN}[OK]${NC} $1"; }
info() { echo -e "${CYAN}[INFO]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
err()  { echo -e "${RED}[ERR]${NC} $1"; exit 1; }

[ -f "$ROOT/artisan" ] || err "Lance ce script depuis la racine de ton projet Laravel."

info "=== Staffing2Earn — Intégration des améliorations ==="

# ------------------------------------------------------------
# 1. Créer les dossiers manquants
# ------------------------------------------------------------
info "Création des dossiers..."
mkdir -p "$ROOT/app/Providers"
mkdir -p "$ROOT/app/Http/Responses"
mkdir -p "$ROOT/app/Livewire/Candidate"
mkdir -p "$ROOT/app/Services"
log "Dossiers créés"

# ------------------------------------------------------------
# 2. Copier les fichiers générés (placés à côté de ce script)
# ------------------------------------------------------------
DEST="$ROOT"
SRC="$(dirname "$0")/files"

copy_file() {
    local src="$SRC/$1"
    local dst="$DEST/$2"
    mkdir -p "$(dirname "$dst")"
    cp "$src" "$dst"
    log "Copié : $2"
}

info "Copie des fichiers..."

copy_file "AppServiceProvider.php"          "app/Providers/AppServiceProvider.php"
copy_file "LogoutResponse.php"              "app/Http/Responses/LogoutResponse.php"
copy_file "NotificationService.php"         "app/Services/NotificationService.php"
copy_file "CandidateService.php"            "app/Services/CandidateService.php"
copy_file "DashboardComponent.php"          "app/Livewire/Candidate/DashboardComponent.php"
copy_file "TakeTestComponent.php"           "app/Livewire/Candidate/TakeTestComponent.php"
copy_file "AuthController.php"              "app/Http/Controllers/AuthController.php"
copy_file "CandidateMiddleware.php"         "app/Http/Middleware/CandidateMiddleware.php"
copy_file "AdminPanelProvider.php"          "app/Providers/Filament/AdminPanelProvider.php"
copy_file "CandidatePanelProvider.php"      "app/Providers/Filament/CandidatePanelProvider.php"
copy_file "providers.php"                   "bootstrap/providers.php"
copy_file "web.php"                         "routes/web.php"
copy_file "ApplicationProgress.php"         "app/Models/ApplicationProgress.php"
copy_file "Test.php"                        "app/Models/Test.php"
copy_file "Temoignage.php"                  "app/Models/Temoignage.php"

# ------------------------------------------------------------
# 3. Vider les caches Laravel
# ------------------------------------------------------------
info "Nettoyage des caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
log "Caches vidés"

# ------------------------------------------------------------
# 4. Vérification finale
# ------------------------------------------------------------
info "Vérification de la syntaxe PHP..."
ERRORS=0
for file in \
    app/Providers/AppServiceProvider.php \
    app/Http/Responses/LogoutResponse.php \
    app/Services/NotificationService.php \
    app/Services/CandidateService.php \
    app/Livewire/Candidate/DashboardComponent.php \
    app/Livewire/Candidate/TakeTestComponent.php \
    app/Http/Controllers/AuthController.php \
    app/Http/Middleware/CandidateMiddleware.php \
    app/Providers/Filament/AdminPanelProvider.php \
    app/Providers/Filament/CandidatePanelProvider.php \
    bootstrap/providers.php \
    routes/web.php \
    app/Models/ApplicationProgress.php \
    app/Models/Test.php \
    app/Models/Temoignage.php
do
    if php -l "$ROOT/$file" 2>&1 | grep -q "Parse error"; then
        err "Erreur syntaxe dans : $file"
        ERRORS=$((ERRORS+1))
    else
        log "Syntaxe OK : $file"
    fi
done

[ $ERRORS -eq 0 ] && log "=== Intégration terminée avec succès ===" || err "Des erreurs de syntaxe ont été détectées."
