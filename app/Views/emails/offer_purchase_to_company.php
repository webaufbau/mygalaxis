<h2>Vielen Dank für den Kauf einer Anfrage</h2>

<p>Sie haben erfolgreich folgende Anfrage gekauft:</p>

<ul>
    <li><strong>Titel:</strong> <?= esc($offer['title'] ?? '') ?></li>
    <li><strong>Typ:</strong> <?= esc(lang('Offers.type.' . $offer['type']) ?? '') ?></li>
</ul>

<hr>

<h3>Kundendaten</h3>

<ul>
    <li><strong>Name:</strong> <?= esc($kunde['firstname'] ?? '') . ' ' . esc($kunde['lastname'] ?? '') ?></li>
    <li><strong>E-Mail:</strong> <?= esc($kunde['email'] ?? '') ?></li>
    <li><strong>Telefon:</strong> <?= esc($kunde['phone'] ?? '') ?></li>
</ul>

<p>Bitte setzen Sie sich direkt mit dem Kunden in Verbindung und unterbreiten Ihre Offerte.</p>

<hr>

<h3>Zusammenfassung der Anfrage</h3>

<?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true]) ?>

<p style="margin-top: 30px;">
    <a href="<?= esc($company_backend_offer_link) ?>" style="background-color:#007BFF; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;">
        Angebot ansehen
    </a>
</p>
