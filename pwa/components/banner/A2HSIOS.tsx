import React, {useEffect, useState} from 'react';
import {DAYS_TO_ADD_FOR_NEW_PROPOSAL} from '@constants/A2HS';
import {Box, Typography} from '@mui/material';
import IosShareIcon from '@mui/icons-material/IosShare';
import AddBoxOutlinedIcon from '@mui/icons-material/AddBoxOutlined';
import CloseRoundedIcon from '@mui/icons-material/CloseRounded';

interface IosNavigator extends Navigator {
  standalone: boolean;
}

/**
 * Adds an "Add To Home Screen" banner, for safari on ios.
 */
export const A2HSIOS = (): JSX.Element => {
  const [display, setDisplay] = useState(false);

  const handleInstallationBanner = () => {
    const isMobile = window.matchMedia(
      'only screen and (max-width: 480px)'
    ).matches;
    const isIos = () => {
      const userAgent = window.navigator.userAgent.toLowerCase();
      return /iphone/.test(userAgent);
    };
    const isInStandaloneMode =
      'standalone' in window.navigator &&
      (window.navigator as IosNavigator).standalone;
    if (isMobile && isIos() && !isInStandaloneMode) {
      setTimeout(setDisplay, 7000, true);
    }
  };

  const handleClose = (): void => {
    const date = new Date();
    localStorage.setItem(
      'rustine_libre_pwa_banner_future_proposal',
      new Date(
        date.setDate(date.getDate() + DAYS_TO_ADD_FOR_NEW_PROPOSAL)
      ).toISOString()
    );
    setDisplay(false);
  };

  useEffect(() => {
    const futureDateProposal = localStorage.getItem(
      'rustine_libre_pwa_banner_future_proposal'
    );
    if (futureDateProposal && new Date() <= new Date(futureDateProposal)) {
      return;
    }
    handleInstallationBanner();
  }, []);

  return (
    <>
      {display && (
        <Box
          gap={2}
          p={1}
          display="flex"
          alignItems="start"
          zIndex="tooltip"
          width="100%"
          position="fixed"
          sx={{backgroundColor: 'white'}}
          boxShadow={1}>
          <CloseRoundedIcon
            onClick={handleClose}
            color="primary"
            sx={{
              '& :hover': {
                cursor: 'pointer',
              },
            }}
          />
          <Box width="100%" display="flex" flexDirection="column" gap={1}>
            <Typography
              fontSize={14}
              fontWeight={600}
              display="flex"
              alignItems="center"
              color="primary">
              Rustine Libre
            </Typography>
            <Typography fontSize={10} display="flex" alignItems="center">
              Pour installer la WebApp, appuyez sur
              <IosShareIcon sx={{mx: 1}} color="primary" />
            </Typography>
            <Typography fontSize={10} display="flex" alignItems="center">
              et sélectionnez &quot;Sur l&apos;écran d&apos;accueil&quot;
              <AddBoxOutlinedIcon
                sx={{mx: 1, textAlign: 'center'}}
                color="primary"
              />
            </Typography>
          </Box>
        </Box>
      )}
    </>
  );
};