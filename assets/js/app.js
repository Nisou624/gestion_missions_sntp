// Scripts JavaScript pour l'application de gestion des missions

$(document).ready(function() {
    
    // Initialisation des tooltips Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialisation des popovers Bootstrap
    $('[data-toggle="popover"]').popover();
    
    // Auto-fermeture des alertes après 5 secondes
    $('.alert:not(.alert-permanent)').delay(5000).fadeOut('slow');
    
    // Confirmation avant suppression
    $('.confirm-delete').click(function(e) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Loading overlay pour les formulaires
    $('form').submit(function() {
        showLoading();
    });
    
    // Validation côté client pour les dates
    $('input[type="date"]').change(function() {
        validateDates();
    });
    
    // Auto-format des numéros de téléphone
    $('input[type="tel"]').on('input', function() {
        formatPhoneNumber(this);
    });
    
});

// Fonctions utilitaires

function showLoading() {
    if ($('.spinner-overlay').length === 0) {
        $('body').append(`
            <div class="spinner-overlay">
                <div class="spinner-border spinner-border-custom text-primary" role="status">
                    <span class="sr-only">Chargement...</span>
                </div>
            </div>
        `);
    }
    $('.spinner-overlay').fadeIn();
}

function hideLoading() {
    $('.spinner-overlay').fadeOut();
}

function validateDates() {
    var dateDepart = $('input[name="date_depart"]').val();
    var dateRetour = $('input[name="date_retour"]').val();
    
    if (dateDepart && dateRetour) {
        var depart = new Date(dateDepart);
        var retour = new Date(dateRetour);
        
        if (retour <= depart) {
            alert('La date de retour doit être postérieure à la date de départ.');
            $('input[name="date_retour"]').val('');
            return false;
        }
    }
    return true;
}

function formatPhoneNumber(input) {
    var value = input.value.replace(/\D/g, '');
    var formattedValue = '';
    
    if (value.length > 0) {
        if (value.startsWith('213')) {
            // Format international algérien: +213 XX XXX XXXX
            formattedValue = '+213 ' + value.substring(3, 5) + ' ' + 
                           value.substring(5, 8) + ' ' + value.substring(8, 12);
        } else if (value.startsWith('0')) {
            // Format national algérien: 0XX XXX XXXX
            formattedValue = value.substring(0, 3) + ' ' + 
                           value.substring(3, 6) + ' ' + value.substring(6, 10);
        } else {
            formattedValue = value;
        }
    }
    
    input.value = formattedValue;
}

// Fonctions pour les DataTables
function initializeDataTable(selector, options = {}) {
    const defaultOptions = {
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        },
        "responsive": true,
        "pageLength": 25,
        "order": [[0, "desc"]]
    };
    
    const finalOptions = Object.assign(defaultOptions, options);
    return $(selector).DataTable(finalOptions);
}

// Gestion des notifications
function showNotification(message, type = 'info') {
    const alertClass = `alert-${type}`;
    const icon = getIconForType(type);
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('#notifications-container').prepend(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.fadeOut();
    }, 5000);
}

function getIconForType(type) {
    const icons = {
        'success': 'fas fa-check-circle',
        'warning': 'fas fa-exclamation-triangle',
        'danger': 'fas fa-times-circle',
        'info': 'fas fa-info-circle'
    };
    return icons[type] || icons['info'];
}

// Export des données
function exportTableToCSV(tableId, filename = 'export.csv') {
    var csv = [];
    var rows = document.querySelectorAll(`#${tableId} tr`);
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            var cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        
        csv.push(row.join(","));
    }
    
    downloadCSV(csv.join("\n"), filename);
}

function downloadCSV(csv, filename) {
    var csvFile = new Blob([csv], {type: "text/csv"});
    var downloadLink = document.createElement("a");
    
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Validation avancée des formulaires
function validateMissionForm() {
    var isValid = true;
    var errors = [];
    
    // Validation des champs obligatoires
    $('input[required], select[required], textarea[required]').each(function() {
        if (!$(this).val()) {
            isValid = false;
            errors.push('Le champ "' + $(this).prev('label').text() + '" est obligatoire.');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Validation des dates
    if (!validateDates()) {
        isValid = false;
    }
    
    // Affichage des erreurs
    if (!isValid) {
        showNotification('Veuillez corriger les erreurs dans le formulaire.', 'danger');
        errors.forEach(error => console.log(error));
    }
    
    return isValid;
}
