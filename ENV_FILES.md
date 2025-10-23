# 📁 ARCHIVOS .ENV PARA HOSTINGER

## 🔥 ARCHIVO 1: /public_html/.env (PRODUCCIÓN)

```env
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_autonimus_adm
DB_PASS=L4vid4esundr4m4.
PANEL_TOKEN=atn-prod-2024-secure-key
ENVIRONMENT=production
DEBUG_MODE=false
```

## 🛠️ ARCHIVO 2: /public_html/dev/.env (DESARROLLO)

```env
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_autonimus_adm
DB_PASS=L4vid4esundr4m4.
PANEL_TOKEN=atn-dev-2024-testing-key
ENVIRONMENT=development
DEBUG_MODE=true
```

---

## 📋 INSTRUCCIONES PARA SUBIR:

### 1️⃣ **Acceder al File Manager de Hostinger:**
- hPanel → Archivos → Administrador de archivos

### 2️⃣ **Crear archivo de PRODUCCIÓN:**
- Navegar a `/public_html/`
- Click "Archivo nuevo"
- Nombre: `.env`
- Copiar y pegar el contenido del **ARCHIVO 1** de arriba

### 3️⃣ **Crear archivo de DESARROLLO:**
- Navegar a `/public_html/dev/`
- Click "Archivo nuevo"
- Nombre: `.env`
- Copiar y pegar el contenido del **ARCHIVO 2** de arriba

---

## 🚀 **RESULTADO:**
Una vez subidos ambos archivos .env, tu sistema estará completamente configurado:

- **🔥 autonimuscorp.online** → Producción
- **🛠️ dev.autonimuscorp.online** → Desarrollo
- **🎛️ Panel IA** en ambos entornos (`/panel/`)

**¡Los GitHub Actions deployarán automáticamente! 🚀**