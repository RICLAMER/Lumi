# Lumi

Lumi is an AI learning companion for children ages 6 to 14. A child can photograph something unfamiliar or ask a question out loud, then hear a short explanation adapted to their age.

**Project page:** [Devpost - Lumi](https://devpost.com/software/lumi-imlr1a)

## What the MVP does

- Account creation with adult email verification
- Fixed, verified test account for hackathon reviewers
- Mobile camera capture and desktop image upload
- Voice recording, playback, transcription, and submission
- Age-adapted educational explanations in English, Brazilian Portuguese, or Spanish
- Browser-language detection with a manual language selector
- AI-generated speech synchronized with the Lumi talking video
- Input and output safety checks for child-appropriate use
- Separate daily photo and audio limits per verified account
- Registration limited to three requests per IP in any five-minute window
- Tester-only control portal for registration and daily usage limits
- Temporary processing only: uploaded photos and recordings are not retained

## OpenAI usage

Lumi uses OpenAI in the core product flow:

| Purpose | Model |
| --- | --- |
| Image understanding and educational explanations | `gpt-5.6-luna` |
| Voice transcription | `gpt-4o-mini-transcribe` |
| Spoken explanations | `gpt-4o-mini-tts` |
| Multimodal input and output safety | `omni-moderation-latest` |

The runtime calls the Responses, Moderations, Audio Transcriptions, and Audio Speech APIs from the PHP server. No API key is exposed to the browser.

The project was designed and implemented with Codex. GPT-5.6 is also used directly in the shipped multimodal explanation workflow.

## Safety and privacy

Lumi is intentionally strict because its audience includes children.

- The registration form asks for an adult's email and a child-safe display name or nickname.
- The exact age and display name stay on the Lumi server.
- Only an age group and preferred language are sent to OpenAI for response adaptation.
- The display name is spoken locally by the browser before the generated explanation.
- Images are resized and re-encoded before analysis, removing EXIF metadata.
- Photos and voice recordings exist only for the duration of the request.
- Unsafe inputs, unsafe model outputs, and high-risk topics use a pre-generated refusal response.
- The app does not identify people in images or infer sensitive personal traits.

See the public `privacy.php` page for the user-facing policy.

## Architecture

- PHP 8.1+ server-rendered pages and JSON endpoints
- MySQL 5.7+/MariaDB with automatic schema initialization
- Native browser Camera, MediaRecorder, Speech Synthesis, and Dialog APIs
- SMTP email verification over TLS
- Dependency-free OpenAI REST integration through PHP cURL
- Apache `.htaccess` rules for private source, configuration, and storage paths
- Database-backed runtime settings managed through `Portal.php`

## Local setup

1. Copy `.env.example` to `.env`.
2. Add the database, SMTP, and OpenAI credentials.
3. Point a PHP 8.1+ web server at the repository root.
4. Open `index.php`.

The database tables and reviewer account are created on the first database connection. Set `TESTER_EMAIL` and `TESTER_PASSWORD` only in the private environment file.

Required PHP extensions:

```text
curl
fileinfo
gd
mbstring
openssl
pdo_mysql
```

## Review access

Reviewer credentials are provided in the private Devpost submission notes. They are intentionally excluded from this public repository.

## Validation

- PHP syntax validation across all application files
- JavaScript syntax validation for landing and app flows
- Live OpenAI image-analysis and voice-question smoke tests
- Live multilingual image-analysis and speech smoke test
- Responsive browser checks at desktop and mobile viewports
- Production HTTPS, static asset, and protected-path checks
- Production database initialization and reviewer login

## Media

The Lumi character artwork, videos, and music in this repository are project assets supplied for the hackathon. The universe background and square Lumi app icon were created with OpenAI image generation for this interface.
