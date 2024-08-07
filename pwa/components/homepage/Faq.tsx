import React, {useState} from 'react';
import Link from 'next/link';
import {
  Box,
  Typography,
  Button,
  AccordionSummary,
  AccordionDetails,
  Accordion,
  Container,
} from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import Image from 'next/image';
import LetterR from '@components/common/LetterR';

const questions = [
  {
    id: 1,
    title: 'Dois-je me créer un compte pour utiliser Rustine Libre ?',
    answer:
      "Pas besoin de créer un compte pour faire une recherche de solution de réparation près de chez vous ! Cela vous sera demandé au moment de la prise de rendez-vous, mais pas d'inquiétude, c'est très simple.",
  },
  {
    id: 2,
    title: "Pourquoi utiliser l'application Rustine Libre ?",
    answer:
      "Parce que Rustine Libre est pratique, elle regroupe des mécanicien·nes portant des valeurs et c'est une plateforme éthique.",
  },
  {
    id: 3,
    title: "Est-ce que l’utilisation de l'application est payante ?",
    answer:
      "L'utilisation de l'application est gratuite. Aucune transaction ne se fait sur la plateforme, et le paiement des réparations se fait selon les modalités de chaque mécanicien·ne référencée.",
  },
];

const Faq = () => {
  const [current, setCurrent] = useState<number>(0);

  return (
    <Box bgcolor="lightsecondary.main" position="relative">
      <Container>
        <Box
          display="flex"
          flexDirection={{xs: 'column-reverse', md: 'row'}}
          bgcolor="lightsecondary.main">
          <Box flex={1}>
            <Box
              maxWidth="500px"
              mx="auto"
              flex={1}
              display="flex"
              flexDirection="column"
              justifyContent="center"
              py={10}
              px={{xs: 2, md: 6}}>
              <Typography variant="h4" component="p" color="secondary.main">
                Une question&nbsp;?
                <br />
                Rustine libre a la réponse&nbsp;!
              </Typography>
              <Box
                my={3}
                display="flex"
                minHeight={{xs: 0, md: '250px'}}
                flexDirection="column"
                justifyContent="flex-start">
                {questions.map((question) => (
                  <Accordion
                    disableGutters
                    elevation={0}
                    sx={{
                      position: 'relative',
                      transition: 'all ease 0.5s',
                      bgcolor: 'transparent',
                      mx: 0,
                      mt: 0,
                      mb: current === question.id ? 2 : 0,
                      '&::before': {
                        content: 'none',
                      },
                    }}
                    key={question.id}
                    expanded={current === question.id}
                    onChange={() => {
                      current === question.id
                        ? setCurrent(0)
                        : setCurrent(question.id);
                    }}>
                    <AccordionSummary
                      sx={{
                        bgcolor: 'transparent',
                        px: 0,
                        zIndex: 1,
                        '& .MuiAccordionSummary-content': {my: 1},
                      }}>
                      <Typography
                        variant="body2"
                        fontWeight={600}
                        color={
                          current === question.id
                            ? 'secondary.main'
                            : 'text.primary'
                        }
                        sx={{display: 'flex', justifyContent: 'space-between'}}>
                        {question.title}
                        {current === question.id ? (
                          <ExpandLessIcon color="secondary" />
                        ) : (
                          <ExpandMoreIcon />
                        )}
                      </Typography>
                    </AccordionSummary>
                    <AccordionDetails sx={{p: 0, zIndex: 1}}>
                      <Typography variant="body2" color="text.secondary">
                        {question.answer}
                      </Typography>
                    </AccordionDetails>
                  </Accordion>
                ))}
              </Box>
              <Link legacyBehavior passHref href="/faq">
                <Button variant="contained" color="secondary" size="large">
                  D&apos;autres questions ?
                </Button>
              </Link>
            </Box>
          </Box>
          <Box
            width={{xs: '100%', md: '50%'}}
            height={{xs: '150px', md: 'auto'}}
            position="relative">
            <Box
              width={{xs: '100vw', md: '50vw'}}
              height="100%"
              position="absolute"
              left={{xs: '50%', md: 0}}
              top="0"
              sx={{
                transform: {
                  xs: 'translateX(-50%)',
                  md: 'none',
                },
              }}>
              <Box position="relative" height="100%" width="100%">
                <Box
                  position="absolute"
                  top={{xs: '100%', md: '15%'}}
                  left={{xs: '60%', md: '0'}}
                  width="110px"
                  height="110px"
                  zIndex={5}
                  sx={{
                    transform: {xs: 'translateY(-50%)', md: 'translateX(-50%)'},
                  }}>
                  <LetterR color="secondary" />
                </Box>
                <Image
                  fill
                  alt=""
                  src="/img/faq.webp"
                  style={{
                    objectFit: 'cover',
                  }}
                />
              </Box>
            </Box>
          </Box>
        </Box>
      </Container>
    </Box>
  );
};

export default Faq;
