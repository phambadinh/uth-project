        </div> <!-- .admin-content -->
    </main>
</div> <!-- .admin-layout -->

<!-- JavaScript -->
<script src="<?= ASSETS_URL ?>/js/admin.js"></script>

<script>
// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// User menu dropdown
document.querySelector('.admin-user-menu')?.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-menu')) {
        this.querySelector('.dropdown-menu').classList.toggle('show');
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.admin-user-menu')) {
        document.querySelectorAll('.admin-user-menu .dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});
</script>

</body>
</html>
