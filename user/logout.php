<?php
session_start();
session_destroy();
?>
<script>
    alert('Vous avez été déconnecté avec succès !');
    window.location.href = '../index.php';
</script>
