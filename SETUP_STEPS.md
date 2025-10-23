# 🚀 PASOS RÁPIDOS PARA CONFIGURAR AUTONIMUS-Q CLOUD

## ✅ YA TIENES:
- ✅ Proyecto inicializado con Git
- ✅ Rama `main` (producción)
- ✅ Rama `dev` (desarrollo)
- ✅ Todos los archivos listos

---

## 🔥 SIGUIENTE: CREAR REPOSITORIO EN GITHUB

### 1️⃣ **En GitHub:**
1. Ir a: https://github.com/new
2. **Repository name**: `autonimus-q-cloud`
3. **Description**: `🚀 Autonimus-Q Cloud - Infraestructura dual prod/dev`
4. **Public** ✅
5. **NO** inicializar con README (ya tienes código)
6. Click **"Create repository"**

### 2️⃣ **Conectar tu repositorio local:**
```bash
# Copiar la URL que te dé GitHub (algo como):
git remote add origin https://github.com/[TU_USUARIO]/autonimus-q-cloud.git

# Subir ambas ramas:
git push -u origin main
git push -u origin dev
```

### 3️⃣ **Configurar Secrets en GitHub:**
1. **Ir a:** Tu repo → Settings → Secrets and variables → Actions
2. **Agregar estos 4 secrets:**

```
FTP_SERVER: ftp.autonimuscorp.online
FTP_USERNAME: u196678264
FTP_PASSWORD: [tu_password_de_hostinger]
FTP_PORT: 21
```

---

## 🏠 SIGUIENTE: CONFIGURAR HOSTINGER

### 1️⃣ **Crear subdominio DEV:**
1. **hPanel** → Dominios → Subdominios
2. **Crear**: `dev` → Directorio: `/public_html/dev/`
3. **Resultado**: `dev.autonimuscorp.online`

### 2️⃣ **Subir archivos .env:**

**Archivo 1:** `/public_html/.env`
```env
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_CristiAndMane
DB_PASS=Manejar123
PANEL_TOKEN=atn-prod-2024-secure-key
ENVIRONMENT=production
DEBUG_MODE=false
```

**Archivo 2:** `/public_html/dev/.env`
```env
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_CristiAndMane
DB_PASS=Manejar123
PANEL_TOKEN=atn-dev-2024-testing-key
ENVIRONMENT=development
DEBUG_MODE=true
```

---

## 🚀 ¡LISTO PARA USAR!

Una vez hagas esto:
1. **Push a main** → Deploy automático a `autonimuscorp.online`
2. **Push a dev** → Deploy automático a `dev.autonimuscorp.online`

### URLs finales:
- 🔥 **Producción**: `autonimuscorp.online/panel/`
- 🛠️ **Desarrollo**: `dev.autonimuscorp.online/panel/`

**¿Necesitas ayuda con algún paso específico?**