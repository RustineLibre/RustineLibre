import React, {ReactElement} from 'react';
import {RepairerRegistrationProvider} from '@contexts/RepairerRegistrationContext';
import {Box, Container, Grid, Typography} from '@mui/material';
import Head from 'next/head';
import WebsiteLayout from './WebsiteLayout';
import LetterR from '@components/common/LetterR';

interface RepairerRegistrationLayoutProps {
  children: ReactElement;
  registrationCompleted?: boolean;
}

const RepairerRegistrationLayout: React.FC<RepairerRegistrationLayoutProps> = ({
  children,
  registrationCompleted = false,
}) => {
  return (
    <>
      <Head>
        <title>Devenir réparateur | Rustine Libre</title>
      </Head>
      <WebsiteLayout>
        <RepairerRegistrationProvider>
          {registrationCompleted ? (
            children
          ) : (
            <>
              <Box
                bgcolor="lightprimary.light"
                height="100%"
                width="100%"
                position="absolute"
                top="0"
                left="0"
                zIndex="-1"
              />
              <Container>
                <Box
                  py={4}
                  display="flex"
                  flexDirection={{xs: 'column', md: 'row'}}
                  gap={4}
                  position="relative"
                  alignItems="flex-start">
                  <Box
                    minHeight={{xs: '0', md: 'calc(100vh - 200px)'}}
                    justifyContent="center"
                    display="flex"
                    flexDirection="column"
                    alignItems={{xs: 'center', md: 'flex-start'}}
                    textAlign={{xs: 'center', md: 'left'}}
                    width={{xs: '100%', md: '45%'}}
                    mx="auto"
                    maxWidth={{xs: '600px', md: '100%'}}>
                    <Typography variant="h1" sx={{mb: 4}} color="primary">
                      Devenir réparateur
                    </Typography>
                    <Typography variant="body1">
                      Tu es un.e professionnel.le du vélo, dans une association
                      ou un atelier indépendant ?
                      <br />
                      Tu as envie de rejoindre un collectif de pairs sur ton
                      territoire ?
                      <br />
                      Tu cherches un outil numérique qui te référence et qui te
                      permet de gérer tes rendez-vous avec tes usagers ?
                      <br />
                      Tu peux remplir ce formulaire et ton collectif local
                      reviendra vers toi rapidement!
                      <br />
                    </Typography>
                    <Typography my={2} variant="h4" color="secondary">
                      Inscris-toi !
                    </Typography>
                    <Box
                      sx={{
                        transform: {
                          xs: 'translateX(-30%)',
                          md: 'translateX(-50%) translateY(20%)',
                          lg: 'translateX(-125%) translateY(20%)',
                        },
                        position: {
                          xs: 'absolute',
                          md: 'static',
                        },
                        left: '0',
                        bottom: '10%',
                      }}>
                      <img alt="" src="/img/flower.svg" width="110px" />
                    </Box>
                  </Box>
                  <Box
                    component="form"
                    noValidate
                    sx={{
                      mt: 1,
                      bgcolor: 'white',
                      px: {xs: 3, md: 5},
                      py: {xs: 4, md: 5},
                      boxShadow: 2,
                      width: {xs: '90%', md: '55%'},
                      borderRadius: 6,
                      mx: 'auto',
                      maxWidth: '700px',
                      position: 'relative',
                    }}>
                    <Box
                      position="absolute"
                      top={{xs: '0', md: '50px'}}
                      left={{xs: '100%', md: '0%'}}
                      width={{xs: '80px', md: '110px'}}
                      sx={{
                        transform: {
                          xs: 'translateY(-80%) translateX(-110%)',
                          md: 'translateX(-85%)',
                        },
                      }}>
                      <LetterR color="secondary" />
                    </Box>
                    <Grid container spacing={2} direction="column">
                      {children}
                    </Grid>
                  </Box>
                </Box>
              </Container>
            </>
          )}
        </RepairerRegistrationProvider>
      </WebsiteLayout>
    </>
  );
};

export default RepairerRegistrationLayout;
