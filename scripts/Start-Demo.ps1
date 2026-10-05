$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$demoDatabase = Join-Path $projectRoot 'database\demo.sqlite'
$demoStorage = Join-Path $projectRoot 'storage\app\demo-private'
$cachedConfig = Join-Path $projectRoot 'bootstrap\cache\config.php'

if (-not (Test-Path (Join-Path $projectRoot 'vendor\autoload.php'))) {
    throw 'Les dépendances Composer sont absentes. Lancez composer install avant la démonstration.'
}

if (-not (Test-Path (Join-Path $projectRoot '.env'))) {
    throw 'Le fichier .env est absent. Configurez l’application avant de démarrer la démonstration.'
}

if (Test-Path $cachedConfig) {
    throw 'La configuration Laravel est en cache. Exécutez php artisan config:clear, puis relancez ce script.'
}

Set-Location $projectRoot

if (-not (Test-Path $demoDatabase)) {
    New-Item -ItemType File -Path $demoDatabase | Out-Null
}

$env:APP_ENV = 'local'
$env:APP_DEBUG = 'false'
$env:APP_URL = 'http://127.0.0.1:8001'
$env:APP_NAME = 'Studio Noroit Demo'
$env:SESSION_COOKIE = 'studio-noroit-demo-session'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = $demoDatabase
$env:DB_URL = ''
$env:FILESYSTEM_LOCAL_ROOT = $demoStorage
$env:QUEUE_CONNECTION = 'sync'

php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    throw 'Les migrations de la base de démonstration ont échoué.'
}

php artisan db:seed --class=Database\Seeders\DemoDataSeeder --force
if ($LASTEXITCODE -ne 0) {
    throw 'La création des données de démonstration a échoué.'
}

php artisan serve --host=127.0.0.1 --port=8001
