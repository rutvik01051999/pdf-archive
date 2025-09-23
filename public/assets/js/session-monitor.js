/**
 * Session Monitor for Admin Panel
 * Monitors user activity and handles session timeouts
 */
class SessionMonitor {
    constructor() {
        this.config = {
            checkInterval: 30000, // Check every 30 seconds
            warningThreshold: 1, // Show warning 1 minute before timeout
            extendEndpoint: '/admin/extend-session',
            statusEndpoint: '/admin/session-status',
            warningShown: false,
            sessionExtended: false
        };

        this.init();
    }

    init() {
        this.bindEvents();
        this.startMonitoring();
        this.checkSessionStatus();
    }

    bindEvents() {
        // Track user activity
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.updateLastActivity();
            }, true);
        });

        // Handle visibility change (tab switching)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkSessionStatus();
            }
        });

        // Handle beforeunload (browser close/refresh)
        window.addEventListener('beforeunload', () => {
            this.handleBeforeUnload();
        });
    }

    startMonitoring() {
        setInterval(() => {
            this.checkSessionStatus();
        }, this.config.checkInterval);
    }

    async checkSessionStatus() {
        try {
            const response = await fetch(this.config.statusEndpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.status === 401) {
                this.handleSessionExpired();
                return;
            }

            const data = await response.json();
            
            if (data.status === 'expired') {
                this.handleSessionExpired();
            } else if (data.minutes_remaining <= this.config.warningThreshold && !this.config.warningShown) {
                this.showSessionWarning(data.minutes_remaining);
            }
        } catch (error) {
            console.error('Session status check failed:', error);
        }
    }

    showSessionWarning(minutesRemaining) {
        this.config.warningShown = true;
        
        // Create warning modal
        const modal = this.createWarningModal(minutesRemaining);
        document.body.appendChild(modal);
        
        // Show modal
        modal.style.display = 'block';
        document.body.classList.add('modal-open');

        // Auto-hide warning after timeout
        setTimeout(() => {
            if (modal.parentNode) {
                this.hideWarningModal(modal);
            }
        }, minutesRemaining * 60 * 1000);
    }

    createWarningModal(minutesRemaining) {
        const modal = document.createElement('div');
        modal.className = 'session-warning-modal';
        modal.innerHTML = `
            <div class="session-warning-overlay">
                <div class="session-warning-content">
                    <div class="session-warning-header">
                        <h4>Session Timeout Warning</h4>
                    </div>
                    <div class="session-warning-body">
                        <p>Your session will expire in <strong>${minutesRemaining} minute(s)</strong> due to inactivity.</p>
                        <p>Click "Extend Session" to continue working.</p>
                    </div>
                    <div class="session-warning-footer">
                        <button type="button" class="btn btn-primary" id="extend-session-btn">
                            <i class="fas fa-clock"></i> Extend Session
                        </button>
                        <button type="button" class="btn btn-secondary" id="logout-now-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout Now
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add styles
        this.addWarningStyles();

        // Bind events
        modal.querySelector('#extend-session-btn').addEventListener('click', () => {
            this.extendSession();
        });

        modal.querySelector('#logout-now-btn').addEventListener('click', () => {
            this.logoutNow();
        });

        return modal;
    }

    addWarningStyles() {
        if (document.getElementById('session-warning-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'session-warning-styles';
        styles.textContent = `
            .session-warning-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                display: none;
            }
            
            .session-warning-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .session-warning-content {
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                max-width: 400px;
                width: 90%;
                animation: slideIn 0.3s ease-out;
            }
            
            .session-warning-header {
                padding: 20px 20px 10px;
                border-bottom: 1px solid #eee;
            }
            
            .session-warning-header h4 {
                margin: 0;
                color: #333;
                font-size: 18px;
            }
            
            .session-warning-body {
                padding: 20px;
            }
            
            .session-warning-body p {
                margin: 0 0 10px;
                color: #666;
                line-height: 1.5;
            }
            
            .session-warning-footer {
                padding: 10px 20px 20px;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
            }
            
            .session-warning-footer .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .session-warning-footer .btn-primary {
                background: #007bff;
                color: white;
            }
            
            .session-warning-footer .btn-secondary {
                background: #6c757d;
                color: white;
            }
            
            .session-warning-footer .btn:hover {
                opacity: 0.9;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .modal-open {
                overflow: hidden;
            }
        `;
        document.head.appendChild(styles);
    }

    hideWarningModal(modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        modal.remove();
        this.config.warningShown = false;
    }

    async extendSession() {
        try {
            const response = await fetch(this.config.extendEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.config.sessionExtended = true;
                this.config.warningShown = false;
                
                // Hide warning modal
                const modal = document.querySelector('.session-warning-modal');
                if (modal) {
                    this.hideWarningModal(modal);
                }

                // Show success message
                this.showNotification('Session extended successfully', 'success');
            } else {
                this.handleSessionExpired();
            }
        } catch (error) {
            console.error('Failed to extend session:', error);
            this.handleSessionExpired();
        }
    }

    logoutNow() {
        // Redirect to logout
        window.location.href = '/logout';
    }

    handleSessionExpired() {
        // Show expired modal
        const modal = this.createExpiredModal();
        document.body.appendChild(modal);
        modal.style.display = 'block';
        document.body.classList.add('modal-open');

        // Auto-redirect after 3 seconds
        setTimeout(() => {
            window.location.href = '/secure-login?session_expired=1';
        }, 3000);
    }

    createExpiredModal() {
        const modal = document.createElement('div');
        modal.className = 'session-expired-modal';
        modal.innerHTML = `
            <div class="session-warning-overlay">
                <div class="session-warning-content">
                    <div class="session-warning-header">
                        <h4>Session Expired</h4>
                    </div>
                    <div class="session-warning-body">
                        <p>Your session has expired due to inactivity.</p>
                        <p>You will be redirected to the login page in a few seconds.</p>
                    </div>
                    <div class="session-warning-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='/secure-login'">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </button>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }

    updateLastActivity() {
        // Update last activity timestamp
        this.lastActivity = Date.now();
    }

    handleBeforeUnload() {
        // Optional: Send logout request when browser is closed
        if (navigator.sendBeacon) {
            navigator.sendBeacon('/logout');
        }
    }

    showNotification(message, type = 'info') {
        // Simple notification system
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} session-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 12px 20px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
        `;
        
        if (type === 'success') {
            notification.style.background = '#28a745';
        } else if (type === 'error') {
            notification.style.background = '#dc3545';
        } else {
            notification.style.background = '#17a2b8';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize session monitor when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize for admin pages
    if (window.location.pathname.startsWith('/admin/')) {
        new SessionMonitor();
    }
});
