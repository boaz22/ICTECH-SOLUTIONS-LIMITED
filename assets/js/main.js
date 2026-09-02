/**
 * ICTECH Solutions - Main JavaScript
 */

// Document ready equivalent
document.addEventListener('DOMContentLoaded', function() {
    initializeSlider();
    initializeCarousels();
    initializeValidation();
    initializeScrollEffects();
});

// ============================================
// Slider Initialization
// ============================================
function initializeSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    const slideInterval = 5000; // 5 seconds
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        slides[index].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }
    
    // Show first slide
    showSlide(0);
    
    // Auto advance slides
    setInterval(nextSlide, slideInterval);
    
    // Navigation arrows if they exist
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
        });
    }
}

// ============================================
// Carousel Initialization (for testimonials, etc)
// ============================================
function initializeCarousels() {
    // Bootstrap carousels are auto-initialized
    const carouselElements = document.querySelectorAll('.carousel');
    carouselElements.forEach(carousel => {
        new bootstrap.Carousel(carousel, {
            interval: 4000,
            wrap: true
        });
    });
}

// ============================================
// Form Validation
// ============================================
function initializeValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        clearError(input);
        
        if (!validateField(input)) {
            isValid = false;
            showError(input);
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.getAttribute('type') || field.tagName.toLowerCase();
    const required = field.hasAttribute('required');
    
    // Check required
    if (required && !value) {
        return false;
    }
    
    // Skip validation if field is empty and not required
    if (!value && !required) {
        return true;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    }
    
    // Phone validation (Kenya)
    if (type === 'tel' && value) {
        const phoneRegex = /^(\+254|0)[1-9]\d{8}$/;
        return phoneRegex.test(value);
    }
    
    // Password validation
    if (type === 'password' && value) {
        return value.length >= 6;
    }
    
    // Number validation
    if (type === 'number' && value) {
        return !isNaN(value) && value > 0;
    }
    
    return true;
}

function showError(field) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    let errorMsg = field.nextElementSibling;
    if (!errorMsg || !errorMsg.classList.contains('form-error')) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'form-error';
        field.parentNode.insertBefore(errorMsg, field.nextSibling);
    }
    
    let message = 'This field is required';
    const type = field.getAttribute('type');
    
    if (type === 'email') {
        message = 'Please enter a valid email address';
    } else if (type === 'tel') {
        message = 'Please enter a valid phone number';
    } else if (type === 'password') {
        message = 'Password must be at least 6 characters';
    }
    
    errorMsg.textContent = message;
}

function clearError(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    const errorMsg = field.nextElementSibling;
    if (errorMsg && errorMsg.classList.contains('form-error')) {
        errorMsg.remove();
    }
}

// ============================================
// Scroll Effects
// ============================================
function initializeScrollEffects() {
    // Scroll reveal for elements with data-scroll attribute
    const elements = document.querySelectorAll('[data-scroll]');
    
    if (elements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    elements.forEach(element => observer.observe(element));
}

// ============================================
// Utility Functions
// ============================================

// Show notification
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Format currency
function formatCurrency(amount) {
    return 'KES ' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Debounce function
function debounce(func, delay = 300) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Throttle function
function throttle(func, delay = 300) {
    let lastCall = 0;
    return function(...args) {
        const now = Date.now();
        if (now - lastCall >= delay) {
            func.apply(this, args);
            lastCall = now;
        }
    };
}

// Search/filter functionality
const setupSearch = (searchInputSelector, itemsSelector, nameAttr = 'data-name') => {
    const searchInput = document.querySelector(searchInputSelector);
    if (!searchInput) return;
    
    const items = document.querySelectorAll(itemsSelector);
    
    searchInput.addEventListener('keyup', debounce(function() {
        const query = this.value.toLowerCase();
        
        items.forEach(item => {
            const name = item.getAttribute(nameAttr).toLowerCase();
            if (name.includes(query)) {
                item.style.display = '';
                item.classList.add('fade-in');
            } else {
                item.style.display = 'none';
            }
        });
    }));
};

// Modal helper
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        bootstrap.Modal.getInstance(modal)?.hide();
    }
}

// Confirm action
function confirmAction(message = 'Are you sure?') {
    return confirm(message);
}

// AJAX request helper
async function fetchRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    try {
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Request failed:', error);
        showNotification('An error occurred. Please try again.', 'danger');
        return null;
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(() => {
            alert(text);
        });
    } else {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showNotification('Copied to clipboard!', 'success');
    }
}

// Smooth scroll
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Set active nav link
function setActiveNav(url) {
    const links = document.querySelectorAll('.nav-link');
    links.forEach(link => {
        if (link.href === url) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Initialize tooltips (Bootstrap)
function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
}

// Initialize popovers (Bootstrap)
function initPopovers() {
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });
}

// Export functions for global use
window.ICTECH = {
    showNotification,
    formatCurrency,
    formatDate,
    showModal,
    hideModal,
    confirmAction,
    fetchRequest,
    copyToClipboard,
    smoothScroll,
    setupSearch,
    initTooltips,
    initPopovers,
    debounce,
    throttle
};
