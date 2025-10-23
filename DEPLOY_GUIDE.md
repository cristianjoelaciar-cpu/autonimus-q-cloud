# Guía de Configuración Autonimus-Q Cloud

## 🚀 Infraestructura Dual Completa

Tu sistema Autonimus-Q Cloud está listo con **arquitectura de doble entorno**:

- **🔥 PRODUCCIÓN**: `autonimuscorp.online` (rama main)
- **🛠️ DESARROLLO**: `dev.autonimuscorp.online` (rama dev)

---

## ⚡ Configuración Rápida en Hostinger

### 1️⃣ Crear Subdominio de Desarrollo

1. **Acceder a hPanel** → Dominios → Subdominios
2. **Crear subdominio**: `dev` → Apuntar a `/public_html/dev/`
3. **Verificar**: `dev.autonimuscorp.online` debe resolver

### 2️⃣ Configurar Variables de Entorno

**Archivo: `/public_html/.env`** (Producción)
```env
# Producción - autonimuscorp.online
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_CristiAndMane
DB_PASS=Manejar123
PANEL_TOKEN=atn-prod-2024-secure-key
ENVIRONMENT=production
DEBUG_MODE=false
```

**Archivo: `/public_html/dev/.env`** (Desarrollo)
```env
# Desarrollo - dev.autonimuscorp.online
DB_HOST=localhost
DB_NAME=u196678264_autonimus_core
DB_USER=u196678264_CristiAndMane
DB_PASS=Manejar123
PANEL_TOKEN=atn-dev-2024-testing-key
ENVIRONMENT=development
DEBUG_MODE=true
```

### 3️⃣ Configurar GitHub Secrets

**Ir a:** GitHub → Settings → Secrets and variables → Actions

```yaml
Secrets requeridos:
- FTP_SERVER: ftp.autonimuscorp.online
- FTP_USERNAME: u196678264
- FTP_PASSWORD: [tu_password_hostinger]
- FTP_PORT: 21
```

---

## 🔧 Estructura del Sistema

```
autonimusq-cloud/
├── public/              # 🔥 PRODUCCIÓN
│   ├── index.html       # Landing principal
│   ├── style.css        # Tema azul profesional
│   ├── core/            # Backend PHP
│   └── panel/           # Panel IA producción
│
├── public-dev/          # 🛠️ DESARROLLO  
│   ├── index.html       # Landing con avisos DEV
│   ├── style.css        # Tema cyan con warnings
│   ├── core/            # Backend PHP con debug
│   └── panel/           # Panel IA desarrollo
│
└── .github/workflows/   # 🚀 CI/CD Automatizado
    ├── deploy-main.yml  # Deploy a producción
    └── deploy-dev.yml   # Deploy a desarrollo
```

---

## 🎯 URLs del Sistema

| Entorno | URL | Panel IA | Logs |
|---------|-----|----------|------|
| **Producción** | `autonimuscorp.online` | `/panel/` | `autonimus.log` |
| **Desarrollo** | `dev.autonimuscorp.online` | `/panel/` | `autonimus-dev.log` |

---

## 🔥 Funcionalidades del Panel IA

### ✅ Producción (`/panel/`)
- 🟢 Estado del sistema en tiempo real
- 📊 Monitoreo de base de datos
- 📝 Gestión de logs (autonimus.log)
- ⚡ Comandos seguros del sistema
- 🔐 Autenticación por token

### 🛠️ Desarrollo (`dev.autonimuscorp.online/panel/`)
- 🟡 Entorno de pruebas identificado
- 🔍 Debug mode activado
- 📝 Logs separados (autonimus-dev.log)
- ⚠️ Interfaz con avisos de desarrollo
- 🧪 Comandos de testing disponibles

---

## 🚀 Despliegue Automatizado

### Flujo de Trabajo:

1. **Desarrollo**: Push a rama `dev` → Auto-deploy a `dev.autonimuscorp.online`
2. **Producción**: Merge a rama `main` → Auto-deploy a `autonimuscorp.online`

### GitHub Actions activará:
- ✅ Validación de código
- 📁 Subida FTP automática
- 🔄 Sincronización de archivos
- ✅ Verificación de deploy

---

## 🛡️ Seguridad Implementada

- 🔐 **Tokens únicos** por entorno
- 🚫 **Protección de archivos** sensibles
- 📍 **Detección automática** de entorno
- 🔒 **Variables seguras** en GitHub
- 🛡️ **Separación completa** prod/dev

---

## 📋 Checklist de Activación

- [ ] Crear subdominio `dev` en hPanel
- [ ] Subir archivos `.env` a ambos directorios
- [ ] Configurar secrets en GitHub
- [ ] Hacer primer push para activar workflows
- [ ] Verificar acceso a ambos paneles
- [ ] Probar conectividad de base de datos

---

## ⚡ Próximos Pasos

1. **¿Necesitas ayuda con hPanel?** Te guío paso a paso
2. **¿Configuramos GitHub ahora?** Preparamos el repositorio
3. **¿Probamos el deploy?** Hacemos el primer despliegue

**¡Tu infraestructura está lista para producción! 🚀**