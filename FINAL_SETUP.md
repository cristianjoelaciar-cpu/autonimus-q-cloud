# 🚀 AUTONIMUS-Q CLOUD - CONFIGURACIÓN FINAL

## ✅ ESTADO ACTUAL:
- ✅ **Repositorio GitHub creado y sincronizado**
- ✅ **Ambas ramas (main y dev) subidas**
- ✅ **GitHub Actions workflows configurados**
- 🔄 **Pendiente: Secrets de GitHub**
- 🔄 **Pendiente: Configuración Hostinger**

---

## 🔐 PASO 1: CONFIGURAR SECRETS EN GITHUB

### **URL directa:** 
https://github.com/cristianjoelaciar-cpu/autonimus-q-cloud/settings/secrets/actions

### **Agregar estos 4 secrets (uno por uno):**

**🔑 Secret 1:**
```
Name: FTP_SERVER
Secret: ftp.autonimuscorp.online
```

**🔑 Secret 2:**
```
Name: FTP_USERNAME
Secret: u196678264
```

**🔑 Secret 3:**
```
Name: FTP_PASSWORD
Secret: [TU_PASSWORD_HOSTINGER]
```

**🔑 Secret 4:**
```
Name: FTP_PORT
Secret: 21
```

---

## 🏠 PASO 2: HOSTINGER - CREAR SUBDOMINIO

### **1️⃣ Acceder a hPanel:**
- URL: https://hpanel.hostinger.com
- Login con tu cuenta

### **2️⃣ Crear subdominio:**
- **Menú:** Dominios → Subdominios
- **Click:** "Crear subdominio"
- **Subdominio:** `dev`
- **Dominio:** `autonimuscorp.online`
- **Directorio:** `/public_html/dev/`
- **Guardar**

---

## 📁 PASO 3: SUBIR ARCHIVOS .ENV

### **Archivo 1: /public_html/.env (PRODUCCIÓN)**
```env
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_CristiAndMane
DB_PASS=Manejar123
PANEL_TOKEN=atn-prod-2024-secure-key
ENVIRONMENT=production
DEBUG_MODE=false
```

### **Archivo 2: /public_html/dev/.env (DESARROLLO)**
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

## 🚀 RESULTADO FINAL

Una vez completados estos pasos:

- **🔥 autonimuscorp.online** → Sistema en producción
- **🛠️ dev.autonimuscorp.online** → Entorno de desarrollo
- **⚡ Deploy automático** en cada push
- **🎛️ Panel IA** en ambos entornos (`/panel/`)

---

## 🎯 URLS FINALES:

| Entorno | URL Principal | Panel IA |
|---------|---------------|----------|
| **Producción** | `autonimuscorp.online` | `autonimuscorp.online/panel/` |
| **Desarrollo** | `dev.autonimuscorp.online` | `dev.autonimuscorp.online/panel/` |

---

## ⚡ PRÓXIMO PASO:
**Configurar los secrets en GitHub para activar el deploy automático!**

¿Empezamos? 🚀