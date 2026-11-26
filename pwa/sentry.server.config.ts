import * as Sentry from '@sentry/nextjs';
import {sentryOptions} from './config/sentry';

Sentry.init(sentryOptions);
