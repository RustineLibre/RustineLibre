import {
  Box,
  CircularProgress,
  Paper,
  Typography,
  useMediaQuery,
} from '@mui/material';
import AddAPhotoIcon from '@mui/icons-material/AddAPhoto';
import React, {useEffect, useState} from 'react';
import {checkFileSize} from '@helpers/checkFileSize';
import {mediaObjectResource} from '@resources/mediaObjectResource';
import {errorRegex} from '@utils/errorRegex';
import {websiteMediaResource} from '@resources/WebsiteMediaResource';
import {WebsiteMedia} from '@interfaces/WebsiteMedia';
import theme from '../../../styles/theme';

export const ParametersHomepagePicture = (): JSX.Element => {
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [loadingPhoto, setLoadingPhoto] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [oldPicture, setOldPicture] = useState<WebsiteMedia | null>(null);

  const fetchPicture = async () => {
    const response = await websiteMediaResource.getById(
      'homepage_main_picture',
      false
    );
    response ? setOldPicture(response) : setOldPicture(null);
  };

  useEffect(() => {
    fetchPicture();
  }, []);

  const handleFileChange = async (
    event: React.ChangeEvent<HTMLInputElement>
  ): Promise<void> => {
    if (event.target.files && event.target.files[0]) {
      setErrorMessage(null);
      let file = event.target.files[0];
      if (!checkFileSize(file, 0.3)) {
        setErrorMessage(
          'Votre photo dépasse la taille maximum autorisée (300ko)'
        );
        return;
      }

      setLoadingPhoto(true);
      try {
        // Upload new picture
        const mediaObjectResponse = await mediaObjectResource.uploadImage(file);

        if (!oldPicture && mediaObjectResponse) {
          const newWebsiteMedia = await websiteMediaResource.post({
            id: 'homepage_main_picture',
            media: mediaObjectResponse['@id'],
          });

          setOldPicture(newWebsiteMedia);
        } else if (oldPicture && mediaObjectResponse && oldPicture.media) {
          const newWebsiteMedia = await websiteMediaResource.patch(
            oldPicture['@id'],
            {
              media: mediaObjectResponse['@id'],
            }
          );

          await mediaObjectResource.delete(oldPicture.media['@id']);
          setOldPicture(newWebsiteMedia);
        } else {
          setErrorMessage(
            `Envoi de l'image impossible : il semble y avoir un problème dans la phase d'envoi`
          );
        }
      } catch (e: any) {
        setErrorMessage(
          `Envoi de l'image impossible : ${e.message?.replace(
            errorRegex,
            '$2'
          )}`
        );
        setTimeout(() => setErrorMessage(null), 3000);
      }
      setLoadingPhoto(false);
    }
  };

  return (
    <Box marginTop={'30px'}>
      <Typography variant="h5" component="label">
        Visuel homepage
      </Typography>
      {errorMessage && (
        <Typography sx={{textAlign: 'center', color: 'red'}}>
          {errorMessage}
        </Typography>
      )}

      <Box
        width="100%"
        elevation={4}
        component={Paper}
        sx={{mt: 3}}
        padding={'30px'}>
        {loadingPhoto && (
          <Box display={'flex'} justifyContent={'center'} margin={'20px'}>
            <CircularProgress />
          </Box>
        )}
        {!loadingPhoto && (
          <label htmlFor="fileUpload">
            {!oldPicture ? (
              <Box
                border="1px solid"
                borderColor="grey.300"
                p={2}
                borderRadius={5}
                sx={{cursor: 'pointer', marginX: 'auto'}}
                display="flex"
                flexDirection="column"
                alignItems="center"
                marginBottom={'30px'}
                width={isMobile ? '80%' : '30%'}>
                <Typography component="p" sx={{mt: 2}} alignContent={'center'}>
                  Sélectionner la photo
                </Typography>
                <AddAPhotoIcon sx={{fontSize: '3em'}} color="primary" />
              </Box>
            ) : (
              <Box
                display={'flex'}
                justifyContent={'center'}
                marginBottom={'30px'}>
                <img
                  height="auto"
                  src={oldPicture.media?.contentUrl}
                  alt="Photo de la page d'accueil"
                  style={{
                    cursor: 'pointer',
                    display: 'block',
                    maxWidth: isMobile ? '200px' : '400px',
                  }}
                />
              </Box>
            )}
          </label>
        )}
        <input
          id="fileUpload"
          name="fileUpload"
          type="file"
          hidden
          accept={'.png, .jpg, .jpeg'}
          onChange={(e) => handleFileChange(e)}
        />
        <Typography textAlign={'center'}>
          Type de fichier : JPG ou PNG. Taille à respecter : 982 px / 825 px.
          Poids max : 300 ko
        </Typography>
      </Box>
    </Box>
  );
};
