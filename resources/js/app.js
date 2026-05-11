import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});
window.Toast = Toast;

window.confirmAction = function(form, title = 'Apakah anda yakin?', text = 'Tindakan ini tidak dapat dibatalkan.', confirmButtonText = 'Ya, Lakukan') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb', // blue-600
        cancelButtonColor: '#64748b', // slate-500
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            container: 'z-[9999]',
            popup: 'rounded-2xl border-none shadow-2xl',
            confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-bold',
            cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

window.confirmDelete = function(form, title = 'Hapus data ini?') {
    Swal.fire({
        title: title,
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // rose-600
        cancelButtonColor: '#64748b', // slate-500
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            container: 'z-[9999]',
            popup: 'rounded-2xl border-none shadow-2xl',
            confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-bold',
            cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

Alpine.start();
