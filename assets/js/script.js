           // This script handles the image preview functionality.
            const fileUpload = document.getElementById('file-upload');
            const photoPreview = document.querySelector('.photo-preview');

            if (fileUpload && photoPreview) {
                fileUpload.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" />`;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // This script handles the mobile sidebar functionality.
            document.addEventListener('DOMContentLoaded', function() {
                // Replaces feather icons with SVG.
                feather.replace();

                // Mobile menu
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');

                if (mobileMenuButton && sidebar && overlay) {
                    mobileMenuButton.addEventListener('click', function() {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    });

                    function closeSidebar() {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }

                    overlay.addEventListener('click', closeSidebar);

                    // Handles window resize events.
                    window.addEventListener('resize', function() {
                        if (window.innerWidth >= 768) {
                            sidebar.classList.remove('-translate-x-full');
                            overlay.classList.add('hidden');
                            document.body.classList.remove('overflow-hidden');
                        } else {
                            sidebar.classList.add('-translate-x-full');
                        }
                    });
                }
            });