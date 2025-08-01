// Sistema de Logging Completo para la Aplicación
class AppLogger {
    constructor() {
        this.logs = [];
        this.maxLogs = 1000; // Máximo número de logs a mantener
        this.enabled = true;
        this.initializeLogger();
    }

    initializeLogger() {
        // Crear elemento para mostrar logs en la página
        this.createLogDisplay();
        
        // Interceptar console.log, console.error, etc.
        this.interceptConsole();
        
        // Log inicial
        this.log('SYSTEM', 'Logger inicializado', { timestamp: new Date().toISOString() });
        
        // Log de información del navegador
        this.log('SYSTEM', 'Información del navegador', {
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine
        });
        
        // Log de información de la página
        this.log('SYSTEM', 'Información de la página', {
            url: window.location.href,
            title: document.title,
            referrer: document.referrer
        });
    }

    createLogDisplay() {
        // Crear contenedor de logs si no existe
        if (!document.getElementById('app-logger')) {
            const loggerDiv = document.createElement('div');
            loggerDiv.id = 'app-logger';
            loggerDiv.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                width: 400px;
                max-height: 300px;
                background: rgba(0, 0, 0, 0.9);
                color: #fff;
                font-family: monospace;
                font-size: 11px;
                padding: 10px;
                border-radius: 5px;
                overflow-y: auto;
                z-index: 9999;
                display: none;
            `;
            
            const toggleBtn = document.createElement('button');
            toggleBtn.textContent = '📋 LOGS';
            toggleBtn.style.cssText = `
                position: fixed;
                top: 10px;
                right: 420px;
                z-index: 10000;
                padding: 5px 10px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            `;
            toggleBtn.onclick = () => {
                loggerDiv.style.display = loggerDiv.style.display === 'none' ? 'block' : 'none';
            };
            
            const clearBtn = document.createElement('button');
            clearBtn.textContent = '🗑️ LIMPIAR';
            clearBtn.style.cssText = `
                position: fixed;
                top: 10px;
                right: 520px;
                z-index: 10000;
                padding: 5px 10px;
                background: #dc3545;
                color: white;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            `;
            clearBtn.onclick = () => {
                this.clearLogs();
            };
            
            document.body.appendChild(toggleBtn);
            document.body.appendChild(clearBtn);
            document.body.appendChild(loggerDiv);
        }
    }

    interceptConsole() {
        const originalLog = console.log;
        const originalError = console.error;
        const originalWarn = console.warn;
        const originalInfo = console.info;

        console.log = (...args) => {
            this.log('CONSOLE', 'LOG', { message: args.join(' ') });
            originalLog.apply(console, args);
        };

        console.error = (...args) => {
            this.log('CONSOLE', 'ERROR', { message: args.join(' ') });
            originalError.apply(console, args);
        };

        console.warn = (...args) => {
            this.log('CONSOLE', 'WARN', { message: args.join(' ') });
            originalWarn.apply(console, args);
        };

        console.info = (...args) => {
            this.log('CONSOLE', 'INFO', { message: args.join(' ') });
            originalInfo.apply(console, args);
        };
    }

    log(category, action, data = {}) {
        if (!this.enabled) return;

        const logEntry = {
            timestamp: new Date().toISOString(),
            category: category,
            action: action,
            data: data,
            url: window.location.href,
            userAgent: navigator.userAgent
        };

        this.logs.push(logEntry);

        // Mantener solo los últimos maxLogs
        if (this.logs.length > this.maxLogs) {
            this.logs = this.logs.slice(-this.maxLogs);
        }

        // Actualizar display
        this.updateLogDisplay();

        // También loggear en localStorage para persistencia
        this.saveToLocalStorage();
    }

    updateLogDisplay() {
        const loggerDiv = document.getElementById('app-logger');
        if (!loggerDiv) return;

        const recentLogs = this.logs.slice(-20); // Mostrar solo los últimos 20 logs
        loggerDiv.innerHTML = recentLogs.map(log => {
            const time = new Date(log.timestamp).toLocaleTimeString();
            const category = log.category;
            const action = log.action;
            const data = JSON.stringify(log.data, null, 2);
            
            return `
                <div style="margin-bottom: 5px; border-bottom: 1px solid #333; padding-bottom: 5px;">
                    <div style="color: #00ff00;">[${time}] ${category}: ${action}</div>
                    <div style="color: #ccc; font-size: 10px; white-space: pre-wrap;">${data}</div>
                </div>
            `;
        }).join('');
    }

    saveToLocalStorage() {
        try {
            localStorage.setItem('app_logs', JSON.stringify(this.logs));
        } catch (e) {
            console.error('Error guardando logs en localStorage:', e);
        }
    }

    loadFromLocalStorage() {
        try {
            const savedLogs = localStorage.getItem('app_logs');
            if (savedLogs) {
                this.logs = JSON.parse(savedLogs);
                this.updateLogDisplay();
            }
        } catch (e) {
            console.error('Error cargando logs desde localStorage:', e);
        }
    }

    exportLogs() {
        const logData = {
            exportDate: new Date().toISOString(),
            totalLogs: this.logs.length,
            logs: this.logs
        };

        const blob = new Blob([JSON.stringify(logData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `app-logs-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        this.log('SYSTEM', 'Logs exportados', { filename: a.download });
    }

    clearLogs() {
        this.logs = [];
        this.updateLogDisplay();
        localStorage.removeItem('app_logs');
        this.log('SYSTEM', 'Logs limpiados');
    }

    // Logs específicos para diferentes categorías
    logAuth(action, data = {}) {
        this.log('AUTH', action, data);
    }

    logAPI(endpoint, method, data = {}) {
        this.log('API', `${method} ${endpoint}`, data);
    }

    logUI(action, data = {}) {
        this.log('UI', action, data);
    }

    logError(error, context = {}) {
        this.log('ERROR', error.message || error, {
            stack: error.stack,
            context: context
        });
    }

    logTest(action, data = {}) {
        this.log('TEST', action, data);
    }

    logAdmin(action, data = {}) {
        this.log('ADMIN', action, data);
    }
}

// Crear instancia global del logger
window.appLogger = new AppLogger();

// Función global para exportar logs
window.exportLogs = function() {
    window.appLogger.exportLogs();
};

// Función global para limpiar logs
window.clearLogs = function() {
    window.appLogger.clearLogs();
};

// Función global para mostrar/ocultar logs
window.toggleLogs = function() {
    const loggerDiv = document.getElementById('app-logger');
    if (loggerDiv) {
        loggerDiv.style.display = loggerDiv.style.display === 'none' ? 'block' : 'none';
    }
};

// Log inicial
window.appLogger.log('SYSTEM', 'Logger.js cargado', { version: '1.0.0' });