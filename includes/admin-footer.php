</div> <!-- Close container -->
    </div> <!-- Close main-content -->

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Klinik MCU</h5>
                    <p>Penyedia layanan Medical Check Up terpercaya dengan tim dokter profesional dan peralatan medis lengkap.</p>
                </div>
                <div class="col-md-4">
                    <h5>Kontak Kami</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> <?php echo getSetting('alamat'); ?></li>
                        <li><i class="fas fa-phone"></i> <?php echo getSetting('telepon'); ?></li>
                        <li><i class="fas fa-envelope"></i> <?php echo getSetting('email'); ?></li>
                        <li><i class="fab fa-whatsapp"></i> <?php echo getSetting('whatsapp'); ?></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Jam Operasional</h5>
                    <ul class="list-unstyled">
                        <li>Senin - Jumat: 08:00 - 16:00</li>
                        <li>Sabtu: 08:00 - 14:00</li>
                        <li>Minggu & Hari Libur: Tutup</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Klinik MCU. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/script.js"></script>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
                }
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });

        // Confirm before delete
        function confirmDelete(message = 'Apakah Anda yakin?') {
            return confirm(message);
        }

        // Print function
        function printElement(elementId) {
            var printContent = document.getElementById(elementId).innerHTML;
            var originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
    </script>
</body>
</html>
