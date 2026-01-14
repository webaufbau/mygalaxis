# Claude Code Projektregeln - MyGalaxis

## Deployment Regeln
- NIEMALS Dateien direkt auf Produktions-Servern ändern via SCP/SSH
- Änderungen IMMER lokal machen, committen und pushen
- Deployment NUR über `./deploy-migration.sh` oder git pull auf dem Server
- Ausnahme: Cache löschen, Logs anschauen, Debugging

## Server-Zugriff
- SSH nur für: Logs lesen, Cache leeren, Migrations ausführen, Debugging
- Keine `scp` für Code-Dateien - immer Git verwenden

## Git Workflow
1. Änderungen lokal machen
2. `git add` + `git commit`
3. `git push`
4. `./deploy-migration.sh -y` für Deployment
