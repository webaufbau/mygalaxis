<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'user' => [
            'title'       => 'Benutzer',
            'description' => 'Öffentlich',
        ],
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Berechtigungen, Einstellungen',
        ],
        'developer' => [
            'title'       => 'Entwickler',
            'description' => 'Logs',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
// Dashboard
        'my.dashboard_view' => 'Dashboard anzeigen',

        // Mein Profil
        'my.profile_view' => 'Profil anzeigen',
        'my.profile_edit' => 'Profil bearbeiten',

        // Mein Abo
        'my.subscription_view' => 'Abo anzeigen',

        // Passwort ändern
        'my.change_password' => 'Passwort ändern',

        // E-Mail ändern
        'my.change_email' => 'E-Mail ändern',

        'my.bookmark_view' => 'Markierte Artikel anzeigen',
        'my.interest_view' => 'Meine Interessen anzeigen',
        'my.interestorganization_view' => 'Meine Gemeinden anzeigen',

        // Meine Artikel
        'my.news_view' => 'Meine Artikel anzeigen',
        'my.news_create' => 'Artikel erstellen',
        'my.news_edit' => 'Artikel bearbeiten',
        'my.news_delete' => 'Artikel löschen',
        'my.news_admin' => 'Alle Artikel administrieren',

        // Meine Veranstaltungen
        'my.event_view' => 'Meine Veranstaltungen anzeigen',
        'my.event_create' => 'Veranstaltung erstellen',
        'my.event_edit' => 'Veranstaltung bearbeiten',
        'my.event_delete' => 'Veranstaltung löschen',
        'my.event_admin' => 'Alle Veranstaltungen administrieren',

        // Meine Organisationen
        'my.organization_view' => 'Meine Organisationen anzeigen',
        'my.organization_create' => 'Organisation erstellen',
        'my.organization_edit' => 'Organisation bearbeiten',
        'my.organization_delete' => 'Organisation löschen',
        'my.organization_admin' => 'Alle Organisationen administrieren',

        // Advertorials
        'my.advertorial_view' => 'Meine Advertorials anzeigen',
        'my.advertorial_create' => 'Advertorial erstellen',
        'my.advertorial_edit' => 'Advertorial bearbeiten',
        'my.advertorial_delete' => 'Advertorial löschen',
        'my.advertorial_admin' => 'Alle Advertorials administrieren',

        // E-Paper Read
        'my.epaper_read' => 'PDF anzeigen',

        // Inserate verwalten
        'my.advertisement_view' => 'Inserate anzeigen',
        'my.advertisement_create' => 'Inserat erstellen',
        'my.advertisement_edit' => 'Inserat bearbeiten',
        'my.advertisement_delete' => 'Inserat löschen',

        // Artikel freigeben
        'my.approve_reviews' => 'Bewertungen freigeben',
        'my.approve_articles' => 'Artikel freigeben',

        // Bewertungen freigeben
        'my.review_view' => 'Bewertungen anzeigen',
        'my.review_create' => 'Bewertungen erstellen',
        'my.review_edit' => 'Bewertungen bearbeiten',
        'my.review_delete' => 'Bewertungen löschen',
        'my.review_admin' => 'Alle Bewertungen administrieren',

        'my.region_view' => 'Region anzeigen',
        'my.region_create' => 'Region erstellen',
        'my.region_edit' => 'Region bearbeiten',
        'my.region_delete' => 'Regionen löschen',
        'my.region_admin' => 'Regionen administrieren',

        // Newsimport
        'my.newsimport_view' => 'NewsImport anzeigen',
        'my.newsimport_create' => 'NewsImport erstellen',
        'my.newsimport_edit' => 'NewsImport bearbeiten',
        'my.newsimport_delete' => 'NewsImport löschen',
        'my.newsimport_admin' => 'Alle NewsImport administrieren',

        // Aboarten Verwaltung
        'my.subscriptiontype_view' => 'Aboarten anzeigen',
        'my.subscriptiontype_create' => 'Aboart erstellen',
        'my.subscriptiontype_edit' => 'Aboart bearbeiten',
        'my.subscriptiontype_delete' => 'Aboart löschen',

        // Online-Bestellungen
        'my.order_view' => 'Online-Bestellungen anzeigen',
        'my.order_edit' => 'Online-Bestellung bearbeiten',
        'my.order_delete' => 'Online-Bestellung löschen',

        // Abodaten hochladen
        'my.subscriptionfile_upload' => 'Abodaten hochladen',

        // E-Paper hochladen
        'my.printpdf_upload' => 'E-Paper hochladen',

        // Benutzer verwalten
        'my.user_view' => 'Benutzer anzeigen',
        'my.user_create' => 'Benutzer erstellen',
        'my.user_edit' => 'Benutzer bearbeiten',
        'my.user_delete' => 'Benutzer löschen',

        // Benutzer Organisation verwalten
        'my.usersorganization_view' => 'Benutzer Organisation anzeigen',
        'my.usersorganization_create' => 'Benutzer Organisation erstellen',
        'my.usersorganization_edit' => 'Benutzer Organisation bearbeiten',
        'my.usersorganization_delete' => 'Benutzer Organisation löschen',

        // Berechtigungen / Rollen
        'my.permission_view' => 'Berechtigungen anzeigen',
        'my.permission_edit' => 'Berechtigungen bearbeiten',

        // Push versenden
        'my.send_push_notifications' => 'Push-Benachrichtigungen senden',

        // Kategorieen verwalten
        'my.category_view' => 'Kategorieen anzeigen',
        'my.category_create' => 'Kategorie erstellen',
        'my.category_edit' => 'Kategorie bearbeiten',
        'my.category_delete' => 'Kategorie löschen',

        // searchindexen verwalten
        'my.searchindex_view' => 'Suche-Index anzeigen',
        'my.searchindex_create' => 'Suche-Index erstellen',
        'my.searchindex_edit' => 'Suche-Index bearbeiten',
        'my.searchindex_delete' => 'Suche-Index löschen',

        // Städte verwalten
        'my.city_view' => 'Städte anzeigen',
        'my.city_create' => 'Stadt erstellen',
        'my.city_edit' => 'Stadt bearbeiten',
        'my.city_delete' => 'Stadt löschen',

        // CMS verwalten
        'my.cms_view' => 'CMS anzeigen',
        'my.cms_create' => 'CMS erstellen',
        'my.cms_edit' => 'CMS bearbeiten',
        'my.cms_delete' => 'CMS löschen',

        // E-Mail Templates
        'my.emailtemplate_view' => 'E-Mail-Vorlagen anzeigen',
        'my.emailtemplate_create' => 'E-Mail-Vorlage erstellen',
        'my.emailtemplate_edit' => 'E-Mail-Vorlage bearbeiten',
        'my.emailtemplate_delete' => 'E-Mail-Vorlage löschen',

        // App Einstellungen
        'my.settings_view' => 'App-Einstellungen anzeigen',
        'my.settings_edit' => 'App-Einstellungen bearbeiten',

        // Logs
        'my.log_view' => 'Logs anzeigen',
        'my.log_edit' => 'Logs Details',

        // Tokens
        'my.jwttoken_view' => 'Tokens anzeigen',
        'my.jwttoken_create' => 'Tokens erstellen',
        'my.jwttoken_edit' => 'Tokens Details',
        'my.jwttoken_delete' => 'Tokens löschen',

        // Push
        'my.push_view' => 'Push anzeigen',
        'my.push_create' => 'Push erstellen',
        'my.push_edit' => 'Push bearbeiten',
        'my.push_delete' => 'Push löschen',
        'my.push_admin' => 'Push administrieren',

        'my.push_notifications_view' => 'Push Benachrichtigungen anzeigen',
        'my.push_notifications_create' => 'Push Benachrichtigungen erstellen',
        'my.push_notifications_edit' => 'Push Benachrichtigungen bearbeiten',
        'my.push_notifications_delete' => 'Push Benachrichtigungen löschen',
        'my.push_notifications_admin' => 'Push Benachrichtigungen administrieren',


        'my.campaign_view' => 'Kampagnen anzeigen',


        'my.statistics_admin' => 'Statistik anzeigen',

        'my.news_impressions_stats' => 'News Statistiken',


        // Export anzeigen
        'my.export' => 'Export anzeigen',

        // Logdateien anzeigen
        'my.logfile_view' => 'Logdateien anzeigen',

        // Aboänderungsprotokoll anzeigen
        'my.subscriptionlog_view' => 'Aboänderungsprotokoll anzeigen',

        // Feedbacks anzeigen
        'my.feedback_view' => 'Feedbacks anzeigen',

        // Mitteilungen anzeigen
        'my.message_view' => 'Mitteilungen anzeigen',

        // Abfragen anzeigen
        'my.queries_view' => 'Abfragen anzeigen',

        // Mitarbeiterverwaltung anzeigen
        'my.employee_view' => 'Mitarbeiterverwaltung anzeigen',

        // Rollenverwaltung anzeigen
        'my.role_view' => 'Rollenverwaltung anzeigen',

        // SSO anzeigen
        'my.sso_view' => 'SSO anzeigen',

        // Language Editor
        'my.language_view' => 'Übersetzungen Editor',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'user' => [
        ],
        'service' => [
            'my.event_admin',
            'my.organization_admin',
            'my.subscriptionfile_upload',
            'my.export',
            'my.subscriptionlog_view',
            'my.feedback_view',
            'my.message_view',
            'my.queries_view',
            'my.advertisement_view',
            'my.advertisement_create',
            'my.advertisement_edit',
            'my.advertisement_delete',

            'my.order_view',
            'my.order_edit',
            'my.order_delete',

            'my.user_view',
            'my.user_create',
            'my.user_edit',
            'my.user_delete',
            'my.statistics_admin',
        ],
        'editor' => [
            'my.news_admin',
            'my.approve_articles',
            'my.approve_reviews',
            'my.review_admin',
            'my.review_view',
            'my.review_create',
            'my.review_edit',
            'my.review_delete',
            'my.epaper_view',
            'my.epaper_create',
            'my.epaper_edit',
            'my.epaper_delete',
            'my.obituary_view',
            'my.obituary_create',
            'my.obituary_edit',
            'my.obituary_delete',
        ],
        'admin' => [
            'my.review_admin',
            'my.review_view',
            'my.review_create',
            'my.review_edit',
            'my.review_delete',
            'my.send_push_notifications',
            'my.category_view',
            'my.category_create',
            'my.category_edit',
            'my.category_delete',
            'my.region_view',
            'my.region_create',
            'my.region_edit',
            'my.region_delete',
            'my.user_view',
            'my.user_create',
            'my.user_edit',
            'my.user_delete',
            'my.city_view',
            //'my.city_create',
            'my.city_edit',
            //'my.city_delete',
            'my.cms_view',
            'my.cms_create',
            'my.cms_edit',
            'my.cms_delete',
            'my.printpdf_upload',
            'my.emailtemplate_view',
            'my.emailtemplate_create',
            'my.emailtemplate_edit',
            'my.emailtemplate_delete',
            'my.logfile_view',
            'my.employee_view',
            'my.role_view',
            'my.sso_view',
            'my.push_view',
            'my.push_edit',
            'my.push_create',
            'my.push_delete',
            'my.push_admin',
            'my.language_view',
            'my.news_impressions_stats',
            'my.campaign_admin',
            'my.campaign_view',
            'my.campaign_create',
            'my.campaign_edit',
            'my.campaign_delete',
        ],
        'superadmin' => [
            'my.permission_view',
            'my.permission_edit',
            'my.settings_view',
            'my.settings_edit',
        ],
        'developer' => [
            'my.log_view',
            'my.log_edit',
            'my.jwttoken_view',
            'my.jwttoken_edit',
            'my.jwttoken_view',
            'my.jwttoken_edit',
        ],
    ];
}
