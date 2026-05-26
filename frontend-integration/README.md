# Frontend integration notes — connect the Laravel backend to the `academy_clean` frontend

Overview

- Place the frontend code inside this repository (recommended path: frontend/academy),
  or run it from its original folder and configure CORS / SANCTUM accordingly.

Quick steps I applied to the backend (development-friendly):

- config/cors.php: added an origin pattern that matches localhost and 127.0.0.1 on any port
- frontend-integration/.env.example: example values for APP_URL and SANCTUM_STATEFUL_DOMAINS

How to copy the frontend into the project (PowerShell):

1. Close dev servers.
2. Run from the project root:

    Copy-Item -Path "C:\Users\aghaa\Desktop\academy_clean\*" -Destination "$(Join-Path (Get-Location) 'frontend')" -Recurse -Force

3. Install and run frontend (inside frontend/artifacts/academy or workspace frontend path):

    cd frontend\artifacts\academy
    pnpm install
    pnpm dev

Backend (.env) settings to use while developing with Vite (example):
Add these to your backend .env (or update existing keys):

    APP_URL=http://127.0.0.1:8000
    SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:3000
    SESSION_DOMAIN=127.0.0.1

API usage / auth notes

- The frontend should call GET {BACKEND}/sanctum/csrf-cookie (with credentials) before login/register requests.
- All fetch requests that require cookies must include credentials: 'include' and use CORS.

Per-page mapping guidance

- I will map frontend pages to backend endpoints in frontend-integration/mapping.md.

If you want, I can now copy the frontend into this workspace for direct edits (I added a copy-frontend.ps1 helper below).
