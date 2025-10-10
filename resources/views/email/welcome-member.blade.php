<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Welcome to Djakarta Mining Club' }}</title>

    <style type="text/css">
        /* --- keep original styles (trimmed for brevity if you want) --- */
        p {
            margin: 10px 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            display: block;
            margin: 0;
            padding: 0;
        }

        img,
        a img {
            border: 0;
            height: auto;
            outline: none;
            text-decoration: none;
        }

        body,
        #bodyTable,
        #bodyCell {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .mcnPreviewText {
            display: none !important;
        }

        #outlook a {
            padding: 0;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        .ReadMsgBody,
        .ExternalClass {
            width: 100%;
        }

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass td,
        .ExternalClass div,
        .ExternalClass span,
        .ExternalClass font {
            line-height: 100%;
        }

        p,
        a,
        li,
        td,
        blockquote {
            mso-line-height-rule: exactly;
        }

        a[href^=tel],
        a[href^=sms] {
            color: inherit;
            cursor: default;
            text-decoration: none;
        }

        p,
        a,
        li,
        td,
        body,
        table,
        blockquote {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table[align=left] {
            float: left;
        }

        table[align=right] {
            float: right;
        }

        #bodyCell {
            padding: 10px;
        }

        .templateContainer {
            max-width: 600px !important;
        }

        a.mcnButton {
            display: block;
        }

        .mcnImage,
        .mcnRetinaImage {
            vertical-align: bottom;
        }

        .mcnTextContent {
            word-break: break-word;
        }

        .mcnTextContent img {
            height: auto !important;
        }

        .mcnDividerBlock {
            table-layout: fixed !important;
        }

        body,
        #bodyTable {
            background-color: #ffffff;
        }

        #templateHeader {
            background: #ffefc5;
            padding: 9px 0 0;
        }

        #templateBody {
            background: #ffefc5;
            padding: 0;
        }

        #templateColumns {
            background: #ffefc5;
            border-bottom: 2px solid #EAEAEA;
            padding-bottom: 9px;
        }

        #templateFooter {
            background: #4e6f5e;
            padding: 9px 0;
        }

        @media only screen and (min-width:768px) {
            .templateContainer {
                width: 600px !important;
            }
        }

        @media only screen and (max-width:480px) {

            body,
            table,
            td,
            p,
            a,
            li,
            blockquote {
                -webkit-text-size-adjust: none !important;
            }

            body {
                width: 100% !important;
                min-width: 100% !important;
            }

            .columnWrapper {
                max-width: 100% !important;
                width: 100% !important;
            }

            .mcnRetinaImage {
                max-width: 100% !important;
            }

            .mcnImage {
                width: 100% !important;
            }

            .mcnTextContent,
            .mcnBoxedTextContentColumn {
                padding-right: 18px !important;
                padding-left: 18px !important;
            }
        }
    </style>
</head>

