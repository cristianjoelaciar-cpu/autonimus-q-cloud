# 🛠️ SOLUCIÓN: Subdominio DEV de Autonimus-Q Cloud

## 🚨 PROBLEMA IDENTIFICADO:
- ✅ **autonimuscorp.online** → Funciona correctamente
- ❌ **dev.autonimuscorp.online** → Página en blanco / no carga

## 🔧 SOLUCIÓN HOSTINGER:

### **1️⃣ VERIFICAR/CREAR SUBDOMINIO:**

**En hPanel:**
1. **Ir a:** Dominios → Subdominios
2. **Verificar que existe:** `dev.autonimuscorp.online`
3. **Si no existe, crear:**
   - **Subdominio:** `dev`
   - **Dominio:** `autonimuscorp.online`
   - **Directorio:** `/public_html/dev/`
4. **Guardar cambios**

### **2️⃣ VERIFICAR ARCHIVOS:**

**File Manager → `/public_html/dev/`**
- ¿Existen los archivos PHP?
- ¿Existe el archivo `.env`?

### **3️⃣ DNS PROPAGATION:**
Puede tardar 5-15 minutos en propagarse.

### **4️⃣ FORZAR DEPLOY DEV:**
Hacer push a rama dev para reactivar deployment.

---

## 🚀 COMANDOS PARA FORZAR DEPLOY:

```bash
git checkout dev
git commit --allow-empty -m "🔄 Force deploy DEV - Fix subdomain"
git push origin dev
```

## ⏰ TIEMPO ESTIMADO:
- **Creación subdominio:** 2 minutos
- **Propagación DNS:** 5-15 minutos
- **Deploy automático:** 2-3 minutos

---

## 🎯 RESULTADO ESPERADO:
- **dev.autonimuscorp.online** → Panel IA development
- **Tema cyan** distintivo
- **Debug mode** activado