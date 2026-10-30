{{-- Gaya modal detail kategori (reg-*) + daftar Company Category Tier; dipakai Members & Company Database. --}}
<style>
    /* Source Breakdown modal — same compact/modern language as the Members
       DMC and Follow-Up tables (sticky header, dense rows, hover, truncate). */
    .reg-stat-row {
        display: flex;
        gap: 12px;
    }
    .reg-stat-card {
        flex: 1;
        border-radius: 10px;
        padding: 14px 16px;
        text-align: center;
    }
    .reg-stat-total { background: #eef2ff; }
    .reg-stat-leads { background: #fff6e6; }
    .reg-stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.1;
        color: #2a2f45;
    }
    .reg-stat-leads .reg-stat-value { color: #b3720c; }
    .reg-stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        margin-top: 4px;
    }
    .reg-stat-sub {
        font-size: 11px;
        color: #6c757d;
        margin-top: 6px;
    }
    .reg-stat-sub .viable { color: #0ca30c; font-weight: 600; }
    .reg-stat-sub .dead { color: #d03b3b; font-weight: 600; }

    .reg-section-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #adb5bd;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .reg-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .reg-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: #f4f5f9;
        font-size: 12px;
        font-weight: 600;
        color: #3c4257;
    }
    .reg-chip i, .reg-chip .reg-chip-dot { font-size: 10px; }
    .reg-chip .reg-chip-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .reg-chip .reg-chip-pct { color: #8a8fa3; font-weight: 500; }

    .reg-status-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        padding: .2em .5em;
        border-radius: 4px;
        color: #fff;
    }

    .reg-search-wrap { position: relative; }
    .reg-search-wrap i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        color: #adb5bd;
    }
    .reg-search-wrap input {
        width: 240px;
        padding-left: 28px;
    }

    .reg-breakdown-table-wrap {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #eef0f4;
        border-radius: 8px;
    }
    .reg-breakdown-table-wrap table {
        border-collapse: separate !important;
        font-size: 12px;
        margin-bottom: 0;
    }
    .reg-breakdown-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fc;
        border-top: none !important;
        border-bottom: 2px solid #e3e6f0 !important;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        padding: .5rem .6rem;
    }
    .reg-breakdown-table-wrap tbody td {
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        padding: .4rem .6rem;
    }
    .reg-breakdown-table-wrap tbody tr:hover { background-color: #f7f9fc; }
    .reg-breakdown-table-wrap .cell-truncate {
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Company Category: daftar semua kategori di samping pie — tiap baris bisa diklik. */
    .company-category-list {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0;
    }
    .company-category-item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 5px 8px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        font-size: 12px;
        color: #3c4257;
        text-align: left;
        cursor: pointer;
    }
    .company-category-item:hover,
    .company-category-item:focus { background: #f4f5f9; outline: none; }
    .company-category-item .cc-dot {
        flex-shrink: 0;
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }
    .company-category-item .cc-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .company-category-item .cc-count { font-weight: 700; }
    .cc-tier-group { margin-bottom: 6px; }
    .company-category-item.cc-tier .cc-name { font-weight: 700; white-space: normal; }
    .company-category-item.cc-sub { padding: 1px 8px 1px 26px; font-size: 11.5px; color: #6c757d; }
    .company-category-item.cc-sub .cc-count { font-weight: 600; }
    .company-category-item .cc-pct { color: #8a8fa3; width: 44px; text-align: right; }
    .cc-filter-chip { cursor: pointer; border: 1px solid transparent; }
    .cc-filter-chip.active { border-color: #2a78d6; background: #eaf2fd; color: #1c5cab; }
    @media (max-width: 575.98px) {
        .company-category-list { grid-template-columns: minmax(0, 1fr); }
        .reg-stat-row { flex-direction: column; }
        .reg-search-wrap input { width: 100%; }
    }
</style>
