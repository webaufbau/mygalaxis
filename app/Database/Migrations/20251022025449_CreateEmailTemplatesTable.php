<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailTemplatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'offer_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'comment'    => 'Type of offer: umzug, reinigung, maler, etc.',
            ],
            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => '5',
                'default'    => 'de',
                'comment'    => 'Language code: de, fr, it, en',
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Email subject line (can contain shortcodes)',
            ],
            'body_template' => [
                'type'    => 'TEXT',
                'comment' => 'HTML template with shortcodes',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'Enable/disable template',
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Internal notes for this template',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['offer_type', 'language'], false);
        $this->forge->createTable('email_templates');

        // Insert default template for 'umzug' as example
        $this->insertDefaultTemplates();
    }

    public function down()
    {
        $this->forge->dropTable('email_templates');
    }

    private function insertDefaultTemplates()
    {
        $data = [
            [
                'offer_type'    => 'umzug',
                'language'      => 'de',
                'subject'       => 'Ihre Umzugsanfrage bei {site_name}',
                'body_template' => '<h2>Vielen Dank für Ihre Anfrage!</h2>

<p>Hallo {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p>Vielen Dank für Ihre Umzugsanfrage über {site_name}.</p>
    <p>Wir leiten Ihre Anfrage an passende Umzugsfirmen in Ihrer Region weiter.</p>
    <p>Sie erhalten in Kürze unverbindliche Offerten direkt per E-Mail oder Telefon.</p>
</div>

<h3>So funktioniert es</h3>
<ul>
    <li>Passende Firmen erhalten Ihre Anfrage</li>
    <li>Sie werden innerhalb von 48 Stunden kontaktiert</li>
    <li>Sie vergleichen bis zu 5 kostenlose Offerten</li>
    <li>Sie wählen das beste Angebot aus</li>
</ul>

<p><strong>Wichtig:</strong> Die Offerten sind komplett kostenlos und unverbindlich.</p>

[if field:umzugsdatum]
<p><strong>Gewünschter Umzugstermin:</strong> {field:umzugsdatum|date:d.m.Y}</p>
[/if]

<h3>Zusammenfassung Ihrer Angaben</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>',
                'is_active'     => 1,
                'notes'         => 'Default template für Umzugsanfragen',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'offer_type'    => 'reinigung',
                'language'      => 'de',
                'subject'       => 'Ihre Reinigungsanfrage bei {site_name}',
                'body_template' => '<h2>🎉 Wir bestätigen Ihnen Ihre Anfrage/Offerte</h2>

<p>Guten Tag {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p><strong>Herzlichen Dank für Ihre Anfrage für Reinigung.</strong></p>
    <p>In Kürze werden Sie bis zu 3 unverbindliche Offerten von passenden Anbietern aus Ihrer Region erhalten.</p>
</div>

<p style="background-color: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <strong>Hinweis:</strong> Je nach Saison kann es vorkommen, dass die Firmen für den gewünschten Zeitraum schon ausgebucht sind und daher keine Angebote unterbreiten.
</p>

<h3>So funktioniert\'s:</h3>
<ul>
    <li>Sie erhalten Angebote per E-Mail – oft innerhalb von 1-3 Werktagen</li>
    <li>Anbieter können Sie kontaktieren, falls Rückfragen bestehen</li>
    <li>Wir arbeiten mit Partnerplattformen zusammen, daher könnten Sie ev. auch von denen Angebote erhalten</li>
    <li>Sie entscheiden in Ruhe, welches Angebot am besten passt</li>
</ul>

<p style="background-color: #e7f3ff; padding: 12px; border-left: 4px solid #007bff; margin: 20px 0;">
    <strong>Hinweis:</strong> Prüfen Sie auch Ihren Spam/Werbungsordner, falls Sie innerhalb von 1-3 Werktagen keine Angebote erhalten.
</p>

<h3>Zusammenfassung Ihrer Anfrage</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>

<p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
    Diese Nachricht wurde automatisch generiert. Bei Fragen kontaktieren Sie uns über {site_name}.
</p>',
                'is_active'     => 1,
                'notes'         => 'Template für Reinigungsanfragen gemäß Kundenvorlage',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'offer_type'    => 'default',
                'language'      => 'de',
                'subject'       => 'Ihre Anfrage bei {site_name}',
                'body_template' => '<h2>Vielen Dank für Ihre Anfrage!</h2>

<p>Hallo {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p>Vielen Dank für Ihre Anfrage über {site_name}.</p>
    <p>Wir leiten Ihre Anfrage an passende Firmen in Ihrer Region weiter.</p>
    <p>Sie erhalten in Kürze unverbindliche Offerten direkt per E-Mail oder Telefon.</p>
</div>

<h3>So funktioniert es</h3>
<ul>
    <li>Passende Firmen erhalten Ihre Anfrage</li>
    <li>Sie werden innerhalb von 48 Stunden kontaktiert</li>
    <li>Sie vergleichen bis zu 5 kostenlose Offerten</li>
    <li>Sie wählen das beste Angebot aus</li>
</ul>

<p><strong>Wichtig:</strong> Die Offerten sind komplett kostenlos und unverbindlich.</p>

<h3>Zusammenfassung Ihrer Angaben</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>',
                'is_active'     => 1,
                'notes'         => 'Fallback template für alle anderen Anfragen',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        $builder = \Config\Database::connect()->table('email_templates');
        $builder->insertBatch($data);
    }
}
