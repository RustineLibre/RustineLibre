const {withSentryConfig} = require('@sentry/nextjs');
const withPWA = require('next-pwa')({
  dest: 'public',
});

/** @type {import('next').NextConfig} */
const nextConfig = withPWA({
  reactStrictMode: true,
  swcMinify: true,
  output: 'standalone',
  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'storage.googleapis.com',
      },
    ],
  },
  eslint: {
    ignoreDuringBuilds: true,
    dirs: [
      'components',
      'contexts',
      'helpers',
      'hooks',
      'interfaces',
      'pages',
      'resources',
      'utils',
      'config',
      '__e2e__',
    ],
  },
});

const sentryAuthToken = process.env.SENTRY_AUTH_TOKEN;

// Source maps are uploaded to Sentry only when SENTRY_AUTH_TOKEN is defined;
// without it the build is unchanged and never fails because of Sentry.
module.exports = withSentryConfig(nextConfig, {
  org: 'cooptilleuls',
  project: 'rustinelibre-js',
  sentryUrl: 'https://de.sentry.io',
  authToken: sentryAuthToken,
  release: {
    name: process.env.NEXT_PUBLIC_SENTRY_RELEASE || undefined,
    create: Boolean(sentryAuthToken),
    finalize: Boolean(sentryAuthToken),
  },
  sourcemaps: {
    disable: !sentryAuthToken,
  },
  silent: true,
  telemetry: false,
});
