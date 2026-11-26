import type {Event} from '@sentry/nextjs';

// Sentry is inert as long as NEXT_PUBLIC_SENTRY_DSN is empty (dev, CI, tests, builds without DSN).
// NEXT_PUBLIC_* values are inlined at build time: they are passed as Docker build args.
export const sentryDsn: string | undefined =
  process.env.NEXT_PUBLIC_SENTRY_DSN || undefined;

export const sentryOptions = {
  dsn: sentryDsn,
  enabled: Boolean(sentryDsn),
  environment: process.env.NEXT_PUBLIC_SENTRY_ENVIRONMENT || 'development',
  release: process.env.NEXT_PUBLIC_SENTRY_RELEASE || undefined,
  sendDefaultPii: false,
  // No tracing, no session replay.
  tracesSampleRate: 0,
  beforeSend: scrubEvent,
};

const SENSITIVE_PARAMS = /([?&](?:token|code|key|password|secret)=)[^&#]*/gi;

export function scrubUrl(url: string): string {
  return url.replace(SENSITIVE_PARAMS, '$1[Filtered]');
}

export function scrubEvent<T extends Event>(event: T): T {
  if (event.request?.url) {
    event.request.url = scrubUrl(event.request.url);
  }
  if (
    event.request?.query_string &&
    typeof event.request.query_string === 'string'
  ) {
    event.request.query_string = scrubUrl(
      `?${event.request.query_string}`
    ).slice(1);
  }
  if (event.request?.cookies) {
    delete event.request.cookies;
  }
  event.breadcrumbs?.forEach((breadcrumb) => {
    const url = breadcrumb.data?.url;
    if (typeof url === 'string' && breadcrumb.data) {
      breadcrumb.data.url = scrubUrl(url);
    }
  });
  return event;
}
