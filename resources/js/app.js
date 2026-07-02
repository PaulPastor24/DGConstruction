import './bootstrap';
import { browserSupportsWebAuthn, startAuthentication, startRegistration } from '@simplewebauthn/browser';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startRegistration = startRegistration;
window.startAuthentication = startAuthentication;

Alpine.start();