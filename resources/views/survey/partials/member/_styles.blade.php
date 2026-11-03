<style>
    /* Tokens & form elements follow lucky-draw/index.blade.php */
    :root {
        --dmc-red: #c8102e;
        --dmc-red-glow: rgba(200, 16, 46, 0.25);
        --dmc-red-dark: #93081f;
        --dmc-red-light: #fff0f2;
        --dmc-red-subtle: #ffe4e8;
        --bg-page: #f8fafc;
        --bg-panel: #ffffff;
        --bg-panel-subtle: #f8fafc;
        --border-panel: #e2e8f0;
        --border-subtle: #edf2f7;
        --border-input: #cbd5e1;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: #f5f7fb;
    }

    /* Header, card & banner keep the original survey layout */
    .header-ribbon {
        padding: 1rem 1.5rem;
        border-radius: 20px;
        background: radial-gradient(120% 140% at 0% 0%, #c53227 0%, #e34d32 45%, #f06e57 100%);
        box-shadow: 0 12px 30px rgba(241, 1, 16, .35);
    }

    .header-ribbon img {
        height: 42px;
    }

    .card-survey {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(22, 31, 56, .08);
        padding: 2rem;
    }

    .survey-banner {
        overflow: hidden;
        border-radius: 16px;
        margin-bottom: 2rem;
    }

    .survey-banner img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Form area uses the lucky-draw elements */
    .stepper,
    #member-survey-form {
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
    }

    /* Stepper */
    .stepper {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
        margin: 22px 0 18px;
    }

    .stepper-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .stepper-bar {
        height: 4px;
        border-radius: 999px;
        background: var(--border-panel);
        transition: background 0.25s;
    }

    .stepper-item.done .stepper-bar,
    .stepper-item.active .stepper-bar {
        background: var(--dmc-red);
    }

    .stepper-item.active {
        color: var(--dmc-red);
    }

    .stepper-item.done {
        color: var(--text-secondary);
    }

    .step-pane {
        display: none;
    }

    .step-pane.active {
        display: block;
        animation: fadeIn 0.25s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Styles */
    .panel-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.12rem;
        font-weight: 800;
        letter-spacing: .02em;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .panel-header-desc {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 14px;
        line-height: 1.35;
    }

    .req-star {
        color: var(--dmc-red);
        font-weight: 800;
        margin-left: 2px;
    }

    .field-hint-badge {
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--text-muted);
        margin-left: 4px;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-group label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
        display: block;
    }

    .form-control-dark {
        background: #ffffff;
        border: 1px solid var(--border-input);
        border-radius: 8px;
        padding: 8px 12px;
        color: var(--text-primary);
        font-size: 0.86rem;
        height: 38px;
        width: 100%;
        transition: all 0.2s;
        outline: none;
    }

    textarea.form-control-dark {
        height: auto;
        resize: vertical;
    }

    .form-control-dark:focus {
        border-color: var(--dmc-red);
        box-shadow: 0 0 0 3px rgba(200, 16, 46, 0.15);
    }

    .form-control-dark::placeholder {
        color: var(--text-muted);
    }

    .form-control-dark.is-invalid,
    .choice-grid.is-invalid .choice-card {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        background-color: #fffbfb;
    }

    .invalid-feedback-custom {
        display: none;
        color: #dc2626;
        font-size: 0.73rem;
        font-weight: 600;
        margin-top: 4px;
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
        animation: shakeInput 0.4s ease-in-out;
    }

    /* Choice cards (same look as the lucky-draw mode toggle) */
    .choice-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        background: var(--bg-panel-subtle);
        padding: 4px;
        border-radius: 10px;
        border: 1px solid var(--border-panel);
    }

    .choice-grid-1 {
        grid-template-columns: 1fr;
    }

    .form-group label.choice-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        margin: 0;
        border-radius: 7px;
        border: 1px solid transparent;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-group label.choice-card:hover {
        background: #ffffff;
    }

    .choice-card input {
        display: none;
    }

    .choice-indicator {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        border: 1.5px solid var(--border-input);
        border-radius: 4px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .choice-indicator.is-radio {
        border-radius: 50%;
    }

    .form-group label.choice-card.active {
        background: #ffffff;
        color: var(--dmc-red);
        border-color: #fecdd3;
        box-shadow: 0 2px 6px rgba(200, 16, 46, 0.08);
    }

    .choice-card.active .choice-indicator {
        border-color: var(--dmc-red);
        background: var(--dmc-red);
    }

    .choice-card.active .choice-indicator::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 9px;
        color: #fff;
    }

    .choice-card.active .choice-indicator.is-radio::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
    }

    /* Phone input */
    .iti {
        width: 100%;
        display: block;
    }

    .iti.is-invalid .form-control-dark {
        border-color: #ef4444;
    }

    .iti__country-list {
        border-color: var(--border-panel) !important;
        border-radius: 10px !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12) !important;
        font-size: 0.9rem !important;
    }

    .iti__country.iti__highlight {
        background-color: var(--dmc-red-light) !important;
    }

    /* Navigation buttons */
    .step-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--border-subtle);
    }

    .btn-draw-main {
        background: linear-gradient(135deg, #c8102e 0%, #93081f 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 11px 22px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.92rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        box-shadow: 0 4px 14px rgba(200, 16, 46, 0.32);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        transition: all 0.25s;
    }

    .btn-draw-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(200, 16, 46, 0.42);
        background: linear-gradient(135deg, #dd1334 0%, #a80a24 100%);
    }

    .btn-draw-main:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-step-back {
        background: #ffffff;
        color: var(--text-secondary);
        border: 1px solid var(--border-panel);
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 0.86rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-step-back:hover {
        color: var(--dmc-red);
        border-color: #fecdd3;
        background: var(--dmc-red-light);
    }

    /* Alerts */
    .alert-error-dmc {
        background: #fff5f5;
        border: 1px solid #f1b0b7;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 16px;
        font-size: 0.84rem;
        color: #991b1b;
    }

    .success-panel {
        text-align: center;
        padding: 24px 8px 8px;
    }

    .success-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #ecfdf5;
        color: #16a34a;
        font-size: 1.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    @media (max-width: 575.98px) {
        .card-survey {
            padding: 1.25rem 1rem;
        }

        .choice-grid {
            grid-template-columns: 1fr;
        }

        .stepper-item {
            font-size: 0.66rem;
        }
    }
</style>
