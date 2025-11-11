<footer class="bg-light text-center py-3 mt-5">
    <div class="container">
        <p class="text-muted mb-0">
            &copy; <?php echo date('Y'); ?> Société Nationale de Travaux Publics - Système de Gestion des Ordres de Mission
        </p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialiser DataTables
    $('.data-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        }
    });
    
    // Animation des alertes
    $('.alert').delay(5000).fadeOut('slow');
});
</script>