<body>
    {{-- Preview text (hidden) --}}
    <span class="mcnPreviewText"
        style="display:none; font-size:0; line-height:0; max-height:0; max-width:0; opacity:0; overflow:hidden; visibility:hidden;">
        {{ $preview_text ?? '' }}
    </span>

    <center>
        <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
            <tr>
                <td align="center" valign="top" id="bodyCell">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">

                        {{-- Preheader (View in browser) --}}
                        <tr>
                            <td id="templatePreheader" style="background:#fbfbfb; padding:9px 0;">
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent"
                                                        style="padding:0 18px 9px; text-align:center;">
                                                        @if(!empty($archive_url))
                                                        <a href="{{ $archive_url }}" target="_blank" rel="noopener">View
                                                            this email in your browser</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Header banner --}}
                        <tr>
                            <td id="templateHeader">
                                <table width="100%" class="mcnImageBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnImageBlockInner" style="padding:0;">
                                            <table align="left" width="100%" class="mcnImageContentContainer"
                                                style="min-width:100%;">
                                                <tr>
                                                    <td class="mcnImageContent" style="padding:0; text-align:center;">
                                                        <img align="center" alt="Header"
                                                            src="{{ $header_image ?? 'https://mcusercontent.com/02704374ac4e9af520447c272/images/7ee79cc5-83da-0bac-0604-cda0d47325ed.png' }}"
                                                            width="600"
                                                            style="max-width:2500px; display:inline !important; vertical-align:bottom;">
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Intro copy --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent"
                                                        style="padding:0 18px 9px; line-height:125%; text-align:justify;">
                                                        <p
                                                            style="font-size:15px; font-family:Arial, Helvetica, sans-serif; margin:0 0 10px;">
                                                            Hi {{ $first_name ?? $name ?? 'there' }},
                                                        </p>
                                                        <p style="font-size:15px; margin:0;">
                                                            Thanks for signing up. Now you’ll join a network of 5,000+
                                                            mining professionals and enjoy benefits like priority
                                                            registration for industrial events, curated mining news, and
                                                            branding opportunities.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Primary CTA --}}
                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:10px; background-color:#4E6F5E;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $cta_url_1 ?? 'https://www.djakarta-miningclub.com' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $cta_text_1 ?? 'DIG IN' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>

                        {{-- Body blocks (kept as in source, with headings/paragraphs) --}}
                        <tr>
                            <td id="templateBody">
                                {{-- "What is Djakarta Mining Club?" --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <h3
                                                            style="font-weight:bold; font-family:'Arial Black', Arial, Helvetica, sans-serif; font-size:22px; line-height:24px; color:#4e6f5e; text-transform:uppercase; text-align:justify;">
                                                            What is Djakarta Mining Club?
                                                        </h3>
                                                        <p
                                                            style="margin:12px 0 18px; font-size:15px; line-height:22px; color:#000; text-align:justify;">
                                                            Djakarta Mining Club will be fostering of bilateral business
                                                            but will have as its primary function to promote sustainable
                                                            minerals production through the education of the masses,
                                                            through promotion of ongoing exploration and through planned
                                                            and ongoing dialogue between stakeholders in the mining
                                                            industry conducted in a non-threatening Safe and conducive
                                                            environment.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Secondary CTA --}}
                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:12px; background-color:#4E6F5E;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $cta_url_2 ?? 'https://www.djakarta-miningclub.com/about' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $cta_text_2 ?? 'LEARN MORE' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- What to Expect --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <h3
                                                            style="font-weight:bold; font-family:'Arial Black', Arial, Helvetica, sans-serif; font-size:22px; line-height:24px; color:#4e6f5e; text-transform:uppercase; text-align:justify;">
                                                            What to Expect?
                                                        </h3>
                                                        <ul style="padding-left:20px; margin:8px 0;">
                                                            <li><strong>Networking Session:</strong> high-impact meetups
                                                                with industry leaders, other mining professionals, and
                                                                investors</li>
                                                            <li><strong>Executive Briefings:</strong> fast, insight-led
                                                                sessions on policy, ESG, and market outlooks</li>
                                                            <li><strong>Thought Leader Talks:</strong> case studies from
                                                                mines, EPCs, and technology providers</li>
                                                            <li><strong>Partner Spotlights:</strong> new solutions,
                                                                practical demos, and Q&amp;A</li>
                                                            <li><strong>Member-Only Invites:</strong> limited-seat
                                                                dialogues and curated introductions</li>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Plan your year --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <h3
                                                            style="font-weight:bold; font-family:'Arial Black', Arial, Helvetica, sans-serif; font-size:22px; line-height:24px; color:#4e6f5e; text-transform:uppercase; text-align:justify;">
                                                            Plan your year with DMC
                                                        </h3>
                                                        <p style="margin:12px 0 0;">View the full events calendar</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Events CTA --}}
                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:4px; background-color:#4E6F5E;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $events_url ?? 'https://www.djakarta-miningclub.com/events' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $events_cta_text ?? 'OUR EVENT' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Next Step list (note: Profile_Link dari file asli → $profile_link) --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <h3
                                                            style="font-weight:bold; font-family:'Arial Black', Arial, Helvetica, sans-serif; font-size:22px; line-height:24px; color:#4e6f5e; text-transform:uppercase; text-align:justify;">
                                                            Next Step
                                                        </h3>
                                                        <ol style="margin:0; padding-left:20px;">
                                                            <li>Complete your profile → <a
                                                                    href="{{ $profile_link ?? '#' }}" target="_blank"
                                                                    rel="noopener">{{ $profile_link_text ?? 'Profile
                                                                    Link' }}</a></li>
                                                            <li>Read the latest newsletter → <a
                                                                    href="{{ $news_url ?? 'https://www.djakarta-miningclub.com/news' }}"
                                                                    target="_blank" rel="noopener">{{ $news_url ??
                                                                    'https://www.djakarta-miningclub.com/news' }}</a>
                                                            </li>
                                                            <li>See upcoming events → <a
                                                                    href="{{ $events_url ?? 'https://www.djakarta-miningclub.com/events' }}"
                                                                    target="_blank" rel="noopener">{{ $events_url ??
                                                                    'https://www.djakarta-miningclub.com/events' }}</a>
                                                            </li>
                                                        </ol>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Sponsors image --}}
                                <table width="100%" class="mcnImageBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnImageBlockInner" style="padding:0;">
                                            <table align="left" width="100%" class="mcnImageContentContainer"
                                                style="min-width:100%;">
                                                <tr>
                                                    <td class="mcnImageContent" style="padding:0; text-align:center;">
                                                        <a href="{{ $sponsors_url ?? 'https://www.djakarta-miningclub.com/sponsors' }}"
                                                            target="_blank" rel="noopener">
                                                            <img align="center" alt="Sponsors"
                                                                src="{{ $sponsors_image ?? 'https://mcusercontent.com/02704374ac4e9af520447c272/images/5536c788-b0ab-ca12-b42b-41948ccafc96.png' }}"
                                                                width="600"
                                                                style="max-width:1764px; display:inline !important; vertical-align:bottom;">
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Sponsors CTA & closing --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        Djakarta Mining Club is proud to be backed by standout companies
                                                        that are moving the industry forward...
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:4px; background-color:#4E6F5E;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $sponsors_url ?? 'https://www.djakarta-miningclub.com/sponsors' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $sponsors_cta_text ?? 'OUR SPONSORS' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <p style="text-align:justify;">We’re glad to have you with us
                                                            and look forward to your active participation.</p>
                                                        <p>Warm regards,<br><strong>Djakarta Mining Club</strong></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Contact / Get in touch CTA --}}
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent" style="padding:0 18px 9px;">
                                                        <h3
                                                            style="font-weight:bold; font-family:'Arial Black', Arial, Helvetica, sans-serif; font-size:22px; line-height:24px; color:#4e6f5e; text-transform:uppercase; text-align:justify;">
                                                            Contact Us
                                                        </h3>
                                                        <div style="text-align:justify;">
                                                            If you have any questions, suggestions, or would like to
                                                            collaborate, feel free to contact us. We are ready to assist
                                                            and will respond efficiently.
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:12px; background-color:#4E6F5E;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $contact_url ?? 'https://www.djakarta-miningclub.com/contact' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $contact_cta_text ?? 'GET IN TOUCH' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>

                        {{-- Footer --}}
                        <tr>
                            <td id="templateFooter">
                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent"
                                                        style="padding:0 18px 9px; color:#ffffff; text-align:center; font-size:12px;">
                                                        <em>Was this email forwarded to you? Become members</em> 👇
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table width="100%" class="mcnButtonBlock" style="min-width:100%;">
                                    <tr>
                                        <td align="center" class="mcnButtonBlockInner" style="padding:0 18px 18px;">
                                            <table class="mcnButtonContentContainer"
                                                style="border-collapse:separate !important; border-radius:4px; background-color:#D92525;">
                                                <tr>
                                                    <td align="center" valign="middle" class="mcnButtonContent"
                                                        style="font-family:Arial; font-size:16px; padding:18px;">
                                                        <a class="mcnButton"
                                                            href="{{ $register_url ?? 'https://www.djakarta-miningclub.com/' }}"
                                                            target="_blank" rel="noopener"
                                                            style="font-weight:bold; text-decoration:none; color:#FFFFFF;">
                                                            {{ $register_cta_text ?? 'Register as a member' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table width="100%" class="mcnTextBlock" style="min-width:100%;">
                                    <tr>
                                        <td class="mcnTextBlockInner" style="padding-top:9px;">
                                            <table align="left" width="100%" class="mcnTextContentContainer">
                                                <tr>
                                                    <td class="mcnTextContent"
                                                        style="padding:0 18px 18px; color:#ffffff; text-align:center; font-size:13px;">
                                                        <strong>Contact us for further information:</strong><br>
                                                        <a href="{{ $site_url ?? 'https://www.djakarta-miningclub.com' }}"
                                                            target="_blank" rel="noopener" style="color:#FFFFFF;">{{
                                                            $site_url ?? 'www.djakarta-miningclub.com' }}</a><br>
                                                        <a href="mailto:{{ $contact_email ?? 'secretariat@djakarta-miningclub.com' }}"
                                                            style="color:#FFFFFF;">{{ $contact_email ??
                                                            'secretariat@djakarta-miningclub.com' }}</a><br>
                                                        <span style="color:#FFFFFF;">{{ $contact_phone ?? '+62
                                                            8111937300' }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>