import React from 'react';
import {GoogleLogin, CredentialResponse} from '@react-oauth/google';
import {getToken} from '@helpers/localHelper';
import {Repairer} from '@interfaces/Repairer';

type GoogleCalendarSyncProps = {
  repairer: Repairer | null;
};

type GoogleDataType = {
  google_oauth_url: string;
};

const GoogleCalendarSync = ({repairer}: GoogleCalendarSyncProps) => {
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
        .then((data: GoogleDataType) => {
          console.log(data);

          window.open(data.google_oauth_url, '_blank');
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
        onError={() => handleLoginFailure}
      />
    </div>
  );
};

export default GoogleCalendarSync;
