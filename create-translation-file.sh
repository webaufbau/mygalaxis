#!/bin/bash

# Erstelle Übersetzungsdatei für Email-Feldwerte
# Dieses Script sollte nach dem Deployment ausgeführt werden

echo "Creating email field translations file..."

# Erstelle Verzeichnis falls nicht vorhanden
mkdir -p writable/data

# Erstelle Übersetzungsdatei
cat > writable/data/email_field_translations.json << 'EOF'
{
    "en": "Andere=Other\nNein=No\nJa=Yes",
    "fr": "",
    "it": ""
}
EOF

chmod 664 writable/data/email_field_translations.json

echo "✓ Translation file created at writable/data/email_field_translations.json"
