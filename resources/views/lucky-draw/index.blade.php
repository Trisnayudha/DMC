<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DMC – Lucky Draw</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#c8102e">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DMC Lucky Draw">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --dmc-red: #c8102e;
            --dmc-red-glow: rgba(200, 16, 46, 0.25);
            --dmc-red-dark: #93081f;
            --dmc-red-light: #fff0f2;
            --dmc-red-subtle: #ffe4e8;
            --bg-page: #f8fafc;
            --bg-stage: #f1f5f9;
            --bg-panel: #ffffff;
            --bg-panel-subtle: #f8fafc;
            --bg-input: #ffffff;
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

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: var(--bg-page);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* Top Bar */
        .top-navbar {
            height: 60px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-panel);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #0f172a;
        }

        .brand-logo:hover {
            text-decoration: none;
            color: #0f172a;
        }

        .brand-logo img {
            height: 38px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.08));
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin: 0;
            color: #0f172a;
        }

        .brand-title span {
            color: var(--dmc-red);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Topbar Spin Mode Segmented Toggle */
        .topbar-mode-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 3px 4px 3px 10px;
            border-radius: 10px;
            border: 1px solid var(--border-panel);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .topbar-mode-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .topbar-segmented-group {
            display: inline-flex;
            align-items: center;
            background: #e2e8f0;
            padding: 2px;
            border-radius: 8px;
            gap: 3px;
        }

        .topbar-mode-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            height: 30px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1px solid transparent;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
            user-select: none;
            white-space: nowrap;
        }

        .topbar-mode-btn:hover {
            color: var(--text-primary);
        }

        .topbar-mode-btn.active[data-spin-mode="anonymous"] {
            background: #ffffff;
            color: #0284c7;
            border-color: #bae6fd;
            box-shadow: 0 2px 6px rgba(14, 165, 233, 0.16);
        }

        .topbar-mode-btn.active[data-spin-mode="required"] {
            background: #ffffff;
            color: var(--dmc-red);
            border-color: #fecdd3;
            box-shadow: 0 2px 6px rgba(200, 16, 46, 0.16);
        }

        @media (max-width: 576px) {
            .topbar-mode-label {
                display: none;
            }
            .topbar-mode-btn {
                padding: 4px 8px;
                font-size: 0.74rem;
            }
        }

        .btn-action-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid var(--border-panel);
            color: var(--text-secondary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            outline: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .btn-action-icon:hover {
            color: var(--dmc-red);
            background: var(--dmc-red-light);
            border-color: #fecdd3;
        }

        .btn-action-icon.active {
            color: var(--dmc-red);
            border-color: var(--dmc-red);
            background: var(--dmc-red-subtle);
        }

        /* Main Workspace Layout */
        .app-layout {
            display: flex;
            height: calc(100vh - 60px);
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }

        /* LEFT: Lucky Draw Stage */
        .wheel-stage {
            flex: 1;
            background: radial-gradient(circle at 45% 50%, rgba(200, 16, 46, 0.05) 0%, #ffffff 50%, #f1f5f9 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 16px 24px;
            user-select: none;
            overflow: hidden;
            min-width: 0;
        }

        .wheel-stage::before {
            content: '';
            position: absolute;
            width: 720px;
            height: 720px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200, 16, 46, 0.06) 0%, transparent 70%);
            pointer-events: none;
            z-index: 1;
        }

        .wheel-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            transition: transform 0.25s ease;
        }

        .wheel-container:hover {
            transform: scale(1.008);
        }

        .wheel-container:active {
            transform: scale(0.995);
        }

        #wheel-canvas {
            display: block;
            border-radius: 50%;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12), 0 0 0 8px #ffffff, 0 0 0 12px rgba(200, 16, 46, 0.22), 0 0 35px rgba(200, 16, 46, 0.1);
            transition: filter 0.3s;
        }

        .wheel-container.is-spinning #wheel-canvas {
            filter: drop-shadow(0 0 25px var(--dmc-red-glow));
        }

        /* Needle / Pointer (Located on the RIGHT edge, pointing LEFT) */
        .wheel-pointer-wrapper {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            pointer-events: none;
            filter: drop-shadow(-3px 4px 8px rgba(200, 16, 46, 0.35));
        }

        .wheel-pointer {
            width: 0;
            height: 0;
            border-top: 18px solid transparent;
            border-bottom: 18px solid transparent;
            border-right: 44px solid var(--dmc-red);
            position: relative;
            transform-origin: 40px 50%;
            transition: transform 0.05s linear;
        }

        .wheel-pointer::after {
            content: '';
            position: absolute;
            top: -13px;
            right: -42px;
            width: 0;
            height: 0;
            border-top: 13px solid transparent;
            border-bottom: 13px solid transparent;
            border-right: 32px solid #e53935;
        }

        .wheel-pointer.flick {
            transform: rotate(-15deg);
        }

        /* Center Hub */
        .wheel-hub {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #ffffff;
            border: 5px solid var(--dmc-red);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.16), inset 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 5;
            transition: transform 0.2s, width 0.2s, height 0.2s;
        }

        .wheel-hub-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .wheel-hub-icon {
            color: var(--dmc-red);
            font-size: 1.2rem;
            line-height: 1;
        }

        .wheel-hub-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 0.75rem;
            letter-spacing: .08em;
            color: var(--dmc-red-dark);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .wheel-hint {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            font-size: 0.84rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.95);
            padding: 6px 18px;
            border-radius: 999px;
            border: 1px solid var(--border-panel);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(6px);
            z-index: 2;
        }

        .wheel-hint i {
            color: var(--dmc-red);
        }

        /* RIGHT: Sidebar Panel */
        .sidebar-panel {
            width: 400px;
            min-width: 340px;
            max-width: 440px;
            flex-shrink: 0;
            background: var(--bg-panel);
            border-left: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            height: 100%;
            z-index: 20;
            box-shadow: -6px 0 25px rgba(15, 23, 42, 0.05);
        }

        /* Tabs Header */
        .sidebar-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-panel);
            background: #ffffff;
            padding: 6px 12px 0;
            gap: 4px;
            flex-shrink: 0;
        }

        .tab-btn {
            flex: 1;
            padding: 10px 10px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-secondary);
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
            border-radius: 6px 6px 0 0;
        }

        .tab-btn:hover {
            color: var(--dmc-red);
            background: var(--dmc-red-light);
        }

        .tab-btn.active {
            color: var(--dmc-red);
            border-bottom-color: var(--dmc-red);
            background: #ffffff;
        }

        .tab-badge {
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--text-secondary);
            font-weight: 700;
        }

        .tab-btn.active .tab-badge {
            background: var(--dmc-red);
            color: #fff;
        }

        /* Sidebar Content */
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
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
            margin-bottom: 12px;
            line-height: 1.35;
        }

        /* Required star & hint badge */
        .req-star {
            color: var(--dmc-red);
            font-weight: 800;
            margin-left: 2px;
            display: none;
        }

        .mode-required-active .req-star {
            display: inline;
        }

        .field-hint-badge {
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-left: 4px;
        }

        .mode-required-active .field-hint-badge.req-hint {
            color: var(--dmc-red);
            font-weight: 600;
        }

        /* Validation invalid state & shake */
        .form-control-dark.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
            background-color: #fffbfb;
        }

        .invalid-feedback-custom {
            display: none;
            color: #dc2626;
            font-size: 0.73rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .form-control-dark.is-invalid ~ .invalid-feedback-custom {
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

        .mode-toggle-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 12px;
            background: #f8fafc;
            padding: 3px;
            border-radius: 9px;
            border: 1px solid var(--border-panel);
        }

        .mode-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
            margin: 0;
            text-align: center;
        }

        .mode-toggle-btn input {
            display: none;
        }

        .mode-toggle-btn.active {
            background: #ffffff;
            color: var(--dmc-red);
            border-color: #fecdd3;
            box-shadow: 0 2px 6px rgba(200, 16, 46, 0.08);
        }

        .mode-toggle-btn.active i {
            color: var(--dmc-red);
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 3px;
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

        .form-control-dark:focus {
            border-color: var(--dmc-red);
            box-shadow: 0 0 0 3px rgba(200, 16, 46, 0.15);
            background: #ffffff;
            color: var(--text-primary);
        }

        .form-control-dark::placeholder {
            color: var(--text-muted);
        }

        .iti {
            width: 100%;
        }

        .iti__country-list {
            background-color: #ffffff !important;
            color: var(--text-primary) !important;
            border-color: var(--border-panel) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .iti__country.iti__highlight {
            background-color: var(--dmc-red-light) !important;
        }

        /* Camera Card Scanner */
        .camera-card-wrapper {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 4px;
        }

        .camera-viewfinder-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            background: #090d16;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .camera-hud-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .card-target-box {
            position: relative;
            width: 86%;
            height: 74%;
            border: 1.5px dashed rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.38);
        }

        .hud-corner {
            position: absolute;
            width: 18px;
            height: 18px;
            border-color: var(--dmc-red);
            border-style: solid;
        }

        .hud-corner.top-left {
            top: -2px;
            left: -2px;
            border-width: 3px 0 0 3px;
            border-top-left-radius: 9px;
        }

        .hud-corner.top-right {
            top: -2px;
            right: -2px;
            border-width: 3px 3px 0 0;
            border-top-right-radius: 9px;
        }

        .hud-corner.bottom-left {
            bottom: -2px;
            left: -2px;
            border-width: 0 0 3px 3px;
            border-bottom-left-radius: 9px;
        }

        .hud-corner.bottom-right {
            bottom: -2px;
            right: -2px;
            border-width: 0 3px 3px 0;
            border-bottom-right-radius: 9px;
        }

        .hud-instruction {
            position: absolute;
            bottom: 8px;
            left: 0;
            right: 0;
            text-align: center;
            color: #ffffff;
            font-size: 0.73rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .camera-flash {
            position: absolute;
            inset: 0;
            background: #ffffff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-out;
            z-index: 25;
        }

        .camera-flash.flash {
            opacity: 0.95;
            transition: none;
        }

        .camera-state-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            z-index: 20;
        }

        .camera-state-overlay i.fa-spinner {
            font-size: 1.8rem;
            color: var(--dmc-red);
        }

        .camera-state-overlay.error i.fa-video-slash {
            font-size: 2rem;
            color: #ef4444;
        }

        .cam-err-title {
            font-size: 0.84rem;
            color: #cbd5e1;
            font-weight: 500;
        }

        .btn-retry-cam {
            background: var(--dmc-red);
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-retry-cam:hover {
            background: var(--dmc-red-dark);
        }

        .cam-fallback-row {
            margin-top: 4px;
            font-size: 0.78rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cam-fallback-label {
            color: #38bdf8;
            cursor: pointer;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Preview container */
        .camera-preview-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            background: #0f172a;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid var(--dmc-red);
            box-shadow: 0 4px 16px rgba(200, 16, 46, 0.15);
        }

        #card-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .preview-success-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(16, 185, 129, 0.9);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Camera Toolbar */
        .camera-toolbar {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px 0;
        }

        .toolbar-stream {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            width: 100%;
        }

        .btn-cam-tool {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            margin: 0;
        }

        .btn-cam-tool:hover {
            background: var(--dmc-red-light);
            color: var(--dmc-red);
            border-color: var(--dmc-red);
        }

        .btn-cam-shutter {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: #ffffff;
            border: 3.5px solid var(--dmc-red);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 14px rgba(200, 16, 46, 0.25);
        }

        .btn-cam-shutter:hover {
            transform: scale(1.06);
            box-shadow: 0 6px 18px rgba(200, 16, 46, 0.35);
        }

        .btn-cam-shutter:active {
            transform: scale(0.92);
        }

        .shutter-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #c8102e 0%, #93081f 100%);
            display: block;
        }

        .toolbar-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
        }

        .btn-cam-retake {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: var(--dmc-red);
            border: 1.5px solid var(--dmc-red);
            padding: 9px 20px;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(200, 16, 46, 0.12);
        }

        .btn-cam-retake:hover {
            background: var(--dmc-red-light);
            transform: translateY(-1px);
        }

        #card-upload-status {
            display: block;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            min-height: 18px;
        }

        .status-uploading {
            color: #f59e0b;
        }

        .status-done {
            color: #10b981;
        }

        .status-error {
            color: #ef4444;
        }

        /* Action Buttons */
        .action-footer {
            padding-top: 10px;
            padding-bottom: 2px;
            border-top: 1px solid var(--border-subtle);
            margin-top: 10px;
            position: sticky;
            bottom: 0;
            background: var(--bg-panel);
            z-index: 10;
        }

        .btn-draw-main {
            width: 100%;
            background: linear-gradient(135deg, #c8102e 0%, #93081f 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.98rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            box-shadow: 0 4px 14px rgba(200, 16, 46, 0.32);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s;
        }

        .btn-draw-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(200, 16, 46, 0.42);
            background: linear-gradient(135deg, #dd1334 0%, #a80a24 100%);
        }

        .btn-draw-main:active {
            transform: translateY(0);
        }

        .btn-draw-main:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Tab 2: Prizes List */
        .prize-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.15s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .prize-list-item:hover {
            transform: translateX(3px);
            border-color: #fca5a5;
            background: #fff9fa;
        }

        .prize-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .prize-color-swatch {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .prize-name-text {
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--text-primary);
        }

        .prize-chance-pill {
            font-size: 0.76rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--text-secondary);
            border: 1px solid var(--border-panel);
        }

        /* Tab 3: Winners Log */
        .winner-empty-state {
            text-align: center;
            padding: 40px 16px;
            color: var(--text-muted);
        }

        .winner-empty-state i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: 0.4;
            color: var(--dmc-red);
        }

        .winner-log-card {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-left: 4px solid var(--dmc-red);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            animation: fadeIn 0.3s ease;
        }

        .winner-log-prize {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.02rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .winner-log-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .winner-log-time {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Modal Celebration Overlay (Wheel of Names Style) */
        .winner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .winner-overlay.active {
            display: flex;
            animation: fadeIn 0.25s ease;
        }

        .winner-modal {
            background: #ffffff;
            border: 2px solid #fecdd3;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.2), 0 0 45px rgba(200, 16, 46, 0.12);
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            padding: 36px 28px 30px;
            position: relative;
            overflow: hidden;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .winner-modal-glow {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200, 16, 46, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .winner-icon-wrap {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c8102e, #93081f);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.3rem;
            margin-bottom: 18px;
            box-shadow: 0 10px 25px rgba(200, 16, 46, 0.4);
            animation: bounceIn 0.6s ease;
        }

        .winner-congrats-sub {
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
            letter-spacing: .15em;
            font-size: 0.85rem;
            font-weight: 700;
            color: #d97706;
            margin-bottom: 6px;
        }

        .winner-prize-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .winner-recipient-name {
            font-size: 1.05rem;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .winner-recipient-name strong {
            color: var(--text-primary);
        }

        .winner-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-modal-close {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-modal-again {
            background: var(--dmc-red);
            border: none;
            color: #fff;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.35);
            transition: all 0.2s;
        }

        .btn-modal-again:hover {
            background: var(--dmc-red-dark);
            transform: translateY(-1px);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes popIn {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Responsive Breakpoints */
        @media (min-width: 992px) and (max-width: 1200px) {
            .sidebar-panel {
                width: 360px;
                min-width: 340px;
            }
            .wheel-stage {
                padding: 12px 16px;
            }
        }

        @media (min-width: 1440px) {
            .sidebar-panel {
                width: 420px;
            }
        }

        @media (max-width: 991px) {
            .app-layout {
                flex-direction: column;
                height: auto;
                overflow: visible;
            }

            .wheel-stage {
                min-height: 520px;
                padding: 30px 10px;
            }

            .sidebar-panel {
                width: 100%;
                min-width: 100%;
                box-shadow: none;
                border-left: none;
                border-top: 1px solid var(--border-panel);
            }
        }

        /* Network Status Badge */
        .network-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid var(--border-panel);
            background: var(--bg-panel);
            color: var(--text-secondary);
            transition: all 0.2s ease;
            user-select: none;
        }

        .network-status-badge:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .network-status-badge .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
            transition: background-color 0.3s;
        }

        .network-status-badge.online .status-dot {
            background-color: #10b981;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
        }

        .network-status-badge.offline .status-dot {
            background-color: #ef4444;
            box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
        }

        .network-status-badge.pending .status-dot {
            background-color: #f59e0b;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.8);
            animation: pulseDot 1.5s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        .network-status-badge .status-count {
            background: #f59e0b;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 10px;
        }

        /* Offline Sync Modal */
        .offline-sync-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: fadeIn 0.2s ease;
        }

        .offline-sync-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-panel);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: popIn 0.25s ease;
        }

        .offline-sync-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-subtle);
            background: #f8fafc;
        }

        .offline-sync-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .offline-sync-title i {
            color: var(--dmc-red);
        }

        .offline-sync-close {
            background: transparent;
            border: none;
            font-size: 1.4rem;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }

        .offline-sync-close:hover {
            color: var(--text-primary);
        }

        .offline-sync-body {
            padding: 20px 22px;
            max-height: 65vh;
            overflow-y: auto;
        }

        .offline-connection-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            background: #f1f5f9;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 16px;
        }

        .offline-stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .offline-stat-box {
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
        }

        .offline-stat-box .stat-number {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .offline-stat-box .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .offline-queue-list-header {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .offline-queue-list {
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            max-height: 180px;
            overflow-y: auto;
            padding: 8px;
        }

        .offline-queue-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            margin-bottom: 6px;
            font-size: 0.83rem;
        }

        .offline-queue-item:last-child {
            margin-bottom: 0;
        }

        .offline-queue-item-info {
            display: flex;
            flex-direction: column;
        }

        .offline-queue-item-name {
            font-weight: 700;
            color: var(--text-primary);
        }

        .offline-queue-item-prize {
            font-size: 0.75rem;
            color: var(--dmc-red);
            font-weight: 600;
        }

        .offline-queue-item-badge {
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 12px;
            font-weight: 700;
        }

        .offline-queue-item-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .offline-queue-item-badge.synced {
            background: #d1fae5;
            color: #059669;
        }

        .offline-empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 24px 10px;
            font-size: 0.85rem;
            font-style: italic;
        }

        .sync-progress-container {
            margin-top: 14px;
        }

        .sync-progress-bar-wrap {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .sync-progress-bar {
            height: 100%;
            background: var(--dmc-red);
            transition: width 0.3s ease;
        }

        .sync-progress-text {
            font-size: 0.78rem;
            color: var(--text-secondary);
            text-align: center;
            font-weight: 600;
        }

        .offline-sync-footer {
            display: flex;
            gap: 10px;
            padding: 14px 22px;
            background: #f8fafc;
            border-top: 1px solid var(--border-subtle);
        }

        .btn-sync-action {
            flex: 1;
            padding: 11px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-sync-export {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }

        .btn-sync-export:hover {
            background: #f1f5f9;
        }

        .btn-sync-primary {
            background: var(--dmc-red);
            color: #ffffff;
        }

        .btn-sync-primary:hover {
            background: var(--dmc-red-dark);
        }

        .btn-sync-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <!-- TOP NAVIGATION BAR -->
    <nav class="top-navbar">
        <a href="{{ url('/') }}" class="brand-logo">
            <img src="{{ asset('image/dmc.png') }}" alt="DMC Logo">
            <h1 class="brand-title"><span>Lucky Draw</span></h1>
        </a>

        <div class="nav-actions">
            <!-- Topbar Mode Segmented Toggle -->
            <div class="topbar-mode-toggle" id="topbar-mode-toggle">
                <span class="topbar-mode-label">
                    <i class="fas fa-sliders-h"></i> <span>Mode:</span>
                </span>
                <div class="topbar-segmented-group">
                    <button type="button" class="topbar-mode-btn active" data-spin-mode="anonymous" id="btn-mode-anonymous" title="Spin without participant details">
                        <i class="fas fa-user-secret"></i>
                        <span>Anonymous</span>
                    </button>
                    <button type="button" class="topbar-mode-btn" data-spin-mode="required" id="btn-mode-required" title="Participant details are required before spinning">
                        <i class="fas fa-user-check"></i>
                        <span>Require Details</span>
                    </button>
                </div>
            </div>

            <!-- Network & Offline Queue Status Badge -->
            <button type="button" class="network-status-badge online" id="btn-network-status" title="Status Jaringan & Antrean Tablet">
                <span class="status-dot"></span>
                <span class="status-text" id="network-status-text">Online</span>
                <span class="status-count" id="network-queue-count" style="display: none;">0</span>
            </button>

            <button class="btn-action-icon" id="btn-sound" title="Toggle Sound" aria-label="Toggle Sound">
                <i class="fas fa-volume-up"></i>
            </button>
            <button class="btn-action-icon" id="btn-fullscreen" title="Toggle Fullscreen"
                aria-label="Toggle Fullscreen">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </nav>

    <!-- APP MAIN LAYOUT -->
    <div class="app-layout">

        <!-- LEFT: HUGE LUCKY DRAW WHEEL STAGE -->
        <main class="wheel-stage" id="wheel-stage">
            <div class="wheel-container" id="wheel-container" title="Click to Spin the Wheel!">
                <!-- HTML5 Canvas for ultra-sharp, large dynamic rendering -->
                <canvas id="wheel-canvas"></canvas>

                <!-- Center Hub with DMC Branding & SPIN text -->
                <div class="wheel-hub">
                    <div class="wheel-hub-inner">
                        {{-- <i class="fas fa-dice-d20 wheel-hub-icon"></i> --}}
                        <span class="wheel-hub-text">SPIN</span>
                    </div>
                </div>

                <!-- Right Pointer Needle pointing LEFT horizontally -->
                <div class="wheel-pointer-wrapper">
                    <div class="wheel-pointer" id="wheel-pointer"></div>
                </div>
            </div>

            <div class="wheel-hint" id="wheel-hint">
                <i class="fas fa-hand-pointer text-primary" id="wheel-hint-icon"></i>
                <span id="wheel-hint-text">Anonymous Mode: Click the wheel or click <strong>Draw Now</strong> to spin directly</span>
            </div>
        </main>

        <!-- RIGHT: SIDEBAR PANEL (FORM, ENTRIES, RESULTS) -->
        <aside class="sidebar-panel">
            <!-- TABS -->
            <div class="sidebar-tabs">
                <button class="tab-btn active" data-tab="form">
                    <i class="fas fa-edit"></i>
                    <span>Form</span>
                </button>
                <button class="tab-btn" data-tab="prizes">
                    <i class="fas fa-gift"></i>
                    <span>Entries</span>
                    <span class="tab-badge" id="badge-prizes-count">{{ count($prizes) + 1 }}</span>
                </button>
                <button class="tab-btn" data-tab="winners">
                    <i class="fas fa-trophy"></i>
                    <span>Results</span>
                    <span class="tab-badge" id="badge-winners-count">0</span>
                </button>
            </div>

            <!-- TAB 1: FORM -->
            <div class="sidebar-content tab-pane active" id="tab-form">
                <h2 class="panel-header-title">Lucky Draw Entry</h2>
                <p class="panel-header-desc" id="panel-header-desc">Participant details are optional. You can spin right away!</p>

                <form action="{{ url('lucky-draw') }}" method="POST" enctype="multipart/form-data" id="lucky-draw-form"
                    novalidate>
                    @csrf
                    <input type="hidden" name="entry_id" id="entry_id" value="">
                    <input type="hidden" name="spin_mode" id="spin_mode" value="anonymous">

                    <!-- Capture Mode Toggle (Fill Details vs Scan Card) -->
                    <div class="mode-toggle-group">
                        <label class="mode-toggle-btn active" data-mode="manual">
                            <input type="radio" name="capture_mode" value="manual" checked>
                            <i class="fas fa-keyboard"></i>
                            <span>Fill Details</span>
                        </label>
                        <label class="mode-toggle-btn" data-mode="card">
                            <input type="radio" name="capture_mode" value="card">
                            <i class="fas fa-camera"></i>
                            <span>Scan Card</span>
                        </label>
                    </div>

                    <!-- Mode 1: Manual Input -->
                    <div id="panel-manual">
                        <div class="form-group">
                            <label for="input-name">
                                Full Name <span class="req-star">*</span>
                                <span class="field-hint-badge req-hint" id="hint-name">(Optional)</span>
                            </label>
                            <input type="text" name="name" id="input-name" class="form-control-dark"
                                placeholder="e.g. John Doe">
                            <div class="invalid-feedback-custom" id="err-name">Full Name is required in this mode.</div>
                        </div>

                        <div class="form-group">
                            <label for="input-company">Company</label>
                            <input type="text" name="company_name" id="input-company" class="form-control-dark"
                                placeholder="e.g. PT Example Mining">
                        </div>

                        <div class="form-group">
                            <label for="input-job">Job Title</label>
                            <input type="text" name="job_title" id="input-job" class="form-control-dark"
                                placeholder="e.g. Operation Manager">
                        </div>

                        <div class="row">
                            <div class="col-12 form-group">
                                <label for="input-email">Email</label>
                                <input type="email" name="email" id="input-email" class="form-control-dark"
                                    placeholder="john@example.com">
                            </div>
                            <div class="col-12 form-group">
                                <label for="phone">
                                    Phone <span class="req-star">*</span>
                                    <span class="field-hint-badge req-hint" id="hint-phone">(Optional)</span>
                                </label>
                                <input type="tel" name="phone" id="phone" class="form-control-dark">
                                <div class="invalid-feedback-custom" id="err-phone">Phone number is required in this mode.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mode 2: Card Scan (Live Camera Stream) -->
                    <div id="panel-card" style="display: none;">
                        <div class="camera-card-wrapper">
                            <!-- Live Viewfinder Box -->
                            <div class="camera-viewfinder-container" id="camera-viewfinder-container">
                                <video id="camera-video" playsinline autoplay muted></video>

                                <!-- HUD Alignment Frame Overlay -->
                                <div class="camera-hud-overlay" id="camera-hud">
                                    <div class="card-target-box">
                                        <div class="hud-corner top-left"></div>
                                        <div class="hud-corner top-right"></div>
                                        <div class="hud-corner bottom-left"></div>
                                        <div class="hud-corner bottom-right"></div>
                                        <div class="hud-instruction">
                                            <i class="fas fa-id-card"></i> Position Business Card in Frame
                                        </div>
                                    </div>
                                </div>

                                <!-- White flash animation on shutter click -->
                                <div class="camera-flash" id="camera-flash"></div>

                                <!-- Camera Loading State -->
                                <div class="camera-state-overlay" id="camera-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Connecting camera...</span>
                                </div>

                                <!-- Camera Error / Denied State -->
                                <div class="camera-state-overlay error" id="camera-error" style="display: none;">
                                    <i class="fas fa-video-slash"></i>
                                    <div class="cam-err-title" id="cam-err-msg">Camera access unavailable</div>
                                    <button type="button" class="btn-retry-cam" id="btn-retry-camera">
                                        <i class="fas fa-redo"></i> Retry Camera Access
                                    </button>
                                    <div class="cam-fallback-row">
                                        <span>or</span>
                                        <label for="business_card_fallback" class="cam-fallback-label">
                                            <i class="fas fa-folder-open"></i> Upload from File
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Photo Preview Container (Shown after capture) -->
                            <div class="camera-preview-container" id="camera-preview-container" style="display: none;">
                                <img id="card-preview-img" src="" alt="Business card captured">
                                <div class="preview-success-tag">
                                    <i class="fas fa-check-circle"></i> Business Card Ready
                                </div>
                            </div>

                            <!-- Hidden Canvas for Full Resolution Snapshot -->
                            <canvas id="camera-canvas" style="display: none;"></canvas>

                            <!-- Hidden Fallback File Input -->
                            <input type="file" id="business_card_fallback" accept="image/*" style="display: none;">

                            <!-- Camera Controls Toolbar -->
                            <div class="camera-toolbar">
                                <!-- Active Stream Controls -->
                                <div class="toolbar-stream" id="toolbar-stream">
                                    <button type="button" class="btn-cam-tool" id="btn-switch-camera" title="Switch Camera (Front/Rear)">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button type="button" class="btn-cam-shutter" id="btn-shutter" title="Take Business Card Photo">
                                        <span class="shutter-circle"></span>
                                    </button>
                                    <label for="business_card_fallback" class="btn-cam-tool" title="Upload from File/Gallery">
                                        <i class="fas fa-folder-open"></i>
                                    </label>
                                </div>

                                <!-- Captured Preview Controls -->
                                <div class="toolbar-preview" id="toolbar-preview" style="display: none;">
                                    <button type="button" class="btn-cam-retake" id="btn-retake">
                                        <i class="fas fa-redo"></i> Retake Photo
                                    </button>
                                </div>
                            </div>

                            <!-- Upload & Validation Status -->
                            <div id="card-upload-status"></div>
                        </div>
                    </div>

                    <!-- Submit / Spin Button -->
                    <div class="action-footer">
                        <button type="submit" class="btn-draw-main" id="btn-draw">
                            <i class="fas fa-play"></i>
                            <span>Draw Now</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: PRIZES / ENTRIES -->
            <div class="sidebar-content tab-pane" id="tab-prizes">
                <h2 class="panel-header-title">Wheel Entries</h2>
                <p class="panel-header-desc">Available prizes on the lucky draw wheel.</p>

                <div id="prizes-list-container">
                    <!-- Populated dynamically via JS matching wheel segment colors -->
                </div>
            </div>

            <!-- TAB 3: RESULTS / WINNERS -->
            <div class="sidebar-content tab-pane" id="tab-winners">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="panel-header-title mb-0">Winners History</h2>
                        <p class="panel-header-desc mb-0">Recorded prizes drawn during this session.</p>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-clear-history"
                        title="Clear this session log" style="font-size: 0.75rem;">
                        <i class="fas fa-trash-alt"></i> Clear
                    </button>
                </div>

                <div id="winners-list-container">
                    <div class="winner-empty-state" id="winner-empty-state">
                        <i class="fas fa-dice"></i>
                        <p>No winners drawn yet.<br>Click the wheel to start drawing!</p>
                    </div>
                </div>
            </div>
        </aside>

    </div>

    <!-- WINNER CELEBRATION MODAL (Wheel of Names Style) -->
    <div class="winner-overlay" id="winner-modal">
        <div class="winner-modal">
            <div class="winner-modal-glow"></div>
            <div class="winner-icon-wrap">
                <i class="fas fa-award"></i>
            </div>
            <div class="winner-congrats-sub" id="winner-modal-badge">🎉 Congratulations! 🎉</div>
            <h3 class="winner-prize-title" id="winner-modal-title">Gelas</h3>
            <p class="winner-recipient-name" id="winner-modal-desc">
                Winner: <strong id="winner-modal-user">Guest Participant</strong>
            </p>
            <div class="winner-modal-actions">
                <button type="button" class="btn-modal-close" id="btn-modal-close">Close</button>
                <button type="button" class="btn-modal-again" id="btn-modal-again">Spin Again</button>
            </div>
        </div>
    </div>

    <!-- OFFLINE SYNC & QUEUE MODAL -->
    <div class="offline-sync-overlay" id="offline-sync-modal" style="display: none;">
        <div class="offline-sync-card">
            <div class="offline-sync-header">
                <div class="offline-sync-title">
                    <i class="fas fa-satellite-dish"></i> Status Jaringan & Antrean Tablet
                </div>
                <button type="button" class="offline-sync-close" id="btn-close-sync-modal">&times;</button>
            </div>
            <div class="offline-sync-body">
                <div class="offline-connection-banner" id="sync-connection-banner">
                    <i class="fas fa-check-circle text-success"></i>
                    <span id="sync-connection-text">Terhubung ke server DMC</span>
                </div>
                <div class="offline-stats-row">
                    <div class="offline-stat-box">
                        <div class="stat-number" id="sync-stat-pending">0</div>
                        <div class="stat-label">Antrean Offline</div>
                    </div>
                    <div class="offline-stat-box">
                        <div class="stat-number" id="sync-stat-synced">0</div>
                        <div class="stat-label">Tersinkronisasi</div>
                    </div>
                </div>

                <div class="offline-queue-list-container">
                    <div class="offline-queue-list-header">
                        <span>Daftar Antrean di Tablet</span>
                        <small style="color:var(--text-muted);font-weight:normal;">Auto-sync saat online</small>
                    </div>
                    <div class="offline-queue-list" id="offline-queue-list">
                        <div class="offline-empty-state">Belum ada antrean di tablet. Semua data aman!</div>
                    </div>
                </div>

                <div class="sync-progress-container" id="sync-progress-container" style="display: none;">
                    <div class="sync-progress-bar-wrap">
                        <div class="sync-progress-bar" id="sync-progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="sync-progress-text" id="sync-progress-text">Mengunggah 1 dari 5...</div>
                </div>
            </div>
            <div class="offline-sync-footer">
                <button type="button" class="btn-sync-action btn-sync-export" id="btn-export-backup" title="Download backup data ke tablet">
                    <i class="fas fa-file-download"></i> Backup (JSON)
                </button>
                <button type="button" class="btn-sync-action btn-sync-primary" id="btn-trigger-sync">
                    <i class="fas fa-sync-alt"></i> Sync Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script>
        // Initialize Phone Input
        var phoneInput = document.querySelector("#phone");
        var iti = null;
        if (phoneInput && window.intlTelInput) {
            iti = window.intlTelInput(phoneInput, {
                initialCountry: "id",
                preferredCountries: ["id", "sg", "my", "au"],
                utilsScript: "https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/utils.js"
            });
        }

        // Global Network & App Connectivity State
        var isAppOnline = navigator.onLine;

        // ==========================================
        // 0. SERVICE WORKER & OFFLINE DATABASE
        // ==========================================
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(reg) {
                    console.log('[PWA] Service Worker active with scope:', reg.scope);
                }).catch(function(err) {
                    console.warn('[PWA] Service Worker registration:', err);
                });
            });
        }

        var OfflineDB = (function() {
            var DB_NAME = 'DMCLuckyDrawOfflineDB';
            var DB_VERSION = 1;
            var STORE_NAME = 'offline_entries';
            var db = null;

            function open() {
                return new Promise(function(resolve, reject) {
                    if (db) return resolve(db);
                    if (!('indexedDB' in window)) {
                        return reject(new Error('IndexedDB not supported'));
                    }
                    var req = indexedDB.open(DB_NAME, DB_VERSION);
                    req.onupgradeneeded = function(e) {
                        var d = e.target.result;
                        if (!d.objectStoreNames.contains(STORE_NAME)) {
                            var store = d.createObjectStore(STORE_NAME, { keyPath: 'id' });
                            store.createIndex('synced', 'synced', { unique: false });
                            store.createIndex('created_at', 'created_at', { unique: false });
                        }
                    };
                    req.onsuccess = function(e) {
                        db = e.target.result;
                        resolve(db);
                    };
                    req.onerror = function(e) {
                        reject(e.target.error);
                    };
                });
            }

            function addEntry(entry) {
                return open().then(function(d) {
                    return new Promise(function(resolve, reject) {
                        var tx = d.transaction(STORE_NAME, 'readwrite');
                        var store = tx.objectStore(STORE_NAME);
                        var req = store.add(entry);
                        req.onsuccess = function() { resolve(entry); };
                        req.onerror = function(e) { reject(e.target.error); };
                    });
                });
            }

            function getAllUnsynced() {
                return open().then(function(d) {
                    return new Promise(function(resolve, reject) {
                        var tx = d.transaction(STORE_NAME, 'readonly');
                        var store = tx.objectStore(STORE_NAME);
                        var req = store.getAll();
                        req.onsuccess = function() {
                            var items = req.result || [];
                            var unsynced = items.filter(function(i) { return !i.synced; });
                            resolve(unsynced);
                        };
                        req.onerror = function(e) { reject(e.target.error); };
                    });
                });
            }

            function markSynced(id) {
                return open().then(function(d) {
                    return new Promise(function(resolve, reject) {
                        var tx = d.transaction(STORE_NAME, 'readwrite');
                        var store = tx.objectStore(STORE_NAME);
                        var req = store.get(id);
                        req.onsuccess = function() {
                            var item = req.result;
                            if (item) {
                                item.synced = true;
                                item.synced_at = new Date().toISOString();
                                store.put(item);
                            }
                            resolve();
                        };
                        req.onerror = function(e) { reject(e.target.error); };
                    });
                });
            }

            function countAll() {
                return open().then(function(d) {
                    return new Promise(function(resolve, reject) {
                        var tx = d.transaction(STORE_NAME, 'readonly');
                        var store = tx.objectStore(STORE_NAME);
                        var req = store.getAll();
                        req.onsuccess = function() {
                            var items = req.result || [];
                            var pending = items.filter(function(i) { return !i.synced; }).length;
                            var synced = items.filter(function(i) { return !!i.synced; }).length;
                            resolve({ pending: pending, synced: synced, items: items });
                        };
                        req.onerror = function() { resolve({ pending: 0, synced: 0, items: [] }); };
                    });
                }).catch(function() {
                    return { pending: 0, synced: 0, items: [] };
                });
            }

            return {
                open: open,
                addEntry: addEntry,
                getAllUnsynced: getAllUnsynced,
                markSynced: markSynced,
                countAll: countAll
            };
        })();

        // ==========================================
        // 1. DATA PREPARATION & COLOR PALETTE
        // ==========================================
        var initialPrizes = @json($prizes);
        try {
            if (initialPrizes && initialPrizes.length > 0) {
                localStorage.setItem('dmc_lucky_draw_prizes', JSON.stringify(initialPrizes));
            } else {
                var cachedPrizes = localStorage.getItem('dmc_lucky_draw_prizes');
                if (cachedPrizes) {
                    initialPrizes = JSON.parse(cachedPrizes);
                }
            }
        } catch (e) {
            console.warn('[Cache] Could not access localStorage for prizes:', e);
        }

        var wheelSegments = [];

        // Build list of segments: prizes plus "Try Again" slice
        if (initialPrizes && initialPrizes.length > 0) {
            initialPrizes.forEach(function(p) {
                wheelSegments.push({
                    id: p.id,
                    name: p.name,
                    chance: p.chance_percent || null,
                    isPrize: true
                });
            });
        } else {
            wheelSegments.push({
                id: null,
                name: "Grand Prize",
                chance: 50,
                isPrize: true
            });
        }
        // Always include zonk / try again option
        wheelSegments.push({
            id: null,
            name: "Try Again",
            chance: null,
            isPrize: false
        });

        // Exact client-side mathematical clone of LuckyDrawService::draw()
        function drawPrizeOffline() {
            // Angka acak 0.0000 - 99.9999 (presisi 4 desimal) identik dengan mt_rand(0, 999999)/10000 di LuckyDrawService
            var roll = Math.floor(Math.random() * 1000000) / 10000;
            var cursor = 0.0;
            for (var i = 0; i < wheelSegments.length; i++) {
                var seg = wheelSegments[i];
                if (seg.isPrize && seg.chance) {
                    cursor += parseFloat(seg.chance);
                    if (roll < cursor) {
                        return { index: i, segment: seg };
                    }
                }
            }
            var tryAgainIndex = wheelSegments.length - 1;
            return { index: tryAgainIndex, segment: wheelSegments[tryAgainIndex] };
        }

        // Wheel color palette with DMC Signature Red
        var colorPalette = [
            '#c8102e', // DMC Signature Red
            '#f59e0b', // Golden Amber
            '#10b981', // Emerald Green
            '#1d4ed8', // Royal Cobalt Blue
            '#8b5cf6', // Violet
            '#d97706', // Warm Amber
            '#e11d48', // Crimson Rose
            '#0f766e' // Deep Teal
        ];

        wheelSegments.forEach(function(seg, idx) {
            seg.color = colorPalette[idx % colorPalette.length];
        });

        // Populate Tab 2: Prizes / Entries list
        var prizesContainer = document.getElementById('prizes-list-container');
        if (prizesContainer) {
            prizesContainer.innerHTML = '';
            wheelSegments.forEach(function(seg) {
                var div = document.createElement('div');
                div.className = 'prize-list-item';
                div.innerHTML = `
                    <div class="prize-item-left">
                        <div class="prize-color-swatch" style="background-color: ${seg.color}"></div>
                        <div class="prize-name-text">${escapeHtml(seg.name)}</div>
                    </div>
                `;
                prizesContainer.appendChild(div);
            });
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }

        // ==========================================
        // 2. AUDIO SYNTHESIZER (Web Audio API)
        // ==========================================
        var audioCtx = null;
        var soundEnabled = true;

        function initAudio() {
            if (!audioCtx) {
                var AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    audioCtx = new AudioContextClass();
                }
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }

        function playPegTick() {
            if (!soundEnabled) return;
            initAudio();
            if (!audioCtx) return;

            try {
                var now = audioCtx.currentTime;
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(650, now);
                osc.frequency.exponentialRampToValueAtTime(140, now + 0.035);

                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.035);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start(now);
                osc.stop(now + 0.04);
            } catch (e) {}
        }

        function playFanfare() {
            if (!soundEnabled) return;
            initAudio();
            if (!audioCtx) return;

            try {
                var notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach(function(freq, i) {
                    var now = audioCtx.currentTime + i * 0.12;
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now);

                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(0.28, now + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc.start(now);
                    osc.stop(now + 0.5);
                });
            } catch (e) {}
        }

        // Sound Toggle Button
        var soundBtn = document.getElementById('btn-sound');
        if (soundBtn) {
            soundBtn.addEventListener('click', function() {
                soundEnabled = !soundEnabled;
                soundBtn.classList.toggle('active', soundEnabled);
                soundBtn.innerHTML = soundEnabled ? '<i class="fas fa-volume-up"></i>' :
                    '<i class="fas fa-volume-mute"></i>';
            });
        }

        // Fullscreen Toggle Button
        var fullscreenBtn = document.getElementById('btn-fullscreen');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(function() {});
                    fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                    fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
                }
            });
        }

        // ==========================================
        // 3. CANVAS WHEEL RENDERING ENGINE
        // ==========================================
        var canvas = document.getElementById('wheel-canvas');
        var ctx = canvas.getContext('2d');
        var wheelContainer = document.getElementById('wheel-container');
        var wheelPointer = document.getElementById('wheel-pointer');
        var stage = document.getElementById('wheel-stage');

        var numSegments = wheelSegments.length;
        var arcAngle = (2 * Math.PI) / numSegments;
        var currentRotation = 0; // Current angle in radians
        var isSpinning = false;
        var canvasSize = 650;

        function resizeCanvas() {
            var isDesktop = window.innerWidth > 991;

            // Available space calculations tailored for desktop vs mobile
            // On desktop, account for stage padding, glowing rings, needle pointer, and hint pill
            var stageWidth = isDesktop ? (stage.clientWidth - 130) : (stage.clientWidth - 30);
            var stageHeight = isDesktop ? (stage.clientHeight - 130) : (stage.clientHeight - 80);
            var availableSize = Math.min(stageWidth, stageHeight);

            // Responsive bounds: max 620px on desktop (leaves plenty of breathing room, zero clipping)
            var maxCap = isDesktop ? 620 : 420;
            var minCap = isDesktop ? 300 : 280;
            canvasSize = Math.max(minCap, Math.min(maxCap, availableSize));

            var dpr = window.devicePixelRatio || 1;
            canvas.width = canvasSize * dpr;
            canvas.height = canvasSize * dpr;
            canvas.style.width = canvasSize + 'px';
            canvas.style.height = canvasSize + 'px';

            // Scale center hub dynamically
            var hub = document.querySelector('.wheel-hub');
            var hubText = document.querySelector('.wheel-hub-text');
            if (hub) {
                var hubSize = Math.max(54, Math.min(92, Math.round(canvasSize * 0.155)));
                hub.style.width = hubSize + 'px';
                hub.style.height = hubSize + 'px';
                if (hubText) {
                    hubText.style.fontSize = Math.max(9, Math.round(hubSize * 0.16)) + 'px';
                }
            }

            ctx.scale(dpr, dpr);
            drawWheel(currentRotation);
        }

        window.addEventListener('resize', resizeCanvas);

        // Draw the complete wheel
        function drawWheel(angle) {
            var size = canvasSize;
            var cx = size / 2;
            var cy = size / 2;
            var radius = size / 2 - 6;

            ctx.clearRect(0, 0, size, size);

            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(angle);

            // Draw Segments
            for (var i = 0; i < numSegments; i++) {
                var seg = wheelSegments[i];
                var segStart = i * arcAngle;
                var segEnd = segStart + arcAngle;

                // Wedge background
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, radius, segStart, segEnd);
                ctx.fillStyle = seg.color;
                ctx.fill();

                // Wedge border line
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, radius, segStart, segEnd);
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.28)';
                ctx.lineWidth = 2.5;
                ctx.stroke();

                // Outer edge pin / peg dot
                var pegX = Math.cos(segStart) * (radius - 9);
                var pegY = Math.sin(segStart) * (radius - 9);
                ctx.beginPath();
                ctx.arc(pegX, pegY, 4, 0, 2 * Math.PI);
                ctx.fillStyle = '#ffffff';
                ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
                ctx.shadowBlur = 3;
                ctx.fill();
                ctx.shadowColor = 'transparent';

                // Draw Text (Centred radially along the wedge)
                ctx.save();
                var midAngle = segStart + arcAngle / 2;
                ctx.rotate(midAngle);

                var hubRadius = 52;
                drawSliceText(ctx, seg.name, hubRadius, radius, arcAngle);

                ctx.restore();
            }

            // Outer metallic rim
            ctx.beginPath();
            ctx.arc(0, 0, radius, 0, 2 * Math.PI);
            ctx.lineWidth = 6;
            ctx.strokeStyle = '#ffffff';
            ctx.stroke();

            ctx.restore();
        }

        // Helper to draw large, bold, centered radial text like Wheel of Names
        function drawSliceText(ctx, text, hubRadius, radius, arcAngle) {
            var availableLength = (radius - hubRadius) - 24;
            var maxThickness = (hubRadius + radius) * Math.sin(arcAngle / 2) * 0.72;

            var words = text.split(' ');
            var lines = [];
            if (words.length > 2 && text.length > 13) {
                var mid = Math.ceil(words.length / 2);
                lines = [words.slice(0, mid).join(' '), words.slice(mid).join(' ')];
            } else {
                lines = [text];
            }

            // Calculate maximum bold font size
            var testSize = Math.min(52, Math.floor(maxThickness / (lines.length * 1.12)));
            testSize = Math.max(18, testSize);

            while (testSize > 16) {
                ctx.font = '900 ' + testSize + 'px "Outfit", "Inter", sans-serif';
                var maxW = 0;
                for (var l = 0; l < lines.length; l++) {
                    var w = ctx.measureText(lines[l]).width;
                    if (w > maxW) maxW = w;
                }
                if (maxW <= availableLength) {
                    break;
                }
                testSize -= 2;
            }

            ctx.font = '900 ' + testSize + 'px "Outfit", "Inter", sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = 'rgba(0, 0, 0, 0.7)';
            ctx.shadowBlur = 6;
            ctx.shadowOffsetX = 1;
            ctx.shadowOffsetY = 1;

            var textDistance = hubRadius + (radius - hubRadius) * 0.52;
            var lineHeight = testSize * 1.15;
            var totalHeight = lines.length * lineHeight;
            var startY = -(totalHeight / 2) + lineHeight / 2;

            for (var i = 0; i < lines.length; i++) {
                ctx.fillText(lines[i], textDistance, startY + i * lineHeight);
            }
        }

        // Initialize Canvas
        resizeCanvas();

        // ==========================================
        // 4. FLAPPER POINTER & SPIN PHYSICS ENGINE
        // ==========================================
        var idleAnimId = null;
        var isIdle = true;
        var idleSpeed = 0.0032; // smooth, gentle ambient rotation on standby

        function startIdleAnimation() {
            if (isSpinning) return;
            isIdle = true;
            if (idleAnimId) cancelAnimationFrame(idleAnimId);

            function idleLoop() {
                if (!isIdle || isSpinning) return;
                currentRotation += idleSpeed;
                drawWheel(currentRotation);
                idleAnimId = requestAnimationFrame(idleLoop);
            }
            idleAnimId = requestAnimationFrame(idleLoop);
        }

        function stopIdleAnimation() {
            isIdle = false;
            if (idleAnimId) {
                cancelAnimationFrame(idleAnimId);
                idleAnimId = null;
            }
        }

        // Start standby spinning right away
        startIdleAnimation();

        function flickPointer() {
            wheelPointer.classList.remove('flick');
            void wheelPointer.offsetWidth; // trigger reflow
            wheelPointer.classList.add('flick');
            setTimeout(function() {
                wheelPointer.classList.remove('flick');
            }, 60);
        }

        /**
         * Spin the wheel to land on targetIndex under the right-side pointer.
         * The right pointer is at 0 rad (3 o'clock).
         */
        function animateSpinTo(targetIndex, onFinish) {
            stopIdleAnimation();
            isSpinning = true;
            wheelContainer.classList.add('is-spinning');
            drawBtn.disabled = true;

            // Target segment center in local coordinates
            var targetMidAngle = targetIndex * arcAngle + arcAngle / 2;

            // Alignment to angle 0 (3 o'clock pointer):
            // (currentAngle + totalDelta + targetMidAngle) % 2PI = 0
            var desiredLocalAngle = (2 * Math.PI - (targetMidAngle % (2 * Math.PI))) % (2 * Math.PI);
            var currentMod = ((currentRotation % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
            var delta = desiredLocalAngle - currentMod;
            if (delta < 0) {
                delta += 2 * Math.PI;
            }

            // Small random jitter within +/- 25% of slice so it doesn't land dead center every time
            var jitter = (Math.random() - 0.5) * (arcAngle * 0.4);

            // Minimum 6 full rotations for dramatic suspense
            var fullRounds = (6 + Math.floor(Math.random() * 2)) * 2 * Math.PI;
            var totalRotation = fullRounds + delta + jitter;

            var startAngle = currentRotation;
            var startTime = performance.now();
            var duration = 5800; // 5.8 seconds

            var lastPegIndex = -1;

            // Custom ease-out curve (deceleration)
            function easeOutQuart(t) {
                return 1 - Math.pow(1 - t, 4);
            }

            function frame(now) {
                var elapsed = now - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var eased = easeOutQuart(progress);

                currentRotation = startAngle + totalRotation * eased;
                drawWheel(currentRotation);

                // Check which slice boundary is crossing the right pointer (angle 0)
                var localAngleAtPointer = ((2 * Math.PI - (currentRotation % (2 * Math.PI))) + 2 * Math.PI) % (2 * Math.PI);
                var currentPeg = Math.floor(localAngleAtPointer / arcAngle);

                if (currentPeg !== lastPegIndex) {
                    lastPegIndex = currentPeg;
                    playPegTick();
                    flickPointer();
                }

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    isSpinning = false;
                    wheelContainer.classList.remove('is-spinning');
                    drawBtn.disabled = false;
                    if (onFinish) onFinish();
                }
            }

            requestAnimationFrame(frame);
        }

        // ==========================================
        // 5. CELEBRATION & WINNER POPUP MODAL
        // ==========================================
        var winnerModal = document.getElementById('winner-modal');
        var winnerTitle = document.getElementById('winner-modal-title');
        var winnerUser = document.getElementById('winner-modal-user');
        var winnerBadge = document.getElementById('winner-modal-badge');
        var btnModalClose = document.getElementById('btn-modal-close');
        var btnModalAgain = document.getElementById('btn-modal-again');
        var winnersLogContainer = document.getElementById('winners-list-container');
        var winnerEmptyState = document.getElementById('winner-empty-state');
        var badgeWinnersCount = document.getElementById('badge-winners-count');
        var winnersCount = 0;

        function triggerConfetti() {
            if (typeof confetti === 'function') {
                // Confetti cannon from left
                confetti({
                    particleCount: 70,
                    angle: 60,
                    spread: 65,
                    origin: {
                        x: 0.1,
                        y: 0.6
                    }
                });
                // Confetti cannon from right
                confetti({
                    particleCount: 70,
                    angle: 120,
                    spread: 65,
                    origin: {
                        x: 0.9,
                        y: 0.6
                    }
                });
                // Center burst
                setTimeout(function() {
                    confetti({
                        particleCount: 90,
                        spread: 100,
                        origin: {
                            y: 0.45
                        }
                    });
                }, 250);
            }
        }

        function showWinnerCelebration(prizeName, recipientName) {
            var isWin = prizeName && prizeName !== 'Try Again';

            var fallbackName = (typeof currentSpinMode !== 'undefined' && currentSpinMode === 'anonymous')
                ? 'Anonymous Participant'
                : 'Guest Participant';
            var finalRecipient = (recipientName && recipientName.trim()) ? recipientName.trim() : fallbackName;

            winnerTitle.textContent = prizeName || 'Try Again';
            winnerUser.textContent = finalRecipient;

            if (isWin) {
                winnerBadge.textContent = '🎉 Congratulations! 🎉';
                winnerBadge.style.color = '#f59e0b';
                playFanfare();
                triggerConfetti();
            } else {
                winnerBadge.textContent = '🙏 Better Luck Next Time! 🙏';
                winnerBadge.style.color = '#94a3b8';
            }

            winnerModal.classList.add('active');
            if (typeof stopCamera === 'function') {
                stopCamera();
            }

            // Log to Winners Tab
            addWinnerToLog(prizeName || 'Try Again', finalRecipient, isWin);
        }

        var STORAGE_KEY = 'dmc_lucky_draw_winners_history';

        function loadWinnersFromCache() {
            try {
                var cached = localStorage.getItem(STORAGE_KEY);
                if (cached) {
                    var items = JSON.parse(cached);
                    if (Array.isArray(items) && items.length > 0) {
                        if (winnerEmptyState) {
                            winnerEmptyState.style.display = 'none';
                        }
                        winnersCount = items.length;
                        badgeWinnersCount.textContent = winnersCount;

                        items.forEach(function(item) {
                            renderWinnerCard(item, false);
                        });
                    }
                }
            } catch (e) {
                console.warn("Could not load winners from localStorage:", e);
            }
        }

        function saveWinnerToCache(item) {
            try {
                var cached = localStorage.getItem(STORAGE_KEY);
                var items = cached ? JSON.parse(cached) : [];
                if (!Array.isArray(items)) items = [];
                items.unshift(item);
                if (items.length > 100) items = items.slice(0, 100);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            } catch (e) {
                console.warn("Could not save winner to localStorage:", e);
            }
        }

        function renderWinnerCard(item, isNew) {
            var logCard = document.createElement('div');
            logCard.className = 'winner-log-card';
            logCard.style.borderLeftColor = item.isWin ? 'var(--dmc-red)' : '#94a3b8';

            logCard.innerHTML = `
                <div class="winner-log-prize">
                    <i class="${item.isWin ? 'fas fa-gift text-warning' : 'fas fa-redo text-secondary'}"></i>
                    <span>${escapeHtml(item.prize)}</span>
                </div>
                <div class="winner-log-meta">
                    <strong>Winner:</strong> ${escapeHtml(item.winner || 'Guest Participant')}
                </div>
                <div class="winner-log-time">
                    <i class="far fa-clock"></i> ${escapeHtml(item.time)}
                </div>
            `;

            if (isNew) {
                winnersLogContainer.insertBefore(logCard, winnersLogContainer.firstChild);
            } else {
                winnersLogContainer.appendChild(logCard);
            }
        }

        function addWinnerToLog(prizeName, recipientName, isWin) {
            if (winnerEmptyState) {
                winnerEmptyState.style.display = 'none';
            }

            winnersCount++;
            badgeWinnersCount.textContent = winnersCount;

            var timeStr = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            var item = {
                id: Date.now(),
                prize: prizeName || 'Try Again',
                winner: recipientName || 'Guest Participant',
                isWin: isWin,
                time: timeStr
            };

            renderWinnerCard(item, true);
            saveWinnerToCache(item);
        }

        // Initialize cache on page load
        loadWinnersFromCache();

        function closeWinnerModal() {
            winnerModal.classList.remove('active');
            startIdleAnimation();
        }

        btnModalClose.addEventListener('click', closeWinnerModal);

        winnerModal.addEventListener('click', function(e) {
            if (e.target === winnerModal) {
                closeWinnerModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && winnerModal.classList.contains('active')) {
                closeWinnerModal();
            }
        });

        btnModalAgain.addEventListener('click', function() {
            winnerModal.classList.remove('active');
            triggerDraw();
        });

        var btnClearHistory = document.getElementById('btn-clear-history');
        if (btnClearHistory) {
            btnClearHistory.addEventListener('click', function() {
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch (e) {}
                winnersLogContainer.innerHTML = '';
                winnersCount = 0;
                badgeWinnersCount.textContent = 0;
                if (winnerEmptyState) {
                    winnerEmptyState.style.display = 'block';
                    winnersLogContainer.appendChild(winnerEmptyState);
                }
            });
        }

        // ==========================================
        // 6. FORM & SUBMISSION INTEGRATION (LIVE CAMERA SCANNER)
        // ==========================================
        var luckyForm = document.getElementById('lucky-draw-form');
        var drawBtn = document.getElementById('btn-draw');
        var entryIdInput = document.getElementById('entry_id');
        var cardStatus = document.getElementById('card-upload-status');
        var cardUploading = false;
        var currentMode = 'manual';

        // Camera elements
        var cameraVideo = document.getElementById('camera-video');
        var cameraCanvas = document.getElementById('camera-canvas');
        var cameraFlash = document.getElementById('camera-flash');
        var cameraViewfinder = document.getElementById('camera-viewfinder-container');
        var cameraPreviewContainer = document.getElementById('camera-preview-container');
        var cardPreviewImg = document.getElementById('card-preview-img');
        var cameraLoading = document.getElementById('camera-loading');
        var cameraError = document.getElementById('camera-error');
        var cameraErrorMsg = document.getElementById('cam-err-msg');
        var toolbarStream = document.getElementById('toolbar-stream');
        var toolbarPreview = document.getElementById('toolbar-preview');
        var btnShutter = document.getElementById('btn-shutter');
        var btnRetake = document.getElementById('btn-retake');
        var btnSwitchCamera = document.getElementById('btn-switch-camera');
        var btnRetryCamera = document.getElementById('btn-retry-camera');
        var fallbackInput = document.getElementById('business_card_fallback');

        var cameraStream = null;
        var currentFacingMode = 'environment';
        var isCameraActive = false;

        // Capture Mode Switcher (Manual vs Card)
        var modeButtons = document.querySelectorAll('.mode-toggle-btn');
        var panelManual = document.getElementById('panel-manual');
        var panelCard = document.getElementById('panel-card');

        // Spin Mode State (Anonymous vs Required Data)
        var currentSpinMode = 'anonymous';
        try {
            var savedSpinMode = localStorage.getItem('dmc_lucky_spin_mode');
            if (savedSpinMode === 'required' || savedSpinMode === 'anonymous') {
                currentSpinMode = savedSpinMode;
            }
        } catch (e) {}

        var spinModeInput = document.getElementById('spin_mode');
        var wheelHintText = document.getElementById('wheel-hint-text');
        var wheelHintIcon = document.getElementById('wheel-hint-icon');
        var hintName = document.getElementById('hint-name');
        var hintPhone = document.getElementById('hint-phone');
        var panelHeaderDesc = document.getElementById('panel-header-desc');

        function setSpinMode(mode, saveToStorage) {
            currentSpinMode = mode;
            if (saveToStorage !== false) {
                try {
                    localStorage.setItem('dmc_lucky_spin_mode', mode);
                } catch (e) {}
            }

            if (spinModeInput) spinModeInput.value = mode;

            var isAnon = mode === 'anonymous';

            // Update topbar segmented buttons
            var btnAnon = document.getElementById('btn-mode-anonymous');
            var btnReq = document.getElementById('btn-mode-required');
            if (btnAnon) btnAnon.classList.toggle('active', isAnon);
            if (btnReq) btnReq.classList.toggle('active', !isAnon);

            // Update form container for req-star styling
            if (luckyForm) {
                luckyForm.classList.toggle('mode-required-active', !isAnon);
            }

            if (panelHeaderDesc) {
                panelHeaderDesc.textContent = isAnon
                    ? 'Anonymous Mode: Participant details are optional. You can spin right away!'
                    : 'Required Mode: Participant details are required before spinning.';
            }

            // Update hint labels (Optional vs Required)
            if (hintName) hintName.textContent = isAnon ? '(Optional)' : '(Required)';
            if (hintPhone) hintPhone.textContent = isAnon ? '(Optional)' : '(Required)';

            // Update wheel stage hint
            if (wheelHintText) {
                if (isAnon) {
                    wheelHintText.innerHTML = 'Anonymous Mode: Click the wheel or click <strong>Draw Now</strong> to spin directly';
                } else {
                    wheelHintText.innerHTML = 'Required Mode: Complete participant details before spinning the wheel';
                }
            }
            if (wheelHintIcon) {
                wheelHintIcon.className = isAnon ? 'fas fa-hand-pointer text-primary' : 'fas fa-lock text-danger';
            }

            // Clear any invalid states if switching to anonymous
            if (isAnon) {
                document.querySelectorAll('.form-control-dark').forEach(function(el) {
                    el.classList.remove('is-invalid');
                });
            }

            updateSubmitAvailability();
        }

        function setCaptureMode(mode) {
            currentMode = mode;
            modeButtons.forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });
            panelManual.style.display = mode === 'manual' ? 'block' : 'none';
            panelCard.style.display = mode === 'card' ? 'block' : 'none';

            if (mode === 'card') {
                // If a card is not already successfully uploaded, start camera immediately
                if (!entryIdInput.value) {
                    startCamera(currentFacingMode);
                }
            } else {
                stopCamera();
            }
            updateSubmitAvailability();
        }

        modeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                setCaptureMode(btn.dataset.mode);
            });
        });

        function updateSubmitAvailability() {
            var blocked = cardUploading;
            drawBtn.disabled = blocked || isSpinning;
            drawBtn.querySelector('span').textContent = cardUploading ? 'Uploading...' : 'Draw Now';
        }

        function setCardStatus(text, kind) {
            if (!cardStatus) return;
            cardStatus.textContent = text;
            cardStatus.className = kind ? 'status-' + kind : '';
        }

        // Live Camera Functions
        function startCamera(facingMode) {
            stopCamera();
            facingMode = facingMode || currentFacingMode;
            currentFacingMode = facingMode;

            if (cameraViewfinder) cameraViewfinder.style.display = 'flex';
            if (cameraPreviewContainer) cameraPreviewContainer.style.display = 'none';
            if (toolbarStream) toolbarStream.style.display = 'flex';
            if (toolbarPreview) toolbarPreview.style.display = 'none';
            if (cameraLoading) cameraLoading.style.display = 'flex';
            if (cameraError) cameraError.style.display = 'none';

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraError('Browser does not support direct camera access.');
                return;
            }

            var constraints = {
                video: {
                    facingMode: { ideal: facingMode },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .catch(function(err) {
                    console.warn('Fallback to default video camera:', err);
                    return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                })
                .then(function(stream) {
                    cameraStream = stream;
                    if (cameraVideo) {
                        cameraVideo.srcObject = stream;
                        cameraVideo.onloadedmetadata = function() {
                            cameraVideo.play().catch(function(e) { console.warn(e); });
                        };
                    }
                    isCameraActive = true;
                    if (cameraLoading) cameraLoading.style.display = 'none';
                })
                .catch(function(err) {
                    console.error('Camera error:', err);
                    showCameraError('Camera access denied or no camera detected.');
                });
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) {
                    track.stop();
                });
                cameraStream = null;
            }
            if (cameraVideo) {
                cameraVideo.srcObject = null;
            }
            isCameraActive = false;
            if (cameraLoading) cameraLoading.style.display = 'none';
        }

        function showCameraError(msg) {
            if (cameraLoading) cameraLoading.style.display = 'none';
            if (cameraError) {
                cameraError.style.display = 'flex';
                if (cameraErrorMsg) cameraErrorMsg.textContent = msg;
            }
        }

        var currentCapturedCardBlob = null;

        // Proportional client-side image compression (max 1280px, quality 0.82)
        function compressImageFile(fileOrBlob, callback) {
            var img = new Image();
            var objectUrl = URL.createObjectURL(fileOrBlob);
            img.onload = function() {
                URL.revokeObjectURL(objectUrl);
                var maxDim = 1280;
                var w = img.width;
                var h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                canvas.toBlob(function(blob) {
                    callback(blob || fileOrBlob);
                }, 'image/jpeg', 0.82);
            };
            img.onerror = function() {
                URL.revokeObjectURL(objectUrl);
                callback(fileOrBlob);
            };
            img.src = objectUrl;
        }

        function capturePhoto() {
            if (!cameraVideo || !cameraVideo.videoWidth) return;

            // Flash effect
            if (cameraFlash) {
                cameraFlash.classList.add('flash');
                setTimeout(function() {
                    cameraFlash.classList.remove('flash');
                }, 250);
            }

            // Audio shutter click
            playPegTick();

            // Resize snapshot on high-res canvas (max 1280px)
            var maxDim = 1280;
            var w = cameraVideo.videoWidth;
            var h = cameraVideo.videoHeight;
            if (w > maxDim || h > maxDim) {
                if (w > h) {
                    h = Math.round((h * maxDim) / w);
                    w = maxDim;
                } else {
                    w = Math.round((w * maxDim) / h);
                    h = maxDim;
                }
            }

            cameraCanvas.width = w;
            cameraCanvas.height = h;
            var ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraVideo, 0, 0, w, h);

            // Display in preview box
            var dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.82);
            if (cardPreviewImg) cardPreviewImg.src = dataUrl;
            if (cameraViewfinder) cameraViewfinder.style.display = 'none';
            if (cameraPreviewContainer) cameraPreviewContainer.style.display = 'block';
            if (toolbarStream) toolbarStream.style.display = 'none';
            if (toolbarPreview) toolbarPreview.style.display = 'flex';

            // Turn off live camera once picture is taken
            stopCamera();

            // Convert to Blob & process upload/offline save
            cameraCanvas.toBlob(function(blob) {
                if (!blob) {
                    setCardStatus('Gagal memproses gambar.', 'error');
                    return;
                }
                currentCapturedCardBlob = blob;
                uploadBusinessCardFile(blob, 'business_card.jpg');
            }, 'image/jpeg', 0.82);
        }

        function uploadBusinessCardFile(fileOrBlob, filename) {
            filename = filename || 'business_card.jpg';
            entryIdInput.value = '';
            currentCapturedCardBlob = fileOrBlob;
            cardUploading = true;
            setCardStatus('Mengompres & memproses kartu nama...', 'uploading');
            updateSubmitAvailability();

            // If offline, store locally and allow instant draw without waiting!
            if (!navigator.onLine || !isAppOnline) {
                cardUploading = false;
                setCardStatus('Kartu nama tersimpan di tablet (Mode Offline) ✓ Siap undi!', 'done');
                updateSubmitAvailability();
                return;
            }

            var formData = new FormData();
            formData.append('business_card', fileOrBlob, filename);
            formData.append('_token', '{{ csrf_token() }}');

            var controller = new AbortController();
            var timeoutId = setTimeout(function() {
                controller.abort();
            }, 3500); // 3.5s timeout for crowded expo networks

            fetch('{{ url('lucky-draw/business-card') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData,
                    signal: controller.signal
                })
                .then(function(res) {
                    clearTimeout(timeoutId);
                    return res.ok ? res.json() : Promise.reject(res);
                })
                .then(function(data) {
                    entryIdInput.value = data.entry_id;
                    setCardStatus('Kartu nama tersimpan di server ✓ Siap undi!', 'done');
                })
                .catch(function(err) {
                    clearTimeout(timeoutId);
                    console.warn('[Network] Upload kartu nama lambat/gagal, disimpan di memori tablet:', err);
                    // Do not block participant! Allow draw using offline queue
                    setCardStatus('Kartu nama tersimpan di tablet ✓ Siap undi!', 'done');
                })
                .finally(function() {
                    cardUploading = false;
                    updateSubmitAvailability();
                });
        }

        if (btnShutter) {
            btnShutter.addEventListener('click', function(e) {
                e.preventDefault();
                capturePhoto();
            });
        }

        if (btnRetake) {
            btnRetake.addEventListener('click', function(e) {
                e.preventDefault();
                entryIdInput.value = '';
                currentCapturedCardBlob = null;
                setCardStatus('');
                updateSubmitAvailability();
                startCamera(currentFacingMode);
            });
        }

        if (btnSwitchCamera) {
            btnSwitchCamera.addEventListener('click', function(e) {
                e.preventDefault();
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                startCamera(currentFacingMode);
            });
        }

        if (btnRetryCamera) {
            btnRetryCamera.addEventListener('click', function(e) {
                e.preventDefault();
                startCamera(currentFacingMode);
            });
        }

        if (fallbackInput) {
            fallbackInput.addEventListener('change', function() {
                var file = fallbackInput.files && fallbackInput.files[0];
                if (!file) return;

                stopCamera();
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (cardPreviewImg) cardPreviewImg.src = e.target.result;
                    if (cameraViewfinder) cameraViewfinder.style.display = 'none';
                    if (cameraPreviewContainer) cameraPreviewContainer.style.display = 'block';
                    if (toolbarStream) toolbarStream.style.display = 'none';
                    if (toolbarPreview) toolbarPreview.style.display = 'flex';
                };
                reader.readAsDataURL(file);

                compressImageFile(file, function(compressedBlob) {
                    currentCapturedCardBlob = compressedBlob;
                    uploadBusinessCardFile(compressedBlob, file.name);
                });
            });
        }

        // Spin Mode Eligibility Validation
        function validateSpinEligibility() {
            if (currentSpinMode === 'anonymous') {
                return true;
            }

            // Mode Wajib Isi Data is active: ensure user is on the Form tab to see inputs
            var formTabBtn = document.querySelector('.tab-btn[data-tab="form"]');
            var tabForm = document.getElementById('tab-form');
            if (tabForm && !tabForm.classList.contains('active') && formTabBtn) {
                formTabBtn.click();
            }

            if (currentMode === 'manual') {
                var nameInput = document.getElementById('input-name');
                var phoneEl = document.getElementById('phone');
                var nameVal = nameInput ? nameInput.value.trim() : '';
                var phoneVal = phoneEl ? (iti ? iti.getNumber() : phoneEl.value.trim()) : '';

                var hasError = false;

                if (!nameVal) {
                    if (nameInput) {
                        nameInput.classList.add('is-invalid');
                        var grp = nameInput.closest('.form-group');
                        if (grp) {
                            grp.classList.add('shake-element');
                            setTimeout(function() { grp.classList.remove('shake-element'); }, 500);
                        }
                    }
                    hasError = true;
                } else if (nameInput) {
                    nameInput.classList.remove('is-invalid');
                }

                if (!phoneVal) {
                    if (phoneEl) {
                        phoneEl.classList.add('is-invalid');
                        var grpPhone = phoneEl.closest('.form-group');
                        if (grpPhone) {
                            grpPhone.classList.add('shake-element');
                            setTimeout(function() { grpPhone.classList.remove('shake-element'); }, 500);
                        }
                    }
                    hasError = true;
                } else if (phoneEl) {
                    phoneEl.classList.remove('is-invalid');
                }

                if (hasError) {
                    swal({
                        title: "Participant Details Required!",
                        text: "Required Mode is active. Please enter Participant's Full Name and Phone Number before spinning the wheel.",
                        icon: "warning",
                        button: "Complete Details"
                    }).then(function() {
                        if (!nameVal && nameInput) {
                            nameInput.focus();
                        } else if (!phoneVal && phoneEl) {
                            phoneEl.focus();
                        }
                    });
                    return false;
                }
                return true;
            } else if (currentMode === 'card') {
                if (cardUploading) {
                    swal({
                        title: "Uploading in Progress...",
                        text: "Please wait a moment until the business card is processed.",
                        icon: "info",
                        button: "OK"
                    });
                    return false;
                }

                // Eligible if uploaded online (entryIdInput) OR captured offline (currentCapturedCardBlob)
                if (!entryIdInput.value && !currentCapturedCardBlob) {
                    var cardWrapper = document.querySelector('.camera-card-wrapper');
                    if (cardWrapper) {
                        cardWrapper.classList.add('shake-element');
                        setTimeout(function() { cardWrapper.classList.remove('shake-element'); }, 500);
                    }
                    swal({
                        title: "Business Card Required!",
                        text: "Required Mode is active. Please capture or upload a business card before spinning the wheel.",
                        icon: "warning",
                        button: "Capture Photo"
                    });
                    return false;
                }
                return true;
            }

            return true;
        }

        // Trigger Draw Function with Fast Timeout & Resilient Offline Queue
        function triggerDraw() {
            if (isSpinning || drawBtn.disabled) return;

            // Validate eligibility based on currentSpinMode
            if (!validateSpinEligibility()) {
                return;
            }

            stopCamera();
            initAudio();

            var participantName = document.getElementById('input-name') ? document.getElementById('input-name').value.trim() : '';
            var companyName = document.getElementById('input-company') ? document.getElementById('input-company').value.trim() : '';
            var jobTitle = document.getElementById('input-job-title') ? document.getElementById('input-job-title').value.trim() : '';
            var phoneEl = document.getElementById('phone');
            var phoneVal = phoneEl ? (iti ? iti.getNumber() : phoneEl.value.trim()) : '';
            var emailVal = document.getElementById('input-email') ? document.getElementById('input-email').value.trim() : '';

            var formData = new FormData(luckyForm);
            if (phoneVal) {
                formData.set('phone', phoneVal);
            }
            if (!entryIdInput.value && currentCapturedCardBlob) {
                formData.append('business_card', currentCapturedCardBlob, 'business_card.jpg');
            }

            drawBtn.disabled = true;
            drawBtn.querySelector('span').textContent = 'Drawing...';

            function finalizeSpinState() {
                drawBtn.disabled = false;
                drawBtn.querySelector('span').textContent = 'Draw Now';

                // Reset form fields
                luckyForm.reset();
                setCaptureMode('manual');
                if (cardPreviewImg) cardPreviewImg.src = '';
                if (cameraViewfinder) cameraViewfinder.style.display = 'flex';
                if (cameraPreviewContainer) cameraPreviewContainer.style.display = 'none';
                if (toolbarStream) toolbarStream.style.display = 'flex';
                if (toolbarPreview) toolbarPreview.style.display = 'none';
                setCardStatus('');
                entryIdInput.value = '';
                currentCapturedCardBlob = null;
                if (iti) iti.setNumber('');

                // Clear invalid states
                document.querySelectorAll('.form-control-dark').forEach(function(el) {
                    el.classList.remove('is-invalid');
                });

                // Reapply current spin mode so input and UI remain in sync
                setSpinMode(currentSpinMode, false);
            }

            function handleOfflineDrawExecution() {
                try {
                    var offlineResult = drawPrizeOffline();
                    var targetIndex = (offlineResult && typeof offlineResult.index === 'number')
                        ? offlineResult.index
                        : (wheelSegments.length - 1);
                    var wonSeg = (offlineResult && offlineResult.segment)
                        ? offlineResult.segment
                        : wheelSegments[targetIndex];
                    var prizeName = wonSeg ? wonSeg.name : 'Try Again';
                    var prizeId = wonSeg ? (wonSeg.id || null) : null;

                    var offlineEntry = {
                        id: 'off_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                        created_at: new Date().toISOString(),
                        drawn_at: new Date().toISOString(),
                        name: participantName,
                        company_name: companyName,
                        job_title: jobTitle,
                        phone: phoneVal,
                        email: emailVal,
                        spin_mode: currentSpinMode,
                        capture_mode: (typeof currentMode !== 'undefined' ? currentMode : 'manual'),
                        lucky_draw_item_id: prizeId,
                        prize_name: prizeName,
                        card_blob: currentCapturedCardBlob || null,
                        synced: false
                    };

                    OfflineDB.addEntry(offlineEntry).then(function() {
                        refreshNetworkQueueUI();
                    }).catch(function(err) {
                        console.error('[OfflineDB] Error saving offline entry:', err);
                    });

                    animateSpinTo(targetIndex, function() {
                        showWinnerCelebration(prizeName, participantName);
                        finalizeSpinState();
                    });
                } catch (drawErr) {
                    console.error('[Draw] Error executing offline draw:', drawErr);
                    finalizeSpinState();
                }
            }

            // If browser or app is offline, trigger offline draw immediately without waiting
            if (!navigator.onLine || !isAppOnline) {
                handleOfflineDrawExecution();
                return;
            }

            // Online attempt with fast timeout (3.5s) for congested expo network
            var controller = new AbortController();
            var timeoutId = setTimeout(function() {
                controller.abort();
            }, 3500);

            fetch(luckyForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData,
                    signal: controller.signal
                })
                .then(function(res) {
                    clearTimeout(timeoutId);
                    return res.ok ? res.json() : Promise.reject(res);
                })
                .then(function(data) {
                    var prizeName = data.prize;
                    var targetIndex = -1;

                    if (prizeName) {
                        targetIndex = wheelSegments.findIndex(function(s) {
                            return s.name.trim().toLowerCase() === prizeName.trim().toLowerCase();
                        });
                    }

                    if (targetIndex === -1) {
                        targetIndex = wheelSegments.length - 1;
                    }

                    animateSpinTo(targetIndex, function() {
                        showWinnerCelebration(data.prize, participantName);
                        finalizeSpinState();
                    });
                })
                .catch(function(err) {
                    clearTimeout(timeoutId);
                    console.warn('[Draw] Server slow/unreachable, continuing with offline spin:', err);
                    isAppOnline = false;
                    updateOnlineStatusUI(false);
                    handleOfflineDrawExecution();
                });
        }

        // Mode Switch Event Listeners
        var btnModeAnon = document.getElementById('btn-mode-anonymous');
        if (btnModeAnon) {
            btnModeAnon.addEventListener('click', function(e) {
                e.preventDefault();
                setSpinMode('anonymous');
            });
        }

        var btnModeReq = document.getElementById('btn-mode-required');
        if (btnModeReq) {
            btnModeReq.addEventListener('click', function(e) {
                e.preventDefault();
                setSpinMode('required');
            });
        }

        // Clear invalid error highlights as user types
        var nameField = document.getElementById('input-name');
        if (nameField) {
            nameField.addEventListener('input', function() {
                if (nameField.value.trim()) {
                    nameField.classList.remove('is-invalid');
                }
            });
        }

        var phoneField = document.getElementById('phone');
        if (phoneField) {
            phoneField.addEventListener('input', function() {
                var val = iti ? iti.getNumber() : phoneField.value.trim();
                if (val) {
                    phoneField.classList.remove('is-invalid');
                }
            });
        }

        // Initialize spin mode UI
        setSpinMode(currentSpinMode, false);

        // Click anywhere on wheel to spin
        wheelContainer.addEventListener('click', function(e) {
            triggerDraw();
        });

        // Form Submit listener
        luckyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerDraw();
        });

        // Sidebar Tabs Switcher
        var tabBtns = document.querySelectorAll('.tab-btn');
        var tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetTab = btn.dataset.tab;
                if (targetTab !== 'form') {
                    stopCamera();
                } else if (currentMode === 'card' && !entryIdInput.value) {
                    startCamera(currentFacingMode);
                }

                tabBtns.forEach(function(b) {
                    b.classList.remove('active');
                });
                tabPanes.forEach(function(p) {
                    p.classList.remove('active');
                });

                btn.classList.add('active');
                var activePane = document.getElementById('tab-' + targetTab);
                if (activePane) {
                    activePane.classList.add('active');
                }
            });
        });

        // Clean up camera when page unloads
        window.addEventListener('beforeunload', function() {
            stopCamera();
        });

        // ==========================================
        // NETWORK STATUS & OFFLINE QUEUE CONTROLLER
        // ==========================================
        var isSyncing = false;

        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function checkNetworkConnection() {
            var badge = document.getElementById('btn-network-status');
            var statusText = document.getElementById('network-status-text');
            var banner = document.getElementById('sync-connection-banner');

            if (!navigator.onLine) {
                isAppOnline = false;
                if (badge) badge.className = 'network-status-badge offline';
                if (statusText) statusText.textContent = 'Offline';
                if (banner) {
                    banner.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#ef4444"></i> <span>Koneksi Offline (Tidak ada jaringan)</span>';
                }
                return Promise.resolve(false);
            }

            var controller = new AbortController();
            var timeoutId = setTimeout(function() { controller.abort(); }, 3000);

            return fetch('{{ url('lucky-draw/ping') }}', { signal: controller.signal })
                .then(function(res) {
                    clearTimeout(timeoutId);
                    var ok = res.ok;
                    isAppOnline = ok;
                    updateOnlineStatusUI(ok);
                    return ok;
                })
                .catch(function() {
                    clearTimeout(timeoutId);
                    isAppOnline = false;
                    updateOnlineStatusUI(false);
                    return false;
                });
        }

        function updateOnlineStatusUI(isOnline) {
            isAppOnline = !!isOnline;
            var badge = document.getElementById('btn-network-status');
            var statusText = document.getElementById('network-status-text');
            var banner = document.getElementById('sync-connection-banner');

            OfflineDB.countAll().then(function(stats) {
                if (!isOnline) {
                    if (badge) badge.className = 'network-status-badge offline';
                    if (statusText) statusText.textContent = 'Offline';
                    if (banner) {
                        banner.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> <span>Server DMC tidak merespons (Jaringan Expo Padat)</span>';
                    }
                } else {
                    if (stats.pending > 0) {
                        if (badge) badge.className = 'network-status-badge pending';
                        if (statusText) statusText.textContent = stats.pending + ' Pending';
                    } else {
                        if (badge) badge.className = 'network-status-badge online';
                        if (statusText) statusText.textContent = 'Online';
                    }
                    if (banner) {
                        banner.innerHTML = '<i class="fas fa-check-circle" style="color:#10b981"></i> <span>Terhubung ke server DMC</span>';
                    }
                }
            });
        }

        function refreshNetworkQueueUI() {
            OfflineDB.countAll().then(function(stats) {
                var countEl = document.getElementById('network-queue-count');
                var statPending = document.getElementById('sync-stat-pending');
                var statSynced = document.getElementById('sync-stat-synced');
                var queueList = document.getElementById('offline-queue-list');
                var syncBtn = document.getElementById('btn-trigger-sync');

                if (countEl) {
                    if (stats.pending > 0) {
                        countEl.style.display = 'inline-block';
                        countEl.textContent = stats.pending;
                    } else {
                        countEl.style.display = 'none';
                    }
                }

                if (statPending) statPending.textContent = stats.pending;
                if (statSynced) statSynced.textContent = stats.synced;
                if (syncBtn) {
                    syncBtn.disabled = stats.pending === 0 || isSyncing;
                }

                if (queueList) {
                    if (stats.items.length === 0) {
                        queueList.innerHTML = '<div class="offline-empty-state">Belum ada antrean di tablet. Semua data aman!</div>';
                    } else {
                        var html = '';
                        stats.items.slice(-20).reverse().forEach(function(item) {
                            var dateStr = new Date(item.drawn_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            var badgeClass = item.synced ? 'synced' : 'pending';
                            var badgeLabel = item.synced ? 'Synced ✓' : 'Pending';
                            var pName = item.name || 'Anonymous';
                            var pPrize = item.prize_name || 'Try Again';

                            html += '<div class="offline-queue-item">' +
                                '<div class="offline-queue-item-info">' +
                                    '<span class="offline-queue-item-name">' + escapeHtml(pName) + ' <small style="color:var(--text-muted)">(' + dateStr + ')</small></span>' +
                                    '<span class="offline-queue-item-prize">' + escapeHtml(pPrize) + '</span>' +
                                '</div>' +
                                '<span class="offline-queue-item-badge ' + badgeClass + '">' + badgeLabel + '</span>' +
                            '</div>';
                        });
                        queueList.innerHTML = html;
                    }
                }

                checkNetworkConnection();
            });
        }

        async function syncPendingEntries(isManual) {
            if (isSyncing) return;

            var unsynced = await OfflineDB.getAllUnsynced();
            if (unsynced.length === 0) {
                if (isManual) {
                    swal({ title: "Semua Terunggah", text: "Semua data undian di tablet sudah tersimpan di server pusat!", icon: "success", timer: 2000, buttons: false });
                }
                refreshNetworkQueueUI();
                return;
            }

            var isOnline = await checkNetworkConnection();
            if (!isOnline) {
                if (isManual) {
                    swal({ title: "Koneksi Belum Siap", text: "Koneksi internet belum stabil. Data tetap aman di memori tablet dan akan disinkronkan otomatis saat sinyal pulih.", icon: "info" });
                }
                return;
            }

            isSyncing = true;
            var progressContainer = document.getElementById('sync-progress-container');
            var progressBar = document.getElementById('sync-progress-bar');
            var progressText = document.getElementById('sync-progress-text');
            var syncBtn = document.getElementById('btn-trigger-sync');

            if (progressContainer) progressContainer.style.display = 'block';
            if (syncBtn) syncBtn.disabled = true;

            var total = unsynced.length;
            var successCount = 0;

            for (var i = 0; i < total; i++) {
                var entry = unsynced[i];
                var pct = Math.round(((i) / total) * 100);
                if (progressBar) progressBar.style.width = pct + '%';
                if (progressText) progressText.textContent = 'Mengunggah ' + (i + 1) + ' dari ' + total + ' (' + (entry.name || 'Anonymous') + ')...';

                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('name', entry.name || '');
                fd.append('company_name', entry.company_name || '');
                fd.append('job_title', entry.job_title || '');
                fd.append('phone', entry.phone || '');
                fd.append('email', entry.email || '');
                fd.append('spin_mode', entry.spin_mode || 'anonymous');
                fd.append('drawn_at', entry.drawn_at || entry.created_at);
                if (entry.lucky_draw_item_id) {
                    fd.append('lucky_draw_item_id', entry.lucky_draw_item_id);
                }
                if (entry.prize_name) {
                    fd.append('prize_name', entry.prize_name);
                }
                if (entry.card_blob) {
                    fd.append('business_card', entry.card_blob, 'business_card.jpg');
                }

                try {
                    var res = await fetch('{{ url('lucky-draw/sync-offline') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: fd
                    });
                    if (res.ok) {
                        await OfflineDB.markSynced(entry.id);
                        successCount++;
                    }
                } catch (e) {
                    console.warn('[Sync] Gagal upload item ' + entry.id, e);
                    break;
                }
            }

            if (progressBar) progressBar.style.width = '100%';
            if (progressText) progressText.textContent = 'Selesai: ' + successCount + ' data berhasil disinkronkan!';

            setTimeout(function() {
                if (progressContainer) progressContainer.style.display = 'none';
                isSyncing = false;
                refreshNetworkQueueUI();
                if (isManual && successCount > 0) {
                    swal({ title: "Berhasil!", text: successCount + " data peserta offline berhasil disinkronkan ke server!", icon: "success", timer: 2500, buttons: false });
                }
            }, 1000);
        }

        function exportOfflineDataBackup() {
            OfflineDB.countAll().then(function(stats) {
                if (stats.items.length === 0) {
                    swal({ title: "Data Kosong", text: "Tidak ada data antrean di tablet.", icon: "info" });
                    return;
                }

                var cleanItems = stats.items.map(function(item) {
                    return {
                        id: item.id,
                        created_at: item.created_at,
                        drawn_at: item.drawn_at,
                        name: item.name,
                        company: item.company_name,
                        job_title: item.job_title,
                        phone: item.phone,
                        email: item.email,
                        prize_won: item.prize_name,
                        spin_mode: item.spin_mode,
                        synced: item.synced,
                        has_card_photo: !!item.card_blob
                    };
                });

                var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(cleanItems, null, 2));
                var dlAnchor = document.createElement('a');
                dlAnchor.setAttribute("href", dataStr);
                dlAnchor.setAttribute("download", "dmc_lucky_draw_backup_" + new Date().toISOString().replace(/[:.]/g, "-") + ".json");
                document.body.appendChild(dlAnchor);
                dlAnchor.click();
                dlAnchor.remove();
            });
        }

        // Modal Controls & Event Listeners
        var btnNetworkStatus = document.getElementById('btn-network-status');
        var modalSync = document.getElementById('offline-sync-modal');
        var btnCloseSync = document.getElementById('btn-close-sync-modal');
        var btnTriggerSync = document.getElementById('btn-trigger-sync');
        var btnExportBackup = document.getElementById('btn-export-backup');

        if (btnNetworkStatus && modalSync) {
            btnNetworkStatus.addEventListener('click', function() {
                modalSync.style.display = 'flex';
                refreshNetworkQueueUI();
            });
        }

        if (btnCloseSync && modalSync) {
            btnCloseSync.addEventListener('click', function() {
                modalSync.style.display = 'none';
            });
        }

        if (modalSync) {
            modalSync.addEventListener('click', function(e) {
                if (e.target === modalSync) {
                    modalSync.style.display = 'none';
                }
            });
        }

        if (btnTriggerSync) {
            btnTriggerSync.addEventListener('click', function() {
                syncPendingEntries(true);
            });
        }

        if (btnExportBackup) {
            btnExportBackup.addEventListener('click', function() {
                exportOfflineDataBackup();
            });
        }

        // Auto-detect online/offline transitions
        window.addEventListener('online', function() {
            isAppOnline = true;
            checkNetworkConnection();
            syncPendingEntries(false);
        });

        window.addEventListener('offline', function() {
            isAppOnline = false;
            updateOnlineStatusUI(false);
        });

        // Periodic background health-check & auto-sync (every 30s)
        setInterval(function() {
            refreshNetworkQueueUI();
            if (navigator.onLine && !isSyncing) {
                syncPendingEntries(false);
            }
        }, 30000);

        // Initial check on page load
        refreshNetworkQueueUI();

        @if (session('success'))
            showWinnerCelebration(@json(session('prize')), '');
        @endif
    </script>
</body>

</html>
