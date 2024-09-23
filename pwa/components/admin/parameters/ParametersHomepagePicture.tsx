import {
  Box,
  Card,
  CircularProgress,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import AddAPhotoIcon from '@mui/icons-material/AddAPhoto';
import React, {useContext, useEffect, useState} from 'react';
import {checkFileSize} from '@helpers/checkFileSize';
import {mediaObjectResource} from '@resources/mediaObjectResource';
import {errorRegex} from '@utils/errorRegex';
import {HomepagePictureContext} from '@contexts/HomepagePictureContext';
import {websiteMediaResource} from '@resources/WebsiteMediaResource';
import {WebsiteMedia} from '@interfaces/WebsiteMedia';
import {MediaObject} from '@interfaces/MediaObject';

export const ParametersHomepagePicture = (): JSX.Element => {
  const {photo, setPhoto} = useContext(HomepagePictureContext);
  const [loadingPhoto, setLoadingPhoto] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [oldPicture, setOldPicture] = useState<WebsiteMedia | null>(null);

  const fetchPicture = async () => {
    const response = await websiteMediaResource.getById(
      'homepage_main_picture'
    );

    setOldPicture(response);
    console.log(response);
  };

  const fetchMediaPicture = async (mediaId: MediaObject) => {
    const response = await mediaObjectResource.getById(mediaId['@id']);

    console.log(response);
    return response;
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

        console.log(mediaObjectResponse);
        if (!oldPicture && mediaObjectResponse) {
          const newWebsiteMedia = await websiteMediaResource.post({
            id: 'homepage_main_picture',
            media: mediaObjectResponse['@id'],
          });

          setOldPicture(newWebsiteMedia);
          setPhoto(mediaObjectResponse);
        } else if (oldPicture && mediaObjectResponse && oldPicture.media) {
          const newWebsiteMedia = await websiteMediaResource.patch(
            oldPicture['@id'],
            {
              media: mediaObjectResponse['@id'],
            }
          );

          await mediaObjectResource.delete(oldPicture.media['@id']);
          setPhoto(mediaObjectResponse);
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
      <Card elevation={4} component={Paper} sx={{mt: 3}}>
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
                    src={oldPicture?.media?.contentUrl}
                    alt="Photo de la page d'accueil"
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
      </Card>
    </Stack>
  );
};
