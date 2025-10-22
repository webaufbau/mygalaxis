# Email Template für Reinigung (Deutsch)

## Template Einstellungen:
- **Offer Type:** `reinigung`
- **Sprache:** `de`
- **Status:** Aktiv

---

## Betreff:
```
Ihre Reinigungsanfrage bei {site_name}
```

---

## Body Template:

```html
<h2>🎉 Wir bestätigen Ihnen Ihre Anfrage/Offerte</h2>

<p>Guten Tag {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p><strong>Herzlichen Dank für Ihre Anfrage für Reinigung.</strong></p>
    <p>In Kürze werden Sie bis zu 3 unverbindliche Offerten von passenden Anbietern aus Ihrer Region erhalten.</p>
</div>

<p style="background-color: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <strong>Hinweis:</strong> Je nach Saison kann es vorkommen, dass die Firmen für den gewünschten Zeitraum schon ausgebucht sind und daher keine Angebote unterbreiten.
</p>

<h3>So funktioniert's:</h3>
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
</p>
```

---

## Alternative Version mit mehr Bedingungen:

Falls Sie bestimmte Felder hervorheben möchten:

```html
<h2>🎉 Wir bestätigen Ihnen Ihre Anfrage/Offerte</h2>

<p>Guten Tag {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p><strong>Herzlichen Dank für Ihre Anfrage für Reinigung.</strong></p>
    <p>In Kürze werden Sie bis zu 3 unverbindliche Offerten von passenden Anbietern aus Ihrer Region erhalten.</p>
</div>

<p style="background-color: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <strong>Hinweis:</strong> Je nach Saison kann es vorkommen, dass die Firmen für den gewünschten Zeitraum schon ausgebucht sind und daher keine Angebote unterbreiten.
</p>

<h3>So funktioniert's:</h3>
<ul>
    <li>Sie erhalten Angebote per E-Mail – oft innerhalb von 1-3 Werktagen</li>
    <li>Anbieter können Sie kontaktieren, falls Rückfragen bestehen</li>
    <li>Wir arbeiten mit Partnerplattformen zusammen, daher könnten Sie ev. auch von denen Angebote erhalten</li>
    <li>Sie entscheiden in Ruhe, welches Angebot am besten passt</li>
</ul>

<p style="background-color: #e7f3ff; padding: 12px; border-left: 4px solid #007bff; margin: 20px 0;">
    <strong>Hinweis:</strong> Prüfen Sie auch Ihren Spam/Werbungsordner, falls Sie innerhalb von 1-3 Werktagen keine Angebote erhalten.
</p>

[if field:cleaning_date]
<div style="background-color: #d4edda; padding: 12px; border-left: 4px solid #28a745; margin: 20px 0;">
    <strong>Gewünschter Termin:</strong> {field:cleaning_date|date:d.m.Y}
    [if field:zeitlich_flexibel]
    <br><em>Sie sind zeitlich flexibel: {field:zeitlich_flexibel}</em>
    [/if]
</div>
[/if]

<h3>Zusammenfassung Ihrer Anfrage</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>

<p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
    Diese Nachricht wurde automatisch generiert. Bei Fragen kontaktieren Sie uns über {site_name}.
</p>
```

---

## Feldnamen-Mapping (zur Info)

Basierend auf dem Beispiel sind folgende Feldnamen wahrscheinlich:

| Deutsch (wie angezeigt) | Feldname (vermutlich) |
|------------------------|----------------------|
| Wer ist der Nutzer? | wer_ist_der_nutzer oder user_type |
| Was soll gereinigt werden? | was_soll_gereinigt_werden oder cleaning_object |
| Wohnungsgrösse | wohnungsgrosse oder apartment_size |
| Stockwerk | stockwerk oder floor |
| Möbliert | mobliert oder furnished |
| Wohnfläche (m²) | wohnflache oder living_space |
| Keller vorhanden | keller_vorhanden oder cellar |
| Hobbyraum | hobbyraum oder hobby_room |
| Dachboden | dachboden oder attic |
| Lift vorhanden | lift_vorhanden oder elevator |
| Reinigungsart | reinigungsart oder cleaning_type |
| Fensterreinigung gewünscht | fensterreinigung_gewunscht oder window_cleaning |
| Fensterauswahl | fensterauswahl oder window_selection |
| Fenstertyp 1 | fenstertyp_1 oder window_type_1 |
| Welche Seite soll gereinigt werden? | welche_seite oder cleaning_side |
| Verschmutzungsgrad Fenster | verschmutzungsgrad_fenster oder dirt_level |
| Wann sollen die Arbeiten beginnen? | wann_sollen_die_arbeiten_beginnen oder cleaning_date |
| Zeitlich flexibel | zeitlich_flexibel oder time_flexible |
| Erreichbar | erreichbar oder availability |

**Hinweis:** Die genauen Feldnamen müssen Sie im Formular bzw. in der Datenbank prüfen. Diese können abweichen!
