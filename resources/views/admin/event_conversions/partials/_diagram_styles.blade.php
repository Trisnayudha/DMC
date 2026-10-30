<style>
    /* Styling khusus diagram Event Database & Member Conversion */
    .conversion-diagram-container {
        background: #fbfbfb;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 30px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .diagram-header-title {
        color: #0e273c;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .diagram-header-subtitle {
        color: #5a6a80;
        font-size: 14.5px;
        margin-top: 4px;
    }

    .diagram-card {
        border-radius: 14px;
        padding: 20px;
        transition: all 0.25s ease;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .diagram-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    /* Left Column: Event Types */
    .card-owned-events {
        background: linear-gradient(145deg, #0e273c 0%, #173752 100%);
        color: #ffffff;
        border-left: 5px solid #d89f4b;
    }

    .card-supporting-events {
        background: linear-gradient(145deg, #1b354d 0%, #294764 100%);
        color: #ffffff;
        border-left: 5px solid #38bdf8;
    }

    .card-type-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .card-type-freq {
        color: #d89f4b;
        font-size: 13.5px;
        font-weight: 600;
    }

    .card-type-meta {
        font-size: 11.5px;
        color: #cbd5e1;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid rgba(255,255,255,0.15);
    }

    /* Center Column: Event Participation */
    .card-participation {
        background: #ffffff;
        border: 2px solid #c8923a;
        border-radius: 14px;
        padding: 24px 20px;
        box-shadow: 0 4px 15px rgba(200, 146, 58, 0.08);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .participation-title {
        color: #0e273c;
        font-size: 19px;
        font-weight: 800;
        text-align: center;
        margin-bottom: 16px;
    }

    .participation-stage-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .participation-stage-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        margin-bottom: 8px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #edf2f7;
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
    }

    .participation-stage-item:last-child {
        margin-bottom: 0;
    }

    .stage-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* Right Column: Outcomes */
    .card-attendance-outcome {
        background: linear-gradient(145deg, #0e273c 0%, #152f47 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 18px 20px;
    }

    .card-conversion-outcome {
        background: linear-gradient(145deg, #b57a2b 0%, #cf923a 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 16px;
    }

    .outcome-title {
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .outcome-metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        text-align: center;
    }

    .outcome-metric-box {
        background: rgba(0, 0, 0, 0.16);
        border-radius: 8px;
        padding: 8px 6px;
    }

    .outcome-metric-value {
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    .outcome-metric-label {
        font-size: 10.5px;
        opacity: 0.9;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .conversion-explainer {
        font-size: 11.5px;
        color: #fef08a;
        margin-top: 8px;
        line-height: 1.35;
    }

    /* Connecting connectors */
    .connector-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .connector-line {
        height: 2px;
        background: #cbd5e1;
        width: 100%;
        position: relative;
    }

    /* Bottom Guideline Quote */
    .guideline-banner {
        margin-top: 26px;
        padding: 16px 20px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        text-align: center;
    }

    .guideline-quote {
        font-size: 14.5px;
        font-weight: 700;
        color: #0e273c;
        margin-bottom: 6px;
    }

    .guideline-sub {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
</style>
