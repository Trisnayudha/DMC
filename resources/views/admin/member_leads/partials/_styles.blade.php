<style>
    .lead-management { --lead-blue: #6777ef; color: #34395e; }
    .lead-list-card .card-header { justify-content:space-between; gap:12px; flex-wrap:wrap; padding:16px 20px; min-height:0; }
    .lead-list-card .card-header h4 { margin:0 0 4px; }
    .lead-list-card .card-body { padding:16px 20px; }
    .lead-heading-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .lead-management .lead-list-card { margin-bottom:12px; }
    .lead-management .bulk-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:38px; padding:8px 14px; border:1px solid #cbd6ff; border-radius:6px; background:#fff; color:var(--lead-blue); font-size:12px; font-weight:700; cursor:pointer; }
    .lead-management .bulk-btn-primary { background:var(--lead-blue); border-color:var(--lead-blue); color:#fff; box-shadow:0 2px 5px #6777ef22; }
    .lead-management .bulk-btn:disabled { opacity:.6; cursor:not-allowed; }
    .lead-summary { display:grid; grid-template-columns:repeat(9,minmax(0,1fr)); gap:10px; margin-bottom:16px; }
    .lead-stat { display:flex; align-items:center; gap:8px; padding:12px 10px; min-width:0; border:1px solid #e8ebf1; border-radius:3px; box-shadow:0 4px 8px #00000008; background:#fff; color:#34395e; transition:box-shadow .15s,border-color .15s; }
    .lead-stat:hover { text-decoration:none; color:#1e2943; border-color:#bdcaff; box-shadow:0 3px 12px #2639610d; }
    .lead-stat-icon { display:flex; align-items:center; justify-content:center; width:30px; height:34px; flex-shrink:0; font-size:21px; color:var(--stat-color); background:var(--stat-bg); border-radius:7px; }
    .lead-stat-icon i { font-size:21px; margin:0 !important; }
    .lead-stat-blue { --stat-color:#6777ef; --stat-bg:#eff3ff; }
    .lead-stat-green { --stat-color:#0eb56c; --stat-bg:#e8faf0; }
    .lead-stat-red,.lead-stat-hot { --stat-color:#f13450; --stat-bg:#fff0f2; }
    .lead-stat-amber { --stat-color:#f0a300; --stat-bg:#fff8df; }
    .lead-stat-cyan { --stat-color:#20a8df; --stat-bg:#edf9ff; }
    .lead-stat-content { flex:1; min-width:0; }
    .lead-stat-label { display:block; font-size:11px; font-weight:700; white-space:nowrap; margin-bottom:4px; }
    .lead-stat-value { display:flex; justify-content:space-between; align-items:baseline; gap:6px; font-size:21px; font-weight:800; line-height:1.2; }
    .lead-stat-value small { color:#7a8497; font-size:11px; font-weight:400; }
    .lead-filters { margin-bottom:10px; }
    .lead-filters .lf-row { display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
    .lead-filters .lf-field { display:flex; align-items:center; gap:8px; min-width:0; height:36px; margin:0; padding:0 10px; background:#fff; border:1px solid #e2e7f0; border-radius:6px; }
    .lead-filters .lf-field>i { color:#6f80a1; font-size:13px; }
    .lead-filters .lf-field>span { font-size:12px; color:#738096; }
    .lead-filters .lf-field input,.lead-filters .lf-field select { min-width:0; height:34px; border:0; background:transparent; color:#34405b; font-size:12px; outline:none; }
    .lead-filters .lf-field select { max-width:170px; cursor:pointer; }
    .lead-filters .lf-search { flex:1 1 240px; }
    .lead-filters .lf-search input { width:100%; }
    .lead-filters .lf-field:focus-within { outline:2px solid #a5b8ff; outline-offset:1px; }
    .lead-filters .lf-clear { color:var(--lead-blue); font-size:11px; font-weight:700; padding:6px; margin-left:auto; white-space:nowrap; }
    .lead-filters .lf-more,.lead-filters .lf-apply { min-height:36px; border:1px solid #cfdaff; border-radius:6px; color:var(--lead-blue); background:#fff; font-size:12px; font-weight:600; padding:6px 12px; cursor:pointer; }
    .lead-filters .lf-apply { background:#eef3ff; }
    .lead-filters .lf-advanced { padding:12px; margin-top:8px; border:1px solid #e2e7f0; border-radius:6px; background:#f7f9fd; }
    .lead-table-toolbar,.lead-utilities,.lead-management .bulk-bar { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
    .lead-table-toolbar { justify-content:space-between; margin:10px 0; color:#7b8599; font-size:11px; }
    .lead-toolbar-divider { padding:0 7px; color:#b3bdce; }
    .lead-management .bulk-btn-link { border:0; padding:0; background:none; color:var(--lead-blue); cursor:pointer; }
    .lead-management .lead-utilities .btn { font-size:11px; padding:5px 10px; box-shadow:none; }
    .lead-sla-count { color:#b86628; }
    .lead-table-wrap { border:1px solid #e7eaf1; border-radius:7px; background:#fff; }
    .lead-management .lead-table { width:100%; min-width:1560px; table-layout:fixed; margin:0; border-collapse:separate; border-spacing:0; }
    .lead-management .lead-table thead th { background:#f3f5f8; color:#35415a; font-size:10px; font-weight:700; white-space:nowrap; vertical-align:middle; border-top:0; border-bottom:1px solid #e7eaf1; border-right:1px solid #fff; padding:8px; height:auto; }
    .lead-management .lead-table thead th i { color:#6777ef; margin-right:4px; }
    .lead-management .lead-table tbody td { font-size:11px; line-height:1.45; color:#536078; padding:7px 8px; height:auto; border-top:0; border-bottom:1px solid #edf0f5; border-right:1px solid #f3f5f9; vertical-align:middle; }
    .lead-management .lead-table tbody tr:last-child td { border-bottom:0; }
    .lead-management .lead-table .lead-excluded { background:#fff0f1; }
    .lead-management .lead-table tbody tr:hover { background:#f5f8ff; }
    .lead-management .lead-table .lead-excluded:hover { background:#ffe7e9; }
    .lead-table input[type=checkbox] { accent-color:#6777ef; width:13px; height:13px; vertical-align:middle; cursor:pointer; }
    .lead-table .cell-truncate { display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle; }
    .lead-table .font-weight-bold { color:#26334e; }
    .lead-table .lead-note { display:inline-block; vertical-align:middle; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .lead-table .btn-icon { width:21px; height:21px; padding:0; display:inline-flex; align-items:center; justify-content:center; font-size:13px; border-radius:4px; margin-left:3px; }
    .lead-channel { color:#6777ef; }
    .lead-management .badge { border-radius:6px; font-size:10px; font-weight:700; line-height:1.4; padding:5px 9px; white-space:nowrap; }
    .lead-management .badge-success { background:#d9fae9; color:#13965b; }
    .lead-management .badge-warning { background:#fff1c1; color:#b9780b; }
    .lead-management .badge-danger { background:#ffe2e6; color:#dc3652; }
    .temp-hot { background:#0fb56c; color:#fff; }
    .temp-warm { background:#ffe9a2; color:#9d670d; }
    .temp-cold,.lead-badge-excluded { background:#e3e8f0; color:#47536a; }
    .lead-management a.temp-hot:hover { color:#fff; background:#099a5b; }
    .lead-management .temp-badge,.lead-table .lead-badge-excluded { min-width:68px; text-align:center; }
    .lead-step,.lead-next-action { display:flex; align-items:center; gap:7px; }
    .lead-step>i,.lead-next-action>i { font-size:15px; }
    .lead-step strong,.lead-next-action strong { display:block; font-size:10px; }
    .lead-step small,.lead-next-action small { display:block; color:#667187; font-size:10px; }
    .lead-step-sent { color:#0ca565; }
    .lead-step-overdue { color:#de4558; }
    .lead-step-empty,.lead-dash { color:#a4adbd; }
    .lead-next-action { padding:0; border:0; background:none; color:#6777ef; cursor:pointer; text-align:left; }
    .lead-next-action:hover strong { text-decoration:underline; }
    .lead-table .dropdown-menu { font-size:12px; }
    .lead-legends { display:grid; grid-template-columns:1.15fr 1.05fr 1.1fr 1.15fr .9fr; gap:8px; margin-top:12px; }
    .lead-legend { padding:12px; border:1px solid #e2eaf6; border-radius:6px; background:#f0f6ff; font-size:10px; line-height:1.45; color:#788399; }
    .lead-legend h3 { font-size:12px; font-weight:800; color:#3266cf; margin:0 0 3px; }
    .lead-legend p { font-size:10px; line-height:1.4; margin:0 0 6px; }
    .lead-legend>div { display:flex; align-items:center; gap:8px; margin-top:4px; }
    .lead-legend .badge { min-width:90px; padding:2px 6px; font-size:9px; text-align:left; }
    .lead-legend .lead-legend-note { margin-top:8px; margin-bottom:0; }
    .legend-purple { background:#f7f2ff; border-color:#ece4fa; }.legend-purple h3 { color:#7950c5; }
    .legend-green { background:#f0faf5; border-color:#e0f0e8; }.legend-green h3 { color:#218b61; }
    .legend-red { background:#fff2f3; border-color:#fae3e6; }.legend-red h3 { color:#df4055; }
    .lead-legend ul { margin:0; padding-left:16px; }.lead-legend li { line-height:1.5; margin:0; padding:0; }.legend-red li::marker { color:#df4055; }
    .lead-pagination { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding-top:14px; }
    .lead-page-info { display:flex; align-items:center; gap:8px; color:#78828a; font-size:12px; }
    .lead-page-info label,.lead-pagination .pagination { margin:0; }
    .lead-page-size { border:1px solid #e4e6fc; border-radius:3px; padding:5px 8px; background:#fff; color:#34395e; }
    .lead-guide { border:1px solid #e8ebf1; border-radius:3px; padding:10px 14px; background:#fff; margin-bottom:16px; }
    .lead-guide summary { cursor:pointer; color:#6777ef; font-size:12px; font-weight:600; }
    @media(min-width:1500px) { .lead-stat:nth-child(3),.lead-stat:nth-child(6) { margin-right:5px; } }
    @media(max-width:1499px) { .lead-summary { grid-template-columns:repeat(5,minmax(0,1fr)); }.lead-legends { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media(max-width:991px) { .lead-summary { grid-template-columns:repeat(3,minmax(0,1fr)); }.lead-legends { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:575px) { .lead-pagination .page-item:not(:first-child):not(:last-child):not(.active) { display:none; } .lead-list-card .card-header,.lead-list-card .card-body { padding:12px; }.lead-page-info { flex-wrap:wrap; }.lead-pagination { justify-content:center; } .lead-summary { grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }.lead-stat { padding:10px 8px; }.lead-stat-icon { display:none; }.lead-stat-label { font-size:10px; }.lead-stat-value { font-size:20px; }.lead-heading-actions { width:100%; }.lead-heading-actions .bulk-btn { flex:1 1 auto; }.lead-legends { grid-template-columns:1fr; }.lead-filters .lf-search { flex-basis:100%; }.lead-filters .lf-field:not(.lf-search) { flex:1 1 44%; }.lead-filters .lf-field select { width:100%; max-width:none; }.lead-utilities { gap:7px; }.lead-table-toolbar { align-items:flex-start; } }
</style>
