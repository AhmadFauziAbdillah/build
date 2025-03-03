<?php
/**
 * Language Handler
 * 
 * This file provides functions to load and manage translations for the application.
 * It supports switching between Indonesian and English languages.
 */

// Conditionally define loadLanguage function if not already defined
if (!function_exists('loadLanguage')) {
    /**
     * Load language strings based on the specified language code
     * 
     * @param string $lang_code The language code (id for Indonesian, en for English)
     * @return array Array of translated strings
     */
    function loadLanguage($lang_code = 'id') {
        // Default to Indonesian if no language specified
        $lang_code = in_array($lang_code, ['id', 'en']) ? $lang_code : 'id';
        
        // Define language strings (your existing translations remain the same)
        $translations = [
            // Indonesian translations (default)
        'id' => [
            // General terms
            'settings' => 'Pengaturan',
            'dashboard' => 'Dasbor',
            'save' => 'Simpan',
            'cancel' => 'Batal',
            'apply' => 'Terapkan',
            'back' => 'Kembali',
            'home' => 'Beranda',
            'logout' => 'Keluar',
            'login' => 'Masuk',
            'register' => 'Daftar',
            'welcome' => 'Selamat Datang',
            'yes' => 'Ya',
            'no' => 'Tidak',
            'submit' => 'Kirim',
            'edit' => 'Edit',
            'delete' => 'Hapus',
            'add' => 'Tambah',
            'view' => 'Lihat',
            'search' => 'Cari',
            'filter' => 'Filter',
            'sort' => 'Urut',
            'next' => 'Selanjutnya',
            'previous' => 'Sebelumnya',
            'total' => 'Total',
            'status' => 'Status',
            'actions' => 'Tindakan',
            'confirm' => 'Konfirmasi',
            'cancel' => 'Batal',
            'success' => 'Berhasil',
            'error' => 'Kesalahan',
            'warning' => 'Peringatan',
            'info' => 'Informasi',
            'details' => 'Detail',
            'more' => 'Lebih Banyak',
            'all' => 'Semua',
            
            // Settings page
            'map_settings' => 'Pengaturan Peta',
            'show_map_index' => 'Tampilkan peta di halaman indeks',
            'default_zoom' => 'Pembesaran Default Peta',
            'zoom_help' => 'Level pembesaran peta (4-8). Nilai lebih tinggi berarti pembesaran lebih besar.',
            'default_center' => 'Pusat Peta Default',
            'center_help' => 'Lokasi awal yang ditampilkan ketika peta dimuat.',
            'default_color' => 'Skema Warna Default',
            'by_patients' => 'Berdasarkan Jumlah Penderita',
            'by_deaths' => 'Berdasarkan Jumlah Kematian',
            'by_cluster' => 'Berdasarkan Cluster',
            'color_help' => 'Metrik yang digunakan untuk mewarnai area pada peta.',
            'map_display_help' => 'Pengaturan ini mengontrol tampilan dan perilaku peta wilayah.',
            'map_disabled' => 'Peta saat ini dinonaktifkan. Anda dapat mengaktifkannya di halaman Pengaturan.',
            'region_map' => 'Peta Distribusi Diabetes per Wilayah',
            'region_distribution' => 'Distribusi Wilayah',
            'map_not_available' => 'Peta tidak tersedia.',
            'coordinates_missing' => 'Koordinat untuk beberapa wilayah tidak ditemukan.',
            'map_by' => 'Warnai peta berdasarkan:',
            'region_coordinates' => 'Koordinat Wilayah',
            'manage_coordinates' => 'Kelola Koordinat Wilayah',
            'coordinates_help' => 'Kelola koordinat wilayah untuk menampilkan data pada peta.',
            'application_preferences' => 'Preferensi Aplikasi',
            'theme' => 'Tema',
            'light_theme' => 'Tema Terang',
            'dark_theme' => 'Tema Gelap',
            'theme_help' => 'Pilih tampilan antarmuka yang diinginkan.',
            'light_theme_desc' => 'Tampilan cerah, terbaik untuk penggunaan siang hari.',
            'dark_theme_desc' => 'Kecerahan berkurang, lebih baik untuk lingkungan cahaya rendah.',
            'language' => 'Bahasa',
            'language_help' => 'Pilih bahasa yang akan digunakan di seluruh aplikasi.',
            'language_description' => 'Pengaturan bahasa akan diterapkan di seluruh aplikasi. Anda dapat mengganti bahasa kapan saja.',
            'chart_display_options' => 'Opsi Tampilan Grafik',
            'chart_display' => 'Tampilan Grafik',
            'show_chart_index' => 'Tampilkan grafik di halaman indeks',
            'show_chart_dashboard' => 'Tampilkan grafik di dasbor',
            'chart_display_help' => 'Aktifkan atau nonaktifkan tampilan grafik di halaman yang berbeda.',
            'chart_display_description' => 'Anda dapat memilih apakah akan menampilkan grafik di halaman utama. Menonaktifkan grafik dapat meningkatkan kinerja halaman pada koneksi yang lebih lambat.',
            'default_sorting' => 'Pengurutan Default',
            'default_sort_help' => 'Pilih bagaimana data diurutkan secara default dalam daftar.',
            'default_sort_description' => 'Pengaturan ini menentukan urutan awal tabel data. Anda selalu dapat mengubah urutan saat melihat tabel.',
            'save_settings' => 'Simpan Pengaturan',
            'about_settings' => 'Tentang Pengaturan',
            'settings_description' => 'Pengaturan Anda disimpan dengan akun Anda dan diterapkan di semua halaman aplikasi. Perubahan tema akan berlaku setelah penyegaran halaman.',
            
            // Data fields and labels
            'region' => 'Wilayah',
            'population' => 'Jumlah Penduduk',
            'year' => 'Tahun',
            'patient_count' => 'Jumlah Penderita',
            'death_count' => 'Jumlah Kematian',
            'cluster' => 'Kluster',
            'low_risk' => 'Risiko Rendah',
            'medium_risk' => 'Risiko Sedang',
            'high_risk' => 'Risiko Tinggi',
            'undefined' => 'Tidak Terdefinisi',
            
            // Dashboard
            'dashboard_title' => 'Dasbor - Tahun',
            'data_overview' => 'Ikhtisar Data',
            'recent_activities' => 'Aktivitas Terbaru',
            'recent_clustering' => 'Klusterisasi Terbaru',
            'view_all' => 'Lihat Semua',
            'run_clustering' => 'Jalankan Klusterisasi',
            'manage_data' => 'Kelola Data',
            'cluster_distribution' => 'Distribusi Kluster',
            'top_regions' => 'Wilayah Teratas berdasarkan Jumlah Pasien',
            'charts_disabled' => 'Grafik saat ini dinonaktifkan. Anda dapat mengaktifkannya di halaman Pengaturan.',
            'change_settings' => 'Ubah Pengaturan',
            
            // DBSCAN Clustering
            'dbscan_parameters' => 'Parameter DBSCAN',
            'epsilon' => 'Epsilon (eps)',
            'min_samples' => 'Sampel Minimum',
            'select_year' => 'Pilih Tahun',
            'run_dbscan' => 'Jalankan Klusterisasi DBSCAN',
            'about_dbscan' => 'Tentang Algoritma DBSCAN',
            'dbscan_description' => 'DBSCAN (Density-Based Spatial Clustering of Applications with Noise) adalah algoritma klusterisasi yang mengelompokkan titik-titik yang berdekatan, menandai sebagai outlier titik-titik yang berada sendiri di daerah kepadatan rendah.',
            'dbscan_param_desc' => 'Parameter Utama:',
            'epsilon_desc' => 'Jarak maksimum antara dua titik agar dianggap sebagai tetangga.',
            'min_samples_desc' => 'Jumlah titik minimum yang diperlukan untuk membentuk area padat (cluster).',
            'clustering_data' => 'Data yang Digunakan untuk Klusterisasi:',
            'population_desc' => 'Jumlah penduduk total di wilayah',
            'patient_desc' => 'Jumlah pasien diabetes',
            'mortality_desc' => 'Jumlah kematian akibat diabetes',
            'cluster_interpret' => 'Interpretasi Kluster:',
            'low_risk_desc' => 'Tingkat keparahan lebih rendah (outlier atau titik noise)',
            'medium_risk_desc' => 'Tingkat keparahan sedang',
            'high_risk_desc' => 'Tingkat keparahan lebih tinggi',
            
            // Login
            'username' => 'Nama Pengguna',
            'password' => 'Kata Sandi',
            'captcha_verification' => 'Verifikasi CAPTCHA',
            'captcha_help' => 'Masukkan karakter yang ditampilkan dalam gambar di atas',
            'refresh_captcha' => 'Segarkan CAPTCHA',
            'admin_login' => 'Login Admin',
            'captcha_failed' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
            'login_failed' => 'Nama pengguna atau kata sandi salah',
            
            // Data management
            'add_data' => 'Tambah Data Baru',
            'edit_data' => 'Edit Data',
            'delete_data' => 'Hapus Data',
            'search_data' => 'Cari data...',
            'clear_search' => 'Hapus Pencarian',
            'no_data' => 'Tidak ada data yang ditemukan.',
            'confirm_delete' => 'Anda yakin ingin menghapus data ini?',
            'data_guidelines' => 'Panduan Input Data',
            'format_correct' => 'Format Data yang Benar:',
            
            // Input form
            'region_desc' => 'Nama provinsi atau kabupaten',
            'population_input' => 'Jumlah total populasi wilayah',
            'year_input' => 'Tahun data (2000-sekarang)',
            'patients_input' => 'Total kasus diabetes',
            'deaths_input' => 'Total kematian akibat diabetes',
            'input_desc' => 'Data yang dimasukkan akan digunakan untuk analisis clustering menggunakan algoritma DBSCAN untuk mengidentifikasi pola penyebaran diabetes.',
            'submit_data' => 'Kirim',
            'reset_form' => 'Reset',
            'update_record' => 'Perbarui Rekaman',
            'cancel_edit' => 'Batal',
            'back_to_list' => 'Kembali ke Daftar',
            'data_success' => 'Data berhasil ditambahkan!',
            'update_success' => 'Data berhasil diperbarui!',
            'delete_success' => 'Data berhasil dihapus!',
            'db_error' => 'Kesalahan Database: ',
            'record_not_found' => 'Rekaman tidak ditemukan.',
            'created_date' => 'Tanggal Pembuatan'
        ],
        
        // English translations
        'en' => [
            // General terms
            'settings' => 'Settings',
            'dashboard' => 'Dashboard',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'apply' => 'Apply',
            'back' => 'Back',
            'home' => 'Home',
            'logout' => 'Logout',
            'login' => 'Login',
            'register' => 'Register',
            'welcome' => 'Welcome',
            'yes' => 'Yes',
            'no' => 'No',
            'submit' => 'Submit',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'add' => 'Add',
            'view' => 'View',
            'search' => 'Search',
            'filter' => 'Filter',
            'sort' => 'Sort',
            'next' => 'Next',
            'previous' => 'Previous',
            'total' => 'Total',
            'status' => 'Status',
            'actions' => 'Actions',
            'confirm' => 'Confirm',
            'cancel' => 'Cancel',
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            'info' => 'Information',
            'details' => 'Details',
            'more' => 'More',
            'all' => 'All',
            
            // Settings page
            'map_settings' => 'Map Settings',
            'show_map_index' => 'Show map on index page',
            'default_zoom' => 'Default Map Zoom',
            'zoom_help' => 'Map zoom level (4-8). Higher values mean more zoomed in.',
            'default_center' => 'Default Map Center',
            'center_help' => 'Initial location displayed when the map loads.',
            'default_color' => 'Default Color Scheme',
            'by_patients' => 'By Patient Count',
            'by_deaths' => 'By Death Count',
            'by_cluster' => 'By Cluster',
            'color_help' => 'Metric used to color areas on the map.',
            'map_display_help' => 'These settings control the display and behavior of the region map.',
            'map_disabled' => 'Map is currently disabled. You can enable it in the Settings page.',
            'region_map' => 'Diabetes Distribution Map by Region',
            'region_distribution' => 'Region Distribution',
            'map_not_available' => 'Map not available.',
            'coordinates_missing' => 'Coordinates for some regions are missing.',
            'map_by' => 'Color map by:',
            'region_coordinates' => 'Region Coordinates',
            'manage_coordinates' => 'Manage Region Coordinates',
            'coordinates_help' => 'Manage region coordinates to display data on the map',
            'application_preferences' => 'Application Preferences',
            'theme' => 'Theme',
            'light_theme' => 'Light Theme',
            'dark_theme' => 'Dark Theme',
            'theme_help' => 'Choose your preferred interface appearance.',
            'light_theme_desc' => 'Default bright appearance, best for daylight use.',
            'dark_theme_desc' => 'Reduced brightness, better for low-light environments.',
            'language' => 'Language',
            'language_help' => 'Select the language to be used throughout the application.',
            'language_description' => 'The language setting will be applied across the entire application. You can change the language at any time.',
            'chart_display_options' => 'Chart Display Options',
            'chart_display' => 'Chart Display',
            'show_chart_index' => 'Show chart on index page',
            'show_chart_dashboard' => 'Show charts on dashboard',
            'chart_display_help' => 'Toggle chart visibility on different pages.',
            'chart_display_description' => 'You can choose whether to display charts on the main pages. Disabling charts can improve page load performance on slower connections.',
            'default_sorting' => 'Default Sorting',
            'default_sort_help' => 'Choose how data is sorted by default in listings.',
            'default_sort_description' => 'This setting determines the initial sort order for data tables. You can always change the sort order while viewing tables.',
            'save_settings' => 'Save Settings',
            'about_settings' => 'About Settings',
            'settings_description' => 'Your settings are stored with your account and are applied across all pages of the application. Theme changes will take effect after page refresh.',
            
            // Data fields and labels
            'region' => 'Region',
            'population' => 'Population',
            'year' => 'Year',
            'patient_count' => 'Patient Count',
            'death_count' => 'Death Count',
            'cluster' => 'Cluster',
            'low_risk' => 'Low Risk',
            'medium_risk' => 'Medium Risk',
            'high_risk' => 'High Risk',
            'undefined' => 'Undefined',
            
            // Dashboard
            'dashboard_title' => 'Dashboard - Year',
            'data_overview' => 'Data Overview',
            'recent_activities' => 'Recent Activities',
            'recent_clustering' => 'Recent Clustering',
            'view_all' => 'View All',
            'run_clustering' => 'Run Clustering',
            'manage_data' => 'Manage Data',
            'cluster_distribution' => 'Cluster Distribution',
            'top_regions' => 'Top Regions by Patient Count',
            'charts_disabled' => 'Charts are currently disabled. You can enable them in the Settings page.',
            'change_settings' => 'Change Settings',
            
            // DBSCAN Clustering
            'dbscan_parameters' => 'DBSCAN Parameters',
            'epsilon' => 'Epsilon (eps)',
            'min_samples' => 'Minimum Samples',
            'select_year' => 'Select Year',
            'run_dbscan' => 'Run DBSCAN Clustering',
            'about_dbscan' => 'About DBSCAN Algorithm',
            'dbscan_description' => 'DBSCAN (Density-Based Spatial Clustering of Applications with Noise) is a clustering algorithm that groups together points that are closely packed together, marking as outliers points that lie alone in low-density regions.',
            'dbscan_param_desc' => 'Key Parameters:',
            'epsilon_desc' => 'The maximum distance between two points for them to be considered neighbors.',
            'min_samples_desc' => 'The minimum number of points required to form a dense region (cluster).',
            'clustering_data' => 'Data Used for Clustering:',
            'population_desc' => 'Total population in the region',
            'patient_desc' => 'Number of diabetes patients',
            'mortality_desc' => 'Number of deaths due to diabetes',
            'cluster_interpret' => 'Cluster Interpretation:',
            'low_risk_desc' => 'Lower severity (outliers or noise points)',
            'medium_risk_desc' => 'Medium severity',
            'high_risk_desc' => 'Higher severity',
            
            // Login
            'username' => 'Username',
            'password' => 'Password',
            'captcha_verification' => 'CAPTCHA Verification',
            'captcha_help' => 'Enter the characters shown in the image above',
            'refresh_captcha' => 'Refresh CAPTCHA',
            'admin_login' => 'Admin Login',
            'captcha_failed' => 'CAPTCHA verification failed. Please try again.',
            'login_failed' => 'Username or password is incorrect',
            
            // Data management
            'add_data' => 'Add New Data',
            'edit_data' => 'Edit Data',
            'delete_data' => 'Delete Data',
            'search_data' => 'Search data...',
            'clear_search' => 'Clear Search',
            'no_data' => 'No data records found.',
            'confirm_delete' => 'Are you sure you want to delete this record?',
            'data_guidelines' => 'Data Input Guidelines',
            'format_correct' => 'Correct Data Format:',
            
            // Input form
            'region_desc' => 'Province or district name',
            'population_input' => 'Total region population',
            'year_input' => 'Data year (2000-present)',
            'patients_input' => 'Total diabetes cases',
            'deaths_input' => 'Total deaths due to diabetes',
            'input_desc' => 'The data entered will be used for clustering analysis using the DBSCAN algorithm to identify diabetes distribution patterns.',
            'submit_data' => 'Submit',
            'reset_form' => 'Reset',
            'update_record' => 'Update Record',
            'cancel_edit' => 'Cancel',
            'back_to_list' => 'Back to List',
            'data_success' => 'Data successfully added!',
            'update_success' => 'Data updated successfully!',
            'delete_success' => 'Record deleted successfully!',
            'db_error' => 'Database Error: ',
            'record_not_found' => 'Record not found.',
            'created_date' => 'Created Date'
        ]
   ];
        
        // Return the requested language array
        return $translations[$lang_code];
    }
}

// Conditionally define getText function if not already defined
if (!function_exists('getText')) {
    /**
     * Get translated text for a specific key
     * 
     * @param string $key The translation key
     * @param string $lang_code The language code
     * @return string The translated text
     */
    function getText($key, $lang_code = null) {
        // Use session language if not specified
        if ($lang_code === null) {
            $lang_code = isset($_SESSION['language']) ? $_SESSION['language'] : 'id';
        }
        
        // Load language array
        $lang = loadLanguage($lang_code);
        
        // Return translation or key if not found
        return isset($lang[$key]) ? $lang[$key] : $key;
    }
}