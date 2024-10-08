import React, {useContext} from 'react';
import {useGoogleLogin} from '@react-oauth/google';
import {getToken} from '@helpers/localHelper';
import {Repairer} from '@interfaces/Repairer';
import {Button} from '@mui/material';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {repairerResource} from '@resources/repairerResource';

type GoogleCalendarSyncProps = {
  repairer: Repairer;
};

const GoogleCalendarSync = ({repairer}: GoogleCalendarSyncProps) => {
  const {setRepairer} = useContext(DashboardRepairerContext);
  const handleLoginSuccess = (code: string) => {
    const currentToken = getToken();
    fetch('/google/sync/callback', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${currentToken}`,
      },
      body: JSON.stringify({
        code: code,
        repairer: repairer ? repairer.id : '',
      }),
    })
      .then(async (response) => {
        if (response.ok) {
          setRepairer(await repairerResource.getById(repairer.id));
        }
      })
      .catch((error) => {
        console.error('Error syncing with Google Calendar:', error);
      });
  };

  const handleLoginFailure = (error: any) => {
    console.error('Google Login Failure:', error);
  };

  const login = useGoogleLogin({
    onSuccess: (codeResponse) => handleLoginSuccess(codeResponse.code),
    onError: (error) => handleLoginFailure(error),
    flow: 'auth-code',
  });

  return (
    <div>
      <Button variant={'outlined'} onClick={() => login()}>
        {repairer.isConnectedToGoogle
          ? 'Changer de compte'
          : 'Se connecter avec Google'}
      </Button>
    </div>
  );
};

export default GoogleCalendarSync;
