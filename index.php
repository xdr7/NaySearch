<?php
// ========== KONFIGURASI API ==========
$accessKey = 'j-FfjyHZBJ9FqB6xsNRdJYWy34LpubuyGke-GyMYaWE'; // Ganti dengan access key Anda dari https://unsplash.com/developers

// Inisialisasi
$searchQuery = '';
$location = '';
$images = [];
$error = '';

// ========== PROSES FORM ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchQuery = isset($_POST['search'])  ? trim($_POST['search'])  : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';

    if ($searchQuery !== '' || $location !== '') {
        // Bangun query dengan lokasi eksplisit
        $query = urlencode(trim("$searchQuery $location"));
        $apiUrl = "https://api.unsplash.com/search/photos?query=$query&per_page=30&client_id=$accessKey";

        $response = @file_get_contents($apiUrl);
        if ($response !== false) {
            $data = json_decode($response, true);
            $images = $data['results'] ?? [];
        } else {
            $error = 'Gagal mengambil data. Periksa koneksi & API key.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naysearch - Pencarian Gambar Lokasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:#ff6b6b; --secondary:#4ecdc4; --accent:#ffe66d;
            --dark:#292f36;    --light:#f7fff7;
        }
        body{background:linear-gradient(135deg,var(--light) 0%,#e8f5e9 100%);min-height:100vh}
        .naysearch-title{
            font-size:3.5rem;font-weight:bold;
            background:linear-gradient(45deg,var(--primary),var(--secondary),var(--accent));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;
            animation:colorShift 3s ease-in-out infinite;
        }
        @keyframes colorShift{0%,100%{filter:hue-rotate(0deg)}50%{filter:hue-rotate(180deg)}}
        .search-container{background:rgba(255,255,255,.9);border-radius:20px;padding:2rem}
        .btn-custom{background:linear-gradient(45deg,var(--primary),var(--secondary));border:none;color:#fff}
        .image-card{transition:transform .3s;border-radius:15px;overflow:hidden}
        .image-card:hover{transform:translateY(-5px)}
        .image-card img{width:100%;height:200px;object-fit:cover}
        .footer{background:var(--dark);color:var(--light);padding:1rem 0;margin-top:auto}
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="text-center py-4">
        <h1 class="naysearch-title"><i class="fas fa-search-location"></i> Naysearch</h1>
        <p class="text-muted">Temukan gambar berdasarkan lokasi dengan mudah</p>
    </header>

    <!-- FORM PENCARIAN -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="search-container">
                    <form method="POST" id="searchForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-image"></i> Kata Kunci</label>
                                <input type="text" name="search" class="form-control form-control-lg"
                                       placeholder="Contoh: beach, mountain, city"
                                       value="<?=htmlspecialchars($searchQuery)?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                                <input type="text" name="location" class="form-control form-control-lg"
                                       placeholder="Contoh: Bali, Tokyo, Paris"
                                       value="<?=htmlspecialchars($location)?>">
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-custom btn-lg px-5"><i class="fas fa-search"></i> Cari Gambar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- LOADING -->
    <div class="text-center mt-4" id="loadingSpinner" style="display:none">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Sedang mencari gambar...</p>
    </div>

    <!-- ERROR / RESULTS -->
    <?php if($error):?>
        <div class="container mt-4">
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
        </div>
    <?php elseif(!empty($images)):?>
        <div class="container mt-5">
            <h5 class="text-center mb-4">
                Hasil untuk: <strong><?=htmlspecialchars("$searchQuery $location")?></strong>
            </h5>
            <div class="row g-4">
                <?php foreach($images as $img):?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="image-card shadow">
                            <img src="<?=$img['urls']['regular']?>" alt="<?=$img['alt_description']??'Gambar'?>" loading="lazy">
                            <div class="p-2">
                                <small class="d-block text-truncate">
                                    <i class="fas fa-user"></i> <?=$img['user']['name']?>
                                </small>
                                <?php if(!empty($img['location']['title'])):?>
                                    <small class="d-block text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?=$img['location']['title']?>
                                    </small>
                                <?php endif;?>
                                <a href="<?=$img['links']['html']?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="fas fa-external-link-alt"></i> Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    <?php elseif($_SERVER['REQUEST_METHOD']==='POST'):?>
        <div class="container mt-4">
            <div class="alert alert-info text-center">Tidak ada gambar ditemukan.</div>
        </div>
    <?php endif;?>

    <!-- FOOTER -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <small>&copy; <?=date('Y')?> Naysearch - Created by Asmaul Asni Subegi | Powered by <a href="https://unsplash.com" class="text-light">Unsplash</a></small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchForm').addEventListener('submit',()=>{
            document.getElementById('loadingSpinner').style.display='block';
        });
    </script>
</body>
</html>