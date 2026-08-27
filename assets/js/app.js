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
            homework: 'Homework help', homeworkReadyTitle: 'Your homework help is ready!',
            homeworkReadyMessage: 'Tap to see the answer and listen to Lumi explain it.',
            homeworkShowAnswer: 'See my help', explanation: 'Explanation', homeworkHistoryMissing: 'That homework help is no longer available.',
            homeworkRecord: 'Record a question', homeworkRecordAgain: 'Record again',
            homeworkPhotoCount: '{count} of {max} photos', homeworkPhotoLimit: 'You can add up to {max} photos.',
            homeworkPhotoRemaining: 'Only {remaining} photo uploads remain today.', homeworkRemovePhoto: 'Remove photo {number}',
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
            homework: 'Ajuda com a lição', homeworkReadyTitle: 'Sua ajuda com a lição está pronta!',
            homeworkReadyMessage: 'Toque para ver a resposta e ouvir a Lumi explicar.',
            homeworkShowAnswer: 'Ver minha ajuda', explanation: 'Explicação', homeworkHistoryMissing: 'Essa ajuda com a lição não está mais disponível.',
            homeworkRecord: 'Gravar uma pergunta', homeworkRecordAgain: 'Gravar novamente',
            homeworkPhotoCount: '{count} de {max} fotos', homeworkPhotoLimit: 'Você pode adicionar até {max} fotos.',
            homeworkPhotoRemaining: 'Restam apenas {remaining} envios de foto hoje.', homeworkRemovePhoto: 'Remover foto {number}',
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
            homework: 'Ayuda con la tarea', homeworkReadyTitle: '¡Tu ayuda con la tarea está lista!',
            homeworkReadyMessage: 'Toca para ver la respuesta y escuchar a Lumi explicarla.',
            homeworkShowAnswer: 'Ver mi ayuda', explanation: 'Explicación', homeworkHistoryMissing: 'Esta ayuda con la tarea ya no está disponible.',
            homeworkRecord: 'Grabar una pregunta', homeworkRecordAgain: 'Grabar de nuevo',
            homeworkPhotoCount: '{count} de {max} fotos', homeworkPhotoLimit: 'Puedes añadir hasta {max} fotos.',
            homeworkPhotoRemaining: 'Solo quedan {remaining} envíos de foto hoy.', homeworkRemovePhoto: 'Eliminar foto {number}',
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
    const homeworkDialog = document.querySelector('#homework-dialog');
    const homeworkResultDialog = document.querySelector('#homework-result-dialog');
    const homeworkHistoryDialog = document.querySelector('#homework-history-dialog');
    const toast = document.querySelector('[data-toast]');

    let musicWanted = true;
    let pendingResult = null;
    let pendingHomeworkResult = null;
    let processingMode = 'discovery';
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

    const anyFlowOpen = () => [
        photoDialog, voiceDialog, processingDialog, explanationDialog,
        homeworkDialog, homeworkResultDialog, homeworkHistoryDialog,
    ]
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
    const processingActionLabel = document.querySelector('[data-processing-action-label]');

    const beginProcessing = async (kind, request, mode = 'discovery') => {
        stopCamera();
        stopRecording();
        stopHomeworkCamera?.();
        stopHomeworkRecording?.();
        hideDialog(photoDialog);
        hideDialog(voiceDialog);
        hideDialog(homeworkDialog);
        pauseMusic();
        processingMode = mode;
        pendingResult = null;
        pendingHomeworkResult = null;
        processingController = new AbortController();
        showExplanationButton.disabled = true;
        loadingDots.hidden = false;
        processingTitle.textContent = mode === 'homework' ? copy.homework : copy.gathering;
        processingMessage.textContent = mode === 'homework' ? copy.processing : copy.processing;
        if (processingActionLabel) processingActionLabel.textContent = mode === 'homework' ? copy.homeworkShowAnswer : copy.explanation;
        processingVideo.muted = false;
        processingVideo.volume = 0.55;
        processingVideo.currentTime = 0;
        showDialog(processingDialog);
        processingVideo.play().catch(() => {});

        try {
            const data = await request(processingController.signal);
            if (mode === 'homework') pendingHomeworkResult = data.result;
            else pendingResult = data.result;
            const result = mode === 'homework' ? pendingHomeworkResult : pendingResult;
            loadingDots.hidden = true;
            processingTitle.textContent = result.blocked
                ? copy.safeTitle
                : mode === 'homework' ? copy.homeworkReadyTitle : copy.readyTitle;
            processingMessage.textContent = result.blocked
                ? copy.safeMessage
                : mode === 'homework' ? copy.homeworkReadyMessage : copy.readyMessage;
            showExplanationButton.disabled = false;
            if (mode === 'homework') {
                if (data.usage?.image) updateUsage('image', data.usage.image);
                if (data.usage?.voice) updateUsage('voice', data.usage.voice);
            } else if (data.usage?.type) {
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
        if (showExplanationButton.disabled) return;
        processingVideo.pause();
        hideDialog(processingDialog);
        if (processingMode === 'homework') {
            if (!pendingHomeworkResult) return;
            showHomeworkResult(pendingHomeworkResult);
            return;
        }
        if (!pendingResult) return;
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

    const homeworkCameraVideo = document.querySelector('[data-homework-camera-video]');
    const homeworkCameraEmpty = document.querySelector('[data-homework-camera-empty]');
    const homeworkPhotoInput = document.querySelector('[data-homework-photo-input]');
    const homeworkPhotoList = document.querySelector('[data-homework-photo-list]');
    const homeworkPhotoCount = document.querySelector('[data-homework-photo-count]');
    const homeworkQuestion = document.querySelector('[data-homework-question]');
    const homeworkSendButton = document.querySelector('[data-send-homework]');
    const homeworkRecordButton = document.querySelector('[data-homework-record]');
    const homeworkRecordLabel = document.querySelector('[data-homework-record-label]');
    const homeworkRecordStatus = document.querySelector('[data-homework-record-status]');
    const homeworkRecordingPreview = document.querySelector('[data-homework-recording-preview]');
    const homeworkHistoryList = document.querySelector('[data-homework-history-list]');
    const homeworkHistoryEmpty = document.querySelector('[data-homework-history-empty]');
    const homeworkResultTitle = document.querySelector('[data-homework-result-title]');
    const homeworkResultSubject = document.querySelector('[data-homework-result-subject]');
    const homeworkAnswerText = document.querySelector('[data-homework-answer-text]');
    const homeworkAnswerTextWrap = document.querySelector('[data-homework-answer-text-wrap]');
    const homeworkAnswerImage = document.querySelector('[data-homework-answer-image]');
    const homeworkAnswerImageButton = document.querySelector('[data-open-homework-image]');
    const homeworkTeachingText = document.querySelector('[data-homework-teaching-text]');
    const homeworkExplanationVideo = document.querySelector('[data-homework-explanation-video]');
    const homeworkExplanationAudio = document.querySelector('[data-homework-explanation-audio]');
    const homeworkImageViewer = document.querySelector('#homework-image-viewer');
    const homeworkFullscreenImage = document.querySelector('[data-homework-fullscreen-image]');
    const homeworkZoomReset = document.querySelector('[data-homework-zoom-reset]');

    let homeworkCameraStream = null;
    let homeworkPhotos = [];
    let homeworkImageZoom = 1;
    let homeworkAudioStream = null;
    let homeworkRecorder = null;
    let homeworkRecordedBlob = null;

    const stopHomeworkCamera = () => {
        homeworkCameraStream?.getTracks().forEach((track) => track.stop());
        homeworkCameraStream = null;
        if (homeworkCameraVideo) homeworkCameraVideo.srcObject = null;
    };

    const startHomeworkCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia || !homeworkCameraVideo) {
            homeworkCameraEmpty?.removeAttribute('hidden');
            return;
        }
        try {
            homeworkCameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 960 } },
                audio: false,
            });
            homeworkCameraVideo.srcObject = homeworkCameraStream;
            homeworkCameraEmpty?.setAttribute('hidden', '');
            await homeworkCameraVideo.play();
        } catch {
            homeworkCameraEmpty?.removeAttribute('hidden');
            showToast(copy.cameraError);
        }
    };

    const HOMEWORK_MAX_PHOTOS = 4;

    const renderHomeworkPhotos = () => {
        if (homeworkPhotoCount) {
            homeworkPhotoCount.textContent = formatCopy(copy.homeworkPhotoCount, {
                count: homeworkPhotos.length,
                max: HOMEWORK_MAX_PHOTOS,
            });
        }
        if (homeworkPhotoList) {
            homeworkPhotoList.replaceChildren(...homeworkPhotos.map((entry, index) => {
                const item = document.createElement('div');
                item.className = 'homework-photo-item';
                const image = document.createElement('img');
                image.src = entry.url;
                image.alt = `${index + 1}`;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'homework-photo-remove';
                remove.dataset.removeHomeworkPhoto = String(index);
                remove.setAttribute('aria-label', formatCopy(copy.homeworkRemovePhoto, { number: index + 1 }));
                remove.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>';
                item.append(image, remove);
                return item;
            }));
        }
        const remaining = Math.max(0, usageState.image.limit - usageState.image.used);
        if (homeworkSendButton) {
            homeworkSendButton.disabled = homeworkPhotos.length === 0 || homeworkPhotos.length > remaining;
        }
    };

    const resetHomeworkPhotos = () => {
        homeworkPhotos.forEach((entry) => URL.revokeObjectURL(entry.url));
        homeworkPhotos = [];
        if (homeworkPhotoInput) homeworkPhotoInput.value = '';
        renderHomeworkPhotos();
    };

    const addHomeworkPhoto = (blob, name = 'homework.jpg') => {
        if (!blob || blob.size > 8 * 1024 * 1024) {
            showToast(copy.photoSize);
            return;
        }
        if (homeworkPhotos.length >= HOMEWORK_MAX_PHOTOS) {
            showToast(formatCopy(copy.homeworkPhotoLimit, { max: HOMEWORK_MAX_PHOTOS }));
            return;
        }
        const file = blob instanceof File ? blob : new File([blob], name, { type: blob.type || 'image/jpeg' });
        homeworkPhotos.push({ file, url: URL.createObjectURL(file) });
        renderHomeworkPhotos();
    };

    const stopHomeworkRecording = () => {
        if (homeworkRecorder?.state === 'recording') homeworkRecorder.stop();
        homeworkAudioStream?.getTracks().forEach((track) => track.stop());
        homeworkAudioStream = null;
        homeworkRecordButton?.classList.remove('is-recording');
        if (homeworkRecordLabel) homeworkRecordLabel.textContent = copy.homeworkRecord;
    };

    const clearHomeworkRecording = () => {
        homeworkRecordedBlob = null;
        homeworkRecordingPreview?.pause();
        homeworkRecordingPreview?.removeAttribute('src');
        if (homeworkRecordingPreview) homeworkRecordingPreview.hidden = true;
        if (homeworkRecordStatus) homeworkRecordStatus.textContent = '';
    };

    const resetHomework = () => {
        stopHomeworkCamera();
        stopHomeworkRecording();
        resetHomeworkPhotos();
        clearHomeworkRecording();
        if (homeworkQuestion) homeworkQuestion.value = '';
        document.querySelector('input[name="homework-answer-format"][value="text"]')?.click();
    };

    const closeHomeworkDialog = () => {
        resetHomework();
        hideDialog(homeworkDialog);
        playMusic();
    };

    document.querySelector('[data-open-homework]')?.addEventListener('click', () => {
        pauseMusic();
        resetHomework();
        showDialog(homeworkDialog);
        startHomeworkCamera();
    });
    document.querySelectorAll('[data-close-homework]').forEach((button) => button.addEventListener('click', closeHomeworkDialog));
    document.querySelector('[data-homework-choose-photo]')?.addEventListener('click', () => homeworkPhotoInput?.click());
    homeworkPhotoInput?.addEventListener('change', () => {
        Array.from(homeworkPhotoInput.files || []).forEach((file) => addHomeworkPhoto(file, file.name));
        homeworkPhotoInput.value = '';
    });
    homeworkPhotoList?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-homework-photo]');
        if (!button) return;
        const index = Number(button.dataset.removeHomeworkPhoto);
        if (!Number.isInteger(index) || !homeworkPhotos[index]) return;
        URL.revokeObjectURL(homeworkPhotos[index].url);
        homeworkPhotos.splice(index, 1);
        renderHomeworkPhotos();
    });
    document.querySelector('[data-homework-capture-photo]')?.addEventListener('click', () => {
        if (!homeworkCameraVideo?.videoWidth) {
            homeworkPhotoInput?.click();
            return;
        }
        const maxEdge = 1280;
        const scale = Math.min(1, maxEdge / Math.max(homeworkCameraVideo.videoWidth, homeworkCameraVideo.videoHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(homeworkCameraVideo.videoWidth * scale);
        canvas.height = Math.round(homeworkCameraVideo.videoHeight * scale);
        canvas.getContext('2d', { alpha: false }).drawImage(homeworkCameraVideo, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => { if (blob) addHomeworkPhoto(blob, `homework-${Date.now()}.jpg`); }, 'image/jpeg', 0.86);
    });

    const startHomeworkRecording = async () => {
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            showToast(copy.unsupported);
            return;
        }
        clearHomeworkRecording();
        try {
            homeworkAudioStream = await navigator.mediaDevices.getUserMedia({
                audio: { echoCancellation: true, noiseSuppression: true, channelCount: 1 },
            });
            const mimeType = supportedRecordingType();
            homeworkRecorder = mimeType ? new MediaRecorder(homeworkAudioStream, { mimeType }) : new MediaRecorder(homeworkAudioStream);
            const chunks = [];
            homeworkRecorder.addEventListener('dataavailable', (event) => { if (event.data.size) chunks.push(event.data); });
            homeworkRecorder.addEventListener('stop', () => {
                homeworkRecordedBlob = new Blob(chunks, { type: homeworkRecorder.mimeType || mimeType || 'audio/webm' });
                homeworkRecordingPreview.src = URL.createObjectURL(homeworkRecordedBlob);
                homeworkRecordingPreview.hidden = false;
                homeworkRecordStatus.textContent = copy.recorded;
                homeworkRecordLabel.textContent = copy.homeworkRecordAgain;
            });
            homeworkRecorder.start(250);
            homeworkRecordButton.classList.add('is-recording');
            homeworkRecordLabel.textContent = copy.stopLabel;
            homeworkRecordStatus.textContent = copy.recording;
        } catch {
            showToast(copy.microphoneError);
        }
    };

    homeworkRecordButton?.addEventListener('click', () => {
        if (homeworkRecorder?.state === 'recording') stopHomeworkRecording();
        else startHomeworkRecording();
    });

    const addHomeworkHistoryItem = (result) => {
        if (!homeworkHistoryList || !result?.id) return;
        homeworkHistoryEmpty?.remove();
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'homework-history-item';
        item.dataset.openHomeworkHistoryItem = '';
        item.dataset.historyId = String(result.id);
        item.innerHTML = '<span class="homework-history-item-icon" aria-hidden="true">✦</span><span><strong></strong><small></small></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></svg>';
        item.querySelector('strong').textContent = result.title || copy.homework;
        item.querySelector('small').textContent = result.school_subject || copy.homework;
        homeworkHistoryList.prepend(item);
    };

    homeworkSendButton?.addEventListener('click', () => {
        if (!homeworkPhotos.length) return;
        const remainingPhotos = Math.max(0, usageState.image.limit - usageState.image.used);
        if (homeworkPhotos.length > remainingPhotos) {
            showToast(formatCopy(copy.homeworkPhotoRemaining, { remaining: remainingPhotos }));
            return;
        }
        if (homeworkRecordedBlob && usageLimitReached('voice')) {
            showToast(formatCopy(copy.limitReached, { type: copy.voiceType }));
            return;
        }
        const answerFormat = document.querySelector('input[name="homework-answer-format"]:checked')?.value || 'text';
        const formData = new FormData();
        homeworkPhotos.forEach(({ file }, index) => {
            formData.append('images[]', file, file.name || `homework-${index + 1}.jpg`);
        });
        formData.append('question', homeworkQuestion?.value.trim() || '');
        formData.append('answer_format', answerFormat);
        if (homeworkRecordedBlob) {
            const extension = homeworkRecordedBlob.type.includes('mp4') ? 'm4a' : 'webm';
            formData.append('audio', homeworkRecordedBlob, `homework-question.${extension}`);
        }
        beginProcessing('homework', async (signal) => {
            const data = await fetchForm('api/analyze-homework.php', formData, signal);
            addHomeworkHistoryItem(data.result);
            return data;
        }, 'homework');
    });

    const stopHomeworkAudio = () => {
        homeworkExplanationAudio?.pause();
        if (homeworkExplanationAudio) homeworkExplanationAudio.currentTime = 0;
    };

    const playHomeworkAudio = () => {
        if (!pendingHomeworkResult) return;
        stopHomeworkAudio();
        homeworkExplanationAudio.src = pendingHomeworkResult.audio_url || pendingHomeworkResult.audio_data_url || '';
        if (!homeworkExplanationAudio.src) return;
        homeworkExplanationAudio.play().catch(() => showToast(copy.audioError));
    };

    const showHomeworkResult = (result) => {
        pendingHomeworkResult = result;
        homeworkResultTitle.textContent = result.title || copy.homework;
        homeworkResultSubject.textContent = result.school_subject || copy.homework;
        homeworkAnswerText.textContent = result.answer_text || '';
        homeworkTeachingText.textContent = result.teaching_text || result.answer_text || '';
        const showImage = result.answer_format === 'image' && Boolean(result.answer_image_url);
        homeworkAnswerTextWrap.hidden = showImage;
        homeworkAnswerImageButton.hidden = !showImage;
        if (showImage) homeworkAnswerImage.src = result.answer_image_url;
        else homeworkAnswerImage.removeAttribute('src');
        showDialog(homeworkResultDialog);
        homeworkExplanationVideo.currentTime = 0;
        homeworkExplanationVideo.play().catch(() => {});
        playHomeworkAudio();
    };

    const setHomeworkImageZoom = (zoom) => {
        homeworkImageZoom = Math.min(4, Math.max(0.75, zoom));
        if (homeworkFullscreenImage) homeworkFullscreenImage.style.width = `${homeworkImageZoom * 100}%`;
        if (homeworkZoomReset) homeworkZoomReset.textContent = `${Math.round(homeworkImageZoom * 100)}%`;
    };

    const closeHomeworkImage = () => hideDialog(homeworkImageViewer);

    homeworkAnswerImageButton?.addEventListener('click', () => {
        if (!homeworkAnswerImage?.src) return;
        homeworkFullscreenImage.src = homeworkAnswerImage.src;
        setHomeworkImageZoom(1);
        showDialog(homeworkImageViewer);
    });
    document.querySelector('[data-homework-zoom-in]')?.addEventListener('click', () => setHomeworkImageZoom(homeworkImageZoom + 0.25));
    document.querySelector('[data-homework-zoom-out]')?.addEventListener('click', () => setHomeworkImageZoom(homeworkImageZoom - 0.25));
    homeworkZoomReset?.addEventListener('click', () => setHomeworkImageZoom(1));
    document.querySelector('[data-close-homework-image]')?.addEventListener('click', closeHomeworkImage);

    const closeHomeworkResult = () => {
        stopHomeworkAudio();
        homeworkExplanationVideo?.pause();
        hideDialog(homeworkResultDialog);
        pendingHomeworkResult = null;
        playMusic();
    };

    document.querySelector('[data-repeat-homework-audio]')?.addEventListener('click', playHomeworkAudio);
    document.querySelectorAll('[data-close-homework-result]').forEach((button) => button.addEventListener('click', closeHomeworkResult));

    const openHomeworkHistoryItem = async (historyId) => {
        try {
            const response = await fetch(`api/homework-history.php?id=${encodeURIComponent(historyId)}`, {
                headers: { 'X-Lumi-Language': language },
            });
            const data = await response.json().catch(() => ({ ok: false, message: copy.homeworkHistoryMissing }));
            if (!response.ok || !data.ok) throw new Error(data.message || copy.homeworkHistoryMissing);
            hideDialog(homeworkHistoryDialog);
            showHomeworkResult(data.result);
        } catch (error) {
            showToast(error.message || copy.homeworkHistoryMissing);
        }
    };

    document.querySelector('[data-open-homework-history]')?.addEventListener('click', () => {
        pauseMusic();
        showDialog(homeworkHistoryDialog);
    });
    document.querySelectorAll('[data-close-homework-history]').forEach((button) => button.addEventListener('click', () => {
        hideDialog(homeworkHistoryDialog);
        playMusic();
    }));
    homeworkHistoryList?.addEventListener('click', (event) => {
        const item = event.target.closest('[data-open-homework-history-item]');
        if (item?.dataset.historyId) openHomeworkHistoryItem(item.dataset.historyId);
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
    homeworkDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeHomeworkDialog();
    });
    homeworkResultDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeHomeworkResult();
    });
    homeworkImageViewer?.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeHomeworkImage();
    });
    homeworkHistoryDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        hideDialog(homeworkHistoryDialog);
        playMusic();
    });

    window.addEventListener('beforeunload', () => {
        stopCamera();
        stopRecording();
        stopExplanationAudio();
        stopHomeworkCamera();
        stopHomeworkRecording();
        stopHomeworkAudio();
    });
})();
