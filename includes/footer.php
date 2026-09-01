<?php
$prefix = isset($path_prefix) ? $path_prefix : '';
?>
<!-- Global Toast Notifications Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Mobile Drawer Overlay -->
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Scripts -->
<script src="<?php echo $prefix; ?>assets/js/script.js?v=2.0"></script>
<script src="<?php echo $prefix; ?>assets/js/dashboard.js?v=2.0"></script>
</body>
</html>
