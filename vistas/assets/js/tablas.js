$(document).ready(function() {
    $(".tablas").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columnDefs: [
            { orderable: false, targets: -1}
        ],
        order: [[0, "asc"]]
    });
});