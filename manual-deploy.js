const FtpDeploy = require('ftp-deploy');
const ftpDeploy = new FtpDeploy();

// Configuración para producción
const configProd = {
    user: 'u196678264',
    password: process.argv[2] || 'PASSWORD_AQUI', // Pasar como argumento
    host: 'ftp.hostinger.com',
    port: 21,
    localRoot: './public/',
    remoteRoot: '/public_html/',
    include: ['*', '**/*'],
    exclude: [],
    deleteRemote: false,
    forcePasv: true,
    sftp: false
};

// Configuración para desarrollo
const configDev = {
    user: 'u196678264',
    password: process.argv[2] || 'PASSWORD_AQUI', // Pasar como argumento
    host: 'ftp.hostinger.com',
    port: 21,
    localRoot: './public-dev/',
    remoteRoot: '/public_html/dev/',
    include: ['*', '**/*'],
    exclude: [],
    deleteRemote: false,
    forcePasv: true,
    sftp: false
};

async function deployProduction() {
    console.log('🔥 Desplegando PRODUCCIÓN...');
    try {
        await ftpDeploy.deploy(configProd);
        console.log('✅ PRODUCCIÓN desplegada correctamente');
    } catch (e) {
        console.error('❌ Error en PRODUCCIÓN:', e);
    }
}

async function deployDevelopment() {
    console.log('🛠️ Desplegando DESARROLLO...');
    try {
        await ftpDeploy.deploy(configDev);
        console.log('✅ DESARROLLO desplegado correctamente');
    } catch (e) {
        console.error('❌ Error en DESARROLLO:', e);
    }
}

async function deployBoth() {
    if (!process.argv[2]) {
        console.error('❌ Falta password. Uso: node manual-deploy.js [PASSWORD]');
        process.exit(1);
    }
    
    console.log('🚀 INICIANDO DEPLOY MANUAL AUTONIMUS-Q CLOUD');
    await deployProduction();
    await deployDevelopment();
    console.log('🎉 DEPLOY COMPLETO - Ambos entornos desplegados');
}

deployBoth();