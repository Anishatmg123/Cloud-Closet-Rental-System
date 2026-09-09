/**
 * Cloud Closet — Dashboard & Interactive Scripts
 */

// Toggle Mobile Sidebar Drawer
function toggleMobileSidebar() {
    const sidebar = document.getElementById('dashboard-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Toggle Topbar Dropdowns (Notifications, Profile)
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    
    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.id !== dropdownId) {
            menu.classList.remove('show');
        }
    });

    dropdown.classList.toggle('show');
}

// Close dropdowns on outside click
document.addEventListener('click', function(event) {
    if (!event.target.closest('.topbar-dropdown-wrap')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Toast Notification Generator
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-msg ${type}`;
    
    let icon = '<i class="fa-solid fa-circle-info"></i>';
    if (type === 'success') icon = '<i class="fa-solid fa-circle-check text-success"></i>';
    if (type === 'error') icon = '<i class="fa-solid fa-circle-exclamation text-danger"></i>';

    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Wishlist AJAX Toggle
async function toggleFavorite(dressId, buttonElement) {
    try {
        const formData = new FormData();
        formData.append('dress_id', dressId);

        // Determine correct path to API endpoint
        let apiPath = '../api/toggle-favorite.php';
        if (window.location.pathname.indexOf('/user/') === -1 && window.location.pathname.indexOf('/vendor/') === -1) {
            apiPath = 'api/toggle-favorite.php';
        }

        const response = await fetch(apiPath, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'unauthorized') {
            showToast('Please log in as a customer to save favorites.', 'error');
            setTimeout(() => {
                window.location.href = (apiPath.startsWith('../') ? '../' : '') + 'login.php';
            }, 1200);
            return;
        }

        if (data.success) {
            const icon = buttonElement.querySelector('i');
            if (data.action === 'added') {
                buttonElement.classList.add('active');
                if (icon) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                }
                showToast('Dress added to your wishlist!', 'success');
            } else {
                buttonElement.classList.remove('active');
                if (icon) {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
                showToast('Dress removed from wishlist.', 'info');
            }

            // Update topbar badge if present
            const badge = document.getElementById('wishlist-badge');
            if (badge) {
                if (data.total_favorites > 0) {
                    badge.textContent = data.total_favorites;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        } else {
            showToast(data.message || 'Error updating wishlist', 'error');
        }
    } catch (err) {
        showToast('Network error updating wishlist', 'error');
    }
}
