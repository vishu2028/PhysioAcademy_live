const config = window.physioFirebaseConfig || {};
const errorElements = [...document.querySelectorAll('[data-provider-error]')];
const consentCheckbox = document.querySelector('[data-auth-consent]');
let firebaseAuth = null;
let firebaseInitializationError = null;
let firebaseSdkPromise = null;
let firebaseAuthSdk = null;
let appleScriptPromise = null;

async function getFirebaseAuth() {
    if (!window.physioFirebaseConfigured) {
        return null;
    }

    if (firebaseAuth) {
        return firebaseAuth;
    }

    firebaseSdkPromise ??= Promise.all([
        import('https://www.gstatic.com/firebasejs/11.0.2/firebase-app.js'),
        import('https://www.gstatic.com/firebasejs/11.0.2/firebase-auth.js')
    ]).then(([appSdk, authSdk]) => {
        const firebaseApp = appSdk.getApps()[0] || appSdk.initializeApp(config);
        firebaseAuthSdk = authSdk;
        firebaseAuth = authSdk.getAuth(firebaseApp);

        return firebaseAuth;
    }).catch((error) => {
        firebaseInitializationError = error;
        throw error;
    });

    return firebaseSdkPromise;
}

const firebaseAuthReady = window.physioFirebaseConfigured
    ? getFirebaseAuth().catch((error) => {
        firebaseInitializationError = error;
        return null;
    })
    : Promise.resolve(null);

function showError(message) {
    errorElements.forEach((element) => {
        element.textContent = message;
        element.classList.add('is-visible');
    });
}

function clearError() {
    errorElements.forEach((element) => {
        element.textContent = '';
        element.classList.remove('is-visible');
    });
}

function hasConsent() {
    if (consentCheckbox?.checked) {
        return true;
    }

    showError('Please agree to the Terms & Conditions and Privacy Policy to continue.');
    consentCheckbox?.focus();
    return false;
}

function setLoading(button, loading, label) {
    button.disabled = loading;
    button.setAttribute('aria-busy', loading ? 'true' : 'false');
    button.querySelector('span').textContent = loading ? 'Connecting securely…' : label;
}

function googleErrorMessage(error) {
    if (error?.code === 'auth/popup-closed-by-user' || error?.code === 'auth/cancelled-popup-request') {
        return 'Google sign-in was cancelled.';
    }

    if (error?.code === 'auth/unauthorized-domain' || error?.code === 'auth/unauthorized-continue-uri') {
        return 'This website is not authorized for Google sign-in. Please contact support.';
    }

    if (error?.code === 'auth/operation-not-allowed') {
        return 'Google sign-in is disabled for this Firebase project. Please contact support.';
    }

    if (error?.code === 'auth/invalid-api-key') {
        return 'Google sign-in is misconfigured. Please contact support.';
    }

    if (error?.code === 'auth/network-request-failed') {
        return 'A network error interrupted Google sign-in. Check your connection and try again.';
    }

    if (error?.code === 'auth/web-storage-unsupported') {
        return 'Your browser is blocking the storage needed for Google sign-in. Enable site data and try again.';
    }

    return error?.message || 'Google sign-in could not be completed. Please try again.';
}

async function postJson(url, payload) {
    let response;

    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': window.physioFirebaseCsrf
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
    } catch (error) {
        if (error instanceof TypeError) {
            throw new Error('Could not reach the PhysioSource sign-in service. Check your connection and try again.');
        }

        throw error;
    }

    let data;
    try {
        data = await response.json();
    } catch {
        throw new Error(`The sign-in service returned an unexpected response (HTTP ${response.status}).`);
    }

    if (!response.ok || !data.success) {
        throw new Error(data.message || `Sign-in could not be completed (HTTP ${response.status}).`);
    }

    return data;
}

const googleButtons = [...document.querySelectorAll('[data-google-action]')];
const googleLabel = 'Continue with Google';

googleButtons.forEach((button) => setLoading(button, true, googleLabel));
void firebaseAuthReady.then(() => {
    googleButtons.forEach((button) => setLoading(button, false, googleLabel));
});

googleButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        clearError();

        if (!hasConsent()) {
            return;
        }

        if (!window.physioFirebaseConfigured) {
            showError(firebaseInitializationError
                ? 'Google sign-in could not be initialized. Please contact support.'
                : 'Google sign-in is unavailable because Firebase web configuration is missing. Please contact support.');
            return;
        }

        setLoading(button, true, googleLabel);

        try {
            const auth = await firebaseAuthReady;
            if (!auth) {
                throw firebaseInitializationError || new Error('Google sign-in could not be initialized.');
            }

            const provider = new firebaseAuthSdk.GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });
            const credential = await firebaseAuthSdk.signInWithPopup(auth, provider);
            const data = await postJson(window.physioFirebaseLoginUrl, {
                id_token: await credential.user.getIdToken(),
                accepted_terms: true
            });

            window.location.assign(data.redirect);
        } catch (error) {
            showError(googleErrorMessage(error));
            setLoading(button, false, googleLabel);
        }
    });
});

function loadAppleSdk() {
    if (window.AppleID?.auth) {
        return Promise.resolve();
    }

    appleScriptPromise ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';
        script.async = true;
        script.onload = resolve;
        script.onerror = () => reject(new Error('Apple sign-in could not be loaded. Please try again.'));
        document.head.appendChild(script);
    });

    return appleScriptPromise;
}

document.querySelectorAll('[data-apple-action]').forEach((button) => {
    const label = 'Continue with Apple';

    button.addEventListener('click', async () => {
        clearError();

        if (!hasConsent()) {
            return;
        }

        if (!window.physioAppleConfig?.enabled) {
            showError('Apple sign-in is not configured for this website yet.');
            return;
        }

        setLoading(button, true, label);

        try {
            await loadAppleSdk();
            const challenge = await postJson(window.physioAppleConfig.challengeUrl, {
                accepted_terms: true
            });

            window.AppleID.auth.init({
                clientId: window.physioAppleConfig.clientId,
                scope: 'name email',
                redirectURI: window.physioAppleConfig.redirectUri,
                state: challenge.state,
                nonce: challenge.nonce,
                usePopup: true
            });

            const result = await window.AppleID.auth.signIn();
            const name = [result?.user?.name?.firstName, result?.user?.name?.lastName]
                .filter(Boolean)
                .join(' ')
                .slice(0, 255);
            const data = await postJson(window.physioAppleConfig.loginUrl, {
                id_token: result?.authorization?.id_token,
                state: result?.authorization?.state,
                name,
                accepted_terms: true
            });

            window.location.assign(data.redirect);
        } catch (error) {
            if (error?.error === 'popup_closed_by_user' || error?.message?.includes('popup_closed_by_user')) {
                showError('Apple sign-in was cancelled.');
            } else {
                showError(error?.message || 'Apple sign-in could not be completed. Please try again.');
            }
            setLoading(button, false, label);
        }
    });
});
