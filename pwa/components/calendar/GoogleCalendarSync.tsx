import React from 'react';
import { GoogleLogin, CredentialResponse } from '@react-oauth/google';

const GoogleCalendarSync = () => {
    const clientId = '821363302500-6nc8cl1jseugkcl1n9lqjv64rueotfad.apps.googleusercontent.com';

    const handleLoginSuccess = (response: CredentialResponse) => {
        console.log('Google Login Success:', response);
        if (response.credential) {
            // Send the token to the server to sync with Google Calendar
            fetch('/sync-google-calendar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tokenId: response.credential }),
            })
                .then((response) => response.json())
                .then((data) => {
                    console.log('Google Calendar sync response:', data);
                })
                .catch((error) => {
                    console.error('Error syncing with Google Calendar:', error);
                });
        }
    };

    const handleLoginFailure = (error: any) => {
        console.error('Google Login Failure:', error);
    };

    return (
        <div>
            <GoogleLogin
                onSuccess={handleLoginSuccess}
                onError={handleLoginFailure}
                useOneTap
            />
        </div>
    );
};

export default GoogleCalendarSync;
