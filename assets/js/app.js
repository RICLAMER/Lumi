(() => {
    const body = document.body;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const language = body.dataset.userLanguage || 'en';
    const copy = {
        en: {
            pauseMusic: 'Pause music', playMusic: 'Play music',
            cameraError: 'I could not open the camera. You can still choose an image from the gallery.',
            photoSize: 'The photo must be no larger than 8 MB.',
            responseUnreadable: 'Lumi’s response could not be read.',
            failed: 'Lumi could not finish right now.',
            gathering: 'Gathering clues...', processing: 'This may take a few seconds.',
            safeTitle: 'I found a safe boundary.', readyTitle: 'Mystery solved!',
            safeMessage: 'Lumi prepared a response to keep the conversation safe.',
            readyMessage: 'The explanation is ready for you to hear.',
            errorTitle: 'That clue got away.', retry: 'Shall we try again?',
            photoUsage: 'You have used {used} of {limit} photo uploads today.',
            voiceUsage: 'You have used {used} of {limit} audio questions today.',
            remaining: '{remaining} of {limit} {type} submissions remain today.',
            imageType: 'photo', voiceType: 'audio',
            limitReached: 'You have reached today’s {type} limit.',
            recordStart: 'Tap the microphone to begin.',
            unsupported: 'This browser cannot record audio. Try an updated Chrome, Edge or Safari.',
            recorded: 'Question recorded. You can listen or send it.',
            recordLabel: 'Start recording', stopLabel: 'Stop recording',
            recording: 'I’m listening... tap again to finish.',
            microphoneError: 'I could not use the microphone. Allow access and try again.',
            audioError: 'Tap Repeat to hear the explanation.',
            languageError: 'I could not change the language right now.',
            discovery: 'Lumi discovery', safety: 'Lumi safety',
            discoverySubject: 'Discovery: {category}', curiosityDefault: 'curiosity',
            school: 'Subject: {value}', curiosity: 'Fun fact: {value}',
            categories: {
                object: 'object', animal: 'animal', plant: 'plant', food: 'food',
                question: 'question', other: 'curiosity',
            },
        },
        pt: {
            pauseMusic: 'Pausar música', playMusic: 'Tocar música',
            cameraError: 'Não consegui abrir a câmera. Você ainda pode escolher uma imagem da galeria.',
            photoSize: 'A foto precisa ter no máximo 8 MB.',
            responseUnreadable: 'A resposta da Lumi não pôde ser lida.',
            failed: 'A Lumi não conseguiu concluir agora.',
            gathering: 'Juntando as pistas...', processing: 'Isso pode levar alguns segundos.',
            safeTitle: 'Encontrei um limite seguro.', readyTitle: 'Mistério desvendado!',
            safeMessage: 'A Lumi preparou uma resposta para manter a conversa segura.',
            readyMessage: 'A explicação está pronta para você ouvir.',
            errorTitle: 'Essa pista escapou.', retry: 'Vamos tentar novamente?',
            photoUsage: 'Você já usou {used} de {limit} envios de foto hoje.',
            voiceUsage: 'Você já usou {used} de {limit} perguntas de áudio hoje.',
            remaining: 'Restam {remaining} de {limit} envios de {type} hoje.',
            imageType: 'foto', voiceType: 'áudio',
            limitReached: 'Você atingiu o limite de {type} de hoje.',
            recordStart: 'Toque no microfone para começar.',
            unsupported: 'Este navegador não permite gravação. Tente usar Chrome, Edge ou Safari atualizado.',
            recorded: 'Pergunta gravada. Você pode ouvir ou enviar.',
            recordLabel: 'Começar gravação', stopLabel: 'Parar gravação',
            recording: 'Estou ouvindo... toque novamente para terminar.',
            microphoneError: 'Não consegui usar o microfone. Libere a permissão e tente novamente.',
            audioError: 'Toque em Repetir para ouvir a explicação.',
            languageError: 'Não consegui alterar o idioma agora.',
            discovery: 'Descoberta da Lumi', safety: 'Segurança da Lumi',
            discoverySubject: 'Descoberta: {category}', curiosityDefault: 'curiosidade',
            school: 'Matéria: {value}', curiosity: 'Curiosidade: {value}',
            categories: {
                object: 'objeto', animal: 'animal', plant: 'planta', food: 'alimento',
                question: 'pergunta', other: 'curiosidade',
            },
        },
        es: {
            pauseMusic: 'Pausar música', playMusic: 'Reproducir música',
            cameraError: 'No pude abrir la cámara. Aún puedes elegir una imagen de la galería.',
            photoSize: 'La foto debe tener un máximo de 8 MB.',
            responseUnreadable: 'No se pudo leer la respuesta de Lumi.',
            failed: 'Lumi no pudo terminar ahora.',
            gathering: 'Reuniendo pistas...', processing: 'Esto puede tardar unos segundos.',
            safeTitle: 'Encontré un límite seguro.', readyTitle: '¡Misterio resuelto!',
            safeMessage: 'Lumi preparó una respuesta para mantener la conversación segura.',
            readyMessage: 'La explicación está lista para escuchar.',
            errorTitle: 'Esa pista se escapó.', retry: '¿Lo intentamos de nuevo?',
            photoUsage: 'Ya usaste {used} de {limit} envíos de foto hoy.',
            voiceUsage: 'Ya usaste {used} de {limit} preguntas de audio hoy.',
            remaining: 'Quedan {remaining} de {limit} envíos de {type} hoy.',
            imageType: 'foto', voiceType: 'audio',
            limitReached: 'Alcanzaste el límite de {type} de hoy.',
            recordStart: 'Toca el micrófono para empezar.',
            unsupported: 'Este navegador no permite grabar. Prueba una versión reciente de Chrome, Edge o Safari.',
            recorded: 'Pregunta grabada. Puedes escucharla o enviarla.',
            recordLabel: 'Empezar grabación', stopLabel: 'Detener grabación',
            recording: 'Estoy escuchando... toca otra vez para terminar.',
            microphoneError: 'No pude usar el micrófono. Permite el acceso e inténtalo de nuevo.',
            audioError: 'Toca Repetir para escuchar la explicación.',
            languageError: 'No pude cambiar el idioma ahora.',
            discovery: 'Descubrimiento de Lumi', safety: 'Seguridad de Lumi',
            discoverySubject: 'Descubrimiento: {category}', curiosityDefault: 'curiosidad',
            school: 'Materia: {value}', curiosity: 'Curiosidad: {value}',
            categories: {
                object: 'objeto', animal: 'animal', plant: 'planta', food: 'alimento',
                question: 'pregunta', other: 'curiosidad',
            },
        },
    }[language];
    const formatCopy = (template, values = {}) => Object.entries(values)
        .reduce((text, [key, value]) => text.replaceAll(`{${key}}`, value), template);

    const music = document.querySelector('[data-background-music]');
    const musicToggle = document.querySelector('[data-music-toggle]');
    const photoDialog = document.querySelector('#photo-dialog');
    const voiceDialog = document.querySelector('#voice-dialog');
    const processingDialog = document.querySelector('#processing-dialog');
    const explanationDialog = document.querySelector('#explanation-dialog');
    const toast = document.querySelector('[data-toast]');

    let musicWanted = true;
    let pendingResult = null;
    let processingController = null;
    let cameraStream = null;
    let selectedPhoto = null;
    let photoPreviewUrl = '';
    let audioStream = null;
    let recorder = null;
    let recordedBlob = null;
    let recordedUrl = '';
    let recordingStartedAt = 0;
    let recordingInterval = null;

    const showDialog = (dialog) => {
        if (!dialog) return;
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else dialog.setAttribute('open', '');
    };

    const hideDialog = (dialog) => {
        if (!dialog) return;
        if (typeof dialog.close === 'function' && dialog.open) dialog.close();
        else dialog.removeAttribute('open');
    };

    const anyFlowOpen = () => [photoDialog, voiceDialog, processingDialog, explanationDialog]
        .some((dialog) => dialog?.open || dialog?.hasAttribute('open'));

    const playMusic = async () => {
        if (!music || !musicWanted || anyFlowOpen()) return;
        music.volume = 0.32;
        try {
            await music.play();
            musicToggle?.classList.remove('is-muted');
            musicToggle?.setAttribute('aria-label', copy.pauseMusic);
            musicToggle?.setAttribute('title', copy.pauseMusic);
        } catch {
            musicToggle?.classList.add('is-muted');
        }
    };

    const pauseMusic = () => {
        music?.pause();
    };

    musicToggle?.addEventListener('click', () => {
        musicWanted = music?.paused ?? true;
        if (musicWanted) {
            playMusic();
        } else {
            pauseMusic();
            musicToggle.classList.add('is-muted');
            musicToggle.setAttribute('aria-label', copy.playMusic);
            musicToggle.setAttribute('title', copy.playMusic);
        }
    });

    document.addEventListener('pointerdown', () => {
        if (musicWanted && music?.paused && !anyFlowOpen()) playMusic();
    }, { once: true });

    playMusic();

    const showToast = (message) => {
        if (!toast || !message) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(showToast.timeout);
        showToast.timeout = window.setTimeout(() => toast.classList.remove('is-visible'), 5500);
    };

    if (toast?.classList.contains('is-visible')) {
        window.setTimeout(() => toast.classList.remove('is-visible'), 5500);
    }

    const languageSelect = document.querySelector('[data-app-language]');
    languageSelect?.addEventListener('change', async () => {
        const nextLanguage = languageSelect.value;
        if (nextLanguage === language) return;

        languageSelect.disabled = true;
        try {
            const response = await fetch('api/update-language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf,
                    'X-Lumi-Language': language,
                },
                body: JSON.stringify({ language: nextLanguage }),
            });
            const data = await response.json().catch(() => ({
                ok: false,
                message: copy.languageError,
            }));
            if (!response.ok || !data.ok) {
                throw new Error(data.message || copy.languageError);
            }
            window.location.assign(data.redirect || 'app.php');
        } catch (error) {
            languageSelect.disabled = false;
            languageSelect.value = language;
            showToast(error.message || copy.languageError);
        }
    });

    const cameraVideo = document.querySelector('[data-camera-video]');
    const cameraEmpty = document.querySelector('[data-camera-empty]');
    const photoPreview = document.querySelector('[data-photo-preview]');
    const photoInput = document.querySelector('[data-photo-input]');
    const sendPhotoButton = document.querySelector('[data-send-photo]');
    const usageElements = {
        image: document.querySelector('[data-usage-image]'),
        voice: document.querySelector('[data-usage-voice]'),
    };
    const usageState = Object.fromEntries(Object.entries(usageElements).map(([type, element]) => [
        type,
        {
            used: Number(element?.dataset.used || 0),
            limit: Number(element?.dataset.limit || 0),
        },
    ]));

    const usageLimitReached = (type) => usageState[type].used >= usageState[type].limit;
    const usageTypeLabel = (type) => type === 'image' ? copy.imageType : copy.voiceType;
    const updateUsage = (type, usage) => {
        if (!usageState[type] || !usage) return;
        usageState[type].used = Number(usage.used ?? usageState[type].used);
        usageState[type].limit = Number(usage.limit ?? usageState[type].limit);
        const element = usageElements[type];
        if (element) {
            element.dataset.used = String(usageState[type].used);
            element.dataset.limit = String(usageState[type].limit);
            element.textContent = formatCopy(
                type === 'image' ? copy.photoUsage : copy.voiceUsage,
                usageState[type]
            );
            element.classList.toggle('is-limit-reached', usageLimitReached(type));
        }
    };

    updateUsage('image', usageState.image);
    updateUsage('voice', usageState.voice);

    const stopCamera = () => {
        cameraStream?.getTracks().forEach((track) => track.stop());
        cameraStream = null;
        if (cameraVideo) cameraVideo.srcObject = null;
    };

    const startCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia || !cameraVideo) {
            cameraEmpty?.removeAttribute('hidden');
            return;
        }
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 960 },
                },
                audio: false,
            });
            cameraVideo.srcObject = cameraStream;
            cameraEmpty?.setAttribute('hidden', '');
            await cameraVideo.play();
        } catch {
            cameraEmpty?.removeAttribute('hidden');
            showToast(copy.cameraError);
        }
    };

    const resetPhoto = () => {
        selectedPhoto = null;
        sendPhotoButton.disabled = true;
        if (photoPreviewUrl) URL.revokeObjectURL(photoPreviewUrl);
        photoPreviewUrl = '';
        photoPreview.hidden = true;
        photoPreview.removeAttribute('src');
        cameraVideo.hidden = false;
        if (photoInput) photoInput.value = '';
    };

    const usePhoto = (blob, name = 'descoberta.jpg') => {
        if (!blob || blob.size > 8 * 1024 * 1024) {
            showToast(copy.photoSize);
            return;
        }
        selectedPhoto = blob instanceof File
            ? blob
            : new File([blob], name, { type: blob.type || 'image/jpeg' });
        if (photoPreviewUrl) URL.revokeObjectURL(photoPreviewUrl);
        photoPreviewUrl = URL.createObjectURL(selectedPhoto);
        photoPreview.src = photoPreviewUrl;
        photoPreview.hidden = false;
        cameraVideo.hidden = true;
        cameraEmpty?.setAttribute('hidden', '');
        sendPhotoButton.disabled = usageLimitReached('image');
        if (usageLimitReached('image')) {
            showToast(formatCopy(copy.limitReached, { type: copy.imageType }));
        }
    };

    document.querySelector('[data-open-photo]')?.addEventListener('click', () => {
        pauseMusic();
        resetPhoto();
        showDialog(photoDialog);
        startCamera();
    });

    document.querySelectorAll('[data-close-photo]').forEach((button) => {
        button.addEventListener('click', () => {
            stopCamera();
            resetPhoto();
            hideDialog(photoDialog);
            playMusic();
        });
    });

    document.querySelector('[data-choose-photo]')?.addEventListener('click', () => photoInput?.click());
    photoInput?.addEventListener('change', () => {
        const file = photoInput.files?.[0];
        if (file) usePhoto(file);
    });

    document.querySelector('[data-capture-photo]')?.addEventListener('click', () => {
        if (!cameraVideo?.videoWidth) {
            photoInput?.click();
            return;
        }
        const maxEdge = 1280;
        const scale = Math.min(1, maxEdge / Math.max(cameraVideo.videoWidth, cameraVideo.videoHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(cameraVideo.videoWidth * scale);
        canvas.height = Math.round(cameraVideo.videoHeight * scale);
        canvas.getContext('2d', { alpha: false })
            .drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
            if (blob) usePhoto(blob);
        }, 'image/jpeg', 0.86);
    });

    const fetchForm = async (url, formData, signal) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrf,
                'X-Lumi-Language': language,
            },
            body: formData,
            signal,
        });
        const data = await response.json().catch(() => ({
            ok: false,
            message: copy.responseUnreadable,
        }));
        if (!response.ok || !data.ok) {
            throw new Error(data.message || copy.failed);
        }
        return data;
    };

    const processingVideo = document.querySelector('[data-processing-video]');
    const processingTitle = document.querySelector('[data-processing-title]');
    const processingMessage = document.querySelector('[data-processing-message]');
    const loadingDots = document.querySelector('[data-loading-dots]');
    const showExplanationButton = document.querySelector('[data-show-explanation]');

    const beginProcessing = async (kind, request) => {
        stopCamera();
        stopRecording();
        hideDialog(photoDialog);
        hideDialog(voiceDialog);
        pauseMusic();
        pendingResult = null;
        processingController = new AbortController();
        showExplanationButton.disabled = true;
        loadingDots.hidden = false;
        processingTitle.textContent = copy.gathering;
        processingMessage.textContent = copy.processing;
        processingVideo.muted = false;
        processingVideo.volume = 0.55;
        processingVideo.currentTime = 0;
        showDialog(processingDialog);
        processingVideo.play().catch(() => {});

        try {
            const data = await request(processingController.signal);
            pendingResult = data.result;
            loadingDots.hidden = true;
            processingTitle.textContent = pendingResult.blocked
                ? copy.safeTitle
                : copy.readyTitle;
            processingMessage.textContent = pendingResult.blocked
                ? copy.safeMessage
                : copy.readyMessage;
            showExplanationButton.disabled = false;
            if (data.usage?.type) {
                updateUsage(data.usage.type, data.usage);
                if (Number(data.usage.remaining) <= 2) {
                    showToast(formatCopy(copy.remaining, {
                        remaining: data.usage.remaining,
                        limit: data.usage.limit,
                        type: usageTypeLabel(data.usage.type),
                    }));
                }
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            loadingDots.hidden = true;
            showExplanationButton.disabled = true;
            processingTitle.textContent = copy.errorTitle;
            processingMessage.textContent = error.message || copy.retry;
            showToast(processingMessage.textContent);
        }
    };

    sendPhotoButton?.addEventListener('click', () => {
        if (!selectedPhoto) return;
        if (usageLimitReached('image')) {
            showToast(formatCopy(copy.limitReached, { type: copy.imageType }));
            return;
        }
        const formData = new FormData();
        formData.append('image', selectedPhoto, selectedPhoto.name || 'descoberta.jpg');
        beginProcessing('image', (signal) => fetchForm('api/analyze-image.php', formData, signal));
    });

    const recordButton = document.querySelector('[data-record]');
    const recordingStatus = document.querySelector('[data-recording-status]');
    const recordingTimer = document.querySelector('[data-recording-timer]');
    const recordingPreview = document.querySelector('[data-recording-preview]');
    const listenRecordingButton = document.querySelector('[data-listen-recording]');
    const sendVoiceButton = document.querySelector('[data-send-voice]');

    const formatTime = (seconds) => {
        const safe = Math.max(0, seconds);
        return `00:${String(safe).padStart(2, '0')}`;
    };

    const clearRecording = () => {
        recordedBlob = null;
        if (recordedUrl) URL.revokeObjectURL(recordedUrl);
        recordedUrl = '';
        recordingPreview.pause();
        recordingPreview.removeAttribute('src');
        recordingPreview.hidden = true;
        listenRecordingButton.disabled = true;
        sendVoiceButton.disabled = true;
        recordingTimer.textContent = '00:00';
        recordingStatus.textContent = copy.recordStart;
    };

    const supportedRecordingType = () => {
        const options = ['audio/webm;codecs=opus', 'audio/mp4', 'audio/webm'];
        return options.find((type) => window.MediaRecorder?.isTypeSupported?.(type)) || '';
    };

    const stopRecording = () => {
        window.clearInterval(recordingInterval);
        recordingInterval = null;
        if (recorder?.state === 'recording') recorder.stop();
        audioStream?.getTracks().forEach((track) => track.stop());
        audioStream = null;
        recordButton?.classList.remove('is-recording');
        recordButton?.setAttribute('aria-label', copy.recordLabel);
    };

    const startRecording = async () => {
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            showToast(copy.unsupported);
            return;
        }
        clearRecording();
        try {
            audioStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    channelCount: 1,
                },
            });
            const mimeType = supportedRecordingType();
            recorder = mimeType
                ? new MediaRecorder(audioStream, { mimeType })
                : new MediaRecorder(audioStream);
            const chunks = [];
            recorder.addEventListener('dataavailable', (event) => {
                if (event.data.size) chunks.push(event.data);
            });
            recorder.addEventListener('stop', () => {
                recordedBlob = new Blob(chunks, { type: recorder.mimeType || mimeType || 'audio/webm' });
                recordedUrl = URL.createObjectURL(recordedBlob);
                recordingPreview.src = recordedUrl;
                recordingPreview.hidden = false;
                listenRecordingButton.disabled = false;
                sendVoiceButton.disabled = usageLimitReached('voice');
                recordingStatus.textContent = copy.recorded;
                if (usageLimitReached('voice')) {
                    showToast(formatCopy(copy.limitReached, { type: copy.voiceType }));
                }
            });
            recorder.start(250);
            recordingStartedAt = Date.now();
            recordButton.classList.add('is-recording');
            recordButton.setAttribute('aria-label', copy.stopLabel);
            recordingStatus.textContent = copy.recording;
            recordingInterval = window.setInterval(() => {
                const elapsed = Math.min(30, Math.floor((Date.now() - recordingStartedAt) / 1000));
                recordingTimer.textContent = formatTime(elapsed);
                if (elapsed >= 30) stopRecording();
            }, 250);
        } catch {
            showToast(copy.microphoneError);
        }
    };

    recordButton?.addEventListener('click', () => {
        if (recorder?.state === 'recording') stopRecording();
        else startRecording();
    });

    document.querySelector('[data-open-voice]')?.addEventListener('click', () => {
        pauseMusic();
        clearRecording();
        showDialog(voiceDialog);
    });

    document.querySelectorAll('[data-close-voice]').forEach((button) => {
        button.addEventListener('click', () => {
            stopRecording();
            clearRecording();
            hideDialog(voiceDialog);
            playMusic();
        });
    });

    listenRecordingButton?.addEventListener('click', () => {
        recordingPreview.currentTime = 0;
        recordingPreview.play().catch(() => {});
    });

    sendVoiceButton?.addEventListener('click', () => {
        if (!recordedBlob) return;
        if (usageLimitReached('voice')) {
            showToast(formatCopy(copy.limitReached, { type: copy.voiceType }));
            return;
        }
        const extension = recordedBlob.type.includes('mp4') ? 'm4a' : 'webm';
        const formData = new FormData();
        formData.append('audio', recordedBlob, `pergunta.${extension}`);
        beginProcessing('voice', (signal) => fetchForm('api/analyze-voice.php', formData, signal));
    });

    document.querySelector('[data-cancel-processing]')?.addEventListener('click', () => {
        processingController?.abort();
        processingController = null;
        processingVideo.pause();
        hideDialog(processingDialog);
        playMusic();
    });

    const explanationVideo = document.querySelector('[data-explanation-video]');
    const explanationAudio = document.querySelector('[data-explanation-audio]');
    const explanationTitle = document.querySelector('[data-explanation-title]');
    const explanationText = document.querySelector('[data-explanation-text]');
    const explanationSubject = document.querySelector('[data-explanation-subject]');
    const explanationSchool = document.querySelector('[data-explanation-school]');
    const explanationCuriosity = document.querySelector('[data-explanation-curiosity]');

    const stopExplanationAudio = () => {
        explanationAudio.pause();
        explanationAudio.currentTime = 0;
    };

    const playExplanation = () => {
        if (!pendingResult) return;
        stopExplanationAudio();
        explanationAudio.src = pendingResult.audio_data_url || pendingResult.audio_url || '';
        if (!explanationAudio.src) return;
        explanationAudio.play().catch(() => {
            showToast(copy.audioError);
        });
    };

    const fillExplanation = () => {
        const result = pendingResult;
        const category = copy.categories[result.category] || copy.categories.other;
        explanationTitle.textContent = result.title || copy.discovery;
        explanationText.textContent = result.spoken_text || result.summary || '';
        explanationSubject.textContent = result.blocked
            ? copy.safety
            : formatCopy(copy.discoverySubject, { category: category || copy.curiosityDefault });

        explanationSchool.textContent = result.school_subject
            ? formatCopy(copy.school, { value: result.school_subject })
            : '';
        explanationSchool.hidden = !result.school_subject;
        explanationCuriosity.textContent = result.curiosity
            ? formatCopy(copy.curiosity, { value: result.curiosity })
            : '';
        explanationCuriosity.hidden = !result.curiosity;
    };

    showExplanationButton?.addEventListener('click', () => {
        if (!pendingResult || showExplanationButton.disabled) return;
        processingVideo.pause();
        hideDialog(processingDialog);
        fillExplanation();
        showDialog(explanationDialog);
        explanationVideo.currentTime = 0;
        explanationVideo.play().catch(() => {});
        playExplanation();
    });

    document.querySelector('[data-repeat-explanation]')?.addEventListener('click', playExplanation);

    document.querySelector('[data-close-explanation]')?.addEventListener('click', () => {
        stopExplanationAudio();
        explanationVideo.pause();
        hideDialog(explanationDialog);
        pendingResult = null;
        resetPhoto();
        clearRecording();
        playMusic();
    });

    photoDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        document.querySelector('[data-close-photo]')?.click();
    });
    voiceDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        document.querySelector('[data-close-voice]')?.click();
    });
    processingDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        document.querySelector('[data-cancel-processing]')?.click();
    });
    explanationDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        document.querySelector('[data-close-explanation]')?.click();
    });

    window.addEventListener('beforeunload', () => {
        stopCamera();
        stopRecording();
        stopExplanationAudio();
    });
})();
