import React, {useEffect, useState} from 'react';
import {exceptionalClosureResource} from '@resources/exceptionalClosingResource';
import ModalAddExceptionalClosure from '@components/dashboard/agenda/ModalAddExceptionalClosure';
import {
  Box,
  Button,
  Chip,
  CircularProgress,
  Grid,
  Divider,
  Typography,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import {RepairerExceptionalClosure} from '@interfaces/RepairerExceptionalClosure';
import {Repairer} from '@interfaces/Repairer';
import {formatDate} from '@helpers/dateHelper';

interface ExceptionalClosureProps {
  repairer: Repairer;
}

export const ExceptionalClosure = ({
  repairer,
}: ExceptionalClosureProps): JSX.Element => {
  const [loading, setLoading] = useState<boolean>(true);
  const [exceptionalClosures, setExceptionalClosures] = useState<
    RepairerExceptionalClosure[]
  >([]);
  const [openModal, setOpenModal] = useState<boolean>(false);
  const handleOpenModal = (): void => setOpenModal(true);
  const handleCloseModal = (refresh = true): void => {
    setOpenModal(false);
    if (refresh) {
      fetchClosure();
    }
  };

  const fetchClosure = async () => {
    setLoading(true);
    const exceptionalClosuresFetch = await exceptionalClosureResource.getAll(
      true,
      {
        repairer: repairer.id,
      }
    );
    setExceptionalClosures(exceptionalClosuresFetch['hydra:member']);
    setLoading(false);
  };

  useEffect(() => {
    fetchClosure();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const removeExceptionalClosure = async (exceptionalClosureIri: string) => {
    setLoading(true);
    await exceptionalClosureResource.delete(exceptionalClosureIri);
    await fetchClosure();
  };

  return (
    <>
      <Typography variant="h5">
        Programmation d&apos;une fermeture exceptionelle
      </Typography>
      <Box sx={{marginTop: '40px'}}>
        {loading && <CircularProgress />}
        {!loading &&
          exceptionalClosures.map((exceptionalClosure) => {
            return (
              <Box sx={{marginBottom: '20px'}} key={exceptionalClosure.id}>
                <Grid container spacing={2} sx={{marginBottom: '20px'}}>
                  <Grid item xs={8}>
                    {loading && <CircularProgress />}
                    <Chip
                      label={`${formatDate(
                        exceptionalClosure.startDate,
                        false
                      )} - ${formatDate(exceptionalClosure.endDate, false)}`}
                      deleteIcon={<DeleteIcon />}
                      onDelete={() =>
                        removeExceptionalClosure(exceptionalClosure['@id'])
                      }
                    />
                  </Grid>
                </Grid>
                <Divider />
              </Box>
            );
          })}
        {!loading && exceptionalClosures.length === 0 && (
          <Box sx={{marginBottom: '30px'}}>
            Aucune fermeture exceptionnelle programmée
          </Box>
        )}
        <br />
        <Button
          variant="contained"
          onClick={handleOpenModal}
          startIcon={<AddIcon />}>
          Ajouter une plage horaire
        </Button>
      </Box>
      <ModalAddExceptionalClosure
        openModal={openModal}
        handleCloseModal={handleCloseModal}
        repairer={repairer}
      />
    </>
  );
};

export default ExceptionalClosure;
