import * as Sentry from '@sentry/nextjs';
import type {NextPageContext} from 'next';
import NextError from 'next/error';

type ErrorPageProps = {statusCode: number};

const CustomErrorComponent = ({statusCode}: ErrorPageProps) => {
  return <NextError statusCode={statusCode} />;
};

CustomErrorComponent.getInitialProps = async (contextData: NextPageContext) => {
  // Report errors thrown while rendering pages (no-op when Sentry is disabled).
  await Sentry.captureUnderscoreErrorException(contextData);

  return NextError.getInitialProps(contextData);
};

export default CustomErrorComponent;
