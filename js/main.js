// Main JavaScript file for the Pipe Mill Investment Analysis System

document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions with AJAX to prevent page reloads
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // For now, just ensure form validation
            // In a complete implementation, we would use AJAX
        });
    });
    
    // Add confirmation to delete actions
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    
    deleteLinks.forEach(link => {
        if (!link.getAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                const confirmed = confirm('Вы уверены, что хотите удалить эту запись?');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // Initialize tooltips if needed
    initializeTooltips();
});

function initializeTooltips() {
    // Add tooltip functionality to elements with title attributes
    const tooltipElements = document.querySelectorAll('[title]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.title;
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = '#333';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '1000';
            tooltip.style.opacity = '0';
            tooltip.style.transition = 'opacity 0.3s';
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            
            // Fade in
            setTimeout(() => {
                tooltip.style.opacity = '1';
            }, 10);
            
            // Store tooltip reference
            this.tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltip) {
                this.tooltip.style.opacity = '0';
                setTimeout(() => {
                    if (this.tooltip && this.tooltip.parentNode) {
                        this.tooltip.parentNode.removeChild(this.tooltip);
                    }
                    this.tooltip = null;
                }, 300);
            }
        });
    });
}

// Utility function for number formatting
function formatNumber(num, decimals = 2) {
    return num.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

// Function to validate form inputs
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#e74c3c';
            
            // Add error message
            let errorMsg = field.parentNode.querySelector('.error-message');
            if (!errorMsg) {
                errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.style.color = '#e74c3c';
                errorMsg.style.fontSize = '0.8rem';
                errorMsg.style.marginTop = '0.2rem';
                errorMsg.textContent = 'Это поле обязательно для заполнения';
                field.parentNode.appendChild(errorMsg);
            }
        } else {
            field.style.borderColor = '#ddd';
            const errorMsg = field.parentNode.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
        
        // Validate numeric fields
        if (field.type === 'number' && field.value) {
            const value = parseFloat(field.value);
            if (isNaN(value) || value < 0) {
                isValid = false;
                field.style.borderColor = '#e74c3c';
                
                let errorMsg = field.parentNode.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.style.color = '#e74c3c';
                    errorMsg.style.fontSize = '0.8rem';
                    errorMsg.style.marginTop = '0.2rem';
                    errorMsg.textContent = 'Введите положительное число';
                    field.parentNode.appendChild(errorMsg);
                }
            }
        }
    });
    
    return isValid;
}

// Function to show notification messages
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '4px';
    notification.style.color = '#fff';
    notification.style.zIndex = '10000';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s';
    
    // Set color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#27ae60';
            break;
        case 'error':
            notification.style.backgroundColor = '#e74c3c';
            break;
        case 'warning':
            notification.style.backgroundColor = '#f39c12';
            break;
        default:
            notification.style.backgroundColor = '#3498db';
    }
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}