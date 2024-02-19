// ===========================================================================================
// ======================================== Custom JS ========================================
// ===========================================================================================

"use strict";

// Toaster Message
$(document).ready(function() {
    var successMessage = $('#successMessage').val();
    var errorMessage = $('#errorMessage').val();
    if (successMessage) {
        iziToast.success({
            title: 'Success!',
            message: successMessage,
            position: 'topRight'
        });
    }else if (errorMessage) {
        iziToast.error({
            title: 'Error!',
            message: errorMessage,
            position: 'topRight'
        });
    }
});

// Wrong Extension Image
document.addEventListener("DOMContentLoaded", function() {
    var fileInput = document.getElementById('customFile');
    var fileError = document.getElementById('fileError');
    var fileSuccess = document.getElementById('fileSuccess');

    fileInput.addEventListener('change', function() {
        var files = this.files;
        var errorMessage = '';

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var extension = file.name.split('.').pop().toLowerCase();
            var allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

            if (allowedExtensions.indexOf(extension) === -1) {
                errorMessage = 'Please select JPG, JPEG, PNG, GIF, or SVG files only.';
                break;
            }
        }

        if (errorMessage) {
            fileError.textContent = errorMessage;
            fileError.style.display = 'block';
            fileSuccess.style.display = 'none';
            this.value = '';
        } else {
            fileError.style.display = 'none';
            fileSuccess.style.display = 'block';
        }
    });
});