<?php

return [
    // Each module covers its pages and actions. Unmapped CMS routes are reserved for Superadmin.
    // Group (2nd value) = tim pemilik menu; Superadmin melihat sidebar per tim dengan urutan di bawah ini.
    'modules' => [
        'dashboard' => ['Dashboard', 'Main', 'home', 'tachometer-alt', ['HomeController']],
        // Member Team
        'member_intake' => ['Data Tampung', 'Member Team', 'member', 'id-badge', []],
        'members' => ['Members DMC', 'Member Team', 'users', 'users', ['UsersController', 'JobTierDetailController']],
        'member_followups' => ['Member Follow-Up', 'Member Team', 'company-follow-ups', 'exchange-alt', ['MemberCompanyFollowUpController'], 'Follow-Up'],
        'leads' => ['Lead Follow-Up', 'Member Team', 'leads', 'bullseye', ['MemberLeadFollowUpController', 'MemberLeadBulkController'], 'Follow-Up'],
        'master_database' => ['Master Database', 'Member Team', 'master-database', 'database', ['MasterDatabaseController'], 'Database'],
        'company_database' => ['Company Database', 'Member Team', 'company-database', 'building', ['CompanyDatabaseController'], 'Database'],
        'company_categories' => ['Company Categories', 'Member Team', 'company-categories', 'tags', ['CompanyCategoryController'], 'Company Settings'],
        'company_subcategories' => ['Company Subcategories', 'Member Team', 'company-subcategories', 'sitemap', ['CompanySubcategoryController'], 'Company Settings'],
        'member_sources' => ['Member Sources', 'Member Team', 'member-sources', 'link', ['MemberSourceController']],
        'event_conversions' => ['Member Conversion', 'Member Team', 'event-conversions', 'chart-line', ['EventConversionController']],
        'lucky_draw_items' => ['Prizes', 'Member Team', 'lucky-draw/items', 'gift', ['LuckyDrawItemController'], 'Lucky Draw'],
        'lucky_draw_entries' => ['Entries / Winners', 'Member Team', 'lucky-draw/entries', 'gift', ['LuckyDrawEntryController'], 'Lucky Draw'],
        'membership' => ['Membership Tier Banners', 'Member Team', 'membership-tier-banners', 'id-card', ['MembershipTierBannerController']],
        'mailchimp' => ['Mailchimp Unsubscribes', 'Member Team', 'mailchimp/unsubscribes', 'envelope-open', ['MailchimpUnsubscribeController']],
        // Event Team
        'events' => ['Events List', 'Event Team', 'events', 'calendar-alt', ['EventController', 'EventsDetailController', 'EventsDetailParticipantController', 'SpecialEventController'], 'Events'],
        'event_sponsors' => ['Sponsor Invitation', 'Event Team', 'events/sponsor', 'handshake', ['EventsRegisterSponsorController'], 'Events'],
        'event_categories' => ['Category', 'Event Team', 'events/category', 'tags', ['EventCategoryController'], 'Events'],
        'event_tickets' => ['Tickets', 'Event Team', 'events/tickets', 'ticket-alt', ['EventsTicketController'], 'Events'],
        'event_conferences' => ['Conference', 'Event Team', 'events/conference', 'users', ['EventsConferenceController'], 'Events'],
        'event_highlights' => ['Highlight', 'Event Team', 'events/highlight', 'images', ['EventsHighlightController'], 'Events'],
        'event_schedules' => ['Schedule', 'Event Team', 'events/schedule', 'clock', ['EventsScheduleController'], 'Events'],
        'event_speakers' => ['Speakers', 'Event Team', 'events/speakers', 'microphone', ['EventsSpeakersController'], 'Events'],
        'event_rundown' => ['Rundown', 'Event Team', 'events/rundown', 'list', ['EventsRundownController'], 'Events'],
        'partnership_events' => ['Partnership Event', 'Event Team', 'partnership-events', 'handshake', ['PartnershipEventVisitorController']],
        // Marketing Team
        'news' => ['News List', 'Marketing Team', 'news', 'newspaper', ['NewsController', 'NewsPartnerController'], 'News'],
        'news_categories' => ['News Category', 'Marketing Team', 'news/category', 'tags', ['NewsCategoryController'], 'News'],
        'program' => ['Program Article', 'Marketing Team', 'program', 'file-alt', ['ProgramController'], 'News'],
        'digital_editions' => ['Digital Edition', 'Marketing Team', 'digital-edition', 'book-open', ['DigitalEditionController']],
        'videos' => ['Videos Highlight', 'Marketing Team', 'videos', 'video', ['VideosController']],
        'advertisement' => ['Side Banner', 'Marketing Team', 'advertisement', 'bullhorn', ['AdvertisementController'], 'Advertisement'],
        'marketing_ads' => ['Pop Up', 'Marketing Team', 'marketing-ads', 'bullhorn', ['MarketingAdsController'], 'Advertisement'],
        'emails' => ['Email Management', 'Marketing Team', 'email-management', 'envelope', ['EmailController']],
        'notifications' => ['Notifications', 'Marketing Team', 'notification', 'bell', ['NotificationController']],
        'wa_campaign' => ['Campaign', 'Marketing Team', 'whatsapp/campaign', 'comment', ['WhatsappCampaignController'], 'Whatsapp'],
        'wa_database' => ['Database', 'Marketing Team', 'whatsapp/db', 'database', ['WhatsappDBController'], 'Whatsapp'],
        'wa_template' => ['Template', 'Marketing Team', 'whatsapp/template', 'file-alt', ['WhatsappTemplateController'], 'Whatsapp'],
        'wa_blasting' => ['Blasting', 'Marketing Team', 'whatsapp/blasting', 'paper-plane', ['WhatsappBlastingController'], 'Whatsapp'],
        'wa_sender' => ['Sender', 'Marketing Team', 'whatsapp/sender', 'comment', ['WhatsappSenderController'], 'Whatsapp'],
        // Secretariat Team
        'sponsors' => ['Sponsors', 'Secretariat Team', 'sponsors', 'handshake', ['SponsorController', 'SponsorBenefitController', 'SponsorExportController', 'SponsorPhotosVideosActivityController', 'SocialMediaEngagementController', 'SponsorAddressController', 'SponsorAdvertisingController', 'SponsorRepresentativeController', 'SponsorCountRepresentativeController', 'SponsorContactDirectoryController', 'SponsorContractHistoryController', 'SponsorRenewalFormController', 'SponsorFollowupController']],
        'sponsor_annual_report' => ['Sponsors Annual Report', 'Secretariat Team', 'sponsors/annual-report', 'chart-bar', ['SponsorAnnualReportController']],
        'interviews' => ['Interview Schedule', 'Secretariat Team', 'interview-schedule/sponsor', 'microphone-alt', ['SponsorInterviewScheduleController']],
        'scholarship' => ['Scholarship', 'Secretariat Team', 'scholarship', 'graduation-cap', ['ScholarshipController']],
        // Financial Team
        'payments' => ['Payment & Financial Report', 'Financial Team', 'financial-reports', 'credit-card', ['PaymentController', 'InvoiceController', 'FinancialReportController']],
        'finance_invoices' => ['Invoice VA Mandiri', 'Financial Team', 'finance-invoices', 'file-invoice-dollar', ['FinanceInvoiceController']],
        // System
        'edit_logs' => ['User Edit Logs', 'System', 'users/edit-logs', 'history', []],
        'quick_search' => ['Quick Search (member & event history)', 'System', 'quick-search', 'search', ['QuickSearchController']],
        'cms_users' => ['CMS Users', 'System', 'cms-users', 'user-shield', ['CmsUsersController']],
        'ngrok' => ['Ngrok Setting', 'System', 'ngrok', 'plug', ['NgrokController']],
    ],
    // Sub menu untuk satu modul. Format: [label, path, modul izin (opsional)].
    // Tanpa modul izin, link memakai izin modul induknya.
    'submenus' => [
        'sponsors' => [
            ['Sponsors List', 'sponsors'],
            ['Contact Directory', 'sponsors/contact-directory'],
            ['Representative Attendance', 'sponsors-representative-count'],
            ['Nearing Contract', 'sponsors/nearing-contract'],
            ['Annual Report', 'sponsors/annual-report', 'sponsor_annual_report'],
            ['Contract History', 'sponsors/contract-history'],
        ],
    ],
    // Sub-kategori (header sidebar) untuk user tim. Urutan header mengikuti menu
    // pertama di team_defaults; menu yang tidak terdaftar masuk "Lainnya".
    'sections' => [
        'Member' => ['member_intake', 'members', 'member_followups', 'leads'],
        'Data & Settings' => ['master_database', 'company_database', 'company_categories', 'company_subcategories', 'member_sources'],
        'Engagement' => ['event_conversions', 'lucky_draw_items', 'lucky_draw_entries'],
        'Membership' => ['membership', 'mailchimp'],
        'Event' => ['events', 'event_sponsors', 'event_categories', 'event_tickets', 'event_conferences', 'event_highlights', 'event_schedules', 'event_speakers', 'event_rundown', 'partnership_events'],
        'Content' => ['news', 'news_categories', 'program', 'digital_editions', 'videos'],
        'Campaign & Communication' => ['advertisement', 'marketing_ads', 'emails', 'notifications', 'wa_campaign', 'wa_database', 'wa_template', 'wa_blasting', 'wa_sender'],
        'Sponsor & Scholarship' => ['sponsors', 'interviews', 'scholarship'],
        'Finance' => ['payments', 'finance_invoices', 'sponsor_annual_report'],
        'System' => ['edit_logs', 'quick_search', 'cms_users', 'ngrok'],
    ],
    // Aksi milik modul lain yang juga boleh dipakai pemegang modul di sini.
    // Annual Report (Finance): generate/preview renewal form + Confirm Renewal
    // (input invoice number/date & paid date) + Edit Contract Record (mis. isi
    // paid date belakangan). Follow-up log, Mark Not Renewed dan halaman Sponsors
    // lain tetap khusus izin Sponsors.
    'shared_actions' => [
        'SponsorController@updateContract' => ['sponsor_annual_report'],
        'SponsorContractHistoryController@update' => ['sponsor_annual_report'],
        'SponsorController@getKmkRate' => ['sponsor_annual_report'],
        'SponsorController@getNextQuotationNumber' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@preview' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@generate' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@latest' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@store' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@getNextFormNumber' => ['sponsor_annual_report'],
        'SponsorRenewalFormController@sponsorOptions' => ['sponsor_annual_report'],
        'SponsorFollowupController@index' => ['sponsor_annual_report'],
    ],
    'actions' => [
        'UsersController@member' => 'member_intake',
        'UsersController@export' => 'member_intake',
        'UsersController@editUserEvent' => 'events',
        'UsersController@editLogs' => 'edit_logs',
        'UsersController@userLogs' => 'edit_logs',
        'UsersController@importToMailchimp' => 'mailchimp',
        'EventsRegisterSponsorController@index' => 'event_sponsors',
    ],
    // Initial access requested for each team. Dashboard is available to everyone.
    'team_defaults' => [
        'member_team' => ['member_intake', 'members', 'member_followups', 'leads', 'master_database', 'company_database', 'company_categories', 'company_subcategories', 'member_sources', 'event_conversions', 'lucky_draw_items', 'lucky_draw_entries', 'membership', 'mailchimp', 'events'],
        'event_team' => ['events', 'event_sponsors', 'event_categories', 'event_tickets', 'event_conferences', 'event_highlights', 'event_schedules', 'event_speakers', 'event_rundown', 'partnership_events', 'event_conversions', 'lucky_draw_items', 'lucky_draw_entries'],
        'marketing_team' => ['news', 'news_categories', 'program', 'digital_editions', 'videos', 'advertisement', 'marketing_ads', 'emails', 'notifications', 'wa_campaign', 'wa_database', 'wa_template', 'wa_blasting', 'wa_sender'],
        'secretariat_team' => ['sponsors', 'sponsor_annual_report', 'scholarship'],
        'finance_team' => ['payments', 'finance_invoices', 'sponsor_annual_report'],
    ],
    'upload_modules' => ['news', 'program', 'digital_editions', 'videos', 'events', 'advertisement', 'marketing_ads', 'sponsors', 'membership'],
];
