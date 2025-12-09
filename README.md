# Study-Whis Module

Module Omeka S pour la transcription et l'analyse de cours audio.

## Installation

1. Copier le module dans `modules/StudyWhis`
2. Activer le module depuis le panneau d'administration Omeka
3. Configurer les clés API Study-Whis dans les paramètres du module

## Configuration

### Paramètres Requis

- **API Key**: Votre clé API Study-Whis
- **API Secret**: Votre secret API Study-Whis

### Paramètres Optionnels

- **Max Upload Size**: Taille maximale des uploads (par défaut 500 MB)
- **Enable Ollama**: Activer l'intégration Ollama pour l'IA locale
- **Ollama Endpoint**: URL de l'endpoint Ollama

## Fonctionnalités

- Transcription audio via Whisper API
- Génération automatique de résumés
- Génération de questions de révision
- Recherche textuelle dans les transcriptions

## Structure du Module

```
StudyWhis/
├── Module.php
├── config/
│   ├── module.config.php
│   └── module.ini
├── src/
│   └── Controller/
│       └── IndexController.php
└── view/
    └── study-whis/
        ├── index/
        │   └── index.phtml
        └── config-form.phtml
```

## Usage

1. Accédez à l'interface d'administration
2. Cliquez sur "Study-Whis" dans le menu de navigation
3. Téléchargez un fichier audio (MP3, WAV, OGG, M4A, FLAC)
4. Attendez la transcription et l'analyse automatique

## Support

Pour toute question ou problème, consultez la documentation Study-Whis ou contactez le support.

## Licence

Ce module est fourni sous licence MIT.
