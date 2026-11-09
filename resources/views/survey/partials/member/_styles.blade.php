<style>
    :root {
        --dmc-red: #c8102e;
        --dmc-red-dark: #93081f;
        --dmc-red-light: #fff1f3;
        --dmc-red-subtle: #ffe4e8;
        --dmc-red-border: #fecdd3;
        --bg-main: #f3f4f8;
        --bg-panel: #ffffff;
        --bg-field: #f7f8fb;
        --border-panel: #e6e8ef;
        --border-input: #d7dce5;
        --text-primary: #111827;
        --text-secondary: #4b5563;
        --text-muted: #9ca3af;
        --side-width: 420px;
        --r: 8px;
        --gold: #eedec5;
        --gold-strong: #eedec5;
        --gold-muted: rgba(238, 222, 197, .7);
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
    }

    body {
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        background: var(--bg-main);
        -webkit-font-smoothing: antialiased;
    }

    /* =========================================================
       Shell: dark sticky sidebar + light main column
       ========================================================= */
    .survey-shell {
        display: flex;
        min-height: 100vh;
    }

    .survey-side {
        position: sticky;
        top: 0;
        flex: 0 0 var(--side-width);
        width: var(--side-width);
        height: 100vh;
        overflow-y: auto;
        color: #fff;
        background: #5f0710;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, .2) transparent;
    }

    /* Wave artwork (inline SVG) pinned behind the sidebar content */
    .side-art {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--side-width);
        height: 100vh;
        display: block;
        pointer-events: none;
    }

    .side-inner {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 28px;
        min-height: 100%;
        padding: 36px 36px 32px;
    }

    .side-brand img {
        height: 46px;
    }

    .survey-main {
        flex: 1;
        min-width: 0;
    }

    .main-inner {
        max-width: 1440px;
        margin: 0 auto;
        padding: 36px 48px 0;
    }

    .survey-shell.is-done .main-inner {
        padding-bottom: 48px;
    }

    /* =========================================================
       Intro (sidebar)
       ========================================================= */
    .survey-intro {
        font-size: .88rem;
        line-height: 1.65;
        color: rgba(255, 255, 255, .72);
    }

    .survey-intro p {
        margin-bottom: 14px;
    }

    .survey-intro .intro-lead {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 400;
        line-height: 1.55;
        color: #fff;
        text-wrap: balance;
    }

    /* Avoid single-word last lines across the intro */
    .survey-intro p,
    .survey-intro li {
        text-wrap: pretty;
    }

    .survey-intro .intro-list-title {
        margin-bottom: 10px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--gold-strong);
    }

    .survey-intro ul {
        list-style: none;
        padding: 0;
        margin: 0 0 16px;
        display: grid;
        gap: 10px;
    }

    .survey-intro li {
        position: relative;
        padding-left: 30px;
        color: var(--gold);
    }

    .survey-intro li::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 3px;
        width: 19px;
        height: 19px;
        border-radius: 4px;
        background: rgba(238, 222, 197, .14);
        color: var(--gold-strong);
        font-size: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .survey-intro .intro-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: var(--r);
        background: rgba(238, 222, 197, .06);
        border: 1px solid rgba(238, 222, 197, .25);
        font-size: .82rem;
        color: var(--gold);
    }

    .survey-intro .intro-thanks {
        margin: 0;
        font-style: italic;
        font-size: .82rem;
        color: var(--gold-muted);
    }

    /* =========================================================
       Main column
       ========================================================= */
    .survey-banner {
        overflow: hidden;
        border-radius: var(--r);
        box-shadow: 0 2px 8px rgba(17, 24, 39, .12);
    }

    .survey-banner img {
        display: block;
        width: 100%;
        height: auto;
    }

    /* ---------- Step cards ----------
       Flex-wrap + nowrap: every label stays on one line; cards wrap to
       a new row instead of truncating when space runs out. */
    .stepper {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 28px 0 32px;
        scroll-margin-top: 24px;
    }

    .stepper-item {
        position: relative;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 16px 14px;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
        color: var(--text-muted);
        background: #fff;
        border: 1px solid var(--border-panel);
        border-radius: var(--r);
        transition: border-color .25s, box-shadow .25s, color .25s;
    }

    /* Top strip */
    .stepper-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #e4e7ee;
        transition: background .3s;
    }

    /* Status dot, placed before the label */
    .stepper-dot {
        order: -1;
        flex-shrink: 0;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        border: 2px solid #d3d7e0;
        background: #fff;
        transition: all .25s;
    }

    .stepper-text {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .stepper-eyebrow {
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .stepper-item.active .stepper-eyebrow {
        color: var(--dmc-red);
    }

    .stepper-item.active {
        color: var(--text-primary);
        border-color: var(--dmc-red);
        box-shadow: 0 0 0 1px var(--dmc-red), 0 8px 20px -10px rgba(200, 16, 46, .35);
    }

    .stepper-item.active .stepper-bar,
    .stepper-item.done .stepper-bar {
        background: var(--dmc-red);
    }

    .stepper-item.active .stepper-dot {
        border-color: var(--dmc-red);
        box-shadow: 0 0 0 3px var(--dmc-red-subtle);
    }

    .stepper-item.done {
        color: var(--text-secondary);
    }

    .stepper-item.done .stepper-dot {
        border-color: var(--dmc-red);
        background: var(--dmc-red);
    }

    #member-survey-form {
        color: var(--text-primary);
    }

    .step-pane {
        display: none;
        scroll-margin-top: 24px;
    }

    .step-pane.active {
        display: block;
        animation: slideIn .35s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .step-head {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 2px solid var(--text-primary);
    }

    .panel-header-title {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0;
        color: var(--text-primary);
    }

    /* ---------- Step 1: fields inside one panel ---------- */
    .step-pane > .row {
        margin: 0;
        padding: 26px 14px 12px;
        background: var(--bg-panel);
        border: 1px solid var(--border-panel);
        border-radius: var(--r);
        box-shadow: 0 1px 2px rgba(17, 24, 39, .04);
    }

    /* ---------- Steps 2-4: each question is a card ---------- */
    .step-pane > .form-group {
        position: relative;
        margin-bottom: 16px;
        padding: 22px 24px 20px;
        background: var(--bg-panel);
        border: 1px solid var(--border-panel);
        border-radius: var(--r);
        box-shadow: 0 1px 2px rgba(17, 24, 39, .04);
        transition: border-color .2s, box-shadow .2s;
    }

    .step-pane > .form-group::before {
        content: '';
        position: absolute;
        left: -1px;
        top: -1px;
        bottom: -1px;
        width: 3px;
        border-radius: 0;
        background: transparent;
        transition: background .2s;
    }

    .step-pane > .form-group:focus-within {
        border-color: #cfd4de;
        box-shadow: 0 2px 6px rgba(17, 24, 39, .06);
    }

    .step-pane > .form-group:focus-within::before {
        background: var(--dmc-red);
    }

    .step-pane > .form-group:has(.is-invalid) {
        border-color: #fca5a5;
    }

    .step-pane > .form-group:has(.is-invalid)::before {
        background: #ef4444;
    }

    .step-pane > .form-group > label {
        margin-bottom: 14px;
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.45;
        color: var(--text-primary);
    }

    /* ---------- Fields ---------- */
    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-group label small {
        font-family: 'Inter', sans-serif;
        font-weight: 500;
        color: var(--text-muted);
    }

    .req-star {
        margin-left: 2px;
        font-weight: 800;
        color: var(--dmc-red);
    }

    .form-control-dark {
        width: 100%;
        height: 46px;
        padding: 10px 14px;
        font-size: .92rem;
        color: var(--text-primary);
        background: #fff;
        border: 1px solid var(--border-input);
        border-radius: var(--r);
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    textarea.form-control-dark {
        height: auto;
        min-height: 110px;
        line-height: 1.6;
        resize: vertical;
    }

    .form-control-dark:hover {
        border-color: #b9c0cc;
    }

    .form-control-dark:focus {
        background: #fff;
        border-color: var(--dmc-red);
        box-shadow: 0 0 0 3px rgba(200, 16, 46, .1);
    }

    .form-control-dark::placeholder {
        color: var(--text-muted);
    }

    .form-control-dark.is-invalid {
        border-color: #ef4444 !important;
        background: #fff7f7;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .08);
    }

    .invalid-feedback-custom {
        display: none;
        margin-top: 7px;
        font-size: .76rem;
        font-weight: 600;
        color: #dc2626;
    }

    .invalid-feedback-custom::before {
        content: '\f06a';
        margin-right: 6px;
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
    }

    .form-control-dark.is-invalid ~ .invalid-feedback-custom,
    .iti.is-invalid ~ .invalid-feedback-custom,
    .choice-grid.is-invalid + .invalid-feedback-custom {
        display: block;
    }

    @keyframes shakeInput {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
    }

    .shake-element {
        animation: shakeInput .4s ease-in-out;
    }

    /* ---------- Choice tiles ---------- */
    .choice-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .choice-grid-1 {
        grid-template-columns: 1fr;
    }

    .form-group label.choice-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 48px;
        margin: 0;
        padding: 11px 14px;
        font-family: 'Inter', sans-serif;
        font-size: .87rem;
        font-weight: 500;
        line-height: 1.4;
        color: var(--text-secondary);
        background: #fff;
        border: 1px solid var(--border-input);
        border-radius: var(--r);
        cursor: pointer;
        user-select: none;
        transition: all .2s;
    }

    .form-group label.choice-card:hover {
        background: #fafbfc;
        border-color: #9aa3b2;
        color: var(--text-primary);
    }

    .choice-card input {
        display: none;
    }

    .choice-indicator {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1.5px solid #9aa3b2;
        border-radius: 4px;
        transition: all .2s;
    }

    .choice-indicator.is-radio {
        border-radius: 50%;
    }

    .form-group label.choice-card.active {
        background: var(--dmc-red-light);
        border-color: var(--dmc-red);
        color: var(--text-primary);
        font-weight: 600;
        box-shadow: inset 3px 0 0 var(--dmc-red);
    }

    .choice-card.active .choice-indicator {
        background: var(--dmc-red);
        border-color: var(--dmc-red);
    }

    .choice-card.active .choice-indicator::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 10px;
        color: #fff;
    }

    .choice-card.active .choice-indicator.is-radio {
        background: #fff;
        border-width: 5px;
    }

    .choice-card.active .choice-indicator.is-radio::after {
        content: none;
    }

    .choice-grid.is-invalid .choice-card {
        border-color: #fca5a5 !important;
        background: #fff7f7;
    }

    /* ---------- Phone input ---------- */
    .iti {
        display: block;
        width: 100%;
    }

    .iti.is-invalid .form-control-dark {
        border-color: #ef4444;
    }

    .iti--separate-dial-code .iti__selected-flag {
        background: transparent !important;
        border-radius: var(--r) 0 0 var(--r);
    }

    .iti__country-list {
        border-color: var(--border-panel) !important;
        border-radius: var(--r) !important;
        box-shadow: 0 18px 40px rgba(17, 24, 39, .16) !important;
        font-size: .9rem !important;
    }

    .iti__country.iti__highlight {
        background-color: var(--dmc-red-light) !important;
    }

    /* =========================================================
       Sticky action bar
       ========================================================= */
    .step-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 28px;
        padding: 18px 0 22px;
        background: var(--bg-main);
        border-top: 1px solid var(--border-panel);
    }

    .step-count {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .step-count #step-counter {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--dmc-red);
    }

    .btn-draw-main {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        padding: 13px 28px;
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #fff;
        background: var(--dmc-red);
        border: 1px solid var(--dmc-red);
        border-radius: var(--r);
        box-shadow: none;
        cursor: pointer;
        transition: all .25s;
    }

    .btn-draw-main i {
        transition: transform .25s;
    }

    .btn-draw-main:hover {
        background: var(--dmc-red-dark);
        border-color: var(--dmc-red-dark);
    }

    #btn-step-next:hover i {
        transform: translateX(4px);
    }

    .btn-draw-main:disabled {
        opacity: .65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-step-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 22px;
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--text-primary);
        background: #fff;
        border: 1px solid var(--text-primary);
        border-radius: var(--r);
        cursor: pointer;
        transition: all .2s;
    }

    .btn-step-back:hover {
        color: #fff;
        background: var(--text-primary);
    }

    .btn-draw-main:focus-visible,
    .btn-step-back:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(200, 16, 46, .3);
    }

    /* =========================================================
       Alerts & success
       ========================================================= */
    .alert-error-dmc {
        margin-bottom: 24px;
        padding: 16px 20px;
        font-size: .86rem;
        color: #991b1b;
        background: #fff;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        border-radius: var(--r);
    }

    .success-panel {
        margin-top: 32px;
        padding: 48px 24px;
        text-align: center;
        background: #fff;
        border: 1px solid var(--border-panel);
        border-radius: var(--r);
        animation: slideIn .4s ease;
    }

    .success-panel h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
    }

    .success-panel p {
        color: var(--text-secondary);
    }

    .success-icon {
        width: 72px;
        height: 72px;
        margin-bottom: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        background: #15803d;
        border-radius: var(--r);
        animation: popIn .35s ease;
    }

    @keyframes popIn {
        from { transform: scale(.85); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation: none !important;
            transition: none !important;
        }
    }

    /* =========================================================
       Tablet / mobile: sidebar becomes a top hero
       ========================================================= */
    @media (max-width: 1199.98px) {
        :root {
            --side-width: 360px;
        }

        .main-inner {
            padding: 32px 32px 0;
        }
    }

    @media (max-width: 991.98px) {
        .survey-shell {
            display: block;
        }

        .side-art {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .survey-side {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: auto;
            border-radius: 0;
        }

        .side-inner {
            gap: 22px;
            padding: 24px 20px 22px;
        }

        .side-brand img {
            height: 38px;
        }

        .survey-intro .intro-lead {
            font-size: 1.05rem;
        }

        .main-inner {
            padding: 24px 16px 0;
        }

        .survey-banner {
            border-radius: var(--r);
        }

        .stepper {
            margin: 20px 0 26px;
        }
    }

    @media (max-width: 575.98px) {
        .step-head {
            gap: 12px;
            margin-bottom: 18px;
        }

        .panel-header-title {
            font-size: 1.3rem;
        }

        .step-pane > .row {
            padding: 20px 4px 6px;
            border-radius: var(--r);
        }

        .step-pane > .form-group {
            padding: 18px 16px 16px;
            border-radius: var(--r);
        }

        .step-pane > .form-group > label {
            font-size: .98rem;
        }

        .choice-grid {
            grid-template-columns: 1fr;
        }

        .stepper {
            gap: 8px;
        }

        .stepper-item {
            padding: 12px 12px 10px;
            font-size: .78rem;
        }

        .step-actions {
            gap: 8px;
            margin-top: 20px;
            padding: 12px 0 14px;
        }

        .btn-draw-main,
        .btn-step-back {
            gap: 6px;
            padding: 9px 16px;
            font-size: .74rem;
            letter-spacing: .08em;
        }

        .step-count {
            font-size: .72rem;
        }

        .step-count #step-counter {
            font-size: .9rem;
        }
    }
</style>
