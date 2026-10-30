{{-- Company Category Tier dari Company Database — hitungan company unik, bukan member,
     dikelompokkan ke 6 grup / Category 1–6 (App\Support\CompanyCategoryTier). Klik slice / grup / kategori
     → modal category detail + subcategory + daftar company. --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Company Category Tier
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Jumlah company unik yang sudah verified dari Company Database (dedup per nama company, sama seperti tab Verified di halaman Company Database) — bukan jumlah member — dikelompokkan ke 6 grup (Category 1–6, sama dengan Dashboard & Event Detail). Klik grup atau kategori untuk lihat category detail, subcategory & daftar company."></i>
                </h4>
                <span class="badge badge-light" id="company-category-total">—</span>
            </div>
            <div class="card-body">
                <div id="company-category-loading" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm mr-2"></span>Memuat...
                </div>
                <div id="company-category-content" class="row align-items-center" style="display:none;">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div style="position:relative; height:460px;">
                            <canvas id="companyCategoryChart" role="img" style="cursor:pointer;"
                                aria-label="Jumlah company per company category tier"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="company-category-list" id="company-category-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /company category row --}}
