import React from 'react';
import {GoogleLogin, CredentialResponse} from '@react-oauth/google';
import {getToken} from '@helpers/localHelper';

const GoogleCalendarSync = ({repairer}) => {
  const handleLoginSuccess = (response: CredentialResponse) => {
    if (response.credential) {
      const currentToken = getToken();
      fetch('/google/auth/url', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${currentToken}`,
        },
        body: JSON.stringify({
          tokenId: response.credential,
          repairer: repairer ? repairer.id : '',
        }),
      })
        .then((response) => response.json())
        .then((data: object) => {
          window.open(data.google_oauth_url, '_blank');
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
        scope="https://www.googleapis.com/auth/calendar"
        flow="auth-code"
        redirectUri={process.env.NEXT_PUBLIC_GOOGLE_OAUTH_REDIRECT_URI}
      />
    </div>
  );
};

export default GoogleCalendarSync;
