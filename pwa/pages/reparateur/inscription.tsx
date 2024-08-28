import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import {ENTRYPOINT} from '@config/entrypoint';
import React, {ReactElement, useContext, useState} from 'react';
import {Box, Button, Grid, Paper} from '@mui/material';
import {
  RepairerRegistrationContext,
  RepairerRegistrationProvider,
} from '@contexts/RepairerRegistrationContext';
import {RegistrationTunnelValidation} from '@components/repairers/registration/RegistrationTunnelValidation';
import Link from 'next/link';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import RegistrationTunnelUserInfo from '@components/repairers/registration/RegistrationTunnelUserInfo';

const RepairerRegistration: NextPageWithLayout = ({}) => {
  // you can already use your context here
  /* Think that on each page you have to check if previous steps are completed (not on the firststep),
    to do this, you have to add steps properties for each step in your context.
    Example:
      - stepOneCompleted -> when the identity form is completed (and valid) you have to put it true when user clic on next button
      - stepTwoFirstQuestionCompleted (antenna) -> if stepOneCompleted is true so you have to put it true when user clic on next button, if stepOneCompleted is false you have to redirect on this stepOne (/repairer/inscription)
      - stepTwoCompleted -> if previous steps are true, you have to put it true when the user have completed this form (and the form is valid) and he clics on next button, if one of the previous steps is false you have to redirect on stepOne (/repairer/inscription)
      - formCompleted => if all previous steps are completed and user checks the case, put this formCompleted to true when submit the form, else if one of the previous steps completed is false, you have to redirect on stepOne (/repairer/inscription)
    usefully for example if a user navigate via browser url /reparateur/inscription/mon-enseigne or if he quit the multiform to navigate on another website page, he must not complete this form part if one of a previous form step is not complete so we have to redirect on stepOne.
    A good idea is to check when the user quit the form (navigating on another page,
    close the browser tab) when he had started
    to complete some fields to avert him (by a modal) that he can lose the data fill-in form */

  return (
    <>
      <RegistrationTunnelUserInfo />
    </>
  );
};

RepairerRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerRegistration;
