(() => {
    const body = document.body;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const userName = body.dataset.userName || 'explorador';
    const userAge = Number(body.dataset.userAge || 8);

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
            musicToggle?.setAttribute('aria-label', 'Pausar música');
            musicToggle?.setAttribute('title', 'Pausar música');
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
            musicToggle.setAttribute('aria-label', 'Tocar música');
            musicToggle.setAttribute('title', 'Tocar música');
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

    const cameraVideo = document.querySelector('[data-camera-video]');
    const cameraEmpty = document.querySelector('[data-camera-empty]');
    const photoPreview = document.querySelector('[data-photo-preview]');
    const photoInput = document.querySelector('[data-photo-input]');
    const sendPhotoButton = document.querySelector('[data-send-photo]');

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
            showToast('Não consegui abrir a câmera. Você ainda pode escolher uma imagem da galeria.');
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
            showToast('A foto precisa ter no máximo 8 MB.');
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
        sendPhotoButton.disabled = false;
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
            headers: { 'X-CSRF-Token': csrf },
            body: formData,
            signal,
        });
        const data = await response.json().catch(() => ({
            ok: false,
            message: 'A resposta da Lumi não pôde ser lida.',
        }));
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'A Lumi não conseguiu concluir agora.');
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
        showExplanationButton.hidden = true;
        loadingDots.hidden = false;
        processingTitle.textContent = 'Juntando as pistas...';
        processingMessage.textContent = 'Isso pode levar alguns segundos.';
        processingVideo.muted = kind === 'image';
        processingVideo.volume = kind === 'voice' ? 0.55 : 0;
        processingVideo.currentTime = 0;
        showDialog(processingDialog);
        processingVideo.play().catch(() => {});

        try {
            const data = await request(processingController.signal);
            pendingResult = data.result;
            loadingDots.hidden = true;
            processingTitle.textContent = pendingResult.blocked
                ? 'Encontrei um limite seguro.'
                : 'Mistério desvendado!';
            processingMessage.textContent = pendingResult.blocked
                ? 'A Lumi preparou uma resposta para manter a conversa segura.'
                : 'A explicação está pronta para você ouvir.';
            showExplanationButton.hidden = false;
            if (typeof data.remaining === 'number' && data.remaining <= 2) {
                showToast(`Restam ${data.remaining} descobertas hoje.`);
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            loadingDots.hidden = true;
            processingTitle.textContent = 'Essa pista escapou.';
            processingMessage.textContent = error.message || 'Vamos tentar novamente?';
            showToast(processingMessage.textContent);
        }
    };

    sendPhotoButton?.addEventListener('click', () => {
        if (!selectedPhoto) return;
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
        recordingStatus.textContent = 'Toque no microfone para começar.';
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
        recordButton?.setAttribute('aria-label', 'Começar gravação');
    };

    const startRecording = async () => {
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            showToast('Este navegador não permite gravação. Tente usar Chrome, Edge ou Safari atualizado.');
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
                sendVoiceButton.disabled = false;
                recordingStatus.textContent = 'Pergunta gravada. Você pode ouvir ou enviar.';
            });
            recorder.start(250);
            recordingStartedAt = Date.now();
            recordButton.classList.add('is-recording');
            recordButton.setAttribute('aria-label', 'Parar gravação');
            recordingStatus.textContent = 'Estou ouvindo... toque novamente para terminar.';
            recordingInterval = window.setInterval(() => {
                const elapsed = Math.min(30, Math.floor((Date.now() - recordingStartedAt) / 1000));
                recordingTimer.textContent = formatTime(elapsed);
                if (elapsed >= 30) stopRecording();
            }, 250);
        } catch {
            showToast('Não consegui usar o microfone. Libere a permissão e tente novamente.');
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
        window.speechSynthesis?.cancel();
        explanationAudio.pause();
        explanationAudio.currentTime = 0;
    };

    const playExplanation = () => {
        if (!pendingResult) return;
        stopExplanationAudio();
        explanationAudio.src = pendingResult.audio_data_url || pendingResult.audio_url || '';

        let audioStarted = false;
        const startAudio = () => {
            if (audioStarted || !explanationAudio.src) return;
            audioStarted = true;
            explanationAudio.play().catch(() => {
                showToast('Toque em Repetir para ouvir a explicação.');
            });
        };

        if ('speechSynthesis' in window && 'SpeechSynthesisUtterance' in window) {
            const greeting = new SpeechSynthesisUtterance(`${userName},`);
            greeting.lang = 'pt-BR';
            greeting.rate = userAge <= 8 ? 0.86 : 0.94;
            greeting.pitch = 1.08;
            greeting.onend = startAudio;
            greeting.onerror = startAudio;
            window.speechSynthesis.speak(greeting);
            window.setTimeout(startAudio, 2200);
        } else {
            startAudio();
        }
    };

    const fillExplanation = () => {
        const result = pendingResult;
        explanationTitle.textContent = result.title || 'Descoberta da Lumi';
        explanationText.textContent = result.spoken_text || result.summary || '';
        explanationSubject.textContent = result.blocked
            ? 'Segurança da Lumi'
            : `Descoberta: ${result.category || 'curiosidade'}`;

        explanationSchool.textContent = result.school_subject
            ? `Matéria: ${result.school_subject}`
            : '';
        explanationSchool.hidden = !result.school_subject;
        explanationCuriosity.textContent = result.curiosity
            ? `Curiosidade: ${result.curiosity}`
            : '';
        explanationCuriosity.hidden = !result.curiosity;
    };

    showExplanationButton?.addEventListener('click', () => {
        if (!pendingResult) return;
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
