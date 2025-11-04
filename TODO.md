# TODO: Migration IA Python vers Laravel + Gemini

## Étape 1: Supprimer l'ancien système Python
- [x] Supprimer AIClientService.php
- [x] Supprimer EnhancedAIClientService.php
- [x] Nettoyer AIController.php (supprimer références Python)
- [x] Supprimer routes /api/ai/* dans web.php
- [x] Supprimer variables PYTHON_API_URL et AI_SERVICE_URL du .env

## Étape 2: Créer le nouveau système Laravel + Gemini
- [x] Créer app/Services/GeminiService.php
- [x] Renommer AIController.php en AIAnalysisController.php et refactorer
- [x] Ajouter route POST /projects/{project}/analyze dans web.php
- [x] Ajouter GEMINI_API_KEY dans .env
- [x] Mettre à jour show.blade.php pour nouvelle route

## Étape 3: Tests et validation
- [x] Tester l'intégration Gemini (config cleared)
- [x] Vérifier création activités/tâches
- [ ] Supprimer fichiers Python si nécessaire
