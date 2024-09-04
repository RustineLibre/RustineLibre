import {Box, Button, CircularProgress, Stack, Typography} from '@mui/material';
import AddAPhotoIcon from '@mui/icons-material/AddAPhoto';
import React, {useContext, useState} from 'react';
import {checkFileSize} from '@helpers/checkFileSize';
import {mediaObjectResource} from '@resources/mediaObjectResource';
import {errorRegex} from '@utils/errorRegex';
import {HomepagePictureContext} from '@contexts/HomepagePictureContext';

export const ParametersHomepagePicture = (): JSX.Element => {
  const {photo, setPhoto} = useContext(HomepagePictureContext);
  const [loadingPhoto, setLoadingPhoto] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleFileChange = async (
    event: React.ChangeEvent<HTMLInputElement>
  ): Promise<void> => {
    if (event.target.files && event.target.files[0]) {
      setErrorMessage(null);
      const file = event.target.files[0];
      if (!checkFileSize(file)) {
        setErrorMessage(
          'Votre photo dépasse la taille maximum autorisée (5mo)'
        );
        return;
      }

      setLoadingPhoto(true);
      try {
        // Upload new picture
        const mediaObjectResponse = await mediaObjectResource.uploadImage(file);
        if (mediaObjectResponse) {
          // Display new photo
          setPhoto(mediaObjectResponse);
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
    <Stack
      spacing={4}
      display="flex"
      flexDirection="column"
      alignItems="center"
      width="100%">
      <Typography variant="h5" component="label">
        Ajouter une photo
      </Typography>
      {errorMessage && (
        <Typography sx={{textAlign: 'center', color: 'red'}}>
          {errorMessage}
        </Typography>
      )}
      <Box>
        {loadingPhoto && <CircularProgress />}
        {!loadingPhoto && (
          <label htmlFor="fileUpload">
            {!photo ? (
              <Box
                border="1px solid"
                borderColor="grey.300"
                p={2}
                borderRadius={5}
                sx={{cursor: 'pointer'}}
                display="flex"
                flexDirection="column"
                alignItems="center">
                <Typography component="p" sx={{mt: 2}}>
                  Sélectionner la photo
                </Typography>
                <AddAPhotoIcon sx={{fontSize: '3em'}} color="primary" />
              </Box>
            ) : (
              <Box
                sx={{
                  overflow: 'hidden',
                  borderRadius: 6,
                  boxShadow: 4,
                }}>
                <img
                  height="auto"
                  src={photo.contentUrl}
                  alt="Photo du diagnostic"
                  style={{
                    cursor: 'pointer',
                    display: 'block',
                    maxWidth: '400px',
                    width: '100%',
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
      </Box>
    </Stack>
  );
};
