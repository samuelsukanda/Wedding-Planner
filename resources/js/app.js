import * as Turbo from '@hotwired/turbo';

Turbo.start();

// Flatpickr milik layout app (halaman form tanggal). Head tidak di-re-execute
// oleh Turbo, jadi didaftarkan sekali di sini, bukan per-load.
document.addEventListener('turbo:load', function() {
    document.querySelectorAll('.datepicker').forEach(function(el) {
        if (el._flatpickr) return;
        flatpickr(el, {
            dateFormat: 'Y-m-d',
            allowInput: true,
            onChange: function() {
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
});
